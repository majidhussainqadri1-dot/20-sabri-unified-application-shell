<?php
/**
 * Fourth independent corrective hardening pass for Future Shell v5.
 *
 * Reconciles later-approved File 00, 01, 02, 19, 21 and 24 contracts while
 * preserving File 20's shell-only ownership boundary. No companion backend,
 * data store, authorization engine or security enforcement is duplicated here.
 *
 * @package SabriUnifiedApplicationShell
 */

namespace Sabri\UnifiedShell;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Fresh cross-file compatibility layer after the fourth ten-round audit. */
final class FutureShellV5FourthHardening {
	const CONTRACT_VERSION = '1.0.4';

	/** File 24 security conditions that File 20 may render, never enforce. */
	private static function security_states() {
		return array(
			'normal'                       => 'file24-normal-native-enforcement-unchanged',
			'elevated-monitoring'          => 'file24-elevated-monitoring-render-only',
			'restricted-high-risk-actions' => 'file24-restriction-advisory-native-modules-enforce',
			'upload-lockdown'              => 'file24-upload-lockdown-render-native-upload-owners-enforce',
			'identity-lockdown'            => 'file24-identity-lockdown-render-file00-file02-enforce',
			'publishing-read-only'         => 'file24-publishing-read-only-render-native-publishing-owner-enforces',
			'platform-read-only'           => 'file24-platform-read-only-render-native-owners-enforce',
			'incident-containment'         => 'file24-incident-containment-render-routing-native-owners-enforce',
		);
	}

	/** Register latest companion compatibility facts after earlier hardening layers. */
	public static function register() {
		add_filter( 'sabri_shell_contract_registry', array( __CLASS__, 'harmonize_current_companion_contracts' ), 1100 );
		add_filter( 'sabri_shell_operational_states', array( __CLASS__, 'harmonize_file24_security_states' ), 1100 );
		add_filter( 'sabri_shell_layout_contexts', array( __CLASS__, 'harmonize_layout_contexts' ), 1100 );
		add_filter( 'sabri_shell_layout_mode', array( __CLASS__, 'force_public_auth_standard_layout' ), 1100, 2 );
	}

