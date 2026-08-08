<?php
/** Static regression for the sixth independent File 20 hardening pass. */
declare(strict_types=1);

$root   = dirname( __DIR__ );
$main   = file_get_contents( $root . '/sabri-unified-application-shell.php' );
$sixth  = file_get_contents( $root . '/includes/class-future-shell-v5-sixth-hardening.php' );
$fail   = array();

$assert = static function ( $condition, $label ) use ( &$fail ): void {
	if ( ! $condition ) {
		$fail[] = $label;
	}
};

$assert( false !== strpos( $main, '* Version: 1.4.6' ) && false !== strpos( $main, "SABRI_SHELL_VERSION', '1.4.6" ), 'release identity 1.4.6' );
$assert( false !== strpos( $main, 'class-future-shell-v5-sixth-hardening.php' ) && false !== strpos( $main, 'FutureShellV5SixthHardening::register();' ), 'sixth hardening loaded and registered' );
$assert( false !== strpos( $sixth, "CONTRACT_VERSION = '1.0.6'" ), 'sixth contract 1.0.6' );

$assert( false !== strpos( $sixth, 'membership-core-1.2.13' ), 'File 00 runtime target 1.2.13' );
$assert( false !== strpos( $sixth, "'provider_schema'            => '1.3.0'" ), 'File 00 schema 1.3.0 target' );
$assert( false !== strpos( $sixth, "'public_membership_contract' => '1.2.0'" ), 'File 00 public membership contract 1.2.0' );
$assert( false !== strpos( $sixth, "'cf01_contract'              => '1.0.0'" ), 'File 00 CF-01 contract 1.0.0' );
$assert( false !== strpos( $sixth, "'advanced_trust_contract'    => '1.0.0'" ), 'File 00 Advanced Trust contract 1.0.0' );

$assert( false !== strpos( $sixth, 'foundation-runtime-2.0.0-future-foundation-18' ), 'File 01 runtime 2.0.0 target' );
$assert( false !== strpos( $sixth, "'foundation_contract'      => '2.0.0'" ), 'File 01 Foundation Contract 2.0.0' );
$assert( false !== strpos( $sixth, "'provider_schema'          => '1.2.0'" ), 'File 01 schema 1.2.0 target' );
$assert( false !== strpos( $sixth, "'future_feature_count'     => 18" ), 'File 01 exact 18 Future Foundation enhancements' );

$assert( false !== strpos( $sixth, 'modern-auth-runtime-1.3.1-third-ten-round-reviewed' ), 'File 02 runtime 1.3.1 target' );
$assert( false !== strpos( $sixth, "'passkey_schema'                    => '1.1.0'" ), 'File 02 passkey schema 1.1.0' );
$assert( false !== strpos( $sixth, "'auth_event_projection_contract'    => '1.1.0'" ), 'File 02 auth event projection contract 1.1.0' );
$assert( false !== strpos( $sixth, "'modern_feature_count'              => 24" ), 'File 02 exact 24 modern-auth enhancements' );
$assert( false !== strpos( $sixth, 'AuthenticationCompromiseReported.v1' ) && false !== strpos( $sixth, 'AuthenticationLockdownEnabled.v1' ) && false !== strpos( $sixth, 'RecoveryChangeCoolingStarted.v1' ), 'File 02 current security event family' );
$assert( false !== strpos( $sixth, 'file02-executes-file00-dual-control-receipt-required-file20-renders-only' ), 'privileged reset native-owner dual-control boundary' );
$assert( false !== strpos( $sixth, 'native-file02-security-remains-enforced-file20-does-not-fallback' ), 'File 24 outage does not disable File 02 native security' );

$assert( false !== strpos( $sixth, 'future-security-runtime-0.99.0' ), 'File 24 runtime 0.99.0 target' );
$assert( false !== strpos( $sixth, "'provider_schema'          => '0.25.5'" ), 'File 24 schema 0.25.5 target' );
$assert( false !== strpos( $sixth, "'future_feature_count'     => 25" ), 'File 24 exact 25 Future Security requirements' );
$assert( false !== strpos( $sixth, 'F24-FUT-001..F24-FUT-025' ), 'File 24 future requirement range' );
$assert( false !== strpos( $sixth, 'native-owners-enforce-file24-assesses-governs-file20-renders' ), 'File 24 native enforcement boundary' );

$assert( false !== strpos( $sixth, 'declared-compatibility-target-not-runtime-detection' ), 'static compatibility facts are not claimed as runtime detection' );
$assert( false !== strpos( $sixth, "'runtime_presence_must_be_verified' => true" ), 'runtime presence requires verification' );
$assert( false !== strpos( $sixth, "'staging_acceptance_implied'        => false" ) && false !== strpos( $sixth, "'live_status_implied'               => false" ), 'compatibility metadata does not imply staging/live status' );
$assert( false !== strpos( $sixth, 'required-on-staging-and-at-native-owner-boundaries' ), 'System Check exposes verification requirement' );

$assert( false === strpos( $sixth, 'CREATE TABLE' ) && false === strpos( $sixth, 'dbDelta(' ) && false === strpos( $sixth, 'INSERT INTO' ), 'no foreign backend/data store' );
$assert( false === strpos( $sixth, 'wp_insert_user' ) && false === strpos( $sixth, 'wp_create_user' ), 'no identity backend' );
$assert( false === strpos( $sixth, 'wp_mail(' ) && false === strpos( $sixth, 'sendBeacon' ), 'no notification delivery or telemetry backend' );

if ( $fail ) {
	fwrite( STDERR, "Future Shell v5 sixth hardening FAIL: " . implode( '; ', $fail ) . "\n" );
	exit( 1 );
}

echo "Future Shell v5 sixth ten-round hardening: latest File 00/01/02/24 compatibility targets, truth-status boundaries and native ownership PASS\n";
