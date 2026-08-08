<?php
/** Static regression for the fourth independent File 20 hardening pass. */
declare(strict_types=1);
$root = dirname( __DIR__ );
$main = file_get_contents( $root . '/sabri-unified-application-shell.php' );
$third = file_get_contents( $root . '/includes/class-future-shell-v5-third-hardening.php' );
$fourth = file_get_contents( $root . '/includes/class-future-shell-v5-fourth-hardening.php' );
$fail = array();
$assert = static function ( $condition, $label ) use ( &$fail ): void { if ( ! $condition ) { $fail[] = $label; } };
$assert( false !== strpos( $main, '* Version: 1.4.9' ) && false !== strpos( $main, "SABRI_SHELL_VERSION', '1.4.9" ), 'current release identity 1.4.9 preserves fourth hardening' );
$assert( false !== strpos( $main, 'class-future-shell-v5-fourth-hardening.php' ) && false !== strpos( $main, 'FutureShellV5FourthHardening::register();' ), 'fourth hardening loaded and registered' );
$assert( false !== strpos( $fourth, "CONTRACT_VERSION = '1.0.4'" ), 'fourth contract 1.0.4' );
$assert( false !== strpos( $fourth, 'membership-core-1.2.13' ), 'File 00 compatibility preserved' );
$assert( false !== strpos( $fourth, 'foundation-runtime-2.0.0-future-foundation-18' ), 'File 01 compatibility preserved' );
$assert( false !== strpos( $fourth, 'modern-auth-1.3.0-candidate' ), 'historical File 02 fourth-pass target preserved' );
$assert( false !== strpos( $fourth, "'public_standard_route'         => '/.well-known/webauthn'" ), 'public WebAuthn standards route preserved' );
$assert( false === strpos( $third, "'/.well-known/webauthn'" ), 'WebAuthn public route not private in third hardening' );
$assert( false !== strpos( $fourth, 'notifications-runtime-3.0.0-intelligent-attention-os' ), 'File 19 compatibility preserved' );
$assert( false !== strpos( $fourth, 'home-news-runtime-1.0.1-ng30-amended' ), 'File 21 compatibility preserved' );
foreach ( array( 'normal','elevated-monitoring','restricted-high-risk-actions','upload-lockdown','identity-lockdown','publishing-read-only','platform-read-only','incident-containment' ) as $state ) {
    $assert( false !== strpos( $fourth, "'{$state}'" ), 'File 24 state ' . $state );
}
$assert( false !== strpos( $fourth, 'native-modules-enforce' ), 'File24 native enforcement boundary' );
$assert( false === strpos( $fourth, 'CREATE TABLE' ) && false === strpos( $fourth, 'dbDelta(' ) && false === strpos( $fourth, 'INSERT INTO' ), 'no foreign backend' );
if ( $fail ) { fwrite( STDERR, "Future Shell v5 fourth hardening FAIL: " . implode( '; ', $fail ) . "\n" ); exit(1); }
echo "Future Shell v5 fourth hardening preserved under 1.4.9: historical companion contracts, public WebAuthn route and File24 states PASS\n";
