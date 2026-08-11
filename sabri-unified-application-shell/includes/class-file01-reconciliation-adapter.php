<?php
/**
 * File 20 canonical shell-route acknowledgement for File 01 legacy cutover.
 *
 * File 20 acknowledges only route/navigation handoff. It never mutates File 01
 * options/pages and never claims native content ownership. Home/News content
 * acknowledgement belongs to File 21.
 *
 * @package SabriUnifiedApplicationShell
 */
namespace Sabri\UnifiedShell;
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class File01ReconciliationAdapter {
    const CONTRACT_VERSION = '1.0.0';
    const RECEIPTS_OPTION  = 'sabri_shell_file01_reconciliation_receipts';

    private static function keys() {
        return array( 'founder', 'learn', 'encyclopedia', 'doctors', 'clinic', 'video_wall', 'reels', 'pdf_library', 'radar', 'ai', 'network', 'marketplace' );
    }

    public static function register() {
        add_filter( 'spf_owner_reconciliation_plan', array( __CLASS__, 'plan' ), 20, 2 );
        add_filter( 'spf_execute_owner_reconciliation', array( __CLASS__, 'execute' ), 20, 3 );
        add_filter( 'spf_rollback_owner_reconciliation', array( __CLASS__, 'rollback' ), 20, 3 );
    }

    public static function plan( $plan, $context ) {
        if ( is_array( $plan ) ) { return $plan; }
        $context = is_array( $context ) ? $context : array();
        $key = sanitize_key( isset( $context['legacy_key'] ) ? $context['legacy_key'] : '' );
        if ( ! in_array( $key, self::keys(), true ) ) { return $plan; }
        $destinations = Defaults::destinations();
        if ( empty( $destinations[ $key ] ) || ! is_array( $destinations[ $key ] ) ) { return $plan; }
        return array(
            'accepted'        => true,
            'owner_module'    => 'file-20',
            'command_version' => self::CONTRACT_VERSION,
            'owner_scope'     => 'shell_route_navigation_handoff',
            'legacy_key'      => $key,
            'page_id'         => absint( isset( $context['page_id'] ) ? $context['page_id'] : 0 ),
            'content_owner'   => 'native-domain-owner',
            'reversible'      => true,
        );
    }

    public static function execute( $result, $action, $plan_hash ) {
        if ( is_array( $result ) ) { return $result; }
        if ( ! is_array( $action ) || ! is_array( isset( $action['owner_plan'] ) ? $action['owner_plan'] : null ) ) { return $result; }
        $owner_plan = $action['owner_plan'];
        if ( 'file-20' !== sanitize_key( isset( $owner_plan['owner_module'] ) ? $owner_plan['owner_module'] : '' ) ) { return $result; }
        if ( self::CONTRACT_VERSION !== (string) ( isset( $owner_plan['command_version'] ) ? $owner_plan['command_version'] : '' ) ) { return $result; }
        $key = sanitize_key( isset( $action['legacy_key'] ) ? $action['legacy_key'] : '' );
        if ( ! in_array( $key, self::keys(), true ) ) { return $result; }
        $page_id = absint( isset( $action['page_id'] ) ? $action['page_id'] : 0 );
        $plan_hash = strtolower( preg_replace( '/[^a-f0-9]/i', '', (string) $plan_hash ) );
        if ( 64 !== strlen( $plan_hash ) ) { return $result; }
        $receipt_id = 'file20-' . substr( hash( 'sha256', $plan_hash . '|' . $key . '|' . $page_id . '|' . self::CONTRACT_VERSION ), 0, 40 );
        $state = array(
            'receipt_id'      => $receipt_id,
            'legacy_key'      => $key,
            'page_id'         => $page_id,
            'plan_hash'       => $plan_hash,
            'command_version' => self::CONTRACT_VERSION,
            'scope'           => 'shell_route_navigation_handoff',
        );
        $state_hash = hash( 'sha256', self::json( $state ) );
        $all = get_option( self::RECEIPTS_OPTION, array() );
        $all = is_array( $all ) ? $all : array();
        if ( isset( $all[ $receipt_id ] ) && self::json( $all[ $receipt_id ] ) !== self::json( $state ) ) { return $result; }
        $all[ $receipt_id ] = $state;
        update_option( self::RECEIPTS_OPTION, $all, false );
        $saved = get_option( self::RECEIPTS_OPTION, array() );
        if ( ! is_array( $saved ) || ! isset( $saved[ $receipt_id ] ) || self::json( $saved[ $receipt_id ] ) !== self::json( $state ) ) { return $result; }
        return array(
            'success'          => true,
            'receipt_id'       => $receipt_id,
            'owner_module'     => 'file-20',
            'command_version'  => self::CONTRACT_VERSION,
            'rollback_command' => 'rollback_file01_shell_route_ack',
            'state_hash'       => $state_hash,
        );
    }

    public static function rollback( $result, $receipt, $plan_hash ) {
        if ( is_array( $result ) && ! empty( $result['success'] ) ) { return $result; }
        if ( ! is_array( $receipt ) || 'file-20' !== sanitize_key( isset( $receipt['owner_module'] ) ? $receipt['owner_module'] : '' ) ) { return $result; }
        if ( 'rollback_file01_shell_route_ack' !== sanitize_key( isset( $receipt['rollback_command'] ) ? $receipt['rollback_command'] : '' ) ) { return $result; }
        $receipt_id = sanitize_key( isset( $receipt['receipt_id'] ) ? $receipt['receipt_id'] : '' );
        $all = get_option( self::RECEIPTS_OPTION, array() );
        $all = is_array( $all ) ? $all : array();
        if ( ! isset( $all[ $receipt_id ] ) ) { return array( 'success' => true, 'idempotent_replay' => true ); }
        $stored = $all[ $receipt_id ];
        $expected_hash = strtolower( preg_replace( '/[^a-f0-9]/i', '', (string) $plan_hash ) );
        if ( ! is_array( $stored ) || ! hash_equals( (string) $stored['plan_hash'], $expected_hash ) ) { return $result; }
        unset( $all[ $receipt_id ] );
        update_option( self::RECEIPTS_OPTION, $all, false );
        $saved = get_option( self::RECEIPTS_OPTION, array() );
        if ( is_array( $saved ) && isset( $saved[ $receipt_id ] ) ) { return $result; }
        return array( 'success' => true, 'receipt_id' => $receipt_id );
    }

    private static function json( $value ) {
        return function_exists( 'wp_json_encode' ) ? (string) wp_json_encode( $value ) : (string) json_encode( $value );
    }
}
