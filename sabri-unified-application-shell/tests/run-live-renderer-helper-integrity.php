<?php
/**
 * Permanent regression for the live File 20 Renderer helper-deletion incident.
 *
 * Development-only CI test; tests/ is excluded from the production ZIP.
 */
declare(strict_types=1);

$renderer_path = dirname( __DIR__ ) . '/includes/class-renderer.php';
$renderer      = file_get_contents( $renderer_path );

if ( false === $renderer ) {
	fwrite( STDERR, "Unable to read class-renderer.php\n" );
	exit( 1 );
}

$required_helpers = array(
	'render_panel',
	'destination_url',
	'item_visible_to_user',
);

foreach ( $required_helpers as $helper ) {
	$definition_pattern = '/\b(?:public|protected|private)\s+static\s+function\s+' . preg_quote( $helper, '/' ) . '\s*\(/';
	$count = preg_match_all( $definition_pattern, $renderer );
	if ( 1 !== $count ) {
		fwrite( STDERR, sprintf( "Renderer helper %s must have exactly one static definition; found %d.\n", $helper, (int) $count ) );
		exit( 1 );
	}

	if ( false === strpos( $renderer, 'self::' . $helper . '(' ) && false === strpos( $renderer, "array( __CLASS__, '" . $helper . "' )" ) ) {
		fwrite( STDERR, sprintf( "Renderer helper %s is defined but no owned call/callback site remains.\n", $helper ) );
		exit( 1 );
	}
}

preg_match_all( '/\b(?:public|protected|private)\s+(?:static\s+)?function\s+([A-Za-z_][A-Za-z0-9_]*)\s*\(/', $renderer, $defined_matches );
$defined_methods = array_fill_keys( $defined_matches[1], true );

preg_match_all( '/\bself::([A-Za-z_][A-Za-z0-9_]*)\s*\(/', $renderer, $self_call_matches );
$self_calls = array_values( array_unique( $self_call_matches[1] ) );

$missing = array();
foreach ( $self_calls as $method ) {
	if ( ! isset( $defined_methods[ $method ] ) ) {
		$missing[] = $method;
	}
}

if ( $missing ) {
	fwrite( STDERR, 'Renderer contains unresolved self:: method call(s): ' . implode( ', ', $missing ) . "\n" );
	exit( 1 );
}

if ( false === strpos( $renderer, "array_filter( $group_items, array( __CLASS__, 'item_visible_to_user' ) )" ) ) {
	fwrite( STDERR, "Renderer visibility callback regression guard is missing.\n" );
	exit( 1 );
}

echo "Live renderer helper integrity regression PASS\n";
