<?php
/**
 * Pre-boot client context for Future Shell v5 hardening.
 *
 * @package SabriUnifiedApplicationShell
 */

namespace Sabri\UnifiedShell;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Publishes privacy and scope facts before future-shell-v5.js executes.
 */
final class FutureShellV5ClientContext {
	/** Register the pre-boot context after the main Future Shell enqueue. */
	public static function register() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_context' ), 132 );
	}

	/** @return array<int,string> */
	private static function private_paths() {
		$settings = FutureShellV5::settings();
		$paths = array_merge(
			array( '/messages', '/network', '/appointments', '/security', '/verification', '/account', '/wp-admin', '/wp-login.php', '/wp-json' ),
			isset( $settings['private_path_fragments'] ) && is_array( $settings['private_path_fragments'] ) ? $settings['private_path_fragments'] : array()
		);
		$out = array();
		foreach ( $paths as $path ) {
			if ( ! is_string( $path ) ) { continue; }
			$path = wp_parse_url( $path, PHP_URL_PATH );
			if ( ! is_string( $path ) || '' === $path ) { continue; }
			$path = untrailingslashit( '/' . ltrim( $path, '/' ) );
			if ( '' !== $path && strlen( $path ) <= 160 ) { $out[ $path ] = $path; }
		}
		return array_values( array_slice( $out, 0, 64, true ) );
	}

	/** @return string */
	private static function scope_path() {
		$path = wp_parse_url( home_url( '/' ), PHP_URL_PATH );
		$path = is_string( $path ) && '' !== $path ? $path : '/';
		return trailingslashit( '/' . ltrim( $path, '/' ) );
	}

	/**
	 * Conservative public-route classification for browser-local history.
	 *
	 * @return bool
	 */
	private static function current_route_public() {
		if ( is_admin() || wp_doing_ajax() || Layout::MINIMAL === Layout::current_mode() || is_404() || is_preview() || ! empty( $_GET ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only route classification.
			return false;
		}
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- path only.
		$path = wp_parse_url( $request_uri, PHP_URL_PATH );
		$path = is_string( $path ) ? '/' . ltrim( $path, '/' ) : '/';
		foreach ( self::private_paths() as $private ) {
			if ( $path === $private || 0 === strpos( $path, trailingslashit( $private ) ) ) { return false; }
		}
		if ( is_singular() ) {
			$post = get_queried_object();
			if ( ! $post instanceof \WP_Post || 'publish' !== get_post_status( $post ) ) { return false; }
			$type = get_post_type_object( $post->post_type );
			return $type && ! empty( $type->publicly_queryable );
		}
		return is_front_page() || is_home() || is_archive();
	}

	/**
	 * Add a separate pre-boot object so ordering with wp_localize_script remains deterministic.
	 *
	 * @return void
	 */
	public static function enqueue_context() {
		if ( ! wp_script_is( 'sabri-shell-future-v5', 'enqueued' ) ) { return; }
		$payload = array(
			'hardeningVersion'   => FutureShellV5Hardening::CONTRACT_VERSION,
			'currentRoutePublic' => self::current_route_public(),
			'swScope'            => self::scope_path(),
			'privatePaths'       => self::private_paths(),
			'recentsVersion'     => FutureShellV5Hardening::RECENTS_VERSION,
		);
		wp_add_inline_script(
			'sabri-shell-future-v5',
			'window.SabriShellFutureV5Hardening=' . wp_json_encode( $payload ) . ';',
			'before'
		);
	}
}
