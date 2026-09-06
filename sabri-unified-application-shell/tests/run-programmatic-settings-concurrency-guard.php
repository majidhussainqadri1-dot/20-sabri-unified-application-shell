<?php
/** Regression for canonical programmatic settings concurrency and row-version accounting. */
declare(strict_types=1);

namespace {
    define( 'ABSPATH', __DIR__ . '/' );
    $GLOBALS['test_options'] = array();
    $GLOBALS['test_audit'] = array();
    $GLOBALS['test_is_admin'] = false;

    function __( $text ) { return $text; }
    function add_filter() { return true; }
    function add_action() { return true; }
    function is_admin() { return ! empty( $GLOBALS['test_is_admin'] ); }
    function current_user_can() { return true; }
    function sanitize_key( $value ) { return strtolower( preg_replace( '/[^a-z0-9_\-]/', '', (string) $value ) ); }
    function sanitize_text_field( $value ) { return trim( strip_tags( (string) $value ) ); }
    function wp_unslash( $value ) { return $value; }
    function absint( $value ) { return abs( (int) $value ); }
    function wp_json_encode( $value ) { return json_encode( $value, JSON_UNESCAPED_SLASHES ); }
    function get_option( $key, $default = false ) { return array_key_exists( $key, $GLOBALS['test_options'] ) ? $GLOBALS['test_options'][ $key ] : $default; }
    function update_option( $key, $value, $autoload = null ) { unset( $autoload ); $GLOBALS['test_options'][ $key ] = $value; return true; }
    function add_option( $key, $value, $deprecated = '', $autoload = null ) { unset( $deprecated, $autoload ); if ( array_key_exists( $key, $GLOBALS['test_options'] ) ) { return false; } $GLOBALS['test_options'][ $key ] = $value; return true; }
    function delete_option( $key ) { unset( $GLOBALS['test_options'][ $key ] ); return true; }
    function add_settings_error() {}
}

namespace Sabri\UnifiedShell {
    final class Defaults { const OPTION_NAME = 'sabri_shell_settings'; }
    final class PlanV4Audit { public static function record( $type, $context ) { $GLOBALS['test_audit'][] = array( $type, $context ); } }
    final class PlanV4ContractHealth { public static function invalidate() {} }
}

namespace {
    require dirname( __DIR__ ) . '/includes/class-plan-v4-settings-concurrency.php';
    require dirname( __DIR__ ) . '/includes/class-programmatic-settings-concurrency-guard.php';
}

namespace Sabri\UnifiedShell {
    final class File01ReconciliationAdapter {
        public static function simulate_borrowed_write( $old, $new ) {
            $candidate = ProgrammaticSettingsConcurrencyGuard::pre_update( $new, $old, Defaults::OPTION_NAME );
            if ( $candidate !== $old ) {
                $GLOBALS['test_options'][ Defaults::OPTION_NAME ] = $candidate;
                ProgrammaticSettingsConcurrencyGuard::after_update( Defaults::OPTION_NAME, $old, $candidate );
            }
            return $candidate;
        }
    }
}

namespace {
    use Sabri\UnifiedShell\Defaults;
    use Sabri\UnifiedShell\File01ReconciliationAdapter;
    use Sabri\UnifiedShell\PlanV4SettingsConcurrency;
    use Sabri\UnifiedShell\ProgrammaticSettingsConcurrencyGuard;

    $failures = array();
    $assert = static function ( $condition, $message ) use ( &$failures ) {
        echo ( $condition ? 'PASS: ' : 'FAIL: ' ) . $message . "\n";
        if ( ! $condition ) { $failures[] = $message; }
    };

    $old = array( 'schema_version' => 5, 'enabled' => true );
    $new = array( 'schema_version' => 5, 'enabled' => false );
    $GLOBALS['test_options'][ Defaults::OPTION_NAME ] = $old;
    $GLOBALS['test_options'][ PlanV4SettingsConcurrency::VERSION_OPTION ] = 1;

