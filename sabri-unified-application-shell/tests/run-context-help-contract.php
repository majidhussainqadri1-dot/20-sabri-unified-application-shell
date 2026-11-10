<?php
/** CV-033 native-owner contextual help contract and privacy adversarial regression. */
require_once __DIR__ . '/bootstrap.php';
require_once dirname( __DIR__ ) . '/includes/class-context-help.php';

use Sabri\UnifiedShell\ContextHelp;

$assert = static function ( $yes, $reason ) {
	if ( ! $yes ) { fwrite( STDERR, "Context Help FAIL: {$reason}\n" ); exit( 1 ); }
};
$_SERVER['REQUEST_URI'] = '/learn/';
$base = array(
	'contract_version' => '1.0.0',
	'owner' => 'file-05',
	'route_url' => 'https://example.test/learn/',
	'visibility' => 'public-safe-guidance',
	'explanation' => 'This section explains the current lesson.',
	'example' => 'For example: begin with unit one.',
	'glossary' => array( array( 'term' => 'Lesson', 'definition' => 'One chapter.' ) ),
	'support_url' => 'https://example.test/support/',
);
$assert( array() === ContextHelp::validate( $base ), 'Owner approval must fail closed by default.' );
$GLOBALS['test_filter_overrides']['sabri_shell_context_help_owner_approved'] = true;
$good = ContextHelp::validate( $base );
$assert( 'file-05' === ( $good['owner'] ?? '' ) && 1 === count( $good['glossary'] ?? array() ), 'Explicitly approved versioned same-origin help must be valid.' );
foreach ( array(
	array( 'contract_version' => '2.0.0' ),
	array( 'owner' => 'file-99' ),
	array( 'route_url' => 'https://attacker.test/learn/' ),
	array( 'route_url' => 'http://example.test/learn/' ),
	array( 'route_url' => 'https://example.test/learn/?token=private' ),
	array( 'route_url' => 'https://example.test/learn/#fragment' ),
	array( 'route_url' => 'https://example.test/%2flearn/' ),
	array( 'route_url' => 'https://example.test/%252flearn/' ),
	array( 'route_url' => 'https://example.test/other/' ),
	array( 'visibility' => 'private-patient-data' ),
	array( 'explanation' => '' ),
) as $mutation ) {
	$assert( array() === ContextHelp::validate( array_merge( $base, $mutation ) ), 'Mutated untrusted contract must fail closed: ' . implode( ', ', array_keys( $mutation ) ) );
}
$oversized = $base;
$oversized['explanation'] = str_repeat( 'A', 3601 );
$assert( array() === ContextHelp::validate( $oversized ), 'Oversized help text must be rejected.' );
$glossary = $base;
$glossary['glossary'] = array_merge( $base['glossary'], array_fill( 0, 30, array( 'term' => 'A', 'definition' => 'B' ) ) );
$assert( 8 === count( ContextHelp::validate( $glossary )['glossary'] ), 'Glossary must be bounded.' );
$bad_support = $base;
$bad_support['support_url'] = 'https://attacker.test/collect';
$assert( '' === ContextHelp::validate( $bad_support )['support_url'], 'External support links must not render.' );
$_SERVER['REQUEST_URI'] = '/another/';
$assert( array() === ContextHelp::validate( $base ), 'Context must match exact requested route.' );
echo "Contextual Help CV-033 owner-contract regression PASS\n";
