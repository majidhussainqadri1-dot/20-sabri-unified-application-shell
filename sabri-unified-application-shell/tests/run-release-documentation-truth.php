<?php
/**
 * Permanent regression for current release/operator/repository truth.
 * Development-only CI test; tests/ is excluded from the production ZIP.
 */
declare(strict_types=1);

$root      = dirname( __DIR__ );
$repo_root = dirname( $root );

$read = static function ( string $relative ) use ( $root ): string {
	$value = @file_get_contents( $root . '/' . $relative );
	if ( ! is_string( $value ) ) {
		fwrite( STDERR, "Unable to read {$relative}\n" );
		exit( 1 );
	}
	return $value;
};

$read_repo = static function ( string $relative ) use ( $repo_root ): string {
	$value = @file_get_contents( $repo_root . '/' . $relative );
	if ( ! is_string( $value ) ) {
		fwrite( STDERR, "Unable to read repository file {$relative}\n" );
		exit( 1 );
	}
	return $value;
};

$main      = $read( 'sabri-unified-application-shell.php' );
$readme    = $read( 'readme.txt' );
$readmemd  = $read( 'README.md' );
$migration = $read( 'MIGRATION.md' );
$staging   = $read( 'STAGING-ACCEPTANCE.md' );
$changelog = $read( 'CHANGELOG.md' );
$rollback  = $read( 'ROLLBACK.md' );
$recovery  = $read( 'includes/class-plan-v4-recovery.php' );
$snapshot  = $read( 'includes/class-snapshot.php' );
$safe      = $read( 'includes/class-safe-mode.php' );
$adapter   = $read( 'includes/class-file01-reconciliation-adapter.php' );
$settings  = $read( 'includes/class-settings.php' );

$repo_status   = $read_repo( 'STATUS.md' );
$repo_manifest = $read_repo( 'MANIFEST.md' );
$incident      = $read_repo( 'LIVE-INCIDENT-CLOSURE-FILE01-RECONCILIATION-2026-08-29.md' );

$fail = array();
$assert = static function ( bool $condition, string $label ) use ( &$fail ): void {
	if ( ! $condition ) {
		$fail[] = $label;
	}
};

$current  = '1.4.17';
$artifact = '20-sabri-unified-application-shell-1.4.17-CANONICAL-PROGRAMMATIC-SETTINGS-WRITER.zip';

$assert( false !== strpos( $main, '* Version: ' . $current ), 'plugin header current version' );
$assert( false !== strpos( $main, "define( 'SABRI_SHELL_VERSION', '" . $current . "' );" ), 'runtime constant current version' );
$assert( false !== strpos( $readme, 'Stable tag: ' . $current ), 'readme stable tag current version' );
$assert( false !== strpos( $readmemd, '- Version: `' . $current . '`' ), 'README current version' );
$assert( false !== strpos( $migration, '## Upgrade to ' . $current ), 'migration current upgrade heading' );
$assert( false !== strpos( $migration, $artifact ), 'migration exact current artifact' );
$assert( false !== strpos( $staging, 'Record File 20 `' . $current . '`' ), 'staging current candidate version' );
$assert( false !== strpos( $staging, $artifact ), 'staging exact current artifact' );
$assert( false !== strpos( $changelog, '## 1.4.17 ' ), 'changelog current release' );
$assert( false !== strpos( $changelog, '## 1.4.16 ' ), 'changelog prior sanitizer persistence release' );
$assert( false !== strpos( $changelog, '## 1.4.15 ' ), 'changelog prior File01 owner-adapter release' );
$assert( false !== strpos( $changelog, '## 1.4.14 ' ), 'changelog prior release-truth correction' );
$assert( false !== strpos( $changelog, '## 1.4.13 ' ), 'changelog Renderer repair release' );
$assert( false !== strpos( $changelog, '## 1.4.12 ' ), 'changelog second-eighty release' );

/* Current operator headings must not direct a deploy to obsolete 1.4.11. Historical sections may retain that version. */
$assert( false === strpos( $migration, '## Upgrade to 1.4.11' ), 'migration does not advertise obsolete current upgrade' );
$assert( false === strpos( $staging, 'Record File 20 `1.4.11`' ), 'staging does not advertise obsolete current candidate' );

