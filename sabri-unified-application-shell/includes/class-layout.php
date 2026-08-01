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

/**
 * Resolves three-column, two-column, and minimal modes.
 */
final class Layout {
	const THREE   = 'three';
	const TWO     = 'two';
	const MINIMAL = 'minimal';

	/**
	 * Conservative content-level selectors. Site/root wrappers are excluded.
	 *
	 * @return array<int,string>
	 */
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

	/**
	 * Return configured selector followed by safe fallbacks.
	 *
	 * @param array<string,mixed>|null $settings Settings.
	 * @return array<int,string>
	 */
	public static function content_target_candidates( $settings = null ) {
		$settings   = null === $settings ? Settings::get() : $settings;
		$candidates = array();
		if ( ! empty( $settings['layout']['theme_content_selector'] ) ) {
			$candidates[] = $settings['layout']['theme_content_selector'];
		}
		return array_values( array_unique( array_merge( $candidates, self::content_target_fallbacks() ) ) );
	}

	/**
	 * Human-readable target resolver summary for System Check.
	 *
	 * @param array<string,mixed>|null $settings Settings.
	 * @return string
	 */
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

	/**
	 * Resolve the current request layout.
	 *
	 * @return string
	 */
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
			if ( 'default' !== $override && in_array( $override, array( self::THREE, self::TWO, self::MINIMAL ), true ) ) {
				return apply_filters( 'sabri_shell_layout_mode', $override, $settings );
			}
		}
		if ( self::is_three_column_context( $settings, $page_id ) ) {
			return apply_filters( 'sabri_shell_layout_mode', self::THREE, $settings );
		}
		return apply_filters( 'sabri_shell_layout_mode', self::TWO, $settings );
	}

	/**
	 * Whether the right sidebar may render.
	 *
	 * @return bool
	 */
	public static function right_sidebar_allowed() {
		$settings = Settings::get();
		return self::THREE === self::current_mode() && ! empty( $settings['right_sidebar']['enabled'] );
	}

	/**
	 * Determine three-column contexts.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @param int                 $page_id Current page ID.
	 * @return bool
	 */
	public static function is_three_column_context( array $settings, $page_id = 0 ) {
		if ( function_exists( 'is_front_page' ) && is_front_page() ) {
			return true;
		}

		$clinic_page_id = ! empty( $settings['layout']['worldwide_clinic_page_id'] ) ? absint( $settings['layout']['worldwide_clinic_page_id'] ) : Integrations::page_id( 'clinic' );
		if ( $clinic_page_id && $page_id && $clinic_page_id === $page_id ) {
			return true;
		}

		$profile_page_id = Integrations::page_id( 'profile' );
		if ( $profile_page_id && $page_id === $profile_page_id && ! empty( $_GET['user'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only public routing.
			return true;
		}

		$post_types = array_filter(
			array_unique(
				array_merge(
					array( isset( $settings['layout']['clinic_post_type'] ) ? sanitize_key( $settings['layout']['clinic_post_type'] ) : '' ),
					(array) Integrations::detect()['clinic_post_types']
				)
			)
		);
		foreach ( $post_types as $post_type ) {
			if ( function_exists( 'is_singular' ) && is_singular( $post_type ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Determine if the request must be excluded.
	 *
	 * @return bool
	 */
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
		$auth_slugs = array(
			'wp-login.php', 'login', 'signup', 'register', 'lostpassword', 'password-reset', 'account-verification',
			'account-login', 'create-account', 'complete-profile', 'forgot-password', 'account-access-required',
			'verification', 'verify-email', 'security-center', 'safe-mode', 'maintenance',
		);
		$segments = array_values( array_filter( explode( '/', trim( strtolower( $path ), '/' ) ) ) );
		if ( array_intersect( $segments, $auth_slugs ) ) {
			return true;
		}
		if ( preg_match( '#/(wp-json|feed|robots\.txt|sitemap[^/]*)#i', $path ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Return current public page ID when available.
	 *
	 * @return int
	 */
	public static function current_page_id() {
		return function_exists( 'get_queried_object_id' ) ? absint( get_queried_object_id() ) : 0;
	}
}
