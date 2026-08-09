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
			'schema_version'       => is_array( $settings ) && isset( $settings['schema_version'] ) ? absint( $settings['schema_version'] ) : 0,
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

	/** Verify an option is exactly present/absent with the intended value. */
	private static function option_matches( $option, $exists, $value = null ) {
		$sentinel = new \stdClass();
		$stored = get_option( $option, $sentinel );
		if ( ! $exists ) {
			return $stored === $sentinel;
		}
		return $stored !== $sentinel && $stored === $value;
	}

	/** Record a fail-closed legacy rollback error without exposing state. */
	private static function rollback_failed( $stage ) {
		if ( class_exists( __NAMESPACE__ . '\\PlanV4Audit', false ) ) {
			PlanV4Audit::record( 'activation_snapshot_rollback_failed', array( 'stage' => sanitize_key( (string) $stage ), 'format_version' => self::FORMAT_VERSION ) );
		}
		return false;
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

		$settings_before = get_option( Defaults::OPTION_NAME, array() );
		$settings_before = is_array( $settings_before ) ? $settings_before : array();
		$emergency_before = ! empty( $settings_before['emergency_disabled'] );
		if ( array_key_exists( 'settings', $snapshot ) ) {
			if ( null === $snapshot['settings'] ) {
				/* Never erase an active Emergency Disable flag through legacy activation rollback. */
				if ( $emergency_before ) { return false; }
				delete_option( Defaults::OPTION_NAME );
				if ( ! self::option_matches( Defaults::OPTION_NAME, false ) ) { return self::rollback_failed( 'settings-delete' ); }
			} else {
				if ( ! is_array( $snapshot['settings'] ) ) { return false; }
				$restored_settings = $snapshot['settings'];
				$restored_settings['emergency_disabled'] = $emergency_before;
				update_option( Defaults::OPTION_NAME, $restored_settings, false );
				if ( ! self::option_matches( Defaults::OPTION_NAME, true, $restored_settings ) ) { return self::rollback_failed( 'settings-write' ); }
			}
		}
		if ( class_exists( __NAMESPACE__ . '\\FutureShellV5', false ) && array_key_exists( 'future_settings', $snapshot ) ) {
			if ( null === $snapshot['future_settings'] ) {
				delete_option( FutureShellV5::OPTION );
				if ( ! self::option_matches( FutureShellV5::OPTION, false ) ) { return self::rollback_failed( 'future-settings-delete' ); }
			} else {
				update_option( FutureShellV5::OPTION, $snapshot['future_settings'], false );
				if ( ! self::option_matches( FutureShellV5::OPTION, true, $snapshot['future_settings'] ) ) { return self::rollback_failed( 'future-settings-write' ); }
			}
		}
		if ( array_key_exists( 'flush_scheduled', $snapshot ) ) {
			if ( null === $snapshot['flush_scheduled'] ) {
				delete_option( 'sabri_shell_flush_rewrite_rules' );
				if ( ! self::option_matches( 'sabri_shell_flush_rewrite_rules', false ) ) { return self::rollback_failed( 'rewrite-flag-delete' ); }
			} else {
				update_option( 'sabri_shell_flush_rewrite_rules', $snapshot['flush_scheduled'], false );
				if ( ! self::option_matches( 'sabri_shell_flush_rewrite_rules', true, $snapshot['flush_scheduled'] ) ) { return self::rollback_failed( 'rewrite-flag-write' ); }
			}
		}

		$settings_after = get_option( Defaults::OPTION_NAME, array() );
		$settings_after = is_array( $settings_after ) ? $settings_after : array();
		PlanV4SettingsConcurrency::record_programmatic_change( $settings_before, $settings_after, 'activation-snapshot-rollback' );
		Navigation::invalidate_cache();
		Integrations::invalidate_cache();
		PlanV4PrivacyCache::purge();
		update_option( 'sabri_shell_flush_rewrite_rules', 1, false );
		if ( ! self::option_matches( 'sabri_shell_flush_rewrite_rules', true, 1 ) ) { return self::rollback_failed( 'rewrite-flag-schedule' ); }
		if ( class_exists( __NAMESPACE__ . '\\PlanV4Audit', false ) ) {
			PlanV4Audit::record( 'activation_snapshot_rollback', array( 'format_version' => self::FORMAT_VERSION, 'cache_purged' => true, 'post_write_verified' => true ) );
		}
		return true;
	}
}
