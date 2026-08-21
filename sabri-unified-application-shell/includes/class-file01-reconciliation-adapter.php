<?php
/**
 * File 01-B legacy page-map reconciliation adapter.
 *
 * File 20 owns only the shell route reference. Native page/content/domain truth
 * remains with each canonical companion owner. The adapter is reversible and
 * fail-closed so File 01 can retire its legacy page map without breaking shell
 * navigation or taking ownership of companion data.
 *
 * @package SabriUnifiedApplicationShell
 */

namespace Sabri\UnifiedShell;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class File01ReconciliationAdapter {
	const COMMAND_VERSION = '1.0.0';
	const RECEIPTS_OPTION = 'sabri_shell_file01_reconciliation_receipts';
	const MAX_RECEIPTS    = 64;

	/** Register the File 01 owner-plan/execute/rollback contract. */
	public static function register() {
		add_filter( 'spf_owner_reconciliation_plan', array( __CLASS__, 'plan' ), 20, 2 );
		add_filter( 'spf_execute_owner_reconciliation', array( __CLASS__, 'execute' ), 20, 3 );
		add_filter( 'spf_rollback_owner_reconciliation', array( __CLASS__, 'rollback' ), 20, 3 );
	}

	/**
	 * Map legacy File 01 keys to File 20 shell destinations and native owners.
	 *
	 * File 20 does not claim the native domain; it only persists the route Page ID
	 * in its own navigation settings before File 01 removes spf_page_map.
	 */
	public static function route_map() {
		return array(
			'founder'      => array( 'destination' => 'founder',      'content_owner' => 'file-03' ),
			'learn'        => array( 'destination' => 'learn',        'content_owner' => 'file-05' ),
			'encyclopedia' => array( 'destination' => 'encyclopedia', 'content_owner' => 'file-06' ),
			'doctors'      => array( 'destination' => 'doctors',      'content_owner' => 'file-07' ),
			'clinic'       => array( 'destination' => 'clinic',       'content_owner' => 'file-08' ),
			'videos'       => array( 'destination' => 'video_wall',   'content_owner' => 'file-10' ),
			'reels'        => array( 'destination' => 'reels',        'content_owner' => 'file-11' ),
			'pdf'          => array( 'destination' => 'pdf_library',  'content_owner' => 'file-12' ),
			'radar'        => array( 'destination' => 'radar',        'content_owner' => 'file-15' ),
			'ai'           => array( 'destination' => 'ai',           'content_owner' => 'file-16' ),
			'network'      => array( 'destination' => 'network',      'content_owner' => 'file-17' ),
			'marketplace'  => array( 'destination' => 'marketplace',  'content_owner' => 'file-18' ),
		);
	}

	/** Return an accepted File 20 plan only for the shell-owned legacy routes. */
	public static function plan( $plan, $context ) {
		/* Respect an earlier canonical owner such as File 21 Home/News. */
		if ( null !== $plan ) {
			return $plan;
		}
		if ( ! is_array( $context ) ) {
			return null;
		}

		$legacy_key = sanitize_key( isset( $context['legacy_key'] ) ? $context['legacy_key'] : '' );
		$map        = self::route_map();
		if ( empty( $map[ $legacy_key ] ) ) {
			return null;
		}

		$targets = isset( $context['target_owners'] ) && is_array( $context['target_owners'] ) ? array_map( 'sanitize_key', $context['target_owners'] ) : array();
		if ( $targets && ! in_array( 'file-20', $targets, true ) ) {
			return null;
		}

		$page_id     = absint( isset( $context['page_id'] ) ? $context['page_id'] : 0 );
		$destination = $map[ $legacy_key ]['destination'];
		if ( ! self::valid_published_page( $page_id ) || ! isset( Defaults::destinations()[ $destination ] ) || ! Navigation::page_owner_compatible( $destination, $page_id ) ) {
			return array(
				'accepted'        => false,
				'owner_module'    => 'file-20',
				'command_version' => self::COMMAND_VERSION,
				'reason'          => 'invalid_or_conflicting_legacy_page',
			);
		}

		return array(
			'accepted'        => true,
			'owner_module'    => 'file-20',
			'command_version' => self::COMMAND_VERSION,
			'route_key'       => $destination,
			'content_owner'   => $map[ $legacy_key ]['content_owner'],
			'page_id'         => $page_id,
			'scope'           => 'shell_navigation_reference_only',
			'rollback'        => 'supported',
		);
	}

