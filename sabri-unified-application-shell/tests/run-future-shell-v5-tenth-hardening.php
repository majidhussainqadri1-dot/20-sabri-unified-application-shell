<?php
/** Static/adversarial regression for the tenth fresh File 20 ten-round review. */
declare(strict_types=1);

$root = dirname( __DIR__ );
$read = static function ( $path ) use ( $root ) { return (string) file_get_contents( $root . '/' . $path ); };
$main    = $read( 'sabri-unified-application-shell.php' );
$plugin  = $read( 'includes/class-plugin.php' );
$defaults= $read( 'includes/class-defaults.php' );
$tenth   = $read( 'includes/class-future-shell-v5-tenth-hardening.php' );
$slots   = $read( 'includes/class-native-content-slots.php' );
$safe    = $read( 'includes/class-safe-mode.php' );
$health  = $read( 'includes/class-plan-v4-contract-health.php' );
$fail = array();
$assert = static function ( $ok, $label ) use ( &$fail ): void { if ( ! $ok ) { $fail[] = $label; } };

$assert( false !== strpos( $main, '* Version: 1.4.11' ) && false !== strpos( $main, "SABRI_SHELL_VERSION', '1.4.11" ), 'release identity 1.4.11' );
$assert( false !== strpos( $main, 'FutureShellV5TenthHardening::register();' ), 'tenth hardening registered' );
$assert( false !== strpos( $tenth, "CONTRACT_VERSION = '1.0.10'" ), 'tenth contract 1.0.10' );

$assert( false === strpos( $plugin, 'HomeFeed::register();' ), 'File20 local HomeFeed runtime not registered' );
$assert( false !== strpos( $tenth, "'sabri_shell_home_feed' !== sanitize_key" ), 'retired File20 feed shortcode removed from route candidates' );
$assert( false !== strpos( $tenth, "'auto_insert' => false" ) && false !== strpos( $tenth, "'posts_count' => 0" ), 'retired local feed persisted inert' );
$assert( false !== strpos( $tenth, "\$value['navigation']['home']['shortcode'] = 'sabri_complete_home_feed';" ), 'configured legacy File20 feed shortcode migrates to the File21-compatible provider shortcode' );
$assert( false !== strpos( $defaults, "'home_feed'" ), 'legacy defaults remain migration-readable only' );

foreach ( array( 'sabri_shell_home_before_main', 'sabri_shell_home_main', 'sabri_shell_home_after_main', 'sabri_shell_home_right_sidebar', 'sabri_shell_news_main' ) as $hook ) {
    $assert( false !== strpos( $slots, $hook ), 'native slot ' . $hook );
}
$assert( false !== strpos( $slots, "'' !== trim( \$main ) ? \$main : \$content" ), 'native Home/News output replaces legacy content instead of duplicating it' );
$assert( false !== strpos( $slots, 'self::$home_content_dispatched' ) && false !== strpos( $slots, 'self::$news_content_dispatched' ), 'native slots one-per-request guarded' );
$assert( false !== strpos( $slots, 'catch ( \\Throwable $error )' ), 'slot provider failure isolated' );

$assert( false !== strpos( $tenth, 'remove_filter( \'body_class\', array( Renderer::class, \'body_classes\' )' ), 'legacy File20 visual body classes retired' );
$assert( false === strpos( substr( $tenth, strpos( $tenth, 'structural_body_classes' ), 1800 ), "['appearance']" ), 'tenth structural classes never read File20 appearance state' );

$assert( false !== strpos( $tenth, 'membership-core-1.2.18-reviewed-head' ) && false !== strpos( $tenth, "'critical' => 13" ) && false !== strpos( $tenth, "'high' => 44" ), 'latest File00 audit truth recorded' );
$assert( false !== strpos( $tenth, "'production_safe_implied' => false" ) && false !== strpos( $tenth, "'known_external_release_blockers' => true" ), 'File00 audit cannot imply production safety' );

$assert( false !== strpos( $tenth, 'file26_search_surface_only' ) && false !== strpos( $tenth, "'file20_fallback' => false" ), 'File26-only search surface' );
$assert( false !== strpos( $health, "'status' => 'unavailable'" ) && false !== strpos( $health, "'file20_fallback' => false" ), 'base search surface is fail-closed with no File20 fallback before the final File26 override' );
$assert( false !== strpos( $tenth, "add_filter( 'sabri_shell_search_surface'" ) && false !== strpos( $tenth, 'PHP_INT_MAX' ), 'final search fallback override is last' );

$assert( false !== strpos( $tenth, 'bind_actual_provider_versions' ) && false !== strpos( $tenth, "\$providers['file-25-visual']['version']" ) && false !== strpos( $tenth, "\$providers['file-01b-registry-search']['version']" ), 'native provider versions bound into health registry' );
$assert( false !== strpos( $tenth, "preg_match( '/^\\d+\\.\\d+\\.\\d+$/', \$version )" ), 'provider versions require semantic shape' );

$assert( false !== strpos( $tenth, 'critical_health_state' ) && false !== strpos( $tenth, "array( 'file-20-shell', 'file-00-identity' )" ), 'critical health gate names File20 and File00' );
$assert( false !== strpos( $safe, 'FutureShellV5TenthHardening::critical_health_state( $health )' ) && false !== strpos( $safe, "'healthy' !== \$critical_state" ), 'Emergency re-enable uses critical health gate' );
$assert( false !== strpos( $tenth, 'healthy-only-when-critical-contracts-verified' ), 'health endpoint cannot false-green required providers' );

$combined = $main . $plugin . $tenth . $slots . $safe;
$assert( false === strpos( $combined, 'CREATE TABLE' ) && false === strpos( $combined, 'dbDelta(' ) && false === strpos( $combined, 'INSERT INTO' ), 'no foreign backend introduced by tenth cycle' );

if ( $fail ) {
    fwrite( STDERR, "Future Shell v5 tenth hardening FAIL: " . implode( '; ', $fail ) . "\n" );
    exit( 1 );
}
echo "Future Shell v5 tenth hardening: feed ownership, native slots, File25/File26 boundaries, File00 truth, provider health and Emergency gate PASS\n";
