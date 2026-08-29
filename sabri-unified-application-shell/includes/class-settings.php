<?php
/**
 * Settings storage, schema, and sanitization.
 *
 * @package SabriUnifiedApplicationShell
 */

namespace Sabri\UnifiedShell;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WordPress Settings API adapter.
 */
final class Settings {
	/**
	 * Register Settings API option.
	 *
	 * @return void
	 */
	public static function register() {
		register_setting(
			'sabri_shell_settings',
			Defaults::OPTION_NAME,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => Defaults::settings(),
			)
		);
	}

	/**
	 * Get current settings merged with defaults.
	 *
	 * @return array<string,mixed>
	 */
	public static function get() {
		$raw = get_option( Defaults::OPTION_NAME, array() );
		if ( ! is_array( $raw ) ) {
			$raw = array();
		}

		return self::enforce_owned_invariants( self::deep_merge( Defaults::settings(), $raw ) );
	}

	/**
	 * Ensure defaults exist without deleting unknown future values.
	 *
	 * @return void
	 */
	public static function ensure_defaults() {
		$current = get_option( Defaults::OPTION_NAME, array() );
		if ( ! is_array( $current ) ) {
			$current = array();
		}

		$merged                   = self::deep_merge( Defaults::settings(), $current );
		$merged['schema_version'] = Defaults::SCHEMA_VERSION;
		self::update_programmatically( $merged );
	}

	/**
	 * Canonical persistence path for trusted File 20 programmatic settings writes.
	 *
	 * The registered Settings API sanitizer is intentionally tab-oriented for
	 * options.php submissions. Trusted internal workflows do not carry an
	 * _active_tab marker, so calling update_option() directly while that sanitizer
	 * is registered can normalize the proposed value back to the old settings.
	 *
	 * This method suspends only this class' Settings API sanitizer for the exact
	 * bounded write, explicitly applies File 20 ownership invariants, leaves every
	 * other WordPress/core/security/concurrency pre-update filter active, and
	 * restores the sanitizer in finally. Nested calls are safe: only the call that
	 * actually removed the filter restores it.
	 *
	 * @param array<string,mixed> $settings Trusted File 20 settings state.
	 * @return bool WordPress update_option() result.
	 */
	public static function update_programmatically( array $settings ) {
		$settings = self::enforce_owned_invariants( $settings );
		$hook     = 'sanitize_option_' . Defaults::OPTION_NAME;
		$callback = array( __CLASS__, 'sanitize' );
		$removed  = false;

		if ( function_exists( 'remove_filter' ) ) {
			$removed = remove_filter( $hook, $callback, 10 );
		}

		try {
			return update_option( Defaults::OPTION_NAME, $settings, false );
		} finally {
			if ( $removed && function_exists( 'add_filter' ) ) {
				add_filter( $hook, $callback, 10, 1 );
			}
		}
	}

	/**
	 * Sanitize submitted settings. Only the submitted tab is normalized.
	 *
	 * @param array<string,mixed> $input Raw submitted settings.
	 * @return array<string,mixed>
	 */
	public static function sanitize( $input ) {
		$existing = self::get();
		if ( ! is_array( $input ) ) {
			return $existing;
		}

		$tab      = isset( $input['_active_tab'] ) ? sanitize_key( $input['_active_tab'] ) : '';
		$output   = $existing;
		$defaults = Defaults::settings();

		switch ( $tab ) {
			case 'overview':
			case 'safe-mode':
				$output['enabled']             = self::bool_from_input( $input, 'enabled', $existing['enabled'] );
				$output['emergency_disabled']  = self::bool_from_input( $input, 'emergency_disabled', $existing['emergency_disabled'] );
				$output['delete_on_uninstall'] = self::bool_from_input( $input, 'delete_on_uninstall', $existing['delete_on_uninstall'] );
				break;

			case 'layout':
				$output['layout'] = self::sanitize_layout_group( isset( $input['layout'] ) && is_array( $input['layout'] ) ? $input['layout'] : array(), $existing['layout'] );
				break;

			case 'header':
				$output['header'] = self::sanitize_header_group( isset( $input['header'] ) && is_array( $input['header'] ) ? $input['header'] : array(), $existing['header'] );
				break;

			case 'navigation':
				$output['navigation'] = self::sanitize_navigation_group( isset( $input['navigation'] ) && is_array( $input['navigation'] ) ? $input['navigation'] : array(), $existing['navigation'] );
				break;

			case 'left-sidebar':
				$output['left_sidebar'] = self::sanitize_left_sidebar_group( isset( $input['left_sidebar'] ) && is_array( $input['left_sidebar'] ) ? $input['left_sidebar'] : array(), $existing['left_sidebar'] );
				break;

			case 'right-sidebar':
				$output['right_sidebar'] = self::sanitize_right_sidebar_group( isset( $input['right_sidebar'] ) && is_array( $input['right_sidebar'] ) ? $input['right_sidebar'] : array(), $existing['right_sidebar'] );
				break;

			case 'mobile':
				$output['mobile'] = self::sanitize_mobile_group( isset( $input['mobile'] ) && is_array( $input['mobile'] ) ? $input['mobile'] : array(), $existing['mobile'] );
				break;

			case 'integrations':
				$output['integrations'] = self::sanitize_integrations_group( isset( $input['integrations'] ) && is_array( $input['integrations'] ) ? $input['integrations'] : array(), $existing['integrations'] );
				break;

		}

		$output['schema_version'] = Defaults::SCHEMA_VERSION;
		$output                  = self::enforce_owned_invariants( self::deep_merge( $defaults, $output ) );

		Navigation::invalidate_cache();
		Integrations::invalidate_cache();

		return $output;
	}


	/**
	 * Enforce non-negotiable File 20 ownership/safety invariants on every read/write.
	 *
	 * Historical keys may exist in old database rows, but they cannot reactivate
	 * retired File 20 domain behavior or role/visual authority.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @return array<string,mixed>
	 */
	public static function enforce_owned_invariants( array $settings ) {
		$settings['schema_version'] = Defaults::SCHEMA_VERSION;
		$settings['home_feed'] = array(
			'retired'     => true,
			'auto_insert' => false,
			'posts_count' => 0,
		);
		if ( isset( $settings['navigation']['home']['shortcode'] ) && 'sabri_shell_home_feed' === sanitize_key( (string) $settings['navigation']['home']['shortcode'] ) ) {
			$settings['navigation']['home']['shortcode'] = 'sabri_complete_home_feed';
		}
		if ( isset( $settings['header'] ) && is_array( $settings['header'] ) ) {
			unset( $settings['header']['allowed_roles'] );
		}
		if ( isset( $settings['mobile'] ) && is_array( $settings['mobile'] ) ) {
			$settings['mobile']['bottom_nav'] = false;
		}
		if ( isset( $settings['integrations'] ) && is_array( $settings['integrations'] ) ) {
			unset( $settings['integrations']['functions'] );
		}
		if ( isset( $settings['right_sidebar']['home_modules'] ) && is_array( $settings['right_sidebar']['home_modules'] ) ) {
			foreach ( array( 'doctors', 'latest_posts', 'research', 'marketplace' ) as $legacy_domain_module ) {
				unset( $settings['right_sidebar']['home_modules'][ $legacy_domain_module ] );
			}
		}
		if ( isset( $settings['right_sidebar']['clinic_modules'] ) && is_array( $settings['right_sidebar']['clinic_modules'] ) ) {
			foreach ( array( 'finder', 'filters', 'doctors', 'emergency' ) as $legacy_domain_module ) {
				unset( $settings['right_sidebar']['clinic_modules'][ $legacy_domain_module ] );
			}
		}
		if ( isset( $settings['right_sidebar']['single_modules'] ) && is_array( $settings['right_sidebar']['single_modules'] ) ) {
			unset( $settings['right_sidebar']['single_modules']['reviews'], $settings['right_sidebar']['single_modules']['safety'] );
		}
		if ( isset( $settings['right_sidebar'] ) && is_array( $settings['right_sidebar'] ) ) {
			unset( $settings['right_sidebar']['emergency_notice'] );
		}
		return $settings;
	}

	/** Enforce core invariants for every programmatic option write, not only Settings API submissions. */
	public static function enforce_owned_invariants_filter( $value, $old_value, $option ) {
		unset( $option );
		if ( ! is_array( $value ) ) {
			return is_array( $old_value ) ? $old_value : Defaults::settings();
		}
		return self::enforce_owned_invariants( $value );
	}

	/**
	 * Merge arrays recursively while preserving unknown keys.
	 *
	 * @param array<string,mixed> $base Base array.
	 * @param array<string,mixed> $overrides Override array.
	 * @return array<string,mixed>
	 */
	public static function deep_merge( array $base, array $overrides ) {
		if ( self::is_list( $base ) || ( array() !== $overrides && self::is_list( $overrides ) ) ) {
			return array_values( $overrides );
		}

		foreach ( $overrides as $key => $value ) {
			if ( is_array( $value ) && isset( $base[ $key ] ) && is_array( $base[ $key ] ) ) {
				$base[ $key ] = self::deep_merge( $base[ $key ], $value );
			} else {
				$base[ $key ] = $value;
			}
		}

		return $base;
	}

	/**
	 * Determine whether an array is a sequential list.
	 *
	 * @param array<mixed> $value Array.
	 * @return bool
	 */
	private static function is_list( array $value ) {
		if ( function_exists( 'array_is_list' ) ) {
			return array_is_list( $value );
		}

		if ( array() === $value ) {
			return true;
		}

		return array_keys( $value ) === range( 0, count( $value ) - 1 );
	}

	/**
	 * Sanitize layout fields.
	 *
	 * @param array<string,mixed> $input Input.
	 * @param array<string,mixed> $existing Existing.
	 * @return array<string,mixed>
	 */
	private static function sanitize_layout_group( array $input, array $existing ) {
		$output                                = $existing;
		$output['max_width']                   = self::int_range( $input, 'max_width', 960, 2400, $existing['max_width'] );
		$output['left_width']                  = self::int_range( $input, 'left_width', 220, 380, $existing['left_width'] );
		$output['right_width']                 = self::int_range( $input, 'right_width', 260, 460, $existing['right_width'] );
		$output['gap']                         = self::int_range( $input, 'gap', 8, 48, $existing['gap'] );
		$output['sticky_header']               = self::bool_from_input( $input, 'sticky_header', $existing['sticky_header'] );
		$output['compact_desktop']             = self::bool_from_input( $input, 'compact_desktop', $existing['compact_desktop'] );
		$output['worldwide_clinic_page_id']    = absint( isset( $input['worldwide_clinic_page_id'] ) ? $input['worldwide_clinic_page_id'] : 0 );
		$output['clinic_post_type']            = sanitize_key( isset( $input['clinic_post_type'] ) ? $input['clinic_post_type'] : $existing['clinic_post_type'] );
		$output['excluded_page_ids']           = self::sanitize_id_list( isset( $input['excluded_page_ids'] ) ? $input['excluded_page_ids'] : array() );
		$output['per_page_overrides']          = self::sanitize_layout_overrides( isset( $input['per_page_overrides'] ) ? $input['per_page_overrides'] : array() );
		$output['theme_content_selector']      = self::sanitize_css_selector( isset( $input['theme_content_selector'] ) ? $input['theme_content_selector'] : '' );
		$output['hide_theme_header']           = self::bool_from_input( $input, 'hide_theme_header', $existing['hide_theme_header'] );
		$output['hide_theme_footer']           = self::bool_from_input( $input, 'hide_theme_footer', $existing['hide_theme_footer'] );
		$output['custom_hide_selectors']       = self::sanitize_css_selector_list( isset( $input['custom_hide_selectors'] ) ? $input['custom_hide_selectors'] : '' );

		return $output;
	}

	/**
	 * Sanitize header fields.
	 *
	 * @param array<string,mixed> $input Input.
	 * @param array<string,mixed> $existing Existing.
	 * @return array<string,mixed>
	 */
	private static function sanitize_header_group( array $input, array $existing ) {
		$output                   = $existing;
		$output['enabled']        = self::bool_from_input( $input, 'enabled', $existing['enabled'] );
		$output['platform_title'] = sanitize_text_field( isset( $input['platform_title'] ) ? $input['platform_title'] : $existing['platform_title'] );
		$output['search']         = self::bool_from_input( $input, 'search', $existing['search'] );
		$output['create']         = self::bool_from_input( $input, 'create', $existing['create'] );
		$output['messages']       = self::bool_from_input( $input, 'messages', $existing['messages'] );
		$output['notifications']  = self::bool_from_input( $input, 'notifications', $existing['notifications'] );
		$output['help']           = self::bool_from_input( $input, 'help', $existing['help'] );
		$output['language']       = self::bool_from_input( $input, 'language', $existing['language'] );
		$output['profile']        = self::bool_from_input( $input, 'profile', $existing['profile'] );

		return $output;
	}

	/**
	 * Sanitize navigation fields.
	 *
	 * @param array<string,mixed> $input Input.
	 * @param array<string,mixed> $existing Existing.
	 * @return array<string,mixed>
	 */
	private static function sanitize_navigation_group( array $input, array $existing ) {
		$output       = $existing;
		$destinations = Defaults::destinations();

		foreach ( $destinations as $key => $destination ) {
			$row = isset( $input[ $key ] ) && is_array( $input[ $key ] ) ? $input[ $key ] : array();
			if ( ! isset( $output[ $key ] ) || ! is_array( $output[ $key ] ) ) {
				$output[ $key ] = array();
			}

			$output[ $key ]['enabled']      = self::bool_from_input( $row, 'enabled', false );
			$output[ $key ]['label']        = sanitize_text_field( isset( $row['label'] ) ? $row['label'] : $destination['label'] );
			$output[ $key ]['page_id']      = absint( isset( $row['page_id'] ) ? $row['page_id'] : 0 );
			$output[ $key ]['shortcode']    = sanitize_key( isset( $row['shortcode'] ) ? $row['shortcode'] : '' );
			$output[ $key ]['slug']         = sanitize_title( isset( $row['slug'] ) ? $row['slug'] : '' );
			$output[ $key ]['url_override'] = self::sanitize_url( isset( $row['url_override'] ) ? $row['url_override'] : '' );
			$output[ $key ]['order']        = self::int_range( $row, 'order', 0, 9999, isset( $destination['order'] ) ? (int) $destination['order'] : 999 );
		}

		return $output;
	}

	/**
	 * Sanitize left sidebar settings.
	 *
	 * @param array<string,mixed> $input Input.
	 * @param array<string,mixed> $existing Existing.
	 * @return array<string,mixed>
	 */
	private static function sanitize_left_sidebar_group( array $input, array $existing ) {
		$output              = $existing;
		$output['enabled']   = self::bool_from_input( $input, 'enabled', $existing['enabled'] );
		$output['groups']    = self::sanitize_bool_map( isset( $input['groups'] ) && is_array( $input['groups'] ) ? $input['groups'] : array(), Defaults::groups() );
		$output['items']     = self::sanitize_bool_map( isset( $input['items'] ) && is_array( $input['items'] ) ? $input['items'] : array(), Defaults::destinations() );
		$footer              = isset( $input['footer_mappings'] ) && is_array( $input['footer_mappings'] ) ? $input['footer_mappings'] : array();
		$output['footer_mappings'] = array(
			'privacy'    => self::sanitize_url( isset( $footer['privacy'] ) ? $footer['privacy'] : '' ),
			'terms'      => self::sanitize_url( isset( $footer['terms'] ) ? $footer['terms'] : '' ),
			'disclaimer' => self::sanitize_url( isset( $footer['disclaimer'] ) ? $footer['disclaimer'] : '' ),
			'guidelines' => self::sanitize_url( isset( $footer['guidelines'] ) ? $footer['guidelines'] : '' ),
			'contact'    => self::sanitize_url( isset( $footer['contact'] ) ? $footer['contact'] : '' ),
			'whatsapp'   => self::sanitize_url( isset( $footer['whatsapp'] ) ? $footer['whatsapp'] : '' ),
		);

		return $output;
	}

	/**
	 * Sanitize right sidebar settings.
	 *
	 * @param array<string,mixed> $input Input.
	 * @param array<string,mixed> $existing Existing.
	 * @return array<string,mixed>
	 */
	private static function sanitize_right_sidebar_group( array $input, array $existing ) {
		$output                      = $existing;
		$output['enabled']           = self::bool_from_input( $input, 'enabled', $existing['enabled'] );
		$output['hide_missing']      = self::bool_from_input( $input, 'hide_missing', $existing['hide_missing'] );
		$output['home_modules']      = self::sanitize_bool_map( isset( $input['home_modules'] ) && is_array( $input['home_modules'] ) ? $input['home_modules'] : array(), $existing['home_modules'] );
		$output['clinic_modules']    = self::sanitize_bool_map( isset( $input['clinic_modules'] ) && is_array( $input['clinic_modules'] ) ? $input['clinic_modules'] : array(), $existing['clinic_modules'] );
		$output['single_modules']    = self::sanitize_bool_map( isset( $input['single_modules'] ) && is_array( $input['single_modules'] ) ? $input['single_modules'] : array(), $existing['single_modules'] );
		$output['announcement']      = sanitize_textarea_field( isset( $input['announcement'] ) ? $input['announcement'] : '' );

		return $output;
	}

	/**
	 * Sanitize mobile settings.
	 *
	 * @param array<string,mixed> $input Input.
	 * @param array<string,mixed> $existing Existing.
	 * @return array<string,mixed>
	 */
	private static function sanitize_mobile_group( array $input, array $existing ) {
		$output                      = $existing;
		$output['bottom_nav']        = false;
		$output['drawers']           = self::bool_from_input( $input, 'drawers', $existing['drawers'] );
		$output['create_or_doctors'] = in_array( isset( $input['create_or_doctors'] ) ? $input['create_or_doctors'] : 'auto', array( 'auto', 'create', 'doctors' ), true ) ? $input['create_or_doctors'] : 'auto';
		$output['menu_label']        = sanitize_text_field( isset( $input['menu_label'] ) ? $input['menu_label'] : $existing['menu_label'] );
		$output['create_label']      = sanitize_text_field( isset( $input['create_label'] ) ? $input['create_label'] : $existing['create_label'] );

		return $output;
	}

	/**
	 * Sanitize integration settings.
	 *
	 * @param array<string,mixed> $input Input.
	 * @param array<string,mixed> $existing Existing.
	 * @return array<string,mixed>
	 */
	private static function sanitize_integrations_group( array $input, array $existing ) {
		$output = $existing;

		foreach ( array( 'messages', 'notifications', 'appointments', 'help', 'whatsapp' ) as $key ) {
			$output['urls'][ $key ] = self::sanitize_url( isset( $input['urls'][ $key ] ) ? $input['urls'][ $key ] : '' );
		}

		return $output;
	}

	/**
	 * Boolean input helper.
	 *
	 * @param array<string,mixed> $input Input.
	 * @param string              $key Key.
	 * @param bool                $fallback Fallback.
	 * @return bool
	 */
	private static function bool_from_input( array $input, $key, $fallback ) {
		if ( ! array_key_exists( $key, $input ) ) {
			return (bool) $fallback;
		}

		return '1' === (string) $input[ $key ] || 1 === $input[ $key ] || true === $input[ $key ];
	}

	/**
	 * Sanitize boolean map.
	 *
	 * @param array<string,mixed> $input Input.
	 * @param array<string,mixed> $known Known keys.
	 * @return array<string,bool>
	 */
	private static function sanitize_bool_map( array $input, array $known ) {
		$output = array();
		foreach ( $known as $key => $value ) {
			$output[ $key ] = self::bool_from_input( $input, $key, false );
		}

		return $output;
	}

	/**
	 * Integer range helper.
	 *
	 * @param array<string,mixed> $input Input.
	 * @param string              $key Key.
	 * @param int                 $min Minimum.
	 * @param int                 $max Maximum.
	 * @param int                 $fallback Fallback.
	 * @return int
	 */
	private static function int_range( array $input, $key, $min, $max, $fallback ) {
		$value = isset( $input[ $key ] ) ? absint( $input[ $key ] ) : (int) $fallback;
		if ( $value < $min || $value > $max ) {
			return (int) $fallback;
		}

		return $value;
	}

	/**
	 * Float range helper.
	 *
	 * @param array<string,mixed> $input Input.
	 * @param string              $key Key.
	 * @param float               $min Minimum.
	 * @param float               $max Maximum.
	 * @param float               $fallback Fallback.
	 * @return float
	 */
	private static function float_range( array $input, $key, $min, $max, $fallback ) {
		$value = isset( $input[ $key ] ) ? (float) $input[ $key ] : (float) $fallback;
		if ( $value < $min || $value > $max ) {
			return (float) $fallback;
		}

		return $value;
	}

	/**
	 * Sanitize IDs submitted as array or comma-separated text.
	 *
	 * @param mixed $value Raw value.
	 * @return array<int>
	 */
	private static function sanitize_id_list( $value ) {
		if ( is_string( $value ) ) {
			$value = preg_split( '/[\s,]+/', $value );
		}
		if ( ! is_array( $value ) ) {
			return array();
		}

		$ids = array_map( 'absint', $value );
		$ids = array_filter( $ids );
		return array_values( array_unique( $ids ) );
	}

	/**
	 * Sanitize per-page overrides submitted as array or lines of "id:mode".
	 *
	 * @param mixed $value Raw value.
	 * @return array<int,string>
	 */
	private static function sanitize_layout_overrides( $value ) {
		$output = array();
		if ( is_string( $value ) ) {
			$lines = preg_split( '/\r\n|\r|\n/', $value );
			foreach ( $lines as $line ) {
				if ( false === strpos( $line, ':' ) ) {
					continue;
				}
				list( $id, $mode ) = array_map( 'trim', explode( ':', $line, 2 ) );
				$id = absint( $id );
				if ( $id && in_array( $mode, array( 'default', 'three', 'two', 'minimal', 'immersive' ), true ) ) {
					$output[ $id ] = $mode;
				}
			}
			return $output;
		}

		if ( is_array( $value ) ) {
			foreach ( $value as $id => $mode ) {
				$id = absint( $id );
				if ( $id && in_array( $mode, array( 'default', 'three', 'two', 'minimal', 'immersive' ), true ) ) {
					$output[ $id ] = $mode;
				}
			}
		}

		return $output;
	}

	/**
	 * Sanitize safe URLs.
	 *
	 * @param string $url Raw URL.
	 * @return string
	 */
	public static function sanitize_url( $url ) {
		$url = trim( (string) $url );
		if ( '' === $url ) {
			return '';
		}

		if ( 0 === strpos( $url, '/' ) && 0 !== strpos( $url, '//' ) ) {
			return esc_url_raw( $url );
		}

		$clean = esc_url_raw( $url, array( 'http', 'https' ) );
		if ( ! $clean || ! wp_http_validate_url( $clean ) ) {
			return '';
		}

		return $clean;
	}

	/**
	 * Sanitize CSS selector list.
	 *
	 * @param string $selectors Raw selectors.
	 * @return string
	 */
	public static function sanitize_css_selector_list( $selectors ) {
		$selectors = trim( (string) $selectors );
		if ( '' === $selectors ) {
			return '';
		}

		$parts = array_map( 'trim', explode( ',', $selectors ) );
		$valid = array();
		foreach ( $parts as $selector ) {
			$selector = self::sanitize_css_selector( $selector );
			if ( '' !== $selector ) {
				$valid[] = $selector;
			}
		}

		return implode( ', ', $valid );
	}

	/**
	 * Sanitize a conservative CSS selector.
	 *
	 * @param string $selector Raw selector.
	 * @return string
	 */
	public static function sanitize_css_selector( $selector ) {
		$selector = trim( (string) $selector );
		if ( '' === $selector ) {
			return '';
		}

		if ( preg_match( '/[{}<>]/', $selector ) ) {
			return '';
		}

		if ( preg_match( '/\b(script|iframe|object|embed|html|body)\b/i', $selector ) ) {
			return '';
		}

		if ( ! preg_match( '/^[#.:\[\]\(\)=\^\$\*~\|\w\s>+,-]+$/', $selector ) ) {
			return '';
		}

		/* Custom hide selectors are a bounded compatibility escape hatch, not a
		 * whole-document styling language. Require a concrete class/id anchor and
		 * reject universal/pseudo-only selectors that could hide the entire shell
		 * or an arbitrarily broad theme subtree. */
		if ( false === strpos( $selector, '.' ) && false === strpos( $selector, '#' ) ) {
			return '';
		}
		if ( preg_match( '/(^|[>+~\s,])\*(?=($|[>+~\s,.:#\[]))/', $selector ) || preg_match( '/(^|[>+~\s,]):(root|not|is|where|has)\b/i', $selector ) ) {
			return '';
		}

		return $selector;
	}

	/**
	 * Sanitize a hex color.
	 *
	 * @param string $color Raw color.
	 * @param string $fallback Fallback.
	 * @return string
	 */
	private static function sanitize_hex_color( $color, $fallback ) {
		$color = trim( (string) $color );
		if ( preg_match( '/^#[0-9a-fA-F]{6}$/', $color ) ) {
			return strtolower( $color );
		}

		return $fallback;
	}
}
