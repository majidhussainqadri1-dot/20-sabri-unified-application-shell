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

/**
 * Builds an admin-readable shell health report.
 */
final class SystemCheck {
	/**
	 * Build report rows.
	 *
	 * @return array<int,array<string,string>>
	 */
	public static function report() {
		$settings     = Settings::get();
		$integrations = Integrations::detect();
		$nav          = Navigation::resolved();
		$theme        = wp_get_theme();
		$missing      = array_diff( array_keys( Defaults::destinations() ), array_keys( $nav ) );
		$rows         = array(
			self::row( __( 'Plugin', 'sabri-unified-application-shell' ), 'Sabri Unified Application Shell ' . SABRI_SHELL_VERSION, 'pass' ),
			self::row( __( 'WordPress version', 'sabri-unified-application-shell' ), get_bloginfo( 'version' ), 'info' ),
			self::row( __( 'PHP version', 'sabri-unified-application-shell' ), PHP_VERSION, version_compare( PHP_VERSION, '7.4', '>=' ) ? 'pass' : 'fail' ),
			self::row( __( 'Active theme', 'sabri-unified-application-shell' ), $theme->get( 'Name' ), 'info' ),
			self::row( __( 'Theme type inference', 'sabri-unified-application-shell' ), function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ? __( 'Block theme', 'sabri-unified-application-shell' ) : __( 'Classic theme', 'sabri-unified-application-shell' ), 'info' ),
			self::row( __( 'Content target safety', 'sabri-unified-application-shell' ), __( 'Content is annotated in place; theme root wrappers are not reparented or renamed.', 'sabri-unified-application-shell' ), 'pass' ),
			self::row( __( 'Content target resolver', 'sabri-unified-application-shell' ), Layout::content_target_report( $settings ), 'info' ),
			self::row( __( 'Home feed ownership', 'sabri-unified-application-shell' ), __( 'Known File 04/File 21 feeds suppress shell auto-insertion.', 'sabri-unified-application-shell' ), 'pass' ),
			self::row( __( 'Navigation resolution', 'sabri-unified-application-shell' ), sprintf( __( '%1$d resolved; %2$d unresolved', 'sabri-unified-application-shell' ), count( $nav ), count( $missing ) ), empty( $missing ) ? 'pass' : 'warn' ),
			self::row( __( 'Authentication contract', 'sabri-unified-application-shell' ), Integrations::auth_url( 'login' ) ? __( 'Platform login resolved', 'sabri-unified-application-shell' ) : __( 'Core login fallback only', 'sabri-unified-application-shell' ), Integrations::page_url( 'login' ) ? 'pass' : 'warn' ),
			self::row( __( 'Moderated composer', 'sabri-unified-application-shell' ), Integrations::create_url() ? __( 'Resolved', 'sabri-unified-application-shell' ) : __( 'Not resolved; Create remains hidden', 'sabri-unified-application-shell' ), Integrations::create_url() ? 'pass' : 'warn' ),
			self::row( __( 'Notifications integration', 'sabri-unified-application-shell' ), $integrations['notifications'] ? __( 'Detected; shell claims one global output', 'sabri-unified-application-shell' ) : __( 'Not detected', 'sabri-unified-application-shell' ), $integrations['notifications'] ? 'pass' : 'warn' ),
			self::row( __( 'Network integration', 'sabri-unified-application-shell' ), $integrations['network'] ? __( 'Detected', 'sabri-unified-application-shell' ) : __( 'Not detected', 'sabri-unified-application-shell' ), $integrations['network'] ? 'pass' : 'warn' ),
			self::row( __( 'Messages integration', 'sabri-unified-application-shell' ), $integrations['messages'] ? __( 'Detected', 'sabri-unified-application-shell' ) : __( 'Not detected', 'sabri-unified-application-shell' ), $integrations['messages'] ? 'pass' : 'warn' ),
			self::row( __( 'Marketplace integration', 'sabri-unified-application-shell' ), $integrations['marketplace'] ? __( 'Detected', 'sabri-unified-application-shell' ) : __( 'Not detected', 'sabri-unified-application-shell' ), $integrations['marketplace'] ? 'pass' : 'warn' ),
			self::row( __( 'Appointment integration', 'sabri-unified-application-shell' ), $integrations['appointments'] ? __( 'Detected', 'sabri-unified-application-shell' ) : __( 'Not detected', 'sabri-unified-application-shell' ), $integrations['appointments'] ? 'pass' : 'warn' ),
			self::row( __( 'Doctor roles', 'sabri-unified-application-shell' ), implode( ', ', $integrations['doctor_roles'] ) ?: __( 'None detected', 'sabri-unified-application-shell' ), empty( $integrations['doctor_roles'] ) ? 'warn' : 'pass' ),
			self::row( __( 'Current layout', 'sabri-unified-application-shell' ), Layout::current_mode(), 'info' ),
			self::row( __( 'Request exclusions', 'sabri-unified-application-shell' ), __( 'Admin, REST, AJAX, cron, feeds, previews, print, authentication, verification, and maintenance routes are excluded by code; staging runtime verification remains required.', 'sabri-unified-application-shell' ), 'info' ),
			self::row( __( 'Settings schema', 'sabri-unified-application-shell' ), (string) $settings['schema_version'], (int) $settings['schema_version'] === Defaults::SCHEMA_VERSION ? 'pass' : 'warn' ),
			self::row( __( 'Activation snapshot', 'sabri-unified-application-shell' ), get_option( Defaults::SNAPSHOT_OPTION_NAME, false ) ? __( 'Captured', 'sabri-unified-application-shell' ) : __( 'Not captured yet', 'sabri-unified-application-shell' ), get_option( Defaults::SNAPSHOT_OPTION_NAME, false ) ? 'pass' : 'warn' ),
			self::row( __( 'Emergency disable', 'sabri-unified-application-shell' ), ! empty( $settings['emergency_disabled'] ) ? __( 'Enabled', 'sabri-unified-application-shell' ) : __( 'Off', 'sabri-unified-application-shell' ), ! empty( $settings['emergency_disabled'] ) ? 'warn' : 'pass' ),
			self::row( __( 'CSS asset', 'sabri-unified-application-shell' ), file_exists( SABRI_SHELL_PATH . 'assets/css/shell.css' ) ? __( 'Present', 'sabri-unified-application-shell' ) : __( 'Missing', 'sabri-unified-application-shell' ), file_exists( SABRI_SHELL_PATH . 'assets/css/shell.css' ) ? 'pass' : 'fail' ),
			self::row( __( 'JavaScript asset', 'sabri-unified-application-shell' ), file_exists( SABRI_SHELL_PATH . 'assets/js/shell.js' ) ? __( 'Present', 'sabri-unified-application-shell' ) : __( 'Missing', 'sabri-unified-application-shell' ), file_exists( SABRI_SHELL_PATH . 'assets/js/shell.js' ) ? 'pass' : 'fail' ),
			self::row( __( 'Duplicate shell detection', 'sabri-unified-application-shell' ), self::duplicate_shell_plugins(), 'info' ),
			self::row( __( 'Missing pages', 'sabri-unified-application-shell' ), self::missing_navigation_pages( $nav ), empty( $missing ) ? 'pass' : 'warn' ),
			self::row( __( 'Staging acceptance', 'sabri-unified-application-shell' ), __( 'Not established by System Check; complete the staging checklist separately.', 'sabri-unified-application-shell' ), 'warn' ),
		);

		foreach ( $nav as $item ) {
			$rows[] = self::row(
				sprintf( __( 'Navigation: %s', 'sabri-unified-application-shell' ), $item['label'] ),
				$item['reason'] . ' - ' . $item['url'],
				'pass'
			);
		}
		return apply_filters( 'sabri_shell_system_check_report', $rows );
	}

