<?php
/**
 * CV-034/035: authenticated, owner-verified ENTRY links to native workspaces.
 * NO chat/draft/patient/document metadata, localStorage, secondary data table,
 * per-entity title, user relationship or private content is created in File20.
 *
 * Filter contract: sabri_shell_workspace_entries_contract
 * Owner attestation: sabri_shell_workspace_entry_owner_approved (false by default)
 * @package SabriUnifiedApplicationShell
 */
namespace Sabri\UnifiedShell;

defined( 'ABSPATH' ) || exit;

final class WorkspaceEntries {
	const VERSION = '1.0.0';
	const MAX_ITEMS = 10;

	public static function register() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ), 36 );
		add_action( 'wp_body_open', array( __CLASS__, 'render' ), 31 );
	}

	/** Never use local storage or a cross-principal WP transient for private shortcuts. */
	public static function current() {
		if ( ! in_array( Layout::current_mode(), array( Layout::TWO, Layout::THREE ), true ) ||
			! is_user_logged_in() || ! get_current_user_id() ) {
			return array();
		}
		$provided = apply_filters( 'sabri_shell_workspace_entries_contract', array() );
		return self::validate_for_principal( $provided, (int) get_current_user_id(), true );
	}

	/** Only generic hub links, not personal history objects or saved record data. */
	public static function validate_for_principal( $contract, $user_id, $authenticated ) {
		$user_id = absint( $user_id );
		if ( ! $authenticated || 0 === $user_id || ! is_array( $contract ) ||
			! isset( $contract['version'], $contract['principal_id'], $contract['items'] ) ||
			! is_string( $contract['version'] ) || ! hash_equals( self::VERSION, $contract['version'] ) ||
			! is_int( $contract['principal_id'] ) || $contract['principal_id'] !== $user_id ||
			! is_array( $contract['items'] ) || count( $contract['items'] ) > self::MAX_ITEMS ) {
			return array();
		}
		$allowed_owners = array( 'file-03', 'file-05', 'file-06', 'file-08', 'file-12',
			'file-17', 'file-21', 'file-22', 'file-23', 'cf-01', 'cf-02' );
		$output = array( 'recent' => array(), 'saved' => array() );
		$seen = array();
		foreach ( $contract['items'] as $item ) {
			if ( ! is_array( $item ) || ! isset( $item['kind'], $item['owner'], $item['label'], $item['url'], $item['intent'] ) ||
				! is_string( $item['kind'] ) || ! in_array( $item['kind'], array( 'recent', 'saved' ), true ) ||
				'entry-only' !== $item['intent'] || ! is_string( $item['owner'] ) || ! is_string( $item['label'] ) ) {
				continue;
			}
			$kind = $item['kind'];
			$owner = sanitize_key( $item['owner'] );
			$label = sanitize_text_field( $item['label'] );
			if ( ! in_array( $owner, $allowed_owners, true ) ||
				'' === $label || strlen( $label ) > 180 ) {
				continue;
			}
			$url = self::safe_owner_hub_url( $item['url'] );
			$key = $kind . '|' . $owner . '|' . $url;
			if ( '' === $url || isset( $seen[ $key ] ) ||
				true !== apply_filters( 'sabri_shell_workspace_entry_owner_approved', false, $owner, $kind, $url, $user_id ) ) {
				continue;
			}
			$seen[ $key ] = true;
			$output[ $kind ][] = array( 'owner' => $owner, 'label' => $label, 'url' => $url );
		}
		return $output;
	}

	/** Exact site origin and subdirectory, no query, fragment, credentials or disguised traversal. */
	private static function safe_owner_hub_url( $url ) {
		if ( ! is_string( $url ) || '' === trim( $url ) || strlen( $url ) > 1500 ) {
			return '';
		}
		$target = wp_parse_url( $url );
		$home = wp_parse_url( home_url( '/' ) );
		if ( ! is_array( $target ) || ! is_array( $home ) ||
			empty( $target['scheme'] ) || empty( $target['host'] ) ||
			empty( $home['scheme'] ) || empty( $home['host'] ) ||
			isset( $target['user'] ) || isset( $target['pass'] ) ||
			isset( $target['query'] ) || isset( $target['fragment'] ) ) {
			return '';
		}
		$s = strtolower( $target['scheme'] );
		$hs = strtolower( $home['scheme'] );
		$port = isset( $target['port'] ) ? absint( $target['port'] ) : ( 'https' === $s ? 443 : 80 );
		$hp = isset( $home['port'] ) ? absint( $home['port'] ) : ( 'https' === $hs ? 443 : 80 );
		$path = isset( $target['path'] ) ? $target['path'] : '/';
		$decoded = rawurldecode( $path );
		if ( ! in_array( $s, array( 'http', 'https' ), true ) || $hs !== $s ||
			! hash_equals( strtolower( $home['host'] ), strtolower( $target['host'] ) ) ||
			$hp !== $port || preg_match( '/%(?:2f|5c|00)/i', $path ) ||
			false !== strpos( $decoded, '\\' ) ||
			preg_match( '#(?:^|/)\.{1,2}(?:/|$)#', $decoded ) ) {
			return '';
		}
		$site_path = isset( $home['path'] ) ? '/' . trim( $home['path'], '/' ) : '/';
		$site_path = '/' === $site_path ? '/' : trailingslashit( $site_path );
		if ( '/' !== $site_path && 0 !== strpos( $path, $site_path ) ) {
			return '';
		}
		return esc_url_raw( $url, array( 'https', 'http' ) );
	}

	/** Tiny global shell style, available before late owner-supplied contracts. */
	public static function enqueue() {
		if ( ! in_array( Layout::current_mode(), array( Layout::TWO, Layout::THREE ), true ) ) {
			return;
		}
		wp_enqueue_style( 'sabri-shell-workspace-entries', SABRI_SHELL_URL . 'assets/css/workspace-entries.css', array(), SABRI_SHELL_VERSION );
	}

	/** Only native hub links are displayed; no private record titles in HTML. */
	public static function render() {
		static $rendered = false;
		if ( $rendered ) {
			return;
		}
		$entries = self::current();
		if ( ! $entries || ( ! $entries['recent'] && ! $entries['saved'] ) ) {
			return;
		}
		$rendered = true;
		echo '<nav class="sabri-shell-workspace-entries" data-sabri-shell-component="workspace-entries" aria-label="' . esc_attr__( 'Your workspaces', 'sabri-unified-application-shell' ) . '">';
		foreach ( array( 'recent' => __( 'Recent workspaces', 'sabri-unified-application-shell' ),
			'saved' => __( 'Saved workspaces', 'sabri-unified-application-shell' ) ) as $kind => $caption ) {
			if ( ! $entries[ $kind ] ) { continue; }
			echo '<details><summary>' . esc_html( $caption ) . '</summary><ul>';
			foreach ( $entries[ $kind ] as $item ) {
				echo '<li><a href="' . esc_url( $item['url'] ) . '" rel="nofollow">' . esc_html( $item['label'] ) . '</a></li>';
			}
			echo '</ul></details>';
		}
		echo '</nav>';
	}
}
