<?php
/** Dry-run-first repair and compatible, auditable rollback for File 20-owned state. */
namespace Sabri\UnifiedShell;
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class PlanV4Recovery {
    const SNAPSHOTS = 'sabri_shell_plan_v4_snapshots';
    const LOCK = 'sabri_shell_plan_v4_recovery_lock';
    const MAX_SNAPSHOTS = 10;

    public static function register() {
        add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
        add_filter( 'sabri_shell_repair_actions', array( __CLASS__, 'repair_actions' ) );
        add_filter( 'sabri_shell_system_check_sections', array( __CLASS__, 'system_check_section' ) );
    }

    public static function repair_actions( $actions ) {
        $actions = (array) $actions;
        $actions['plan_v4_normalize_settings'] = __( 'Normalize File 20 settings', 'sabri-unified-application-shell' );
        $actions['plan_v4_rebuild_contract_health'] = __( 'Rebuild provider health', 'sabri-unified-application-shell' );
        $actions['plan_v4_reschedule_jobs'] = __( 'Restore scheduled maintenance', 'sabri-unified-application-shell' );
        $actions['plan_v4_flush_rewrites'] = __( 'Flush rewrite rules', 'sabri-unified-application-shell' );
        $actions['plan_v4_purge_cache'] = __( 'Purge shell and LiteSpeed caches', 'sabri-unified-application-shell' );
        return $actions;
    }

    public static function system_check_section( $sections ) {
        $sections = (array) $sections;
        $sections['plan_v4_completion'] = array(
            'label' => __( 'Plan v4 operational completion', 'sabri-unified-application-shell' ),
            'audit_chain' => PlanV4Audit::verify_chain() ? 'pass' : 'fail',
            'settings_row_version' => PlanV4SettingsConcurrency::current_version(),
            'scheduled' => (bool) wp_next_scheduled( PlanV4Jobs::HOOK ),
            'provider_health' => PlanV4ContractHealth::health(),
            'last_job' => get_option( PlanV4Jobs::STATE, array() ),
        );
        return $sections;
    }

    public static function register_routes() {
        register_rest_route( 'sabri-shell/v1', '/health', array(
            'methods' => \WP_REST_Server::READABLE,
            'callback' => array( __CLASS__, 'rest_health' ),
            'permission_callback' => array( __CLASS__, 'can_manage' ),
        ) );
        register_rest_route( 'sabri-shell/v1', '/repair/preview', array(
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => array( __CLASS__, 'rest_repair_preview' ),
            'permission_callback' => array( __CLASS__, 'can_manage' ),
            'args' => array( 'actions' => array( 'type' => 'array', 'required' => true ) ),
        ) );
        register_rest_route( 'sabri-shell/v1', '/repair/execute', array(
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => array( __CLASS__, 'rest_repair_execute' ),
            'permission_callback' => array( __CLASS__, 'can_manage' ),
            'args' => array(
                'actions' => array( 'type' => 'array', 'required' => true ),
                'expected_settings_version' => array( 'type' => 'integer', 'required' => true ),
            ),
        ) );
        register_rest_route( 'sabri-shell/v1', '/rollback/preview', array(
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => array( __CLASS__, 'rest_rollback_preview' ),
            'permission_callback' => array( __CLASS__, 'can_manage' ),
            'args' => array( 'snapshot_id' => array( 'type' => 'string', 'required' => true ) ),
        ) );
        register_rest_route( 'sabri-shell/v1', '/rollback/execute', array(
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => array( __CLASS__, 'rest_rollback_execute' ),
            'permission_callback' => array( __CLASS__, 'can_manage' ),
            'args' => array( 'snapshot_id' => array( 'type' => 'string', 'required' => true ) ),
        ) );
    }

    public static function can_manage() {
        return is_user_logged_in() && current_user_can( 'manage_options' );
    }

    public static function rest_health() {
        return rest_ensure_response( array(
            'schema' => 'sabri-shell-health/1.0',
            'state' => self::overall_state(),
            'checked_at' => gmdate( 'c' ),
            'audit_chain' => PlanV4Audit::verify_chain() ? 'valid' : 'invalid',
            'providers' => PlanV4ContractHealth::health(),
            'job' => get_option( PlanV4Jobs::STATE, array() ),
            'settings_row_version' => PlanV4SettingsConcurrency::current_version(),
        ) );
    }

    public static function rest_repair_preview( \WP_REST_Request $request ) {
        return rest_ensure_response( self::preview_repair( (array) $request->get_param( 'actions' ) ) );
    }

    public static function rest_repair_execute( \WP_REST_Request $request ) {
        $actions = (array) $request->get_param( 'actions' );
        $expected = absint( $request->get_param( 'expected_settings_version' ) );
        $result = self::execute_repair( $actions, $expected );
        return is_wp_error( $result ) ? $result : rest_ensure_response( $result );
    }

    public static function rest_rollback_preview( \WP_REST_Request $request ) {
        $result = self::preview_rollback( sanitize_text_field( $request->get_param( 'snapshot_id' ) ) );
        return is_wp_error( $result ) ? $result : rest_ensure_response( $result );
    }

    public static function rest_rollback_execute( \WP_REST_Request $request ) {
        $result = self::execute_rollback( sanitize_text_field( $request->get_param( 'snapshot_id' ) ) );
        return is_wp_error( $result ) ? $result : rest_ensure_response( $result );
    }

    public static function preview_repair( array $actions ) {
        $allowed = array_keys( self::repair_actions( array() ) );
        $actions = array_values( array_unique( array_intersect( array_map( 'sanitize_key', $actions ), $allowed ) ) );
        $operations = array();
        foreach ( $actions as $action ) {
            $operations[] = array( 'action' => $action, 'scope' => 'file-20-owned-state', 'destructive' => false );
        }
        return array(
            'dry_run' => true,
            'operations' => $operations,
            'settings_row_version' => PlanV4SettingsConcurrency::current_version(),
            'snapshot_required' => true,
        );
    }

    public static function execute_repair( array $actions, $expected_version ) {
        if ( absint( $expected_version ) !== PlanV4SettingsConcurrency::current_version() ) {
            return new \WP_Error( 'sabri_shell_stale_repair', __( 'Settings changed after the repair preview. Run the preview again.', 'sabri-unified-application-shell' ), array( 'status' => 409 ) );
        }
        $preview = self::preview_repair( $actions );
        if ( empty( $preview['operations'] ) ) {
            return new \WP_Error( 'sabri_shell_empty_repair', __( 'No valid repair action was selected.', 'sabri-unified-application-shell' ), array( 'status' => 400 ) );
        }
        $token = self::lock();
        if ( is_wp_error( $token ) ) {
            return $token;
        }
        $snapshot = self::create_snapshot( 'pre-repair' );
        $results = array();
        $failed = false;
        try {
            foreach ( $preview['operations'] as $operation ) {
                $action = $operation['action'];
                $ok = self::run_repair_action( $action );
                $results[] = array( 'action' => $action, 'success' => $ok );
                if ( ! $ok ) { $failed = true; }
            }
            $health = PlanV4ContractHealth::health( array(), true );
            if ( $failed ) {
                do_action( 'sabri_shell_repair_failed', array( 'snapshot_id' => $snapshot['id'] ) );
                PlanV4Audit::record( 'repair_failed', array( 'results' => $results, 'snapshot_id' => $snapshot['id'] ) );
                return new \WP_Error( 'sabri_shell_repair_partial_failure', __( 'Repair did not complete. Recovery evidence and the pre-repair snapshot were retained.', 'sabri-unified-application-shell' ), array( 'status' => 500, 'results' => $results ) );
            }
            PlanV4Audit::record( 'repair_completed', array( 'results' => $results, 'snapshot_id' => $snapshot['id'] ) );
            return array( 'success' => true, 'results' => $results, 'snapshot_id' => $snapshot['id'], 'health' => $health );
        } finally {
            self::unlock( $token );
        }
    }

    public static function preview_rollback( $snapshot_id ) {
        $snapshot = self::find_snapshot( $snapshot_id );
        if ( ! $snapshot ) {
            return new \WP_Error( 'sabri_shell_snapshot_missing', __( 'The requested snapshot was not found.', 'sabri-unified-application-shell' ), array( 'status' => 404 ) );
        }
        if ( ! self::verify_snapshot( $snapshot ) ) {
            return new \WP_Error( 'sabri_shell_snapshot_invalid', __( 'The requested snapshot failed its integrity check.', 'sabri-unified-application-shell' ), array( 'status' => 409 ) );
        }
        $current_major = (int) strtok( defined( 'SABRI_SHELL_VERSION' ) ? SABRI_SHELL_VERSION : '0', '.' );
        $target_major = (int) strtok( isset( $snapshot['plugin_version'] ) ? $snapshot['plugin_version'] : '0', '.' );
        return array(
            'dry_run' => true,
            'snapshot_id' => $snapshot['id'],
            'created_at' => $snapshot['created_at'],
            'compatible' => $current_major === $target_major,
            'scope' => array_keys( (array) $snapshot['state'] ),
            'pre_rollback_snapshot_required' => true,
        );
    }

    public static function execute_rollback( $snapshot_id ) {
        $preview = self::preview_rollback( $snapshot_id );
        if ( is_wp_error( $preview ) ) { return $preview; }
        if ( empty( $preview['compatible'] ) ) {
            return new \WP_Error( 'sabri_shell_snapshot_incompatible', __( 'This snapshot is not compatible with the current major version. Use read-only recovery and a manual migration plan.', 'sabri-unified-application-shell' ), array( 'status' => 409 ) );
        }
        $token = self::lock();
        if ( is_wp_error( $token ) ) { return $token; }
        $pre = self::create_snapshot( 'pre-rollback' );
        $target = self::find_snapshot( $snapshot_id );
        try {
            foreach ( (array) $target['state'] as $option => $value ) {
                if ( 0 !== strpos( $option, 'sabri_shell_' ) && 0 !== strpos( $option, 'sabri_unified_shell_' ) ) {
                    continue;
                }
                update_option( $option, $value, false );
            }
            PlanV4PrivacyCache::purge();
            $smoke = class_exists( __NAMESPACE__ . '\\Layout', false ) && in_array( Layout::current_mode(), Layout::modes(), true );
            if ( ! $smoke ) {
                do_action( 'sabri_shell_rollback_failed', array( 'snapshot_id' => $snapshot_id, 'pre_rollback_snapshot_id' => $pre['id'] ) );
                return new \WP_Error( 'sabri_shell_rollback_smoke_failed', __( 'Rollback state was restored, but the post-rollback smoke test failed. The shell remains in recovery-required state.', 'sabri-unified-application-shell' ), array( 'status' => 500 ) );
            }
            PlanV4Audit::record( 'rollback_completed', array( 'snapshot_id' => $snapshot_id, 'pre_rollback_snapshot_id' => $pre['id'] ) );
            return array( 'success' => true, 'snapshot_id' => $snapshot_id, 'pre_rollback_snapshot_id' => $pre['id'], 'smoke_test' => 'pass' );
        } catch ( \Throwable $exception ) {
            do_action( 'sabri_shell_rollback_failed', array( 'snapshot_id' => $snapshot_id, 'exception_class' => get_class( $exception ) ) );
            return new \WP_Error( 'sabri_shell_rollback_exception', __( 'Rollback failed. Recovery evidence was retained.', 'sabri-unified-application-shell' ), array( 'status' => 500 ) );
        } finally {
            self::unlock( $token );
        }
    }

    public static function create_snapshot( $reason ) {
        $state = array();
        foreach ( array( 'sabri_shell_settings', 'sabri_unified_shell_settings', PlanV4SettingsConcurrency::VERSION_OPTION ) as $option ) {
            $state[ $option ] = get_option( $option, null );
        }
        $snapshot = array(
            'id' => function_exists( 'wp_generate_uuid4' ) ? wp_generate_uuid4() : uniqid( 'f20-snapshot-', true ),
            'reason' => sanitize_key( $reason ),
            'created_at' => gmdate( 'c' ),
            'actor_id' => get_current_user_id(),
            'plugin_version' => defined( 'SABRI_SHELL_VERSION' ) ? SABRI_SHELL_VERSION : '0.0.0',
            'contract_fingerprint' => hash( 'sha256', wp_json_encode( PlanV4ContractHealth::providers() ) ),
            'state' => $state,
        );
        $copy = $snapshot;
        $snapshot['hash'] = hash( 'sha256', wp_json_encode( $copy ) );
        $snapshots = (array) get_option( self::SNAPSHOTS, array() );
        $snapshots[] = $snapshot;
        update_option( self::SNAPSHOTS, array_slice( $snapshots, -self::MAX_SNAPSHOTS ), false );
        PlanV4Audit::record( 'snapshot_created', array( 'snapshot_id' => $snapshot['id'], 'reason' => $snapshot['reason'] ) );
        return $snapshot;
    }

    private static function run_repair_action( $action ) {
        switch ( $action ) {
            case 'plan_v4_normalize_settings':
                if ( class_exists( __NAMESPACE__ . '\\Settings', false ) ) {
                    $settings = Settings::get();
                    return is_array( $settings );
                }
                return false;
            case 'plan_v4_rebuild_contract_health':
                PlanV4ContractHealth::invalidate();
                return is_array( PlanV4ContractHealth::health( array(), true ) );
            case 'plan_v4_reschedule_jobs':
                PlanV4Jobs::ensure_schedule();
                return (bool) wp_next_scheduled( PlanV4Jobs::HOOK );
            case 'plan_v4_flush_rewrites':
                flush_rewrite_rules( false );
                return true;
            case 'plan_v4_purge_cache':
                PlanV4PrivacyCache::purge();
                return true;
        }
        return false;
    }

    private static function overall_state() {
        if ( ! PlanV4Audit::verify_chain() ) { return 'repair_required'; }
        $health = PlanV4ContractHealth::health();
        foreach ( $health as $provider ) {
            if ( isset( $provider['state'] ) && in_array( $provider['state'], array( 'collision', 'error', 'invalid' ), true ) ) {
                return 'degraded';
            }
        }
        return 'healthy';
    }

    private static function find_snapshot( $id ) {
        foreach ( (array) get_option( self::SNAPSHOTS, array() ) as $snapshot ) {
            if ( isset( $snapshot['id'] ) && hash_equals( (string) $snapshot['id'], (string) $id ) ) { return $snapshot; }
        }
        return null;
    }

    private static function verify_snapshot( array $snapshot ) {
        if ( empty( $snapshot['hash'] ) ) { return false; }
        $hash = (string) $snapshot['hash'];
        unset( $snapshot['hash'] );
        return hash_equals( hash( 'sha256', wp_json_encode( $snapshot ) ), $hash );
    }

    private static function lock() {
        $token = function_exists( 'wp_generate_uuid4' ) ? wp_generate_uuid4() : uniqid( 'f20-recovery-', true );
        $record = array( 'token' => $token, 'expires' => time() + 300 );
        if ( add_option( self::LOCK, $record, '', 'no' ) ) { return $token; }
        $current = get_option( self::LOCK, array() );
        if ( is_array( $current ) && isset( $current['expires'] ) && absint( $current['expires'] ) < time() ) {
            delete_option( self::LOCK );
            if ( add_option( self::LOCK, $record, '', 'no' ) ) { return $token; }
        }
        return new \WP_Error( 'sabri_shell_recovery_locked', __( 'Another repair or rollback operation is active.', 'sabri-unified-application-shell' ), array( 'status' => 409 ) );
    }

    private static function unlock( $token ) {
        $current = get_option( self::LOCK, array() );
        if ( is_array( $current ) && isset( $current['token'] ) && hash_equals( (string) $current['token'], (string) $token ) ) {
            delete_option( self::LOCK );
        }
    }
}

PlanV4Recovery::register();
