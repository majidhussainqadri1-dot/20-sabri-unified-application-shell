<?php
/** Runtime and static regression for File 20 1.1.2. */
declare(strict_types=1);

namespace {
	define( 'ABSPATH', __DIR__ . '/' );
	define( 'SABRI_SHELL_PATH', dirname( __DIR__ ) . DIRECTORY_SEPARATOR );
	define( 'SABRI_SHELL_FILE', __FILE__ );
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
		if ( 'throw' === $GLOBALS['shell_filter_mode'] ) { throw new \RuntimeException( 'Adapter failure' ); }
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
	$assert( ! CreateContract::visible_for_current_user(), 'File 22 cannot elevate a principal denied by File 00/File 20.' );
	Integrations::$can_publish = true;

	$GLOBALS['shell_filter_mode'] = 'recursive';
	$assert( ! CreateContract::visible_for_current_user(), 'Recursive visibility resolution fails closed.' );
	$GLOBALS['shell_filter_mode'] = 'throw';
	$assert( ! CreateContract::visible_for_current_user(), 'Adapter/filter exceptions fail closed.' );
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
	$contract = file_get_contents( $root . '/includes/class-create-contract.php' );
	$css = file_get_contents( $root . '/assets/css/shell-corrective-1.1.1.css' );
	$js = file_get_contents( $root . '/assets/js/shell-corrective-1.1.1.js' );
	$assert( false !== strpos( $main, "SABRI_SHELL_CREATE_CONTRACT_VERSION', '1.0.1" ), 'Main package declares exact File 22 contract version.' );
	$assert( false !== strpos( $main, "SABRI_SHELL_CREATE_CONTRACT_OWNER', 'sabri-unified-application-shell" ), 'Main package declares canonical owner.' );
	$assert( false !== strpos( $main, 'SABRI_SHELL_CREATE_FUNCTIONS_OWNED' ), 'Main package declares function ownership.' );
	$assert(
		false !== strpos( $main, "'Sabri\\\\UnifiedShell\\\\SafeMode'       => realpath" ) &&
		false !== strpos( $main, "'Sabri\\\\UnifiedShell\\\\CreateContract' => realpath" ) &&
		false !== strpos( $main, 'new ReflectionClass' ),
		'Canonical bootstrap proves SafeMode and CreateContract package files before File 22 reflection.'
	);
	$assert( false !== strpos( $main, 'CreateContract::register()' ) && false !== strpos( $main, 'LayoutCorrection::register()' ), 'Corrective services register from canonical bootstrap.' );
	$assert( false !== strpos( $contract, 'ReflectionClass' ) && false !== strpos( $contract, 'ReflectionFunction' ), 'Contract verifies package-source ownership.' );
	$assert( false !== strpos( $contract, 'catch ( \\Throwable $error )' ), 'Contract fails closed on adapter/filter exceptions.' );
	$renderer = file_get_contents( $root . '/includes/class-renderer.php' );
	$integrations = file_get_contents( $root . '/includes/class-integrations.php' );
	$navigation = file_get_contents( $root . '/includes/class-navigation.php' );
	$assert( false !== strpos( $contract, 'if ( ! $base_allowed )' ), 'File 22 filter is a deny-only narrowing gate.' );
	$assert( false !== strpos( $renderer, 'sabri_shell_create_visible_for_current_user()' ), 'Server-rendered Create markup consumes the exact visibility contract.' );
	$assert( false === strpos( $renderer, "create_or_doctors'] ||" ), 'Explicit mobile Create configuration cannot bypass authorization.' );
	$assert( false === strpos( $integrations, "'posts_per_page'         => -1" ) && false === strpos( $navigation, "'posts_per_page'         => -1" ), 'Shortcode fallback discovery contains no unbounded Page query.' );
	$assert( false !== strpos( $integrations, '$max_pages  = 50;' ), 'Shortcode compatibility scan has an explicit page ceiling.' );
	$doctor_projection = explode( 'public static function doctor_public_data', $integrations, 2 )[1];
	$doctor_projection = explode( 'public static function language_switcher', $doctor_projection, 2 )[0];
	$assert( false === strpos( $doctor_projection, 'smc_get_profile' ) && false === strpos( $doctor_projection, 'SPD_Helpers::get' ), 'Public doctor projection never falls back to raw File 00/File 03 metadata getters.' );
	$assert( false !== strpos( $css, 'flex-wrap: wrap' ) && false !== strpos( $css, '.sabri-shell-user-card > div' ), 'Navigation wrap and user-card separation are present.' );
	$assert( false !== strpos( $js, 'MutationObserver' ) && false !== strpos( $js, 'sabri-hnf-single-content' ), 'Bounded managed-single target recovery is present.' );
	$assert( false !== strpos( $js, 'sabri-hnf-content-integrity-single' ) && false !== strpos( $js, 'sabri-shell-publication-layout-failed' ), 'Neutral legacy-publication signal and fail-safe state are supported.' );
	$assert( false !== strpos( $css, 'sabri-shell-publication-layout-pending' ) && false !== strpos( $css, 'visibility: hidden !important' ), 'Desktop sidebar is suppressed while publication target recovery is pending.' );
	$assert( false !== strpos( $css, 'sabri-shell-publication-layout-failed' ) && false !== strpos( $css, 'max-inline-size: 960px' ), 'Failed target recovery preserves a readable centered publication without sidebar overlay.' );
	$layout_correction = file_get_contents( $root . '/includes/class-layout-correction.php' );
	$assert( false !== strpos( $layout_correction, "SABRI_SHELL_VERSION . '-publication-layout-r3'" ), 'Corrective assets use an explicit cache-busting identity.' );
	$assert( false === strpos( $js, 'appendChild(' ) && false === strpos( $js, 'replaceChild(' ) && false === strpos( $js, 'insertBefore(' ), 'Correction does not reparent theme or companion DOM.' );

	if ( $failures ) {
		fwrite( STDERR, "File20 Create/layout correction: {$passed} PASS, " . count( $failures ) . " FAIL\n- " . implode( "\n- ", $failures ) . "\n" );
		exit( 1 );
	}
	echo "File20 Create/layout correction: {$passed} PASS, 0 FAIL\n";
}
