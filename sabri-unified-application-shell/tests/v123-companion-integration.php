<?php
/**
 * V12.3 exact File20/File25 companion-source smoke test (not WordPress staging).
 * Usage: php tests/v123-companion-integration.php <checked-out-file25-root>
 */
namespace {
	if ( PHP_SAPI !== 'cli' ) { exit( 1 ); }
	define( 'ABSPATH', __DIR__ . '/' );
	define( 'SABRI_SHELL_VERSION', '1.4.17' );
	define( 'SABRI_PUBLIC_EXPERIENCE_VERSION', '0.15.0' );
	$file25 = $argv[1] ?? '';
	if ( ! is_string( $file25 ) || ! is_dir( $file25 . '/includes' ) ) {
		fwrite( STDERR, 'File25 checked-out directory required.' . PHP_EOL );
		exit( 2 );
	}
	define( 'SABRI_PUBLIC_EXPERIENCE_DIR', realpath( $file25 ) . '/' );
	$GLOBALS['welcome_options'] = array();
	$GLOBALS['welcome_filters'] = array();
	$GLOBALS['is_safe'] = false;
	function __( $s, $domain = '' ) { return $s; }
	function esc_html__( $s, $domain = '' ) { return htmlspecialchars( $s, ENT_QUOTES, 'UTF-8' ); }
	function esc_attr__( $s, $domain = '' ) { return htmlspecialchars( $s, ENT_QUOTES, 'UTF-8' ); }
	function esc_html( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' ); }
	function esc_attr( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' ); }
	function esc_url( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' ); }
	function esc_url_raw( $s ) { return $s; }
	function sanitize_key( $s ) { return strtolower( preg_replace( '/[^a-z0-9_-]/', '', (string) $s ) ); }
	function sanitize_text_field( $s ) { return trim( strip_tags( (string) $s ) ); }
	function sanitize_textarea_field( $s ) { return trim( strip_tags( (string) $s ) ); }
	function absint( $v ) { return abs( (int) $v ); }
	function home_url( $p = '/' ) { return 'https://example.test' . $p; }
	function wp_parse_url( $s, $component = -1 ) { return parse_url( $s, $component ); }
	function wp_unslash( $s ) { return $s; }
	function wp_json_encode( $s ) { return json_encode( $s ); }
	function get_option( $name, $default = false ) { return $GLOBALS['welcome_options'][ $name ] ?? $default; }
	function is_admin() { return false; }
	function wp_doing_ajax() { return false; }
	function is_feed() { return false; }
	function is_404() { return false; }
	function add_filter( $tag, $callback, $priority = 10, $args = 1 ) {
		$GLOBALS['welcome_filters'][ $tag ][] = array( $priority, $callback );
	}
	function has_filter( $tag, $callback ) {
		foreach ( $GLOBALS['welcome_filters'][ $tag ] ?? array() as $row ) {
			if ( $row[1] === $callback ) { return $row[0]; }
		}
		return false;
	}
	function apply_filters( $tag, $value, ...$args ) {
		$rows = $GLOBALS['welcome_filters'][ $tag ] ?? array();
		usort( $rows, static function ( $a, $b ) { return $a[0] <=> $b[0]; } );
		foreach ( $rows as $row ) { $value = call_user_func( $row[1], $value, ...$args ); }
		return $value;
	}
	class SMC_Contracts {
		public static function assertions( $user_id ) {
			return array(
				'user_id' => (int) $user_id,
				'account_class' => 'founder',
				'approved' => true, 'eligible' => true, 'suspended' => false,
			);
		}
	}
}
namespace Sabri\UnifiedShell {
	final class SafeMode {
		public static function disabled() { return ! empty( $GLOBALS['is_safe'] ); }
	}
	final class Layout {
		const MINIMAL = 'minimal';
		const IMMERSIVE = 'immersive';
		public static function current_mode() { return 'two-column'; }
	}
}
namespace {
	require SABRI_PUBLIC_EXPERIENCE_DIR . 'includes/class-public-url.php';
	require SABRI_PUBLIC_EXPERIENCE_DIR . 'includes/class-design-system.php';
	require SABRI_PUBLIC_EXPERIENCE_DIR . 'includes/class-three-plan-corrections.php';
	require dirname( __DIR__ ) . '/includes/class-welcome-experience.php';

	function sabri_visual_experience_render_welcome( array $args = array() ) {
		return \Sabri\PublicExperience\Three_Plan_Corrections::render_welcome_panel( $args );
	}
	$assertions = 0;
	$check = static function ( $condition, $reason ) use ( &$assertions ) {
		if ( ! $condition ) {
			fwrite( STDERR, 'FAIL: ' . $reason . PHP_EOL );
			exit( 1 );
		}
		$assertions++;
	};
	$owner = \Sabri\UnifiedShell\WelcomeExperience::class;
	$contract = \Sabri\PublicExperience\Design_System::class;
	$check( ! $owner::public_eligible(), 'Disabled default cannot show welcome.' );
	$GLOBALS['welcome_options'][ $owner::OPTION ] = array_replace(
		$owner::defaults(), array( 'enabled' => true, 'message' => 'Learn classical homeopathy' )
	);
	$check( ! $owner::public_eligible(), 'Enabled without canonical Founder signature is not public.' );
	add_filter( 'sabri_shell_file25_visual_contract', array( $contract, 'filter_shell_visual_contract' ), 20 );
	$config = $owner::configuration();
	$signature = $owner::approval_signature( $config );
	$check( '' !== $signature, 'Exact native File25 renderer/owner hash must be available.' );
	$GLOBALS['welcome_options'][ $owner::APPROVAL_OPTION ] = array( 'signer_id' => 91, 'signature' => $signature );
	$_SERVER['REQUEST_URI'] = '/';
	$check( $owner::approval_valid( $config ), 'File00 Founder assertion and exact visual digest accepted.' );
	$check( $owner::public_eligible(), 'First eligible homepage authorized only after valid approval.' );
	$css_path = SABRI_PUBLIC_EXPERIENCE_DIR . 'assets/css/design-system.css';
	$original_css = file_get_contents( $css_path );
	$check( is_string( $original_css ), 'Genuine File25 CSS is readable.' );
	try {
		file_put_contents( $css_path, $original_css . "\n/* simulated unapproved design change */\n" );
		$check( ! $owner::approval_valid( $config ), 'A changed File25 design asset invalidates Founder approval.' );
	} finally {
		file_put_contents( $css_path, $original_css );
	}
	$check( $owner::approval_valid( $config ), 'Exact File25 CSS restoration recovers approved hash.' );
	$GLOBALS['welcome_options'][ $owner::AUDIT_GAP_OPTION ] = array( 'event' => 'config_saved' );
	$check( ! $owner::public_eligible(), 'Unreconciled configuration audit gap blocks approved welcome.' );
	unset( $GLOBALS['welcome_options'][ $owner::AUDIT_GAP_OPTION ] );
	$_SERVER['REQUEST_URI'] = '/?appointment=42';
	$check( ! $owner::public_eligible(), 'Homepage task/deep-link query blocked.' );
	$_SERVER['REQUEST_URI'] = '/';
	$GLOBALS['is_safe'] = true;
	$check( ! $owner::public_eligible(), 'File20 canonical SafeMode shuts off intro instantly.' );
	$GLOBALS['is_safe'] = false;
	$check( '' === \Sabri\PublicExperience\Three_Plan_Corrections::render_welcome_panel( array( 'title' => 'Unauthorized' ) ), 'File25 may not self-invoke.' );
	$visual = \Sabri\PublicExperience\Three_Plan_Corrections::render_welcome_panel( array(
		'invoked_by_file_20' => true,
		'title' => 'Welcome',
		'message' => 'Read & learn',
		'continue_url' => home_url( '/' ),
	) );
	foreach ( array( 'data-sabri-welcome-skip', 'data-sabri-welcome-dismiss', 'data-sabri-welcome-continue', 'aria-live="polite"' ) as $token ) {
		$check( false !== strpos( $visual, $token ), 'Real File25 visual contract includes ' . $token );
	}
	ob_start();
	$owner::render();
	$first = ob_get_clean();
	$check( 1 === substr_count( $first, 'id="sabri-shell-welcome"' ), 'One canonical File20 host renders File25 panel.' );
	$check( false !== strpos( $first, 'data-sabri-welcome-skip' ), 'Rendered host includes the genuine File25 Skip control.' );
	ob_start();
	$owner::render();
	$again = ob_get_clean();
	$check( '' === $again, 'Body and footer cannot render duplicate welcome hosts.' );
	$GLOBALS['welcome_options'][ $owner::OPTION ]['message'] = 'Changed without approval';
	$check( ! $owner::public_eligible(), 'Stale approval invalidates on changed copy.' );
	echo 'File20/File25 V12.3 companion source integration: ' . $assertions . ' assertions passed' . PHP_EOL;
}
