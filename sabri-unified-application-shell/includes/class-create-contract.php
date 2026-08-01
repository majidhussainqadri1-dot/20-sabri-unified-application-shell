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

	/** Register presentation evidence for the exact current-user decision. */
	public static function register() {
		// File 22 verifies the SafeMode class without autoloading. Eagerly resolve
		// the package-owned class before File 22 registers its shell bridge.
		if ( ! class_exists( SafeMode::class ) ) {
			return;
		}
		add_filter( 'body_class', array( __CLASS__, 'body_classes' ), 30 );
	}

	/** Verify classes and global producer functions are sourced by this package. */
	private static function package_owned() {
		if ( ! defined( 'SABRI_SHELL_PATH' ) || ! defined( 'SABRI_SHELL_FILE' ) ) {
			return false;
		}
		$path = realpath( (string) SABRI_SHELL_PATH );
		$file = realpath( (string) SABRI_SHELL_FILE );
		if ( false === $path || false === $file || dirname( $file ) !== $path ) {
			return false;
		}
		$prefix = rtrim( $path, '/\\' ) . DIRECTORY_SEPARATOR;
		try {
			foreach ( array( __CLASS__, SafeMode::class ) as $class_name ) {
				$source = ( new \ReflectionClass( $class_name ) )->getFileName();
				$source = is_string( $source ) ? realpath( $source ) : false;
				if ( false === $source || 0 !== strpos( $source, $prefix ) ) {
					return false;
				}
			}
			foreach ( array( 'sabri_shell_create_contract_available', 'sabri_shell_create_visible_for_current_user' ) as $function ) {
				if ( ! function_exists( $function ) ) {
					return false;
				}
				$source = ( new \ReflectionFunction( $function ) )->getFileName();
				$source = is_string( $source ) ? realpath( $source ) : false;
				if ( false === $source || $source !== $file ) {
					return false;
				}
			}
		} catch ( \Throwable $error ) {
			unset( $error );
			return false;
		}
		return true;
	}

	/** Whether the exact package-owned public contract is available. */
	public static function available() {
		return defined( 'SABRI_SHELL_CREATE_CONTRACT_VERSION' )
			&& '1.0.1' === SABRI_SHELL_CREATE_CONTRACT_VERSION
			&& defined( 'SABRI_SHELL_CREATE_CONTRACT_OWNER' )
			&& 'sabri-unified-application-shell' === SABRI_SHELL_CREATE_CONTRACT_OWNER
			&& defined( 'SABRI_SHELL_CREATE_FUNCTIONS_OWNED' )
			&& true === SABRI_SHELL_CREATE_FUNCTIONS_OWNED
			&& self::package_owned()
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
		} catch ( \Throwable $error ) {
			unset( $error );
			return false;
		} finally {
			self::$resolving = false;
		}
	}

	/** Add a stable class so actual shell output follows the official contract. */
	public static function body_classes( $classes ) {
		if ( ! is_array( $classes ) ) {
			$classes = array();
		}
		$classes[] = self::visible_for_current_user()
			? 'sabri-shell-create-contract-allowed'
			: 'sabri-shell-create-contract-denied';
		return array_values( array_unique( $classes ) );
	}
}
