<?php
/** Static/adversarial regression for the ninth independent File 20 review. */
declare(strict_types=1);

$root = dirname( __DIR__ );
$main = (string) file_get_contents( $root . '/sabri-unified-application-shell.php' );
$defaults = (string) file_get_contents( $root . '/includes/class-defaults.php' );
$safe = (string) file_get_contents( $root . '/includes/class-safe-mode.php' );
$recovery = (string) file_get_contents( $root . '/includes/class-plan-v4-recovery.php' );
$concurrency = (string) file_get_contents( $root . '/includes/class-plan-v4-settings-concurrency.php' );
$route = (string) file_get_contents( $root . '/includes/class-route-security.php' );
$nav = (string) file_get_contents( $root . '/includes/class-navigation.php' );
$ninth = (string) file_get_contents( $root . '/includes/class-future-shell-v5-ninth-hardening.php' );
$uninstall = (string) file_get_contents( $root . '/uninstall.php' );
$fail = array();
$assert = static function ( $ok, $label ) use ( &$fail ): void { if ( ! $ok ) { $fail[] = $label; } };

$assert( false !== strpos( $main, '* Version: 1.4.9' ) && false !== strpos( $main, "SABRI_SHELL_VERSION', '1.4.9" ), 'release identity 1.4.9' );
$assert( false !== strpos( $main, 'RouteSecurity::register();' ), 'route security registered' );
$assert( false !== strpos( $main, 'FutureShellV5NinthHardening::register();' ) && false !== strpos( $ninth, "CONTRACT_VERSION = '1.0.9'" ), 'ninth contract registered' );

$assert( false !== strpos( $defaults, 'const SCHEMA_VERSION       = 4;' ), 'settings schema 4' );
$assert( false === strpos( $defaults, "'appearance'            => array(" ), 'File20 no longer creates appearance defaults' );
$assert( false !== strpos( $defaults, 'File 25 is the sole visual-design authority' ), 'File25 ownership documented in defaults' );

$assert( false !== strpos( $safe, "pre_update_option_' . Defaults::OPTION_NAME" ) && false !== strpos( $safe, 'emergency_direct_write_blocked' ), 'direct Emergency write guard' );
$assert( false !== strpos( $safe, 'private static $emergency_write_authorized' ) && false !== strpos( $safe, 'write_settings' ), 'canonical Emergency write authority' );

$assert( false !== strpos( $recovery, 'const SNAPSHOT_FORMAT = 2;' ), 'presence-aware snapshot format 2' );
$assert( false !== strpos( $recovery, "'exists' => \$value !== \$sentinel" ) && false !== strpos( $recovery, 'delete_option( $option )' ), 'snapshot distinguishes absence from null' );
$locked = strpos( $recovery, '$locked_preview = self::preview_rollback( $snapshot_id )' );
$pre = strpos( $recovery, "$pre = self::create_snapshot( 'pre-rollback' )" );
$assert( false !== $locked && false !== $pre && $locked < $pre, 'rollback revalidates target under lock before retention mutation' );
$assert( false !== strpos( $recovery, 'sabri_shell_stale_repair_locked' ), 'repair concurrency revalidated under lock' );
$assert( false !== strpos( $recovery, 'plan_v4_quarantine_stale_page_bindings' ) && false !== strpos( $recovery, 'stale_page_binding_diff' ), 'selectable page-map repair and dry-run diff' );
$assert( false !== strpos( $concurrency, 'record_programmatic_change' ) && false !== strpos( $recovery, "'repair-page-map'" ), 'programmatic repair advances concurrency evidence' );
$assert( false !== strpos( $recovery, "'current_emergency_state_preserved' => true" ) && false !== strpos( $recovery, "'settings_row_version_monotonic' => true" ), 'rollback safety/concurrency disclosure' );
$owned = substr( $recovery, strpos( $recovery, 'private static function owned_options()' ), 900 );
$assert( false === strpos( $owned, 'PlanV4SettingsConcurrency::VERSION_OPTION' ) && false === strpos( $owned, 'SafeMode::EMERGENCY_META_OPTION' ), 'rollback scope excludes monotonic counter and Emergency lifecycle metadata' );
$assert( false !== strpos( $recovery, 'restore_option_entry' ) && false !== strpos( $recovery, 'rollback_option_verification_failed' ), 'rollback verifies each post-write option state' );

$assert( false !== strpos( $route, "esc_url_raw( \$url, array( 'https' ) )" ), 'route overrides absolute HTTPS only' );
$assert( false !== strpos( $route, "strpos( \$url, '?' )" ) && false !== strpos( $route, "strpos( \$url, '#' )" ), 'route overrides reject query/fragment' );
$assert( false !== strpos( $route, 'sabri_shell_route_override_allowed_hosts' ) && false !== strpos( $route, "option_' . Defaults::OPTION_NAME" ), 'explicit external allowlist and read-time revalidation' );

$p1 = strpos( $nav, "'source_priority'] = 1" );
$p2 = strpos( $nav, "'source_priority'] = 2" );
$p3 = strpos( $nav, "'source_priority'] = 3" );
$p4 = strpos( $nav, "'source_priority'] = 4" );
$p5 = strpos( $nav, "'source_priority'] = 5" );
$assert( false !== $p1 && false !== $p2 && false !== $p3 && false !== $p4 && false !== $p5 && $p1 < $p2 && $p2 < $p3 && $p3 < $p4 && $p4 < $p5, 'canonical route precedence order' );
$assert( false === strpos( $nav, 'Integrations::destination_url( $key )' ) && false !== strpos( $nav, 'RouteSecurity::sanitize_override' ), 'arbitrary companion callback cannot preempt precedence' );

$assert( false !== strpos( $uninstall, "empty( \$settings['delete_on_uninstall'] )" ), 'uninstall remains opt-in' );
foreach ( array( 'sabri_shell_plan_v4_audit', 'sabri_shell_plan_v4_snapshots', 'sabri_shell_future_v5', 'sabri_shell_future_lkg', 'sabri_shell_emergency_state', 'sabri_shell_settings_row_version' ) as $option ) {
    $assert( false !== strpos( $uninstall, $option ), 'explicit uninstall purges ' . $option );
}
$assert( false !== strpos( $uninstall, "wp_clear_scheduled_hook( 'sabri_shell_plan_v4_maintenance' )" ), 'uninstall clears maintenance schedule' );

$combined = $main . $defaults . $safe . $recovery . $concurrency . $route . $nav . $ninth . $uninstall;
$assert( false === strpos( $combined, 'CREATE TABLE' ) && false === strpos( $combined, 'dbDelta(' ) && false === strpos( $combined, 'INSERT INTO' ), 'no companion-domain database ownership introduced' );

if ( $fail ) {
    fwrite( STDERR, "Future Shell v5 ninth hardening FAIL: " . implode( '; ', $fail ) . "\n" );
    exit( 1 );
}
echo "Future Shell v5 ninth hardening: visual ownership, Emergency integrity, presence-aware recovery, locked concurrency, page-map repair, strict routes, canonical precedence, uninstall and rollback safety PASS\n";
