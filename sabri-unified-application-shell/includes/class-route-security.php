<?php
/** Strict validation/quarantine for File 20 route URL overrides. */
namespace Sabri\UnifiedShell;
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class RouteSecurity {
    public static function register() {
        add_filter( 'pre_update_option_' . Defaults::OPTION_NAME, array( __CLASS__, 'sanitize_persisted_overrides' ), PHP_INT_MAX - 20, 3 );
        add_filter( 'sabri_shell_system_check_sections', array( __CLASS__, 'system_check' ), 85 );
    }

    /**
     * Strict route-override contract: relative same-site path or HTTPS URL.
     * Query strings, fragments, credentials, protocol-relative URLs and
     * unauthorized external hosts are rejected.
     */
    public static function sanitize_override( $url ) {
        $url = trim( (string) $url );
        if ( '' === $url ) { return ''; }
        if ( false !== strpos( $url, "\r" ) || false !== strpos( $url, "\n" ) || false !== strpos( $url, '?' ) || false !== strpos( $url, '#' ) ) { return ''; }

        if ( 0 === strpos( $url, '/' ) ) {
            if ( 0 === strpos( $url, '//' ) || false !== strpos( $url, '\\' ) ) { return ''; }
            $path = wp_parse_url( $url, PHP_URL_PATH );
            if ( ! is_string( $path ) || '' === $path || 0 !== strpos( $path, '/' ) ) { return ''; }
            $path = preg_replace( '#/+#', '/', $path );
            return is_string( $path ) && '' !== $path ? $path : '';
        }

        $clean = esc_url_raw( $url, array( 'https' ) );
        if ( ! $clean || ! wp_http_validate_url( $clean ) ) { return ''; }
        $parts = wp_parse_url( $clean );
        if ( ! is_array( $parts ) || 'https' !== strtolower( isset( $parts['scheme'] ) ? (string) $parts['scheme'] : '' ) || empty( $parts['host'] ) ) { return ''; }
        if ( isset( $parts['user'] ) || isset( $parts['pass'] ) || isset( $parts['query'] ) || isset( $parts['fragment'] ) ) { return ''; }

        $host = strtolower( (string) $parts['host'] );
        $home = wp_parse_url( home_url( '/' ) );
        $home_host = is_array( $home ) && ! empty( $home['host'] ) ? strtolower( (string) $home['host'] ) : '';
        $home_port = is_array( $home ) && isset( $home['port'] ) ? absint( $home['port'] ) : 0;
        $port = isset( $parts['port'] ) ? absint( $parts['port'] ) : 0;
        $same_site = '' !== $home_host && hash_equals( $home_host, $host ) && $home_port === $port;

        $allowed = apply_filters( 'sabri_shell_route_override_allowed_hosts', array() );
        $allowed = is_array( $allowed ) ? array_values( array_unique( array_filter( array_map( 'strtolower', array_map( 'strval', $allowed ) ) ) ) ) : array();
        if ( ! $same_site && ! in_array( $host, $allowed, true ) ) { return ''; }

        if ( $same_site && '' === wp_validate_redirect( $clean, '' ) ) { return ''; }
        return $clean;
    }

    /** Quarantine invalid overrides before they can be persisted. */
    public static function sanitize_persisted_overrides( $value, $old_value, $option ) {
        unset( $old_value, $option );
        if ( ! is_array( $value ) || empty( $value['navigation'] ) || ! is_array( $value['navigation'] ) ) { return $value; }
        foreach ( $value['navigation'] as $key => &$config ) {
            if ( ! is_array( $config ) || ! array_key_exists( 'url_override', $config ) ) { continue; }
            $raw = trim( (string) $config['url_override'] );
            $clean = self::sanitize_override( $raw );
            if ( '' !== $raw && '' === $clean && class_exists( __NAMESPACE__ . '\\PlanV4Audit', false ) ) {
                PlanV4Audit::record( 'route_override_rejected', array( 'route_key' => sanitize_key( (string) $key ), 'reason_code' => 'strict-route-override-policy' ) );
            }
            $config['url_override'] = $clean;
        }
        unset( $config );
        return $value;
    }

    public static function system_check( $sections ) {
        $sections = is_array( $sections ) ? $sections : array();
        $sections['route_override_security'] = array(
            'label' => __( 'Route override security', 'sabri-unified-application-shell' ),
            'policy' => 'relative-or-https;same-site-default;external-explicit-allowlist;no-query-fragment-credentials',
            'runtime_revalidation' => true,
        );
        return $sections;
    }
}
