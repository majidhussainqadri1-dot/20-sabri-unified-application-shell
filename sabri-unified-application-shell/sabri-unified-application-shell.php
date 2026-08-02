<?php
/**
 * Plugin Name: Sabri Unified Application Shell
 * Plugin URI: https://github.com/majidhussainqadri1-dot/20-sabri-unified-application-shell
 * Description: Secure responsive public application shell for the Sabri Social Homeopathy Platform.
 * Version: 1.2.0
 * Author: Dr. Allamah Majid Hussain Sabri Muhaddith Mursheed
 * Text Domain: sabri-unified-application-shell
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 *
 * @package SabriUnifiedApplicationShell
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SABRI_SHELL_VERSION', '1.2.0' );
define( 'SABRI_SHELL_FILE', __FILE__ );
define( 'SABRI_SHELL_PATH', plugin_dir_path( __FILE__ ) );
define( 'SABRI_SHELL_URL', plugin_dir_url( __FILE__ ) );
define( 'SABRI_SHELL_SLUG', 'sabri-unified-application-shell' );
define( 'SABRI_SHELL_TEXT_DOMAIN', 'sabri-unified-application-shell' );

// File 20 v4 operational-completion services. Native domain ownership is preserved.
require_once SABRI_SHELL_PATH . 'includes/class-plan-v4-audit.php';
require_once SABRI_SHELL_PATH . 'includes/class-plan-v4-assurance.php';
require_once SABRI_SHELL_PATH . 'includes/class-plan-v4-contract-health.php';
require_once SABRI_SHELL_PATH . 'includes/class-plan-v4-context.php';
require_once SABRI_SHELL_PATH . 'includes/class-plan-v4-settings-concurrency.php';
require_once SABRI_SHELL_PATH . 'includes/class-plan-v4-privacy-cache.php';
require_once SABRI_SHELL_PATH . 'includes/class-plan-v4-jobs.php';
require_once SABRI_SHELL_PATH . 'includes/class-plan-v4-recovery.php';


spl_autoload_register(
	static function ( $class_name ) {
		$prefix = 'Sabri\\UnifiedShell\\';
		if ( 0 !== strpos( $class_name, $prefix ) ) {
			return;
		}

		$relative = substr( $class_name, strlen( $prefix ) );
		$relative = str_replace( '\\', DIRECTORY_SEPARATOR, $relative );
		$parts    = explode( DIRECTORY_SEPARATOR, $relative );
		$base     = array_shift( $parts );
		$file     = 'class-' . strtolower( preg_replace( '/(?<!^)[A-Z]/', '-$0', $base ) ) . '.php';

		if ( ! empty( $parts ) ) {
			$file = implode( DIRECTORY_SEPARATOR, $parts ) . DIRECTORY_SEPARATOR . $file;
		}

		$paths = array(
			SABRI_SHELL_PATH . 'includes' . DIRECTORY_SEPARATOR . $file,
			SABRI_SHELL_PATH . 'admin' . DIRECTORY_SEPARATOR . $file,
		);

		foreach ( $paths as $path ) {
			if ( file_exists( $path ) ) {
				require_once $path;
				return;
			}
		}
	}
);

/*
 * File 22 performs non-autoloading reflection checks for File 20's SafeMode
 * class and Create producers. Resolve the established corrective classes and
 * prove their exact package origin before advertising that contract.
 */
$sabri_shell_corrective_classes_owned = static function () {
	$expected = array(
		'Sabri\\UnifiedShell\\SafeMode'       => realpath( SABRI_SHELL_PATH . 'includes/class-safe-mode.php' ),
		'Sabri\\UnifiedShell\\CreateContract' => realpath( SABRI_SHELL_PATH . 'includes/class-create-contract.php' ),
		'Sabri\\UnifiedShell\\LayoutCorrection' => realpath( SABRI_SHELL_PATH . 'includes/class-layout-correction.php' ),
	);
	try {
		foreach ( $expected as $class_name => $expected_file ) {
			if ( false === $expected_file || ! class_exists( $class_name ) ) {
				return false;
			}
			$source = ( new ReflectionClass( $class_name ) )->getFileName();
			$source = is_string( $source ) ? realpath( $source ) : false;
			if ( false === $source || $source !== $expected_file ) {
				return false;
			}
		}
	} catch ( Throwable $error ) {
		unset( $error );
		return false;
	}
	return true;
};
$sabri_shell_corrective_classes_are_owned = $sabri_shell_corrective_classes_owned();

