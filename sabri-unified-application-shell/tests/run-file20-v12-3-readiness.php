<?php
/**
 * V12.3 source-readiness contract regression. This is not a Live acceptance test.
 * @package SabriUnifiedApplicationShell
 */
require_once __DIR__ . '/bootstrap.php';
require_once dirname( __DIR__ ) . '/includes/class-future-shell-v5.php';

use Sabri\UnifiedShell\Defaults;
use Sabri\UnifiedShell\FutureShellV5;

$failures = array();
$assert = static function ( $ok, $message ) use ( &$failures ) {
    if ( ! $ok ) { $failures[] = $message; echo "FAIL: {$message}\n"; }
    else { echo "PASS: {$message}\n"; }
};

$path = dirname( __DIR__ ) . '/contracts/file20-v12-3-readiness.json';
$data = json_decode( (string) file_get_contents( $path ), true );
if ( ! is_array( $data ) ) { fwrite( STDERR, "Invalid V12.3 readiness JSON\n" ); exit( 1 ); }
$assert( 'file20.v12.3.source-readiness.v1' === ( $data['contract_id'] ?? null ), 'Correct non-runtime V12.3 contract identity.' );
$assert( 'NON-RUNTIME / SOURCE-READINESS ONLY' === ( $data['classification'] ?? null ), 'This artifact cannot masquerade as installed runtime.' );
$assert( '2026-11-13' === ( $data['plan_date'] ?? null ), 'V12.3 governing plan date is pinned.' );
$expected_destinations = array( 'home', 'news', 'founder', 'learn', 'encyclopedia', 'doctors', 'clinic', 'video_wall', 'reels', 'pdf_library', 'radar', 'ai', 'network', 'marketplace' );
$actual_destinations = array_keys( Defaults::destinations() );
$assert( $expected_destinations === ( $data['primary_destinations'] ?? array() ), 'Exactly 14 V12.3 canonical menu destinations.' );
$assert( ! array_diff( $expected_destinations, $actual_destinations ), 'All 14 destinations have existing File20 registry keys.' );
$assert( count( $actual_destinations ) >= 14, 'Non-primary native destination entries remain available.' );

$ids = array();
$allowed_states = array( 'partial', 'draft-source', 'not-started', 'unverified', 'blocked', 'source-present' );
foreach ( (array) ( $data['requirements'] ?? array() ) as $row ) {
    $ids[] = $row['id'] ?? '';
    $assert( in_array( $row['state'] ?? '', $allowed_states, true ), 'Non-operational status for ' . ( $row['id'] ?? 'missing requirement' ) );
}
$expected_ids = array();
for ( $n = 26; $n <= 36; ++$n ) { $expected_ids[] = sprintf( 'CV-%03d', $n ); }
$assert( $expected_ids === $ids, 'CV-026 through CV-036 are ordered once, with no omissions.' );

$expected_groups = array( 'account', 'security', 'privacy', 'language', 'display', 'notifications', 'connection' );
$group_ids = array();
$control_ids = array();
foreach ( (array) ( $data['settings_groups'] ?? array() ) as $group ) {
    $group_ids[] = $group['id'] ?? '';
    foreach ( (array) ( $group['controls'] ?? array() ) as $control ) {
        $key = $control['key'] ?? '';
        $control_ids[] = $key;
        $assert( 0 === strpos( $key, ( $group['id'] ?? '_invalid' ) . '.' ), 'Control key has proper owner-group prefix: ' . $key );
        $assert( 'native-owner-only' === ( $control['storage'] ?? '' ), 'File20 creates no independent private store for ' . $key );
        $assert( 'unverified-provider' === ( $control['state'] ?? '' ), 'Provider cannot be promoted to available by manifest alone: ' . $key );
        $assert( ! empty( $control['owner'] ), 'Canonical owner listed for ' . $key );
    }
}
$assert( $expected_groups === $group_ids, 'Exactly seven ordered V12.3 settings groups.' );
$assert( 23 === count( $control_ids ) && 23 === count( array_unique( $control_ids ) ), 'Exactly 23 unique settings controls.' );
$expected_controls = array(
    'account.profile', 'account.membership', 'account.credentials',
    'security.sessions', 'security.passkeys', 'security.recovery',
    'privacy.consent', 'privacy.analytics', 'privacy.requests',
    'language.ui', 'language.content', 'language.edition', 'language.reading',
    'display.theme', 'display.text', 'display.motion', 'display.captions',
    'notifications.quiet', 'notifications.channels', 'notifications.focus',
    'connection.visibility', 'connection.data', 'connection.playback',
);
$assert( $expected_controls === $control_ids, 'Every approved control key and ordering is stable.' );

$actual_features = array_keys( FutureShellV5::features() );
$assert( 18 === count( $actual_features ) && $actual_features === ( $data['future_shell_features'] ?? array() ), 'Exactly 18 Future Shell v5 keys; no nineteenth feature.' );
$wp = array_column( (array) ( $data['work_packages'] ?? array() ), 'id' );
$assert( 26 === count( $wp ) && 26 === count( array_unique( $wp ) ), '26 unique controlled work packages.' );
foreach ( array( 'deployed_version', 'db_version', 'migration_state', 'live_verification', 'wp_options', 'runtime_logs', 'package_checksum' ) as $field ) {
    $assert( 'unverified' === ( $data['live'][ $field ] ?? '' ), 'Current Live ' . $field . ' remains explicitly unverified.' );
}
$assert( 'not-started' === ( $data['review']['round_1'] ?? '' ) && 'not-started' === ( $data['review']['round_2'] ?? '' ), 'The two complete corrective review rounds are not claimed prematurely.' );
$assert( 0 === ( $data['review']['fixed_runtime_defects'] ?? -1 ), 'No invented Live fix count.' );
$assert( 8 === count( $data['release_gates'] ?? array() ), 'All eight independent release gates are recorded.' );

if ( $failures ) { fwrite( STDERR, count( $failures ) . " V12.3 contract failure(s)\n" ); exit( 1 ); }
echo "File20 V12.3 source-readiness contract PASS (no staging or live acceptance implied).\n";
