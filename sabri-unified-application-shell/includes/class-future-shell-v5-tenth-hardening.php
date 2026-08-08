<?php
/** Tenth fresh ten-round File 20 corrective hardening layer. */
namespace Sabri\UnifiedShell;
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class FutureShellV5TenthHardening {
    const CONTRACT_VERSION = '1.0.10';

    public static function register() {
        add_filter( 'sabri_shell_navigation_destinations', array( __CLASS__, 'retire_local_feed_destination_source' ), PHP_INT_MAX );
        remove_filter( 'body_class', array( Renderer::class, 'body_classes' ) );
        add_filter( 'body_class', array( __CLASS__, 'structural_body_classes' ), 10 );
        add_filter( 'sabri_shell_contract_registry', array( __CLASS__, 'harmonize_latest_file00_audit_truth' ), PHP_INT_MAX - 100 );
        add_filter( 'sabri_shell_provider_registry', array( __CLASS__, 'bind_actual_provider_versions' ), PHP_INT_MAX - 100 );
        add_filter( 'sabri_shell_search_surface', array( __CLASS__, 'file26_search_surface_only' ), PHP_INT_MAX );
        NativeContentSlots::register();
        add_action( 'rest_api_init', array( __CLASS__, 'replace_health_route' ), PHP_INT_MAX );
        add_filter( 'sabri_shell_system_check_sections', array( __CLASS__, 'system_check' ), 95 );
    }

    public static function retire_local_feed_destination_source( $destinations ) {
        if ( ! is_array( $destinations ) || empty( $destinations['home']['shortcodes'] ) || ! is_array( $destinations['home']['shortcodes'] ) ) { return $destinations; }
        $destinations['home']['shortcodes'] = array_values( array_filter( $destinations['home']['shortcodes'], static function ( $shortcode ) { return 'sabri_shell_home_feed' !== sanitize_key( (string) $shortcode ); } ) );
        return $destinations;
    }

    public static function structural_body_classes( $classes ) {
        $classes = is_array( $classes ) ? $classes : array();
        $mode = Layout::current_mode();
        $classes[] = 'sabri-shell-layout-' . sanitize_html_class( (string) $mode );
        if ( Layout::MINIMAL !== $mode ) { $classes[] = 'sabri-shell-enabled'; }
        $settings = Settings::get();
        $layout = isset( $settings['layout'] ) && is_array( $settings['layout'] ) ? $settings['layout'] : array();
        $classes[] = ! empty( $layout['sticky_header'] ) ? 'sabri-shell-sticky-header' : 'sabri-shell-static-header';
        $classes[] = ! empty( $layout['compact_desktop'] ) ? 'sabri-shell-compact-desktop' : 'sabri-shell-standard-desktop';
        return array_values( array_unique( array_filter( array_map( 'strval', $classes ) ) ) );
    }

    public static function harmonize_latest_file00_audit_truth( $registry ) {
        $registry = is_array( $registry ) ? $registry : array();
        $entry = isset( $registry['00'] ) && is_array( $registry['00'] ) ? $registry['00'] : array();
        $registry['00'] = array_merge( $entry, array(
            'provider_baseline' => 'membership-core-1.2.18-reviewed-head',
            'provider_schema' => '1.3.0',
            'public_membership_contract' => '1.2.0',
            'evidence_kind' => 'external-reviewed-code-audit-not-runtime-health',
            'reviewed_file00_commit' => '3a84c32a6ddad151f2ed09d244fa8aa536a58108',
            'reviewed_finding_counts' => array( 'critical' => 13, 'high' => 44, 'medium' => 17, 'low' => 1 ),
            'known_external_release_blockers' => true,
            'production_safe_implied' => false,
            'runtime_presence_must_be_verified' => true,
            'runtime_authorization_remains_native' => true,
            'staging_acceptance_implied' => false,
            'live_status_implied' => false,
            'file20_boundary' => 'consume-native-versioned-claims-only-no-membership-identity-mfa-or-trust-write',
        ) );
        return $registry;
    }

    public static function bind_actual_provider_versions( $providers ) {
        $providers = is_array( $providers ) ? $providers : array();
        if ( isset( $providers['file-25-visual'] ) && is_array( $providers['file-25-visual'] ) ) {
            $contract = apply_filters( 'sabri_shell_file25_visual_contract', null );
            $providers['file-25-visual']['version'] = is_array( $contract ) && isset( $contract['version'] ) ? sanitize_text_field( (string) $contract['version'] ) : '0.0.0';
            $providers['file-25-visual']['probe'] = array( __CLASS__, 'probe_file25_contract_shape' );
        }
        if ( isset( $providers['file-01b-registry-search'] ) && is_array( $providers['file-01b-registry-search'] ) ) {
            $manifest = apply_filters( 'sabri_platform_foundation_manifest', null );
            $providers['file-01b-registry-search']['version'] = is_array( $manifest ) && isset( $manifest['version'] ) ? sanitize_text_field( (string) $manifest['version'] ) : '0.0.0';
            $providers['file-01b-registry-search']['probe'] = array( __CLASS__, 'probe_file01b_contract_shape' );
        }
        return $providers;
    }

    public static function probe_file25_contract_shape() {
        $contract = apply_filters( 'sabri_shell_file25_visual_contract', null );
        if ( ! is_array( $contract ) || empty( $contract['owner'] ) || empty( $contract['version'] ) ) { return false; }
        $owner = sanitize_key( (string) $contract['owner'] );
        $version = sanitize_text_field( (string) $contract['version'] );
        return in_array( $owner, array( 'file-25', 'sabri-public-experience', 'sabri-unified-global-visual-experience' ), true ) && 1 === preg_match( '/^\d+\.\d+\.\d+$/', $version );
    }

    public static function probe_file01b_contract_shape() {
        $manifest = apply_filters( 'sabri_platform_foundation_manifest', null );
        if ( ! is_array( $manifest ) || empty( $manifest['owner'] ) || empty( $manifest['version'] ) ) { return false; }
        $owner = sanitize_key( (string) $manifest['owner'] );
        $version = sanitize_text_field( (string) $manifest['version'] );
        return 'file-01-b' === $owner && 1 === preg_match( '/^\d+\.\d+\.\d+$/', $version );
    }

    public static function file26_search_surface_only( $surface ) {
        unset( $surface );
        $contract = FourPlanHarmonization::file26_search_contract();
        if ( empty( $contract ) ) { return array( 'owner' => 'file-26', 'url' => '', 'status' => 'unavailable', 'scope' => 'none', 'file20_fallback' => false ); }
        return array(
            'owner' => sanitize_key( (string) $contract['owner'] ), 'url' => esc_url_raw( (string) $contract['url'] ), 'status' => 'available',
            'scope' => 'file-26-federated-search', 'version' => sanitize_text_field( (string) $contract['version'] ),
            'query_param' => sanitize_key( (string) $contract['query_param'] ), 'file20_fallback' => false,
        );
    }

    public static function replace_health_route() {
        if ( function_exists( 'unregister_rest_route' ) ) { unregister_rest_route( 'sabri-shell/v1', '/health' ); }
        register_rest_route( 'sabri-shell/v1', '/health', array(
            'methods' => \WP_REST_Server::READABLE, 'callback' => array( __CLASS__, 'rest_health' ),
            'permission_callback' => array( PlanV4Recovery::class, 'can_manage' ),
        ) );
    }

    public static function rest_health() {
        $providers = PlanV4ContractHealth::health();
        return rest_ensure_response( array(
            'schema' => 'sabri-shell-health/1.1', 'state' => self::authoritative_health_state( $providers ), 'checked_at' => gmdate( 'c' ),
            'audit_chain' => PlanV4Audit::verify_chain() ? 'valid' : 'invalid', 'providers' => $providers,
            'job' => get_option( PlanV4Jobs::STATE, array() ), 'settings_row_version' => PlanV4SettingsConcurrency::current_version(),
            'truth_rule' => 'healthy-only-when-critical-contracts-verified',
        ) );
    }

    /** Safety result for the two contracts without which File 20 cannot claim a healthy privileged shell. */
    public static function critical_health_state( $providers = null ) {
        if ( ! PlanV4Audit::verify_chain() ) { return 'repair_required'; }
        $providers = is_array( $providers ) ? $providers : PlanV4ContractHealth::health();
        foreach ( array( 'file-20-shell', 'file-00-identity' ) as $key ) {
            if ( ! isset( $providers[ $key ] ) || ! is_array( $providers[ $key ] ) ) { return 'unknown'; }
            $state = sanitize_key( (string) ( $providers[ $key ]['state'] ?? 'unknown' ) );
            if ( 'healthy' === $state ) { continue; }
            if ( 'incompatible' === $state ) { return 'incompatible'; }
            if ( in_array( $state, array( 'unknown', 'unavailable', 'stale' ), true ) ) { return 'unknown'; }
            return 'degraded';
        }
        return 'healthy';
    }

    public static function authoritative_health_state( $providers = null ) {
        $providers = is_array( $providers ) ? $providers : PlanV4ContractHealth::health();
        $critical_state = self::critical_health_state( $providers );
        if ( 'healthy' !== $critical_state ) { return $critical_state; }
        foreach ( $providers as $provider ) {
            if ( ! is_array( $provider ) || 'healthy' !== sanitize_key( (string) ( $provider['state'] ?? 'unknown' ) ) ) { return 'degraded'; }
        }
        return 'healthy';
    }

    public static function system_check( $sections ) {
        $sections = is_array( $sections ) ? $sections : array();
        $providers = PlanV4ContractHealth::health();
        $search = self::file26_search_surface_only( array() );
        $sections['future_shell_v5_tenth_hardening'] = array(
            'label' => __( 'Future Shell v5 tenth fresh ten-round hardening', 'sabri-unified-application-shell' ),
            'contract_version' => self::CONTRACT_VERSION, 'approved_feature_count' => 18,
            'file20_local_home_feed_runtime' => 'retired-file21-canonical-owner',
            'file25_visual_runtime_ownership' => 'native-file25-only-file20-structural-classes',
            'file00_latest_reviewed_evidence' => 'runtime-1.2.18/schema-1.3.0/contract-1.2.0/audit-known-blockers',
            'file00_production_safe_implied' => false, 'file00_runtime_health_must_be_verified' => true,
            'file21_native_slots' => array( 'sabri_shell_home_before_main', 'sabri_shell_home_main', 'sabri_shell_home_after_main', 'sabri_shell_home_right_sidebar', 'sabri_shell_news_main' ),
            'file21_native_slot_runtime' => 'published-by-native-content-slots',
            'critical_health_state' => self::critical_health_state( $providers ),
            'authoritative_health_state' => self::authoritative_health_state( $providers ),
            'healthy_truth_rule' => 'all-critical-contracts-must-be-verified',
            'search_surface_owner' => $search['owner'], 'search_surface_status' => $search['status'], 'search_native_fallback' => false,
            'provider_versions_bound_to_native_evidence' => true, 'staging_accepted' => false, 'live_deployed' => false,
        );
        return $sections;
    }
}
