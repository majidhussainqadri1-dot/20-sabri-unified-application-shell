<?php
/** Tenth fresh ten-round File 20 corrective hardening layer. */
namespace Sabri\UnifiedShell;
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class FutureShellV5TenthHardening {
    const CONTRACT_VERSION = '1.0.10';

    public static function register() {
        add_filter( 'sabri_shell_navigation_destinations', array( __CLASS__, 'retire_local_feed_destination_source' ), PHP_INT_MAX );

        /* Renderer historically read legacy appearance state. File 25 now owns all visual truth. */
        remove_filter( 'body_class', array( Renderer::class, 'body_classes' ) );
        add_filter( 'body_class', array( __CLASS__, 'structural_body_classes' ), 10 );

        /* Supersede stale sixth-pass File 00 compatibility metadata with the latest reviewed evidence. */
        add_filter( 'sabri_shell_contract_registry', array( __CLASS__, 'harmonize_latest_file00_audit_truth' ), PHP_INT_MAX - 100 );

        NativeContentSlots::register();
        add_filter( 'sabri_shell_system_check_sections', array( __CLASS__, 'system_check' ), 95 );
    }

    /** File 21 exclusively owns the Home/News feed. File 20 never resolves via its retired feed shortcode. */
    public static function retire_local_feed_destination_source( $destinations ) {
        if ( ! is_array( $destinations ) || empty( $destinations['home']['shortcodes'] ) || ! is_array( $destinations['home']['shortcodes'] ) ) {
            return $destinations;
        }
        $destinations['home']['shortcodes'] = array_values(
            array_filter(
                $destinations['home']['shortcodes'],
                static function ( $shortcode ) {
                    return 'sabri_shell_home_feed' !== sanitize_key( (string) $shortcode );
                }
            )
        );
        return $destinations;
    }

    /** Structural File 20 classes only; no color-mode/density ownership or missing legacy-array access. */
    public static function structural_body_classes( $classes ) {
        $classes = is_array( $classes ) ? $classes : array();
        $mode = Layout::current_mode();
        $classes[] = 'sabri-shell-layout-' . sanitize_html_class( (string) $mode );
        if ( Layout::MINIMAL !== $mode ) {
            $classes[] = 'sabri-shell-enabled';
        }
        $settings = Settings::get();
        $layout = isset( $settings['layout'] ) && is_array( $settings['layout'] ) ? $settings['layout'] : array();
        $classes[] = ! empty( $layout['sticky_header'] ) ? 'sabri-shell-sticky-header' : 'sabri-shell-static-header';
        $classes[] = ! empty( $layout['compact_desktop'] ) ? 'sabri-shell-compact-desktop' : 'sabri-shell-standard-desktop';
        return array_values( array_unique( array_filter( array_map( 'strval', $classes ) ) ) );
    }

    /**
     * Record the latest reviewed File 00 evidence without turning static metadata
     * into runtime health or authorization. File 00 remains the native authority.
     */
    public static function harmonize_latest_file00_audit_truth( $registry ) {
        $registry = is_array( $registry ) ? $registry : array();
        $entry = isset( $registry['00'] ) && is_array( $registry['00'] ) ? $registry['00'] : array();
        $registry['00'] = array_merge(
            $entry,
            array(
                'provider_baseline' => 'membership-core-1.2.18-reviewed-head',
                'provider_schema' => '1.3.0',
                'public_membership_contract' => '1.2.0',
                'evidence_kind' => 'external-reviewed-code-audit-not-runtime-health',
                'reviewed_file00_commit' => '3a84c32a6ddad151f2ed09d244fa8aa536a58108',
                'reviewed_finding_counts' => array(
                    'critical' => 13,
                    'high' => 44,
                    'medium' => 17,
                    'low' => 1,
                ),
                'known_external_release_blockers' => true,
                'production_safe_implied' => false,
                'runtime_presence_must_be_verified' => true,
                'runtime_authorization_remains_native' => true,
                'staging_acceptance_implied' => false,
                'live_status_implied' => false,
                'file20_boundary' => 'consume-native-versioned-claims-only-no-membership-identity-mfa-or-trust-write',
            )
        );
        return $registry;
    }

    public static function system_check( $sections ) {
        $sections = is_array( $sections ) ? $sections : array();
        $sections['future_shell_v5_tenth_hardening'] = array(
            'label' => __( 'Future Shell v5 tenth fresh ten-round hardening', 'sabri-unified-application-shell' ),
            'contract_version' => self::CONTRACT_VERSION,
            'approved_feature_count' => 18,
            'file20_local_home_feed_runtime' => 'retired-file21-canonical-owner',
            'file25_visual_runtime_ownership' => 'native-file25-only-file20-structural-classes',
            'file00_latest_reviewed_evidence' => 'runtime-1.2.18/schema-1.3.0/contract-1.2.0/audit-known-blockers',
            'file00_production_safe_implied' => false,
            'file00_runtime_health_must_be_verified' => true,
            'file21_native_slots' => array(
                'sabri_shell_home_before_main',
                'sabri_shell_home_main',
                'sabri_shell_home_after_main',
                'sabri_shell_home_right_sidebar',
                'sabri_shell_news_main',
            ),
            'file21_native_slot_runtime' => 'published-by-native-content-slots',
            'staging_accepted' => false,
            'live_deployed' => false,
        );
        return $sections;
    }
}
