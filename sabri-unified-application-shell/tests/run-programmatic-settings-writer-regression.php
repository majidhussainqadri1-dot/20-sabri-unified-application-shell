<?php
/** Regression for the canonical trusted File20 programmatic settings writer. */
declare(strict_types=1);

namespace {
    define( 'ABSPATH', __DIR__ . '/' );
    $GLOBALS['test_options'] = array();
    $GLOBALS['test_filters'] = array();

    function sanitize_key( $value ) { return strtolower( preg_replace( '/[^a-z0-9_\-]/', '', (string) $value ) ); }
    function sanitize_text_field( $value ) { return trim( strip_tags( (string) $value ) ); }
    function sanitize_title( $value ) { return sanitize_key( str_replace( ' ', '-', (string) $value ) ); }
    function sanitize_textarea_field( $value ) { return trim( strip_tags( (string) $value ) ); }
    function absint( $value ) { return abs( (int) $value ); }
    function esc_url_raw( $url, $protocols = null ) { unset( $protocols ); return (string) $url; }
    function wp_http_validate_url( $url ) { return '' !== (string) $url; }

    function callback_id( $callback ) {
        if ( is_array( $callback ) ) { return ( is_object( $callback[0] ) ? get_class( $callback[0] ) : $callback[0] ) . '::' . $callback[1]; }
        return is_string( $callback ) ? $callback : 'closure-' . spl_object_hash( $callback );
    }
    function add_filter( $tag, $callback, $priority = 10, $accepted_args = 1 ) {
        $GLOBALS['test_filters'][ $tag ][ $priority ][ callback_id( $callback ) ] = array( 'function' => $callback, 'accepted_args' => $accepted_args );
        return true;
    }
    function remove_filter( $tag, $callback, $priority = 10 ) {
        $id = callback_id( $callback );
        if ( ! isset( $GLOBALS['test_filters'][ $tag ][ $priority ][ $id ] ) ) { return false; }
        unset( $GLOBALS['test_filters'][ $tag ][ $priority ][ $id ] );
        return true;
    }
    function apply_test_filters( $tag, $value, ...$args ) {
        if ( empty( $GLOBALS['test_filters'][ $tag ] ) ) { return $value; }
        $priorities = array_keys( $GLOBALS['test_filters'][ $tag ] );
        sort( $priorities, SORT_NUMERIC );
        foreach ( $priorities as $priority ) {
            foreach ( $GLOBALS['test_filters'][ $tag ][ $priority ] as $entry ) {
                $all = array_merge( array( $value ), $args );
                $value = call_user_func_array( $entry['function'], array_slice( $all, 0, $entry['accepted_args'] ) );
            }
        }
        return $value;
    }
    function register_setting( $group, $option, $args ) {
        unset( $group );
        if ( ! empty( $args['sanitize_callback'] ) ) { add_filter( 'sanitize_option_' . $option, $args['sanitize_callback'], 10, 1 ); }
    }
    function get_option( $key, $default = false ) { return array_key_exists( $key, $GLOBALS['test_options'] ) ? $GLOBALS['test_options'][ $key ] : $default; }
    function update_option( $key, $value, $autoload = null ) {
        unset( $autoload );
        $old = get_option( $key, false );
        $value = apply_test_filters( 'sanitize_option_' . $key, $value, $key, $old );
        $value = apply_test_filters( 'pre_update_option_' . $key, $value, $old, $key );
        $GLOBALS['test_options'][ $key ] = $value;
        return true;
    }
}

namespace Sabri\UnifiedShell {
    final class Defaults {
        const OPTION_NAME = 'sabri_shell_settings';
        const SCHEMA_VERSION = 5;
        public static function settings() {
            return array(
                'schema_version' => 5,
                'enabled' => true,
                'emergency_disabled' => false,
                'delete_on_uninstall' => false,
                'home_feed' => array( 'retired' => true, 'auto_insert' => false, 'posts_count' => 0 ),
                'header' => array(), 'mobile' => array( 'bottom_nav' => false ), 'integrations' => array(),
                'right_sidebar' => array(),
                'navigation' => array( 'home' => array( 'shortcode' => 'sabri_complete_home_feed' ), 'founder' => array( 'page_id' => 0 ) ),
            );
        }
        public static function destinations() { return array(); }
        public static function groups() { return array(); }
    }
    final class Navigation { public static function invalidate_cache() {} }
    final class Integrations { public static function invalidate_cache() {} }
}

namespace {
    require dirname( __DIR__ ) . '/includes/class-settings.php';
    use Sabri\UnifiedShell\Defaults;
    use Sabri\UnifiedShell\Settings;

    $failures = array();
    $assert = static function ( $condition, $message ) use ( &$failures ) {
        echo ( $condition ? 'PASS: ' : 'FAIL: ' ) . $message . "\n";
        if ( ! $condition ) { $failures[] = $message; }
    };

    $GLOBALS['test_options'][ Defaults::OPTION_NAME ] = Defaults::settings();
    Settings::register();
    add_filter( 'pre_update_option_' . Defaults::OPTION_NAME, array( Settings::class, 'enforce_owned_invariants_filter' ), PHP_INT_MAX - 30, 3 );

    $probe = get_option( Defaults::OPTION_NAME );
    $probe['navigation']['founder']['page_id'] = 164;
    update_option( Defaults::OPTION_NAME, $probe, false );
    $assert( 0 === absint( get_option( Defaults::OPTION_NAME )['navigation']['founder']['page_id'] ?? 0 ), 'Control reproduces the registered tab-oriented sanitizer swallowing a raw programmatic write.' );

    $trusted = get_option( Defaults::OPTION_NAME );
    $trusted['navigation']['founder']['page_id'] = 164;
    $trusted['navigation']['home']['shortcode'] = 'sabri_shell_home_feed';
    Settings::update_programmatically( $trusted );
    $stored = get_option( Defaults::OPTION_NAME );

    $assert( 164 === absint( $stored['navigation']['founder']['page_id'] ?? 0 ), 'Canonical programmatic writer persists the trusted Page-ID mutation.' );
    $assert( 'sabri_complete_home_feed' === ( $stored['navigation']['home']['shortcode'] ?? '' ), 'Canonical programmatic writer enforces File20 ownership invariants before persistence.' );
    $assert( isset( $GLOBALS['test_filters']['sanitize_option_' . Defaults::OPTION_NAME][10]['Sabri\\UnifiedShell\\Settings::sanitize'] ), 'Settings API sanitizer is restored after the bounded trusted write.' );

    $again = $stored;
    $again['navigation']['founder']['page_id'] = 165;
    update_option( Defaults::OPTION_NAME, $again, false );
    $assert( 164 === absint( get_option( Defaults::OPTION_NAME )['navigation']['founder']['page_id'] ?? 0 ), 'Restored sanitizer still protects subsequent raw no-tab writes.' );

    if ( $failures ) { exit( 1 ); }
    echo "\nCanonical File20 programmatic settings writer regression PASS.\n";
}
