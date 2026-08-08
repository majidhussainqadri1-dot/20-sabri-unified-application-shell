<?php
/**
 * Eighth independent corrective hardening pass for Future Shell v5.
 *
 * Fresh review over merged 1.4.7. This layer closes post-merge gaps without
 * taking any native-domain authority away from canonical companion owners.
 *
 * @package SabriUnifiedApplicationShell
 */

namespace Sabri\UnifiedShell;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class FutureShellV5EighthHardening {
	const CONTRACT_VERSION = '1.0.8';

	public static function register() {
		/* File 25 is the only visual authority. Retire the stale File 20 editor. */
		add_action( 'admin_init', array( __CLASS__, 'retire_appearance_screen' ), 1 );
		add_action( 'admin_head', array( __CLASS__, 'hide_appearance_tab' ), 999 );

		/* Final route classifier must work at web root and WordPress subdirectories. */
		add_filter( 'sabri_shell_layout_mode', array( __CLASS__, 'force_subdirectory_safe_sensitive_layout' ), 1500, 2 );

		add_filter( 'sabri_shell_system_check_sections', array( __CLASS__, 'system_check' ), 80 );
	}

	/**
	 * Prevent direct use of File 20's superseded Appearance editor.
	 * Historical appearance option values remain untouched as migration data.
	 */
	public static function retire_appearance_screen() {
		if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only navigation guard.
		$tab  = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only navigation guard.
		if ( 'sabri-shell' !== $page || 'appearance' !== $tab ) {
			return;
		}
		wp_safe_redirect( add_query_arg( array( 'page' => 'sabri-shell', 'sabri_shell_notice' => 'appearance-owned-by-file25' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	/** Hide the obsolete tab link on the File 20 administration surface. */
	public static function hide_appearance_tab() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		echo '<style id="sabri-shell-file25-appearance-ownership">.sabri-shell-admin .nav-tab[href*="tab=appearance"]{display:none!important}</style>';
	}

	/** Normalize the current request to a site-relative, lower-case path. */
	private static function current_relative_path() {
		$request = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- path-only classification.
		$path    = wp_parse_url( (string) $request, PHP_URL_PATH );
		$root    = wp_parse_url( home_url( '/' ), PHP_URL_PATH );
		$path    = is_string( $path ) && '' !== $path ? '/' . trim( preg_replace( '#/+#', '/', $path ), '/' ) : '/';
		$root    = is_string( $root ) && '' !== $root ? '/' . trim( preg_replace( '#/+#', '/', $root ), '/' ) : '/';
		$path    = strtolower( '/' === $path ? '/' : untrailingslashit( $path ) );
		$root    = strtolower( '/' === $root ? '/' : untrailingslashit( $root ) );
		if ( '/' !== $root && ( $path === $root || 0 === strpos( $path, $root . '/' ) ) ) {
			$path = substr( $path, strlen( $root ) );
			$path = '' === $path ? '/' : $path;
		}
		return $path;
	}

	private static function path_matches( $path, $prefix ) {
		return $path === $prefix || 0 === strpos( $path, $prefix . '/' );
	}

	/**
	 * Correct the older root-only task classifier at a later priority.
	 * This changes presentation only; native owners still authorize every task.
	 */
	public static function force_subdirectory_safe_sensitive_layout( $mode, $settings ) {
		unset( $settings );
		$path = self::current_relative_path();
		$private_tasks = array(
			'/account-security', '/account-passkeys', '/resolve-account', '/membership-application',
			'/membership-status', '/guardian-consent', '/membership-security', '/platform-system-check',
			'/platform-foundation/status',
		);
		foreach ( $private_tasks as $prefix ) {
			if ( self::path_matches( $path, $prefix ) ) {
				return Layout::MINIMAL;
			}
		}
		return $mode;
	}

	public static function system_check( $sections ) {
		$sections = is_array( $sections ) ? $sections : array();
		$sections['future_shell_v5_eighth_hardening'] = array(
			'label'                            => __( 'Future Shell v5 eighth-pass hardening', 'sabri-unified-application-shell' ),
			'contract_version'                 => self::CONTRACT_VERSION,
			'visual_owner'                     => 'file-25',
			'file20_appearance_editor'         => 'retired',
			'legacy_appearance_data'           => 'preserved-migration-only',
			'sensitive_task_subdirectory_safe' => true,
			'staging_accepted'                 => false,
			'live_deployed'                    => false,
		);
		return $sections;
	}
}
