<?php
/** Runtime contract provenance, compatibility and failure-state registry. */
namespace Sabri\UnifiedShell;
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class PlanV4ContractHealth {
    const CACHE_KEY = 'sabri_shell_plan_v4_contract_health';
    const CACHE_TTL = 300;

    public static function register() {
        add_filter( 'sabri_shell_provider_health', array( __CLASS__, 'health' ) );
        add_filter( 'sabri_shell_search_surface', array( __CLASS__, 'search_surface' ) );
    }

    public static function health( $existing = array(), $force = false ) {
        if ( ! $force ) {
            $cached = get_transient( self::CACHE_KEY );
            if ( is_array( $cached ) ) {
                return array_merge( (array) $existing, $cached );
            }
        }
        $providers = self::providers();
        $health = array();
        foreach ( $providers as $key => $records ) {
            $records = is_array( $records ) && isset( $records[0] ) && is_array( $records[0] ) ? $records : array( $records );
            if ( count( $records ) > 1 ) {
                $health[ $key ] = self::status( $key, 'collision', 'Multiple providers claimed one canonical contract.', $records );
                do_action( 'sabri_shell_contract_collision', array( 'contract' => $key, 'provider_count' => count( $records ) ) );
                continue;
            }
            $health[ $key ] = self::inspect( $key, reset( $records ) );
        }
        set_transient( self::CACHE_KEY, $health, self::CACHE_TTL );
        return array_merge( (array) $existing, $health );
    }

    public static function invalidate() {
        delete_transient( self::CACHE_KEY );
    }

    public static function providers() {
        $providers = array(
            'file-20-shell' => array(
                'owner' => 'file-20', 'version' => defined( 'SABRI_SHELL_VERSION' ) ? SABRI_SHELL_VERSION : '0.0.0',
                'minimum' => '1.2.0', 'probe' => array( __CLASS__, 'probe_file20' ), 'failure' => 'incompatible',
            ),
            'file-00-identity' => array(
                'owner' => 'file-00', 'version' => defined( 'SMC_VERSION' ) ? SMC_VERSION : '0.0.0',
                'minimum' => '1.1.2', 'probe' => array( __CLASS__, 'probe_file00' ), 'failure' => 'privileged_actions_closed',
            ),
            'file-22-create' => array(
                'owner' => 'file-20', 'version' => defined( 'SABRI_SHELL_CREATE_CONTRACT_VERSION' ) ? SABRI_SHELL_CREATE_CONTRACT_VERSION : '0.0.0',
                'minimum' => '1.0.1', 'probe' => array( __CLASS__, 'probe_file22' ), 'failure' => 'create_hidden',
            ),
            'file-25-visual' => array(
                'owner' => 'file-25', 'version' => '0.0.0', 'minimum' => '1.0.0',
                'probe' => array( __CLASS__, 'probe_file25' ), 'failure' => 'continuity_fallback',
            ),
            'file-01b-registry-search' => array(
                'owner' => 'file-01-b', 'version' => '0.0.0', 'minimum' => '1.0.0',
                'probe' => array( __CLASS__, 'probe_file01b' ), 'failure' => 'bounded_local_registry_only',
            ),
        );
        return apply_filters( 'sabri_shell_provider_registry', $providers );
    }

    public static function search_surface( $surface = array() ) {
        $provider = apply_filters( 'sabri_platform_search_surface_provider', null );
        if ( is_array( $provider ) && ! empty( $provider['url'] ) && ! empty( $provider['owner'] ) ) {
            return array(
                'owner' => sanitize_key( $provider['owner'] ),
                'url' => esc_url_raw( $provider['url'] ),
                'status' => 'available',
                'scope' => isset( $provider['scope'] ) ? sanitize_key( $provider['scope'] ) : 'platform',
            );
        }
        return array(
            'owner' => 'file-26',
            'url' => '',
            'status' => 'unavailable',
            'scope' => 'none',
            'file20_fallback' => false,
        );
    }

    public static function probe_file20() { return class_exists( __NAMESPACE__ . '\\Layout', false ); }
    public static function probe_file00() { return class_exists( 'SMC_Contracts' ) && is_callable( array( 'SMC_Contracts', 'assertions' ) ); }
    public static function probe_file22() { return function_exists( 'sabri_shell_create_visible_for_current_user' ) && function_exists( 'sabri_shell_create_url' ); }
    public static function probe_file25() {
        $contract = apply_filters( 'sabri_shell_file25_visual_contract', null );
        return is_array( $contract ) && isset( $contract['owner'], $contract['version'] ) && 'file-25' === sanitize_key( $contract['owner'] );
    }
    public static function probe_file01b() {
        $manifest = apply_filters( 'sabri_platform_foundation_manifest', null );
        return is_array( $manifest ) && isset( $manifest['owner'], $manifest['version'] ) && 'file-01-b' === sanitize_key( $manifest['owner'] );
    }

    private static function inspect( $key, $record ) {
        if ( ! is_array( $record ) || empty( $record['owner'] ) || empty( $record['minimum'] ) || empty( $record['probe'] ) ) {
            return self::status( $key, 'invalid', 'Provider record is incomplete.', $record );
        }
        $available = false;
        try {
            $available = is_callable( $record['probe'] ) && (bool) call_user_func( $record['probe'] );
        } catch ( \Throwable $exception ) {
            return self::status( $key, 'error', 'Provider probe threw an exception.', $record );
        }
        if ( ! $available ) {
            return self::status( $key, 'unavailable', isset( $record['failure'] ) ? $record['failure'] : 'unavailable', $record );
        }
        $version = isset( $record['version'] ) ? (string) $record['version'] : '';
        $minimum = isset( $record['minimum'] ) ? (string) $record['minimum'] : '';
        if ( ! self::valid_semver( $minimum ) ) {
            return self::status( $key, 'invalid', 'Provider minimum version is malformed.', $record );
        }
        if ( '' === $version || '0.0.0' === $version ) {
            return self::status( $key, 'unknown', 'Provider is present but did not publish usable version evidence.', $record );
        }
        if ( ! self::valid_semver( $version ) ) {
            return self::status( $key, 'invalid', 'Provider version evidence is malformed.', $record );
        }
        if ( version_compare( $version, $minimum, '<' ) ) {
            return self::status( $key, 'incompatible', 'Provider version is below the declared minimum.', $record );
        }
        return self::status( $key, 'healthy', 'Provider provenance, version and probe passed.', $record );
    }


    private static function valid_semver( $version ) {
        return is_string( $version ) && 1 === preg_match( '/^\d+\.\d+\.\d+(?:[-+][0-9A-Za-z.-]+)?$/', $version );
    }

    private static function status( $key, $state, $message, $record ) {
        return array(
            'contract' => sanitize_key( $key ),
            'state' => sanitize_key( $state ),
            'owner' => is_array( $record ) && isset( $record['owner'] ) ? sanitize_key( $record['owner'] ) : 'unknown',
            'version' => is_array( $record ) && isset( $record['version'] ) ? sanitize_text_field( (string) $record['version'] ) : 'unknown',
            'checked_at' => gmdate( 'c' ),
            'message' => sanitize_text_field( $message ),
        );
    }
}

PlanV4ContractHealth::register();
