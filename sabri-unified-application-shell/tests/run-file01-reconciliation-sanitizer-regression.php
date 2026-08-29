<?php
/* Regression for the live-proven WordPress Settings API sanitizer conflict. */

namespace {
	define( 'ABSPATH', __DIR__ . '/' );
	$GLOBALS['test_options'] = array();
	$GLOBALS['test_filters'] = array();
	$GLOBALS['test_post_status'] = array();

	function sanitize_key( $value ) { return strtolower( preg_replace( '/[^a-z0-9_\-]/', '', (string) $value ) ); }
	function sanitize_text_field( $value ) { return trim( strip_tags( (string) $value ) ); }
	function absint( $value ) { return abs( (int) $value ); }
	function wp_json_encode( $value, $flags = 0 ) { return json_encode( $value, $flags ); }
	function current_time( $type = 'mysql', $gmt = false ) { unset( $type, $gmt ); return '2026-08-29 00:00:00'; }
	function wp_generate_uuid4() { return '11111111-2222-4333-8444-555555555555'; }
	function get_post_status( $id ) { return $GLOBALS['test_post_status'][ absint( $id ) ] ?? false; }
	function get_post_type( $id ) { return ! empty( $GLOBALS['test_post_status'][ absint( $id ) ] ) ? 'page' : false; }

	function test_callback_id( $callback ) {
		if ( is_array( $callback ) ) { return ( is_object( $callback[0] ) ? get_class( $callback[0] ) : $callback[0] ) . '::' . $callback[1]; }
		return is_string( $callback ) ? $callback : 'closure-' . spl_object_hash( $callback );
	}
	function add_filter( $tag, $callback, $priority = 10, $accepted_args = 1 ) {
		$GLOBALS['test_filters'][ $tag ][ $priority ][ test_callback_id( $callback ) ] = array( 'function' => $callback, 'accepted_args' => $accepted_args );
		return true;
	}
	function remove_filter( $tag, $callback, $priority = 10 ) {
		$id = test_callback_id( $callback );
		if ( ! isset( $GLOBALS['test_filters'][ $tag ][ $priority ][ $id ] ) ) { return false; }
		unset( $GLOBALS['test_filters'][ $tag ][ $priority ][ $id ] );
		return true;
	}
	function apply_test_filters( $tag, $value, ...$args ) {
		if ( empty( $GLOBALS['test_filters'][ $tag ] ) ) { return $value; }
		$priorities = array_keys( $GLOBALS['test_filters'][ $tag ] );
		sort( $priorities, SORT_NUMERIC );
		foreach ( $priorities as $priority ) {
			foreach ( $GLOBALS['test_filters'][ $tag ][ $priority ] as $entry ) {
				$all = array_merge( array( $value ), $args );
				$value = call_user_func_array( $entry['function'], array_slice( $all, 0, $entry['accepted_args'] ) );
			}
		}
		return $value;
	}
	function get_option( $key, $default = false ) { return array_key_exists( $key, $GLOBALS['test_options'] ) ? $GLOBALS['test_options'][ $key ] : $default; }
	function update_option( $key, $value, $autoload = null ) {
		unset( $autoload );
		$value = apply_test_filters( 'sanitize_option_' . $key, $value, $key, $value );
		$GLOBALS['test_options'][ $key ] = $value;
		return true;
	}
	function add_option( $key, $value, $deprecated = '', $autoload = 'yes' ) {
		unset( $deprecated, $autoload );
		if ( array_key_exists( $key, $GLOBALS['test_options'] ) ) { return false; }
		$GLOBALS['test_options'][ $key ] = $value;
		return true;
	}
	function delete_option( $key ) { unset( $GLOBALS['test_options'][ $key ] ); return true; }
}

