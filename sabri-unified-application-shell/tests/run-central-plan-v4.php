<?php
/** Static regression checks for File 20 central-plan harmonization. */
declare(strict_types=1);

$root = dirname( __DIR__ );
$checks = 0;
$failures = array();
$assert = static function ( bool $condition, string $message ) use ( &$checks, &$failures ): void {
	++$checks;
	if ( ! $condition ) {
		$failures[] = $message;
	}
};
$read = static function ( string $relative ) use ( $root ): string {
	$data = @file_get_contents( $root . '/' . $relative );
	if ( ! is_string( $data ) ) {
		fwrite( STDERR, "Unable to read {$relative}\n" );
		exit( 1 );
	}
	return $data;
};

$main     = $read( 'sabri-unified-application-shell.php' );
$contract = $read( 'includes/class-central-plan-contract.php' );
$contract_json = $read( 'contracts/file20-central-plan-v4.json' );
$seventh  = $read( 'includes/class-future-shell-v5-seventh-hardening.php' );
$eighth   = $read( 'includes/class-future-shell-v5-eighth-hardening.php' );
$ninth    = $read( 'includes/class-future-shell-v5-ninth-hardening.php' );
$tenth    = $read( 'includes/class-future-shell-v5-tenth-hardening.php' );
$eleventh = $read( 'includes/class-future-shell-v5-eleventh-hardening.php' );
$layout   = $read( 'includes/class-layout.php' );
$assets   = $read( 'includes/class-assets.php' );
$css      = $read( 'assets/css/shell-central-plan-v4.css' );

$assert( false !== strpos( $main, '* Version: 1.4.17' ), 'Plugin header must be 1.4.17.' );
$assert( false !== strpos( $main, "define( 'SABRI_SHELL_VERSION', '1.4.17' )" ), 'Runtime version must be 1.4.17.' );
$assert( false !== strpos( $main, 'CentralPlanContract::register()' ), 'Central-plan contract register' );
$assert( false !== strpos( $main, 'FutureShellV5SeventhHardening::register()' ), 'Seventh register' );
$assert( false !== strpos( $main, 'FutureShellV5EighthHardening::register()' ) && false !== strpos( $eighth, "CONTRACT_VERSION = '1.0.8'" ), 'Eighth preserved' );
$assert( false !== strpos( $main, 'FutureShellV5NinthHardening::register()' ) && false !== strpos( $ninth, "CONTRACT_VERSION = '1.0.9'" ), 'Ninth preserved' );
$assert( false !== strpos( $main, 'FutureShellV5TenthHardening::register()' ) && false !== strpos( $tenth, "CONTRACT_VERSION = '1.0.10'" ), 'Tenth preserved' );
$assert( false !== strpos( $main, 'FutureShellV5EleventhHardening::register()' ) && false !== strpos( $eleventh, "CONTRACT_VERSION = '1.0.11'" ), 'Eleventh registered' );
$assert( false !== strpos( $main, "SABRI_SHELL_CREATE_CONTRACT_VERSION', '1.0.1'" ), 'Create contract 1.0.1' );

foreach ( array( '00','01-A','01-B','02','03','04','05','06','07','08','09','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26' ) as $file ) {
	$assert( false !== strpos( $contract, "'{$file}'" ), 'Missing canonical contract ' . $file );
}
foreach ( array( 'CF-01','CF-02','CF-03','CF-04','CF-05','CF-06' ) as $file ) {
	$assert( false !== strpos( $seventh, "\$registry['{$file}']" ), 'Missing conditional ' . $file );
}
$assert( false !== strpos( $seventh, 'declared-conditional-contract-target-not-runtime-detection' ), 'CF not runtime claim' );
$assert( false !== strpos( $seventh, "'conditional_activation_required'  => true" ), 'CF activation required' );

$assert( false !== strpos( $contract, 'sabri_shell_file25_visual_contract' ), 'File25 hook' );
$assert( false !== strpos( $contract, 'file-20-continuity-fallback' ), 'Visual fallback remains explicitly continuity-only' );
$assert( false !== strpos( strtolower( $contract ), "'primary_color' => '#087a4e'" ), 'Continuity fallback must use governing Sabri Green #087A4E' );
$assert( false === strpos( strtolower( $contract ), "'primary_color' => '#15803d'" ), 'Retired fallback primary must not return' );
$assert( false !== strpos( $eighth, 'appearance-owned-by-file25' ), 'Appearance retired' );
$assert( false !== strpos( $tenth, "remove_filter( 'body_class', array( Renderer::class, 'body_classes' )" ), 'Legacy visual classes retired' );
$assert( false !== strpos( $eleventh, 'consume-foundation-registry-no-shell-or-search-truth' ), 'File01 no Search truth' );

$decoded = json_decode( $contract_json, true );
$assert( is_array( $decoded ), 'Central-plan JSON must decode' );
$assert( is_array( $decoded ) && '1.4.17' === ( $decoded['runtime_target'] ?? '' ), 'Machine-readable runtime target must match current source line' );
$assert( is_array( $decoded ) && false !== strpos( (string) ( $decoded['file_plan'] ?? '' ), 'v4.1' ), 'Machine-readable plan must identify final v4.1 baseline' );
$assert( is_array( $decoded ) && false !== strpos( (string) ( $decoded['file_plan'] ?? '' ), 'v5.0 Future Shell 18 Enhancements' ), 'Machine-readable plan must include final v5 addendum' );

foreach ( array( 'const THREE','const TWO','const MINIMAL','const IMMERSIVE' ) as $mode ) {
	$assert( false !== strpos( $layout, $mode ), 'Layout ' . $mode );
}
$assert( false === strpos( $layout, "'publishing-dashboard'" ), 'Publishing Dashboard not Minimal' );
$assert( false === strpos( $layout, "'security-center'" ), 'Security Center not Minimal' );
$assert( false === strpos( $layout, "! empty( \$_GET['user'] )" ), 'Query user does not force Three' );
$assert( false !== strpos( $layout, 'sabri_shell_is_immersive_context' ), 'Immersive extension hook' );
$assert( false !== strpos( $eighth, 'force_subdirectory_safe_sensitive_layout' ), 'Subdirectory task layout' );
$assert( false === strpos( $assets, "\$settings['appearance']" ), 'Assets no appearance ownership' );
$assert( false !== strpos( $assets, 'CentralPlanContract::visual_contract()' ), 'Visual provider exposed' );
$assert( false === strpos( $assets, '--sabri-shell-primary:' ), 'Assets no File25 token write' );
$assert( false !== strpos( $css, 'flex-wrap: nowrap !important' ), 'Desktop nav nowrap' );
$assert( false !== strpos( $css, 'inline-size: max-content' ), 'Nav intrinsic width' );
$assert( false !== strpos( $css, 'sabri-shell-layout-immersive' ), 'Immersive CSS' );
$assert( false !== strpos( $css, ':focus-visible' ), 'Focus visible' );
$assert( substr_count( $css, '{' ) === substr_count( $css, '}' ), 'CSS balanced' );

if ( $failures ) {
	foreach ( $failures as $failure ) {
		fwrite( STDERR, "FAIL: {$failure}\n" );
	}
	fwrite( STDERR, sprintf( "File 20 central-plan: %d checks, %d failures.\n", $checks, count( $failures ) ) );
	exit( 1 );
}

echo sprintf( "File 20 central-plan/eleventh harmonization under 1.4.17: %d PASS, 0 FAIL\n", $checks );
