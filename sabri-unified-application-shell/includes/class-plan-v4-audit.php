<?php
/**
 * Bounded, privacy-safe and tamper-evident File 20 operational evidence.
 *
 * @package SabriUnifiedApplicationShell
 */

namespace Sabri\UnifiedShell;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class PlanV4Audit {
    const OPTION = 'sabri_shell_plan_v4_audit';
    const LOCK_OPTION = 'sabri_shell_plan_v4_audit_lock';
    const MAX_EVENTS = 500;
    const LOCK_TTL = 30;

    public static function register() {
        add_filter( 'wp_privacy_personal_data_exporters', array( __CLASS__, 'register_exporter' ) );
        add_filter( 'wp_privacy_personal_data_erasers', array( __CLASS__, 'register_eraser' ) );
    }

    public static function record( $type, array $context = array() ) {
        $type = sanitize_key( (string) $type );
        if ( '' === $type ) {
            return new \WP_Error( 'sabri_shell_invalid_audit_type', __( 'The audit event type is invalid.', 'sabri-unified-application-shell' ) );
        }

        $token = self::acquire_lock();
        if ( is_wp_error( $token ) ) {
            return $token;
        }

        try {
            $events = get_option( self::OPTION, array() );
            $events = is_array( $events ) ? array_values( $events ) : array();
            $previous_hash = $events ? (string) end( $events )['hash'] : str_repeat( '0', 64 );
            $payload = array(
                'event_id' => function_exists( 'wp_generate_uuid4' ) ? wp_generate_uuid4() : uniqid( 'f20-', true ),
                'type' => $type,
                'occurred_at' => gmdate( 'c' ),
                'actor_id' => function_exists( 'get_current_user_id' ) ? absint( get_current_user_id() ) : 0,
                'correlation_id' => self::correlation_id(),
                'context' => self::redact( $context ),
                'previous_hash' => $previous_hash,
            );
            $payload['hash'] = hash( 'sha256', $previous_hash . '|' . wp_json_encode( $payload ) );
            $events[] = $payload;
            if ( count( $events ) > self::MAX_EVENTS ) {
                $events = array_slice( $events, -self::MAX_EVENTS );
            }
            update_option( self::OPTION, $events, false );
            do_action( 'sabri_shell_audit_event', $payload );
            return $payload;
        } finally {
            self::release_lock( $token );
        }
    }

    public static function verify_chain() {
        $events = get_option( self::OPTION, array() );
        if ( ! is_array( $events ) ) {
            return false;
        }
        $previous_hash = str_repeat( '0', 64 );
        foreach ( $events as $event ) {
            if ( ! is_array( $event ) || ! isset( $event['hash'], $event['previous_hash'] ) || ! hash_equals( $previous_hash, (string) $event['previous_hash'] ) ) {
                return false;
            }
            $copy = $event;
            $hash = (string) $copy['hash'];
            unset( $copy['hash'] );
            $expected = hash( 'sha256', $previous_hash . '|' . wp_json_encode( $copy ) );
            if ( ! hash_equals( $expected, $hash ) ) {
                return false;
            }
            $previous_hash = $hash;
        }
        return true;
    }

    public static function prune() {
        $events = get_option( self::OPTION, array() );
        if ( is_array( $events ) && count( $events ) > self::MAX_EVENTS ) {
            update_option( self::OPTION, array_slice( $events, -self::MAX_EVENTS ), false );
        }
    }

    public static function register_exporter( $exporters ) {
        $exporters['sabri-shell-operational-evidence'] = array(
            'exporter_friendly_name' => __( 'Sabri Shell operational evidence', 'sabri-unified-application-shell' ),
            'callback' => array( __CLASS__, 'export_personal_data' ),
        );
        return $exporters;
    }

