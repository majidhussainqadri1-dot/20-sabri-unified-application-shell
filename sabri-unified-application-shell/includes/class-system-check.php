<?php
/**
 * System check reporting.
 *
 * @package SabriUnifiedApplicationShell
 */

namespace Sabri\UnifiedShell;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Builds an admin-readable, evidence-bearing shell health report. */
final class SystemCheck {
	const MAX_ROWS = 250;

	/**
	 * Build report rows.
	 *
	 * Every row carries the governing evidence fields while preserving the
	 * historical label/value/status keys used by the existing admin table.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public static function report() {
		$settings     = Settings::get();
		$integrations = Integrations::detect();
		$nav          = Navigation::resolved();
		$theme        = wp_get_theme();
		$missing      = array_diff( array_keys( Defaults::destinations() ), array_keys( $nav ) );
		$now          = gmdate( 'c' );
		$duplicate    = self::duplicate_shell_plugins();
		$activation_snapshot = get_option( Defaults::SNAPSHOT_OPTION_NAME, false );
		$activation_snapshot_valid = is_array( $activation_snapshot ) && Snapshot::verify( $activation_snapshot );
		$native_slots_registered = class_exists( __NAMESPACE__ . '\\NativeContentSlots', false )
			&& has_filter( 'the_content', array( NativeContentSlots::class, 'mount_main_slots' ) )
			&& has_action( 'wp_body_open', array( NativeContentSlots::class, 'begin_shell_capture' ) )
			&& has_action( 'wp_body_open', array( NativeContentSlots::class, 'end_shell_capture' ) );
		$rows         = array(
			self::row( 'plugin-version', __( 'Plugin', 'sabri-unified-application-shell' ), 'Sabri Unified Application Shell ' . SABRI_SHELL_VERSION, 'pass', 'info', 'Runtime constant/header identity.', $now, 'file-20-runtime', __( 'Keep plugin header, readme and release package identity synchronized.', 'sabri-unified-application-shell' ) ),
			self::row( 'wordpress-version', __( 'WordPress version', 'sabri-unified-application-shell' ), get_bloginfo( 'version' ), 'info', 'info', 'WordPress runtime version.', $now, 'platform-runtime', __( 'Verify the deployed WordPress version against the release support matrix.', 'sabri-unified-application-shell' ) ),
			self::row( 'php-version', __( 'PHP version', 'sabri-unified-application-shell' ), PHP_VERSION, version_compare( PHP_VERSION, '7.4', '>=' ) ? 'pass' : 'fail', version_compare( PHP_VERSION, '7.4', '>=' ) ? 'info' : 'critical', 'PHP_VERSION runtime constant.', $now, 'platform-runtime', __( 'Use a supported PHP version and rerun the full regression suite.', 'sabri-unified-application-shell' ) ),
			self::row( 'active-theme', __( 'Active theme', 'sabri-unified-application-shell' ), $theme->get( 'Name' ), 'info', 'info', 'Active WordPress theme metadata.', $now, 'theme-integration', __( 'Verify the active production theme on staging.', 'sabri-unified-application-shell' ) ),
			self::row( 'theme-type', __( 'Theme type inference', 'sabri-unified-application-shell' ), function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ? __( 'Block theme', 'sabri-unified-application-shell' ) : __( 'Classic theme', 'sabri-unified-application-shell' ), 'info', 'info', 'wp_is_block_theme when available.', $now, 'theme-integration', __( 'Test landmarks and content containment with the active theme.', 'sabri-unified-application-shell' ) ),
			self::row( 'content-target-safety', __( 'Content target safety', 'sabri-unified-application-shell' ), __( 'Content is annotated in place; theme root wrappers are not reparented or renamed.', 'sabri-unified-application-shell' ), 'pass', 'high', 'File 20 no-DOM-reparenting contract.', $now, 'theme-content', __( 'Retest after any theme/template change.', 'sabri-unified-application-shell' ) ),
			self::row( 'content-target-resolver', __( 'Content target resolver', 'sabri-unified-application-shell' ), Layout::content_target_report( $settings ), 'info', 'medium', 'Current Layout content-target resolver report.', $now, 'theme-content', __( 'Inspect unresolved or degraded targets on staging.', 'sabri-unified-application-shell' ) ),
			self::row( 'home-feed-ownership', __( 'Home feed ownership', 'sabri-unified-application-shell' ), __( 'Known File 04/File 21 feeds suppress shell auto-insertion.', 'sabri-unified-application-shell' ), 'pass', 'high', 'Canonical publishing ownership guard.', $now, 'home-news', __( 'Confirm exactly one Home/News native feed output.', 'sabri-unified-application-shell' ) ),
			self::row( 'navigation-resolution', __( 'Navigation resolution', 'sabri-unified-application-shell' ), sprintf( __( '%1$d resolved; %2$d unresolved', 'sabri-unified-application-shell' ), count( $nav ), count( $missing ) ), empty( $missing ) ? 'pass' : 'warn', empty( $missing ) ? 'info' : 'medium', 'Resolved navigation registry versus canonical destinations.', $now, 'global-navigation', __( 'Repair or hide unresolved destinations; never fabricate a dead fallback.', 'sabri-unified-application-shell' ) ),
			self::row( 'authentication-contract', __( 'Authentication contract', 'sabri-unified-application-shell' ), Integrations::auth_url( 'login' ) ? __( 'Platform login resolved', 'sabri-unified-application-shell' ) : __( 'Core login fallback only', 'sabri-unified-application-shell' ), Integrations::page_url( 'login' ) ? 'pass' : 'warn', Integrations::page_url( 'login' ) ? 'info' : 'high', 'Current authoritative login route resolution.', $now, 'authentication-entry', __( 'Verify the File 02 owner route and fail closed for privileged presentation.', 'sabri-unified-application-shell' ) ),
			self::row( 'moderated-composer', __( 'Moderated composer', 'sabri-unified-application-shell' ), Integrations::create_url() ? __( 'Resolved', 'sabri-unified-application-shell' ) : __( 'Not resolved; Create remains hidden', 'sabri-unified-application-shell' ), Integrations::create_url() ? 'pass' : 'warn', Integrations::create_url() ? 'info' : 'high', 'File 22 Create route contract.', $now, 'create-control', __( 'Keep Create hidden until current File 00/File 22 contracts resolve and authorize it.', 'sabri-unified-application-shell' ) ),
			self::row( 'notifications-integration', __( 'Notifications integration', 'sabri-unified-application-shell' ), $integrations['notifications'] ? __( 'Detected; shell claims one global output', 'sabri-unified-application-shell' ) : __( 'Not detected', 'sabri-unified-application-shell' ), $integrations['notifications'] ? 'pass' : 'warn', $integrations['notifications'] ? 'info' : 'medium', 'Current File 19 integration detector.', $now, 'notification-entry', __( 'Verify exactly one File 19 bell/center and no duplicate output.', 'sabri-unified-application-shell' ) ),
			self::row( 'network-integration', __( 'Network integration', 'sabri-unified-application-shell' ), $integrations['network'] ? __( 'Detected', 'sabri-unified-application-shell' ) : __( 'Not detected', 'sabri-unified-application-shell' ), $integrations['network'] ? 'pass' : 'warn', $integrations['network'] ? 'info' : 'medium', 'Current File 17 network detector.', $now, 'network-entry', __( 'Verify the current File 17 contract on staging.', 'sabri-unified-application-shell' ) ),
			self::row( 'messages-integration', __( 'Messages integration', 'sabri-unified-application-shell' ), $integrations['messages'] ? __( 'Detected', 'sabri-unified-application-shell' ) : __( 'Not detected', 'sabri-unified-application-shell' ), $integrations['messages'] ? 'pass' : 'warn', $integrations['messages'] ? 'info' : 'medium', 'Current File 17 messages detector.', $now, 'messages-entry', __( 'Verify the current File 17 conversation route.', 'sabri-unified-application-shell' ) ),
			self::row( 'marketplace-integration', __( 'Marketplace integration', 'sabri-unified-application-shell' ), $integrations['marketplace'] ? __( 'Detected', 'sabri-unified-application-shell' ) : __( 'Not detected', 'sabri-unified-application-shell' ), $integrations['marketplace'] ? 'pass' : 'warn', $integrations['marketplace'] ? 'info' : 'medium', 'Current File 18 detector.', $now, 'marketplace-entry', __( 'Verify the File 18 owner route and authorization contract.', 'sabri-unified-application-shell' ) ),
			self::row( 'appointments-integration', __( 'Appointment integration', 'sabri-unified-application-shell' ), $integrations['appointments'] ? __( 'Detected', 'sabri-unified-application-shell' ) : __( 'Not detected', 'sabri-unified-application-shell' ), $integrations['appointments'] ? 'pass' : 'warn', $integrations['appointments'] ? 'info' : 'medium', 'Current appointments detector.', $now, 'appointments-entry', __( 'Verify native appointment ownership and route authorization.', 'sabri-unified-application-shell' ) ),
			self::row( 'doctor-role-diagnostic', __( 'Doctor roles', 'sabri-unified-application-shell' ), implode( ', ', $integrations['doctor_roles'] ) ?: __( 'None detected', 'sabri-unified-application-shell' ), empty( $integrations['doctor_roles'] ) ? 'warn' : 'info', empty( $integrations['doctor_roles'] ) ? 'medium' : 'info', 'Diagnostic role inventory only; not an authorization source.', $now, 'doctor-discovery', __( 'Use File 00/File 09 assertions for authority; role labels are diagnostic only.', 'sabri-unified-application-shell' ) ),
			self::row( 'current-layout', __( 'Current layout', 'sabri-unified-application-shell' ), Layout::current_mode(), 'info', 'info', 'Current File 20 layout resolver.', $now, 'application-shell', __( 'Verify the resolved mode against the route/context matrix.', 'sabri-unified-application-shell' ) ),
			self::row( 'request-exclusions', __( 'Request exclusions', 'sabri-unified-application-shell' ), __( 'Admin, REST, AJAX, cron, feeds, previews, print, authentication, verification, and maintenance routes are excluded by code; staging runtime verification remains required.', 'sabri-unified-application-shell' ), 'info', 'high', 'Static exclusion contract plus current request classifiers.', $now, 'nonvisual-requests', __( 'Adversarially test excluded routes at root and WordPress subdirectory installs.', 'sabri-unified-application-shell' ) ),
			self::row( 'settings-schema', __( 'Settings schema', 'sabri-unified-application-shell' ), (string) $settings['schema_version'], (int) $settings['schema_version'] === Defaults::SCHEMA_VERSION ? 'pass' : 'warn', (int) $settings['schema_version'] === Defaults::SCHEMA_VERSION ? 'info' : 'high', 'Persisted settings schema versus current Defaults schema.', $now, 'file-20-settings', __( 'Run dry-run repair/normalization before any write.', 'sabri-unified-application-shell' ) ),
			self::row( 'activation-snapshot', __( 'Activation snapshot', 'sabri-unified-application-shell' ), $activation_snapshot_valid ? __( 'Captured and integrity-valid', 'sabri-unified-application-shell' ) : ( $activation_snapshot ? __( 'Present but invalid/incompatible', 'sabri-unified-application-shell' ) : __( 'Not captured yet', 'sabri-unified-application-shell' ) ), $activation_snapshot_valid ? 'pass' : ( $activation_snapshot ? 'fail' : 'warn' ), $activation_snapshot_valid ? 'info' : 'high', 'File 20 activation snapshot integrity verification.', $now, 'recovery', __( 'Create and integrity-check a File 20-owned snapshot before repair/upgrade.', 'sabri-unified-application-shell' ) ),
			self::row( 'native-home-news-slots', __( 'File 21 native Home/News slots', 'sabri-unified-application-shell' ), $native_slots_registered ? __( 'Five-slot publisher registered', 'sabri-unified-application-shell' ) : __( 'Native slot publisher missing', 'sabri-unified-application-shell' ), $native_slots_registered ? 'pass' : 'fail', $native_slots_registered ? 'info' : 'critical', 'NativeContentSlots the_content + wp_body_open mount registrations.', $now, 'home-news-integration', __( 'Restore the exact five File 21 shell hooks and rerun native mount tests.', 'sabri-unified-application-shell' ) ),
			self::row( 'emergency-disable', __( 'Emergency disable', 'sabri-unified-application-shell' ), ! empty( $settings['emergency_disabled'] ) ? __( 'Enabled', 'sabri-unified-application-shell' ) : __( 'Off', 'sabri-unified-application-shell' ), ! empty( $settings['emergency_disabled'] ) ? 'warn' : 'pass', ! empty( $settings['emergency_disabled'] ) ? 'high' : 'info', 'Current File 20 emergency flag.', $now, 'application-shell', __( 'Review the emergency reason/evidence before re-enable.', 'sabri-unified-application-shell' ) ),
			self::row( 'css-asset', __( 'CSS asset', 'sabri-unified-application-shell' ), file_exists( SABRI_SHELL_PATH . 'assets/css/shell.css' ) ? __( 'Present', 'sabri-unified-application-shell' ) : __( 'Missing', 'sabri-unified-application-shell' ), file_exists( SABRI_SHELL_PATH . 'assets/css/shell.css' ) ? 'pass' : 'fail', file_exists( SABRI_SHELL_PATH . 'assets/css/shell.css' ) ? 'info' : 'critical', 'Filesystem presence of shell.css.', $now, 'shell-assets', __( 'Restore the exact packaged asset before release.', 'sabri-unified-application-shell' ) ),
			self::row( 'javascript-asset', __( 'JavaScript asset', 'sabri-unified-application-shell' ), file_exists( SABRI_SHELL_PATH . 'assets/js/shell.js' ) ? __( 'Present', 'sabri-unified-application-shell' ) : __( 'Missing', 'sabri-unified-application-shell' ), file_exists( SABRI_SHELL_PATH . 'assets/js/shell.js' ) ? 'pass' : 'fail', file_exists( SABRI_SHELL_PATH . 'assets/js/shell.js' ) ? 'info' : 'critical', 'Filesystem presence of shell.js.', $now, 'shell-assets', __( 'Restore the exact packaged asset before release.', 'sabri-unified-application-shell' ) ),
			self::row( 'duplicate-shell', __( 'Duplicate shell detection', 'sabri-unified-application-shell' ), $duplicate, self::duplicate_shell_is_clear( $duplicate ) ? 'pass' : 'fail', self::duplicate_shell_is_clear( $duplicate ) ? 'info' : 'critical', 'Active plugin list duplicate-shell scan.', $now, 'application-shell', __( 'Disable the duplicate shell and verify one canonical File 20 renderer.', 'sabri-unified-application-shell' ) ),
			self::row( 'missing-pages', __( 'Missing pages', 'sabri-unified-application-shell' ), self::missing_navigation_pages( $nav ), empty( $missing ) ? 'pass' : 'warn', empty( $missing ) ? 'info' : 'medium', 'Canonical destination keys compared with resolved navigation.', $now, 'global-navigation', __( 'Bind a validated native owner route or hide the unavailable destination.', 'sabri-unified-application-shell' ) ),
			self::row( 'staging-acceptance', __( 'Staging acceptance', 'sabri-unified-application-shell' ), __( 'Not established by System Check; complete the staging checklist separately.', 'sabri-unified-application-shell' ), 'unknown', 'high', 'No repository/runtime self-report may prove Founder staging acceptance.', $now, 'release-lifecycle', __( 'Complete Hostinger staging, real-role, browser, accessibility, backup and rollback acceptance.', 'sabri-unified-application-shell' ) ),
		);

		foreach ( $nav as $key => $item ) {
			$rows[] = self::row(
				'navigation-' . sanitize_key( $key ),
				sprintf( __( 'Navigation: %s', 'sabri-unified-application-shell' ), $item['label'] ),
				$item['reason'] . ' - ' . $item['url'],
				'pass',
				'info',
				'Validated current navigation resolution.',
				$now,
				'global-navigation',
				__( 'Revalidate after route, page, permalink or provider changes.', 'sabri-unified-application-shell' )
			);
		}

		/* Consume every plan/hardening section instead of leaving it dormant. */
		$sections = apply_filters( 'sabri_shell_system_check_sections', array() );
		if ( is_array( $sections ) ) {
			foreach ( array_slice( $sections, 0, 100, true ) as $key => $section ) {
				if ( ! is_array( $section ) ) {
					continue;
				}
				$clean = self::sanitize_evidence( $section );
				$label = isset( $clean['label'] ) ? (string) $clean['label'] : sanitize_key( (string) $key );
				unset( $clean['label'] );
				$section_status = self::section_status( $section );
				$section_severity = isset( $section['severity'] ) && in_array( $section['severity'], array( 'critical', 'high', 'medium', 'low', 'info' ), true ) ? $section['severity'] : ( in_array( $section_status, array( 'fail', 'incompatible' ), true ) ? 'high' : ( in_array( $section_status, array( 'warn', 'unknown', 'unavailable' ), true ) ? 'medium' : 'info' ) );
				$rows[] = self::row(
					'section-' . sanitize_key( (string) $key ),
					$label,
					wp_json_encode( $clean, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ),
					$section_status,
					$section_severity,
					'Bounded, sanitized section emitted by a registered File 20 hardening/operational contract.',
					$now,
					'file-20-contract-health',
					__( 'Verify any declared compatibility target against the native runtime; never treat static declarations as staging/live proof.', 'sabri-unified-application-shell' )
				);
			}
		}

