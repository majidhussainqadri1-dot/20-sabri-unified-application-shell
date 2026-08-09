<?php
/** Control-plane guard for Future Shell v5 release rings and LKG recovery. */
namespace Sabri\UnifiedShell;
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class FutureShellV5ControlGuard {
	const LKG_LOCK_OPTION = 'sabri_shell_future_lkg_restore_lock';
	const LKG_LOCK_TTL = 30;
	private static $recovering = false;

	public static function register() {
		add_filter( 'rest_pre_dispatch', array( __CLASS__, 'validate_feature_update' ), 5, 3 );
		add_filter( 'sabri_shell_auto_recovery_allowed', array( __CLASS__, 'block_legacy_auto_restore' ), PHP_INT_MAX, 2 );
		add_action( 'sabri_shell_runtime_failure', array( __CLASS__, 'maybe_hardened_recovery' ), 20, 1 );
	}

	public static function validate_feature_update( $result, $server, $request ) {
		unset( $server );
		if ( '/sabri-shell/v1/future/features' !== $request->get_route() || 'GET' === strtoupper( $request->get_method() ) || ! FutureShellV5::can_manage() ) { return $result; }
		$incoming = $request->get_json_params();
		if ( ! is_array( $incoming ) || empty( $incoming['features'] ) || ! is_array( $incoming['features'] ) ) { return $result; }
		$allowed = array( 'disabled', 'internal', 'staging', 'limited', 'general' );
		foreach ( $incoming['features'] as $feature => $rule ) {
			$feature = sanitize_key( $feature );
			if ( ! isset( FutureShellV5::features()[ $feature ] ) ) { return new \WP_Error( 'sabri_shell_unknown_future_feature', __( 'Unknown Future Shell feature. No feature settings were changed.', 'sabri-unified-application-shell' ), array( 'status' => 400, 'feature' => $feature ) ); }
			if ( ! is_array( $rule ) || ! array_key_exists( 'ring', $rule ) ) { return new \WP_Error( 'sabri_shell_release_ring_required', __( 'Every updated Future Shell feature must provide an explicit release ring. No feature settings were changed.', 'sabri-unified-application-shell' ), array( 'status' => 400, 'feature' => $feature ) ); }
			$ring = is_string( $rule['ring'] ) ? sanitize_key( $rule['ring'] ) : '';
			if ( ! in_array( $ring, $allowed, true ) ) { return new \WP_Error( 'sabri_shell_invalid_release_ring', __( 'Invalid Future Shell release ring. No feature settings were changed.', 'sabri-unified-application-shell' ), array( 'status' => 400, 'feature' => $feature ) ); }
		}
		return $result;
	}

	public static function block_legacy_auto_restore( $allowed, $context ) { unset( $allowed, $context ); return false; }

	public static function maybe_hardened_recovery( $context = array() ) {
		if ( self::$recovering || ! FutureShellV5::feature_enabled( 'last_known_good' ) ) { return; }
		$settings = FutureShellV5::settings();
		if ( empty( $settings['auto_recovery'] ) ) { return; }
		$state = get_option( FutureShellV5::CRITICAL_OPTION, array() );
		$events = is_array( $state ) && isset( $state['events'] ) && is_array( $state['events'] ) ? $state['events'] : array();
		$now = time();
		$recent = array_values( array_filter( $events, static function ( $stamp ) use ( $now ) { return $now - absint( $stamp ) <= 300; } ) );
		if ( count( $recent ) < 3 ) { return; }
		if ( self::restore_current_snapshot( 'critical-failure-threshold', $context ) ) { delete_option( FutureShellV5::CRITICAL_OPTION ); }
	}

	public static function restore_current_snapshot( $reason, $context = array() ) {
		$lock = self::acquire_lkg_lock();
		if ( '' === $lock ) { return false; }
		try {
			$snapshot = get_option( FutureShellV5::LKG_OPTION, array() );
			if ( ! is_array( $snapshot ) || empty( $snapshot['hash'] ) || empty( $snapshot['captured_at'] ) || ! isset( $snapshot['settings'] ) || ! is_array( $snapshot['settings'] ) ) { return false; }
			if ( (string) ( $snapshot['plugin_version'] ?? '' ) !== SABRI_SHELL_VERSION || Defaults::SCHEMA_VERSION !== absint( $snapshot['settings']['schema_version'] ?? 0 ) ) { return false; }
			$copy = $snapshot; unset( $copy['hash'] );
			if ( ! hash_equals( (string) $snapshot['hash'], hash( 'sha256', wp_json_encode( $copy ) ) ) ) { return false; }

			self::$recovering = true;
			$hook = array( FutureShellV5Hardening::class, 'capture_previous_lkg' );
			remove_action( 'update_option_' . Defaults::OPTION_NAME, $hook, 30 );
			$success = false;
			$before = get_option( Defaults::OPTION_NAME, array() );
			$before = is_array( $before ) ? $before : array();
			$current = array();
			try {
				update_option( Defaults::OPTION_NAME, $snapshot['settings'], false );
				$current = get_option( Defaults::OPTION_NAME, array() );
				$current = is_array( $current ) ? Settings::enforce_owned_invariants( $current ) : array();
				$expected = Settings::enforce_owned_invariants( $snapshot['settings'] );
				$expected['emergency_disabled'] = ! empty( $current['emergency_disabled'] );
				$success = $current === $expected;
			} catch ( \Throwable $error ) {
				do_action( 'sabri_shell_lkg_restore_failed', array( 'reason' => sanitize_key( $reason ), 'error' => get_class( $error ), 'context' => is_array( $context ) ? array_keys( $context ) : array() ) );
				$success = false;
			} finally {
				add_action( 'update_option_' . Defaults::OPTION_NAME, $hook, 30, 3 );
				self::$recovering = false;
			}
			if ( ! $success ) { return false; }
			PlanV4SettingsConcurrency::record_programmatic_change( $before, $current, 'lkg-restore' );
			Navigation::invalidate_cache();
			Integrations::invalidate_cache();
			if ( class_exists( __NAMESPACE__ . '\\PlanV4PrivacyCache', false ) ) { PlanV4PrivacyCache::purge(); }
			do_action( 'sabri_shell_lkg_restored', array( 'reason' => sanitize_key( $reason ), 'captured_at' => sanitize_text_field( (string) $snapshot['captured_at'] ), 'guarded' => true, 'serialized' => true, 'cache_purged' => true ) );
			return true;
		} finally {
			self::release_lkg_lock( $lock );
		}
	}

	private static function acquire_lkg_lock() {
		$token = function_exists( 'wp_generate_uuid4' ) ? wp_generate_uuid4() : uniqid( 'f20-lkg-', true );
		$record = array( 'token' => $token, 'expires' => time() + self::LKG_LOCK_TTL );
		if ( add_option( self::LKG_LOCK_OPTION, $record, '', 'no' ) ) { return $token; }
		$current = get_option( self::LKG_LOCK_OPTION, array() );
		if ( is_array( $current ) && absint( isset( $current['expires'] ) ? $current['expires'] : 0 ) < time() ) {
			delete_option( self::LKG_LOCK_OPTION );
			if ( add_option( self::LKG_LOCK_OPTION, $record, '', 'no' ) ) { return $token; }
		}
		return '';
	}

	private static function release_lkg_lock( $token ) {
		$current = get_option( self::LKG_LOCK_OPTION, array() );
		if ( is_array( $current ) && isset( $current['token'] ) && hash_equals( (string) $current['token'], (string) $token ) ) { delete_option( self::LKG_LOCK_OPTION ); }
	}
}
