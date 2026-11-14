<?php
/**
 * V12.3 PWA source privacy allowlist regression. Does not access Live caches.
 */
require __DIR__ . '/bootstrap.php';
require_once dirname( __DIR__ ) . '/includes/class-future-shell-v5.php';
if ( ! function_exists( 'wp_json_encode' ) ) {
    function wp_json_encode( $data ) { return json_encode( $data ); }
}
$ref = new ReflectionMethod( Sabri\UnifiedShell\FutureShellV5::class, 'service_worker_source' );
$ref->setAccessible( true );
$js = (string) $ref->invoke( null );
$errors = array();
$assert = static function ( $ok, $message ) use ( &$errors ) {
    echo ( $ok ? 'PASS: ' : 'FAIL: ' ) . $message . "\n";
    if ( ! $ok ) { $errors[] = $message; }
};
$assert( false !== strpos( $js, "const prefix='/wp-content/plugins/sabri-unified-application-shell/assets/'" ), 'Cache prefix constrained to File20 public asset folder.' );
$assert( false !== strpos( $js, "parts.length===2" ) && false !== strpos( $js, "parts[0]==='css'" ) && false !== strpos( $js, "parts[0]==='js'" ), 'Strict CSS/JS immediate child allowlist.' );
$assert( false !== strpos( $js, "r.destination==='style'" ) && false !== strpos( $js, "r.destination==='script'" ), 'Only real stylesheet/script fetch destinations are cached.' );
$assert( false !== strpos( $js, "q.has('ver')" ), 'Only safe WordPress asset version parameter is cache-eligible.' );
$assert( false !== strpos( $js, "filename.indexOf('..')===-1" ), 'Hidden and ambiguous file names cannot qualify for public cache.' );
$assert( false !== strpos( $js, "res.redirected" ) && false !== strpos( $js, "res.url===r.url" ), 'Redirected/substituted responses never populate asset cache.' );
$assert( false !== strpos( $js, "res.headers.get('vary')" ), 'Vary Cookie and Authorization responses are rejected.' );
$assert( false !== strpos( $js, "res.headers.get('content-type')" ), 'Only MIME-matched CSS and JavaScript are cache eligible.' );
$assert( false !== strpos( $js, "no-store" ) && false !== strpos( $js, "private" ), 'Origin private/no-store caching vetoes exist.' );
$assert( false === strpos( $js, "if(u.pathname.includes('/wp-content/plugins/sabri-unified-application-shell/'))" ), 'Old blanket plugin-folder caching removed.' );
$assert( false !== strpos( $js, "fetch(r,{cache:'no-store'})" ), 'Navigations never cache full HTML/patient pages.' );
if ( $errors ) { fwrite( STDERR, count( $errors ) . " PWA privacy source failure(s)\n" ); exit( 1 ); }
echo "V12.3 PWA cache source privacy guard PASS — no installed or Live acceptance implied.\n";
