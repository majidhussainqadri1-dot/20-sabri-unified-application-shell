<?php
/** Dry-run-first repair and compatible, auditable rollback for File 20-owned state. */
namespace Sabri\UnifiedShell;
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class PlanV4Recovery {
    const SNAPSHOTS = 'sabri_shell_plan_v4_snapshots';
    const LOCK = 'sabri_shell_plan_v4_recovery_lock';
    const MAX_SNAPSHOTS = 10;
    const MAX_DIFF_ROWS = 100;
    const SNAPSHOT_FORMAT = 2;

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
            'snapshot_schema_required' => Defaults::SCHEMA_VERSION,
            'snapshot_format_required' => self::SNAPSHOT_FORMAT,
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
            $operations[] = array(
                'action' => $action,
                'scope' => 'file-20-owned-state',
                'destructive' => false,
                'diff' => self::repair_action_diff( $action ),
            );
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
        try {
            /* Close preview-to-execute TOCTOU before creating any snapshot or write. */
            if ( absint( $expected_version ) !== PlanV4SettingsConcurrency::current_version() ) {
                return new \WP_Error( 'sabri_shell_stale_repair_locked', __( 'Settings changed while the repair was waiting for its lock. Run the preview again.', 'sabri-unified-application-shell' ), array( 'status' => 409 ) );
            }
            $locked_preview = self::preview_repair( $actions );
            if ( empty( $locked_preview['operations'] ) || absint( $locked_preview['settings_row_version'] ) !== absint( $expected_version ) ) {
                return new \WP_Error( 'sabri_shell_stale_repair_locked', __( 'The repair plan changed before execution. Run the preview again.', 'sabri-unified-application-shell' ), array( 'status' => 409 ) );
            }

            $snapshot = self::create_snapshot( 'pre-repair' );
            $results = array();
            $failed = false;
            foreach ( $locked_preview['operations'] as $operation ) {
                $action = $operation['action'];
                $ok = self::run_repair_action( $action );
                $results[] = array( 'action' => $action, 'success' => $ok, 'preview_diff' => $operation['diff'] );
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

    /**
     * Rollback preview is fail-closed across snapshot format, code-major and settings-schema.
     * Legacy ambiguous snapshots remain read-only evidence requiring a manual migration.
     */
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
        $target_schema = self::snapshot_schema_version( $snapshot );
        $target_format = isset( $snapshot['snapshot_format'] ) ? absint( $snapshot['snapshot_format'] ) : 0;
        $version_compatible = $current_major === $target_major;
        $schema_compatible = Defaults::SCHEMA_VERSION === $target_schema;
        $format_compatible = self::SNAPSHOT_FORMAT === $target_format;
        return array(
            'dry_run' => true,
            'snapshot_id' => $snapshot['id'],
            'created_at' => $snapshot['created_at'],
            'version_compatible' => $version_compatible,
            'schema_compatible' => $schema_compatible,
            'format_compatible' => $format_compatible,
            'target_schema_version' => $target_schema,
            'current_schema_version' => Defaults::SCHEMA_VERSION,
            'target_snapshot_format' => $target_format,
            'current_snapshot_format' => self::SNAPSHOT_FORMAT,
            'compatible' => $version_compatible && $schema_compatible && $format_compatible,
            'scope' => array_keys( (array) $snapshot['state'] ),
            'pre_rollback_snapshot_required' => true,
        );
    }

    public static function execute_rollback( $snapshot_id ) {
        $preview = self::preview_rollback( $snapshot_id );
        if ( is_wp_error( $preview ) ) { return $preview; }
        if ( empty( $preview['compatible'] ) ) {
            return new \WP_Error( 'sabri_shell_snapshot_incompatible', __( 'This snapshot is not compatible with the current snapshot format/code/schema. Keep it read-only and use a manual migration plan.', 'sabri-unified-application-shell' ), array( 'status' => 409, 'preview' => $preview ) );
        }
        $token = self::lock();
        if ( is_wp_error( $token ) ) { return $token; }
        $pre = null;
        try {
            /* Preview-to-execute is revalidated under the recovery lock. */
            $locked_preview = self::preview_rollback( $snapshot_id );
            if ( is_wp_error( $locked_preview ) ) {
                return $locked_preview;
            }
            if ( empty( $locked_preview['compatible'] ) ) {
                return new \WP_Error( 'sabri_shell_snapshot_changed', __( 'The selected rollback snapshot changed or became incompatible. Preview it again.', 'sabri-unified-application-shell' ), array( 'status' => 409, 'preview' => $locked_preview ) );
            }
            $target = self::find_snapshot( $snapshot_id );
            if ( ! is_array( $target ) || ! self::verify_snapshot( $target ) ) {
                return new \WP_Error( 'sabri_shell_snapshot_changed', __( 'The selected rollback snapshot is no longer available or valid.', 'sabri-unified-application-shell' ), array( 'status' => 409 ) );
            }

            /* Hold the verified target in memory before retention can evict it. */
            $pre = self::create_snapshot( 'pre-rollback' );
            foreach ( (array) $target['state'] as $option => $entry ) {
                if ( 0 !== strpos( $option, 'sabri_shell_' ) && 0 !== strpos( $option, 'sabri_unified_shell_' ) ) {
                    continue;
                }
                if ( ! is_array( $entry ) || ! array_key_exists( 'exists', $entry ) || ! array_key_exists( 'value', $entry ) ) {
                    throw new \RuntimeException( 'ambiguous_snapshot_state' );
                }
                if ( empty( $entry['exists'] ) ) {
                    delete_option( $option );
                } else {
                    update_option( $option, $entry['value'], false );
                }
            }
            Navigation::invalidate_cache();
            Integrations::invalidate_cache();
            PlanV4PrivacyCache::purge();
            $smoke = class_exists( __NAMESPACE__ . '\\Layout', false ) && in_array( Layout::current_mode(), Layout::modes(), true );
            if ( ! $smoke ) {
                do_action( 'sabri_shell_rollback_failed', array( 'snapshot_id' => $snapshot_id, 'pre_rollback_snapshot_id' => $pre['id'] ) );
                PlanV4Audit::record( 'rollback_failed', array( 'snapshot_id' => $snapshot_id, 'pre_rollback_snapshot_id' => $pre['id'], 'reason' => 'smoke-test' ) );
                return new \WP_Error( 'sabri_shell_rollback_smoke_failed', __( 'Rollback state was restored, but the post-rollback smoke test failed. The shell remains in recovery-required state.', 'sabri-unified-application-shell' ), array( 'status' => 500 ) );
            }
            PlanV4Audit::record( 'rollback_completed', array( 'snapshot_id' => $snapshot_id, 'pre_rollback_snapshot_id' => $pre['id'], 'cache_purged' => true ) );
            return array( 'success' => true, 'snapshot_id' => $snapshot_id, 'pre_rollback_snapshot_id' => $pre['id'], 'smoke_test' => 'pass', 'cache_purged' => true );
        } catch ( \Throwable $exception ) {
            $pre_id = is_array( $pre ) && isset( $pre['id'] ) ? $pre['id'] : '';
            do_action( 'sabri_shell_rollback_failed', array( 'snapshot_id' => $snapshot_id, 'pre_rollback_snapshot_id' => $pre_id, 'exception_class' => get_class( $exception ) ) );
            PlanV4Audit::record( 'rollback_failed', array( 'snapshot_id' => $snapshot_id, 'pre_rollback_snapshot_id' => $pre_id, 'exception_class' => get_class( $exception ) ) );
            return new \WP_Error( 'sabri_shell_rollback_exception', __( 'Rollback failed. Recovery evidence was retained.', 'sabri-unified-application-shell' ), array( 'status' => 500 ) );
        } finally {
            self::unlock( $token );
        }
    }

    /** Return bounded snapshot metadata for operator selection; never expose state values. */
    public static function snapshot_list() {
        $out = array();
        foreach ( array_slice( (array) get_option( self::SNAPSHOTS, array() ), -self::MAX_SNAPSHOTS ) as $snapshot ) {
            if ( ! is_array( $snapshot ) || empty( $snapshot['id'] ) ) { continue; }
            $out[] = array(
                'id' => sanitize_text_field( (string) $snapshot['id'] ),
                'reason' => sanitize_key( isset( $snapshot['reason'] ) ? $snapshot['reason'] : '' ),
                'created_at' => sanitize_text_field( isset( $snapshot['created_at'] ) ? $snapshot['created_at'] : '' ),
                'plugin_version' => sanitize_text_field( isset( $snapshot['plugin_version'] ) ? $snapshot['plugin_version'] : '' ),
                'schema_version' => self::snapshot_schema_version( $snapshot ),
                'snapshot_format' => isset( $snapshot['snapshot_format'] ) ? absint( $snapshot['snapshot_format'] ) : 0,
                'integrity' => self::verify_snapshot( $snapshot ) ? 'valid' : 'invalid',
            );
        }
        return $out;
    }

    public static function create_snapshot( $reason ) {
        $state = array();
        foreach ( self::owned_options() as $option ) {
            $state[ $option ] = self::capture_option_state( $option );
        }
        $snapshot = array(
            'id' => function_exists( 'wp_generate_uuid4' ) ? wp_generate_uuid4() : uniqid( 'f20-snapshot-', true ),
            'reason' => sanitize_key( $reason ),
            'created_at' => gmdate( 'c' ),
            'actor_id' => get_current_user_id(),
            'source' => 'file-20-plan-v4-recovery',
            'snapshot_format' => self::SNAPSHOT_FORMAT,
            'plugin_version' => defined( 'SABRI_SHELL_VERSION' ) ? SABRI_SHELL_VERSION : '0.0.0',
            'schema_version' => Defaults::SCHEMA_VERSION,
            'contract_fingerprint' => hash( 'sha256', wp_json_encode( PlanV4ContractHealth::providers() ) ),
            'route_fingerprint' => hash( 'sha256', wp_json_encode( Navigation::resolved() ) ),
            'feature_fingerprint' => hash( 'sha256', wp_json_encode( FutureShellV5::settings() ) ),
            'state' => $state,
        );
        $copy = $snapshot;
        $snapshot['hash'] = hash( 'sha256', wp_json_encode( $copy ) );
        $snapshots = (array) get_option( self::SNAPSHOTS, array() );
        $snapshots[] = $snapshot;
        update_option( self::SNAPSHOTS, array_slice( $snapshots, -self::MAX_SNAPSHOTS ), false );
        PlanV4Audit::record( 'snapshot_created', array( 'snapshot_id' => $snapshot['id'], 'reason' => $snapshot['reason'], 'schema_version' => Defaults::SCHEMA_VERSION, 'snapshot_format' => self::SNAPSHOT_FORMAT ) );
        return $snapshot;
    }

    /** File 20-owned option allowlist captured by recovery. */
    private static function owned_options() {
        return array(
            Defaults::OPTION_NAME,
            'sabri_unified_shell_settings',
            PlanV4SettingsConcurrency::VERSION_OPTION,
            FutureShellV5::OPTION,
            SafeMode::EMERGENCY_META_OPTION,
            'sabri_shell_four_plan_migration',
            'sabri_shell_future_rewrite_version',
            'sabri_shell_flush_rewrite_rules',
        );
    }

    /** Capture both existence and value so rollback can exactly restore absence. */
    private static function capture_option_state( $option ) {
        $sentinel = new \stdClass();
        $value = get_option( $option, $sentinel );
        return array(
            'exists' => $value !== $sentinel,
            'value' => $value === $sentinel ? null : $value,
        );
    }

    /** Build a bounded, evidence-oriented dry-run diff for one repair action. */
    private static function repair_action_diff( $action ) {
        switch ( $action ) {
            case 'plan_v4_normalize_settings':
                $current = get_option( Defaults::OPTION_NAME, array() );
                $current = is_array( $current ) ? $current : array();
                $target = Settings::deep_merge( Defaults::settings(), $current );
                $target['schema_version'] = Defaults::SCHEMA_VERSION;
                return self::diff_values( $current, $target );
            case 'plan_v4_rebuild_contract_health':
                return array( array( 'path' => 'provider_health_cache', 'before' => 'current-cached-evidence', 'after' => 'invalidated-and-authoritatively-rechecked' ) );
            case 'plan_v4_reschedule_jobs':
                return array( array( 'path' => 'scheduled_maintenance', 'before' => wp_next_scheduled( PlanV4Jobs::HOOK ) ? 'scheduled' : 'missing', 'after' => 'scheduled' ) );
            case 'plan_v4_flush_rewrites':
                return array( array( 'path' => 'rewrite_rules', 'before' => 'current', 'after' => 'flushed-once' ) );
            case 'plan_v4_purge_cache':
                return array( array( 'path' => 'file20_and_litespeed_cache', 'before' => 'current', 'after' => 'purged-and-reconciled' ) );
        }
        return array();
    }

    /** Recursively describe actual settings changes without exposing secret-like values. */
    private static function diff_values( $before, $after, $prefix = '' ) {
        $rows = array();
        if ( is_array( $before ) && is_array( $after ) ) {
            $keys = array_unique( array_merge( array_keys( $before ), array_keys( $after ) ) );
            foreach ( $keys as $key ) {
                if ( count( $rows ) >= self::MAX_DIFF_ROWS ) { break; }
                $path = '' === $prefix ? (string) $key : $prefix . '.' . $key;
                $left = array_key_exists( $key, $before ) ? $before[ $key ] : null;
                $right = array_key_exists( $key, $after ) ? $after[ $key ] : null;
                if ( $left === $right ) { continue; }
                if ( is_array( $left ) && is_array( $right ) ) {
                    $rows = array_merge( $rows, self::diff_values( $left, $right, $path ) );
                    $rows = array_slice( $rows, 0, self::MAX_DIFF_ROWS );
                    continue;
                }
                $rows[] = array( 'path' => sanitize_text_field( $path ), 'before' => self::safe_diff_value( $left, $path ), 'after' => self::safe_diff_value( $right, $path ) );
            }
            return $rows;
        }
        if ( $before !== $after ) {
            $rows[] = array( 'path' => sanitize_text_field( $prefix ), 'before' => self::safe_diff_value( $before, $prefix ), 'after' => self::safe_diff_value( $after, $prefix ) );
        }
        return $rows;
    }

    private static function safe_diff_value( $value, $path ) {
        if ( preg_match( '/pass|secret|token|cookie|authorization|nonce|credential|document|phone|email|key/i', (string) $path ) ) {
            return '[redacted]';
        }
        if ( is_bool( $value ) || is_int( $value ) || is_float( $value ) || null === $value ) {
            return $value;
        }
        if ( is_array( $value ) ) {
            return '[structured-value]';
        }
        $text = sanitize_text_field( (string) $value );
        return function_exists( 'mb_substr' ) ? mb_substr( $text, 0, 200 ) : substr( $text, 0, 200 );
    }

    private static function run_repair_action( $action ) {
        switch ( $action ) {
            case 'plan_v4_normalize_settings':
                if ( ! class_exists( __NAMESPACE__ . '\\Settings', false ) ) {
                    return false;
                }
                Settings::ensure_defaults();
                $stored = get_option( Defaults::OPTION_NAME, array() );
                return is_array( $stored ) && isset( $stored['schema_version'] ) && Defaults::SCHEMA_VERSION === absint( $stored['schema_version'] );
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

    private static function snapshot_schema_version( array $snapshot ) {
        if ( isset( $snapshot['schema_version'] ) ) {
            return absint( $snapshot['schema_version'] );
        }
        return 0;
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
