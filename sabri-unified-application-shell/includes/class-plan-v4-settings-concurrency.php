<?php
/** Optimistic concurrency control for File 20 settings. */
namespace Sabri\UnifiedShell;
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class PlanV4SettingsConcurrency {
    const VERSION_OPTION = 'sabri_shell_settings_row_version';
    const LOCK_OPTION = 'sabri_shell_settings_update_lock';
    const LOCK_TTL = 30;
    private static $accepted_update = false;
    private static $lock_token = '';

    public static function register() {
        foreach ( array( 'sabri_shell_settings', 'sabri_unified_shell_settings' ) as $option ) {
            add_filter( 'pre_update_option_' . $option, array( __CLASS__, 'pre_update' ), 10, 3 );
        }
        add_action( 'updated_option', array( __CLASS__, 'after_update' ), 10, 3 );
        add_action( 'shutdown', array( __CLASS__, 'release_update_lock' ), PHP_INT_MAX );
        add_filter( 'sabri_shell_settings_row_version', array( __CLASS__, 'current_version' ) );
    }

    public static function current_version() { return max( 1, absint( get_option( self::VERSION_OPTION, 1 ) ) ); }

    public static function pre_update( $new_value, $old_value, $option ) {
        self::$accepted_update = false;
        if ( ! is_admin() || ! current_user_can( 'manage_options' ) || empty( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) ) { return $new_value; }
        $option_page = isset( $_POST['option_page'] ) ? sanitize_key( wp_unslash( $_POST['option_page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Settings API verifies the nonce.
        if ( 'sabri_shell_settings' !== $option_page ) { return $new_value; }

        if ( '' === self::$lock_token ) {
            self::$lock_token = self::acquire_update_lock();
        }
        if ( '' === self::$lock_token ) {
            add_settings_error( 'sabri_shell_settings', 'sabri_shell_settings_locked', __( 'Another File 20 settings update is in progress. Reload and try again.', 'sabri-unified-application-shell' ), 'error' );
            PlanV4Audit::record( 'settings_conflict', array( 'expected_version' => 'lock-contended', 'current_version' => self::current_version(), 'reason' => 'concurrent-settings-write' ) );
            return $old_value;
        }

        if ( ! isset( $_POST['sabri_shell_settings_row_version'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
            add_settings_error( 'sabri_shell_settings', 'sabri_shell_settings_version_missing', __( 'The settings concurrency token is missing. Reload the page and submit again.', 'sabri-unified-application-shell' ), 'error' );
            PlanV4Audit::record( 'settings_conflict', array( 'expected_version' => 'missing', 'current_version' => self::current_version(), 'reason' => 'missing-concurrency-token' ) );
            self::release_update_lock();
            return $old_value;
        }
        $expected = absint( wp_unslash( $_POST['sabri_shell_settings_row_version'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $current = self::current_version();
        if ( $expected !== $current ) {
            add_settings_error( 'sabri_shell_settings', 'sabri_shell_settings_conflict', __( 'These settings changed in another session. Reload, compare and submit again.', 'sabri-unified-application-shell' ), 'error' );
            PlanV4Audit::record( 'settings_conflict', array( 'expected_version' => $expected, 'current_version' => $current ) );
            self::release_update_lock();
            return $old_value;
        }
        self::$accepted_update = true;
        return $new_value;
    }

    public static function after_update( $option, $old_value, $value ) {
        if ( ! self::$accepted_update || ! in_array( $option, array( 'sabri_shell_settings', 'sabri_unified_shell_settings' ), true ) ) { return; }
        self::$accepted_update = false;
        try { self::record_change( (array) $old_value, (array) $value, 'settings-api' ); }
        finally { self::release_update_lock(); }
    }

    public static function record_programmatic_change( $old_value, $new_value, $reason ) {
        $old_value = is_array( $old_value ) ? $old_value : array();
        $new_value = is_array( $new_value ) ? $new_value : array();
        if ( $old_value === $new_value ) { return self::current_version(); }
        return self::record_change( $old_value, $new_value, sanitize_key( (string) $reason ) );
    }

    private static function record_change( array $old_value, array $value, $reason ) {
        $next = self::current_version() + 1;
        update_option( self::VERSION_OPTION, $next, false );
        $changed = array_values( array_unique( array_merge( array_keys( $old_value ), array_keys( $value ) ) ) );
        PlanV4Audit::record( 'settings_updated', array( 'row_version' => $next, 'changed_groups' => array_slice( $changed, 0, 30 ), 'reason' => sanitize_key( (string) $reason ) ) );
        PlanV4ContractHealth::invalidate();
        return $next;
    }

    private static function acquire_update_lock() {
        $token = function_exists( 'wp_generate_uuid4' ) ? wp_generate_uuid4() : uniqid( 'f20-settings-', true );
        $record = array( 'token' => $token, 'expires' => time() + self::LOCK_TTL );
        if ( add_option( self::LOCK_OPTION, $record, '', 'no' ) ) { return $token; }
        $current = get_option( self::LOCK_OPTION, array() );
        if ( is_array( $current ) && absint( isset( $current['expires'] ) ? $current['expires'] : 0 ) < time() ) {
            delete_option( self::LOCK_OPTION );
            if ( add_option( self::LOCK_OPTION, $record, '', 'no' ) ) { return $token; }
        }
        return '';
    }

    public static function release_update_lock() {
        if ( '' === self::$lock_token ) { return; }
        $current = get_option( self::LOCK_OPTION, array() );
        if ( is_array( $current ) && isset( $current['token'] ) && hash_equals( (string) $current['token'], self::$lock_token ) ) { delete_option( self::LOCK_OPTION ); }
        self::$lock_token = '';
        self::$accepted_update = false;
    }
}

PlanV4SettingsConcurrency::register();