	/** Persist the legacy Page ID into File 20-owned navigation state. */
	public static function execute( $result, $action, $plan_hash ) {
		if ( null !== $result ) {
			return $result;
		}
		if ( ! is_array( $action ) || 'reconcile_legacy_mapping' !== ( isset( $action['action'] ) ? $action['action'] : '' ) ) {
			return null;
		}

		$legacy_key = sanitize_key( isset( $action['legacy_key'] ) ? $action['legacy_key'] : '' );
		$map        = self::route_map();
		if ( empty( $map[ $legacy_key ] ) ) {
			return null;
		}

		$page_id    = absint( isset( $action['page_id'] ) ? $action['page_id'] : 0 );
		$owner_plan = isset( $action['owner_plan'] ) && is_array( $action['owner_plan'] ) ? $action['owner_plan'] : array();
		$route_key  = $map[ $legacy_key ]['destination'];
		$plan_hash  = self::valid_hash( $plan_hash ) ? strtolower( (string) $plan_hash ) : '';

		if ( '' === $plan_hash || empty( $owner_plan['accepted'] ) || 'file-20' !== sanitize_key( isset( $owner_plan['owner_module'] ) ? $owner_plan['owner_module'] : '' ) || self::COMMAND_VERSION !== sanitize_text_field( isset( $owner_plan['command_version'] ) ? $owner_plan['command_version'] : '' ) || $route_key !== sanitize_key( isset( $owner_plan['route_key'] ) ? $owner_plan['route_key'] : '' ) || $page_id !== absint( isset( $owner_plan['page_id'] ) ? $owner_plan['page_id'] : 0 ) || ! self::valid_published_page( $page_id ) || ! Navigation::page_owner_compatible( $route_key, $page_id ) ) {
			return array( 'success' => false, 'code' => 'file20_reconciliation_action_invalid' );
		}

		$receipt_id = self::receipt_id( $plan_hash, $legacy_key, $page_id );
		$store      = self::receipt_store();
		if ( isset( $store[ $receipt_id ] ) && is_array( $store[ $receipt_id ] ) ) {
			$existing = $store[ $receipt_id ];
			if ( 'applied' === ( isset( $existing['status'] ) ? $existing['status'] : '' ) && self::current_page_id( $route_key ) === $page_id ) {
				return self::public_receipt( $existing );
			}
		}

		$raw = get_option( Defaults::OPTION_NAME, array() );
		$raw = is_array( $raw ) ? $raw : array();
		$navigation_container_exists = isset( $raw['navigation'] ) && is_array( $raw['navigation'] );
		if ( ! $navigation_container_exists ) {
			$raw['navigation'] = array();
		}
		$row_exists = array_key_exists( $route_key, $raw['navigation'] );
		$before_row = $row_exists && is_array( $raw['navigation'][ $route_key ] ) ? $raw['navigation'][ $route_key ] : null;
		$current_row = $row_exists && is_array( $raw['navigation'][ $route_key ] ) ? $raw['navigation'][ $route_key ] : array();
		$current_row['page_id'] = $page_id;
		$raw['navigation'][ $route_key ] = $current_row;

		update_option( Defaults::OPTION_NAME, $raw, false );
		Navigation::invalidate_cache();
		if ( self::current_page_id( $route_key ) !== $page_id ) {
			self::restore_navigation_row( $route_key, $navigation_container_exists, $row_exists, $before_row );
			return array( 'success' => false, 'code' => 'file20_reconciliation_route_persist_failed' );
		}

		$state_hash = hash( 'sha256', self::json( array(
			'plan_hash'       => $plan_hash,
			'legacy_key'      => $legacy_key,
			'route_key'       => $route_key,
			'page_id'         => $page_id,
			'command_version' => self::COMMAND_VERSION,
		) ) );
		$record = array(
			'receipt_id'                  => $receipt_id,
			'owner_module'                => 'file-20',
			'command_version'             => self::COMMAND_VERSION,
			'rollback_command'            => 'file20_restore_navigation_route',
			'state_hash'                  => $state_hash,
			'plan_hash'                   => $plan_hash,
			'legacy_key'                  => $legacy_key,
			'route_key'                   => $route_key,
			'content_owner'               => $map[ $legacy_key ]['content_owner'],
			'page_id'                     => $page_id,
			'navigation_container_existed'=> $navigation_container_exists,
			'row_existed'                 => $row_exists,
			'before_row'                  => $before_row,
			'status'                      => 'applied',
			'applied_at'                  => current_time( 'mysql', true ),
		);
		$record['record_hash'] = self::record_hash( $record );
		$store[ $receipt_id ]  = $record;
		$store = self::bounded_store( $store );
		update_option( self::RECEIPTS_OPTION, $store, false );

		$persisted = self::receipt_store();
		if ( empty( $persisted[ $receipt_id ] ) || ! self::valid_record( $persisted[ $receipt_id ] ) ) {
			self::restore_navigation_row( $route_key, $navigation_container_exists, $row_exists, $before_row );
			return array( 'success' => false, 'code' => 'file20_reconciliation_receipt_persist_failed' );
		}

		return self::public_receipt( $persisted[ $receipt_id ] );
	}

