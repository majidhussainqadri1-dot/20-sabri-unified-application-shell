<?php
/**
 * File20 V12.3 canonical navigation item attribute regression.
 * Independent source-only test: no installed/staging/live assertions.
 */
require __DIR__ . '/bootstrap.php';
require_once dirname( __DIR__ ) . '/includes/class-renderer.php';
if ( ! function_exists( 'esc_url' ) ) { function esc_url( $text ) { return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' ); } }
if ( ! function_exists( 'esc_attr' ) ) { function esc_attr( $text ) { return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' ); } }
if ( ! function_exists( 'esc_html' ) ) { function esc_html( $text ) { return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' ); } }
$failures = array();
$ok = static function ( $condition, $why ) use ( &$failures ) {
    echo ( $condition ? 'PASS: ' : 'FAIL: ' ) . $why . "\n";
    if ( ! $condition ) { $failures[] = $why; }
};
$primary = array( 'home','news','founder','learn','encyclopedia','doctors','clinic','video_wall','reels','pdf_library','radar','ai','network','marketplace' );
$all = array_keys( Sabri\UnifiedShell\Defaults::destinations() );
$ok( 14 === count( $primary ) && ! array_diff( $primary, $all ), 'All 14 canonical destinations already exist in the native source registry.' );
$ref = new ReflectionMethod( Sabri\UnifiedShell\Renderer::class, 'render_nav_item' );
$ref->setAccessible( true );
ob_start();
$ref->invoke( null, array(
    'key' => 'learn', 'url' => 'https://example.test/learn/', 'label' => 'Education',
    'visibility' => 'public'
) );
$html = ob_get_clean();
$ok( false !== strpos( $html, 'data-sabri-destination="learn"' ), 'Owner key is exposed as a data attribute for the progressive menu.' );
$ok( false !== strpos( $html, 'href="https://example.test/learn/"' ), 'Native resolved route remains intact.' );
$ok( false !== strpos( $html, '>Education</a>' ), 'Owner resolved label preserved.' );
$primary_ref = new ReflectionMethod( Sabri\UnifiedShell\Renderer::class, 'render_primary_nav' );
$primary_ref->setAccessible( true );
$sample = array();
foreach ( array( 'home', 'news', 'founder', 'learn' ) as $key ) {
    $sample[ $key ] = array( 'key' => $key, 'url' => 'https://example.test/' . $key . '/', 'label' => ucfirst( $key ), 'visibility' => 'public' );
}
ob_start();
$primary_ref->invoke( null, $sample );
$short_menu_html = ob_get_clean();
$ok( false !== strpos( $short_menu_html, 'class="sabri-shell-nav-more" hidden' ), 'Even a four-destination menu supplies one initially hidden More reflow target.' );
$ok( 4 === substr_count( $short_menu_html, 'data-sabri-destination=' ), 'Small-route fallback preserves all four accessible routes exactly once.' );
$src = (string) file_get_contents( dirname( __DIR__ ) . '/includes/class-assets.php' );
$ok( false !== strpos( $src, 'v12-3-adaptive-navigation.js' ) && false !== strpos( $src, 'v12-3-adaptive-navigation.css' ), 'Assets registered with native shell rather than a second menu.' );
$renderer = (string) file_get_contents( dirname( __DIR__ ) . '/includes/class-renderer.php' );
$ok( false !== strpos( $renderer, 'array_slice( $visible, 0, 6 )' ), 'No-JS fixed six and More remains a complete accessible fallback.' );
if ( $failures ) { fwrite( STDERR, count( $failures ) . " adaptive navigation error(s)\n" ); exit( 1 ); }
echo "V12.3 responsive menu source regression PASS; browser and installed acceptance unverified.\n";
