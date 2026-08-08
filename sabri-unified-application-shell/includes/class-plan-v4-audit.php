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
	const OPTION        = 'sabri_shell_plan_v4_audit';
	const ANCHOR_OPTION = 'sabri_shell_plan_v4_audit_anchor';
	const LOCK_OPTION   = 'sabri_shell_plan_v4_audit_lock';
	const MAX_EVENTS    = 500;
	const LOCK_TTL      = 30;

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
			$anchor = self::anchor_for_events( $events );
			if ( ! self::verify_events( $events, $anchor ) ) {
				return new \WP_Error( 'sabri_shell_audit_chain_invalid', __( 'Operational evidence failed its integrity check; no new event was appended.', 'sabri-unified-application-shell' ) );
			}

			$previous_hash = $events ? (string) $events[ count( $events ) - 1 ]['hash'] : $anchor;
			$payload = array(
				'event_id'       => function_exists( 'wp_generate_uuid4' ) ? wp_generate_uuid4() : uniqid( 'f20-', true ),
				'type'           => $type,
				'occurred_at'    => gmdate( 'c' ),
				'actor_id'       => function_exists( 'get_current_user_id' ) ? absint( get_current_user_id() ) : 0,
				'correlation_id' => self::correlation_id(),
				'context'        => self::redact( $context ),
				'previous_hash'  => $previous_hash,
			);
			$payload['hash'] = hash( 'sha256', $previous_hash . '|' . wp_json_encode( $payload ) );
			$events[] = $payload;

			if ( count( $events ) > self::MAX_EVENTS ) {
				$drop = count( $events ) - self::MAX_EVENTS;
				$new_anchor = isset( $events[ $drop - 1 ]['hash'] ) ? (string) $events[ $drop - 1 ]['hash'] : '';
				if ( ! self::valid_hash( $new_anchor ) ) {
					return new \WP_Error( 'sabri_shell_audit_anchor_invalid', __( 'Operational evidence could not advance its retention anchor safely.', 'sabri-unified-application-shell' ) );
				}
				update_option( self::ANCHOR_OPTION, $new_anchor, false );
				$events = array_slice( $events, $drop );
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
		$events = array_values( $events );
		$anchor = self::anchor_for_events( $events );
		return self::verify_events( $events, $anchor );
	}

	/** Prune under the same owner-token lock and advance the retained-chain anchor. */
	public static function prune() {
		$token = self::acquire_lock();
		if ( is_wp_error( $token ) ) {
			return $token;
		}
		try {
			$events = get_option( self::OPTION, array() );
			if ( ! is_array( $events ) ) {
				return false;
			}
			$events = array_values( $events );
			$anchor = self::anchor_for_events( $events );
			if ( ! self::verify_events( $events, $anchor ) ) {
				return false;
			}
			if ( count( $events ) <= self::MAX_EVENTS ) {
				return true;
			}
			$drop = count( $events ) - self::MAX_EVENTS;
			$new_anchor = (string) $events[ $drop - 1 ]['hash'];
			if ( ! self::valid_hash( $new_anchor ) ) {
				return false;
			}
			update_option( self::ANCHOR_OPTION, $new_anchor, false );
			update_option( self::OPTION, array_slice( $events, $drop ), false );
			return true;
		} finally {
			self::release_lock( $token );
		}
	}

	public static function register_exporter( $exporters ) {
		$exporters['sabri-shell-operational-evidence'] = array(
			'exporter_friendly_name' => __( 'Sabri Shell operational evidence', 'sabri-unified-application-shell' ),
			'callback'               => array( __CLASS__, 'export_personal_data' ),
		);
		return $exporters;
	}

	public static function export_personal_data( $email_address, $page = 1 ) {
		unset( $page );
		$user = get_user_by( 'email', $email_address );
		if ( ! $user ) {
			return array( 'data' => array(), 'done' => true );
		}
		$data = array();
		foreach ( (array) get_option( self::OPTION, array() ) as $event ) {
			if ( ! is_array( $event ) || absint( isset( $event['actor_id'] ) ? $event['actor_id'] : 0 ) !== absint( $user->ID ) ) {
				continue;
			}
			$data[] = array(
				'group_id'    => 'sabri-shell-operational-evidence',
				'group_label' => __( 'Sabri Shell operational evidence', 'sabri-unified-application-shell' ),
				'item_id'     => sanitize_key( (string) $event['event_id'] ),
				'data'        => array(
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
			'callback'             => array( __CLASS__, 'erase_personal_data' ),
		);
		return $erasers;
	}

	/**
	 * Erase direct subject linkage while preserving a verifiable retained chain.
	 */
	public static function erase_personal_data( $email_address, $page = 1 ) {
		unset( $page );
		$user = get_user_by( 'email', $email_address );
		if ( ! $user ) {
			return array( 'items_removed' => false, 'items_retained' => false, 'messages' => array(), 'done' => true );
		}

		$token = self::acquire_lock();
		if ( is_wp_error( $token ) ) {
			return array( 'items_removed' => false, 'items_retained' => true, 'messages' => array( __( 'Operational evidence is temporarily locked; retry erasure.', 'sabri-unified-application-shell' ) ), 'done' => false );
		}
		try {
			$events = get_option( self::OPTION, array() );
			$events = is_array( $events ) ? array_values( $events ) : array();
			$anchor = self::anchor_for_events( $events );
			if ( ! self::verify_events( $events, $anchor ) ) {
				return array( 'items_removed' => false, 'items_retained' => true, 'messages' => array( __( 'Operational evidence integrity failed; erasure was not applied.', 'sabri-unified-application-shell' ) ), 'done' => false );
			}

			$changed = false;
			foreach ( $events as &$event ) {
				if ( absint( isset( $event['actor_id'] ) ? $event['actor_id'] : 0 ) === absint( $user->ID ) ) {
					$event['actor_id'] = 0;
					$event['context']  = array( 'privacy_erased' => true );
					$changed = true;
				}
			}
			unset( $event );

			if ( $changed ) {
				$events = self::rehash_events( $events, $anchor );
				if ( false === $events ) {
					return array( 'items_removed' => false, 'items_retained' => true, 'messages' => array( __( 'Operational evidence could not be rehashed safely.', 'sabri-unified-application-shell' ) ), 'done' => false );
				}
				update_option( self::OPTION, $events, false );
			}
			return array( 'items_removed' => $changed, 'items_retained' => false, 'messages' => array(), 'done' => true );
		} finally {
			self::release_lock( $token );
		}
	}

	/** Verify one bounded event list against its retained-chain anchor. */
	private static function verify_events( array $events, $anchor ) {
		if ( ! self::valid_hash( $anchor ) ) {
			return false;
		}
		$previous_hash = $anchor;
		foreach ( $events as $event ) {
			if ( ! is_array( $event ) || ! isset( $event['hash'], $event['previous_hash'] ) ) {
				return false;
			}
			$hash = (string) $event['hash'];
			if ( ! self::valid_hash( $hash ) || ! hash_equals( $previous_hash, (string) $event['previous_hash'] ) ) {
				return false;
			}
			$copy = $event;
			unset( $copy['hash'] );
			$expected = hash( 'sha256', $previous_hash . '|' . wp_json_encode( $copy ) );
			if ( ! hash_equals( $expected, $hash ) ) {
				return false;
			}
			$previous_hash = $hash;
		}
		return true;
	}

	/** Rebuild hashes only after an authorized privacy mutation. */
	private static function rehash_events( array $events, $anchor ) {
		if ( ! self::valid_hash( $anchor ) ) {
			return false;
		}
		$previous_hash = $anchor;
		foreach ( $events as &$event ) {
			if ( ! is_array( $event ) ) {
				unset( $event );
				return false;
			}
			$event['previous_hash'] = $previous_hash;
			unset( $event['hash'] );
			$event['hash'] = hash( 'sha256', $previous_hash . '|' . wp_json_encode( $event ) );
			$previous_hash = $event['hash'];
		}
		unset( $event );
		return $events;
	}

	/**
	 * Resolve the external anchor for the first retained event.
	 * Existing bounded logs are migrated once from their first predecessor hash.
	 */
	private static function anchor_for_events( array $events ) {
		$stored = get_option( self::ANCHOR_OPTION, '' );
		if ( self::valid_hash( $stored ) ) {
			return strtolower( (string) $stored );
		}
		$anchor = str_repeat( '0', 64 );
		if ( $events && isset( $events[0]['previous_hash'] ) && self::valid_hash( $events[0]['previous_hash'] ) ) {
			$anchor = strtolower( (string) $events[0]['previous_hash'] );
		}
		update_option( self::ANCHOR_OPTION, $anchor, false );
		return $anchor;
	}

	private static function valid_hash( $hash ) {
		return is_string( $hash ) && 1 === preg_match( '/^[a-f0-9]{64}$/i', $hash );
	}

	private static function acquire_lock() {
		$token  = function_exists( 'wp_generate_uuid4' ) ? wp_generate_uuid4() : uniqid( 'f20-lock-', true );
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
				$clean_key = sanitize_key( (string) $child_key );
				if ( '' === $clean_key ) {
					$clean_key = 'item';
				}
				$base = $clean_key;
				$i = 2;
				while ( array_key_exists( $clean_key, $clean ) ) {
					$clean_key = $base . '-' . $i;
					++$i;
				}
				$clean[ $clean_key ] = self::redact( $child_value, $child_key );
			}
			return $clean;
		}
		if ( is_bool( $value ) || is_int( $value ) || is_float( $value ) || null === $value ) {
			return $value;
		}
		$text = sanitize_text_field( (string) $value );
		return function_exists( 'mb_substr' ) ? mb_substr( $text, 0, 500 ) : substr( $text, 0, 500 );
	}
}

PlanV4Audit::register();
