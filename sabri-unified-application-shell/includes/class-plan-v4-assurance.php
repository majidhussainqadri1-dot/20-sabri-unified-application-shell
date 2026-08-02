<?php
/** File 24 assurance bridge without transferring native enforcement. */
namespace Sabri\UnifiedShell;
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class PlanV4Assurance {
    const OPTION = 'sabri_shell_plan_v4_assurance_queue';
    const MAX_EVENTS = 100;

    public static function register() {
        add_action( 'sabri_shell_emergency_disabled', array( __CLASS__, 'emergency_disabled' ), 10, 1 );
        add_action( 'sabri_shell_rollback_failed', array( __CLASS__, 'rollback_failed' ), 10, 1 );
        add_action( 'sabri_shell_contract_collision', array( __CLASS__, 'contract_collision' ), 10, 1 );
        add_action( 'sabri_shell_repair_failed', array( __CLASS__, 'repair_failed' ), 10, 1 );
    }

    public static function emergency_disabled( $context = array() ) { self::emit( 'emergency_disable', 'critical', (array) $context ); }
    public static function rollback_failed( $context = array() ) { self::emit( 'rollback_failure', 'critical', (array) $context ); }
    public static function contract_collision( $context = array() ) { self::emit( 'contract_collision', 'high', (array) $context ); }
    public static function repair_failed( $context = array() ) { self::emit( 'repair_failure', 'high', (array) $context ); }

    public static function emit( $type, $severity, array $context = array() ) {
        $event = array(
            'schema' => 'sabri-shell-assurance/1.0',
            'source' => 'file-20',
            'type' => sanitize_key( $type ),
            'severity' => in_array( $severity, array( 'critical', 'high', 'medium', 'low', 'info' ), true ) ? $severity : 'info',
            'occurred_at' => gmdate( 'c' ),
            'context' => self::sanitize_context( $context ),
        );
        PlanV4Audit::record( 'assurance_' . $event['type'], $event );
        do_action( 'sabri_shell_assurance_event', $event );

        $delivered = false;
        if ( function_exists( 'sabri_security_assurance_event' ) ) {
            try {
                $delivered = (bool) sabri_security_assurance_event( $event );
            } catch ( \Throwable $exception ) {
                $delivered = false;
            }
        }
        if ( ! $delivered ) {
            $queue = (array) get_option( self::OPTION, array() );
            $queue[] = $event;
            update_option( self::OPTION, array_slice( $queue, -self::MAX_EVENTS ), false );
        }
        return $delivered;
    }

    public static function flush_queue() {
        if ( ! function_exists( 'sabri_security_assurance_event' ) ) {
            return 0;
        }
        $remaining = array();
        $sent = 0;
        foreach ( array_slice( (array) get_option( self::OPTION, array() ), 0, 25 ) as $event ) {
            try {
                if ( sabri_security_assurance_event( $event ) ) {
                    ++$sent;
                    continue;
                }
            } catch ( \Throwable $exception ) {
                // Native shell controls continue; evidence remains queued.
            }
            $remaining[] = $event;
        }
        $all = (array) get_option( self::OPTION, array() );
        $tail = array_slice( $all, 25 );
        update_option( self::OPTION, array_slice( array_merge( $remaining, $tail ), -self::MAX_EVENTS ), false );
        return $sent;
    }

    private static function sanitize_context( array $context ) {
        $safe = array();
        foreach ( array_slice( $context, 0, 30, true ) as $key => $value ) {
            $key = sanitize_key( (string) $key );
            if ( preg_match( '/secret|token|nonce|cookie|password|key|document|phone|email/i', $key ) ) {
                $safe[ $key ] = '[redacted]';
            } elseif ( is_scalar( $value ) || null === $value ) {
                $safe[ $key ] = mb_substr( sanitize_text_field( (string) $value ), 0, 300 );
            }
        }
        return $safe;
    }
}

PlanV4Assurance::register();
