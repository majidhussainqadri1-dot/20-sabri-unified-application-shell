<?php
/**
 * V12.3 acceptance-data gate: source test ONLY; no fake staging or Live.
 * @package SabriUnifiedApplicationShell
 */
$path = dirname( __DIR__ ) . '/contracts/file20-v12-3-acceptance.json';
$matrix = json_decode( (string) file_get_contents( $path ), true );
if ( ! is_array( $matrix ) ) { fwrite( STDERR, "V12.3 acceptance JSON invalid\n" ); exit( 1 ); }
$problems = array();
$check = static function ( $valid, $msg ) use ( &$problems ) {
    if ( ! $valid ) { $problems[] = $msg; echo "FAIL {$msg}\n"; }
    else { echo "PASS {$msg}\n"; }
};
$check( 'file20.v12.3.acceptance-evidence.v1' === ( $matrix['contract_id'] ?? '' ), 'Versioned V12.3 acceptance matrix.' );
$check( 'QA-PLANNING-ONLY' === ( $matrix['contract_kind'] ?? '' ), 'Matrix cannot claim operational state.' );
$qa_ids = array_column( $matrix['qa'] ?? array(), 'id' );
$expected_qa = array();
for ( $n = 15; $n <= 24; $n++ ) { $expected_qa[] = 'QA-' . $n; }
$check( $expected_qa === $qa_ids, 'QA-15 through QA-24 all listed exactly once.' );
$ux_ids = array_column( $matrix['ux'] ?? array(), 'id' );
$expected_ux = array();
for ( $n = 1; $n <= 14; $n++ ) { $expected_ux[] = sprintf( 'UX-%02d', $n ); }
$check( $expected_ux === $ux_ids, 'UX-01 through UX-14 all listed exactly once.' );
$gates = array_column( $matrix['gates'] ?? array(), 'id' );
$expected_gates = array();
for ( $n = 0; $n <= 12; $n++ ) { $expected_gates[] = 'G' . $n; }
$check( $expected_gates === $gates, 'G0 through G12 all listed exactly once.' );
$check( array( 320, 390, 480, 768, 1024, 1440, 1920 ) === ( $matrix['responsive_css_px'] ?? array() ), 'Seven ordered representative responsive widths.' );
foreach ( (array) ( $matrix['qa'] ?? array() ) as $test ) {
    foreach ( array( 'source_status', 'staging_status', 'live_status' ) as $status_field ) {
        $check( 'not-run' === ( $test[ $status_field ] ?? '' ), ( $test['id'] ?? 'QA unknown' ) . ' ' . $status_field . ' not falsely passed.' );
    }
    $check( empty( $test['evidence'] ), ( $test['id'] ?? 'QA unknown' ) . ' has no invented evidence.' );
}
foreach ( (array) ( $matrix['ux'] ?? array() ) as $test ) {
    $check( 'not-run' === ( $test['live_status'] ?? '' ), ( $test['id'] ?? 'UX unknown' ) . ' Live task not claimed.' );
}
$states = array( 'in-progress', 'blocked', 'partial', 'not-started' );
foreach ( (array) ( $matrix['gates'] ?? array() ) as $gate ) {
    $check( in_array( $gate['state'] ?? '', $states, true ), ( $gate['id'] ?? 'Gate unknown' ) . ' appropriately not complete.' );
    $check( empty( $gate['evidence'] ), ( $gate['id'] ?? 'Gate unknown' ) . ' has no synthetic acceptance evidence.' );
}
foreach ( array( 'deployed_version', 'db_version', 'migration_state', 'artifact_checksum' ) as $key ) {
    $check( 'unverified' === ( $matrix['live_evidence'][ $key ] ?? '' ), 'Live ' . $key . ' explicitly unknown.' );
}
$check( 'not-run' === ( $matrix['live_evidence']['live_verification_status'] ?? '' ), 'Live Verification explicitly not run.' );
$check( 'NOT RELEASE-APPROVED' === ( $matrix['authorization'] ?? '' ), 'No unauthorized production promotion.' );
$check( false === ( $matrix['visual_first_impression_pilot']['measured'] ?? true ), 'Pilot targets not fabricated as measured results.' );
if ( $problems ) { fwrite( STDERR, count( $problems ) . " acceptance matrix error(s)\n" ); exit( 1 ); }
echo "V12.3 QA/UX/Gates planning matrix PASS; all real environment tests remain NOT RUN.\n";
