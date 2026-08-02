<?php
/** Deterministic layout context evidence: mode, reason, source, route and timestamp. */
namespace Sabri\UnifiedShell;
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class PlanV4Context {
    public static function register() {
        add_filter( 'body_class', array( __CLASS__, 'body_classes' ) );
        add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
    }

    public static function describe() {
        $settings = class_exists( __NAMESPACE__ . '\\Settings', false ) ? Settings::get() : array();
        $page_id = class_exists( __NAMESPACE__ . '\\Layout', false ) ? Layout::current_page_id() : 0;
        $mode = class_exists( __NAMESPACE__ . '\\Layout', false ) ? Layout::current_mode() : 'minimal';
        $reason = 'default_public_safe';
        $source = 'file-20-resolver';

        if ( class_exists( __NAMESPACE__ . '\\SafeMode', false ) && SafeMode::disabled() ) {
            $reason = 'safe_mode';
        } elseif ( class_exists( __NAMESPACE__ . '\\Layout', false ) && Layout::is_excluded_request() ) {
            $reason = 'task_or_machine_request';
        } elseif ( $page_id && isset( $settings['layout']['per_page_overrides'][ $page_id ] ) && 'default' !== $settings['layout']['per_page_overrides'][ $page_id ] ) {
            $reason = 'validated_page_override';
        } elseif ( class_exists( __NAMESPACE__ . '\\Layout', false ) && Layout::is_immersive_context( $settings, $page_id ) ) {
            $reason = 'native_immersive_context';
        } elseif ( class_exists( __NAMESPACE__ . '\\Layout', false ) && Layout::is_three_column_context( $settings, $page_id ) ) {
            $reason = 'canonical_three_column_context';
        }

        $path = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ) : '/';
        $route_key = apply_filters( 'sabri_shell_current_route_key', '' );
        if ( ! is_string( $route_key ) || '' === $route_key ) {
            $segments = array_values( array_filter( explode( '/', trim( strtolower( $path ), '/' ) ) ) );
            $route_key = $segments ? sanitize_key( reset( $segments ) ) : 'home';
        }

        $descriptor = array(
            'schema' => 'sabri-shell-context/1.0',
            'mode' => sanitize_key( $mode ),
            'reason' => sanitize_key( $reason ),
            'source' => sanitize_key( $source ),
            'route_key' => sanitize_key( $route_key ),
            'page_id' => absint( $page_id ),
            'resolved_at' => gmdate( 'c' ),
        );
        return apply_filters( 'sabri_shell_context_descriptor', $descriptor );
    }

    public static function body_classes( $classes ) {
        $descriptor = self::describe();
        $classes[] = 'sabri-shell-route-' . sanitize_html_class( $descriptor['route_key'] );
        $classes[] = 'sabri-shell-context-evidenced';
        return array_values( array_unique( $classes ) );
    }

    public static function register_routes() {
        register_rest_route( 'sabri-shell/v1', '/context', array(
            'methods' => \WP_REST_Server::READABLE,
            'callback' => array( __CLASS__, 'rest_context' ),
            'permission_callback' => '__return_true',
        ) );
    }

    public static function rest_context() {
        $descriptor = self::describe();
        unset( $descriptor['page_id'] );
        return rest_ensure_response( $descriptor );
    }
}

PlanV4Context::register();
