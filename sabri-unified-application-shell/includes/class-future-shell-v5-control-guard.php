<?php
/**
 * Control-plane guard for Future Shell v5 release rings and LKG recovery.
 *
 * @package SabriUnifiedApplicationShell
 */

namespace Sabri\UnifiedShell;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Keeps malformed control-plane input fail closed and replaces the legacy
 * automatic LKG restore path with an exception-safe compatible restore.
 */
final class FutureShellV5ControlGuard {
	/** @var bool */
	private static $recovering = false;

	/** Register control-plane guards. */
	public static function register() {
		add_filter( 'rest_pre_dispatch', array( __CLASS__, 'validate_feature_update' ), 5, 3 );
		/* The base class keeps its public restore method for compatibility, but its
		 * automatic path is disabled and replaced below so restoration can use
		 * try/finally and suppress LKG overwrite during the restore transaction. */
		add_filter( 'sabri_shell_auto_recovery_allowed', array( __CLASS__, 'block_legacy_auto_restore' ), PHP_INT_MAX, 2 );
		add_action( 'sabri_shell_runtime_failure', array( __CLASS__, 'maybe_hardened_recovery' ), 20, 1 );
	}

	/**
	 * Reject malformed release-ring input before the base REST callback can map
	 * an unknown value to General.
	 *
	 * @param mixed            $result  Pre-dispatch result.
	 * @param \WP_REST_Server  $server  REST server.
	 * @param \WP_REST_Request $request Request.
	 * @return mixed
	 */
	public static function validate_feature_update( $result, $server, $request ) {
		unset( $server );
		if ( '/sabri-shell/v1/future/features' !== $request->get_route() || 'GET' === strtoupper( $request->get_method() ) || ! FutureShellV5::can_manage() ) {
			return $result;
		}
		$incoming = $request->get_json_params();
		if ( ! is_array( $incoming ) || empty( $incoming['features'] ) || ! is_array( $incoming['features'] ) ) {
			return $result;
		}
		$allowed = array( 'disabled', 'internal', 'staging', 'limited', 'general' );
		foreach ( $incoming['features'] as $feature => $rule ) {
			$feature = sanitize_key( $feature );
			if ( ! isset( FutureShellV5::features()[ $feature ] ) ) {
				return new \WP_Error(
					'sabri_shell_unknown_future_feature',
					__( 'Unknown Future Shell feature. No feature settings were changed.', 'sabri-unified-application-shell' ),
					array( 'status' => 400, 'feature' => $feature )
				);
			}
			if ( ! is_array( $rule ) || ! array_key_exists( 'ring', $rule ) ) {
				return new \WP_Error(
					'sabri_shell_release_ring_required',
					__( 'Every updated Future Shell feature must provide an explicit release ring. No feature settings were changed.', 'sabri-unified-application-shell' ),
					array( 'status' => 400, 'feature' => $feature )
				);
			}
			$ring = is_string( $rule['ring'] ) ? sanitize_key( $rule['ring'] ) : '';
			if ( ! in_array( $ring, $allowed, true ) ) {
				return new \WP_Error(
					'sabri_shell_invalid_release_ring',
					__( 'Invalid Future Shell release ring. No feature settings were changed.', 'sabri-unified-application-shell' ),
					array( 'status' => 400, 'feature' => $feature )
				);
			}
		}
		return $result;
	}

	/** Always block the legacy automatic restore; the guarded action replaces it. */
	public static function block_legacy_auto_restore( $allowed, $context ) {
		unset( $allowed, $context );
		return false;
	}

	/**
	 * Run exception-safe recovery after the base failure counter is updated.
	 *
	 * @param mixed $context Failure context.
	 * @return void
	 */
	public static function maybe_hardened_recovery( $context = array() ) {
		if ( self::$recovering || ! FutureShellV5::feature_enabled( 'last_known_good' ) ) {
			return;
		}
		$settings = FutureShellV5::settings();
		if ( empty( $settings['auto_recovery'] ) ) {
			return;
		}
		$state  = get_option( FutureShellV5::CRITICAL_OPTION, array() );
		$events = is_array( $state ) && isset( $state['events'] ) && is_array( $state['events'] ) ? $state['events'] : array();
		$now    = time();
		$recent = array_values( array_filter( $events, static function ( $stamp ) use ( $now ) { return $now - absint( $stamp ) <= 300; } ) );
		if ( count( $recent ) < 3 ) {
			return;
		}
		if ( self::restore_current_snapshot( 'critical-failure-threshold', $context ) ) {
			delete_option( FutureShellV5::CRITICAL_OPTION );
		}
	}

	/**
	 * Verify and restore only a snapshot from this plugin/settings schema.
	 *
	 * @param string $reason  Recovery reason.
	 * @param mixed  $context Failure context.
	 * @return bool
	 */
	public static function restore_current_snapshot( $reason, $context = array() ) {
		$snapshot = get_option( FutureShellV5::LKG_OPTION, array() );
		if ( ! is_array( $snapshot ) || empty( $snapshot['hash'] ) || empty( $snapshot['captured_at'] ) || ! isset( $snapshot['settings'] ) || ! is_array( $snapshot['settings'] ) ) {
			return false;
		}
		if ( (string) ( $snapshot['plugin_version'] ?? '' ) !== SABRI_SHELL_VERSION || Defaults::SCHEMA_VERSION !== absint( $snapshot['settings']['schema_version'] ?? 0 ) ) {
			return false;
		}
		$copy = $snapshot;
		unset( $copy['hash'] );
		if ( ! hash_equals( (string) $snapshot['hash'], hash( 'sha256', wp_json_encode( $copy ) ) ) ) {
			return false;
		}

		self::$recovering = true;
		$hook = array( FutureShellV5Hardening::class, 'capture_previous_lkg' );
		remove_action( 'update_option_' . Defaults::OPTION_NAME, $hook, 30 );
		$success = false;
		$before = get_option( Defaults::OPTION_NAME, array() );
		$before = is_array( $before ) ? $before : array();
		try {
			update_option( Defaults::OPTION_NAME, $snapshot['settings'], false );
			$current = get_option( Defaults::OPTION_NAME, array() );
			$current = is_array( $current ) ? Settings::enforce_owned_invariants( $current ) : array();
			$expected = Settings::enforce_owned_invariants( $snapshot['settings'] );
			/* SafeMode may preserve the current emergency flag; compare all other state. */
			$current_emergency = ! empty( $current['emergency_disabled'] );
			$expected['emergency_disabled'] = $current_emergency;
			$success = $current === $expected;
		} catch ( \Throwable $error ) {
			do_action(
				'sabri_shell_lkg_restore_failed',
				array(
					'reason'    => sanitize_key( $reason ),
					'error'     => get_class( $error ),
					'context'   => is_array( $context ) ? array_keys( $context ) : array(),
				)
			);
			$success = false;
		} finally {
			add_action( 'update_option_' . Defaults::OPTION_NAME, $hook, 30, 3 );
			self::$recovering = false;
		}

		if ( ! $success ) {
			return false;
		}
		PlanV4SettingsConcurrency::record_programmatic_change( $before, $current, 'lkg-restore' );
		Navigation::invalidate_cache();
		Integrations::invalidate_cache();
		do_action(
			'sabri_shell_lkg_restored',
			array(
				'reason'      => sanitize_key( $reason ),
				'captured_at' => sanitize_text_field( (string) $snapshot['captured_at'] ),
				'guarded'     => true,
			)
		);
		return true;
	}
}