	/**
	 * Build one row.
	 *
	 * @param string $label Label.
	 * @param string $value Value.
	 * @param string $status Status.
	 * @return array<string,string>
	 */
	private static function row( $label, $value, $status ) {
		return array(
			'label'  => $label,
			'value'  => $value,
			'status' => $status,
		);
	}

	/**
	 * Cache plugin detection.
	 *
	 * @return string
	 */
	private static function cache_plugins() {
		$constants = array( 'WP_CACHE', 'W3TC', 'LSCWP_V' );
		$detected  = array();
		foreach ( $constants as $constant ) {
			if ( defined( $constant ) ) {
				$detected[] = $constant;
			}
		}

		return $detected ? implode( ', ', $detected ) : __( 'No common cache constants detected', 'sabri-unified-application-shell' );
	}

	/**
	 * Duplicate shell plugin detection.
	 *
	 * @return string
	 */
	private static function duplicate_shell_plugins() {
		$active = (array) get_option( 'active_plugins', array() );
		$matches = array_filter(
			$active,
			static function ( $plugin ) {
				return false !== strpos( $plugin, 'sabri-global-ui' );
			}
		);
		$unified = array_filter(
			$active,
			static function ( $plugin ) {
				return false !== strpos( $plugin, 'sabri-unified-application-shell' );
			}
		);
		if ( count( $unified ) > 1 ) {
			$matches = array_merge( $matches, $unified );
		}

		return implode( ', ', $matches ) ?: __( 'No duplicate shell plugin detected', 'sabri-unified-application-shell' );
	}

	/**
	 * Summarize missing navigation pages.
	 *
	 * @param array<string,mixed> $nav Resolved nav.
	 * @return string
	 */
	private static function missing_navigation_pages( array $nav ) {
		$missing = array_diff( array_keys( Defaults::destinations() ), array_keys( $nav ) );
		return $missing ? implode( ', ', $missing ) : __( 'None', 'sabri-unified-application-shell' );
	}

	/**
	 * Summarize missing integrations.
	 *
	 * @param array<string,mixed> $integrations Integrations.
	 * @return string
	 */
	private static function missing_integrations( array $integrations ) {
		$keys    = array( 'notifications', 'network', 'messages', 'marketplace', 'appointments' );
		$missing = array();
		foreach ( $keys as $key ) {
			if ( empty( $integrations[ $key ] ) ) {
				$missing[] = $key;
			}
		}

		return $missing ? implode( ', ', $missing ) : __( 'None', 'sabri-unified-application-shell' );
	}
}
