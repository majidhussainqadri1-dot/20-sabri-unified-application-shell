<?php
/**
 * V12.1 public security guidance: no invented pages, no duplicated nav,
 * no cached proof of an active native owner.
 * File02 owns account-safety rewrite; File24 owns the public shortcode.
 */
namespace Sabri\UnifiedShell;
defined( 'ABSPATH' ) || exit;

final class SecurityNavigation {
	/** Accept exact site-origin, public, clean URLs; never a tokenized action URL. */
	public static function public_url( $url ) {
		if ( ! is_string( $url ) || '' === trim( $url ) || strlen( $url ) > 1500 ) { return ''; }
		$t = wp_parse_url( $url );
		$s = wp_parse_url( home_url( '/' ) );
		if ( ! is_array( $t ) || ! is_array( $s ) ||
			empty( $t['scheme'] ) || empty( $t['host'] ) ||
			empty( $s['scheme'] ) || empty( $s['host'] ) ||
			isset( $t['user'] ) || isset( $t['pass'] ) ||
			isset( $t['query'] ) || isset( $t['fragment'] ) ) { return ''; }
		$scheme = strtolower( $t['scheme'] );
		$site_scheme = strtolower( $s['scheme'] );
		$port = isset( $t['port'] ) ? absint( $t['port'] ) : ( 'https' === $scheme ? 443 : 80 );
		$site_port = isset( $s['port'] ) ? absint( $s['port'] ) : ( 'https' === $site_scheme ? 443 : 80 );
		$path = isset( $t['path'] ) ? $t['path'] : '/';
		if ( ! in_array( $scheme, array( 'http', 'https' ), true ) ||
			$scheme !== $site_scheme ||
			! hash_equals( strtolower( $s['host'] ), strtolower( $t['host'] ) ) ||
			$port !== $site_port || ! is_string( $path ) ||
			preg_match( '/%(?:2f|5c|00|25)/i', $path ) ||
			false !== strpos( $path, '\\' ) ||
			preg_match( '#(?:^|/)\.{1,2}(?:/|$)#', rawurldecode( $path ) ) ) { return ''; }
		$site_path = isset( $s['path'] ) ? '/' . trim( $s['path'], '/' ) : '/';
		$site_path = '/' === $site_path ? '/' : trailingslashit( $site_path );
		if ( '/' !== $site_path && 0 !== strpos( $path, $site_path ) ) { return ''; }
		return esc_url_raw( $url, array( 'http', 'https' ) );
	}

	/** File02 safety has an owner-registered rewrite, not necessarily a WP Page. */
	private static function file02_url() {
		if ( ! defined( 'SAUTH_VERSION' ) ||
			! class_exists( 'SAUTH_Canonical_Routes', false ) ||
			! class_exists( 'SAUTH_Account_Safety', false ) ||
			! method_exists( 'SAUTH_Canonical_Routes', 'render_safety' ) ||
			! method_exists( 'SAUTH_Account_Safety', 'render' ) ||
			! function_exists( 'has_action' ) || ! function_exists( 'has_filter' ) ||
			! function_exists( 'shortcode_exists' ) ||
			! shortcode_exists( 'sabri_auth_account_safety' ) ||
			false === has_action( 'template_redirect', array( 'SAUTH_Canonical_Routes', 'render_safety' ) ) ||
			false === has_filter( 'sauth_canonical_route_url', array( 'SAUTH_Canonical_Routes', 'route_url' ) ) ||
			'1' !== (string) get_option( 'sauth_safety_route_ready', '' ) ||
			(string) SAUTH_VERSION !== (string) get_option( 'sauth_rewrite_version', '' ) ) { return ''; }
		$url = apply_filters( 'sauth_canonical_route_url', '', 'safety' );
		$canonical = home_url( '/account/safety/' );
		return is_string( $url ) && $url === $canonical ? self::public_url( $url ) : '';
	}

	/** File24 must own a real public shortcode on a unique published WordPress Page. */
	private static function file24_url() {
		$class = 'Sabri\\Platform\\Security\\Guidance\\SecurityGuidance';
		if ( ! class_exists( $class, false ) || ! function_exists( 'shortcode_exists' ) ||
			! shortcode_exists( 'sabri_security_guidance' ) ) { return ''; }
		global $shortcode_tags;
		$handler = isset( $shortcode_tags['sabri_security_guidance'] ) ? $shortcode_tags['sabri_security_guidance'] : null;
		if ( ! is_array( $handler ) || ! isset( $handler[0], $handler[1] ) ||
			! is_object( $handler[0] ) || ! is_a( $handler[0], $class ) ||
			'renderPublic' !== $handler[1] ) { return ''; }
		// The existing File20 resolver enforces published-page-only, bounded scans,
		// unique shortcode destination and rejects collisions/foreign domains.
		return self::public_url( Integrations::find_page_by_shortcodes( array( 'sabri_security_guidance' ) ) );
	}

	/** Per-request projection; never treat File20's cached nav as owner-health truth. */
	public static function augment( array $items ) {
		$candidates = array(
			'account_safety' => array( 'label' => __( 'Account Safety', 'sabri-unified-application-shell' ), 'owner' => 'file-02', 'url' => self::file02_url(), 'order' => 235 ),
			'security_guidance' => array( 'label' => __( 'Security Guidance', 'sabri-unified-application-shell' ), 'owner' => 'file-24', 'url' => self::file24_url(), 'order' => 236 ),
		);
		$existing = array();
		foreach ( $items as $item ) {
			$id = self::identity( isset( $item['url'] ) ? $item['url'] : '' );
			if ( $id ) { $existing[ $id ] = true; }
		}
		$counts = array();
		foreach ( $candidates as $candidate ) {
			$id = self::identity( $candidate['url'] );
			if ( $id ) { $counts[ $id ] = isset( $counts[ $id ] ) ? $counts[ $id ] + 1 : 1; }
		}
		foreach ( $candidates as $key => $candidate ) {
			$id = self::identity( $candidate['url'] );
			if ( ! $id || isset( $items[ $key ] ) || isset( $existing[ $id ] ) ||
				$counts[ $id ] !== 1 ) { continue; }
			$items[ $key ] = array(
				'key' => $key, 'owner' => $candidate['owner'],
				'label' => $candidate['label'], 'url' => $candidate['url'],
				'reason' => 'validated_native_owner_public_route', 'source_priority' => 1,
				'group' => 'platform', 'visibility' => 'public', 'order' => $candidate['order'],
			);
		}
		return $items;
	}

	private static function identity( $url ) {
		$url = self::public_url( $url );
		if ( ! $url ) { return ''; }
		$parts = wp_parse_url( $url );
		$path = isset( $parts['path'] ) ? $parts['path'] : '/';
		$scheme = strtolower( $parts['scheme'] );
		$port = isset( $parts['port'] ) ? absint( $parts['port'] ) : ( 'https' === $scheme ? 443 : 80 );
		return $scheme . '://' . strtolower( $parts['host'] ) . ':' . $port . ( '/' === $path ? '/' : rtrim( $path, '/' ) );
	}
}
