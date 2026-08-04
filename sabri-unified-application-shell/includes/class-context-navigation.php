<?php
/**
 * Shared contextual Back and Home navigation.
 *
 * @package SabriUnifiedApplicationShell
 */

namespace Sabri\UnifiedShell;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provides safe, RTL-first contextual navigation on internal public pages.
 */
final class ContextNavigation {
	/**
	 * Once-only render guard.
	 *
	 * @var bool
	 */
	private static $rendered = false;

	/**
	 * Register public hooks.
	 *
	 * @return void
	 */
	public static function register() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ), 30 );
		add_action( 'wp_body_open', array( __CLASS__, 'render' ), 25 );
	}

	/**
	 * Enqueue isolated assets only where the controls can render.
	 *
	 * @return void
	 */
	public static function enqueue() {
		if ( ! self::should_render() ) {
			return;
		}

		wp_enqueue_style(
			'sabri-shell-context-navigation',
			SABRI_SHELL_URL . 'assets/css/context-navigation.css',
			array(),
			SABRI_SHELL_VERSION
		);

		wp_enqueue_script(
			'sabri-shell-context-navigation',
			SABRI_SHELL_URL . 'assets/js/context-navigation.js',
			array(),
			SABRI_SHELL_VERSION,
			true
		);
	}

	/**
	 * Render the shared controls before theme-owned page content.
	 *
	 * @return void
	 */
	public static function render() {
		if ( self::$rendered || ! self::should_render() ) {
			return;
		}
		self::$rendered = true;

		$home_url     = home_url( '/' );
		$fallback_url = self::fallback_url( $home_url );
		$back_label   = __( 'Back', 'sabri-unified-application-shell' );
		$home_label   = __( 'Home', 'sabri-unified-application-shell' );

		echo '<nav class="sabri-context-navigation" data-sabri-context-navigation aria-label="' . esc_attr__( 'Page navigation', 'sabri-unified-application-shell' ) . '">';
		echo '<div class="sabri-context-navigation__inner">';
		echo '<a class="sabri-context-navigation__control sabri-context-navigation__back" data-sabri-context-back data-fallback-url="' . esc_url( $fallback_url ) . '" data-home-url="' . esc_url( $home_url ) . '" href="' . esc_url( $fallback_url ) . '" aria-label="' . esc_attr( $back_label ) . '">';
		echo '<span class="sabri-context-navigation__icon sabri-context-navigation__back-icon" aria-hidden="true">&#8594;</span>';
		echo '<span class="sabri-context-navigation__label">' . esc_html( $back_label ) . '</span>';
		echo '</a>';
		echo '<a class="sabri-context-navigation__control sabri-context-navigation__home" href="' . esc_url( $home_url ) . '" aria-label="' . esc_attr( $home_label ) . '">';
		echo '<span class="sabri-context-navigation__icon" aria-hidden="true">&#8962;</span>';
		echo '<span class="sabri-context-navigation__label">' . esc_html( $home_label ) . '</span>';
		echo '</a>';
		echo '</div>';
		echo '</nav>';
	}

	/**
	 * Decide whether the current request is an internal public page.
	 *
	 * @return bool
	 */
	private static function should_render() {
		if ( is_admin() || wp_doing_ajax() || is_front_page() ) {
			return false;
		}

		/**
		 * Filter whether File 20 renders the shared Back + Home controls.
		 *
		 * Native modules may disable the component only for a documented context
		 * such as a true full-screen player that supplies an equivalent control.
		 *
		 * @param bool $enabled Whether the component is enabled.
		 */
		return (bool) apply_filters( 'sabri_shell_context_navigation_enabled', true );
	}

	/**
	 * Resolve a bounded same-origin fallback.
	 *
	 * Detail URLs default to their first canonical section route; section indexes
	 * and unknown routes default to Home. Native modules may narrow this through
	 * the documented filter, but cross-origin values are discarded.
	 *
	 * @param string $home_url Canonical Home URL.
	 * @return string
	 */
	private static function fallback_url( $home_url ) {
		$default = self::section_fallback_url( $home_url );

		/**
		 * Filter the contextual Back fallback URL.
		 *
		 * @param string $default  Default section or Home fallback.
		 * @param string $home_url Canonical Home URL.
		 */
		$filtered = apply_filters( 'sabri_shell_context_navigation_fallback_url', $default, $home_url );

		return self::same_origin_url( $filtered, $home_url );
	}

	/**
	 * Build a section-index fallback for nested routes.
	 *
	 * @param string $home_url Canonical Home URL.
	 * @return string
	 */
	private static function section_fallback_url( $home_url ) {
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- parsed as URL path and sanitized below.
		$path        = wp_parse_url( $request_uri, PHP_URL_PATH );
		$home_path   = wp_parse_url( $home_url, PHP_URL_PATH );

		if ( ! is_string( $path ) || '' === $path ) {
			return $home_url;
		}

		$home_path = is_string( $home_path ) ? trailingslashit( $home_path ) : '/';
		if ( '/' !== $home_path && 0 === strpos( trailingslashit( $path ), $home_path ) ) {
			$path = substr( $path, strlen( untrailingslashit( $home_path ) ) );
		}

		$segments = array_values( array_filter( explode( '/', trim( $path, '/' ) ) ) );
		if ( count( $segments ) < 2 ) {
			return $home_url;
		}

		$section = sanitize_title( rawurldecode( $segments[0] ) );
		if ( '' === $section ) {
			return $home_url;
		}

		return home_url( '/' . $section . '/' );
	}

	/**
	 * Accept only HTTP(S) URLs with the canonical Home origin.
	 *
	 * @param mixed  $candidate Candidate URL.
	 * @param string $fallback  Safe fallback URL.
	 * @return string
	 */
	private static function same_origin_url( $candidate, $fallback ) {
		if ( ! is_string( $candidate ) || '' === $candidate ) {
			return $fallback;
		}

		if ( 0 === strpos( $candidate, '/' ) && 0 !== strpos( $candidate, '//' ) ) {
			$candidate = home_url( $candidate );
		}

		$candidate_parts = wp_parse_url( $candidate );
		$home_parts      = wp_parse_url( $fallback );
		if ( ! is_array( $candidate_parts ) || ! is_array( $home_parts ) ) {
			return $fallback;
		}

		$scheme      = isset( $candidate_parts['scheme'] ) ? strtolower( $candidate_parts['scheme'] ) : '';
		$home_scheme = isset( $home_parts['scheme'] ) ? strtolower( $home_parts['scheme'] ) : '';

		if (
			empty( $candidate_parts['host'] ) ||
			empty( $home_parts['host'] ) ||
			! in_array( $scheme, array( 'http', 'https' ), true ) ||
			$scheme !== $home_scheme ||
			strtolower( $candidate_parts['host'] ) !== strtolower( $home_parts['host'] ) ||
			self::normalized_port( $candidate_parts ) !== self::normalized_port( $home_parts )
		) {
			return $fallback;
		}

		return $candidate;
	}

	/**
	 * Normalize explicit/default URL ports for origin comparison.
	 *
	 * @param array<string,mixed> $parts Parsed URL parts.
	 * @return int
	 */
	private static function normalized_port( array $parts ) {
		if ( isset( $parts['port'] ) ) {
			return (int) $parts['port'];
		}

		return isset( $parts['scheme'] ) && 'https' === strtolower( $parts['scheme'] ) ? 443 : 80;
	}
}
