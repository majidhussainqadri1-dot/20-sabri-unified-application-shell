<?php
/**
 * File 20 Future Shell v5: eighteen progressive application-shell enhancements.
 * Native-domain ownership remains with canonical companion files.
 *
 * @package SabriUnifiedApplicationShell
 */
namespace Sabri\UnifiedShell;
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class FutureShellV5 {
    const CONTRACT_VERSION = '1.0.0';
    const OPTION = 'sabri_shell_future_v5';
    const LKG_OPTION = 'sabri_shell_future_lkg';
    const CIRCUIT_OPTION = 'sabri_shell_future_circuits';
    const CIRCUIT_LOCK_OPTION = 'sabri_shell_future_circuit_lock';
    const CIRCUIT_LOCK_TTL = 10;
    const CRITICAL_OPTION = 'sabri_shell_future_critical_failures';
    const SW_QUERY = 'sabri_shell_future_sw';
    const MANIFEST_QUERY = 'sabri_shell_future_manifest';
    const CIRCUIT_THRESHOLD = 3;
    const CIRCUIT_COOLDOWN = 300;
    private static $restoring = false;

    public static function register() {
        add_action( 'init', array( __CLASS__, 'register_rewrites' ), 2 );
        add_filter( 'query_vars', array( __CLASS__, 'query_vars' ) );
        add_action( 'template_redirect', array( __CLASS__, 'serve_virtual_assets' ), 0 );
        add_action( 'wp_head', array( __CLASS__, 'head_links' ), 4 );
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ), 130 );
        add_action( 'wp_footer', array( __CLASS__, 'render' ), 80 );
        add_filter( 'body_class', array( __CLASS__, 'body_classes' ), 40 );
        add_action( 'rest_api_init', array( __CLASS__, 'register_rest_routes' ) );
        add_action( 'update_option_' . Defaults::OPTION_NAME, array( __CLASS__, 'capture_lkg' ), 30, 2 );
        add_action( 'sabri_shell_module_failure', array( __CLASS__, 'record_module_failure' ), 10, 2 );
        add_action( 'sabri_shell_module_success', array( __CLASS__, 'record_module_success' ), 10, 1 );
        add_filter( 'sabri_shell_module_available', array( __CLASS__, 'filter_module_available' ), 10, 2 );
        add_action( 'sabri_shell_runtime_failure', array( __CLASS__, 'record_critical_failure' ), 10, 1 );
        add_filter( 'sabri_shell_system_check_sections', array( __CLASS__, 'system_check' ), 60 );
    }

    public static function features() {
        return array(
            'command_palette' => 'Global Command Palette',
            'pwa_shell' => 'Installable PWA Application Shell',
            'offline_mode' => 'Intelligent Offline / Weak-Network Mode',
            'data_saver' => 'Low-Data / Data-Saver Mode',
            'recent_resume' => 'Universal Recent & Resume Center',
            'module_circuit_breaker' => 'Module Circuit Breaker',
            'last_known_good' => 'Automatic Last-Known-Good Shell Recovery',
            'performance_guardian' => 'Shell Performance Guardian',
            'smart_navigation' => 'Smart Navigation 2.0',
            'keyboard_accessibility' => 'Universal Keyboard & Accessibility Command Layer',
            'focus_mode' => 'Focus / Reading Shell Mode',
            'split_workspace' => 'Desktop Split Workspace',
            'adaptive_foldable' => 'Foldable / Tablet / Ultra-Wide Adaptive Shell',
            'view_transitions' => 'Progressive View Transitions',
            'predictive_prefetch' => 'Privacy-Safe Predictive Prefetch',
            'language_direction' => 'Universal Language & Direction Quick Control',
            'accessibility_center' => 'Accessibility Preference Center',
            'release_rings' => 'Shell Release Rings & Feature Flags',
        );
    }

    public static function defaults() {
        $features = array();
        foreach ( self::features() as $key => $label ) {
            $features[ $key ] = array( 'ring' => 'general', 'percent' => 100 );
        }
        return array(
            'contract_version' => self::CONTRACT_VERSION,
            'features' => $features,
            'auto_recovery' => true,
            'private_path_fragments' => array( '/messages', '/network', '/appointments', '/security', '/verification', '/account', '/wp-admin', '/wp-login.php' ),
        );
    }

    public static function settings() {
        $saved = get_option( self::OPTION, array() );
        return array_replace_recursive( self::defaults(), is_array( $saved ) ? $saved : array() );
    }

    public static function feature_enabled( $feature ) {
        $feature = sanitize_key( $feature );
        if ( ! isset( self::features()[ $feature ] ) ) { return false; }
        $settings = self::settings();
        $rule = isset( $settings['features'][ $feature ] ) && is_array( $settings['features'][ $feature ] ) ? $settings['features'][ $feature ] : array();
        $ring = isset( $rule['ring'] ) ? sanitize_key( $rule['ring'] ) : 'general';
        $percent = isset( $rule['percent'] ) ? min( 100, max( 0, absint( $rule['percent'] ) ) ) : 100;
        $enabled = false;
        switch ( $ring ) {
            case 'disabled': $enabled = false; break;
            case 'internal': $enabled = is_user_logged_in() && current_user_can( 'manage_options' ); break;
            case 'staging':
                $environment = function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production';
                $enabled = in_array( $environment, array( 'local', 'development', 'staging' ), true );
                break;
            case 'limited':
                if ( is_user_logged_in() ) {
                    $bucket = absint( crc32( (string) get_current_user_id() . '|' . $feature ) ) % 100;
                    $enabled = $bucket < $percent;
                }
                break;
            case 'general': default: $enabled = true; break;
        }
        return (bool) apply_filters( 'sabri_shell_future_feature_enabled', $enabled, $feature, $rule );
    }

    public static function register_rewrites() {
        if ( self::feature_enabled( 'pwa_shell' ) ) {
            add_rewrite_rule( '^sabri-shell-sw\.js$', 'index.php?' . self::SW_QUERY . '=1', 'top' );
            add_rewrite_rule( '^sabri-shell-manifest\.webmanifest$', 'index.php?' . self::MANIFEST_QUERY . '=1', 'top' );
        }
    }

    public static function query_vars( $vars ) {
        $vars[] = self::SW_QUERY;
        $vars[] = self::MANIFEST_QUERY;
        return $vars;
    }

    public static function serve_virtual_assets() {
        if ( get_query_var( self::SW_QUERY ) && self::feature_enabled( 'pwa_shell' ) ) {
            nocache_headers();
            header( 'Content-Type: application/javascript; charset=utf-8' );
            header( 'Service-Worker-Allowed: /' );
            echo self::service_worker_source();
            exit;
        }
        if ( get_query_var( self::MANIFEST_QUERY ) && self::feature_enabled( 'pwa_shell' ) ) {
            nocache_headers();
            header( 'Content-Type: application/manifest+json; charset=utf-8' );
            echo wp_json_encode( self::manifest(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
            exit;
        }
    }

    private static function manifest() {
        $icons = array();
        $site_icon = get_option( 'site_icon' );
        if ( $site_icon ) {
            foreach ( array( 192, 512 ) as $size ) {
                $url = wp_get_attachment_image_url( $site_icon, array( $size, $size ) );
                if ( $url ) { $icons[] = array( 'src' => $url, 'sizes' => $size . 'x' . $size, 'type' => 'image/png', 'purpose' => 'any maskable' ); }
            }
        }
        return array(
            'name' => get_bloginfo( 'name' ) ?: 'Sabri Social Homeopathy Platform',
            'short_name' => 'Sabri Homeopathy',
            'start_url' => home_url( '/' ),
            'scope' => home_url( '/' ),
            'display' => 'standalone',
            'background_color' => '#ffffff',
            'theme_color' => '#16803a',
            'lang' => get_bloginfo( 'language' ) ?: 'en-US',
            'icons' => $icons,
        );
    }

    private static function service_worker_source() {
        $offline = '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Offline</title></head><body><main><h1>Sabri Homeopathy</h1><p>You are offline. Reconnect to continue.</p></main></body></html>';
        return "const CACHE='sabri-shell-v140-static';\nconst OFFLINE=" . wp_json_encode( $offline ) . ";\n" .
            "self.addEventListener('install',e=>self.skipWaiting());\n" .
            "self.addEventListener('activate',e=>e.waitUntil((async()=>{for(const k of await caches.keys()){if(k!==CACHE&&k.indexOf('sabri-shell-')===0)await caches.delete(k);}await self.clients.claim();})()));\n" .
            "self.addEventListener('fetch',e=>{const r=e.request;if(r.method!=='GET')return;const u=new URL(r.url);if(u.origin!==self.location.origin)return;if(/\\/(wp-admin|wp-login\\.php|wp-json|messages|network|appointments|security|verification|account)(\\/|$)/i.test(u.pathname))return;if(r.mode==='navigate'){e.respondWith(fetch(r,{cache:'no-store'}).catch(()=>new Response(OFFLINE,{headers:{'Content-Type':'text/html; charset=utf-8'}})));return;}if(u.pathname.includes('/wp-content/plugins/sabri-unified-application-shell/')){e.respondWith(caches.open(CACHE).then(async c=>{const hit=await c.match(r);if(hit)return hit;const res=await fetch(r);if(res.ok)c.put(r,res.clone());return res;}));}});\n";
    }

    public static function head_links() {
        if ( ! self::feature_enabled( 'pwa_shell' ) ) { return; }
        echo '<link rel="manifest" href="' . esc_url( home_url( '/sabri-shell-manifest.webmanifest' ) ) . '">';
        echo '<meta name="theme-color" content="#16803a">';
    }

    public static function enqueue() {
        if ( is_admin() || ! in_array( Layout::current_mode(), array( Layout::TWO, Layout::THREE ), true ) ) { return; }
        wp_enqueue_style( 'sabri-shell-future-v5', SABRI_SHELL_URL . 'assets/css/future-shell-v5.css', array(), SABRI_SHELL_VERSION );
        wp_enqueue_script( 'sabri-shell-future-v5', SABRI_SHELL_URL . 'assets/js/future-shell-v5.js', array(), SABRI_SHELL_VERSION, true );
        $enabled = array();
        foreach ( self::features() as $key => $label ) { $enabled[ $key ] = self::feature_enabled( $key ); }
        $settings = self::settings();
        $search = self::feature_enabled( 'command_palette' ) ? FourPlanHarmonization::file26_search_contract() : array();
        wp_localize_script( 'sabri-shell-future-v5', 'SabriShellFutureV5', array(
            'version' => SABRI_SHELL_VERSION,
            'homeUrl' => home_url( '/' ),
            'swUrl' => home_url( '/sabri-shell-sw.js' ),
            'features' => $enabled,
            'privatePaths' => array_values( array_map( 'strval', (array) $settings['private_path_fragments'] ) ),
            'search' => $search,
            'strings' => array(
                'offline' => __( 'Offline - reconnect to continue.', 'sabri-unified-application-shell' ),
                'weak' => __( 'Weak network - data-saving behavior is active.', 'sabri-unified-application-shell' ),
                'online' => __( 'Back online.', 'sabri-unified-application-shell' ),
            ),
        ) );
    }

    public static function body_classes( $classes ) {
        if ( ! in_array( Layout::current_mode(), array( Layout::TWO, Layout::THREE ), true ) ) { return $classes; }
        if ( self::feature_enabled( 'adaptive_foldable' ) ) { $classes[] = 'sabri-shell-adaptive-v5'; }
        if ( self::feature_enabled( 'view_transitions' ) ) { $classes[] = 'sabri-shell-view-transitions'; }
        return $classes;
    }

    public static function render() {
        if ( is_admin() || ! in_array( Layout::current_mode(), array( Layout::TWO, Layout::THREE ), true ) ) { return; }
        echo '<div id="sabri-shell-connectivity" class="sabri-shell-connectivity" role="status" aria-live="polite" hidden></div>';
        echo '<dialog id="sabri-shell-command-palette" class="sabri-shell-command-palette" aria-labelledby="sabri-shell-command-title"><form method="dialog"><button class="sabri-shell-dialog-close" value="close" aria-label="' . esc_attr__( 'Close', 'sabri-unified-application-shell' ) . '">&times;</button></form><h2 id="sabri-shell-command-title">' . esc_html__( 'Quick Command', 'sabri-unified-application-shell' ) . '</h2><label class="screen-reader-text" for="sabri-shell-command-input">' . esc_html__( 'Search commands', 'sabri-unified-application-shell' ) . '</label><input id="sabri-shell-command-input" type="search" autocomplete="off" placeholder="' . esc_attr__( 'Search or type a command', 'sabri-unified-application-shell' ) . '"><div id="sabri-shell-command-results" role="listbox"></div></dialog>';
        echo '<dialog id="sabri-shell-accessibility-center" class="sabri-shell-accessibility-center" aria-labelledby="sabri-shell-a11y-title"><form method="dialog"><button class="sabri-shell-dialog-close" value="close" aria-label="' . esc_attr__( 'Close', 'sabri-unified-application-shell' ) . '">&times;</button></form><h2 id="sabri-shell-a11y-title">' . esc_html__( 'Accessibility and Reading Preferences', 'sabri-unified-application-shell' ) . '</h2><div class="sabri-shell-pref-grid"><button type="button" data-sabri-pref="font">' . esc_html__( 'Larger text', 'sabri-unified-application-shell' ) . '</button><button type="button" data-sabri-pref="contrast">' . esc_html__( 'High contrast', 'sabri-unified-application-shell' ) . '</button><button type="button" data-sabri-pref="focus">' . esc_html__( 'Strong focus', 'sabri-unified-application-shell' ) . '</button><button type="button" data-sabri-pref="spacing">' . esc_html__( 'Comfort spacing', 'sabri-unified-application-shell' ) . '</button><button type="button" data-sabri-pref="motion">' . esc_html__( 'Reduce motion', 'sabri-unified-application-shell' ) . '</button><button type="button" data-sabri-pref="data">' . esc_html__( 'Data saver', 'sabri-unified-application-shell' ) . '</button></div><div class="sabri-shell-language-quick"><h3>' . esc_html__( 'Language', 'sabri-unified-application-shell' ) . '</h3>';
        $switcher = Integrations::language_switcher();
        if ( $switcher ) { echo wp_kses_post( $switcher ); } else { echo '<p>' . esc_html__( 'Language provider is not currently available.', 'sabri-unified-application-shell' ) . '</p>'; }
        echo '</div></dialog>';
        echo '<dialog id="sabri-shell-recent-center" class="sabri-shell-recent-center" aria-labelledby="sabri-shell-recent-title"><form method="dialog"><button class="sabri-shell-dialog-close" value="close" aria-label="' . esc_attr__( 'Close', 'sabri-unified-application-shell' ) . '">&times;</button></form><h2 id="sabri-shell-recent-title">' . esc_html__( 'Recent and Resume', 'sabri-unified-application-shell' ) . '</h2><div id="sabri-shell-recent-list"></div><button type="button" data-sabri-clear-recents>' . esc_html__( 'Clear local history', 'sabri-unified-application-shell' ) . '</button></dialog>';
        if ( self::feature_enabled( 'split_workspace' ) && apply_filters( 'sabri_shell_split_workspace_available', false ) ) {
            echo '<aside id="sabri-shell-split-workspace" class="sabri-shell-split-workspace" aria-label="' . esc_attr__( 'Secondary workspace', 'sabri-unified-application-shell' ) . '" hidden><button type="button" data-sabri-split-close aria-label="' . esc_attr__( 'Close secondary workspace', 'sabri-unified-application-shell' ) . '">&times;</button>';
            do_action( 'sabri_shell_split_workspace_render' );
            echo '</aside>';
        }
    }

    public static function capture_lkg( $old_value, $value ) {
        if ( self::$restoring || ! self::feature_enabled( 'last_known_good' ) || ! is_array( $value ) ) { return; }
        $payload = array( 'captured_at' => gmdate( 'c' ), 'plugin_version' => SABRI_SHELL_VERSION, 'settings' => $value );
        $payload['hash'] = hash( 'sha256', wp_json_encode( $payload ) );
        update_option( self::LKG_OPTION, $payload, false );
    }

    public static function restore_lkg( $reason = 'automatic' ) {
        /* All restore entry points, including compatibility callers, must use
         * the same current-version/schema, Emergency-preserving, concurrency-
         * aware transaction as automatic recovery. Never expose the legacy
         * direct update_option restore path. */
        if ( class_exists( __NAMESPACE__ . '\\FutureShellV5ControlGuard', false ) ) {
            return FutureShellV5ControlGuard::restore_current_snapshot( $reason, array( 'source' => 'compatibility-api' ) );
        }
        return false;
    }

    public static function record_module_failure( $module, $context = array() ) {
        if ( ! self::feature_enabled( 'module_circuit_breaker' ) ) { return; }
        $module = sanitize_key( $module ); if ( '' === $module ) { return; }
        $token = self::acquire_circuit_lock();
        if ( '' === $token ) {
            do_action( 'sabri_shell_circuit_lock_contended', array( 'module' => $module ) );
            return;
        }
        try {
            $all = (array) get_option( self::CIRCUIT_OPTION, array() );
            $state = isset( $all[ $module ] ) && is_array( $all[ $module ] ) ? $all[ $module ] : array( 'failures' => 0, 'opened_at' => 0 );
            if ( ! empty( $state['opened_at'] ) && time() - absint( $state['opened_at'] ) > self::CIRCUIT_COOLDOWN ) { $state = array( 'failures' => 0, 'opened_at' => 0 ); }
            $state['failures'] = absint( $state['failures'] ) + 1; $state['last_failure_at'] = time();
            if ( $state['failures'] >= self::CIRCUIT_THRESHOLD ) { $state['opened_at'] = time(); }
            $all[ $module ] = $state; update_option( self::CIRCUIT_OPTION, $all, false );
        } finally {
            self::release_circuit_lock( $token );
        }
        unset( $context );
    }

    public static function record_module_success( $module ) {
        $module = sanitize_key( $module ); if ( '' === $module ) { return; }
        $token = self::acquire_circuit_lock();
        if ( '' === $token ) { return; }
        try {
            $all = (array) get_option( self::CIRCUIT_OPTION, array() );
            if ( isset( $all[ $module ] ) ) { unset( $all[ $module ] ); update_option( self::CIRCUIT_OPTION, $all, false ); }
        } finally {
            self::release_circuit_lock( $token );
        }
    }

    public static function circuit_open( $module ) {
        $module = sanitize_key( $module ); $all = (array) get_option( self::CIRCUIT_OPTION, array() );
        if ( empty( $all[ $module ]['opened_at'] ) ) { return false; }
        if ( time() - absint( $all[ $module ]['opened_at'] ) > self::CIRCUIT_COOLDOWN ) { self::record_module_success( $module ); return false; }
        return true;
    }

    public static function filter_module_available( $available, $module ) {
        return self::feature_enabled( 'module_circuit_breaker' ) && self::circuit_open( $module ) ? false : $available;
    }

    public static function record_critical_failure( $context = array() ) {
        if ( ! self::feature_enabled( 'last_known_good' ) ) { return; }
        $state = (array) get_option( self::CRITICAL_OPTION, array() );
        $now = time(); $events = array();
        foreach ( (array) ( isset( $state['events'] ) ? $state['events'] : array() ) as $stamp ) { if ( $now - absint( $stamp ) <= 300 ) { $events[] = absint( $stamp ); } }
        $events[] = $now; update_option( self::CRITICAL_OPTION, array( 'events' => array_slice( $events, -5 ) ), false );
        $settings = self::settings();
        if ( count( $events ) >= 3 && ! empty( $settings['auto_recovery'] ) && apply_filters( 'sabri_shell_auto_recovery_allowed', true, $context ) ) { self::restore_lkg( 'critical-failure-threshold' ); delete_option( self::CRITICAL_OPTION ); }
    }

    public static function register_rest_routes() {
        register_rest_route( 'sabri-shell/v1', '/future/features', array(
            array( 'methods' => \WP_REST_Server::READABLE, 'callback' => array( __CLASS__, 'rest_features' ), 'permission_callback' => array( __CLASS__, 'can_manage' ) ),
            array( 'methods' => \WP_REST_Server::EDITABLE, 'callback' => array( __CLASS__, 'rest_update_features' ), 'permission_callback' => array( __CLASS__, 'can_manage' ) ),
        ) );
    }
    public static function can_manage() { return is_user_logged_in() && current_user_can( 'manage_options' ); }
    public static function rest_features() { return rest_ensure_response( array( 'schema' => 'sabri-shell-future/1.0', 'features' => self::settings()['features'] ) ); }
    public static function rest_update_features( \WP_REST_Request $request ) {
        $incoming = $request->get_json_params(); $incoming = is_array( $incoming ) ? $incoming : array();
        $settings = self::settings(); $allowed_rings = array( 'disabled', 'internal', 'staging', 'limited', 'general' );
        foreach ( self::features() as $key => $label ) {
            if ( empty( $incoming['features'][ $key ] ) || ! is_array( $incoming['features'][ $key ] ) ) { continue; }
            $rule = $incoming['features'][ $key ]; $ring = isset( $rule['ring'] ) ? sanitize_key( $rule['ring'] ) : 'general';
            if ( ! in_array( $ring, $allowed_rings, true ) ) { $ring = 'general'; }
            $settings['features'][ $key ] = array( 'ring' => $ring, 'percent' => isset( $rule['percent'] ) ? min( 100, max( 0, absint( $rule['percent'] ) ) ) : 100 );
            unset( $label );
        }
        update_option( self::OPTION, $settings, false );
        return rest_ensure_response( array( 'success' => true, 'features' => $settings['features'] ) );
    }

    public static function system_check( $sections ) {
        $sections = (array) $sections;
        $enabled = array(); foreach ( self::features() as $key => $label ) { if ( self::feature_enabled( $key ) ) { $enabled[] = $key; } unset( $label ); }
        $sections['future_shell_v5'] = array(
            'label' => __( 'Future Shell v5 - 18 enhancements', 'sabri-unified-application-shell' ),
            'contract_version' => self::CONTRACT_VERSION,
            'enabled_count' => count( $enabled ),
            'enabled' => $enabled,
            'lkg_available' => (bool) get_option( self::LKG_OPTION, false ),
            'open_circuits' => array_keys( array_filter( (array) get_option( self::CIRCUIT_OPTION, array() ), function ( $state ) { return is_array( $state ) && ! empty( $state['opened_at'] ); } ) ),
            'pwa_manifest' => self::feature_enabled( 'pwa_shell' ) ? home_url( '/sabri-shell-manifest.webmanifest' ) : '',
        );
        return $sections;
    }
}
