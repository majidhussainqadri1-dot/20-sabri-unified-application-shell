<?php
/** File20 canonical welcome frequency; File25 renders, File13 remains historical. */
namespace Sabri\UnifiedShell;
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class WelcomeFrequency {
    const CONTRACT_VERSION = '1.0.0';
    const PERIOD_SECONDS = 2592000;
    const USER_META = 'sabri_shell_welcome_dismissed_at';
    const GUEST_COOKIE = 'sabri_welcome_dismissed_at';
    private static $prepared = false;
    private static $eligible = false;

    public static function register() {
        add_action( 'wp', array( __CLASS__, 'prepare' ), 98 );
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ), 125 );
        add_action( 'wp_body_open', array( __CLASS__, 'render' ), 2 );
        add_action( 'rest_api_init', array( __CLASS__, 'register_rest' ) );
        add_filter( 'sabri_shell_welcome_frequency_contract', array( __CLASS__, 'contract' ) );
    }

    public static function contract( $existing = array() ) {
        return array_replace( is_array( $existing ) ? $existing : array(), array(
            'owner' => 'file-20', 'version' => self::CONTRACT_VERSION,
            'days' => 30, 'presentation_owner' => 'file-25',
            'guest_preference' => 'first-party-cookie-and-local-storage',
            'member_preference' => 'authenticated-user-meta',
            'no_file13_public_hook' => true, 'live_verified' => false,
        ) );
    }

    /** Never interrupt account, appointment, clinical, deep-link or task routes. */
    public static function eligible_route() {
        if ( ! FourPlanHarmonization::welcome_eligible()
            || SafeMode::constant_disabled() || SafeMode::emergency_disabled()
            || ! function_exists( 'sabri_visual_experience_render_welcome' ) ) {
            return false;
        }
        $uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
        $path = is_string( $uri ) ? wp_parse_url( $uri, PHP_URL_PATH ) : '';
        $base = wp_parse_url( home_url( '/' ), PHP_URL_PATH );
        if ( ! is_string( $path ) || ! is_string( $base ) ) { return false; }
        $path = '/' . trim( $path, '/' );
        $base = '/' . trim( $base, '/' );
        return $path === $base && (bool) apply_filters( 'sabri_shell_welcome_home_eligible', true );
    }

    public static function due( $now, $last ) {
        $now = (int) $now;
        $last = (int) $last;
        return $last <= 0 || $last > $now || ( $now - $last ) >= self::PERIOD_SECONDS;
    }

    public static function prepare() {
        if ( self::$prepared ) { return; }
        self::$prepared = true;
        self::$eligible = false;
        if ( ! self::eligible_route() ) { return; }
        $now = time();
        // An account never inherits a different account's or a guest's
        // browser-wide dismissal. Guest fallback is for guests ONLY.
        $last = is_user_logged_in()
            ? get_user_meta( get_current_user_id(), self::USER_META, true )
            : ( isset( $_COOKIE[ self::GUEST_COOKIE ] ) ? wp_unslash( $_COOKIE[ self::GUEST_COOKIE ] ) : 0 );
        if ( ! ( is_int( $last ) || ( is_string( $last ) && ctype_digit( $last ) ) ) ) { $last = 0; }
        self::$eligible = self::due( $now, $last );
    }

    /** Restrict guest cookies to this WordPress site's base directory. */
    public static function cookie_path() {
        $path = wp_parse_url( home_url( '/' ), PHP_URL_PATH );
        return is_string( $path ) ? '/' . trim( $path, '/' ) . ( '/' === $path ? '' : '/' ) : '/';
    }

    public static function enqueue() {
        if ( ! self::$eligible ) { return; }
        wp_enqueue_style( 'sabri-shell-welcome-owner', SABRI_SHELL_URL . 'assets/css/welcome-frequency.css', array( 'sabri-visual-design-system' ), SABRI_SHELL_VERSION );
        wp_enqueue_script( 'sabri-shell-welcome-owner', SABRI_SHELL_URL . 'assets/js/welcome-frequency.js', array(), SABRI_SHELL_VERSION, true );
        wp_localize_script( 'sabri-shell-welcome-owner', 'sabriWelcomeFrequency', array(
            'loggedIn' => is_user_logged_in(),
            'scope' => is_user_logged_in() ? 'member' : 'guest',
            'siteScope' => md5( home_url( '/' ) ),
            'cookiePath' => self::cookie_path(),
            'seconds' => self::PERIOD_SECONDS,
            'cookieName' => self::GUEST_COOKIE,
            'endpoint' => esc_url_raw( rest_url( 'sabri-shell/v1/welcome-dismiss' ) ),
            'nonce' => is_user_logged_in() ? wp_create_nonce( 'wp_rest' ) : '',
        ) );
    }

    public static function render() {
        if ( ! self::$eligible ) { return; }
        $html = sabri_visual_experience_render_welcome( array(
            'invoked_by_file_20' => true,
            'title' => __( 'Welcome to Sabri Social Homeopathy Platform', 'sabri-unified-application-shell' ),
            'continue_url' => home_url( '/' ),
            'continue_label' => __( 'Continue', 'sabri-unified-application-shell' ),
            'close_label' => __( 'Close', 'sabri-unified-application-shell' ),
            'skip_label' => __( 'Skip intro', 'sabri-unified-application-shell' ),
        ) );
        if ( ! is_string( $html ) || '' === $html ) { return; }
        echo '<aside id="sabri-shell-welcome-host" class="sabri-shell-welcome-host" hidden aria-label="' . esc_attr__( 'Welcome', 'sabri-unified-application-shell' ) . '">';
        echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by File25 canonical renderer.
        echo '</aside>';
    }

    public static function register_rest() {
        register_rest_route( 'sabri-shell/v1', '/welcome-dismiss', array(
            'methods' => 'POST',
            'callback' => array( __CLASS__, 'dismiss' ),
            'permission_callback' => static function () { return is_user_logged_in() && current_user_can( 'read' ); },
        ) );
    }

    public static function dismiss( $request ) {
        $actor = get_current_user_id();
        $nonce = is_object( $request ) && method_exists( $request, 'get_header' ) ? (string) $request->get_header( 'x-wp-nonce' ) : '';
        if ( $actor < 1 || ! is_user_logged_in() || ! current_user_can( 'read' ) || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
            return new \WP_Error( 'sabri_welcome_forbidden', __( 'Welcome preference not authorized.', 'sabri-unified-application-shell' ), array( 'status' => 403 ) );
        }
        $now = time();
        update_user_meta( $actor, self::USER_META, $now );
        $persisted = get_user_meta( $actor, self::USER_META, true );
        if ( (int) $persisted !== $now ) {
            return new \WP_Error( 'sabri_welcome_unavailable', __( 'Welcome preference not saved.', 'sabri-unified-application-shell' ), array( 'status' => 503 ) );
        }
        return rest_ensure_response( array( 'saved' => true, 'next_eligible_at' => $now + self::PERIOD_SECONDS ) );
    }
}