	/** Restore the exact pre-reconciliation File 20 navigation row. */
	public static function rollback( $result, $receipt, $plan_hash ) {
		if ( null !== $result ) {
			return $result;
		}
		if ( ! is_array( $receipt ) || 'file-20' !== sanitize_key( isset( $receipt['owner_module'] ) ? $receipt['owner_module'] : '' ) ) {
			return null;
		}

		$receipt_id = sanitize_text_field( isset( $receipt['receipt_id'] ) ? $receipt['receipt_id'] : '' );
		$store      = self::receipt_store();
		if ( '' === $receipt_id || empty( $store[ $receipt_id ] ) || ! self::valid_record( $store[ $receipt_id ] ) ) {
			return array( 'success' => false, 'code' => 'file20_reconciliation_receipt_missing' );
		}

		$record = $store[ $receipt_id ];
		if ( ! self::valid_hash( $plan_hash ) || ! hash_equals( $record['plan_hash'], strtolower( (string) $plan_hash ) ) || self::COMMAND_VERSION !== sanitize_text_field( isset( $receipt['command_version'] ) ? $receipt['command_version'] : '' ) || ! hash_equals( $record['state_hash'], strtolower( (string) ( isset( $receipt['state_hash'] ) ? $receipt['state_hash'] : '' ) ) ) ) {
			return array( 'success' => false, 'code' => 'file20_reconciliation_receipt_binding_invalid' );
		}
		if ( 'rolled_back' === $record['status'] ) {
			return array( 'success' => true, 'receipt_id' => $receipt_id, 'status' => 'rolled_back', 'idempotent_replay' => true );
		}
		if ( 'applied' !== $record['status'] || self::current_page_id( $record['route_key'] ) !== absint( $record['page_id'] ) ) {
			return array( 'success' => false, 'code' => 'file20_reconciliation_state_changed' );
		}

		if ( ! self::restore_navigation_row( $record['route_key'], ! empty( $record['navigation_container_existed'] ), ! empty( $record['row_existed'] ), $record['before_row'] ) ) {
			return array( 'success' => false, 'code' => 'file20_reconciliation_restore_failed' );
		}

		$record['status']         = 'rolled_back';
		$record['rolled_back_at'] = current_time( 'mysql', true );
		$record['record_hash']    = self::record_hash( $record );
		$store[ $receipt_id ]     = $record;
		update_option( self::RECEIPTS_OPTION, self::bounded_store( $store ), false );
		$persisted = self::receipt_store();
		if ( empty( $persisted[ $receipt_id ] ) || ! self::valid_record( $persisted[ $receipt_id ] ) || 'rolled_back' !== $persisted[ $receipt_id ]['status'] ) {
			return array( 'success' => false, 'code' => 'file20_reconciliation_rollback_receipt_persist_failed' );
		}

		return array( 'success' => true, 'receipt_id' => $receipt_id, 'status' => 'rolled_back' );
	}

