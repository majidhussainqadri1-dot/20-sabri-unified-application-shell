<?php
/**
 * Seventh independent corrective hardening pass for Future Shell v5.
 *
 * Reconciles the later consolidated central-plan CF-01..CF-06 conditional
 * module surface with File 20 without activating any conditional backend.
 * File 20 remains the structural shell only; every domain owner retains its
 * own authorization, data, workflow, security, cache and activation truth.
 *
 * @package SabriUnifiedApplicationShell
 */

namespace Sabri\UnifiedShell;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Conditional-module compatibility and privacy layer. */
final class FutureShellV5SeventhHardening {
	const CONTRACT_VERSION = '1.0.7';
	const CONDITIONAL_COUNT = 6;

	/** Register after the sixth hardening layer. */
	public static function register() {
		add_filter( 'sabri_shell_contract_registry', array( __CLASS__, 'harmonize_conditional_modules' ), 1300 );
		add_filter( 'sabri_shell_future_private_path_fragments', array( __CLASS__, 'conditional_private_paths' ), 1300 );
		add_filter( 'sabri_shell_layout_contexts', array( __CLASS__, 'conditional_layout_contexts' ), 1300 );
		add_filter( 'sabri_shell_layout_mode', array( __CLASS__, 'force_nonvisual_conditional_routes' ), 1300, 2 );
		add_filter( 'sabri_shell_feature_ui_contracts', array( __CLASS__, 'conditional_ui_contracts' ), 1300 );
		add_filter( 'sabri_shell_system_check_sections', array( __CLASS__, 'system_check' ), 76 );
	}

	/** Add common truth boundaries to one conditional module entry. */
	private static function conditional_target( $entry, $facts ) {
		$entry = is_array( $entry ) ? $entry : array();
		$facts = is_array( $facts ) ? $facts : array();
		return array_merge(
			$entry,
			$facts,
			array(
				'contract_version'                 => self::CONTRACT_VERSION,
				'evidence_kind'                    => 'declared-conditional-contract-target-not-runtime-detection',
				'conditional_activation_required'  => true,
				'runtime_presence_must_be_verified' => true,
				'staging_acceptance_implied'        => false,
				'live_status_implied'               => false,
				'cache_policy'                      => 'native-owner-authoritative-file20-no-cache-truth',
			)
		);
	}

