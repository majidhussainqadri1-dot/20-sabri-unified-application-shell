<?php
/** Static/adversarial regression for the eighth independent File 20 review. */
declare(strict_types=1);

$root      = dirname( __DIR__ );
$main      = (string) file_get_contents( $root . '/sabri-unified-application-shell.php' );
$eighth    = (string) file_get_contents( $root . '/includes/class-future-shell-v5-eighth-hardening.php' );
$system    = (string) file_get_contents( $root . '/includes/class-system-check.php' );
$audit     = (string) file_get_contents( $root . '/includes/class-plan-v4-audit.php' );
$assurance = (string) file_get_contents( $root . '/includes/class-plan-v4-assurance.php' );
$recovery  = (string) file_get_contents( $root . '/includes/class-plan-v4-recovery.php' );
$safe      = (string) file_get_contents( $root . '/includes/class-safe-mode.php' );
$snapshot  = (string) file_get_contents( $root . '/includes/class-snapshot.php' );
$fail      = array();

$assert = static function ( $ok, $label ) use ( &$fail ): void {
    if ( ! $ok ) { $fail[] = $label; }
};

$assert( false !== strpos( $main, '* Version: 1.4.9' ) && false !== strpos( $main, "SABRI_SHELL_VERSION', '1.4.9" ), 'current release identity 1.4.9 preserves eighth hardening' );
$assert( false !== strpos( $main, 'class-future-shell-v5-eighth-hardening.php' ) && false !== strpos( $main, 'FutureShellV5EighthHardening::register();' ), 'eighth hardening loaded/registered' );
$assert( false !== strpos( $eighth, "CONTRACT_VERSION = '1.0.8'" ), 'eighth contract 1.0.8' );

$assert( false !== strpos( $eighth, "'appearance' !== \$tab" ) && false !== strpos( $eighth, 'appearance-owned-by-file25' ), 'File25 appearance editor retirement' );
$assert( false !== strpos( $eighth, "wp_parse_url( home_url( '/' ), PHP_URL_PATH )" ) && false !== strpos( $eighth, 'force_subdirectory_safe_sensitive_layout' ), 'subdirectory-aware sensitive task layout' );
$assert( false !== strpos( $eighth, '/system-check/export' ) && false !== strpos( $eighth, "current_user_can( 'manage_options' )" ), 'authenticated system-check export' );
$assert( false !== strpos( $system, "'id'" ) && false !== strpos( $system, "'severity'" ) && false !== strpos( $system, "'evidence'" ) && false !== strpos( $system, "'last_run'" ) && false !== strpos( $system, "'affected_surface'" ) && false !== strpos( $system, "'remediation'" ), 'structured System Check evidence fields' );
$assert( false !== strpos( $system, "apply_filters( 'sabri_shell_system_check_sections'" ) && false !== strpos( $system, 'public static function export()' ), 'System Check consumes sections and exports sanitized evidence' );
$assert( false !== strpos( $system, "'staging-acceptance'" ) && false !== strpos( $system, "'unknown'" ), 'staging remains unknown not pass' );

$assert( false !== strpos( $audit, 'ANCHOR_OPTION' ) && false !== strpos( $audit, 'anchor_for_events' ) && false !== strpos( $audit, 'rehash_events' ), 'bounded audit anchor and authorized rehash' );
$assert( false !== strpos( $audit, 'sabri_shell_audit_chain_invalid' ) && false !== strpos( $audit, 'verify_events' ), 'audit append fails closed on invalid chain' );
$assert( false !== strpos( $audit, "function_exists( 'mb_substr' )" ) && false !== strpos( $audit, 'substr( $text, 0, 500 )' ), 'audit has no hidden mbstring requirement' );
$assert( false !== strpos( $assurance, "function_exists( 'mb_substr' )" ) && false !== strpos( $assurance, 'substr( $text, 0, 300 )' ), 'assurance has no hidden mbstring requirement' );

$assert( false !== strpos( $recovery, 'repair_action_diff' ) && false !== strpos( $recovery, 'diff_values' ) && false !== strpos( $recovery, 'Settings::ensure_defaults();' ), 'repair dry-run diff and real normalization' );
$assert( false !== strpos( $recovery, "'schema_compatible'" ) && false !== strpos( $recovery, 'snapshot_schema_version' ) && false !== strpos( $recovery, "FutureShellV5::OPTION" ), 'rollback schema compatibility and Future Shell state snapshot' );
$assert( false !== strpos( $recovery, 'public static function snapshot_list()' ) && false !== strpos( $recovery, "'source' => 'file-20-plan-v4-recovery'" ), 'bounded operator snapshot metadata and source evidence' );

$assert( false !== strpos( $safe, 'QUERY_NONCE_ACTION' ) && false !== strpos( $safe, 'wp_verify_nonce' ) && false !== strpos( $safe, 'query_safe_mode_url' ), 'nonce-bound query Safe Mode' );
$assert( false !== strpos( $safe, 'EMERGENCY_META_OPTION' ) && false !== strpos( $safe, "'review_at'" ) && false !== strpos( $safe, 'PlanV4Audit::record' ) && false !== strpos( $safe, 'PlanV4PrivacyCache::purge' ), 'audited emergency lifecycle and re-enable cache gate' );
$assert( false !== strpos( $eighth, "remove_action( 'admin_post_sabri_shell_repair'" ) && false !== strpos( $eighth, 'PlanV4Recovery::execute_repair' ), 'admin repair routed through hardened recovery' );
$assert( false !== strpos( $eighth, "remove_action( 'admin_post_sabri_shell_rollback'" ) && false !== strpos( $eighth, 'PlanV4Recovery::execute_rollback' ), 'admin rollback routed through hardened recovery' );
$assert( false !== strpos( $eighth, "remove_action( 'admin_post_sabri_shell_emergency'" ) && false !== strpos( $eighth, 'SafeMode::set_emergency_disabled' ), 'admin emergency routed through hardened lifecycle' );

$assert( false !== strpos( $snapshot, 'FORMAT_VERSION = 2' ) && false !== strpos( $snapshot, "'hash'" ) && false !== strpos( $snapshot, "'source'" ) && false !== strpos( $snapshot, "'future_settings'" ), 'activation snapshot v2 integrity/source/Future Shell evidence' );
$assert( false === strpos( $snapshot, "update_option( 'page_on_front'" ) && false === strpos( $snapshot, "update_option( 'show_on_front'" ), 'activation rollback never writes shared front-page ownership' );

$combined = $eighth . $system . $audit . $assurance . $recovery . $safe . $snapshot;
$assert( false === strpos( $combined, 'CREATE TABLE' ) && false === strpos( $combined, 'dbDelta(' ) && false === strpos( $combined, 'INSERT INTO' ), 'no companion-domain database ownership introduced' );

if ( $fail ) {
    fwrite( STDERR, "Future Shell v5 eighth hardening FAIL: " . implode( '; ', $fail ) . "\n" );
    exit( 1 );
}

echo "Future Shell v5 eighth hardening preserved under 1.4.9: File25 ownership, subdirectory privacy/layout, structured System Check/export, audit retention integrity, safe repair/rollback, emergency lifecycle and activation snapshot PASS\n";
