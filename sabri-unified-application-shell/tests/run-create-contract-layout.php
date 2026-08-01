<?php
/** Runtime and static regression for File 20 1.1.1. */
declare(strict_types=1);

namespace {
	define( 'ABSPATH', __DIR__ . '/' );
	define( 'SABRI_SHELL_CREATE_CONTRACT_VERSION', '1.0.1' );
	define( 'SABRI_SHELL_CREATE_CONTRACT_OWNER', 'sabri-unified-application-shell' );
	define( 'SABRI_SHELL_CREATE_FUNCTIONS_OWNED', true );

	$GLOBALS['shell_logged_in'] = true;
	$GLOBALS['shell_user_id'] = 7;
	$GLOBALS['shell_filter_mode'] = 'pass';
	$GLOBALS['shell_filters'] = array();

	function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
		$GLOBALS['shell_filters'][] = array( $hook, $callback, $priority, $accepted_args );
		return true;
	}
	function is_user_logged_in() { return (bool) $GLOBALS['shell_logged_in']; }
	function get_current_user_id() { return (int) $GLOBALS['shell_user_id']; }
	function apply_filters( $hook, $value, ...$args ) {
		if ( 'sabri_shell_can_show_create' !== $hook ) { return $value; }
		if ( 'deny' === $GLOBALS['shell_filter_mode'] ) { return false; }
		if ( 'allow' === $GLOBALS['shell_filter_mode'] ) { return true; }
		if ( 'recursive' === $GLOBALS['shell_filter_mode'] ) { return sabri_shell_create_visible_for_current_user(); }
		return $value;
	}
	function sabri_shell_create_contract_available() {
		return \Sabri\UnifiedShell\CreateContract::available();
	}
	function sabri_shell_create_visible_for_current_user() {
		return \Sabri\UnifiedShell\CreateContract::visible_for_current_user();
	}
}

namespace Sabri\UnifiedShell {
	final class SafeMode { public static $disabled = false; public static function disabled() { return self::$disabled; } }
	final class Settings {
		public static $settings = array(
			'enabled' => true,
			'header' => array( 'enabled' => true, 'create' => true ),
		);
		public static function get() { return self::$settings; }
	}
	final class Integrations {
		public static $can_publish = true;
		public static $create_url = '/create/';
		public static function can_publish( $user_id ) { return 7 === (int) $user_id && self::$can_publish; }
		public static function create_url() { return self::$create_url; }
	}

	require dirname( __DIR__ ) . '/includes/class-create-contract.php';

	$failures = array();
	$passed = 0;
	$assert = static function ( $condition, $message ) use ( &$failures, &$passed ) {
		if ( $condition ) { ++$passed; return; }
		$failures[] = $message;
	};

	CreateContract::register();
	$assert( count( $GLOBALS['shell_filters'] ) === 1 && 'body_class' === $GLOBALS['shell_filters'][0][0], 'Create contract registers one public decision class.' );
	$assert( CreateContract::available(), 'Exact package-owned contract is available outside Safe Mode.' );
	$assert( CreateContract::visible_for_current_user(), 'Authorized current user is visible.' );

	$GLOBALS['shell_filter_mode'] = 'deny';
	$assert( ! CreateContract::visible_for_current_user(), 'File 22 adapter filter may deny the control.' );
	$GLOBALS['shell_filter_mode'] = 'allow';
	Integrations::$can_publish = false;
	$assert( CreateContract::visible_for_current_user(), 'File 22 may provide the final adapter-aware allowance after File 20 identity checks.' );
	Integrations::$can_publish = true;

	$GLOBALS['shell_filter_mode'] = 'recursive';
	$assert( ! CreateContract::visible_for_current_user(), 'Recursive visibility resolution fails closed.' );
	$GLOBALS['shell_filter_mode'] = 'pass';

	SafeMode::$disabled = true;
	$assert( ! CreateContract::available() && ! CreateContract::visible_for_current_user(), 'Safe Mode disables contract and presentation.' );
	SafeMode::$disabled = false;

	$GLOBALS['shell_logged_in'] = false;
	$assert( ! CreateContract::visible_for_current_user(), 'Logged-out user is denied.' );
	$GLOBALS['shell_logged_in'] = true;

	Settings::$settings['header']['create'] = false;
	$assert( ! CreateContract::visible_for_current_user(), 'Disabled Create setting is respected.' );
	Settings::$settings['header']['create'] = true;

	$GLOBALS['shell_filter_mode'] = 'allow';
	$classes = CreateContract::body_classes( array( 'existing' ) );
	$assert( in_array( 'sabri-shell-create-contract-allowed', $classes, true ), 'Allowed body class is emitted.' );
	$GLOBALS['shell_filter_mode'] = 'deny';
	$classes = CreateContract::body_classes( array() );
	$assert( in_array( 'sabri-shell-create-contract-denied', $classes, true ), 'Denied body class is emitted.' );

	$root = dirname( __DIR__ );
	$main = file_get_contents( $root . '/sabri-unified-application-shell.php' );
	$css = file_get_contents( $root . '/assets/css/shell-corrective-1.1.1.css' );
	$js = file_get_contents( $root . '/assets/js/shell-corrective-1.1.1.js' );
	$assert( false !== strpos( $main, "SABRI_SHELL_CREATE_CONTRACT_VERSION', '1.0.1" ), 'Main package declares exact File 22 contract version.' );
	$assert( false !== strpos( $main, "SABRI_SHELL_CREATE_CONTRACT_OWNER', 'sabri-unified-application-shell" ), 'Main package declares canonical owner.' );
	$assert( false !== strpos( $main, 'SABRI_SHELL_CREATE_FUNCTIONS_OWNED' ), 'Main package declares function ownership.' );
	$assert( false !== strpos( $main, 'CreateContract::register()' ) && false !== strpos( $main, 'LayoutCorrection::register()' ), 'Corrective services register from canonical bootstrap.' );
	$assert( false !== strpos( $css, 'flex-wrap: wrap' ) && false !== strpos( $css, '.sabri-shell-user-card > div' ), 'Navigation wrap and user-card separation are present.' );
	$assert( false !== strpos( $js, 'MutationObserver' ) && false !== strpos( $js, 'sabri-hnf-single-content' ), 'Bounded managed-single target recovery is present.' );
	$assert( false === strpos( $js, 'appendChild(' ) && false === strpos( $js, 'replaceChild(' ), 'Correction does not reparent theme or companion DOM.' );

	if ( $failures ) {
		fwrite( STDERR, "File20 Create/layout correction: {$passed} PASS, " . count( $failures ) . " FAIL\n- " . implode( "\n- ", $failures ) . "\n" );
		exit( 1 );
	}
	echo "File20 Create/layout correction: {$passed} PASS, 0 FAIL\n";
}