	/**
	 * Publish CF-01..CF-06 as conditional companion contracts.
	 *
	 * No entry below activates a module, grants a capability or creates domain
	 * storage. It only tells File 20 how to remain a safe shell if/when a native
	 * owner is approved and present.
	 */
	public static function harmonize_conditional_modules( $registry ) {
		$registry = is_array( $registry ) ? $registry : array();

		$registry['CF-01'] = self::conditional_target(
			isset( $registry['CF-01'] ) ? $registry['CF-01'] : array(),
			array(
				'owner'            => 'Clinical Records Prescription Follow-Up',
				'plan_version'     => '1.0',
				'native_scope'     => 'clinical-records-encounters-prescriptions-follow-up-consent-break-glass',
				'file20_boundary'  => 'authenticated-shell-route-context-only-no-clinical-data-authorization-prescription-or-break-glass',
				'criticality'      => 'conditional-high-sensitivity',
				'failure_behavior' => 'clinical-surface-unavailable-no-public-fallback',
			)
		);

		$registry['CF-02'] = self::conditional_target(
			isset( $registry['CF-02'] ) ? $registry['CF-02'] : array(),
			array(
				'owner'            => 'Support Appeals Case Management',
				'plan_version'     => '1.0',
				'native_scope'     => 'support-cases-appeals-sla-agent-workspace-case-evidence',
				'file20_boundary'  => 'help-and-case-shell-placement-only-no-case-decision-appeal-outcome-or-domain-write',
				'criticality'      => 'conditional',
				'failure_behavior' => 'private-case-surface-unavailable-public-help-may-remain-native',
			)
		);

		$registry['CF-03'] = self::conditional_target(
			isset( $registry['CF-03'] ) ? $registry['CF-03'] : array(),
			array(
				'owner'                           => 'Payments Donations Financial Operations',
				'plan_version'                    => '2.0',
				'native_scope'                    => 'donations-financial-transparency-payment-ledger-refund-provider-operations-if-approved',
				'file20_boundary'                 => 'route-disclosure-and-accessible-shell-only-no-ledger-price-payment-refund-or-provider-authority',
				'criticality'                     => 'conditional-financial',
				'failure_behavior'                => 'financial-action-unavailable-no-shell-fabricated-success',
				'current_platform_financial_mode' => 'single-free-tier-voluntary-donation-no-donor-advantage',
				'paid_collection_activation'      => 'dormant-unless-new-founder-change-control-and-cf03-activation-evidence',
				'zero_commission'                 => true,
				'donor_advantage'                 => false,
			)
		);

		$registry['CF-04'] = self::conditional_target(
			isset( $registry['CF-04'] ) ? $registry['CF-04'] : array(),
			array(
				'owner'                         => 'Central Media Processing Secure Delivery',
				'plan_version'                  => '1.0',
				'native_scope'                  => 'secure-upload-processing-quarantine-transcode-storage-delivery-grants',
				'file20_boundary'                => 'ui-entry-and-shell-state-only-no-binary-storage-scanning-token-grant-cdn-or-processing-authority',
				'criticality'                    => 'conditional-security-sensitive',
				'failure_behavior'               => 'secure-media-action-unavailable-no-public-url-fallback',
				'token_delivery_shell_behavior' => 'minimal-no-future-shell-client-history-or-prefetch-native-cache-and-range-authority-preserved',
			)
		);

		$registry['CF-05'] = self::conditional_target(
			isset( $registry['CF-05'] ) ? $registry['CF-05'] : array(),
			array(
				'owner'            => 'Analytics Metrics Institutional Intelligence',
				'plan_version'     => '1.0',
				'native_scope'     => 'aggregate-metrics-quality-lineage-access-and-institutional-intelligence',
				'file20_boundary'  => 'authorized-insights-shell-only-no-event-ingestion-warehouse-metric-truth-raw-user-or-clinical-data',
				'criticality'      => 'conditional-private-analytics',
				'failure_behavior' => 'insights-unavailable-no-local-analytics-fallback',
			)
		);

		$registry['CF-06'] = self::conditional_target(
			isset( $registry['CF-06'] ) ? $registry['CF-06'] : array(),
			array(
				'owner'            => 'Localization Translation Operations',
				'plan_version'     => '1.0',
				'native_scope'     => 'locale-registry-translation-projects-bundles-quality-release-operations',
				'file20_boundary'  => 'language-preference-and-direction-shell-surface-only-consume-approved-provider-no-translation-bundle-truth',
				'criticality'      => 'conditional-localization',
				'failure_behavior' => 'language-provider-unavailable-no-fabricated-translation',
			)
		);

		return $registry;
	}

	/**
	 * Privacy-sensitive CF route prefixes.
	 *
	 * Public help, donation/transparency pages, localized public routes and the
	 * CF-04 token-delivery route are deliberately absent. Native owners retain
	 * response/cache truth. This list prevents File 20's local history, prefetch
	 * and PWA conveniences from treating genuinely private routes as public.
	 */
	public static function conditional_private_paths( $paths ) {
		$paths = is_array( $paths ) ? $paths : array();
		$required = array(
			'/clinic/records', '/clinic/patients', '/my-health-record', '/clinic/encounters',
			'/clinic/prescriptions', '/clinic/follow-ups', '/admin/clinical-governance', '/api/clinical/v1',
			'/support/cases', '/support/appeals', '/admin/support', '/api/support/v1',
			'/checkout', '/billing', '/admin/finance', '/api/finance/v1',
			'/api/media/v1', '/admin/media',
			'/insights', '/admin/analytics', '/api/analytics/v1',
			'/settings/language', '/admin/localization', '/api/localization/v1',
		);
		return array_values( array_unique( array_merge( $paths, $required ) ) );
	}

	/** Publish semantic contexts without overriding a native owner's domain state. */
	public static function conditional_layout_contexts( $contexts ) {
		$contexts = is_array( $contexts ) ? $contexts : array();
		$contexts['conditional_clinical_application']     = array( 'mode' => Layout::TWO, 'owner' => 'cf-01' );
		$contexts['conditional_support_application']      = array( 'mode' => Layout::TWO, 'owner' => 'cf-02' );
		$contexts['conditional_financial_application']    = array( 'mode' => Layout::TWO, 'owner' => 'cf-03' );
		$contexts['conditional_media_operations']         = array( 'mode' => Layout::TWO, 'owner' => 'cf-04' );
		$contexts['conditional_analytics_application']    = array( 'mode' => Layout::TWO, 'owner' => 'cf-05' );
		$contexts['conditional_localization_application'] = array( 'mode' => Layout::TWO, 'owner' => 'cf-06' );
		$contexts['conditional_machine_endpoint']         = array( 'mode' => Layout::MINIMAL, 'owner' => 'native-conditional-owner' );
		return $contexts;
	}

