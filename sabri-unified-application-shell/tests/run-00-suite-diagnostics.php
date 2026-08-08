<?php
/** Development-only CI diagnostic runner; tests/ is excluded from production ZIP. */
declare(strict_types=1);

$self = realpath( __FILE__ );
$tests = glob( __DIR__ . '/run*.php' );
sort( $tests );
$failures = array();
foreach ( $tests as $test ) {
    if ( realpath( $test ) === $self ) { continue; }
    $cmd = escapeshellarg( PHP_BINARY ) . ' ' . escapeshellarg( $test ) . ' 2>&1';
    $output = array();
    $exit = 0;
    exec( $cmd, $output, $exit );
    if ( 0 !== $exit ) {
        $relative = basename( $test );
        $message = $output ? implode( ' | ', array_slice( $output, -12 ) ) : 'No test output captured.';
        $message = str_replace( array( "\r", "\n", '::' ), array( ' ', ' ', ': :' ), $message );
        echo '::error file=sabri-unified-application-shell/tests/' . $relative . ',title=File 20 regression failed::' . $relative . ' — ' . $message . "\n";
        $failures[] = $relative;
    }
}
if ( $failures ) {
    fwrite( STDERR, 'Diagnostic suite failures: ' . implode( ', ', $failures ) . "\n" );
    exit( 1 );
}
echo "Diagnostic runner: every File 20 regression suite PASS\n";
