<?php
/**
 * Canonical File 20 Create visibility contract for File 22 integration.
 *
 * @package SabriUnifiedApplicationShell
 */

namespace Sabri\UnifiedShell;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Supplies the role-aware, Safe-Mode-aware Create producer contract.
 *
 * File 20 owns whether the global Create control may be considered. File 22
 * remains the final adapter/workflow authority through the official
 * `sabri_shell_can_show_create` filter.
 */
final class CreateContract {
	/** Prevent accidental recursion through third-party filter callbacks. */
	private static $resolving = false;

	/** Whether the exact package-owned public contract is available. */
	public static function available() {
		return defined( 'SABRI_SHELL_CREATE_CONTRACT_VERSION' )
			&& '1.0.1' === SABRI_SHELL_CREATE_CONTRACT_VERSION
			&& defined( 'SABRI_SHELL_CREATE_CONTRACT_OWNER' )
			&& 'sabri-unified-application-shell' === SABRI_SHELL_CREATE_CONTRACT_OWNER
			&& defined( 'SABRI_SHELL_CREATE_FUNCTIONS_OWNED' )
			&& true === SABRI_SHELL_CREATE_FUNCTIONS_OWNED
			&& function_exists( 'sabri_shell_create_contract_available' )
			&& function_exists( 'sabri_shell_create_visible_for_current_user' )
			&& ! SafeMode::disabled();
	}

	/**
	 * Resolve current-user Create visibility only.
	 *
	 * No foreign subject/user ID is accepted. The logged-in principal, current
	 * File 00-backed publishing assertion, shell settings, Safe Mode, canonical
	 * Create URL, and File 22 adapter filter are re-evaluated on every call.
	 */
	public static function visible_for_current_user() {
		if ( self::$resolving || ! self::available() || ! is_user_logged_in() ) {
			return false;
		}

		$user_id = get_current_user_id();
		if ( $user_id <= 0 ) {
			return false;
		}

		$settings = Settings::get();
		if (
			empty( $settings['enabled'] ) ||
			empty( $settings['header']['enabled'] ) ||
			empty( $settings['header']['create'] )
		) {
			return false;
		}

		$base_allowed = Integrations::can_publish( $user_id ) && '' !== Integrations::create_url();
		self::$resolving = true;
		try {
			/**
			 * Filter the current principal's global Create visibility.
			 *
			 * File 22 consumes this exact hook and supplies its final adapter-aware
			 * decision. Callers must not use it to authorize a different subject.
			 *
			 * @param bool                $base_allowed Existing File 20 decision.
			 * @param int                 $user_id Current logged-in user ID.
			 * @param array<string,mixed> $settings Current File 20 settings.
			 */
			return (bool) apply_filters( 'sabri_shell_can_show_create', $base_allowed, $user_id, $settings );
		} finally {
			self::$resolving = false;
		}
	}
}