	/**
	 * Publish current companion metadata without making it shell-owned truth.
	 *
	 * These are compatibility/readiness facts only. Runtime authorization and
	 * domain decisions remain with the declared canonical owners.
	 */
	public static function harmonize_current_companion_contracts( $registry ) {
		$registry = is_array( $registry ) ? $registry : array();
		$common   = array(
			'contract_version' => CentralPlanContract::CONTRACT_VERSION,
			'cache_policy'     => 'owner-aware-bounded',
		);

		$registry['00'] = array_merge( $common, array(
			'owner'                      => 'Sabri Membership Core',
			'native_scope'               => 'membership-identity-eligibility-assurance-consent-reverification-trust-security-assertions',
			'file20_boundary'            => 'consume-versioned-claims-no-authentication-ceremony-or-identity-write',
			'criticality'                => 'hard',
			'failure_behavior'           => 'privileged-presentation-fail-closed',
			'provider_baseline'          => 'membership-core-1.2.13',
			'public_membership_contract' => '1.2.0',
			'advanced_trust_contract'    => '1.0.0',
		) );

		$registry['01-B'] = array_merge( $common, array(
			'owner'                 => 'Sabri Platform Foundation',
			'native_scope'          => 'bootstrap-registries-contracts-activation-governance-shared-conventions',
			'file20_boundary'       => 'consume-foundation-registry-no-shell-search-ranking-or-domain-truth',
			'criticality'           => 'required',
			'failure_behavior'      => 'bounded-last-known-or-unavailable',
			'provider_baseline'     => 'foundation-runtime-2.0.0-future-foundation-18',
			'future_feature_count'  => 18,
		) );

		$registry['02'] = array_merge( $common, array(
			'owner'                         => 'Authentication and Accounts',
			'native_scope'                  => 'authentication-ceremony-credentials-passkeys-sessions-containment-recovery-entry-ux',
			'file20_boundary'               => 'route-slot-mount-and-minimal-task-layout-only-no-authentication-authority',
			'criticality'                   => 'required',
			'failure_behavior'              => 'route-unavailable-no-authentication-fallback',
			'provider_baseline'             => 'modern-auth-1.3.0-candidate',
			'modern_feature_count'          => 24,
			'passkey_assurance_v1_compat'   => '1.0.0',
			'authentication_assurance_v2'   => '2.0.0',
			'public_standard_route'         => '/.well-known/webauthn',
		) );

		$registry['19'] = array_merge( $common, array(
			'owner'                   => 'Unified Notifications and Alerts',
			'native_scope'            => 'notification-entity-delivery-intelligent-attention-preferences-queues-adapters',
			'file20_boundary'         => 'exactly-one-bell-center-settings-placement-no-notification-truth',
			'criticality'             => 'optional',
			'failure_behavior'        => 'bell-hidden-no-local-notification-store',
			'provider_baseline'       => 'notifications-runtime-3.0.0-intelligent-attention-os',
			'advanced_feature_count'  => 48,
		) );

		$registry['21'] = array_merge( $common, array(
			'owner'                => 'Complete Home and News Feed',
			'native_scope'         => 'home-social-posts-news-feed-newsroom-interactions-ng30',
			'file20_boundary'      => 'five-native-content-slots-no-global-shell-navigation-or-sidebar-ownership',
			'criticality'          => 'core-provider',
			'failure_behavior'     => 'section-unavailable-or-owner-fallback',
			'provider_baseline'    => 'home-news-runtime-1.0.1-ng30-amended',
			'native_slot_count'    => 5,
			'native_slots'         => array(
				'sabri_shell_home_before_main',
				'sabri_shell_home_main',
				'sabri_shell_home_after_main',
				'sabri_shell_home_right_sidebar',
				'sabri_shell_news_main',
			),
		) );

		$registry['24'] = array_merge( $common, array(
			'owner'                 => 'Security Privacy Compliance and Resilience Center',
			'native_scope'          => 'security-privacy-compliance-risk-incidents-resilience-assurance-governance',
			'file20_boundary'       => 'render-route-security-state-only-native-modules-enforce-no-second-safe-mode-engine',
			'criticality'           => 'assurance',
			'failure_behavior'      => 'unknown-no-secure-claim-native-controls-remain',
			'provider_baseline'     => 'future-security-superset-main',
			'security_state_count'  => 8,
			'security_state_owner'  => 'file-24',
		) );

		return $registry;
	}

	/** Add File 24's current state vocabulary as presentation/routing states. */
	public static function harmonize_file24_security_states( $states ) {
		return array_replace( is_array( $states ) ? $states : array(), self::security_states() );
	}

	/** Publish the public standards endpoint as a Minimal, non-page shell context. */
	public static function harmonize_layout_contexts( $contexts ) {
		$contexts = is_array( $contexts ) ? $contexts : array();
		$contexts['authentication_public_standard'] = array(
			'mode'  => Layout::MINIMAL,
			'owner' => 'file-02',
		);
		return $contexts;
	}

	/** Normalize the current request path for exact public standards routing. */
	private static function current_path() {
		$request = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- path-only classification.
		$path    = wp_parse_url( (string) $request, PHP_URL_PATH );
		if ( ! is_string( $path ) || '' === $path ) {
			return '/';
		}
		$path = preg_replace( '#/+#', '/', '/' . ltrim( $path, '/' ) );
		return strtolower( untrailingslashit( (string) $path ) );
	}

	/** Return the WordPress home path without a trailing slash. */
	private static function scope_root() {
		$path = wp_parse_url( home_url( '/' ), PHP_URL_PATH );
		$path = is_string( $path ) && '' !== $path ? $path : '/';
		$path = '/' . trim( $path, '/' );
		return '/' === $path ? '' : strtolower( $path );
	}

	/**
	 * Keep File 02's public /.well-known/webauthn response out of visual shell.
	 *
	 * This route is deliberately NOT marked private: File 02 defines it as public
	 * JSON with no-cache semantics. File 02 remains responsible for the response.
	 */
	public static function force_public_auth_standard_layout( $mode, $settings ) {
		unset( $settings );
		$expected = self::scope_root() . '/.well-known/webauthn';
		if ( self::current_path() === $expected ) {
			return Layout::MINIMAL;
		}
		return $mode;
	}
}