namespace Sabri\UnifiedShell {
	final class Defaults {
		const OPTION_NAME = 'sabri_shell_settings';
		public static function destinations() { return array( 'founder' => array() ); }
	}
	final class Navigation {
		public static function page_owner_compatible( $route, $page_id ) { return 'founder' === $route && 164 === absint( $page_id ); }
		public static function invalidate_cache() {}
	}
	final class Settings {
		public static function register() { add_filter( 'sanitize_option_' . Defaults::OPTION_NAME, array( __CLASS__, 'sanitize' ), 10, 1 ); }
		public static function sanitize( $input ) {
			$existing = self::get();
			if ( ! is_array( $input ) ) { return $existing; }
			$tab = isset( $input['_active_tab'] ) ? sanitize_key( $input['_active_tab'] ) : '';
			if ( 'navigation' !== $tab ) { return $existing; }
			$existing['navigation'] = isset( $input['navigation'] ) && is_array( $input['navigation'] ) ? $input['navigation'] : array();
			return $existing;
		}
		public static function get() {
			$value = get_option( Defaults::OPTION_NAME, array() );
			return is_array( $value ) ? $value : array();
		}
		public static function enforce_owned_invariants( array $value ) { return $value; }
	}
	final class PlanV4SettingsConcurrency {
		const LOCK_OPTION = 'sabri_shell_settings_update_lock';
		const LOCK_TTL = 30;
		public static function record_programmatic_change( $old, $new, $reason ) { unset( $old, $new, $reason ); return 1; }
	}
}

namespace {
	require dirname( __DIR__ ) . '/includes/class-file01-reconciliation-adapter.php';
	use Sabri\UnifiedShell\Defaults;
	use Sabri\UnifiedShell\File01ReconciliationAdapter;
	use Sabri\UnifiedShell\Settings;

	$failures = array();
	$assert = static function ( $condition, $message ) use ( &$failures ) {
		echo ( $condition ? 'PASS: ' : 'FAIL: ' ) . $message . "\n";
		if ( ! $condition ) { $failures[] = $message; }
	};

	$GLOBALS['test_options'][ Defaults::OPTION_NAME ] = array( 'navigation' => array( 'founder' => array( 'page_id' => 0 ) ) );
	$GLOBALS['test_post_status'][164] = 'publish';
	Settings::register();

	$probe = get_option( Defaults::OPTION_NAME );
	$probe['navigation']['founder']['page_id'] = 164;
	update_option( Defaults::OPTION_NAME, $probe, false );
	$assert( 0 === absint( Settings::get()['navigation']['founder']['page_id'] ?? 0 ), 'Control reproduces live defect: tab-oriented registered sanitizer swallows a trusted programmatic Page-ID write.' );

	$plan = File01ReconciliationAdapter::plan( null, array( 'legacy_key' => 'founder', 'page_id' => 164, 'target_owners' => array( 'file-20', 'file-21' ) ) );
	$action = array( 'action' => 'reconcile_legacy_mapping', 'legacy_key' => 'founder', 'page_id' => 164, 'owned' => true, 'owner_plan' => $plan );
	$receipt = File01ReconciliationAdapter::execute( null, $action, str_repeat( 'a', 64 ) );

	$assert( is_array( $receipt ) && ! empty( $receipt['success'] ), 'Adapter execute succeeds while the real registered sanitizer simulation is active.' );
	$assert( '1.0.1' === ( $receipt['command_version'] ?? '' ), 'Corrected reconciliation command contract is version 1.0.1.' );
	$assert( 164 === absint( Settings::get()['navigation']['founder']['page_id'] ?? 0 ), 'Trusted File20 programmatic writer persists founder Page ID instead of being swallowed.' );
	$assert( isset( $GLOBALS['test_filters']['sanitize_option_' . Defaults::OPTION_NAME][10]['Sabri\\UnifiedShell\\Settings::sanitize'] ), 'Settings::sanitize callback is restored immediately after the bounded trusted write.' );

	$rolled = File01ReconciliationAdapter::rollback( null, $receipt, str_repeat( 'a', 64 ) );
	$assert( is_array( $rolled ) && ! empty( $rolled['success'] ), 'Rollback succeeds through the same bounded trusted writer.' );
	$assert( 0 === absint( Settings::get()['navigation']['founder']['page_id'] ?? 0 ), 'Rollback restores the exact pre-reconciliation Page-ID state under the active sanitizer.' );

	if ( $failures ) { exit( 1 ); }
	echo "\nAll Settings API sanitizer reconciliation regressions passed.\n";
}
