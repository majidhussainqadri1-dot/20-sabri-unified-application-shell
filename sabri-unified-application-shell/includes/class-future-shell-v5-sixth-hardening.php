<?php
/**
 * Sixth independent corrective hardening pass for Future Shell v5.
 *
 * Reconciles newer File 00/01/02/24 evidence published after File 20 1.4.5.
 * These values are compatibility targets, never proof that a companion is
 * installed, healthy, staging-accepted or authorized for a current action.
 * Native owners remain responsible for runtime truth and enforcement.
 *
 * @package SabriUnifiedApplicationShell
 */

namespace Sabri\UnifiedShell;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Latest cross-file compatibility-truth layer after the sixth ten-round audit. */
final class FutureShellV5SixthHardening {
	const CONTRACT_VERSION = '1.0.6';

	/** Register after the prior compatibility layers. */
	public static function register() {
		add_filter( 'sabri_shell_contract_registry', array( __CLASS__, 'harmonize_latest_companion_targets' ), 1200 );
		add_filter( 'sabri_shell_system_check_sections', array( __CLASS__, 'system_check' ), 75 );
	}

	/**
	 * Mark a registry entry as declared compatibility metadata rather than live health.
	 *
	 * @param mixed $entry Existing registry entry.
	 * @param array $facts Latest approved compatibility facts.
	 * @return array
	 */
	private static function compatibility_target( $entry, $facts ) {
		$entry = is_array( $entry ) ? $entry : array();
		$facts = is_array( $facts ) ? $facts : array();
		return array_merge(
			$entry,
			$facts,
			array(
				'evidence_kind'                    => 'declared-compatibility-target-not-runtime-detection',
				'runtime_presence_must_be_verified' => true,
				'staging_acceptance_implied'        => false,
				'live_status_implied'               => false,
			)
		);
	}

	/**
	 * Reconcile companion contract facts that became newer than File 20 1.4.5.
	 *
	 * No File 20 authorization, authentication, notification, publishing or
	 * security-enforcement authority is created by this metadata.
	 *
	 * @param mixed $registry Contract registry.
	 * @return array
	 */
	public static function harmonize_latest_companion_targets( $registry ) {
		$registry = is_array( $registry ) ? $registry : array();

		$registry['00'] = self::compatibility_target(
			isset( $registry['00'] ) ? $registry['00'] : array(),
			array(
				'provider_baseline'          => 'membership-core-1.2.13',
				'provider_schema'            => '1.3.0',
				'public_membership_contract' => '1.2.0',
				'cf01_contract'              => '1.0.0',
				'advanced_trust_contract'    => '1.0.0',
				'file20_boundary'            => 'consume-versioned-claims-only-no-membership-identity-consent-or-trust-write',
			)
		);

		$registry['01-B'] = self::compatibility_target(
			isset( $registry['01-B'] ) ? $registry['01-B'] : array(),
			array(
				'provider_baseline'        => 'foundation-runtime-2.0.0-future-foundation-18',
				'provider_schema'          => '1.2.0',
				'foundation_contract'      => '2.0.0',
				'future_feature_count'     => 18,
				'file20_boundary'          => 'consume-foundation-registry-and-readiness-no-deploy-authority-shell-search-ranking-or-domain-truth',
			)
		);

		$registry['02'] = self::compatibility_target(
			isset( $registry['02'] ) ? $registry['02'] : array(),
			array(
				'provider_baseline'                 => 'modern-auth-runtime-1.3.1-third-ten-round-reviewed',
				'provider_schema'                   => '1.3.0',
				'passkey_schema'                    => '1.1.0',
				'modern_feature_count'              => 24,
				'passkey_assurance_v1_compat'       => '1.0.0',
				'authentication_assurance_v2'       => '2.0.0',
				'auth_event_projection_contract'    => '1.1.0',
				'file00_minimum_runtime'            => '1.2.13',
				'file00_cf01_contract'              => '1.0.0',
				'public_standard_route'             => '/.well-known/webauthn',
				'current_security_events'           => array(
					'AuthenticationCompromiseReported.v1',
					'AuthenticationLockdownEnabled.v1',
					'RecoveryChangeCoolingStarted.v1',
				),
				'privileged_reset_boundary'         => 'file02-executes-file00-dual-control-receipt-required-file20-renders-only',
				'file24_unavailable_behavior'       => 'native-file02-security-remains-enforced-file20-does-not-fallback',
				'file20_boundary'                   => 'route-slot-mount-minimal-layout-and-safe-presentation-only-no-authentication-security-or-containment-authority',
			)
		);

		$registry['24'] = self::compatibility_target(
			isset( $registry['24'] ) ? $registry['24'] : array(),
			array(
				'provider_baseline'        => 'future-security-runtime-0.99.0',
				'provider_schema'          => '0.25.5',
				'future_feature_count'     => 25,
				'future_requirement_range' => 'F24-FUT-001..F24-FUT-025',
				'native_enforcement'       => 'native-owners-enforce-file24-assesses-governs-file20-renders',
				'file20_boundary'          => 'render-route-security-state-only-no-security-governance-enforcement-or-second-safe-mode-engine',
			)
		);

		return $registry;
	}

	/** Publish non-sensitive compatibility targets without claiming live detection. */
	public static function system_check( $sections ) {
		$sections = is_array( $sections ) ? $sections : array();
		$sections['future_shell_v5_sixth_hardening'] = array(
			'label'                        => __( 'Future Shell v5 sixth-pass compatibility targets', 'sabri-unified-application-shell' ),
			'contract_version'             => self::CONTRACT_VERSION,
			'evidence_kind'                => 'declared-compatibility-target-not-runtime-detection',
			'runtime_verification'         => 'required-on-staging-and-at-native-owner-boundaries',
			'file00_target'                => 'runtime-1.2.13/schema-1.3.0/public-1.2.0/cf01-1.0.0/advanced-trust-1.0.0',
			'file01_target'                => 'runtime-2.0.0/schema-1.2.0/foundation-contract-2.0.0/future-18',
			'file02_target'                => 'runtime-1.3.1/schema-1.3.0/passkey-schema-1.1.0/auth-event-contract-1.1.0/features-24',
			'file24_target'                => 'runtime-0.99.0/schema-0.25.5/future-25',
			'native_enforcement_preserved' => true,
			'staging_accepted'             => false,
			'live_deployed'                => false,
		);
		return $sections;
	}
}
