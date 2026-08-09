<?php
/** File 24 assurance bridge without transferring native enforcement. */
namespace Sabri\UnifiedShell;
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class PlanV4Assurance {
    const OPTION = 'sabri_shell_plan_v4_assurance_queue';
    const LOCK_OPTION = 'sabri_shell_plan_v4_assurance_lock';
    const MAX_EVENTS = 100;
    const LOCK_TTL = 15;

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
            self::queue_event( $event );
        }
        return $delivered;
    }

    /** Serialize assurance queue read-modify-write operations. */
    private static function acquire_lock() {
        $token = function_exists( 'wp_generate_uuid4' ) ? wp_generate_uuid4() : uniqid( 'f20-assurance-', true );
        $record = array( 'token' => $token, 'expires' => time() + self::LOCK_TTL );
        if ( add_option( self::LOCK_OPTION, $record, '', 'no' ) ) { return $token; }
        $current = get_option( self::LOCK_OPTION, array() );
        if ( is_array( $current ) && absint( $current['expires'] ?? 0 ) < time() ) {
            delete_option( self::LOCK_OPTION );
            if ( add_option( self::LOCK_OPTION, $record, '', 'no' ) ) { return $token; }
        }
        return '';
    }

    private static function release_lock( $token ) {
        $current = get_option( self::LOCK_OPTION, array() );
        if ( is_string( $token ) && '' !== $token && is_array( $current ) && isset( $current['token'] ) && hash_equals( (string) $current['token'], $token ) ) {
            delete_option( self::LOCK_OPTION );
        }
    }

    /** Queue one sanitized event without racing another request. */
    private static function queue_event( array $event ) {
        $token = self::acquire_lock();
        if ( '' === $token ) {
            PlanV4Audit::record( 'assurance_queue_lock_contended', array( 'event_type' => $event['type'] ?? 'unknown' ) );
            return false;
        }
        try {
            $queue = get_option( self::OPTION, array() );
            $queue = is_array( $queue ) ? $queue : array();
            $queue[] = $event;
            update_option( self::OPTION, array_slice( $queue, -self::MAX_EVENTS ), false );
            return true;
        } finally {
            self::release_lock( $token );
        }
    }

    public static function flush_queue() {
        if ( ! function_exists( 'sabri_security_assurance_event' ) ) { return 0; }
        $token = self::acquire_lock();
        if ( '' === $token ) { return 0; }
        try {
            $all = get_option( self::OPTION, array() );
            $all = is_array( $all ) ? array_values( $all ) : array();
            $remaining = array();
            $sent = 0;
            foreach ( array_slice( $all, 0, 25 ) as $event ) {
                try {
                    if ( sabri_security_assurance_event( $event ) ) { ++$sent; continue; }
                } catch ( \Throwable $exception ) {
                    // Native shell controls continue; evidence remains queued.
                }
                $remaining[] = $event;
            }
            $tail = array_slice( $all, 25 );
            update_option( self::OPTION, array_slice( array_merge( $remaining, $tail ), -self::MAX_EVENTS ), false );
            return $sent;
        } finally {
            self::release_lock( $token );
        }
    }

    private static function sanitize_context( array $context ) {
        $safe = array();
        foreach ( array_slice( $context, 0, 30, true ) as $original_key => $value ) {
            $key = sanitize_key( (string) $original_key );
            if ( '' === $key ) {
                $key = 'item';
            }
            $base = $key;
            $suffix = 2;
            while ( array_key_exists( $key, $safe ) ) {
                $key = $base . '-' . $suffix;
                ++$suffix;
            }
            if ( preg_match( '/secret|token|nonce|cookie|password|key|document|phone|email/i', (string) $original_key ) ) {
                $safe[ $key ] = '[redacted]';
            } elseif ( is_scalar( $value ) || null === $value ) {
                $text = sanitize_text_field( (string) $value );
                $safe[ $key ] = function_exists( 'mb_substr' ) ? mb_substr( $text, 0, 300 ) : substr( $text, 0, 300 );
            }
        }
        return $safe;
    }
}

PlanV4Assurance::register();
