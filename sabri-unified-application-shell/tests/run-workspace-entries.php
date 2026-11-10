<?php
/** CV-034/035: generic, authenticated cross-module workspace entry consumer. */
require_once __DIR__ . '/bootstrap.php';
require_once dirname( __DIR__ ) . '/includes/class-workspace-entries.php';
use Sabri\UnifiedShell\WorkspaceEntries;

$assert = static function ( $value, $message ) {
	if ( ! $value ) { fwrite( STDERR, "Workspace Entries FAIL: {$message}\n" ); exit( 1 ); }
};
$valid = array(
	'version' => '1.0.0',
	'principal_id' => 42,
	'items' => array(
		array( 'kind' => 'recent', 'owner' => 'file-05', 'intent' => 'entry-only', 'label' => 'Courses', 'url' => 'https://example.test/learn/' ),
		array( 'kind' => 'saved', 'owner' => 'file-12', 'intent' => 'entry-only', 'label' => 'Library bookmarks', 'url' => 'https://example.test/pdf-library/' ),
	),
);
$assert( array() === WorkspaceEntries::validate_for_principal( $valid, 42, false ), 'Guest cannot see private links.' );
$assert( array() === WorkspaceEntries::validate_for_principal( $valid, 43, true ), 'Other principal cannot see private links.' );
$a = WorkspaceEntries::validate_for_principal( $valid, 42, true );
$assert( ! $a['recent'] && ! $a['saved'], 'Without owner attestation nothing is mounted.' );
$GLOBALS['test_filter_overrides']['sabri_shell_workspace_entry_owner_approved'] = true;
$a = WorkspaceEntries::validate_for_principal( $valid, 42, true );
$assert( 1 === count( $a['recent'] ) && 1 === count( $a['saved'] ), 'Two approved native hubs should mount.' );
$assert( ! isset( $a['recent'][0]['entity_id'] ) && ! isset( $a['recent'][0]['content'] ), 'No native private object is copied.' );
foreach ( array(
	array( 'version' => '9.9.9' ),
	array( 'principal_id' => 43 ),
	array( 'items' => array_fill( 0, 11, $valid['items'][0] ) ),
) as $mutation ) {
	$assert( array() === WorkspaceEntries::validate_for_principal( array_merge( $valid, $mutation ), 42, true ), 'Invalid version/principal/size fails closed.' );
}
$bad_entries = array(
	array( 'kind' => 'recent', 'owner' => 'file-05', 'intent' => 'full-private-record', 'label' => 'secret', 'url' => 'https://example.test/learn/' ),
	array( 'kind' => 'recent', 'owner' => 'file-99', 'intent' => 'entry-only', 'label' => 'other', 'url' => 'https://example.test/learn/' ),
	array( 'kind' => 'recent', 'owner' => 'file-05', 'intent' => 'entry-only', 'label' => 'other', 'url' => 'https://outside.test/steal' ),
	array( 'kind' => 'recent', 'owner' => 'file-05', 'intent' => 'entry-only', 'label' => 'other', 'url' => 'https://example.test/learn/?token=abc' ),
	array( 'kind' => 'recent', 'owner' => 'file-05', 'intent' => 'entry-only', 'label' => 'other', 'url' => 'https://example.test/%2flearn/' ),
);
$mutated = $valid;
$mutated['items'] = $bad_entries;
$a = WorkspaceEntries::validate_for_principal( $mutated, 42, true );
$assert( ! $a['recent'] && ! $a['saved'], 'Forbidden private data intents and unsafe URLs rejected.' );
$twice = $valid;
$twice['items'][] = $valid['items'][0];
$a = WorkspaceEntries::validate_for_principal( $twice, 42, true );
$assert( 1 === count( $a['recent'] ), 'Duplicate links must collapse.' );
echo "CV-034/035 owner-gated workspace entry contract PASS\n";
