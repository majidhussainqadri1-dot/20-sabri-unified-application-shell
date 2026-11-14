<?php
/**
 * V12.3 File25→File20 PWA color source parity. Not a Live acceptance claim.
 */
require __DIR__ . '/bootstrap.php';
require_once dirname( __DIR__ ) . '/includes/class-future-shell-v5.php';

use Sabri\UnifiedShell\FutureShellV5;

if ( ! function_exists( 'get_bloginfo' ) ) {
    function get_bloginfo( $field ) {
        return 'language' === $field ? 'ur-PK' : 'Sabri Homeopathy';
    }
}
$errors = array();
$check = static function ( $ok, $msg ) use ( &$errors ) {
    echo ( $ok ? 'PASS: ' : 'FAIL: ' ) . $msg . "\n";
    if ( ! $ok ) { $errors[] = $msg; }
};
$ref = new ReflectionMethod( FutureShellV5::class, 'manifest' );
$ref->setAccessible( true );
$manifest = $ref->invoke( null );
$check( is_array( $manifest ) && '#087A4E' === ( $manifest['theme_color'] ?? '' ), 'Installable manifest uses canonical V12.3/File25 green.' );
$check( 'ur-PK' === ( $manifest['lang'] ?? '' ), 'PWA preserves site language rather than fixing locale.' );

$src = (string) file_get_contents( dirname( __DIR__ ) . '/includes/class-future-shell-v5.php' );
$check( false === stripos( $src, '#16803a' ), 'Retired inconsistent shell green is absent from PWA source.' );
$check( false !== strpos( $src, '<meta name="theme-color" content="#087A4E">' ), 'Browser theme meta matches the installable manifest.' );
$check( 2 === substr_count( $src, '#087A4E' ), 'PWA color appears in exactly two synchronized shell surfaces.' );
if ( $errors ) { fwrite( STDERR, count( $errors ) . " PWA source parity failure(s)\n" ); exit( 1 ); }
echo "V12.3 File20/File25 PWA source parity PASS — installed/live not verified.\n";
