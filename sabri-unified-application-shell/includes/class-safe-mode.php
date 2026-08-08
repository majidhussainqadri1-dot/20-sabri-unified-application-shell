<?php
/**
 * Safe mode and emergency disable helpers.
 *
 * @package SabriUnifiedApplicationShell
 */

namespace Sabri\UnifiedShell;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Determines when the public shell should be suppressed. */
final class SafeMode {
	const QUERY_NONCE_ACTION    = 'sabri_shell_safe_mode';
	const EMERGENCY_META_OPTION = 'sabri_shell_emergency_state';

	/** @var bool True only while this class performs an audited emergency write. */
	private static $emergency_write_authorized = false;

	/** Register the option-level emergency-state integrity guard. */
	public static function register_integrity_guard() {
		add_filter( 'pre_update_option_' . Defaults::OPTION_NAME, array( __CLASS__, 'guard_emergency_settings_write' ), PHP_INT_MAX, 3 );
	}

	/**
	 * Block all direct emergency flag writes outside the canonical lifecycle.
	 *
	 * @param mixed  $value New option value.
	 * @param mixed  $old_value Previous option value.
	 * @param string $option Option name.
	 * @return mixed
	 */
	public static function guard_emergency_settings_write( $value, $old_value, $option ) {
		unset( $option );
		if ( self::$emergency_write_authorized || ! is_array( $value ) ) {
			return $value;
		}
		$old_value = is_array( $old_value ) ? $old_value : array();
		$old_flag  = ! empty( $old_value['emergency_disabled'] );
		$new_flag  = ! empty( $value['emergency_disabled'] );
		if ( $old_flag === $new_flag ) {
			return $value;
		}
		$value['emergency_disabled'] = $old_flag;
		if ( class_exists( __NAMESPACE__ . '\\PlanV4Audit', false ) ) {
			PlanV4Audit::record(
				'emergency_direct_write_blocked',
				array(
					'actor_id'       => function_exists( 'get_current_user_id' ) ? get_current_user_id() : 0,
					'attempted_state'=> $new_flag ? 'disabled' : 'enabled',
					'reason_code'    => 'canonical-lifecycle-required',
				)
			);
		}
		return $value;
	}

	/** Whether the constant kill switch is active. */
	public static function constant_disabled() {
		return defined( 'SABRI_SHELL_DISABLE' ) && SABRI_SHELL_DISABLE;
	}

	/** Whether an authorized, nonce-bound safe-mode URL flag is active. */
	public static function query_safe_mode() {
		if ( empty( $_GET['sabri_shell_safe'] ) ) {
			return false;
		}
		if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
			return false;
		}
		$nonce = isset( $_GET['_sabri_shell_safe_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_sabri_shell_safe_nonce'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- nonce is verified below.
		return '' !== $nonce && (bool) wp_verify_nonce( $nonce, self::QUERY_NONCE_ACTION );
	}

	/** Generate an authorized Safe Mode URL for the current administrator session. */
	public static function query_safe_mode_url( $url = '' ) {
		if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
			return '';
		}
		$url = is_string( $url ) && '' !== $url ? $url : home_url( '/' );
		return add_query_arg(
			array(
				'sabri_shell_safe'        => 1,
				'_sabri_shell_safe_nonce' => wp_create_nonce( self::QUERY_NONCE_ACTION ),
			),
			$url
		);
	}

	/** Whether admin emergency disable is active. */
	public static function emergency_disabled() {
		$settings = Settings::get();
		return ! empty( $settings['emergency_disabled'] );
	}

	/** Return bounded emergency lifecycle metadata. */
	public static function emergency_metadata() {
		$meta = get_option( self::EMERGENCY_META_OPTION, array() );
		return is_array( $meta ) ? $meta : array();
	}

	/** Write the File 20 settings option through the canonical emergency gate. */
	private static function write_settings( array $settings ) {
		self::$emergency_write_authorized = true;
		try {
			update_option( Defaults::OPTION_NAME, $settings, false );
		} finally {
			self::$emergency_write_authorized = false;
		}
		$stored = get_option( Defaults::OPTION_NAME, array() );
		return is_array( $stored ) && ! empty( $stored['emergency_disabled'] ) === ! empty( $settings['emergency_disabled'] );
	}

