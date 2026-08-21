<?php
/** Explicit-policy-only File 20 uninstall cleanup. */
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) { exit; }
$settings = get_option( 'sabri_shell_settings', array() );
if ( ! is_array( $settings ) || empty( $settings['delete_on_uninstall'] ) ) { return; }
wp_clear_scheduled_hook( 'sabri_shell_plan_v4_maintenance' );
$shortcode_cache_keys = get_option( 'sabri_shell_shortcode_cache_keys', array() );
if ( is_array( $shortcode_cache_keys ) ) {
    foreach ( array_slice( array_values( array_unique( array_map( 'sanitize_key', $shortcode_cache_keys ) ) ), 0, 64 ) as $transient_name ) {
        if ( 0 === strpos( $transient_name, 'sabri_shell_shortcode_page_' ) ) { delete_transient( $transient_name ); }
    }
}
$file20_options = array(
    'sabri_shell_settings','sabri_unified_shell_settings','sabri_shell_activation_snapshot','sabri_shell_activation_snapshot_legacy',
    'sabri_shell_flush_rewrite_rules','sabri_shell_future_rewrite_version','sabri_shell_navigation_cache_epoch',
    'sabri_shell_settings_row_version','sabri_shell_settings_update_lock','sabri_shell_plan_v4_audit','sabri_shell_plan_v4_audit_anchor',
    'sabri_shell_plan_v4_audit_lock','sabri_shell_plan_v4_assurance_queue','sabri_shell_plan_v4_assurance_lock',
    'sabri_shell_plan_v4_job_state','sabri_shell_plan_v4_job_lock','sabri_shell_plan_v4_snapshots','sabri_shell_plan_v4_recovery_lock',
    'sabri_shell_plan_v4_contract_health','sabri_shell_future_v5','sabri_shell_future_lkg','sabri_shell_future_lkg_restore_lock',
    'sabri_shell_future_circuits','sabri_shell_future_circuit_lock','sabri_shell_future_critical_failures','sabri_shell_emergency_state',
    'sabri_shell_shortcode_cache_keys','sabri_shell_four_plan_migration','sabri_shell_file01_reconciliation_receipts',
);
foreach ( $file20_options as $option_name ) { delete_option( $option_name ); }
foreach ( array( 'sabri_shell_navigation_cache_v1','sabri_shell_integration_cache','sabri_shell_plan_v4_contract_health' ) as $transient_name ) { delete_transient( $transient_name ); }
if ( function_exists( 'delete_metadata' ) ) { delete_metadata( 'user', 0, 'sabri_shell_welcome_dismissed_at', '', true ); }
