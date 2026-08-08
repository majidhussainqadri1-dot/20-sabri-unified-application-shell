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

	public static function system_check( $sections ) {
		$sections = is_array( $sections ) ? $sections : array();
		$sections['future_shell_v5_eighth_hardening'] = array(
			'label'                     => __( 'Future Shell v5 eighth-pass hardening', 'sabri-unified-application-shell' ),
			'contract_version'          => self::CONTRACT_VERSION,
			'visual_owner'              => 'file-25',
			'file20_appearance_editor'  => 'retired',
			'legacy_appearance_data'    => 'preserved-migration-only',
			'staging_accepted'          => false,
			'live_deployed'             => false,
		);
		return $sections;
	}
}
