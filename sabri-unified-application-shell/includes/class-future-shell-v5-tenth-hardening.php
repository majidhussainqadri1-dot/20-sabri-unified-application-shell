<?php
/** Tenth fresh ten-round File 20 corrective hardening layer. */
namespace Sabri\UnifiedShell;
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class FutureShellV5TenthHardening {
    const CONTRACT_VERSION = '1.0.10';

    public static function register() {
        add_filter( 'sabri_shell_navigation_destinations', array( __CLASS__, 'retire_local_feed_destination_source' ), PHP_INT_MAX );
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

    public static function system_check( $sections ) {
        $sections = is_array( $sections ) ? $sections : array();
        $sections['future_shell_v5_tenth_hardening'] = array(
            'label' => __( 'Future Shell v5 tenth fresh ten-round hardening', 'sabri-unified-application-shell' ),
            'contract_version' => self::CONTRACT_VERSION,
            'approved_feature_count' => 18,
            'file20_local_home_feed_runtime' => 'retired-file21-canonical-owner',
            'staging_accepted' => false,
            'live_deployed' => false,
        );
        return $sections;
    }
}
