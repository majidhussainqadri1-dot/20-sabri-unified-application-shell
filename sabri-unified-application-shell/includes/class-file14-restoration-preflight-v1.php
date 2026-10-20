<?php
/**
 * File20 external owner contract preflight for File14 F14-FUT-17.
 * No WordPress boot hook, SQL statement, write or experiment activation.
 * A structurally valid dry run is NOT a Founder signature or CAS restoration.
 */
namespace Sabri\UnifiedShell;
if ( ! defined( 'ABSPATH' ) ) { exit; }
final class File14RestorationPreflightV1 {
    const CONTRACT = 'file20.file14.placement-restore-preflight.v1';

    public static function validate( $request ) {
        $closed = array( 'shape_valid' => false, 'cas_write_executable' => false,
            'owner_authorized' => false, 'staging_accepted' => false,
            'reason' => 'invalid_restore_request' );
        if ( ! is_array( $request ) ) { return $closed; }
        $fields = array( 'contract','experiment','approved_by','placement','baseline',
            'current','snapshot_hash','mutation_origin' );
        $actual = array_keys( $request );
        sort( $actual, SORT_STRING );
        sort( $fields, SORT_STRING );
        if ( $actual !== $fields ) { $closed['reason'] = 'unverified_contract_keys'; return $closed; }
        if ( self::CONTRACT !== $request['contract']
            || ! is_string( $request['experiment'] )
            || ! preg_match( '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/D', $request['experiment'] )
            || ! is_int( $request['approved_by'] ) || $request['approved_by'] <= 0
            || 'file14-owned-placement' !== $request['mutation_origin'] ) {
            $closed['reason'] = 'experiment_or_approval_unverified'; return $closed;
        }
        $place = $request['placement'];
        if ( ! is_string( $place ) || ! preg_match( '/^[a-z][a-z0-9_]{0,80}$/D', $place ) ) { return $closed; }
        $before = $request['baseline'];
        $current = $request['current'];
        $names = array( 'placement_key','row_version','status','block_key','route_key','slot_key','public_id' );
        foreach ( array( $before, $current ) as $row ) {
            if ( ! is_array( $row ) ) { return $closed; }
            $keys = array_keys( $row );
            sort( $keys, SORT_STRING );
            $expected = $names;
            sort( $expected, SORT_STRING );
            if ( $keys !== $expected ) { return $closed; }
            foreach ( array( 'placement_key','status','block_key','route_key','slot_key','public_id' ) as $n ) {
                if ( ! is_string( $row[$n] ) || '' === $row[$n] ) { return $closed; }
            }
            if ( ! is_int( $row['row_version'] ) || $row['row_version'] < 1 ) { return $closed; }
            if ( $row['placement_key'] !== $place ) { $closed['reason'] = 'placement_identity_drift'; return $closed; }
        }
        if ( $before['public_id'] !== $current['public_id']
            || $before['route_key'] !== $current['route_key']
            || $before['slot_key'] !== $current['slot_key'] ) {
            $closed['reason'] = 'owner_identity_drift'; return $closed;
        }
        if ( $before['row_version'] >= PHP_INT_MAX
            || $current['row_version'] !== $before['row_version'] + 1 ) {
            $closed['reason'] = 'version_conflict'; return $closed;
        }
        if ( ! is_string( $request['snapshot_hash'] ) || ! preg_match( '/^[a-f0-9]{64}$/D', $request['snapshot_hash'] )
            || ! hash_equals( hash( 'sha256', json_encode( $before ) ), $request['snapshot_hash'] ) ) {
            $closed['reason'] = 'snapshot_integrity_unverified'; return $closed;
        }
        // Merely reading two arrays cannot prove that the experiment was the
        // last writer. A future File14 owner mutation ledger and File20/25
        // render-state CAS protocol must attest it before any SQL changes.
        return array( 'shape_valid' => true, 'cas_write_executable' => false,
            'owner_authorized' => false, 'staging_accepted' => false,
            'reason' => 'original_mutation_provenance_and_live_cas_not_attested' );
    }
}