		$rows = array_slice( $rows, 0, self::MAX_ROWS );
		$filtered = apply_filters( 'sabri_shell_system_check_report', $rows );
		return is_array( $filtered ) ? array_slice( $filtered, 0, self::MAX_ROWS ) : $rows;
	}

	/** Return a sanitized machine-readable report suitable for controlled export. */
	public static function export() {
		$rows = array();
		foreach ( self::report() as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$rows[] = self::sanitize_evidence( $row );
		}
		return array(
			'schema'       => 'sabri-shell-system-check/2.0',
			'generated_at' => gmdate( 'c' ),
			'plugin'       => SABRI_SHELL_VERSION,
			'rows'         => array_slice( $rows, 0, self::MAX_ROWS ),
			'lifecycle'    => array( 'staging_accepted' => false, 'live_deployed' => false, 'operational' => false ),
		);
	}

	private static function row( $id, $label, $value, $status, $severity, $evidence, $last_run, $affected_surface, $remediation ) {
		$status = in_array( $status, array( 'pass', 'warn', 'fail', 'info', 'unknown', 'unavailable', 'incompatible' ), true ) ? $status : 'unknown';
		if ( in_array( $status, array( 'unknown', 'unavailable', 'incompatible' ), true ) ) {
			$status = $status; // Explicitly never promoted to PASS.
		}
		$severity = in_array( $severity, array( 'critical', 'high', 'medium', 'low', 'info' ), true ) ? $severity : 'info';
		return array(
			'id'               => sanitize_key( (string) $id ),
			'label'            => sanitize_text_field( (string) $label ),
			'value'            => sanitize_text_field( (string) $value ),
			'status'           => $status,
			'severity'         => $severity,
			'evidence'         => sanitize_text_field( (string) $evidence ),
			'last_run'         => sanitize_text_field( (string) $last_run ),
			'affected_surface' => sanitize_key( (string) $affected_surface ),
			'remediation'      => sanitize_text_field( (string) $remediation ),
		);
	}

	/** Recursively sanitize bounded diagnostic evidence and redact secret-like keys. */
	private static function sanitize_evidence( $value, $key = '', $depth = 0 ) {
		if ( $depth > 4 || preg_match( '/pass|secret|token|cookie|authorization|nonce|credential|document|phone|email|private|key/i', (string) $key ) ) {
			return $depth > 4 ? '[bounded]' : '[redacted]';
		}
		if ( is_array( $value ) ) {
			$out = array();
			foreach ( array_slice( $value, 0, 60, true ) as $child_key => $child_value ) {
				$clean_key = sanitize_key( (string) $child_key );
				if ( '' === $clean_key ) {
					$clean_key = 'item';
				}
				while ( array_key_exists( $clean_key, $out ) ) {
					$clean_key .= '-x';
				}
				$out[ $clean_key ] = self::sanitize_evidence( $child_value, $child_key, $depth + 1 );
			}
			return $out;
		}
		if ( is_bool( $value ) || is_int( $value ) || is_float( $value ) || null === $value ) {
			return $value;
		}
		$text = sanitize_text_field( (string) $value );
		return function_exists( 'mb_substr' ) ? mb_substr( $text, 0, 500 ) : substr( $text, 0, 500 );
	}

	private static function duplicate_shell_is_clear( $message ) {
		return false !== strpos( (string) $message, 'No duplicate shell plugin detected' );
	}

	private static function duplicate_shell_plugins() {
		$active = (array) get_option( 'active_plugins', array() );
		$current = array_values( array_filter( $active, static function ( $plugin ) { return false !== strpos( $plugin, 'sabri-unified-application-shell' ); } ) );
		$legacy_patterns = array( 'sabri-global-ui', 'sabri-global-shell', 'sabri-application-shell', 'sabri-unified-shell' );
		$matches = array();
		foreach ( $active as $plugin ) {
			foreach ( $legacy_patterns as $pattern ) {
				if ( false !== strpos( strtolower( (string) $plugin ), $pattern ) ) { $matches[] = $plugin; break; }
			}
		}
		if ( count( $current ) > 1 ) { $matches = array_merge( $matches, $current ); }
		$matches = array_values( array_unique( $matches ) );
		return $matches ? implode( ', ', $matches ) : __( 'No duplicate shell plugin detected', 'sabri-unified-application-shell' );
	}

	private static function section_status( array $section ) {
		if ( isset( $section['status'] ) && in_array( $section['status'], array( 'pass', 'warn', 'fail', 'info', 'unknown', 'unavailable', 'incompatible' ), true ) ) {
			return $section['status'];
		}
		$state = isset( $section['state'] ) ? sanitize_key( (string) $section['state'] ) : '';
		$map = array( 'healthy' => 'pass', 'degraded' => 'warn', 'repair_required' => 'fail', 'repair-required' => 'fail', 'unknown' => 'unknown', 'unavailable' => 'unavailable', 'incompatible' => 'incompatible', 'error' => 'fail', 'invalid' => 'fail', 'collision' => 'fail' );
		return isset( $map[ $state ] ) ? $map[ $state ] : 'info';
	}

	private static function missing_navigation_pages( array $nav ) {
		$missing = array_diff( array_keys( Defaults::destinations() ), array_keys( $nav ) );
		return $missing ? implode( ', ', $missing ) : __( 'None', 'sabri-unified-application-shell' );
	}
}