	/**
	 * Apply an audited emergency state transition.
	 *
	 * Disable requires a reason and review window. Re-enable requires an intact
	 * audit chain, verified critical File 20/File 00 providers, and a cache purge.
	 */
	public static function set_emergency_disabled( $disable, $reason = '', $review_hours = 24 ) {
		if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
			return new \WP_Error( 'sabri_shell_emergency_forbidden', __( 'You are not allowed to change emergency state.', 'sabri-unified-application-shell' ), array( 'status' => 403 ) );
		}
		$disable      = (bool) $disable;
		$reason       = sanitize_text_field( (string) $reason );
		$review_hours = max( 1, min( 168, absint( $review_hours ) ) );
		$settings     = Settings::get();
		$previous     = ! empty( $settings['emergency_disabled'] );
		if ( $disable === $previous ) {
			return array( 'changed' => false, 'disabled' => $disable, 'metadata' => self::emergency_metadata() );
		}

		if ( $disable ) {
			if ( '' === $reason ) {
				return new \WP_Error( 'sabri_shell_emergency_reason_required', __( 'Emergency Disable requires a reason.', 'sabri-unified-application-shell' ), array( 'status' => 400 ) );
			}
			$now  = time();
			$meta = array(
				'state'        => 'disabled',
				'reason'       => $reason,
				'actor_id'     => get_current_user_id(),
				'disabled_at'  => gmdate( 'c', $now ),
				'review_at'    => gmdate( 'c', $now + ( $review_hours * HOUR_IN_SECONDS ) ),
				'review_hours' => $review_hours,
			);
			$settings['emergency_disabled'] = true;
			if ( ! self::write_settings( $settings ) ) {
				return new \WP_Error( 'sabri_shell_emergency_write_failed', __( 'Emergency state could not be persisted safely.', 'sabri-unified-application-shell' ), array( 'status' => 500 ) );
			}
			update_option( self::EMERGENCY_META_OPTION, $meta, false );
			Navigation::invalidate_cache();
			Integrations::invalidate_cache();
			PlanV4Audit::record( 'emergency_disabled', $meta );
			do_action( 'sabri_shell_emergency_disabled', array( 'reason' => $reason, 'actor_id' => get_current_user_id(), 'review_at' => $meta['review_at'] ) );
			return array( 'changed' => true, 'disabled' => true, 'metadata' => $meta );
		}

		if ( ! PlanV4Audit::verify_chain() ) {
			return new \WP_Error( 'sabri_shell_emergency_reenable_audit_invalid', __( 'The shell cannot be re-enabled while File 20 audit integrity is invalid.', 'sabri-unified-application-shell' ), array( 'status' => 409 ) );
		}
		$health = PlanV4ContractHealth::health( array(), true );
		$critical_state = class_exists( __NAMESPACE__ . '\\FutureShellV5TenthHardening', false )
			? FutureShellV5TenthHardening::critical_health_state( $health )
			: 'unknown';
		if ( 'healthy' !== $critical_state ) {
			return new \WP_Error(
				'sabri_shell_emergency_reenable_health_blocked',
				__( 'The shell cannot be re-enabled until critical File 20 and File 00 provider evidence is healthy.', 'sabri-unified-application-shell' ),
				array( 'status' => 409, 'critical_state' => $critical_state )
			);
		}

		PlanV4PrivacyCache::purge();
		$settings['emergency_disabled'] = false;
		if ( ! self::write_settings( $settings ) ) {
			return new \WP_Error( 'sabri_shell_emergency_write_failed', __( 'The shell could not be re-enabled safely.', 'sabri-unified-application-shell' ), array( 'status' => 500 ) );
		}
		$meta = self::emergency_metadata();
		$meta['state']        = 're-enabled';
		$meta['reenabled_at'] = gmdate( 'c' );
		$meta['reenabled_by'] = get_current_user_id();
		if ( '' !== $reason ) {
			$meta['reenable_reason'] = $reason;
		}
		update_option( self::EMERGENCY_META_OPTION, $meta, false );
		Navigation::invalidate_cache();
		Integrations::invalidate_cache();
		PlanV4Audit::record( 'emergency_reenabled', array( 'actor_id' => get_current_user_id(), 'reason' => $reason, 'cache_purged' => true, 'critical_health_state' => $critical_state ) );
		do_action( 'sabri_shell_emergency_reenabled', array( 'actor_id' => get_current_user_id(), 'cache_purged' => true ) );
		return array( 'changed' => true, 'disabled' => false, 'metadata' => $meta );
	}

	/** Whether any disable path is active. */
	public static function disabled() {
		return self::constant_disabled() || self::query_safe_mode() || self::emergency_disabled();
	}
}

SafeMode::register_integrity_guard();
