<?php
/**
 * Eleventh corrective hardening layer for the second fresh eighty-round audit.
 *
 * This is a compatibility/safety layer only. It does not add a nineteenth
 * Future Shell feature or assume any companion-domain authority.
 *
 * @package SabriUnifiedApplicationShell
 */
namespace Sabri\UnifiedShell;
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class FutureShellV5EleventhHardening {
    const CONTRACT_VERSION = '1.0.11';

    public static function register() {
        add_filter( 'sabri_shell_route_result_allowed', array( __CLASS__, 'reject_page_id_collisions' ), PHP_INT_MAX, 5 );
        add_filter( 'sabri_shell_navigation_destinations', array( __CLASS__, 'correct_messages_destination' ), PHP_INT_MAX );
        add_filter( 'pre_update_option_' . Defaults::OPTION_NAME, array( __CLASS__, 'normalize_messages_setting_write' ), PHP_INT_MAX - 3, 3 );
        add_filter( 'option_' . Defaults::OPTION_NAME, array( __CLASS__, 'normalize_messages_setting_read' ), PHP_INT_MAX );
        add_filter( 'default_option_' . Defaults::OPTION_NAME, array( __CLASS__, 'normalize_messages_default' ), PHP_INT_MAX, 3 );
        /* Current platform law is High-Trust Verified Entry, not WordPress open registration.
         * Canonical File00/File02 signup pages resolve before this core fallback is asked for. */
        add_filter( 'register_url', array( __CLASS__, 'block_core_registration_fallback' ), PHP_INT_MAX );
        add_filter( 'sabri_shell_system_check_sections', array( __CLASS__, 'system_check' ), 110 );
    }

    public static function reject_page_id_collisions( $allowed, $key, $url, $source, $destination ) {
        unset( $destination );
        if ( ! $allowed ) { return false; }
        $page_sources = array( 'configured_page_id', 'registered_page_map', 'wordpress_front_page_id', 'posts_page' );
        if ( ! in_array( $source, $page_sources, true ) ) { return true; }
        if ( ! function_exists( 'url_to_postid' ) ) { return false; }
        $page_id = absint( url_to_postid( $url ) );
        if ( ! $page_id ) { return false; }

        if ( 'messages' === $key && 'registered_page_map' === $source ) {
            $map = get_option( 'sn_page_map', array() );
            $dedicated = is_array( $map ) && ! empty( $map['messages'] ) ? absint( $map['messages'] ) : 0;
            $network_fallback = absint( get_option( 'sn_network_page_id', 0 ) );
            if ( ! $dedicated && $network_fallback && $network_fallback === $page_id ) { return false; }
        }

        return self::page_id_has_single_canonical_claim( sanitize_key( $key ), $page_id, $source );
    }

    private static function page_id_has_single_canonical_claim( $key, $page_id, $source ) {
        $raw = get_option( Defaults::OPTION_NAME, array() );
        $navigation = is_array( $raw ) && isset( $raw['navigation'] ) && is_array( $raw['navigation'] ) ? $raw['navigation'] : array();
        foreach ( array_keys( Defaults::destinations() ) as $other_key ) {
            if ( $other_key === $key ) { continue; }
            if ( isset( $navigation[ $other_key ]['page_id'] ) && absint( $navigation[ $other_key ]['page_id'] ) === $page_id ) { return false; }
            $other_registered = Integrations::page_id( $other_key );
            if ( $other_registered && $other_registered === $page_id ) { return false; }
        }
        $front_id = absint( get_option( 'page_on_front', 0 ) );
        if ( $front_id === $page_id && ( 'home' !== $key || 'wordpress_front_page_id' !== $source && 'configured_page_id' !== $source && 'registered_page_map' !== $source ) ) { return false; }
        $posts_id = absint( get_option( 'page_for_posts', 0 ) );
        if ( $posts_id === $page_id && 'posts_page' !== $source ) { return false; }
        return (bool) apply_filters( 'sabri_shell_route_page_id_single_owner', true, $key, $page_id, $source );
    }

    public static function correct_messages_destination( $destinations ) {
        if ( ! is_array( $destinations ) ) { return $destinations; }
        if ( isset( $destinations['messages'] ) && is_array( $destinations['messages'] ) ) {
            $destinations['messages']['shortcodes'] = array( 'sabri_messages', 'sabri_communication' );
        }
        return $destinations;
    }

    public static function normalize_messages_setting_write( $value, $old_value, $option ) {
        unset( $old_value, $option );
        return self::normalize_messages_setting( $value );
    }

    public static function normalize_messages_setting_read( $value ) { return self::normalize_messages_setting( $value ); }

    public static function normalize_messages_default( $value, $option = '', $passed_default = false ) {
        unset( $option, $passed_default );
        return self::normalize_messages_setting( $value );
    }

    private static function normalize_messages_setting( $value ) {
        if ( ! is_array( $value ) || ! isset( $value['navigation'] ) || ! is_array( $value['navigation'] ) || ! isset( $value['navigation']['messages'] ) || ! is_array( $value['navigation']['messages'] ) ) { return $value; }
        $shortcode = isset( $value['navigation']['messages']['shortcode'] ) ? sanitize_key( (string) $value['navigation']['messages']['shortcode'] ) : '';
        if ( '' === $shortcode || 'sabri_network' === $shortcode ) { $value['navigation']['messages']['shortcode'] = 'sabri_messages'; }
        return $value;
    }

    /** Never advertise generic wp-login.php?action=register as a platform signup route. */
    public static function block_core_registration_fallback( $url ) {
        unset( $url );
        return '';
    }

    public static function system_check( $sections ) {
        $sections = is_array( $sections ) ? $sections : array();
        $sections['future_shell_v5_eleventh_hardening'] = array(
            'label' => __( 'Future Shell v5 eleventh corrective hardening', 'sabri-unified-application-shell' ),
            'contract_version' => self::CONTRACT_VERSION,
            'page_id_collision_policy' => 'all-page-id-sources-single-canonical-owner-fail-closed',
            'file17_messages_shortcode' => 'sabri_messages',
            'file17_network_shortcode' => 'sabri_network',
            'file17_messages_network_page_id_fallback' => 'not-canonical-page-id-evidence',
            'core_open_registration_fallback' => 'blocked-high-trust-entry-canonical-provider-only',
            'approved_feature_count' => 18,
            'staging_accepted' => false,
            'live_deployed' => false,
        );
        return $sections;
    }
}
