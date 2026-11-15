<?php
/**
 * Explicit File00 deferred installer for a FRESH isolated CI WordPress DB.
 * Not a production migration utility, not installable as a plugin.
 * File00 1.2.44 intentionally does not migrate in the activation HTTP request.
 */
if ( ! defined( 'ABSPATH' ) || ! defined( 'WP_CLI' ) || ! WP_CLI ||
	'1' !== getenv( 'SABRI_EPHEMERAL_CI' ) || ! in_array( wp_parse_url( home_url( '/' ), PHP_URL_HOST ), array( 'localhost', '127.0.0.1' ), true ) ) {
	fwrite( STDERR, "Isolated CI/localhost only; refusing File00 migration.\n" );
	exit( 2 );
}
$assert = static function ( $ok, $message ) {
	if ( ! $ok ) { WP_CLI::error( '[CI deferred File00 migration] ' . $message ); }
};
$assert( defined( 'SMC_DB_VERSION' ) && class_exists( 'SMC_Installer' ) &&
	class_exists( 'SMC_Security' ) && class_exists( 'SMC_Schema_Compat' ), 'Verified File00 plugin must be active.' );
$pre = (string) get_option( 'smc_db_version', '' );
WP_CLI::log( 'File00 schema preflight: current=' . ( '' === $pre ? 'missing' : $pre ) .
	', target=' . SMC_DB_VERSION . ', pending=' . ( get_option( 'smc_activation_pending_v2', false ) ? 'yes' : 'no' ) );
$ready = SMC_Security::ensure_key_ready();
$assert( ! is_wp_error( $ready ), 'Real File00 key prerequisite unavailable.' );
$audit = SMC_Installer::ensure_audit_infrastructure();
$assert( ! is_wp_error( $audit ), 'Real File00 audit infrastructure failed.' );
SMC_Schema_Compat::reconcile_verification_queue_index();
SMC_Installer::maybe_upgrade();
global $wpdb;
foreach ( array( 'smc_applications', 'smc_role_grants', 'smc_migrations' ) as $name ) {
	$table = $wpdb->prefix . $name;
	$found = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) ) );
	$assert( $table === $found, 'Required File00 table missing after real deferred migration: ' . $name );
}
$version = (string) get_option( 'smc_db_version', '' );
$deferred = get_option( 'smc_migration_deferred_v1', false );
$failure = get_option( 'smc_last_migration_failure', false );
$assert( SMC_DB_VERSION === $version, 'File00 migration not current; do not continue into File20/25 integration. Current=' . $version );
$assert( empty( $deferred ), 'File00 schema still has deferred migration state.' );
$assert( empty( $failure ), 'File00 schema still has migration failure state.' );
SMC_Schema_Compat::assert_current_queue_indexes();
WP_CLI::success( 'File00 genuine deferred installer completed with real tables, version ' . $version . ' and no failure/defer marker.' );
