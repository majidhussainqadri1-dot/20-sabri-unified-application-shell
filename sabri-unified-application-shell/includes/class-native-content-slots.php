<?php
/** Native File 20 presentation slots consumed by File 21 and approved providers. */
namespace Sabri\UnifiedShell;
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class NativeContentSlots {
    private static $home_content_dispatched = false;
    private static $news_content_dispatched = false;
    private static $shell_capture_level = 0;

    public static function register() {
        add_filter( 'the_content', array( __CLASS__, 'mount_main_slots' ), 8 );
        add_action( 'wp_body_open', array( __CLASS__, 'begin_shell_capture' ), 4 );
        add_action( 'wp_body_open', array( __CLASS__, 'end_shell_capture' ), 6 );
    }

    /** Mount canonical Home/News slots exactly once; legacy page content is fallback, never a second authoritative renderer. */
    public static function mount_main_slots( $content ) {
        if ( is_admin() || ! is_string( $content ) || ! in_the_loop() || ! is_main_query() ) {
            return $content;
        }
        if ( Layout::MINIMAL === Layout::current_mode() || Layout::IMMERSIVE === Layout::current_mode() ) {
            return $content;
        }

        if ( self::is_home_request() && ! self::$home_content_dispatched ) {
            self::$home_content_dispatched = true;
            $before = self::capture_action( 'sabri_shell_home_before_main' );
            $main   = self::capture_action( 'sabri_shell_home_main' );
            $after  = self::capture_action( 'sabri_shell_home_after_main' );
            $authoritative = '' !== trim( $main ) ? $main : $content;
            return $before . $authoritative . $after;
        }

        if ( self::is_news_request() && ! self::$news_content_dispatched ) {
            self::$news_content_dispatched = true;
            $main = self::capture_action( 'sabri_shell_news_main' );
            return '' !== trim( $main ) ? $main : $content;
        }

        return $content;
    }

    /** Capture only when File 20 has actually enabled the structural Home right-sidebar surface. */
    public static function begin_shell_capture() {
        $settings = Settings::get();
        if (
            ! self::is_home_request()
            || Layout::THREE !== Layout::current_mode()
            || empty( $settings['right_sidebar']['enabled'] )
            || ! has_action( 'sabri_shell_home_right_sidebar' )
        ) {
            return;
        }
        self::$shell_capture_level = ob_get_level() + 1;
        ob_start();
    }

    /** Insert the provider slot in the existing Home right sidebar, or emit a bounded structural provider aside. */
    public static function end_shell_capture() {
        if ( self::$shell_capture_level <= 0 || ob_get_level() < self::$shell_capture_level ) {
            self::$shell_capture_level = 0;
            return;
        }
        $buffer = (string) ob_get_clean();
        self::$shell_capture_level = 0;
        $slot = self::capture_action( 'sabri_shell_home_right_sidebar' );
        if ( '' === trim( $slot ) ) {
            echo $buffer; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- captured File 20 renderer output.
            return;
        }

        $marker = 'data-sabri-right-context="home"';
        $marker_pos = strpos( $buffer, $marker );
        if ( false !== $marker_pos ) {
            $close_pos = strpos( $buffer, '</aside>', $marker_pos );
            if ( false !== $close_pos ) {
                $buffer = substr( $buffer, 0, $close_pos )
                    . '<div class="sabri-shell-native-slot sabri-shell-native-slot-home-right" data-sabri-shell-slot="sabri_shell_home_right_sidebar">'
                    . $slot
                    . '</div>'
                    . substr( $buffer, $close_pos );
                echo $buffer; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- captured renderer + approved provider slot output.
                return;
            }
        }

        /* This branch is only reached when File 20 enabled the structural sidebar
         * but no built-in module caused Renderer to emit its ordinary container. */
        echo $buffer; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- captured File 20 renderer output.
        echo '<aside class="sabri-shell-right-sidebar sabri-shell-right-sidebar-provider" aria-label="' . esc_attr__( 'Home context', 'sabri-unified-application-shell' ) . '" data-sabri-shell-component="right-sidebar" data-sabri-right-context="home"><div class="sabri-shell-native-slot sabri-shell-native-slot-home-right" data-sabri-shell-slot="sabri_shell_home_right_sidebar">' . $slot . '</div></aside>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- approved provider owns slot markup; shell owns containment.
    }

    private static function capture_action( $hook ) {
        if ( ! has_action( $hook ) ) { return ''; }
        ob_start();
        try {
            do_action( $hook );
            return (string) ob_get_clean();
        } catch ( \Throwable $error ) {
            ob_end_clean();
            if ( class_exists( __NAMESPACE__ . '\\PlanV4Audit', false ) ) {
                PlanV4Audit::record( 'native_slot_provider_failure', array( 'slot' => sanitize_key( $hook ), 'exception_class' => sanitize_text_field( get_class( $error ) ) ) );
            }
            return '';
        }
    }

    private static function is_home_request() {
        if ( function_exists( 'is_front_page' ) && is_front_page() ) { return true; }
        $home_id = Integrations::page_id( 'home' );
        return $home_id > 0 && function_exists( 'get_queried_object_id' ) && absint( get_queried_object_id() ) === $home_id;
    }

    private static function is_news_request() {
        if ( function_exists( 'is_post_type_archive' ) && is_post_type_archive( 'snp_publication' ) ) { return true; }
        $news_id = Integrations::page_id( 'news' );
        if ( $news_id > 0 && function_exists( 'get_queried_object_id' ) && absint( get_queried_object_id() ) === $news_id ) { return true; }
        return function_exists( 'is_home' ) && is_home() && ! self::is_home_request();
    }
}
