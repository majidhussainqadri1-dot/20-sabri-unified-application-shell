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
	const QUERY_NONCE_ACTION   = 'sabri_shell_safe_mode';
	const EMERGENCY_META_OPTION = 'sabri_shell_emergency_state';

	/** Whether the constant kill switch is active. */
	public static function constant_disabled() {
		return defined( 'SABRI_SHELL_DISABLE' ) && SABRI_SHELL_DISABLE;
	}

	/**
	 * Whether an authorized, nonce-bound safe-mode URL flag is active.
	 * The configuration constant remains the highest-priority break-glass path.
	 */
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
				'sabri_shell_safe'          => 1,
				'_sabri_shell_safe_nonce'   => wp_create_nonce( self::QUERY_NONCE_ACTION ),
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

	/**
	 * Apply an audited emergency state transition.
	 *
	 * Disable requires a reason and review window. Re-enable requires an intact
	 * File 20 audit chain, no critical provider collision/error/invalid state,
	 * and a cache purge before the shell is allowed to render again.
	 */
	public static function set_emergency_disabled( $disable, $reason = '', $review_hours = 24 ) {
		if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
			return new \WP_Error( 'sabri_shell_emergency_forbidden', __( 'You are not allowed to change emergency state.', 'sabri-unified-application-shell' ), array( 'status' => 403 ) );
		}
		$disable = (bool) $disable;
		$reason  = sanitize_text_field( (string) $reason );
		$review_hours = max( 1, min( 168, absint( $review_hours ) ) );
		$settings = Settings::get();
		$previous = ! empty( $settings['emergency_disabled'] );
		if ( $disable === $previous ) {
			return array( 'changed' => false, 'disabled' => $disable, 'metadata' => self::emergency_metadata() );
		}

		if ( $disable ) {
			if ( '' === $reason ) {
				return new \WP_Error( 'sabri_shell_emergency_reason_required', __( 'Emergency Disable requires a reason.', 'sabri-unified-application-shell' ), array( 'status' => 400 ) );
			}
			$now = time();
			$meta = array(
				'state'       => 'disabled',
				'reason'      => $reason,
				'actor_id'    => get_current_user_id(),
				'disabled_at' => gmdate( 'c', $now ),
				'review_at'   => gmdate( 'c', $now + ( $review_hours * HOUR_IN_SECONDS ) ),
				'review_hours'=> $review_hours,
			);
			$settings['emergency_disabled'] = true;
			update_option( Defaults::OPTION_NAME, $settings, false );
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
		foreach ( (array) $health as $provider ) {
			if ( is_array( $provider ) && isset( $provider['state'] ) && in_array( $provider['state'], array( 'collision', 'error', 'invalid' ), true ) ) {
				return new \WP_Error( 'sabri_shell_emergency_reenable_health_blocked', __( 'The shell cannot be re-enabled while a critical provider state is unresolved.', 'sabri-unified-application-shell' ), array( 'status' => 409 ) );
			}
		}

		PlanV4PrivacyCache::purge();
		$settings['emergency_disabled'] = false;
		update_option( Defaults::OPTION_NAME, $settings, false );
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
		PlanV4Audit::record( 'emergency_reenabled', array( 'actor_id' => get_current_user_id(), 'reason' => $reason, 'cache_purged' => true ) );
		do_action( 'sabri_shell_emergency_reenabled', array( 'actor_id' => get_current_user_id(), 'cache_purged' => true ) );
		return array( 'changed' => true, 'disabled' => false, 'metadata' => $meta );
	}

	/** Whether any disable path is active. */
	public static function disabled() {
		return self::constant_disabled() || self::query_safe_mode() || self::emergency_disabled();
	}
}
