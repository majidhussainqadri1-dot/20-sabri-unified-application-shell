<?php
/** Static guard for File20's single canonical programmatic settings writer. */
declare(strict_types=1);
$root = dirname( __DIR__ );
$allowed = realpath( $root . '/includes/class-settings.php' );
$violations = array();
$it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS ) );
foreach ( $it as $file ) {
    if ( ! $file->isFile() || 'php' !== strtolower( $file->getExtension() ) ) { continue; }
    $path = $file->getRealPath();
    if ( $path === $allowed || false !== strpos( $path, DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR ) ) { continue; }
    $code = (string) file_get_contents( $path );
    if ( preg_match( '/update_option\s*\(\s*Defaults::OPTION_NAME\b/', $code ) ) {
        $violations[] = $path;
    }
}
if ( $violations ) {
    fwrite( STDERR, "Direct File20 settings writes bypass the canonical writer:\n" . implode( "\n", $violations ) . "\n" );
    exit( 1 );
}
$settings = (string) file_get_contents( $allowed );
foreach ( array( 'public static function update_programmatically', "remove_filter( \$hook, \$callback, 10 )", 'self::enforce_owned_invariants( $settings )', "add_filter( \$hook, \$callback, 10, 1 )" ) as $needle ) {
    if ( false === strpos( $settings, $needle ) ) { fwrite( STDERR, "Canonical writer invariant missing: {$needle}\n" ); exit( 1 ); }
}
echo "File20 canonical programmatic settings writer static guard PASS\n";
