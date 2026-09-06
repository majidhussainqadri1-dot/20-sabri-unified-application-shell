<?php
/**
 * Shared concurrency boundary for trusted File 20 programmatic settings writes.
 *
 * Settings API submissions already use optimistic row-version checks in
 * PlanV4SettingsConcurrency. This guard closes the other half of the contract:
 * every non-Settings-API full settings mutation is serialized through the same
 * lock option, conflicts fail closed, and successful old/new transitions advance
 * the same monotonic row version exactly once.
 *
 * @package SabriUnifiedApplicationShell
 */
namespace Sabri\UnifiedShell;
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class ProgrammaticSettingsConcurrencyGuard {
    const CONTRACT_VERSION = '1.0.0';
    private static $owned_token = '';

    public static function register() {
        add_filter( 'pre_update_option_' . Defaults::OPTION_NAME, array( __CLASS__, 'pre_update' ), PHP_INT_MAX - 2, 3 );
        add_action( 'updated_option', array( __CLASS__, 'after_update' ), 11, 3 );
        add_action( 'shutdown', array( __CLASS__, 'release_owned_lock' ), PHP_INT_MAX - 1 );
        add_filter( 'sabri_shell_system_check_sections', array( __CLASS__, 'system_check' ), 111 );
    }

    /** Settings API writes remain governed by the existing submitted row token. */
    private static function is_settings_api_request() {
        if ( ! function_exists( 'is_admin' ) || ! is_admin() ) { return false; }
        $method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) : '';
        $page = isset( $_POST['option_page'] ) ? sanitize_key( wp_unslash( $_POST['option_page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- classification only; Settings API owns nonce verification.
        return 'POST' === $method && 'sabri_shell_settings' === $page;
    }

    /**
     * File01 reconciliation deliberately owns the same lock across its complete
     * read/write/receipt transaction. Its private helper creates an f20-file01-
     * token before entering Settings::update_programmatically(). We may borrow
     * that lock only when the current PHP call stack proves this exact File01
     * transaction is the caller; a different concurrent File01 request fails in
     * its own acquire step and cannot reach this write path.
     */
    private static function file01_transaction_owns_lock() {
        $current = get_option( PlanV4SettingsConcurrency::LOCK_OPTION, array() );
        $token = is_array( $current ) && isset( $current['token'] ) ? (string) $current['token'] : '';
        if ( 0 !== strpos( $token, 'f20-file01-' ) ) { return false; }
        $trace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 24 );
        foreach ( $trace as $frame ) {
            if ( isset( $frame['class'] ) && __NAMESPACE__ . '\\File01ReconciliationAdapter' === $frame['class'] ) {
                return true;
            }
        }
        return false;
    }

    /** Serialize every non-Settings-API File20 settings mutation. */
    public static function pre_update( $new_value, $old_value, $option ) {
        unset( $option );
        if ( $new_value === $old_value || self::is_settings_api_request() ) { return $new_value; }

        if ( self::file01_transaction_owns_lock() ) {
            return $new_value;
        }

        if ( '' !== self::$owned_token ) {
            /* A nested canonical write in the same request already owns the lock. */
            return $new_value;
        }

        $token = self::acquire_lock();
        if ( '' === $token ) {
            if ( class_exists( __NAMESPACE__ . '\\PlanV4Audit', false ) ) {
                PlanV4Audit::record( 'settings_conflict', array(
                    'expected_version' => 'programmatic-lock',
                    'current_version' => PlanV4SettingsConcurrency::current_version(),
                    'reason' => 'programmatic-concurrent-settings-write',
                ) );
            }
            return $old_value;
        }
        self::$owned_token = $token;
        return $new_value;
    }

    /** Advance the canonical row version once after a successful mutation. */
    public static function after_update( $option, $old_value, $value ) {
        if ( Defaults::OPTION_NAME !== $option || self::is_settings_api_request() || $old_value === $value ) { return; }
        try {
            if ( class_exists( __NAMESPACE__ . '\\PlanV4SettingsConcurrency', false ) ) {
                PlanV4SettingsConcurrency::record_programmatic_change(
                    is_array( $old_value ) ? $old_value : array(),
                    is_array( $value ) ? $value : array(),
                    'canonical-programmatic-write'
                );
            }
        } finally {
            self::release_owned_lock();
        }
    }

    private static function acquire_lock() {
        $token = function_exists( 'wp_generate_uuid4' ) ? wp_generate_uuid4() : uniqid( 'f20-programmatic-', true );
        $record = array( 'token' => $token, 'expires' => time() + PlanV4SettingsConcurrency::LOCK_TTL );
        if ( add_option( PlanV4SettingsConcurrency::LOCK_OPTION, $record, '', 'no' ) ) { return $token; }
        $current = get_option( PlanV4SettingsConcurrency::LOCK_OPTION, array() );
        if ( is_array( $current ) && absint( isset( $current['expires'] ) ? $current['expires'] : 0 ) < time() ) {
            delete_option( PlanV4SettingsConcurrency::LOCK_OPTION );
            if ( add_option( PlanV4SettingsConcurrency::LOCK_OPTION, $record, '', 'no' ) ) { return $token; }
        }
        return '';
    }

    public static function release_owned_lock() {
        if ( '' === self::$owned_token ) { return; }
        $current = get_option( PlanV4SettingsConcurrency::LOCK_OPTION, array() );
        if ( is_array( $current ) && isset( $current['token'] ) && hash_equals( (string) $current['token'], self::$owned_token ) ) {
            delete_option( PlanV4SettingsConcurrency::LOCK_OPTION );
        }
        self::$owned_token = '';
    }

    public static function system_check( $sections ) {
        $sections = is_array( $sections ) ? $sections : array();
        $sections['programmatic_settings_concurrency'] = array(
            'label' => __( 'Programmatic settings concurrency guard', 'sabri-unified-application-shell' ),
            'contract_version' => self::CONTRACT_VERSION,
            'settings_api_policy' => 'optimistic-row-version-plus-shared-lock',
            'programmatic_policy' => 'shared-lock-fail-closed-plus-monotonic-row-version',
            'file01_transaction_lock' => 'borrowed-only-with-call-stack-proof',
        );
        return $sections;
    }
}