/* Root repository lifecycle documents must not silently lag behind the current runtime line. */
$assert( false !== strpos( $repo_status, 'File 20 is **Sabri Unified Application Shell 1.4.17**' ), 'root STATUS identifies current 1.4.17 source truth' );
$assert( false !== strpos( $repo_status, 'Scoped evidence only' ), 'root STATUS preserves scoped Live evidence boundary' );
$assert( false !== strpos( $repo_status, 'Operational | **Not established' ), 'root STATUS does not overclaim operational acceptance' );
$assert( false === strpos( $repo_status, '1.4.11 eighty-round candidate' ), 'root STATUS does not retain obsolete current candidate' );
$assert( false !== strpos( $repo_manifest, 'Current runtime/source version: **1.4.17**' ), 'root MANIFEST identifies current runtime/source line' );
$assert( false !== strpos( $repo_manifest, '1.4.17-CANONICAL-PROGRAMMATIC-SETTINGS-WRITER' ), 'root MANIFEST identifies current package constitution' );
$assert( false === strpos( $repo_manifest, 'current **1.4.11 eighty-round** release workflow' ), 'root MANIFEST does not advertise obsolete current workflow' );
$assert( false !== strpos( $incident, 'Status: **LIVE VERIFIED / RESOLVED**' ), 'scoped live incident closure remains explicit' );
$assert( false !== strpos( $incident, 'Do not infer broader platform-production acceptance' ), 'live incident record preserves closure boundary' );

/* Automatic rollback boundary must match current code rather than old shared WordPress front-page behavior. */
foreach ( array( 'show_on_front', 'page_on_front', 'page_for_posts' ) as $shared_option ) {
	$assert( false === strpos( $recovery, "'{$shared_option}'" ), 'PlanV4 recovery excludes shared option ' . $shared_option );
	$assert( false === strpos( $snapshot, "'{$shared_option}'" ), 'activation snapshot excludes shared option ' . $shared_option );
}
$assert( false !== strpos( $rollback, 'Automatic rollback **does not restore shared WordPress front-page options**' ), 'rollback docs explicitly exclude shared front-page options' );
$assert( false !== strpos( $rollback, '`sabri_shell_settings`' ), 'rollback docs include File20 settings boundary' );
$assert( false !== strpos( $recovery, 'private static function owned_options()' ), 'runtime rollback allowlist remains explicit' );

/* Query Safe Mode truth: admin + nonce are mandatory; raw query alone is documented as insufficient. */
$assert( false !== strpos( $safe, "'_sabri_shell_safe_nonce'" ), 'Safe Mode runtime requires nonce parameter' );
$assert( false !== strpos( $safe, 'wp_verify_nonce' ), 'Safe Mode runtime verifies nonce' );
$assert( false !== strpos( $safe, "current_user_can( 'manage_options' )" ), 'Safe Mode runtime requires administrator capability' );
$assert( false !== strpos( $rollback, 'Do **not** rely on a raw URL containing only:' ), 'rollback docs reject raw Safe Mode query alone' );
$assert( false !== strpos( $rollback, '`SafeMode::query_safe_mode_url()`' ), 'rollback docs point to nonce-bearing product URL generator' );
$assert( false !== strpos( $rollback, "define( 'SABRI_SHELL_DISABLE', true );" ), 'rollback docs retain emergency configuration constant' );

/* Live incident closure must remain an evidence boundary, not a repository-only assertion. */
$assert( false !== strpos( $migration, '/google-account-security/' ), 'migration retains original live incident retest' );
$assert( false !== strpos( $staging, '/google-account-security/' ), 'acceptance retains original live incident retest' );
$assert( false !== strpos( $readme, 'does not by itself claim' ), 'readme preserves repository/live evidence boundary' );

/* File01 reconciliation sanitizer closure remains deployment/live evidence, not a repository-only inference. */
$assert( false !== strpos( $migration, 'controlled apply failed' ) && false !== strpos( $migration, 'raw database and `get_option()` both remained `0`' ), 'migration preserves 1.4.15 live sanitizer-persistence root-cause evidence' );
$assert( false !== strpos( $staging, 'blocker count must be exactly zero' ), 'staging requires zero File01 blockers before apply' );
$assert( false !== strpos( $staging, 'command version `1.0.1`' ), 'staging requires corrected File20 reconciliation command contract' );
$assert( false !== strpos( $adapter, "const COMMAND_VERSION = '1.0.1';" ), 'runtime adapter command contract is 1.0.1' );
$assert( false !== strpos( $adapter, 'Settings::update_programmatically' ) && false === strpos( $adapter, 'persist_settings_option' ), 'runtime adapter delegates trusted persistence to canonical Settings owner' );
$assert( false !== strpos( $settings, 'public static function update_programmatically' ) && false !== strpos( $settings, 'remove_filter' ) && false !== strpos( $settings, 'add_filter' ), 'canonical Settings owner contains bounded trusted sanitizer persistence path' );
$assert( false !== strpos( $readme, 'File01 reconciliation completion' ), 'readme retains explicit File01 reconciliation evidence boundary wording' );

if ( $fail ) {
	fwrite( STDERR, 'File 20 release documentation truth FAIL: ' . implode( '; ', $fail ) . "\n" );
	exit( 1 );
}

echo "File 20 1.4.17 release/operator/repository documentation truth PASS\n";