    $candidate = ProgrammaticSettingsConcurrencyGuard::pre_update( $new, $old, Defaults::OPTION_NAME );
    $assert( $candidate === $new, 'Uncontended programmatic mutation is admitted.' );
    $assert( isset( $GLOBALS['test_options'][ PlanV4SettingsConcurrency::LOCK_OPTION ] ), 'Programmatic mutation acquires the shared File20 settings lock.' );
    $GLOBALS['test_options'][ Defaults::OPTION_NAME ] = $candidate;
    ProgrammaticSettingsConcurrencyGuard::after_update( Defaults::OPTION_NAME, $old, $candidate );
    $assert( 2 === PlanV4SettingsConcurrency::current_version(), 'Successful programmatic mutation advances the shared row version.' );
    $assert( ! isset( $GLOBALS['test_options'][ PlanV4SettingsConcurrency::LOCK_OPTION ] ), 'Owned programmatic lock is released after successful update.' );

    PlanV4SettingsConcurrency::record_programmatic_change( $old, $new, 'legacy-specific-reason' );
    $assert( 2 === PlanV4SettingsConcurrency::current_version(), 'Legacy post-write accounting of the same old/new mutation cannot double-increment row version.' );

    $GLOBALS['test_options'][ PlanV4SettingsConcurrency::LOCK_OPTION ] = array( 'token' => 'other-request', 'expires' => time() + 30 );
    $next = array( 'schema_version' => 5, 'enabled' => true );
    $blocked = ProgrammaticSettingsConcurrencyGuard::pre_update( $next, $new, Defaults::OPTION_NAME );
    $assert( $blocked === $new, 'Concurrent programmatic mutation fails closed to the stored old value.' );
    $last_audit = end( $GLOBALS['test_audit'] );
    $assert( is_array( $last_audit ) && 'settings_conflict' === $last_audit[0] && 'programmatic-concurrent-settings-write' === ( $last_audit[1]['reason'] ?? '' ), 'Programmatic lock contention is auditable.' );
    unset( $GLOBALS['test_options'][ PlanV4SettingsConcurrency::LOCK_OPTION ] );

    $GLOBALS['test_options'][ PlanV4SettingsConcurrency::LOCK_OPTION ] = array( 'token' => 'stale', 'expires' => time() - 1 );
    $recovered = ProgrammaticSettingsConcurrencyGuard::pre_update( $next, $new, Defaults::OPTION_NAME );
    $assert( $recovered === $next, 'Expired shared lock is recovered deterministically.' );
    ProgrammaticSettingsConcurrencyGuard::release_owned_lock();
    $assert( ! isset( $GLOBALS['test_options'][ PlanV4SettingsConcurrency::LOCK_OPTION ] ), 'Recovered lock is released by its current owner.' );

    $GLOBALS['test_options'][ PlanV4SettingsConcurrency::LOCK_OPTION ] = array( 'token' => 'f20-file01-owned', 'expires' => time() + 30 );
    $before_version = PlanV4SettingsConcurrency::current_version();
    $borrowed = File01ReconciliationAdapter::simulate_borrowed_write( $new, $next );
    $assert( $borrowed === $next, 'File01 transaction may borrow its already-held shared lock only from its own call stack.' );
    $assert( isset( $GLOBALS['test_options'][ PlanV4SettingsConcurrency::LOCK_OPTION ] ) && 'f20-file01-owned' === $GLOBALS['test_options'][ PlanV4SettingsConcurrency::LOCK_OPTION ]['token'], 'Programmatic guard never releases the outer File01 transaction lock.' );
    $assert( $before_version + 1 === PlanV4SettingsConcurrency::current_version(), 'Borrowed File01 mutation still advances the canonical row version once.' );
    unset( $GLOBALS['test_options'][ PlanV4SettingsConcurrency::LOCK_OPTION ] );

    $GLOBALS['test_is_admin'] = true;
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_POST['option_page'] = 'sabri_shell_settings';
    $GLOBALS['test_options'][ PlanV4SettingsConcurrency::LOCK_OPTION ] = array( 'token' => 'settings-api-layer', 'expires' => time() + 30 );
    $admin_candidate = ProgrammaticSettingsConcurrencyGuard::pre_update( $old, $next, Defaults::OPTION_NAME );
    $assert( $admin_candidate === $old, 'Settings API submission is left to the existing optimistic concurrency layer rather than double-locked.' );
    unset( $GLOBALS['test_options'][ PlanV4SettingsConcurrency::LOCK_OPTION ] );

    if ( $failures ) { exit( 1 ); }
    echo "\nFile20 programmatic settings concurrency guard regression PASS.\n";
}
