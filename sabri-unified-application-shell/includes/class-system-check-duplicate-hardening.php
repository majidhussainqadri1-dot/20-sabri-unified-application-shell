<?php
/** Locale-safe duplicate-shell correction for System Check. */
namespace Sabri\UnifiedShell;
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class SystemCheckDuplicateHardening {
	public static function register() {
		add_filter( 'sabri_shell_system_check_report', array( __CLASS__, 'correct_duplicate_row' ), PHP_INT_MAX );
	}

	public static function correct_duplicate_row( $rows ) {
		if ( ! is_array( $rows ) ) { return $rows; }
		$active  = (array) get_option( 'active_plugins', array() );
		$matches = array();
		$unified = array();
		foreach ( $active as $plugin ) {
			$plugin = (string) $plugin;
			if ( false !== strpos( $plugin, 'sabri-global-ui' ) ) { $matches[] = $plugin; }
			if ( false !== strpos( $plugin, 'sabri-unified-application-shell' ) ) { $unified[] = $plugin; }
		}
		if ( count( $unified ) > 1 ) { $matches = array_merge( $matches, $unified ); }
		$matches = array_values( array_unique( $matches ) );
		foreach ( $rows as &$row ) {
			if ( ! is_array( $row ) || 'duplicate-shell' !== ( isset( $row['id'] ) ? $row['id'] : '' ) ) { continue; }
			$row['value'] = $matches ? implode( ', ', $matches ) : __( 'No duplicate shell plugin detected', 'sabri-unified-application-shell' );
			$row['status'] = $matches ? 'fail' : 'pass';
			$row['severity'] = $matches ? 'critical' : 'info';
			$row['evidence'] = 'Structured active-plugin duplicate scan; status is independent of translated display text.';
			break;
		}
		unset( $row );
		return $rows;
	}
}
