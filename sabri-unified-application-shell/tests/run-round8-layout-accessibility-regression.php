<?php
/** Round 8: layout/theme-DOM/accessibility regression. */
$root = dirname( __DIR__ );
$fail = static function ( $message ) { fwrite( STDERR, "FAIL: {$message}\n" ); exit( 1 ); };
$read = static function ( $path ) use ( $root, $fail ) {
    $data = file_get_contents( $root . '/' . $path );
    if ( false === $data ) { $fail( 'Cannot read ' . $path ); }
    return $data;
};
$context = $read( 'includes/class-context-navigation.php' );
$css     = $read( 'assets/css/context-navigation.css' );
$layout  = $read( 'includes/class-layout.php' );
$shelljs = $read( 'assets/js/shell.js' );

if ( false === strpos( $context, 'Layout::IMMERSIVE' ) ) { $fail( 'Immersive mode lost File 20 accessible exit/restoration controls.' ); }
if ( false === strpos( $context, 'data-sabri-context-back' ) || false === strpos( $context, 'data-home-url' ) ) { $fail( 'Back/Home restoration contract missing.' ); }
if ( 1 === preg_match( '/direction\s*:\s*rtl\s*;/', $css ) ) { $fail( 'Context navigation still forces RTL in every locale.' ); }
if ( false === strpos( $css, 'direction: inherit;' ) || false === strpos( $css, 'body.sabri-shell-layout-immersive .sabri-context-navigation' ) ) { $fail( 'Bidirectional immersive exit styling missing.' ); }
if ( false === strpos( $css, "html[dir='ltr'] .sabri-context-navigation__back-icon" ) ) { $fail( 'LTR Back icon direction correction missing.' ); }
if ( false === strpos( $layout, 'Layout::IMMERSIVE' ) && false === strpos( $layout, 'self::IMMERSIVE' ) ) { $fail( 'Four-mode resolver lost Immersive mode.' ); }
foreach ( array( 'appendChild', 'insertBefore', 'replaceChild' ) as $surgery ) {
    if ( false !== strpos( $shelljs, $surgery . '(target' ) ) { $fail( 'Shell structural target is being reparented: ' . $surgery ); }
}
if ( false === strpos( $shelljs, "target.classList.add('sabri-shell-content-column')" ) ) { $fail( 'Bounded content annotation missing.' ); }

echo "Round 8 layout/theme-DOM/accessibility regression PASS\n";
