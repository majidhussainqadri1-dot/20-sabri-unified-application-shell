<?php
/**
 * Structured File 20 System Check reporting.
 *
 * @package SabriUnifiedApplicationShell
 */
namespace Sabri\UnifiedShell;
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class SystemCheck {
    const MAX_ROWS = 250;

    /** Build bounded operator-facing health evidence. */
    public static function report() {
        $settings = Settings::get();
        $integrations = Integrations::detect();
        $nav = Navigation::resolved();
        $now = gmdate( 'c' );
        $theme = wp_get_theme();
        $missing = array_diff( array_keys( Defaults::destinations() ), array_keys( $nav ) );
        $activation_snapshot = get_option( Defaults::SNAPSHOT_OPTION_NAME, false );
        $activation_snapshot_valid = is_array( $activation_snapshot ) && Snapshot::verify( $activation_snapshot );
        $native_slots_registered = class_exists( __NAMESPACE__ . '\\NativeContentSlots', false )
            && has_filter( 'the_content', array( NativeContentSlots::class, 'mount_main_slots' ) )
            && has_action( 'wp_body_open', array( NativeContentSlots::class, 'begin_shell_capture' ) )
            && has_action( 'wp_body_open', array( NativeContentSlots::class, 'end_shell_capture' ) );
        $duplicate = self::duplicate_shell_plugins();
        $provider_health = class_exists( __NAMESPACE__ . '\\PlanV4ContractHealth', false ) ? PlanV4ContractHealth::health() : array();
        $critical_health = self::critical_provider_state( $provider_health );

        $rows = array(
            self::row( 'plugin-version', __( 'Plugin', 'sabri-unified-application-shell' ), 'Sabri Unified Application Shell ' . SABRI_SHELL_VERSION, 'pass', 'info', 'Runtime constant/header identity.', $now, 'file-20-runtime', __( 'Keep plugin header, readme and release package identity synchronized.', 'sabri-unified-application-shell' ) ),
            self::row( 'wordpress-version', __( 'WordPress version', 'sabri-unified-application-shell' ), get_bloginfo( 'version' ), 'info', 'info', 'WordPress runtime version.', $now, 'platform-runtime', __( 'Verify the deployed WordPress version against the release support matrix.', 'sabri-unified-application-shell' ) ),
            self::row( 'php-version', __( 'PHP version', 'sabri-unified-application-shell' ), PHP_VERSION, version_compare( PHP_VERSION, '7.4', '>=' ) ? 'pass' : 'fail', version_compare( PHP_VERSION, '7.4', '>=' ) ? 'info' : 'critical', 'PHP_VERSION runtime constant.', $now, 'platform-runtime', __( 'Use a supported PHP version and rerun the full regression suite.', 'sabri-unified-application-shell' ) ),
            self::row( 'active-theme', __( 'Active theme', 'sabri-unified-application-shell' ), $theme->get( 'Name' ), 'info', 'info', 'Active WordPress theme metadata.', $now, 'theme-integration', __( 'Verify the active production theme on staging.', 'sabri-unified-application-shell' ) ),
            self::row( 'content-target-safety', __( 'Content target safety', 'sabri-unified-application-shell' ), __( 'Theme content is annotated in place; root wrappers are not reparented or renamed.', 'sabri-unified-application-shell' ), 'pass', 'high', 'File 20 no-DOM-reparenting contract.', $now, 'theme-content', __( 'Retest after any theme/template change.', 'sabri-unified-application-shell' ) ),
            self::row( 'content-target-resolver', __( 'Content target resolver', 'sabri-unified-application-shell' ), Layout::content_target_report( $settings ), 'info', 'medium', 'Current content-target candidate report.', $now, 'theme-content', __( 'Inspect unresolved/degraded targets on staging.', 'sabri-unified-application-shell' ) ),
            self::row( 'home-feed-ownership', __( 'Home feed ownership', 'sabri-unified-application-shell' ), __( 'File 21 remains the canonical Home/News publisher; File 20 exposes native slots only.', 'sabri-unified-application-shell' ), $native_slots_registered ? 'pass' : 'fail', $native_slots_registered ? 'info' : 'critical', 'NativeContentSlots registration plus retired local File 20 HomeFeed producer.', $now, 'home-news', __( 'Restore the exact File 21 slots; do not create a local feed backend.', 'sabri-unified-application-shell' ) ),
            self::row( 'native-home-news-slots', __( 'File 21 native Home/News slots', 'sabri-unified-application-shell' ), $native_slots_registered ? __( 'Five-slot publisher registered', 'sabri-unified-application-shell' ) : __( 'Native slot publisher missing', 'sabri-unified-application-shell' ), $native_slots_registered ? 'pass' : 'fail', $native_slots_registered ? 'info' : 'critical', 'the_content + wp_body_open File 21 slot publisher registrations.', $now, 'home-news-integration', __( 'Restore the exact five shell hooks and rerun native mount tests.', 'sabri-unified-application-shell' ) ),
            self::row( 'navigation-resolution', __( 'Navigation resolution', 'sabri-unified-application-shell' ), sprintf( __( '%1$d resolved; %2$d unresolved', 'sabri-unified-application-shell' ), count( $nav ), count( $missing ) ), empty( $missing ) ? 'pass' : 'warn', empty( $missing ) ? 'info' : 'medium', 'Resolved canonical destinations after collision/access/owner validation.', $now, 'global-navigation', __( 'Repair or hide unresolved destinations; never fabricate a dead fallback.', 'sabri-unified-application-shell' ) ),
            self::row( 'authentication-contract', __( 'Authentication contract', 'sabri-unified-application-shell' ), Integrations::page_url( 'login' ) ? __( 'Canonical platform login page resolved', 'sabri-unified-application-shell' ) : __( 'Canonical platform login page unavailable; WordPress login remains break-glass access only', 'sabri-unified-application-shell' ), Integrations::page_url( 'login' ) ? 'pass' : 'warn', Integrations::page_url( 'login' ) ? 'info' : 'high', 'File 02/File 00 login page-map evidence.', $now, 'authentication-entry', __( 'Verify the File 02 owner route; never infer platform signup from core open registration.', 'sabri-unified-application-shell' ) ),
            self::row( 'moderated-composer', __( 'Moderated composer', 'sabri-unified-application-shell' ), Integrations::create_url() ? __( 'Resolved', 'sabri-unified-application-shell' ) : __( 'Not resolved; Create remains hidden', 'sabri-unified-application-shell' ), Integrations::create_url() ? 'pass' : 'warn', Integrations::create_url() ? 'info' : 'high', 'File 22 Create route contract.', $now, 'create-control', __( 'Keep Create hidden until File 00/File 22 authorize the current principal.', 'sabri-unified-application-shell' ) ),
            self::integration_row( 'notifications', __( 'Notifications integration', 'sabri-unified-application-shell' ), ! empty( $integrations['notifications'] ), 'notification-entry', 'File 19 one-bell provider evidence.', $now ),
            self::integration_row( 'network', __( 'Network integration', 'sabri-unified-application-shell' ), ! empty( $integrations['network'] ), 'network-entry', 'File 17 Network provider evidence.', $now ),
            self::integration_row( 'messages', __( 'Messages integration', 'sabri-unified-application-shell' ), ! empty( $integrations['messages'] ), 'messages-entry', 'File 17 Messages provider evidence; final hardening requires dedicated Messages evidence.', $now ),
            self::integration_row( 'marketplace', __( 'Marketplace integration', 'sabri-unified-application-shell' ), ! empty( $integrations['marketplace'] ), 'marketplace-entry', 'File 18 provider evidence.', $now ),
            self::integration_row( 'appointments', __( 'Appointment integration', 'sabri-unified-application-shell' ), ! empty( $integrations['appointments'] ), 'appointments-entry', 'File 08 provider evidence.', $now ),
            self::row( 'doctor-verification-authority', __( 'Doctor verification authority', 'sabri-unified-application-shell' ), __( 'File 00/File 09 assertions only; WordPress role labels are not authority or health evidence.', 'sabri-unified-application-shell' ), 'info', 'high', 'Canonical identity/doctor-verification ownership boundary.', $now, 'doctor-discovery', __( 'Verify File 00/File 09 provider contracts on staging.', 'sabri-unified-application-shell' ) ),
            self::row( 'critical-provider-health', __( 'Critical provider health', 'sabri-unified-application-shell' ), $critical_health['label'], $critical_health['status'], $critical_health['severity'], 'Current PlanV4ContractHealth aggregate for File 20 and required critical authority.', $now, 'cross-file-contracts', __( 'Unknown/unavailable/incompatible critical authority must not be described as healthy.', 'sabri-unified-application-shell' ) ),
            self::row( 'current-layout', __( 'Current layout', 'sabri-unified-application-shell' ), Layout::current_mode(), 'info', 'info', 'Current four-mode File 20 layout resolver.', $now, 'application-shell', __( 'Verify the resolved mode against the route/context matrix.', 'sabri-unified-application-shell' ) ),
            self::row( 'request-exclusions', __( 'Request exclusions', 'sabri-unified-application-shell' ), __( 'Admin, REST, AJAX, cron, feed, preview, print, auth/verification and recovery task requests are excluded by code.', 'sabri-unified-application-shell' ), 'info', 'high', 'Static exclusion contract plus site-relative classifiers.', $now, 'nonvisual-requests', __( 'Adversarially test root and WordPress-subdirectory installs.', 'sabri-unified-application-shell' ) ),
            self::row( 'settings-schema', __( 'Settings schema', 'sabri-unified-application-shell' ), isset( $settings['schema_version'] ) ? (string) $settings['schema_version'] : 'missing', isset( $settings['schema_version'] ) && (int) $settings['schema_version'] === Defaults::SCHEMA_VERSION ? 'pass' : 'warn', isset( $settings['schema_version'] ) && (int) $settings['schema_version'] === Defaults::SCHEMA_VERSION ? 'info' : 'high', 'Persisted settings schema versus current Defaults schema.', $now, 'file-20-settings', __( 'Run dry-run normalization before any corrective write.', 'sabri-unified-application-shell' ) ),
            self::row( 'activation-snapshot', __( 'Activation snapshot', 'sabri-unified-application-shell' ), $activation_snapshot_valid ? __( 'Captured and integrity-valid', 'sabri-unified-application-shell' ) : ( $activation_snapshot ? __( 'Present but invalid/incompatible', 'sabri-unified-application-shell' ) : __( 'Not captured yet', 'sabri-unified-application-shell' ) ), $activation_snapshot_valid ? 'pass' : ( $activation_snapshot ? 'fail' : 'warn' ), $activation_snapshot_valid ? 'info' : 'high', 'File 20 activation snapshot integrity verification.', $now, 'recovery', __( 'Create and integrity-check a File 20-owned snapshot before repair/upgrade.', 'sabri-unified-application-shell' ) ),
            self::row( 'emergency-disable', __( 'Emergency disable', 'sabri-unified-application-shell' ), ! empty( $settings['emergency_disabled'] ) ? __( 'Enabled', 'sabri-unified-application-shell' ) : __( 'Off', 'sabri-unified-application-shell' ), ! empty( $settings['emergency_disabled'] ) ? 'warn' : 'pass', ! empty( $settings['emergency_disabled'] ) ? 'high' : 'info', 'Current File 20 emergency flag plus separate audited metadata.', $now, 'application-shell', __( 'Use the canonical Emergency lifecycle; do not directly write the flag.', 'sabri-unified-application-shell' ) ),
            self::asset_row( 'css-asset', __( 'CSS asset', 'sabri-unified-application-shell' ), 'assets/css/shell.css', $now ),
            self::asset_row( 'javascript-asset', __( 'JavaScript asset', 'sabri-unified-application-shell' ), 'assets/js/shell.js', $now ),
            self::row( 'duplicate-shell', __( 'Duplicate shell detection', 'sabri-unified-application-shell' ), $duplicate['label'], empty( $duplicate['plugins'] ) ? 'pass' : 'fail', empty( $duplicate['plugins'] ) ? 'info' : 'critical', 'Structured active-plugin duplicate-shell scan.', $now, 'application-shell', __( 'Disable the duplicate shell and verify one canonical File 20 renderer.', 'sabri-unified-application-shell' ) ),
            self::row( 'missing-pages', __( 'Missing pages', 'sabri-unified-application-shell' ), $missing ? implode( ', ', $missing ) : __( 'None', 'sabri-unified-application-shell' ), empty( $missing ) ? 'pass' : 'warn', empty( $missing ) ? 'info' : 'medium', 'Canonical destination keys compared with resolved navigation.', $now, 'global-navigation', __( 'Bind a validated owner route or keep the destination unavailable.', 'sabri-unified-application-shell' ) ),
            self::row( 'staging-acceptance', __( 'Staging acceptance', 'sabri-unified-application-shell' ), __( 'Not established by System Check.', 'sabri-unified-application-shell' ), 'unknown', 'high', 'Repository/runtime self-report cannot prove Founder staging acceptance.', $now, 'release-lifecycle', __( 'Complete Hostinger staging, browser/accessibility, backup and rollback acceptance.', 'sabri-unified-application-shell' ) ),
        );

        foreach ( $nav as $key => $item ) {
            $rows[] = self::row( 'navigation-' . sanitize_key( $key ), sprintf( __( 'Navigation: %s', 'sabri-unified-application-shell' ), isset( $item['label'] ) ? $item['label'] : $key ), ( isset( $item['reason'] ) ? $item['reason'] : 'resolved' ) . ' - ' . ( isset( $item['url'] ) ? $item['url'] : '' ), 'pass', 'info', 'Validated current navigation resolution.', $now, 'global-navigation', __( 'Revalidate after route/page/provider changes.', 'sabri-unified-application-shell' ) );
        }

        $sections = apply_filters( 'sabri_shell_system_check_sections', array() );
        if ( is_array( $sections ) ) {
            foreach ( array_slice( $sections, 0, 100, true ) as $key => $section ) {
                if ( ! is_array( $section ) ) { continue; }
                $clean = self::sanitize_evidence( $section );
                $label = isset( $clean['label'] ) ? (string) $clean['label'] : sanitize_key( (string) $key );
                $status = isset( $clean['status'] ) && in_array( $clean['status'], self::statuses(), true ) ? $clean['status'] : 'info';
                $severity = isset( $clean['severity'] ) && in_array( $clean['severity'], self::severities(), true ) ? $clean['severity'] : 'info';
                unset( $clean['label'], $clean['status'], $clean['severity'] );
                $rows[] = self::row( 'section-' . sanitize_key( (string) $key ), $label, wp_json_encode( $clean, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ), $status, $severity, 'Bounded sanitized evidence emitted by a registered File 20 contract.', $now, 'file-20-contract-health', __( 'Verify declared compatibility against the native runtime; static metadata is not staging/live proof.', 'sabri-unified-application-shell' ) );
            }
        }

        $rows = array_slice( $rows, 0, self::MAX_ROWS );
        $filtered = apply_filters( 'sabri_shell_system_check_report', $rows );
        return is_array( $filtered ) ? array_slice( $filtered, 0, self::MAX_ROWS ) : $rows;
    }

    public static function export() {
        $rows = array();
        foreach ( self::report() as $row ) { if ( is_array( $row ) ) { $rows[] = self::sanitize_evidence( $row ); } }
        return array(
            'schema' => 'sabri-shell-system-check/2.1',
            'generated_at' => gmdate( 'c' ),
            'plugin' => SABRI_SHELL_VERSION,
            'rows' => array_slice( $rows, 0, self::MAX_ROWS ),
            'lifecycle' => array( 'staging_accepted' => false, 'live_deployed' => false, 'operational' => false ),
        );
    }

    private static function integration_row( $key, $label, $detected, $surface, $evidence, $now ) {
        return self::row( $key . '-integration', $label, $detected ? __( 'Detected', 'sabri-unified-application-shell' ) : __( 'Not detected', 'sabri-unified-application-shell' ), $detected ? 'pass' : 'warn', $detected ? 'info' : 'medium', $evidence, $now, $surface, __( 'Verify the native owner contract on staging.', 'sabri-unified-application-shell' ) );
    }

    private static function asset_row( $id, $label, $relative, $now ) {
        $exists = file_exists( SABRI_SHELL_PATH . $relative );
        return self::row( $id, $label, $exists ? __( 'Present', 'sabri-unified-application-shell' ) : __( 'Missing', 'sabri-unified-application-shell' ), $exists ? 'pass' : 'fail', $exists ? 'info' : 'critical', 'Packaged File 20 filesystem asset.', $now, 'shell-assets', __( 'Restore the exact packaged asset before release.', 'sabri-unified-application-shell' ) );
    }

    private static function critical_provider_state( $health ) {
        if ( ! is_array( $health ) || ! $health ) { return array( 'label' => 'Unknown', 'status' => 'unknown', 'severity' => 'high' ); }
        $bad = array( 'collision', 'error', 'invalid', 'unknown', 'unavailable', 'incompatible', 'stale' );
        foreach ( $health as $key => $row ) {
            if ( ! is_array( $row ) ) { continue; }
            $required = in_array( (string) $key, array( 'file20', 'file-20', 'membership', 'file00', 'file-00' ), true ) || ! empty( $row['required'] ) || ( isset( $row['criticality'] ) && in_array( $row['criticality'], array( 'hard', 'self', 'required' ), true ) );
            if ( $required && isset( $row['state'] ) && in_array( strtolower( (string) $row['state'] ), $bad, true ) ) { return array( 'label' => 'Critical provider ' . sanitize_key( (string) $key ) . ': ' . sanitize_key( (string) $row['state'] ), 'status' => 'warn', 'severity' => 'high' ); }
        }
        return array( 'label' => 'No known critical-provider failure in current evidence', 'status' => 'pass', 'severity' => 'info' );
    }

    private static function duplicate_shell_plugins() {
        $active = (array) get_option( 'active_plugins', array() );
        $matches = array();
        $unified = array();
        foreach ( $active as $plugin ) {
            $plugin = (string) $plugin;
            if ( false !== strpos( $plugin, 'sabri-global-ui' ) || false !== strpos( $plugin, 'unified-application-shell' ) ) { $matches[] = $plugin; }
            if ( false !== strpos( $plugin, 'sabri-unified-application-shell' ) ) { $unified[] = $plugin; }
        }
        if ( count( $unified ) <= 1 ) { $matches = array_values( array_diff( $matches, $unified ) ); }
        $matches = array_values( array_unique( $matches ) );
        return array( 'plugins' => $matches, 'label' => $matches ? implode( ', ', $matches ) : __( 'No duplicate shell plugin detected', 'sabri-unified-application-shell' ) );
    }

    private static function row( $id, $label, $value, $status, $severity, $evidence, $last_run, $affected_surface, $remediation ) {
        $status = in_array( $status, self::statuses(), true ) ? $status : 'unknown';
        $severity = in_array( $severity, self::severities(), true ) ? $severity : 'info';
        return array(
            'id' => sanitize_key( (string) $id ),
            'label' => sanitize_text_field( (string) $label ),
            'value' => sanitize_text_field( is_scalar( $value ) ? (string) $value : wp_json_encode( $value ) ),
            'status' => $status,
            'severity' => $severity,
            'evidence' => sanitize_text_field( (string) $evidence ),
            'last_run' => sanitize_text_field( (string) $last_run ),
            'affected_surface' => sanitize_key( (string) $affected_surface ),
            'remediation' => sanitize_text_field( (string) $remediation ),
        );
    }

    private static function statuses() { return array( 'pass', 'warn', 'fail', 'info', 'unknown', 'unavailable', 'incompatible', 'degraded' ); }
    private static function severities() { return array( 'critical', 'high', 'medium', 'low', 'info' ); }

    private static function sanitize_evidence( $value, $key = '', $depth = 0 ) {
        if ( $depth > 4 ) { return '[bounded]'; }
        if ( preg_match( '/pass|secret|token|cookie|authorization|nonce|credential|document|phone|email|private[_-]?key/i', (string) $key ) ) { return '[redacted]'; }
        if ( is_array( $value ) ) {
            $out = array();
            foreach ( array_slice( $value, 0, 60, true ) as $child_key => $child_value ) {
                $clean_key = sanitize_key( (string) $child_key );
                if ( '' === $clean_key ) { $clean_key = 'item'; }
                $base = $clean_key; $i = 2;
                while ( array_key_exists( $clean_key, $out ) ) { $clean_key = $base . '-' . $i; ++$i; }
                $out[ $clean_key ] = self::sanitize_evidence( $child_value, $child_key, $depth + 1 );
            }
            return $out;
        }
        if ( is_bool( $value ) || is_int( $value ) || is_float( $value ) || null === $value ) { return $value; }
        $text = sanitize_text_field( (string) $value );
        return function_exists( 'mb_substr' ) ? mb_substr( $text, 0, 500 ) : substr( $text, 0, 500 );
    }
}
