<?php
/**
 * Activation snapshot and rollback.
 *
 * @package SabriUnifiedApplicationShell
 */

namespace Sabri\UnifiedShell;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Stores shell-only pre-activation state before defaults or migrations run. */
final class Snapshot {
	const FORMAT_VERSION = 2;
	const LEGACY_OPTION  = 'sabri_shell_activation_snapshot_legacy';

	/** Capture an integrity-protected File-20-only activation snapshot. */
	public static function capture_activation_snapshot() {
		$existing = get_option( Defaults::SNAPSHOT_OPTION_NAME, false );
		if ( is_array( $existing ) && self::verify( $existing ) && self::FORMAT_VERSION === absint( isset( $existing['format_version'] ) ? $existing['format_version'] : 0 ) ) {
			return;
		}

		/* Preserve a legacy snapshot as evidence, but do not execute it automatically. */
		if ( false !== $existing && ! get_option( self::LEGACY_OPTION, false ) ) {
			update_option( self::LEGACY_OPTION, $existing, false );
		}

		$settings = get_option( Defaults::OPTION_NAME, null );
		$future   = class_exists( __NAMESPACE__ . '\\FutureShellV5', false ) ? get_option( FutureShellV5::OPTION, null ) : null;
		$snapshot = array(
			'format_version'       => self::FORMAT_VERSION,
			'captured_at'          => gmdate( 'c' ),
			'actor_id'             => function_exists( 'get_current_user_id' ) ? get_current_user_id() : 0,
			'source'               => 'file-20-activation',
			'plugin_version'       => defined( 'SABRI_SHELL_VERSION' ) ? SABRI_SHELL_VERSION : '0.0.0',
			'schema_version'       => Defaults::SCHEMA_VERSION,
			'settings'             => $settings,
			'future_settings'      => $future,
			'flush_scheduled'      => get_option( 'sabri_shell_flush_rewrite_rules', null ),
			'route_fingerprint'    => class_exists( __NAMESPACE__ . '\\Navigation', false ) ? hash( 'sha256', wp_json_encode( Navigation::resolved() ) ) : '',
			'integration_fingerprint' => class_exists( __NAMESPACE__ . '\\PlanV4ContractHealth', false ) ? hash( 'sha256', wp_json_encode( PlanV4ContractHealth::providers() ) ) : '',
			'allowed_boundaries'   => array(
				'sabri_shell_settings',
				FutureShellV5::OPTION,
				'sabri_shell_flush_rewrite_rules',
			),
		);
		$copy = $snapshot;
		$snapshot['hash'] = hash( 'sha256', wp_json_encode( $copy ) );
		update_option( Defaults::SNAPSHOT_OPTION_NAME, $snapshot, false );
	}

	/** Verify snapshot integrity and current automatic-rollback compatibility. */
	public static function verify( array $snapshot ) {
		if ( empty( $snapshot['hash'] ) || self::FORMAT_VERSION !== absint( isset( $snapshot['format_version'] ) ? $snapshot['format_version'] : 0 ) ) {
			return false;
		}
		$hash = (string) $snapshot['hash'];
		unset( $snapshot['hash'] );
		return hash_equals( hash( 'sha256', wp_json_encode( $snapshot ) ), $hash );
	}

	/**
	 * Legacy convenience rollback now obeys the same File-20-only boundaries.
	 * New operator workflows should use PlanV4Recovery preview/execute instead.
	 */
	public static function rollback() {
		$snapshot = get_option( Defaults::SNAPSHOT_OPTION_NAME, false );
		if ( ! is_array( $snapshot ) || ! self::verify( $snapshot ) ) {
			return false;
		}
		$current_major = (int) strtok( defined( 'SABRI_SHELL_VERSION' ) ? SABRI_SHELL_VERSION : '0', '.' );
		$target_major  = (int) strtok( isset( $snapshot['plugin_version'] ) ? $snapshot['plugin_version'] : '0', '.' );
		if ( $current_major !== $target_major || Defaults::SCHEMA_VERSION !== absint( isset( $snapshot['schema_version'] ) ? $snapshot['schema_version'] : 0 ) ) {
			return false;
		}

		if ( array_key_exists( 'settings', $snapshot ) ) {
			if ( null === $snapshot['settings'] ) {
				delete_option( Defaults::OPTION_NAME );
			} else {
				update_option( Defaults::OPTION_NAME, $snapshot['settings'], false );
			}
		}
		if ( class_exists( __NAMESPACE__ . '\\FutureShellV5', false ) && array_key_exists( 'future_settings', $snapshot ) ) {
			if ( null === $snapshot['future_settings'] ) {
				delete_option( FutureShellV5::OPTION );
			} else {
				update_option( FutureShellV5::OPTION, $snapshot['future_settings'], false );
			}
		}
		if ( array_key_exists( 'flush_scheduled', $snapshot ) ) {
			if ( null === $snapshot['flush_scheduled'] ) {
				delete_option( 'sabri_shell_flush_rewrite_rules' );
			} else {
				update_option( 'sabri_shell_flush_rewrite_rules', $snapshot['flush_scheduled'], false );
			}
		}

		Navigation::invalidate_cache();
		Integrations::invalidate_cache();
		PlanV4PrivacyCache::purge();
		update_option( 'sabri_shell_flush_rewrite_rules', 1, false );
		if ( class_exists( __NAMESPACE__ . '\\PlanV4Audit', false ) ) {
			PlanV4Audit::record( 'activation_snapshot_rollback', array( 'format_version' => self::FORMAT_VERSION, 'cache_purged' => true ) );
		}
		return true;
	}
}