    public static function export_personal_data( $email_address, $page = 1 ) {
        $user = get_user_by( 'email', $email_address );
        if ( ! $user ) {
            return array( 'data' => array(), 'done' => true );
        }
        $data = array();
        foreach ( (array) get_option( self::OPTION, array() ) as $event ) {
            if ( absint( isset( $event['actor_id'] ) ? $event['actor_id'] : 0 ) !== absint( $user->ID ) ) {
                continue;
            }
            $data[] = array(
                'group_id' => 'sabri-shell-operational-evidence',
                'group_label' => __( 'Sabri Shell operational evidence', 'sabri-unified-application-shell' ),
                'item_id' => sanitize_key( (string) $event['event_id'] ),
                'data' => array(
                    array( 'name' => __( 'Event', 'sabri-unified-application-shell' ), 'value' => sanitize_text_field( (string) $event['type'] ) ),
                    array( 'name' => __( 'Time', 'sabri-unified-application-shell' ), 'value' => sanitize_text_field( (string) $event['occurred_at'] ) ),
                    array( 'name' => __( 'Correlation ID', 'sabri-unified-application-shell' ), 'value' => sanitize_text_field( (string) $event['correlation_id'] ) ),
                ),
            );
        }
        return array( 'data' => $data, 'done' => true );
    }

    public static function register_eraser( $erasers ) {
        $erasers['sabri-shell-operational-evidence'] = array(
            'eraser_friendly_name' => __( 'Sabri Shell operational evidence', 'sabri-unified-application-shell' ),
            'callback' => array( __CLASS__, 'erase_personal_data' ),
        );
        return $erasers;
    }

    public static function erase_personal_data( $email_address, $page = 1 ) {
        $user = get_user_by( 'email', $email_address );
        if ( ! $user ) {
            return array( 'items_removed' => false, 'items_retained' => false, 'messages' => array(), 'done' => true );
        }
        $changed = false;
        $events = (array) get_option( self::OPTION, array() );
        foreach ( $events as &$event ) {
            if ( absint( isset( $event['actor_id'] ) ? $event['actor_id'] : 0 ) === absint( $user->ID ) ) {
                $event['actor_id'] = 0;
                $event['context'] = array( 'privacy_erased' => true );
                $changed = true;
            }
        }
        unset( $event );
        if ( $changed ) {
            update_option( self::OPTION, $events, false );
        }
        return array( 'items_removed' => $changed, 'items_retained' => false, 'messages' => array(), 'done' => true );
    }

    private static function acquire_lock() {
        $token = function_exists( 'wp_generate_uuid4' ) ? wp_generate_uuid4() : uniqid( 'f20-lock-', true );
        $record = array( 'token' => $token, 'expires' => time() + self::LOCK_TTL );
        if ( add_option( self::LOCK_OPTION, $record, '', 'no' ) ) {
            return $token;
        }
        $current = get_option( self::LOCK_OPTION, array() );
        if ( is_array( $current ) && isset( $current['expires'] ) && absint( $current['expires'] ) < time() ) {
            delete_option( self::LOCK_OPTION );
            if ( add_option( self::LOCK_OPTION, $record, '', 'no' ) ) {
                return $token;
            }
        }
        return new \WP_Error( 'sabri_shell_audit_locked', __( 'Operational evidence is temporarily locked.', 'sabri-unified-application-shell' ) );
    }

    private static function release_lock( $token ) {
        $current = get_option( self::LOCK_OPTION, array() );
        if ( is_array( $current ) && isset( $current['token'] ) && hash_equals( (string) $current['token'], (string) $token ) ) {
            delete_option( self::LOCK_OPTION );
        }
    }

    private static function correlation_id() {
        static $id = null;
        if ( null === $id ) {
            $id = function_exists( 'wp_generate_uuid4' ) ? wp_generate_uuid4() : uniqid( 'f20-request-', true );
        }
        return $id;
    }

    private static function redact( $value, $key = '' ) {
        if ( preg_match( '/pass|secret|token|cookie|authorization|nonce|key|credential|document|phone|email/i', (string) $key ) ) {
            return '[redacted]';
        }
        if ( is_array( $value ) ) {
            $clean = array();
            foreach ( array_slice( $value, 0, 100, true ) as $child_key => $child_value ) {
                $clean[ sanitize_key( (string) $child_key ) ] = self::redact( $child_value, $child_key );
            }
            return $clean;
        }
        if ( is_bool( $value ) || is_int( $value ) || is_float( $value ) || null === $value ) {
            return $value;
        }
        return mb_substr( sanitize_text_field( (string) $value ), 0, 500 );
    }
}

PlanV4Audit::register();
