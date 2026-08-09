<?php
/** Eleventh corrective hardening layer for the second fresh eighty-round audit. */
namespace Sabri\UnifiedShell;
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class FutureShellV5EleventhHardening {
    const CONTRACT_VERSION = '1.0.11';

    public static function register() {
        add_filter( 'sabri_shell_route_result_allowed', array( __CLASS__, 'reject_page_id_collisions' ), PHP_INT_MAX, 5 );
        add_filter( 'sabri_shell_navigation_destinations', array( __CLASS__, 'correct_messages_destination' ), PHP_INT_MAX );
        add_filter( 'sabri_shell_contract_registry', array( __CLASS__, 'correct_foundation_contract_metadata' ), PHP_INT_MAX );
        add_filter( 'pre_update_option_' . Defaults::OPTION_NAME, array( __CLASS__, 'normalize_setting_write' ), PHP_INT_MAX - 3, 3 );
        add_filter( 'option_' . Defaults::OPTION_NAME, array( __CLASS__, 'normalize_setting_read' ), PHP_INT_MAX );
        add_filter( 'default_option_' . Defaults::OPTION_NAME, array( __CLASS__, 'normalize_setting_default' ), PHP_INT_MAX, 3 );
        add_filter( 'register_url', array( __CLASS__, 'block_core_registration_fallback' ), PHP_INT_MAX );
        add_filter( 'sabri_shell_system_check_report', array( __CLASS__, 'correct_messages_diagnostic' ), PHP_INT_MAX - 10 );
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_provider_slot_responsive_fix' ), 220 );
        add_filter( 'sabri_shell_system_check_sections', array( __CLASS__, 'system_check' ), 110 );
    }

    public static function reject_page_id_collisions( $allowed, $key, $url, $source, $destination ) {
        unset( $destination );
        if ( ! $allowed ) { return false; }
        $page_sources = array( 'configured_page_id', 'registered_page_map', 'wordpress_front_page_id', 'posts_page' );
        if ( ! in_array( $source, $page_sources, true ) || ! function_exists( 'url_to_postid' ) ) { return ! in_array( $source, $page_sources, true ); }
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
        if ( $front_id === $page_id && ( 'home' !== $key || ! in_array( $source, array( 'wordpress_front_page_id', 'configured_page_id', 'registered_page_map' ), true ) ) ) { return false; }
        $posts_id = absint( get_option( 'page_for_posts', 0 ) );
        if ( $posts_id === $page_id && 'posts_page' !== $source ) { return false; }
        return (bool) apply_filters( 'sabri_shell_route_page_id_single_owner', true, $key, $page_id, $source );
    }

    public static function correct_messages_destination( $destinations ) {
        if ( is_array( $destinations ) && isset( $destinations['messages'] ) && is_array( $destinations['messages'] ) ) { $destinations['messages']['shortcodes'] = array( 'sabri_messages', 'sabri_communication' ); }
        return $destinations;
    }

    public static function correct_foundation_contract_metadata( $registry ) {
        if ( is_array( $registry ) && isset( $registry['01-B'] ) && is_array( $registry['01-B'] ) ) {
            $registry['01-B']['native_scope'] = 'bootstrap-registry-contracts-activation-shared-conventions';
            $registry['01-B']['file20_boundary'] = 'consume-foundation-registry-no-shell-or-search-truth';
            $registry['01-B']['failure_behavior'] = 'bounded-last-known-or-unavailable';
        }
        return $registry;
    }

    public static function normalize_setting_write( $value, $old_value, $option ) { unset( $old_value, $option ); return self::normalize_setting( $value ); }
    public static function normalize_setting_read( $value ) { return self::normalize_setting( $value ); }
    public static function normalize_setting_default( $value, $option = '', $passed_default = false ) { unset( $option, $passed_default ); return self::normalize_setting( $value ); }

    private static function normalize_setting( $value ) {
        if ( ! is_array( $value ) ) { return $value; }
        if ( isset( $value['navigation']['messages'] ) && is_array( $value['navigation']['messages'] ) ) {
            $shortcode = isset( $value['navigation']['messages']['shortcode'] ) ? sanitize_key( (string) $value['navigation']['messages']['shortcode'] ) : '';
            if ( '' === $shortcode || 'sabri_network' === $shortcode ) { $value['navigation']['messages']['shortcode'] = 'sabri_messages'; }
        }
        if ( isset( $value['left_sidebar']['footer_mappings'] ) && is_array( $value['left_sidebar']['footer_mappings'] ) ) {
            foreach ( $value['left_sidebar']['footer_mappings'] as $key => $url ) { $value['left_sidebar']['footer_mappings'][ $key ] = 'whatsapp' === $key ? self::https_or_relative_url( $url, false ) : self::https_or_relative_url( $url, true ); }
        }
        if ( isset( $value['integrations']['urls'] ) && is_array( $value['integrations']['urls'] ) ) {
            foreach ( $value['integrations']['urls'] as $key => $url ) { $value['integrations']['urls'][ $key ] = 'whatsapp' === $key ? self::https_or_relative_url( $url, false ) : self::https_or_relative_url( $url, true ); }
        }
        return $value;
    }

    private static function https_or_relative_url( $url, $same_site ) {
        $url = trim( (string) $url );
        if ( '' === $url ) { return ''; }
        if ( 0 === strpos( $url, '/' ) && 0 !== strpos( $url, '//' ) ) { return RouteSecurity::sanitize_override( $url ); }
        $clean = esc_url_raw( $url, array( 'https' ) );
        if ( ! $clean || ! wp_http_validate_url( $clean ) ) { return ''; }
        $parts = wp_parse_url( $clean );
        if ( ! is_array( $parts ) || 'https' !== strtolower( isset( $parts['scheme'] ) ? (string) $parts['scheme'] : '' ) || empty( $parts['host'] ) || isset( $parts['user'] ) || isset( $parts['pass'] ) || isset( $parts['fragment'] ) ) { return ''; }
        if ( ! $same_site ) { return $clean; }
        $home = wp_parse_url( home_url( '/' ) );
        if ( ! is_array( $home ) || empty( $home['host'] ) || strtolower( (string) $home['host'] ) !== strtolower( (string) $parts['host'] ) ) { return ''; }
        $home_port = isset( $home['port'] ) ? absint( $home['port'] ) : ( 'https' === strtolower( isset( $home['scheme'] ) ? (string) $home['scheme'] : '' ) ? 443 : 80 );
        $port = isset( $parts['port'] ) ? absint( $parts['port'] ) : 443;
        return $home_port === $port ? $clean : '';
    }

    public static function block_core_registration_fallback( $url ) { unset( $url ); return ''; }

    public static function correct_messages_diagnostic( $rows ) {
        if ( ! is_array( $rows ) ) { return $rows; }
        $map = get_option( 'sn_page_map', array() );
        $dedicated_map = is_array( $map ) && ! empty( $map['messages'] );
        $dedicated = shortcode_exists( 'sabri_messages' ) || shortcode_exists( 'sabri_communication' ) || $dedicated_map || ( class_exists( 'SN_Activator' ) && is_callable( array( 'SN_Activator', 'messages_url' ) ) );
        foreach ( $rows as &$row ) {
            if ( ! is_array( $row ) || 'messages-integration' !== ( isset( $row['id'] ) ? $row['id'] : '' ) ) { continue; }
            $row['value'] = $dedicated ? __( 'Dedicated File 17 Messages surface detected', 'sabri-unified-application-shell' ) : __( 'Dedicated File 17 Messages surface not detected', 'sabri-unified-application-shell' );
            $row['status'] = $dedicated ? 'pass' : 'warn';
            $row['severity'] = $dedicated ? 'info' : 'high';
            $row['evidence'] = 'Dedicated sabri_messages/sabri_communication shortcode, sn_page_map.messages, or SN_Activator::messages_url; generic Network evidence is insufficient.';
            break;
        }
        unset( $row );
        return $rows;
    }

    /** Provider-only Home right slots must not disappear below the desktop breakpoint. */
    public static function enqueue_provider_slot_responsive_fix() {
        if ( ! in_array( Layout::current_mode(), array( Layout::TWO, Layout::THREE ), true ) || ! wp_style_is( 'sabri-shell-central-plan-v4', 'enqueued' ) ) { return; }
        $css = '@media (max-width:1199px){.sabri-shell-right-sidebar-provider:not(.sabri-shell-right-sidebar-drawer){display:block!important;position:static!important;inset:auto!important;inline-size:auto!important;max-block-size:none!important;margin:12px var(--sabri-shell-gap,24px);border:1px solid var(--sabri-shell-border);overflow:visible!important;}}';
        wp_add_inline_style( 'sabri-shell-central-plan-v4', $css );
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
            'file17_messages_diagnostic' => 'dedicated-evidence-required',
            'file01_foundation_scope' => 'registry-contracts-activation-no-search-truth',
            'core_open_registration_fallback' => 'blocked-high-trust-entry-canonical-provider-only',
            'configured_url_policy' => 'internal-relative-or-same-site-https;external-whatsapp-https-only',
            'file21_provider_only_home_right_slot' => 'desktop-right-context-mobile-inline-accessible',
            'approved_feature_count' => 18,
            'staging_accepted' => false,
            'live_deployed' => false,
        );
        return $sections;
    }
}
