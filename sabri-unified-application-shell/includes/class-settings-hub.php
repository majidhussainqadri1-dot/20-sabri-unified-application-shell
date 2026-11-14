<?php
/**
 * V12.3 source-stage Settings Hub: presentation + safe native-owner links only.
 * No new data store, private value read, settings write, REST endpoint or cron.
 *
 * @package SabriUnifiedApplicationShell
 */
namespace Sabri\UnifiedShell;

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class SettingsHub {
    const CONTRACT_VERSION = '12.3.0';
    const SHORTCODE = 'sabri_shell_settings_hub';
    private static $render_sequence = 0;

    /**
     * The 23 V12.3 keys, their public labels, approved source owners and
     * deliberately bounded synonyms. An owner must independently grant each
     * link on each request; the presence of this catalogue is NOT readiness.
     */
    public static function catalogue() {
        return array(
            'account' => array(
                'label' => 'اکاؤنٹ اور شناخت',
                'controls' => array(
                    'account.profile' => array( 'label' => 'میرا پروفائل', 'aliases' => 'profile mera profile', 'owners' => array( 'File03' ) ),
                    'account.membership' => array( 'label' => 'رکنیت اور شناخت', 'aliases' => 'membership identity rukniyat', 'owners' => array( 'File00' ) ),
                    'account.credentials' => array( 'label' => 'پیشہ ورانہ اسناد', 'aliases' => 'credentials degree verification sanad', 'owners' => array( 'File09' ) ),
                ),
            ),
            'security' => array(
                'label' => 'حفاظت اور نشستیں',
                'controls' => array(
                    'security.sessions' => array( 'label' => 'فعال نشستیں', 'aliases' => 'session sessions active nashist', 'owners' => array( 'File02' ) ),
                    'security.passkeys' => array( 'label' => 'پاس کی', 'aliases' => 'passkey passkeys login', 'owners' => array( 'File02' ) ),
                    'security.recovery' => array( 'label' => 'اکاؤنٹ کی بحالی', 'aliases' => 'recovery reset bahali', 'owners' => array( 'File02' ) ),
                ),
            ),
            'privacy' => array(
                'label' => 'رازداری اور ذاتی معلومات',
                'controls' => array(
                    'privacy.consent' => array( 'label' => 'رضامندی', 'aliases' => 'consent razamandi', 'owners' => array( 'File00', 'native-domain' ) ),
                    'privacy.analytics' => array( 'label' => 'تجزیاتی ترجیحات', 'aliases' => 'analytics usage privacy', 'owners' => array( 'CF05', 'native-domain' ) ),
                    'privacy.requests' => array( 'label' => 'ڈیٹا کی درخواستیں', 'aliases' => 'data export delete request', 'owners' => array( 'CF02', 'native-domain' ) ),
                ),
            ),
            'language' => array(
                'label' => 'زبان اور مطالعہ',
                'controls' => array(
                    'language.ui' => array( 'label' => 'انٹرفیس کی زبان', 'aliases' => 'ui language zaban language', 'owners' => array( 'File20', 'CF06' ) ),
                    'language.content' => array( 'label' => 'مواد کی زبان', 'aliases' => 'content language asli matn', 'owners' => array( 'File20', 'CF06' ) ),
                    'language.edition' => array( 'label' => 'اصل اور ترجمہ نسخہ', 'aliases' => 'edition version translation tarjuma', 'owners' => array( 'CF06', 'native-domain' ) ),
                    'language.reading' => array( 'label' => 'مطالعے کی ترجیحات', 'aliases' => 'reading reader progress', 'owners' => array( 'File05', 'File12' ) ),
                ),
            ),
            'display' => array(
                'label' => 'ظاہری صورت اور رسائی',
                'controls' => array(
                    'display.theme' => array( 'label' => 'تھیم اور رنگ', 'aliases' => 'theme dark light system', 'owners' => array( 'File20', 'File25' ) ),
                    'display.text' => array( 'label' => 'متن اور کثافت', 'aliases' => 'text font zoom density', 'owners' => array( 'File20', 'File25' ) ),
                    'display.motion' => array( 'label' => 'حرکت میں کمی', 'aliases' => 'reduced motion animation', 'owners' => array( 'File20', 'File25' ) ),
                    'display.captions' => array( 'label' => 'ویڈیو کے عنوانات', 'aliases' => 'caption captions subtitles', 'owners' => array( 'File10', 'File11' ) ),
                ),
            ),
            'notifications' => array(
                'label' => 'اطلاعات اور توجہ',
                'controls' => array(
                    'notifications.quiet' => array( 'label' => 'خاموش اوقات', 'aliases' => 'khamosh auqat quiet hours silent time', 'owners' => array( 'File19' ) ),
                    'notifications.channels' => array( 'label' => 'اطلاعات کے ذرائع', 'aliases' => 'notification channel push digest mute', 'owners' => array( 'File19' ) ),
                    'notifications.focus' => array( 'label' => 'عارضی توجہ', 'aliases' => 'focus temporary do not disturb', 'owners' => array( 'File19', 'File20' ) ),
                ),
            ),
            'connection' => array(
                'label' => 'رابطہ اور نیٹ ورک',
                'controls' => array(
                    'connection.visibility' => array( 'label' => 'رابطے کی مرئیت', 'aliases' => 'visibility network contact', 'owners' => array( 'File17' ) ),
                    'connection.data' => array( 'label' => 'کم ڈیٹا استعمال', 'aliases' => 'data saver low bandwidth kam data', 'owners' => array( 'File20' ) ),
                    'connection.playback' => array( 'label' => 'میڈیا چلانے کی ترجیحات', 'aliases' => 'autoplay playback quality', 'owners' => array( 'File10', 'File11' ) ),
                ),
            ),
        );
    }

    /** Public device choices do not disclose account membership or private works. */
    private static function guest_key_allowed( $key ) {
        return in_array( $key, array(
            'language.ui', 'language.content', 'display.theme',
            'display.text', 'display.motion', 'connection.data',
        ), true );
    }

    /** Do not accept cross-site links, user info, sensitive query strings or traversal. */
    public static function safe_owner_url( $candidate ) {
        if ( ! is_string( $candidate ) || '' === $candidate ||
            false !== strpos( $candidate, '\\' ) || false !== strpos( $candidate, "\0" ) ) {
            return '';
        }
        $site = wp_parse_url( home_url( '/' ) );
        $url  = wp_parse_url( $candidate );
        if ( ! is_array( $site ) || ! is_array( $url ) ) { return ''; }
        foreach ( array( 'scheme', 'host' ) as $part ) {
            if ( ! isset( $site[ $part ], $url[ $part ] ) ||
                strtolower( (string) $site[ $part ] ) !== strtolower( (string) $url[ $part ] ) ) { return ''; }
        }
        if ( ! in_array( strtolower( (string) $url['scheme'] ), array( 'http', 'https' ), true ) ||
            ( isset( $site['port'] ) ? (int) $site['port'] : 0 ) !== ( isset( $url['port'] ) ? (int) $url['port'] : 0 ) ||
            isset( $url['user'] ) || isset( $url['pass'] ) || isset( $url['query'] ) || isset( $url['fragment'] ) ) {
            return '';
        }
        $path = isset( $url['path'] ) ? (string) $url['path'] : '';
        if ( '' === $path || '/' !== substr( $path, 0, 1 ) ) { return ''; }
        for ( $i = 0; $i < 3; ++$i ) {
            if ( false !== strpos( $path, '//' ) || false !== strpos( $path, '\\' ) || false !== strpos( $path, "\0" ) ||
                preg_match( '#(?:^|/)\.\.(?:/|$)#', $path ) ) { return ''; }
            $next = rawurldecode( $path );
            if ( $next === $path ) { break; }
            $path = $next;
        }
        if ( false !== strpos( $path, '//' ) || false !== strpos( $path, '\\' ) || preg_match( '#(?:^|/)\.\.(?:/|$)#', $path ) ) { return ''; }
        return $candidate;
    }

    /**
     * Optional verified owner projection. Invalid contracts never become links.
     * Real authorization is still the native owner's own endpoint responsibility.
     */
    public static function approved_descriptor( $key, $spec, $user_id ) {
        $raw = apply_filters( 'sabri_shell_v12_3_settings_control', null, $key, $user_id );
        if ( ! is_array( $raw ) || true !== ( $raw['authorized'] ?? false ) ||
            'available' !== ( $raw['status'] ?? '' ) ) { return array(); }
        /* A placeholder cannot impersonate a concrete, versioned canonical owner. */
        if ( 'native-domain' === ( $raw['owner_file'] ?? '' ) || ! in_array( $raw['owner_file'] ?? '', $spec['owners'], true ) ) { return array(); }
        if ( ! isset( $raw['contract_version'] ) || ! is_string( $raw['contract_version'] ) ||
            ! preg_match( '/^[0-9]+\.[0-9]+\.[0-9]+$/D', $raw['contract_version'] ) ) { return array(); }
        if ( 0 === $user_id && ! self::guest_key_allowed( $key ) ) { return array(); }
        $url = self::safe_owner_url( $raw['url'] ?? '' );
        if ( '' === $url ) { return array(); }
        $effective = isset( $raw['effective_state'] ) ? (string) $raw['effective_state'] : '';
        if ( ! in_array( $effective, array( 'on', 'off', 'system', 'pending', 'unknown' ), true ) ) {
            $effective = 'unknown';
        }
        $scope = isset( $raw['scope'] ) ? (string) $raw['scope'] : '';
        if ( ! in_array( $scope, array( 'device', 'account', 'session', 'service' ), true ) ) {
            $scope = 'service';
        }
        return array( 'url' => $url, 'scope' => $scope, 'effective_state' => $effective );
    }

    public static function register() {
        add_shortcode( self::SHORTCODE, array( __CLASS__, 'render' ) );
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'maybe_enqueue_assets' ) );
    }

    /**
     * Prevent late CSS injection for a confirmed ordinary Settings page.
     * Dynamic widgets/page-builders still use the shortcode's lazy fallback.
     */
    public static function maybe_enqueue_assets() {
        if ( ! function_exists( 'is_singular' ) || ! is_singular() || ! function_exists( 'get_post' ) ) { return; }
        $post = get_post();
        if ( is_object( $post ) && isset( $post->post_content ) &&
            function_exists( 'has_shortcode' ) && has_shortcode( (string) $post->post_content, self::SHORTCODE ) ) {
            self::enqueue_assets();
        }
    }

    private static function enqueue_assets() {
        wp_enqueue_style( 'sabri-v12-settings-hub', SABRI_SHELL_URL . 'assets/css/v12-3-settings-hub.css', array(), SABRI_SHELL_VERSION );
        wp_enqueue_script( 'sabri-v12-settings-hub', SABRI_SHELL_URL . 'assets/js/v12-3-settings-hub.js', array(), SABRI_SHELL_VERSION, true );
    }

    public static function render() {
        $user_id = is_user_logged_in() ? (int) get_current_user_id() : 0;
        $unique = (string) ++self::$render_sequence;
        $search_id = 'sabri-v12-settings-search-' . $unique;
        $hint_id = 'sabri-v12-settings-hint-' . $unique;
        $html = '<section class="sabri-v12-settings" data-sabri-v12-settings aria-label="' . esc_attr__( 'Settings', 'sabri-unified-application-shell' ) . '">';
        $html .= '<h2>' . esc_html__( 'Settings', 'sabri-unified-application-shell' ) . '</h2>';
        $html .= '<label for="' . esc_attr( $search_id ) . '">' . esc_html( 'ترتیبات تلاش کریں' ) . '</label>';
        $html .= '<input id="' . esc_attr( $search_id ) . '" data-sabri-v12-settings-search type="search" autocomplete="off" spellcheck="false" aria-describedby="' . esc_attr( $hint_id ) . '" />';
        $html .= '<p id="' . esc_attr( $hint_id ) . '">' . esc_html( 'صرف نظر آنے والے عنوانات تلاش ہوں گے؛ آپ کی تلاش محفوظ نہیں ہوتی۔' ) . '</p>';
        $html .= '<p data-sabri-v12-search-status role="status" aria-live="polite"></p>';
        $html .= '<div class="sabri-v12-settings-layout"><nav aria-label="' . esc_attr( 'ترتیبات کے گروہ' ) . '"><ul>';
        $groups = array();
        foreach ( self::catalogue() as $group => $record ) {
            $rows = array();
            foreach ( $record['controls'] as $key => $spec ) {
                if ( 0 === $user_id && ! self::guest_key_allowed( $key ) ) { continue; }
                $descriptor = self::approved_descriptor( $key, $spec, $user_id );
                /* Unverified private controls are hidden from the DOM and its
                 * local search index, including for unverified logged-in roles. */
                if ( ! self::guest_key_allowed( $key ) && ! $descriptor ) { continue; }
                $text = $spec['label'];
                $search = $text . ' ' . $spec['aliases'] . ' ' . $key;
                $line = '<li class="sabri-v12-control" tabindex="-1" data-sabri-v12-control-key="' . esc_attr( $key ) . '" data-sabri-v12-search-tokens="' . esc_attr( $search ) . '">';
                if ( $descriptor ) {
                    $line .= '<a href="' . esc_url( $descriptor['url'] ) . '">' . esc_html( $text ) . '</a>';
                    $line .= '<small>' . esc_html( $descriptor['scope'] . ' · ' . $descriptor['effective_state'] ) . '</small>';
                } else {
                    $line .= '<span>' . esc_html( $text ) . '</span>';
                    $line .= '<small>' . esc_html( 'اصل سہولت فی الحال غیر مصدقہ یا دستیاب نہیں' ) . '</small>';
                }
                $rows[] = $line . '</li>';
            }
            if ( $rows ) { $groups[ $group ] = array( 'label' => $record['label'], 'rows' => $rows ); }
        }
        foreach ( $groups as $group => $record ) {
            $html .= '<li><a href="#sabri-v12-group-' . esc_attr( $unique . '-' . $group ) . '" data-sabri-v12-group-link="' . esc_attr( $group ) . '">' . esc_html( $record['label'] ) . '</a></li>';
        }
        $html .= '</ul></nav><div class="sabri-v12-settings-detail">';
        foreach ( $groups as $group => $record ) {
            $html .= '<section class="sabri-v12-settings-group" id="sabri-v12-group-' . esc_attr( $unique . '-' . $group ) . '" data-sabri-v12-group="' . esc_attr( $group ) . '">';
            $html .= '<h3>' . esc_html( $record['label'] ) . '</h3><ul>';
            $html .= implode( '', $record['rows'] );
            $html .= '</ul></section>';
        }
        $html .= '</div></div></section>';
        self::enqueue_assets();
        return $html;
    }
}