	/** Normalize current request to a site-relative lower-case path. */
	private static function current_relative_path() {
		$request = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- path-only classification.
		$path    = wp_parse_url( (string) $request, PHP_URL_PATH );
		$path    = is_string( $path ) && '' !== $path ? '/' . trim( preg_replace( '#/+#', '/', $path ), '/' ) : '/';
		$root    = wp_parse_url( home_url( '/' ), PHP_URL_PATH );
		$root    = is_string( $root ) && '' !== $root ? '/' . trim( preg_replace( '#/+#', '/', $root ), '/' ) : '/';
		$path    = strtolower( '/' === $path ? '/' : untrailingslashit( $path ) );
		$root    = strtolower( '/' === $root ? '/' : untrailingslashit( $root ) );
		if ( '/' !== $root && ( $path === $root || 0 === strpos( $path, $root . '/' ) ) ) {
			$path = substr( $path, strlen( $root ) );
			$path = '' === $path ? '/' : $path;
		}
		return $path;
	}

	/** Prefix matcher for one site-relative path. */
	private static function path_matches( $path, $prefix ) {
		return $path === $prefix || 0 === strpos( $path, $prefix . '/' );
	}

	/**
	 * Machine/token endpoints receive Minimal/no visual shell.
	 *
	 * This is presentation only. Native owners continue to set response bodies,
	 * authorization, signatures, range/cache headers and delivery semantics.
	 */
	public static function force_nonvisual_conditional_routes( $mode, $settings ) {
		unset( $settings );
		$path = self::current_relative_path();
		$nonvisual = array(
			'/api/clinical/v1', '/api/support/v1', '/api/finance/v1', '/api/media/v1',
			'/api/analytics/v1', '/api/localization/v1', '/media/d',
		);
		foreach ( $nonvisual as $prefix ) {
			if ( self::path_matches( $path, $prefix ) ) {
				return Layout::MINIMAL;
			}
		}
		return $mode;
	}

	/** Reconcile UI-only directives with the conditional owners. */
	public static function conditional_ui_contracts( $contracts ) {
		$contracts = is_array( $contracts ) ? $contracts : array();
		$contracts['verified_file_transfer'] = array_merge(
			isset( $contracts['verified_file_transfer'] ) && is_array( $contracts['verified_file_transfer'] ) ? $contracts['verified_file_transfer'] : array(),
			array(
				'owner'                  => 'file-17-cf-04-after-activation',
				'file20_role'            => 'ui-entry-only',
				'backend_owned'          => false,
				'per_file_limit_bytes'   => 1073741824,
				'cf04_activation_needed' => true,
			)
		);
		$contracts['download_manager'] = array_merge(
			isset( $contracts['download_manager'] ) && is_array( $contracts['download_manager'] ) ? $contracts['download_manager'] : array(),
			array(
				'owner'                  => 'native-owners-file24-cf04-after-activation',
				'file20_role'            => 'eligible-download-ui-entry-only',
				'backend_owned'          => false,
				'cf04_activation_needed' => true,
			)
		);
		$contracts['language_direction'] = array(
			'owner'                  => 'file-20-shell-surface-cf-06-locale-provider-after-activation',
			'file20_role'            => 'preference-and-direction-ui-only',
			'backend_owned'          => false,
			'cf06_activation_needed' => true,
			'fallback_behavior'      => 'existing-approved-language-provider-or-honest-unavailable',
		);
		return $contracts;
	}

	/** Publish non-sensitive conditional-module readiness facts. */
	public static function system_check( $sections ) {
		$sections = is_array( $sections ) ? $sections : array();
		$sections['future_shell_v5_seventh_hardening'] = array(
			'label'                          => __( 'Future Shell v5 seventh-pass conditional-module contracts', 'sabri-unified-application-shell' ),
			'contract_version'               => self::CONTRACT_VERSION,
			'conditional_module_count'       => self::CONDITIONAL_COUNT,
			'conditional_modules'            => array( 'CF-01', 'CF-02', 'CF-03', 'CF-04', 'CF-05', 'CF-06' ),
			'evidence_kind'                  => 'declared-conditional-contract-target-not-runtime-detection',
			'activation_authority'           => 'founder-change-control-plus-native-module-gates',
			'native_authorization_preserved' => true,
			'native_cache_truth_preserved'   => true,
			'current_financial_mode'         => 'single-free-tier-voluntary-donation-no-donor-advantage',
			'future_shell_feature_count'     => 18,
			'staging_accepted'               => false,
			'live_deployed'                  => false,
		);
		return $sections;
	}
}
