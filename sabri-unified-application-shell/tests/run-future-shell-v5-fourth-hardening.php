<?php
/** Static regression for the fourth independent File 20 hardening pass. */
declare(strict_types=1);

$root    = dirname( __DIR__ );
$main    = file_get_contents( $root . '/sabri-unified-application-shell.php' );
$third   = file_get_contents( $root . '/includes/class-future-shell-v5-third-hardening.php' );
$fourth  = file_get_contents( $root . '/includes/class-future-shell-v5-fourth-hardening.php' );
$fail    = array();

$assert = static function ( $condition, $label ) use ( &$fail ): void {
	if ( ! $condition ) {
		$fail[] = $label;
	}
};

$assert( false !== strpos( $main, '* Version: 1.4.6' ) && false !== strpos( $main, "SABRI_SHELL_VERSION', '1.4.6" ), 'current release identity 1.4.6 preserves fourth hardening' );
$assert( false !== strpos( $main, 'class-future-shell-v5-fourth-hardening.php' ) && false !== strpos( $main, 'FutureShellV5FourthHardening::register();' ), 'fourth hardening loaded and registered' );
$assert( false !== strpos( $fourth, "CONTRACT_VERSION = '1.0.4'" ), 'fourth contract 1.0.4' );

$assert( false !== strpos( $fourth, 'membership-core-1.2.13' ), 'File 00 runtime 1.2.13 compatibility preserved' );
$assert( false !== strpos( $fourth, "'public_membership_contract' => '1.2.0'" ), 'File 00 public membership contract 1.2.0 preserved' );
$assert( false !== strpos( $fourth, "'advanced_trust_contract'    => '1.0.0'" ), 'File 00 advanced trust contract 1.0.0 preserved' );

$assert( false !== strpos( $fourth, 'foundation-runtime-2.0.0-future-foundation-18' ), 'File 01 Future Foundation v2 compatibility preserved' );
$assert( false !== strpos( $fourth, "'future_feature_count'  => 18" ), 'File 01 exact 18 enhancements preserved' );

$assert( false !== strpos( $fourth, 'modern-auth-1.3.0-candidate' ), 'historical fourth-pass File 02 1.3.0 compatibility preserved for traceability' );
$assert( false !== strpos( $fourth, "'modern_feature_count'          => 24" ), 'File 02 exact 24 enhancements preserved' );
$assert( false !== strpos( $fourth, "'passkey_assurance_v1_compat'   => '1.0.0'" ), 'File 02/File 00 passkey v1 compatibility' );
$assert( false !== strpos( $fourth, "'authentication_assurance_v2'   => '2.0.0'" ), 'File 02 authentication assurance v2' );
$assert( false !== strpos( $fourth, "'public_standard_route'         => '/.well-known/webauthn'" ), 'File 02 public WebAuthn standards route' );
$assert( false !== strpos( $fourth, 'authentication_public_standard' ) && false !== strpos( $fourth, 'return Layout::MINIMAL;' ), 'public WebAuthn standards route is Minimal presentation' );
$assert( false === strpos( $third, "'/.well-known/webauthn'" ), 'public WebAuthn standards route is not misclassified private by third hardening' );

$assert( false !== strpos( $fourth, 'notifications-runtime-3.0.0-intelligent-attention-os' ), 'File 19 3.0 Intelligent Attention compatibility' );
$assert( false !== strpos( $fourth, "'advanced_feature_count'  => 48" ), 'File 19 advanced feature count' );
$assert( false !== strpos( $fourth, 'exactly-one-bell-center-settings-placement-no-notification-truth' ), 'File 19 one-bell/no-local-truth boundary' );

$slots = array(
	'sabri_shell_home_before_main',
	'sabri_shell_home_main',
	'sabri_shell_home_after_main',
	'sabri_shell_home_right_sidebar',
	'sabri_shell_news_main',
);
foreach ( $slots as $slot ) {
	$assert( false !== strpos( $fourth, "'{$slot}'" ), 'File 21 exact native slot ' . $slot );
}
$assert( false !== strpos( $fourth, "'native_slot_count'    => 5" ), 'File 21 exact five-slot contract' );
$assert( false !== strpos( $fourth, 'home-news-runtime-1.0.1-ng30-amended' ), 'File 21 NG30 runtime compatibility' );

$security_states = array(
	'normal', 'elevated-monitoring', 'restricted-high-risk-actions', 'upload-lockdown',
	'identity-lockdown', 'publishing-read-only', 'platform-read-only', 'incident-containment',
);
foreach ( $security_states as $state ) {
	$assert( false !== strpos( $fourth, "'{$state}'" ), 'File 24 security state ' . $state );
}
$assert( false !== strpos( $fourth, "'security_state_count'  => 8" ), 'File 24 exact eight security states' );
$assert( false !== strpos( $fourth, "'security_state_owner'  => 'file-24'" ), 'File 24 security-state ownership retained' );
$assert( false !== strpos( $fourth, 'native-modules-enforce' ), 'File 24/File 20 render-versus-enforce boundary' );

$assert( false === strpos( $fourth, 'CREATE TABLE' ) && false === strpos( $fourth, 'dbDelta(' ) && false === strpos( $fourth, 'INSERT INTO' ), 'no foreign backend/data-store creation' );
$assert( false === strpos( $fourth, 'wp_insert_user' ) && false === strpos( $fourth, 'wp_create_user' ), 'no identity backend creation' );
$assert( false === strpos( $fourth, 'wp_mail(' ) && false === strpos( $fourth, 'sendBeacon' ), 'no notification delivery or telemetry backend' );

if ( $fail ) {
	fwrite( STDERR, "Future Shell v5 fourth hardening FAIL: " . implode( '; ', $fail ) . "\n" );
	exit( 1 );
}

echo "Future Shell v5 fourth ten-round hardening preserved under 1.4.6: historical File 00/01/02/19/21/24 contracts, public WebAuthn route and security states PASS\n";
