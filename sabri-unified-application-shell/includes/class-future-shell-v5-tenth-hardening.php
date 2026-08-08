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

    public static function system_check( $sections ) {
        $sections = is_array( $sections ) ? $sections : array();
        $sections['future_shell_v5_tenth_hardening'] = array(
            'label' => __( 'Future Shell v5 tenth fresh ten-round hardening', 'sabri-unified-application-shell' ),
            'contract_version' => self::CONTRACT_VERSION,
            'approved_feature_count' => 18,
            'file20_local_home_feed_runtime' => 'retired-file21-canonical-owner',
            'file25_visual_runtime_ownership' => 'native-file25-only-file20-structural-classes',
            'staging_accepted' => false,
            'live_deployed' => false,
        );
        return $sections;
    }
}
