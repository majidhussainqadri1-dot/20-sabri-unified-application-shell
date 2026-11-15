<?php
/**
 * V12.3 real ephemeral WordPress wp_options audit-gap recovery and rollback.
 *
 * This file is exclusively a WP-CLI test against GitHub Actions' disposable
 * MySQL service; it must NOT run against a deployed/staging WordPress DB.
 */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI || ! defined( 'ABSPATH' ) ||
	! defined( 'SABRI_SHELL_VERSION' ) || ! defined( 'SABRI_PUBLIC_EXPERIENCE_VERSION' ) ) {
	fwrite( STDERR, "This fixture needs isolated WordPress, File20 and File25.\n" );
	exit( 2 );
}
$fail = static function ( $message ) { WP_CLI::error( '[V12.3 ephemeral audit] ' . $message ); };
$check = static function ( $condition, $message ) use ( $fail ) {
	if ( ! $condition ) { $fail( $message ); }
};
$owner = 'Sabri\\UnifiedShell\\WelcomeExperience';
$founder = get_user_by( 'login', 'ci_founder' );
$check( $founder instanceof \WP_User && $owner::founder_authorized( $founder->ID ),
	'Canonical File00 Founder authority unavailable.' );
wp_set_current_user( (int) $founder->ID );
$config = get_option( $owner::OPTION, false );
$approval = get_option( $owner::APPROVAL_OPTION, false );
$check( is_array( $config ) && ! empty( $config['enabled'] ), 'Enabled source CI welcome is missing.' );
$check( is_array( $approval ) && $owner::approval_valid( $owner::configuration() ),
	'Previously approved Founder acceptance is invalid.' );
$check( false === get_option( $owner::AUDIT_GAP_OPTION, false ), 'Ephemeral DB must start without an audit gap.' );

// Append a real bounded audit row through the genuine private owner function.
$append = new \ReflectionMethod( $owner, 'append_audit' );
$append->setAccessible( true );
$check( true === $append->invoke( null, array(
	'event' => 'ci_audit_rollout',
	'actor' => (int) $founder->ID,
	'at' => gmdate( 'c' ),
) ), 'Native wp_options CAS append failed on a real MySQL-backed WordPress.' );
$audit = get_option( $owner::AUDIT_OPTION, array() );
$check( is_array( $audit ) && count( $audit ) > 0, 'Audit append was not durably visible through the WordPress option cache.' );
$last = end( $audit );
$check( is_array( $last ) && 'ci_audit_rollout' === $last['event'], 'Audit journal lost its latest entry.' );

// Preserve exactly what existed, exercise interruption, then reconcile.
$initial_gap = array(
	'detected_at' => gmdate( 'c' ),
	'event' => 'ci_simulated_concurrent_failure',
	'actor' => (int) $founder->ID,
);
update_option( $owner::AUDIT_GAP_OPTION, $initial_gap, false );
$check( ! $owner::approval_valid( $owner::configuration() ), 'An unresolved audit gap failed to invalidate approval.' );
wp_set_current_user( 0 );
$_SERVER['REQUEST_URI'] = '/';
$check( ! $owner::public_eligible(), 'An unresolved audit gap permitted a guest welcome.' );
wp_set_current_user( (int) $founder->ID );

$reconcile = new \ReflectionMethod( $owner, 'reconcile_gap' );
$reconcile->setAccessible( true );
$check( true === $reconcile->invoke( null, (int) $founder->ID ),
	'Founder-approved CAS reconciliation did not remove exact matching audit gap.' );
$check( false === get_option( $owner::AUDIT_GAP_OPTION, false ), 'Reconciled audit gap remains.' );
$check( false === get_option( $owner::APPROVAL_OPTION, false ), 'Stale prior Founder approval survived reconciliation.' );
$journal = get_option( $owner::AUDIT_OPTION, array() );
$latest = end( $journal );
$check( 'audit_gap_reconciled' === ( $latest['event'] ?? '' ) &&
		false === ( $latest['historical_entries_reconstructed'] ?? true ),
	'Reconciliation must not fabricate missing historical audit records.' );
$check( ! $owner::approval_valid( $owner::configuration() ),
	'Automatic welcome enabled after reconciliation without a fresh Founder approval.' );

// Simulate a RESTORE from an exact previously verified configuration snapshot;
// preserve the subsequent durable audit evidence, never silently delete it.
$check( update_option( $owner::OPTION, $config, false ) || get_option( $owner::OPTION ) === $config,
	'Idempotent DB config restore failed.' );
$check( update_option( $owner::APPROVAL_OPTION, $approval, false ),
	'Restore of previously trusted source-bound approval record failed.' );
$check( $owner::approval_valid( $owner::configuration() ),
	'Restored approved config and unchanged source hashes failed approval check.' );
$check( count( get_option( $owner::AUDIT_OPTION, array() ) ) >= count( $journal ),
	'Rollback must not destroy append-only audit evidence.' );
wp_set_current_user( 0 );
$check( $owner::public_eligible(), 'Restored approved guest introduction did not reactivate.');
WP_CLI::success( 'Real WordPress/MySQL File20 welcome CAS, audit interruption, Founder reconciliation, safe suppression and verified restore passed.' );