	private static function valid_published_page( $page_id ) {
		$page_id = absint( $page_id );
		if ( ! $page_id || 'publish' !== get_post_status( $page_id ) ) {
			return false;
		}
		return ! function_exists( 'get_post_type' ) || 'page' === get_post_type( $page_id );
	}

	private static function current_page_id( $route_key ) {
		$settings = Settings::get();
		return absint( isset( $settings['navigation'][ $route_key ]['page_id'] ) ? $settings['navigation'][ $route_key ]['page_id'] : 0 );
	}

	private static function restore_navigation_row( $route_key, $container_existed, $row_existed, $before_row ) {
		$raw = get_option( Defaults::OPTION_NAME, array() );
		$raw = is_array( $raw ) ? $raw : array();
		if ( ! isset( $raw['navigation'] ) || ! is_array( $raw['navigation'] ) ) {
			$raw['navigation'] = array();
		}
		if ( $row_existed ) {
			$raw['navigation'][ $route_key ] = is_array( $before_row ) ? $before_row : array();
		} else {
			unset( $raw['navigation'][ $route_key ] );
		}
		if ( ! $container_existed && empty( $raw['navigation'] ) ) {
			unset( $raw['navigation'] );
		}
		update_option( Defaults::OPTION_NAME, $raw, false );
		Navigation::invalidate_cache();
		$verify = get_option( Defaults::OPTION_NAME, array() );
		$verify = is_array( $verify ) ? $verify : array();
		if ( $row_existed ) {
			return isset( $verify['navigation'][ $route_key ] ) && self::json( $verify['navigation'][ $route_key ] ) === self::json( is_array( $before_row ) ? $before_row : array() );
		}
		return empty( $verify['navigation'] ) || ! array_key_exists( $route_key, $verify['navigation'] );
	}

	private static function receipt_id( $plan_hash, $legacy_key, $page_id ) {
		return 'file20-' . substr( hash( 'sha256', $plan_hash . '|' . $legacy_key . '|' . absint( $page_id ) . '|' . self::COMMAND_VERSION ), 0, 40 );
	}

	private static function receipt_store() {
		$store = get_option( self::RECEIPTS_OPTION, array() );
		return is_array( $store ) ? $store : array();
	}

	private static function bounded_store( array $store ) {
		if ( count( $store ) <= self::MAX_RECEIPTS ) {
			return $store;
		}
		return array_slice( $store, -self::MAX_RECEIPTS, null, true );
	}

	private static function public_receipt( array $record ) {
		return array(
			'success'          => true,
			'receipt_id'       => $record['receipt_id'],
			'owner_module'     => 'file-20',
			'command_version'  => self::COMMAND_VERSION,
			'rollback_command' => 'file20_restore_navigation_route',
			'state_hash'       => $record['state_hash'],
		);
	}

	private static function valid_record( array $record ) {
		if ( empty( $record['receipt_id'] ) || empty( $record['route_key'] ) || empty( $record['legacy_key'] ) || empty( $record['plan_hash'] ) || empty( $record['state_hash'] ) || empty( $record['record_hash'] ) || ! self::valid_hash( $record['plan_hash'] ) || ! self::valid_hash( $record['state_hash'] ) ) {
			return false;
		}
		return hash_equals( $record['record_hash'], self::record_hash( $record ) );
	}

	private static function record_hash( array $record ) {
		unset( $record['record_hash'] );
		ksort( $record );
		return hash( 'sha256', self::json( $record ) );
	}

	private static function valid_hash( $value ) {
		return 1 === preg_match( '/^[a-f0-9]{64}$/i', (string) $value );
	}

	private static function json( $value ) {
		$json = function_exists( 'wp_json_encode' ) ? wp_json_encode( $value, JSON_UNESCAPED_SLASHES ) : json_encode( $value, JSON_UNESCAPED_SLASHES );
		return is_string( $json ) ? $json : '';
	}
}
