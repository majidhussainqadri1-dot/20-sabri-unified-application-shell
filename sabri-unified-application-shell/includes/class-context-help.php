<?php
/**
 * Native-owner contextual assistance presentation for Master Plan V12.1 CV-033.
 *
 * File20 owns the shell slot, not domain content, support cases or permissions.
 * No private context, draft text or patient information is persisted here.
 *
 * Provider filter: sabri_shell_context_help_contract
 * Provider approval: sabri_shell_context_help_owner_approved (false by default)
 *
 * @package SabriUnifiedApplicationShell
 */
namespace Sabri\UnifiedShell;

defined( 'ABSPATH' ) || exit;

final class ContextHelp {
	const CONTRACT_VERSION = '1.0.0';

	/** Attach one optional semantic HTML help slot in the ordinary public shell. */
	public static function register() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ), 35 );
		add_action( 'wp_body_open', array( __CLASS__, 'render' ), 30 );
	}

	/** A displayed help contract must be evaluated on every request, not cached between users. */
	public static function current() {
		if ( ! in_array( Layout::current_mode(), array( Layout::TWO, Layout::THREE ), true ) ) {
			return array();
		}
		if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return array();
		}
		return self::validate( apply_filters( 'sabri_shell_context_help_contract', array() ) );
	}

	/**
	 * Verify the actual native-owner route and an explicit owner approval.
	 * Only generic public-safe guidance is accepted, including on private pages.
	 * Real authorization of actions and personal data stays with the native owner.
	 */
	public static function validate( $raw ) {
		if ( ! is_array( $raw ) ||
			! isset( $raw['contract_version'], $raw['owner'], $raw['route_url'], $raw['visibility'], $raw['explanation'] ) ||
			! is_string( $raw['contract_version'] ) ||
			! hash_equals( self::CONTRACT_VERSION, $raw['contract_version'] ) ||
			'public-safe-guidance' !== $raw['visibility'] ) {
			return array();
		}
		$owner = sanitize_key( $raw['owner'] );
		$known = array( 'file-00', 'file-02', 'file-03', 'file-05', 'file-06', 'file-07', 'file-08',
			'file-09', 'file-10', 'file-11', 'file-12', 'file-13', 'file-14', 'file-15', 'file-16',
			'file-17', 'file-18', 'file-19', 'file-20', 'file-21', 'file-22', 'file-23', 'file-24',
			'file-25', 'file-26', 'cf-01', 'cf-02', 'cf-03', 'cf-04', 'cf-05', 'cf-06' );
		if ( ! in_array( $owner, $known, true ) ) {
			return array();
		}
		$route = self::same_origin_route( $raw['route_url'] );
		if ( ! $route || ! self::matches_current_route( $route ) ) {
			return array();
		}
		// A registry entry or page slug does not prove a living, authoritative owner.
		if ( true !== apply_filters( 'sabri_shell_context_help_owner_approved', false, $owner, $route ) ) {
			return array();
		}
		if ( ! is_string( $raw['explanation'] ) || '' === trim( $raw['explanation'] ) || strlen( $raw['explanation'] ) > 3600 ) {
			return array();
		}
		$clean = array(
			'owner'       => $owner,
			'route_url'   => $route,
			'explanation' => sanitize_textarea_field( $raw['explanation'] ),
			'example'     => '',
			'glossary'    => array(),
			'support_url' => '',
		);
		if ( isset( $raw['example'] ) && is_string( $raw['example'] ) && strlen( $raw['example'] ) <= 1800 ) {
			$clean['example'] = sanitize_textarea_field( $raw['example'] );
		}
		if ( isset( $raw['glossary'] ) && is_array( $raw['glossary'] ) ) {
			foreach ( array_slice( $raw['glossary'], 0, 8 ) as $entry ) {
				if ( ! is_array( $entry ) || ! isset( $entry['term'], $entry['definition'] ) ||
					! is_string( $entry['term'] ) || ! is_string( $entry['definition'] ) ||
					strlen( $entry['term'] ) > 180 || strlen( $entry['definition'] ) > 850 ) {
					continue;
				}
				$term = sanitize_text_field( $entry['term'] );
				$definition = sanitize_textarea_field( $entry['definition'] );
				if ( '' !== trim( $term ) && '' !== trim( $definition ) ) {
					$clean['glossary'][] = array( 'term' => $term, 'definition' => $definition );
				}
			}
		}
		if ( isset( $raw['support_url'] ) ) {
			$clean['support_url'] = self::same_origin_route( $raw['support_url'] );
		}
		return $clean;
	}

	/** Exact origin, no credentials/query/fragment and no encoded path separators. */
	private static function same_origin_route( $candidate ) {
		if ( ! is_string( $candidate ) || '' === trim( $candidate ) || strlen( $candidate ) > 1500 ) {
			return '';
		}
		$parts = wp_parse_url( $candidate );
		$home = wp_parse_url( home_url( '/' ) );
		if ( ! is_array( $parts ) || ! is_array( $home ) ||
			empty( $parts['scheme'] ) || empty( $parts['host'] ) || empty( $home['scheme'] ) || empty( $home['host'] ) ||
			isset( $parts['user'] ) || isset( $parts['pass'] ) || isset( $parts['query'] ) || isset( $parts['fragment'] ) ) {
			return '';
		}
		$scheme = strtolower( $parts['scheme'] );
		$home_scheme = strtolower( $home['scheme'] );
		$port = isset( $parts['port'] ) ? absint( $parts['port'] ) : ( 'https' === $scheme ? 443 : 80 );
		$home_port = isset( $home['port'] ) ? absint( $home['port'] ) : ( 'https' === $home_scheme ? 443 : 80 );
		$path = isset( $parts['path'] ) ? $parts['path'] : '/';
		if ( ! in_array( $scheme, array( 'https', 'http' ), true ) || $scheme !== $home_scheme ||
			! hash_equals( strtolower( $home['host'] ), strtolower( $parts['host'] ) ) || $port !== $home_port ||
			preg_match( '/%(?:2f|5c|00)/i', $path ) || false !== strpos( $path, '\\' ) ||
			preg_match( '#(?:^|/)\.{1,2}(?:/|$)#', rawurldecode( $path ) ) ) {
			return '';
		}
		$site_path = isset( $home['path'] ) ? '/' . trim( $home['path'], '/' ) : '/';
		$site_path = '/' === $site_path ? '/' : trailingslashit( $site_path );
		if ( '/' !== $site_path && 0 !== strpos( $path, $site_path ) ) {
			return '';
		}
		return esc_url_raw( $candidate, array( 'https', 'http' ) );
	}

	private static function matches_current_route( $candidate ) {
		$current = isset( $_SERVER['REQUEST_URI'] ) && is_string( $_SERVER['REQUEST_URI'] ) ?
			wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		$path = wp_parse_url( $candidate, PHP_URL_PATH );
		$current_path = wp_parse_url( $current, PHP_URL_PATH );
		if ( ! is_string( $path ) || ! is_string( $current_path ) ) {
			return false;
		}
		return '/' . trim( $path, '/' ) === '/' . trim( $current_path, '/' );
	}

	public static function enqueue() {
		if ( ! self::current() ) {
			return;
		}
		wp_enqueue_style( 'sabri-shell-context-help', SABRI_SHELL_URL . 'assets/css/context-help.css', array(), SABRI_SHELL_VERSION );
	}

	/** Native details/summary works without JavaScript and never blocks the task. */
	public static function render() {
		static $shown = false;
		if ( $shown ) {
			return;
		}
		$contract = self::current();
		if ( ! $contract ) {
			return;
		}
		$shown = true;
		echo '<section class="sabri-shell-context-help" data-sabri-shell-component="context-help" data-owner="' . esc_attr( $contract['owner'] ) . '" aria-label="' . esc_attr__( 'Help for this page', 'sabri-unified-application-shell' ) . '">';
		echo '<details><summary>' . esc_html__( 'Help for this page', 'sabri-unified-application-shell' ) . '</summary><div class="sabri-shell-context-help__body">';
		echo '<p>' . esc_html( $contract['explanation'] ) . '</p>';
		if ( '' !== $contract['example'] ) {
			echo '<h3>' . esc_html__( 'Example', 'sabri-unified-application-shell' ) . '</h3><p>' . esc_html( $contract['example'] ) . '</p>';
		}
		if ( $contract['glossary'] ) {
			echo '<h3>' . esc_html__( 'Glossary', 'sabri-unified-application-shell' ) . '</h3><dl>';
			foreach ( $contract['glossary'] as $entry ) {
				echo '<dt>' . esc_html( $entry['term'] ) . '</dt><dd>' . esc_html( $entry['definition'] ) . '</dd>';
			}
			echo '</dl>';
		}
		if ( $contract['support_url'] ) {
			echo '<p><a href="' . esc_url( $contract['support_url'] ) . '">' . esc_html__( 'Contact support', 'sabri-unified-application-shell' ) . '</a></p>';
		}
		echo '</div></details></section>';
	}
}
