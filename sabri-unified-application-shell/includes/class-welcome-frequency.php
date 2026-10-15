<?php
/** File20 canonical welcome frequency; File25 renders, File13 remains historical. */
namespace Sabri\UnifiedShell;
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class WelcomeFrequency {
    const CONTRACT_VERSION = '1.0.0';
    const PERIOD_SECONDS = 2592000;
    const USER_META = 'sabri_shell_welcome_dismissed_at';
    const GUEST_COOKIE = 'sabri_welcome_dismissed_at';
    /** Namespace first-party guest state to one WordPress installation. */
    public static function guest_cookie_name() {
        return self::GUEST_COOKIE . '_' . substr( md5( home_url( '/' ) ), 0, 12 );
    }

    private static $prepared = false;
    private static $eligible = false;
    /** One welcome host per HTTP response even when both theme hooks fire. */
    private static $rendered = false;

    public static function register() {
        add_action( 'wp', array( __CLASS__, 'prepare' ), 98 );
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ), 125 );
        add_action( 'wp_body_open', array( __CLASS__, 'render' ), 2 );
        // A few themes omit wp_body_open; footer priority 2 precedes printed
        // footer scripts, while render() enforces a single canonical host.
        add_action( 'wp_footer', array( __CLASS__, 'render' ), 2 );
        add_action( 'rest_api_init', array( __CLASS__, 'register_rest' ) );
        add_filter( 'sabri_shell_welcome_frequency_contract', array( __CLASS__, 'contract' ) );
        // WordPress core personal-data requests must cover the account preference.
        add_filter( 'wp_privacy_personal_data_exporters', array( __CLASS__, 'register_privacy_exporter' ) );
        add_filter( 'wp_privacy_personal_data_erasers', array( __CLASS__, 'register_privacy_eraser' ) );
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
        // File25 may be installed but fail-closed in its own Safe Mode.
        // Never show a raw/un-styled overlay without its registered contract.
        $visual = apply_filters( 'sabri_shell_file25_visual_contract', array() );
        if ( ! is_array( $visual ) || ( $visual['owner'] ?? '' ) !== 'file-25'
            || empty( $visual['tokens'] ) || ! is_array( $visual['tokens'] ) ) {
            return false;
        }
        $uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
        // Query URLs can be search, REST fallback, appointment or other deep links
        // even when their path is exactly the homepage. Never overlay those tasks.
        if ( ! is_string( $uri ) || false === wp_parse_url( $uri ) ) { return false; }
        $query = wp_parse_url( $uri, PHP_URL_QUERY );
        if ( is_string( $query ) && '' !== $query ) { return false; }
        $path = wp_parse_url( $uri, PHP_URL_PATH );
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
        self::$rendered = false;
        if ( ! self::eligible_route() ) { return; }
        $now = time();
        // An account never inherits a different account's or a guest's
        // browser-wide dismissal. Guest fallback is for guests ONLY.
        $cookie_name = self::guest_cookie_name();
        $last = is_user_logged_in()
            ? get_user_meta( get_current_user_id(), self::USER_META, true )
            : ( isset( $_COOKIE[ $cookie_name ] ) ? wp_unslash( $_COOKIE[ $cookie_name ] ) : 0 );
        if ( ! ( is_int( $last ) || ( is_string( $last ) && ctype_digit( $last ) ) ) ) { $last = 0; }
        self::$eligible = self::due( $now, $last );
    }

    /** Restrict guest cookies to this WordPress site's base directory. */
    public static function cookie_path() {
        $path = wp_parse_url( home_url( '/' ), PHP_URL_PATH );
        return is_string( $path ) ? '/' . trim( $path, '/' ) . ( '/' === $path ? '' : '/' ) : '/';
    }

    /** An opaque login-session partition; never write a raw user ID to storage. */
    public static function member_session_scope() {
        if ( ! is_user_logged_in() ) { return 'guest'; }
        $token = function_exists( 'wp_get_session_token' ) ? (string) wp_get_session_token() : '';
        if ( '' === $token ) {
            $token = (string) wp_create_nonce( 'sabri-welcome-session-partition' );
        }
        return 'member-' . substr( wp_hash( (string) get_current_user_id() . '|' . $token, 'nonce' ), 0, 24 );
    }

    public static function enqueue() {
        if ( ! self::$eligible ) { return; }
        wp_enqueue_style( 'sabri-shell-welcome-owner', SABRI_SHELL_URL . 'assets/css/welcome-frequency.css', array( 'sabri-visual-design-system' ), SABRI_SHELL_VERSION );
        wp_enqueue_script( 'sabri-shell-welcome-owner', SABRI_SHELL_URL . 'assets/js/welcome-frequency.js', array(), SABRI_SHELL_VERSION, true );
        wp_localize_script( 'sabri-shell-welcome-owner', 'sabriWelcomeFrequency', array(
            'loggedIn' => is_user_logged_in(),
            'scope' => self::member_session_scope(),
            'siteScope' => md5( home_url( '/' ) ),
            'cookiePath' => self::cookie_path(),
            'seconds' => self::PERIOD_SECONDS,
            'cookieName' => self::guest_cookie_name(),
            'homeUrl' => esc_url_raw( home_url( '/' ) ),
            'endpoint' => esc_url_raw( rest_url( 'sabri-shell/v1/welcome-dismiss' ) ),
            'nonce' => is_user_logged_in() ? wp_create_nonce( 'wp_rest' ) : '',
        ) );
    }

    public static function render() {
        if ( ! self::$eligible || self::$rendered ) { return; }
        $html = sabri_visual_experience_render_welcome( array(
            'invoked_by_file_20' => true,
            'title' => __( 'Welcome to Sabri Social Homeopathy Platform', 'sabri-unified-application-shell' ),
            // Central governing identity, NOT the separately review-gated medical cure hero claim.
            'message' => __( 'The Tridimensional Healing System of Soul, Vital Force, and Matter', 'sabri-unified-application-shell' ),
            'continue_url' => home_url( '/' ),
            'continue_label' => __( 'Continue', 'sabri-unified-application-shell' ),
            'close_label' => __( 'Close', 'sabri-unified-application-shell' ),
            'skip_label' => __( 'Skip intro', 'sabri-unified-application-shell' ),
        ) );
        if ( ! is_string( $html ) || '' === $html ) { return; }
        self::$rendered = true;
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
        // Replayed/duplicated dismiss requests must not perpetually extend
        // the 30-day hold or generate unnecessary user-meta writes.
        $prior = get_user_meta( $actor, self::USER_META, true );
        if ( ( is_int( $prior ) || ( is_string( $prior ) && ctype_digit( $prior ) ) )
            && ! self::due( $now, $prior ) ) {
            return rest_ensure_response( array( 'saved' => true, 'next_eligible_at' => (int) $prior + self::PERIOD_SECONDS ) );
        }
        update_user_meta( $actor, self::USER_META, $now );
        $persisted = get_user_meta( $actor, self::USER_META, true );
        if ( (int) $persisted !== $now ) {
            return new \WP_Error( 'sabri_welcome_unavailable', __( 'Welcome preference not saved.', 'sabri-unified-application-shell' ), array( 'status' => 503 ) );
        }
        return rest_ensure_response( array( 'saved' => true, 'next_eligible_at' => $now + self::PERIOD_SECONDS ) );
    }

    /** Register only the File20-owned welcome preference with WordPress privacy tools. */
    public static function register_privacy_exporter( $exporters ) {
        if ( ! is_array( $exporters ) ) { $exporters = array(); }
        $exporters['sabri-shell-welcome-preference'] = array(
            'exporter_friendly_name' => __( 'Sabri Welcome Preference', 'sabri-unified-application-shell' ),
            'callback' => array( __CLASS__, 'export_personal_data' ),
        );
        return $exporters;
    }

    public static function register_privacy_eraser( $erasers ) {
        if ( ! is_array( $erasers ) ) { $erasers = array(); }
        $erasers['sabri-shell-welcome-preference'] = array(
            'eraser_friendly_name' => __( 'Sabri Welcome Preference', 'sabri-unified-application-shell' ),
            'callback' => array( __CLASS__, 'erase_personal_data' ),
        );
        return $erasers;
    }

    /** @return array{data:array,done:bool} */
    public static function export_personal_data( $email_address, $page = 1 ) {
        $empty = array( 'data' => array(), 'done' => true );
        if ( ! is_string( $email_address ) || '' === $email_address || ! function_exists( 'get_user_by' ) ) {
            return $empty;
        }
        $user = get_user_by( 'email', $email_address );
        if ( ! is_object( $user ) || empty( $user->ID ) ) { return $empty; }
        $timestamp = get_user_meta( (int) $user->ID, self::USER_META, true );
        if ( ! ( is_int( $timestamp ) || ( is_string( $timestamp ) && ctype_digit( $timestamp ) ) ) || (int) $timestamp <= 0 ) {
            return $empty;
        }
        return array(
            'data' => array(
                array(
                    'group_id' => 'sabri-welcome-preference',
                    'group_label' => __( 'Welcome Preference', 'sabri-unified-application-shell' ),
                    'item_id' => 'sabri-welcome-last-dismissed',
                    'data' => array(
                        array(
                            'name' => __( 'Last Welcome dismissal (UTC)', 'sabri-unified-application-shell' ),
                            'value' => gmdate( 'Y-m-d H:i:s', (int) $timestamp ) . ' UTC',
                        ),
                    ),
                ),
            ),
            'done' => true,
        );
    }

    /** @return array{items_removed:bool,items_retained:bool,messages:array,done:bool} */
    public static function erase_personal_data( $email_address, $page = 1 ) {
        $empty = array( 'items_removed' => false, 'items_retained' => false, 'messages' => array(), 'done' => true );
        if ( ! is_string( $email_address ) || '' === $email_address || ! function_exists( 'get_user_by' ) ) {
            return $empty;
        }
        $user = get_user_by( 'email', $email_address );
        if ( ! is_object( $user ) || empty( $user->ID ) ) { return $empty; }
        $actor = (int) $user->ID;
        if ( '' === get_user_meta( $actor, self::USER_META, true ) ) { return $empty; }
        // File24/legal policy may explicitly put this datum on a hold.
        if ( (bool) apply_filters( 'sabri_shell_welcome_erasure_hold', false, $actor ) ) {
            return array(
                'items_removed' => false, 'items_retained' => true,
                'messages' => array( __( 'Welcome preference retained under an applicable hold.', 'sabri-unified-application-shell' ) ),
                'done' => true,
            );
        }
        delete_user_meta( $actor, self::USER_META );
        if ( '' !== get_user_meta( $actor, self::USER_META, true ) ) {
            return array(
                'items_removed' => false, 'items_retained' => true,
                'messages' => array( __( 'Welcome preference could not be removed.', 'sabri-unified-application-shell' ) ),
                'done' => true,
            );
        }
        return array( 'items_removed' => true, 'items_retained' => false, 'messages' => array(), 'done' => true );
    }
}
