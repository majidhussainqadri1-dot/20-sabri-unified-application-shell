<?php
/**
 * Fifth independent corrective hardening pass for Future Shell v5.
 *
 * This layer closes the residual release-ring contract gap discovered during
 * the fifth ten-round audit. It does not create authentication, identity or
 * entitlement truth. File 20 only consumes an explicit internal-principal
 * approval hook while keeping release-ring configuration manager-only.
 *
 * @package SabriUnifiedApplicationShell
 */

namespace Sabri\UnifiedShell;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Final release-ring evaluator for the fifth hardening pass. */
final class FutureShellV5FifthHardening {
	const CONTRACT_VERSION = '1.0.5';
	const INTERNAL_PRINCIPAL_HOOK = 'sabri_shell_future_internal_principal_allowed';

	/** Register the final ring evaluator after all earlier Future Shell layers. */
	public static function register() {
		/* The first hardening evaluator hard-coded Internal to manage_options.
		 * Replace it with the final evaluator so an explicitly approved internal
		 * principal contract can participate without broadening REST authority. */
		remove_filter( 'sabri_shell_future_feature_enabled', array( FutureShellV5Hardening::class, 'narrow_feature_enablement' ), 999999 );
		add_filter( 'sabri_shell_future_feature_enabled', array( __CLASS__, 'final_feature_enablement' ), PHP_INT_MAX, 3 );
		add_filter( 'sabri_shell_system_check_sections', array( __CLASS__, 'system_check' ), 74 );
	}

	/**
	 * Recompute the complete five-state release-ring rule at the final priority.
	 *
	 * The incoming $enabled value is deliberately not trusted because an earlier
	 * filter could broaden or narrow it. This method derives the final decision
	 * from the stored, sanitized rule. Invalid input fails closed.
	 *
	 * @param bool   $enabled Earlier decision; ignored by final evaluator.
	 * @param string $feature Feature id.
	 * @param mixed  $rule    Stored release-ring rule.
	 * @return bool
	 */
	public static function final_feature_enablement( $enabled, $feature, $rule ) {
		unset( $enabled );
		$feature = sanitize_key( $feature );
		if ( ! isset( FutureShellV5::features()[ $feature ] ) || ! is_array( $rule ) ) {
			return false;
		}

		$ring    = isset( $rule['ring'] ) ? sanitize_key( $rule['ring'] ) : '';
		$percent = isset( $rule['percent'] ) ? min( 100, max( 0, absint( $rule['percent'] ) ) ) : 0;

		switch ( $ring ) {
			case 'disabled':
				return false;

			case 'internal':
				if ( ! is_user_logged_in() ) {
					return false;
				}
				if ( current_user_can( 'manage_options' ) ) {
					return true;
				}
				/* Explicit extension contract only. Default remains deny. Native
				 * identity/entitlement owners decide whether a principal is internal. */
				return (bool) apply_filters(
					self::INTERNAL_PRINCIPAL_HOOK,
					false,
					$feature,
					$rule,
					get_current_user_id()
				);

			case 'staging':
				$environment = function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production';
				return in_array( $environment, array( 'local', 'development', 'staging' ), true );

			case 'limited':
				if ( ! is_user_logged_in() || $percent <= 0 ) {
					return false;
				}
				$bucket = absint( crc32( (string) get_current_user_id() . '|' . $feature ) ) % 100;
				return $bucket < $percent;

			case 'general':
				return true;

			default:
				return false;
		}
	}

	/** Publish only non-sensitive contract evidence in System Check. */
	public static function system_check( $sections ) {
		$sections = is_array( $sections ) ? $sections : array();
		$sections['future_shell_v5_fifth_hardening'] = array(
			'label'                       => __( 'Future Shell v5 fifth-pass hardening', 'sabri-unified-application-shell' ),
			'contract_version'            => self::CONTRACT_VERSION,
			'release_ring_states'         => array( 'disabled', 'internal', 'staging', 'limited', 'general' ),
			'internal_principal_contract' => self::INTERNAL_PRINCIPAL_HOOK,
			'configuration_authority'     => 'manage_options',
			'native_identity_owner'       => 'file-00',
			'staging_accepted'            => false,
			'live_deployed'               => false,
		);
		return $sections;
	}
}
