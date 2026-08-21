<?php
/** Static regression for the sixth independent File 20 hardening pass. */
declare(strict_types=1);
$root = dirname( __DIR__ );
$main = file_get_contents( $root . '/sabri-unified-application-shell.php' );
$sixth = file_get_contents( $root . '/includes/class-future-shell-v5-sixth-hardening.php' );
$fail = array();
$assert = static function ( $condition, $label ) use ( &$fail ): void { if ( ! $condition ) { $fail[] = $label; } };
$assert( false !== strpos( $main, '* Version: 1.4.15' ) && false !== strpos( $main, "SABRI_SHELL_VERSION', '1.4.15" ), 'current release 1.4.15 preserves sixth hardening' );
$assert( false !== strpos( $main, 'class-future-shell-v5-sixth-hardening.php' ) && false !== strpos( $main, 'FutureShellV5SixthHardening::register();' ), 'sixth hardening loaded and registered' );
$assert( false !== strpos( $sixth, "CONTRACT_VERSION = '1.0.6'" ), 'sixth contract 1.0.6' );
$assert( false !== strpos( $sixth, 'membership-core-1.2.13' ) && false !== strpos( $sixth, "'cf01_contract'              => '1.0.0'" ), 'File00 historical sixth-pass compatibility targets preserved' );
$assert( false !== strpos( $sixth, 'foundation-runtime-2.0.0-future-foundation-18' ) && false !== strpos( $sixth, "'foundation_contract'      => '2.0.0'" ), 'File01 compatibility targets' );
$assert( false !== strpos( $sixth, 'modern-auth-runtime-1.3.1-third-ten-round-reviewed' ) && false !== strpos( $sixth, "'auth_event_projection_contract'    => '1.1.0'" ), 'File02 compatibility targets' );
$assert( false !== strpos( $sixth, 'AuthenticationCompromiseReported.v1' ) && false !== strpos( $sixth, 'AuthenticationLockdownEnabled.v1' ) && false !== strpos( $sixth, 'RecoveryChangeCoolingStarted.v1' ), 'File02 security event family' );
$assert( false !== strpos( $sixth, 'file02-executes-file00-dual-control-receipt-required-file20-renders-only' ), 'File02 reset dual-control boundary' );
$assert( false !== strpos( $sixth, 'native-file02-security-remains-enforced-file20-does-not-fallback' ), 'File24 outage does not transfer security' );
$assert( false !== strpos( $sixth, 'future-security-runtime-0.99.0' ) && false !== strpos( $sixth, "'provider_schema'          => '0.25.5'" ) && false !== strpos( $sixth, "'future_feature_count'     => 25" ), 'File24 future-security targets' );
$assert( false !== strpos( $sixth, 'F24-FUT-001..F24-FUT-025' ) && false !== strpos( $sixth, 'native-owners-enforce-file24-assesses-governs-file20-renders' ), 'File24 range and enforcement boundary' );
$assert( false !== strpos( $sixth, 'declared-compatibility-target-not-runtime-detection' ) && false !== strpos( $sixth, "'runtime_presence_must_be_verified' => true" ), 'compatibility truth status' );
$assert( false !== strpos( $sixth, "'staging_acceptance_implied'        => false" ) && false !== strpos( $sixth, "'live_status_implied'               => false" ), 'no staging/live implication' );
$assert( false === strpos( $sixth, 'CREATE TABLE' ) && false === strpos( $sixth, 'dbDelta(' ) && false === strpos( $sixth, 'INSERT INTO' ), 'no foreign backend' );
if ( $fail ) { fwrite( STDERR, "Future Shell v5 sixth hardening FAIL: " . implode( '; ', $fail ) . "\n" ); exit(1); }
echo "Future Shell v5 sixth hardening preserved under 1.4.15 PASS\n";
