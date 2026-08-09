<?php
/** Bounded scheduled maintenance, reconciliation and assurance retry. */
namespace Sabri\UnifiedShell;
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class PlanV4Jobs {
    const HOOK = 'sabri_shell_plan_v4_maintenance';
    const STATE = 'sabri_shell_plan_v4_job_state';
    const LOCK = 'sabri_shell_plan_v4_job_lock';

    public static function register() {
        add_action( 'init', array( __CLASS__, 'ensure_schedule' ) );
        add_action( self::HOOK, array( __CLASS__, 'run' ) );
        if ( defined( 'SABRI_SHELL_FILE' ) ) {
            register_deactivation_hook( SABRI_SHELL_FILE, array( __CLASS__, 'deactivate' ) );
        }
    }

    public static function ensure_schedule() {
        if ( ! wp_next_scheduled( self::HOOK ) ) {
            wp_schedule_event( time() + 300, 'hourly', self::HOOK );
        }
    }

    public static function deactivate() {
        $timestamp = wp_next_scheduled( self::HOOK );
        while ( $timestamp ) {
            wp_unschedule_event( $timestamp, self::HOOK );
            $timestamp = wp_next_scheduled( self::HOOK );
        }
    }

    public static function run() {
        $token = self::lock();
        if ( false === $token ) {
            return;
        }
        $started = microtime( true );
        $state = array( 'started_at' => gmdate( 'c' ), 'status' => 'running', 'steps' => array() );
        update_option( self::STATE, $state, false );
        try {
            PlanV4ContractHealth::invalidate();
            $health = PlanV4ContractHealth::health( array(), true );
            $state['steps']['contract_health'] = count( $health );
            $prune = PlanV4Audit::prune();
            $state['steps']['audit_prune'] = is_wp_error( $prune ) ? 'deferred-lock' : ( $prune ? 'complete' : 'failed' );
            $state['steps']['audit_chain'] = PlanV4Audit::verify_chain() ? 'valid' : 'invalid';
            if ( 'invalid' === $state['steps']['audit_chain'] ) {
                PlanV4Assurance::emit( 'audit_chain_invalid', 'high', array() );
            }
            if ( 'failed' === $state['steps']['audit_prune'] ) {
                PlanV4Assurance::emit( 'audit_prune_failed', 'medium', array() );
            }
            $state['steps']['assurance_sent'] = PlanV4Assurance::flush_queue();
            do_action( 'sabri_shell_bounded_reconciliation', 100 );
            $state['status'] = 'invalid' === $state['steps']['audit_chain'] || 'failed' === $state['steps']['audit_prune'] ? 'degraded' : 'success';
        } catch ( \Throwable $exception ) {
            $state['status'] = 'failed';
            $state['error_code'] = 'maintenance_exception';
            PlanV4Assurance::emit( 'maintenance_failure', 'high', array( 'exception_class' => get_class( $exception ) ) );
        } finally {
            $state['finished_at'] = gmdate( 'c' );
            $state['duration_ms'] = (int) round( ( microtime( true ) - $started ) * 1000 );
            update_option( self::STATE, $state, false );
            self::unlock( $token );
        }
    }

    private static function lock() {
        $token = function_exists( 'wp_generate_uuid4' ) ? wp_generate_uuid4() : uniqid( 'f20-job-', true );
        $record = array( 'token' => $token, 'expires' => time() + 900 );
        if ( add_option( self::LOCK, $record, '', 'no' ) ) {
            return $token;
        }
        $current = get_option( self::LOCK, array() );
        if ( is_array( $current ) && isset( $current['expires'] ) && absint( $current['expires'] ) < time() ) {
            delete_option( self::LOCK );
            return add_option( self::LOCK, $record, '', 'no' ) ? $token : false;
        }
        return false;
    }

    private static function unlock( $token ) {
        $current = get_option( self::LOCK, array() );
        if ( is_array( $current ) && isset( $current['token'] ) && hash_equals( (string) $current['token'], (string) $token ) ) {
            delete_option( self::LOCK );
        }
    }
}

PlanV4Jobs::register();
