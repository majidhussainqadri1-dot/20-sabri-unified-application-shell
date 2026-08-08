<?php
/** Static regression for the seventh independent File 20 hardening pass. */
declare(strict_types=1);

$root    = dirname( __DIR__ );
$main    = file_get_contents( $root . '/sabri-unified-application-shell.php' );
$seventh = file_get_contents( $root . '/includes/class-future-shell-v5-seventh-hardening.php' );
$fail    = array();
$assert = static function ( $condition, $label ) use ( &$fail ): void { if ( ! $condition ) { $fail[] = $label; } };

$assert( false !== strpos( $main, '* Version: 1.4.10' ) && false !== strpos( $main, "SABRI_SHELL_VERSION', '1.4.10" ), 'release identity 1.4.10 preserves seventh hardening' );
$assert( false !== strpos( $main, 'class-future-shell-v5-seventh-hardening.php' ) && false !== strpos( $main, 'FutureShellV5SeventhHardening::register();' ), 'seventh hardening loaded and registered' );
$assert( false !== strpos( $seventh, "CONTRACT_VERSION = '1.0.7'" ) && false !== strpos( $seventh, 'CONDITIONAL_COUNT = 6' ), 'seventh contract and exact conditional count' );
foreach ( array( 'CF-01', 'CF-02', 'CF-03', 'CF-04', 'CF-05', 'CF-06' ) as $id ) { $assert( false !== strpos( $seventh, "\$registry['{$id}']" ), 'conditional registry includes ' . $id ); }
$assert( false !== strpos( $seventh, 'declared-conditional-contract-target-not-runtime-detection' ), 'conditional metadata does not claim runtime detection' );
$assert( false !== strpos( $seventh, "'conditional_activation_required'  => true" ), 'conditional activation remains required' );
$assert( false !== strpos( $seventh, "'staging_acceptance_implied'        => false" ) && false !== strpos( $seventh, "'live_status_implied'               => false" ), 'conditional metadata does not imply staging or live' );
foreach ( array('/clinic/records','/clinic/patients','/my-health-record','/clinic/encounters','/clinic/prescriptions','/clinic/follow-ups','/support/cases','/support/appeals','/admin/support','/checkout','/billing','/admin/finance','/api/media/v1','/admin/media','/insights','/admin/analytics','/settings/language','/admin/localization') as $path ) { $assert( false !== strpos( $seventh, "'{$path}'" ), 'private conditional path ' . $path ); }
$assert( false === strpos( $seventh, "'/support'," ), 'public support help root is not globally private' );
$assert( false === strpos( $seventh, "'/donate'," ) && false === strpos( $seventh, "'/transparency'," ) && false === strpos( $seventh, "'/pricing'," ), 'public/dormant CF-03 roots are not globally private' );
$assert( false === strpos( $seventh, "'/media/d', '/admin/media'" ), 'CF-04 token delivery is not inserted into the private-header list' );
$assert( false !== strpos( $seventh, "'/api/localization/v1', '/media/d'" ), 'CF-04 token delivery is forced to Minimal/no Future Shell client surface' );
$assert( false !== strpos( $seventh, 'native-cache-and-range-authority-preserved' ), 'CF-04 native cache/range authority preserved' );
$assert( false !== strpos( $seventh, 'single-free-tier-voluntary-donation-no-donor-advantage' ), 'current single-free-tier/donation-only financial law preserved' );
$assert( false !== strpos( $seventh, 'dormant-unless-new-founder-change-control-and-cf03-activation-evidence' ), 'paid collection remains dormant absent change control' );
$assert( false !== strpos( $seventh, "'zero_commission'                 => true" ) && false !== strpos( $seventh, "'donor_advantage'                 => false" ), 'zero commission and donor neutrality preserved' );
$assert( false !== strpos( $seventh, "'owner'                  => 'file-17-cf-04-after-activation'" ), 'verified transfer remains File17 plus conditional CF04' );
$assert( false !== strpos( $seventh, "'per_file_limit_bytes'   => 1073741824" ), 'verified transfer 1 GB limit preserved' );
$assert( false !== strpos( $seventh, "'owner'                  => 'native-owners-file24-cf04-after-activation'" ), 'download ownership reconciles native/File24/conditional CF04' );
$assert( false !== strpos( $seventh, 'file-20-shell-surface-cf-06-locale-provider-after-activation' ), 'CF06 localization is provider-only for File20 language surface' );
$assert( false !== strpos( $seventh, 'existing-approved-language-provider-or-honest-unavailable' ), 'language provider failure is honest unavailable' );
$assert( false === strpos( $seventh, 'CREATE TABLE' ) && false === strpos( $seventh, 'dbDelta(' ) && false === strpos( $seventh, 'INSERT INTO' ), 'no conditional-domain database creation' );
$assert( false === strpos( $seventh, 'wp_insert_user' ) && false === strpos( $seventh, 'wp_create_user' ) && false === strpos( $seventh, 'wp_mail(' ), 'no identity or delivery backend creation' );
$assert( false === strpos( $seventh, 'PaymentSettled' ) && false === strpos( $seventh, 'PrescriptionCreated' ), 'no financial or clinical event truth fabricated' );
if ( $fail ) { fwrite( STDERR, "Future Shell v5 seventh hardening FAIL: " . implode( '; ', $fail ) . "\n" ); exit( 1 ); }
echo "Future Shell v5 seventh hardening preserved under 1.4.10: CF-01..CF-06 conditional contracts, privacy boundaries, financial law, media cache authority and no foreign backend PASS\n";
