<?php
/**
 * Public layout resolver.
 *
 * @package SabriUnifiedApplicationShell
 */

namespace Sabri\UnifiedShell;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Resolves the four central-plan layout modes. */
final class Layout {
	const THREE     = 'three';
	const TWO       = 'two';
	const MINIMAL   = 'minimal';
	const IMMERSIVE = 'immersive';

	/** Conservative content-level selectors. */
	public static function content_target_fallbacks() {
		return array(
			'main.site-main',
			'main#main',
			'.site-main',
			'#primary',
			'.content-area',
			'main',
			'#content',
			'.site-content',
		);
	}

	/** Return configured selector followed by safe fallbacks. */
	public static function content_target_candidates( $settings = null ) {
		$settings   = null === $settings ? Settings::get() : $settings;
		$candidates = array();
		if ( ! empty( $settings['layout']['theme_content_selector'] ) ) {
			$candidates[] = $settings['layout']['theme_content_selector'];
		}
		return array_values( array_unique( array_merge( $candidates, self::content_target_fallbacks() ) ) );
	}

	/** Human-readable target resolver summary for System Check. */
	public static function content_target_report( $settings = null ) {
		$settings = null === $settings ? Settings::get() : $settings;
		if ( ! empty( $settings['layout']['theme_content_selector'] ) ) {
			return sprintf(
				/* translators: %s is a CSS selector. */
				__( 'Configured content-level selector preferred: %s', 'sabri-unified-application-shell' ),
				$settings['layout']['theme_content_selector']
			);
		}
		return sprintf(
			/* translators: %s is a comma-separated selector list. */
			__( 'Safe annotation fallback order: %s', 'sabri-unified-application-shell' ),
			implode( ', ', self::content_target_fallbacks() )
		);
	}

	/** Resolve the current request layout. */
	public static function current_mode() {
		$settings = Settings::get();
		if ( empty( $settings['enabled'] ) || SafeMode::disabled() || self::is_excluded_request() ) {
			return self::MINIMAL;
		}

		$page_id = self::current_page_id();
		if ( $page_id && in_array( $page_id, (array) $settings['layout']['excluded_page_ids'], true ) ) {
			return self::MINIMAL;
		}

		if ( $page_id && ! empty( $settings['layout']['per_page_overrides'][ $page_id ] ) ) {
			$override = $settings['layout']['per_page_overrides'][ $page_id ];
			if ( 'default' !== $override && in_array( $override, self::modes(), true ) ) {
				return self::filter_mode( $override, $settings );
			}
		}

		if ( self::is_immersive_context( $settings, $page_id ) ) {
			return self::filter_mode( self::IMMERSIVE, $settings );
		}
		if ( self::is_three_column_context( $settings, $page_id ) ) {
			return self::filter_mode( self::THREE, $settings );
		}
		return self::filter_mode( self::TWO, $settings );
	}

	/** Return valid layout modes. */
	public static function modes() {
		return array( self::THREE, self::TWO, self::MINIMAL, self::IMMERSIVE );
	}

	/** Apply the public mode filter without accepting unknown values. */
	private static function filter_mode( $mode, array $settings ) {
		$filtered = apply_filters( 'sabri_shell_layout_mode', $mode, $settings );
		return in_array( $filtered, self::modes(), true ) ? $filtered : $mode;
	}

	/** Whether the right sidebar may render. */
	public static function right_sidebar_allowed() {
		$settings = Settings::get();
		return self::THREE === self::current_mode() && ! empty( $settings['right_sidebar']['enabled'] );
	}

	/** Determine exact three-column contexts. */
	public static function is_three_column_context( array $settings, $page_id = 0 ) {
		if ( function_exists( 'is_front_page' ) && is_front_page() ) {
			return true;
		}

		$clinic_page_id = ! empty( $settings['layout']['worldwide_clinic_page_id'] )
			? absint( $settings['layout']['worldwide_clinic_page_id'] )
			: Integrations::page_id( 'clinic' );
		if ( $clinic_page_id && $page_id && $clinic_page_id === $page_id ) {
			return true;
		}

		$detected = Integrations::detect();
		$post_types = array_filter(
			array_unique(
				array_merge(
					array( isset( $settings['layout']['clinic_post_type'] ) ? sanitize_key( $settings['layout']['clinic_post_type'] ) : '' ),
					isset( $detected['clinic_post_types'] ) ? (array) $detected['clinic_post_types'] : array()
				)
			)
		);
		foreach ( $post_types as $post_type ) {
			if ( function_exists( 'is_singular' ) && is_singular( $post_type ) ) {
				return true;
			}
		}
		return (bool) apply_filters( 'sabri_shell_is_three_column_context', false, $settings, $page_id );
	}

	/** Determine video, live, Reel and PDF-reader immersive contexts. */
	public static function is_immersive_context( array $settings, $page_id = 0 ) {
		$filtered = apply_filters( 'sabri_shell_is_immersive_context', null, $settings, $page_id );
		if ( is_bool( $filtered ) ) {
			return $filtered;
		}

		foreach ( array( 'sabri_immersive', 'immersive', 'reader', 'full_screen' ) as $query_key ) {
			if ( ! empty( $_GET[ $query_key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only display context.
				return true;
			}
		}

		$request = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		$path    = strtolower( (string) wp_parse_url( $request, PHP_URL_PATH ) );
		if ( preg_match( '#/(reels?|video-wall/(watch|live)|live-broadcast|pdf-reader|read-pdf)(/|$)#', $path ) ) {
			return true;
		}

		foreach ( array( 'srl_reel', 'svw_video', 'svw_live', 'spl_reader' ) as $post_type ) {
			if ( function_exists( 'is_singular' ) && post_type_exists( $post_type ) && is_singular( $post_type ) ) {
				return true;
			}
		}
		return false;
	}

	/** Determine if the request must use Minimal/no visual shell. */
	public static function is_excluded_request() {
		if ( is_admin() || ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) || ( function_exists( 'wp_doing_cron' ) && wp_doing_cron() ) ) {
			return true;
		}
		if ( ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) ) {
			return true;
		}
		foreach ( array( 'is_feed', 'is_embed', 'is_preview', 'is_customize_preview', 'is_robots', 'is_favicon', 'is_trackback' ) as $function ) {
			if ( function_exists( $function ) && $function() ) {
				return true;
			}
		}
		if ( function_exists( 'is_singular' ) && is_singular() && function_exists( 'post_password_required' ) && post_password_required() ) {
			return true;
		}
		if ( ! empty( $_GET['print'] ) || ! empty( $_GET['sabri_shell_maintenance'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only display mode.
			return true;
		}

		$request = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		$path    = (string) wp_parse_url( $request, PHP_URL_PATH );
		$task_slugs = array(
			'wp-login.php', 'login', 'signup', 'register', 'lostpassword', 'password-reset', 'account-verification',
			'account-login', 'create-account', 'complete-profile', 'forgot-password', 'account-access-required',
			'verification', 'verify-email', 'doctor-verification', 'security-center',
			'safe-mode', 'repair', 'maintenance',
		);
		$segments = array_values( array_filter( explode( '/', trim( strtolower( $path ), '/' ) ) ) );
		if ( array_intersect( $segments, $task_slugs ) ) {
			return true;
		}
		if ( preg_match( '#/(wp-json|feed|robots\.txt|sitemap[^/]*)#i', $path ) ) {
			return true;
		}
		return false;
	}

	/** Return current public page ID when available. */
	public static function current_page_id() {
		return function_exists( 'get_queried_object_id' ) ? absint( get_queried_object_id() ) : 0;
	}
}
