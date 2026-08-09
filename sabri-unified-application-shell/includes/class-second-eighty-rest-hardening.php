<?php
/** Sensitive REST response cache hardening for the second eighty-round audit. */
namespace Sabri\UnifiedShell;
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class SecondEightyRestHardening {
    public static function register() {
        add_filter( 'rest_post_dispatch', array( __CLASS__, 'no_store_sensitive_file20_rest' ), PHP_INT_MAX, 3 );
    }

    public static function no_store_sensitive_file20_rest( $response, $server, $request ) {
        unset( $server );
        if ( ! is_object( $request ) || ! is_callable( array( $request, 'get_route' ) ) ) { return $response; }
        $route = (string) $request->get_route();
        $sensitive = array(
            '/sabri-shell/v1/health',
            '/sabri-shell/v1/repair/preview',
            '/sabri-shell/v1/repair/execute',
            '/sabri-shell/v1/rollback/preview',
            '/sabri-shell/v1/rollback/execute',
            '/sabri-shell/v1/system-check/export',
        );
        if ( ! in_array( $route, $sensitive, true ) ) { return $response; }
        $response = rest_ensure_response( $response );
        if ( is_object( $response ) && is_callable( array( $response, 'header' ) ) ) {
            $response->header( 'Cache-Control', 'private, no-store, max-age=0' );
            $response->header( 'Pragma', 'no-cache' );
            $response->header( 'X-Robots-Tag', 'noindex, noarchive' );
        }
        return $response;
    }
}
