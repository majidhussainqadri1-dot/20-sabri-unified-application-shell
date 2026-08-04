<?php
/**
 * Static regression suite for the shared Back + Home controls.
 */

$root = dirname( __DIR__ );
$php  = file_get_contents( $root . '/includes/class-context-navigation.php' );
$js   = file_get_contents( $root . '/assets/js/context-navigation.js' );
$css  = file_get_contents( $root . '/assets/css/context-navigation.css' );
$plug = file_get_contents( $root . '/includes/class-plugin.php' );

$assert = static function ( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		exit( 1 );
	}
};

$assert( false !== strpos( $plug, 'ContextNavigation::register();' ), 'Plugin coordinator must register ContextNavigation.' );
$assert( false !== strpos( $php, 'sabri_shell_context_navigation_enabled' ), 'Render availability must be filterable by documented context.' );
$assert( false !== strpos( $php, 'sabri_shell_context_navigation_fallback_url' ), 'Native modules need a bounded fallback filter.' );
$assert( false !== strpos( $php, 'same_origin_url' ), 'Server fallback must pass same-origin validation.' );
$assert( false !== strpos( $php, "array( 'http', 'https' )" ), 'Only HTTP(S) fallback schemes are permitted.' );
$assert( false !== strpos( $php, '$scheme !== $home_scheme' ), 'Server validation must compare the complete scheme-aware origin.' );
$assert( false !== strpos( $php, 'data-home-url' ), 'The client must receive the canonical Home fallback, including subdirectory installs.' );
$assert( false !== strpos( $php, "href=\"' . esc_url( \$fallback_url )" ), 'Back must remain a functional fallback link without JavaScript.' );
$assert( false !== strpos( $php, 'private static $rendered = false' ), 'Duplicate Back + Home output must be guarded.' );
$assert( false === strpos( $php, 'return_to' ), 'Untrusted return_to input must not control Back navigation.' );
$assert( false !== strpos( $js, 'url.origin !== window.location.origin' ), 'Client navigation must enforce same-origin URLs.' );
$assert( false !== strpos( $js, "url.protocol !== 'http:'" ), 'Client navigation must restrict URL schemes.' );
$assert( false !== strpos( $js, 'sabriShellContextNavigationStack' ), 'Back behavior must use a bounded same-origin session stack.' );
$assert( false !== strpos( $js, 'event.preventDefault()' ), 'JavaScript enhancement must preserve the no-JavaScript fallback link.' );
$assert( false !== strpos( $js, "link.getAttribute('data-home-url')" ), 'Client fallback must preserve the canonical WordPress Home URL.' );
$assert( false === strpos( $js, 'window.history.forward' ), 'A generic permanent Forward control is forbidden.' );
$assert( false === strpos( $js, 'window.history.back' ), 'Back must not risk crossing to an unverifiable external history entry.' );
$assert( false === strpos( $js, 'innerHTML' ), 'Context navigation JavaScript must not inject HTML.' );
$assert( false !== strpos( $css, 'min-block-size: 44px' ), 'Controls must satisfy the minimum touch target.' );
$assert( false !== strpos( $css, ':focus-visible' ), 'Visible keyboard focus is required.' );
$assert( false !== strpos( $css, 'direction: rtl' ), 'Right-priority RTL placement is required.' );
$assert( substr_count( $css, '{' ) === substr_count( $css, '}' ), 'Context-navigation CSS braces must balance.' );

echo "Context navigation static regressions passed.\n";
