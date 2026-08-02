<?php
/** Private route indexing and shared-cache protection. */
namespace Sabri\UnifiedShell;
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class PlanV4PrivacyCache {
    public static function register() {
        add_action( 'send_headers', array( __CLASS__, 'send_private_headers' ), 0 );
        add_filter( 'wp_robots', array( __CLASS__, 'robots' ) );
        add_action( 'update_option_sabri_shell_settings', array( __CLASS__, 'purge' ) );
        add_action( 'update_option_permalink_structure', array( __CLASS__, 'purge' ) );
    }

    public static function is_private_request() {
        $path = isset( $_SERVER['REQUEST_URI'] ) ? strtolower( (string) wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ) ) : '';
        $private = (bool) preg_match( '#/(publishing-dashboard|security-center|membership-status|account-security|guardian|verification|settings|safe-mode|repair)(/|$)#', $path );
        if ( is_user_logged_in() && apply_filters( 'sabri_shell_personalized_request', false ) ) {
            $private = true;
        }
        return (bool) apply_filters( 'sabri_shell_private_request', $private, $path );
    }

    public static function send_private_headers() {
        if ( ! self::is_private_request() ) {
            return;
        }
        nocache_headers();
        header( 'Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0', true );
        header( 'Pragma: no-cache', true );
        header( 'X-Robots-Tag: noindex, noarchive, nosnippet', true );
        header( 'Vary: Cookie', false );
    }

    public static function robots( $robots ) {
        if ( self::is_private_request() ) {
            $robots['noindex'] = true;
            $robots['noarchive'] = true;
            $robots['nosnippet'] = true;
        }
        return $robots;
    }

    public static function purge() {
        PlanV4ContractHealth::invalidate();
        if ( function_exists( 'wp_cache_flush' ) ) {
            wp_cache_flush();
        }
        do_action( 'litespeed_purge_all' );
        PlanV4Audit::record( 'cache_purge_requested', array( 'source' => current_filter() ) );
    }
}

PlanV4PrivacyCache::register();