/*
 * Exact optional File 20 Create producer contract consumed by File 22.
 * The contract version remains stable while the File 20 runtime advances.
 */
$sabri_shell_create_contract_unclaimed = $sabri_shell_corrective_classes_are_owned
	&& ! defined( 'SABRI_SHELL_CREATE_CONTRACT_VERSION' )
	&& ! defined( 'SABRI_SHELL_CREATE_CONTRACT_OWNER' )
	&& ! defined( 'SABRI_SHELL_CREATE_FUNCTIONS_OWNED' )
	&& ! function_exists( 'sabri_shell_create_contract_available' )
	&& ! function_exists( 'sabri_shell_create_visible_for_current_user' );

if ( $sabri_shell_create_contract_unclaimed ) {
	define( 'SABRI_SHELL_CREATE_CONTRACT_VERSION', '1.0.1' );
	define( 'SABRI_SHELL_CREATE_CONTRACT_OWNER', 'sabri-unified-application-shell' );
	define( 'SABRI_SHELL_CREATE_FUNCTIONS_OWNED', true );

	/** Whether the exact package-owned File 20 Create contract is usable. */
	function sabri_shell_create_contract_available() {
		return class_exists( 'Sabri\\UnifiedShell\\CreateContract' )
			&& Sabri\UnifiedShell\CreateContract::available();
	}

	/** Whether the current logged-in principal may see the global Create action. */
	function sabri_shell_create_visible_for_current_user() {
		return class_exists( 'Sabri\\UnifiedShell\\CreateContract' )
			&& Sabri\UnifiedShell\CreateContract::visible_for_current_user();
	}
}

/*
 * File 20 v4 central-plan harmonization is separately package-owned. It must
 * never be allowed to weaken or impersonate the established File 22 contract.
 */
$sabri_shell_central_plan_contract_owned = static function () {
	$expected = realpath( SABRI_SHELL_PATH . 'includes/class-central-plan-contract.php' );
	try {
		if ( false === $expected || ! class_exists( 'Sabri\\UnifiedShell\\CentralPlanContract' ) ) {
			return false;
		}
		$source = ( new ReflectionClass( 'Sabri\\UnifiedShell\\CentralPlanContract' ) )->getFileName();
		$source = is_string( $source ) ? realpath( $source ) : false;
		return false !== $source && $source === $expected;
	} catch ( Throwable $error ) {
		unset( $error );
		return false;
	}
};
$sabri_shell_central_plan_contract_is_owned = $sabri_shell_central_plan_contract_owned();

register_activation_hook( __FILE__, array( 'Sabri\\UnifiedShell\\Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Sabri\\UnifiedShell\\Plugin', 'deactivate' ) );

add_action(
	'plugins_loaded',
	static function () use ( $sabri_shell_corrective_classes_are_owned, $sabri_shell_central_plan_contract_is_owned ) {
		load_plugin_textdomain( 'sabri-unified-application-shell', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		Sabri\UnifiedShell\Plugin::instance()->register();
		if ( $sabri_shell_corrective_classes_are_owned ) {
			Sabri\UnifiedShell\CreateContract::register();
			Sabri\UnifiedShell\LayoutCorrection::register();
		}
		if ( $sabri_shell_central_plan_contract_is_owned ) {
			Sabri\UnifiedShell\CentralPlanContract::register();
		}
	}
);

unset(
	$sabri_shell_corrective_classes_owned,
	$sabri_shell_corrective_classes_are_owned,
	$sabri_shell_create_contract_unclaimed,
	$sabri_shell_central_plan_contract_owned,
	$sabri_shell_central_plan_contract_is_owned
);
