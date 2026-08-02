<?php
// Minimal WordPress compatibility layer for deterministic shell contract tests.
define( 'ABSPATH', __DIR__ . '/' );
define( 'HOUR_IN_SECONDS', 3600 );
define( 'ARRAY_A', 'ARRAY_A' );

$GLOBALS['test_options'] = array();
$GLOBALS['test_transients'] = array();
$GLOBALS['test_post_status'] = array();
$GLOBALS['test_permalinks'] = array();
$GLOBALS['test_shortcodes'] = array();
$GLOBALS['test_user_meta'] = array();
$GLOBALS['test_users'] = array();
$GLOBALS['test_user_caps'] = array();
$GLOBALS['test_membership_assertions'] = array();
$GLOBALS['test_directory_eligible'] = array();
$GLOBALS['test_approved_fields'] = array();
$GLOBALS['test_public_contact'] = array();
$GLOBALS['test_profiles'] = array();
$GLOBALS['test_filter_overrides'] = array();

function __( $text ) { return $text; }
function esc_html__( $text ) { return $text; }
function esc_attr__( $text ) { return $text; }
function apply_filters( $tag, $value, ...$args ) {
	if ( array_key_exists( $tag, $GLOBALS['test_filter_overrides'] ) ) {
		$override = $GLOBALS['test_filter_overrides'][ $tag ];
		return is_callable( $override ) ? $override( $value, ...$args ) : $override;
	}
	return $value;
}
function do_action() {}
function add_action() {}
function add_filter() {}
function remove_action() {}
function register_setting() {}
function sanitize_key( $value ) { return strtolower( preg_replace( '/[^a-z0-9_\-]/', '', (string) $value ) ); }
function sanitize_title( $value ) { return trim( strtolower( preg_replace( '/[^a-z0-9]+/i', '-', (string) $value ) ), '-' ); }
function sanitize_text_field( $value ) { return trim( strip_tags( (string) $value ) ); }
function sanitize_textarea_field( $value ) { return trim( strip_tags( (string) $value ) ); }
function absint( $value ) { return abs( (int) $value ); }
function esc_url_raw( $value ) { return (string) $value; }
function wp_http_validate_url( $value ) { return (bool) filter_var( $value, FILTER_VALIDATE_URL ); }
function get_option( $key, $default = false ) { return array_key_exists( $key, $GLOBALS['test_options'] ) ? $GLOBALS['test_options'][ $key ] : $default; }
function update_option( $key, $value ) { $GLOBALS['test_options'][ $key ] = $value; return true; }
function delete_option( $key ) { unset( $GLOBALS['test_options'][ $key ] ); return true; }
function get_transient( $key ) { return $GLOBALS['test_transients'][ $key ] ?? false; }
function set_transient( $key, $value ) { $GLOBALS['test_transients'][ $key ] = $value; return true; }
function delete_transient( $key ) { unset( $GLOBALS['test_transients'][ $key ] ); return true; }
function determine_locale() { return 'en_US'; }
function get_locale() { return 'en_US'; }
function get_post_status( $id ) { return $GLOBALS['test_post_status'][ $id ] ?? false; }
function get_permalink( $id = 0 ) { $id = is_object( $id ) ? $id->ID : $id; return $GLOBALS['test_permalinks'][ $id ] ?? ''; }
function home_url( $path = '/' ) { return 'https://example.test' . ( '/' === $path ? '/' : $path ); }
function get_posts() { return array(); }
function has_shortcode( $content, $shortcode ) { return false !== strpos( (string) $content, '[' . $shortcode ); }
function shortcode_exists( $shortcode ) { return in_array( $shortcode, $GLOBALS['test_shortcodes'], true ); }
function post_type_exists() { return false; }
function get_post_type_archive_link() { return false; }
function get_page_by_path() { return null; }
function wp_parse_url( $url, $component = -1 ) { return parse_url( $url, $component ); }
function untrailingslashit( $value ) { return rtrim( $value, '/' ); }
function add_query_arg( $key, $value, $url ) { return $url . ( false === strpos( $url, '?' ) ? '?' : '&' ) . rawurlencode( $key ) . '=' . rawurlencode( $value ); }
function wp_login_url( $redirect = '' ) { return 'https://example.test/wp-login.php' . ( $redirect ? '?redirect_to=' . rawurlencode( $redirect ) : '' ); }
function wp_registration_url() { return 'https://example.test/wp-login.php?action=register'; }
function wp_lostpassword_url() { return 'https://example.test/wp-login.php?action=lostpassword'; }
function wp_unslash( $value ) { return $value; }
function is_admin() { return false; }
function wp_doing_ajax() { return false; }
function wp_doing_cron() { return false; }
function is_front_page() { return false; }
function is_singular() { return false; }
function get_queried_object_id() { return 0; }
function get_user_meta( $user_id, $key, $single = true ) { return $GLOBALS['test_user_meta'][ $user_id ][ $key ] ?? ''; }
function get_userdata( $user_id ) { return $GLOBALS['test_users'][ $user_id ] ?? false; }
function get_current_user_id() { return 0; }
function user_can( $user, $cap = '' ) { $id = $user instanceof WP_User ? $user->ID : absint( $user ); return ! empty( $GLOBALS['test_user_caps'][ $id ][ $cap ] ); }
function wp_roles() { return (object) array( 'roles' => array( 'administrator' => array(), 'sabri_doctor' => array(), 'sabri_verified_doctor' => array() ) ); }
function current_time() { return '2026-07-30 00:00:00'; }


class SMC_Contracts {
	public static function assertions( $user_id ) {
		return $GLOBALS['test_membership_assertions'][ absint( $user_id ) ] ?? array();
	}
}
class SPD_Verification_Adapter {
	public static function directory_eligible( $user_id ) {
		return ! empty( $GLOBALS['test_directory_eligible'][ absint( $user_id ) ] );
	}
	public static function approved_fields( $user_id ) {
		return $GLOBALS['test_approved_fields'][ absint( $user_id ) ] ?? array();
	}
}
class SPD_Helpers {
	public static function get( $user_id, $key, $default = '' ) {
		$fields = $GLOBALS['test_approved_fields'][ absint( $user_id ) ] ?? array();
		if ( array_key_exists( $key, $fields ) ) {
			return $fields[ $key ];
		}
		$profile = $GLOBALS['test_profiles'][ absint( $user_id ) ] ?? array();
		return array_key_exists( $key, $profile ) ? $profile[ $key ] : $default;
	}
	public static function can_show_contact( $user_id, $founder = false ) {
		return ! empty( $GLOBALS['test_public_contact'][ absint( $user_id ) ] ) || ( $founder && smc_is_founder( $user_id ) );
	}
	public static function profile_url( $user_id ) {
		return 'https://example.test/profile/?user=' . absint( $user_id );
	}
	public static function verification_status( $user_id ) {
		return ! empty( $GLOBALS['test_directory_eligible'][ absint( $user_id ) ] ) ? 'verified' : 'pending';
	}
}
function smc_get_profile( $user_id ) { return $GLOBALS['test_profiles'][ absint( $user_id ) ] ?? array(); }
function smc_is_founder( $user_id ) { return 'founder' === ( $GLOBALS['test_membership_assertions'][ absint( $user_id ) ]['account_class'] ?? '' ); }

class WP_User {
	public $ID;
	public $roles;
	public $display_name;
	public $user_nicename;
	public function __construct( $id, $roles = array(), $name = 'User' ) {
		$this->ID = $id;
		$this->roles = $roles;
		$this->display_name = $name;
		$this->user_nicename = strtolower( str_replace( ' ', '-', $name ) );
	}
}
class WP_Query {}
class TestWpdb {
	public $prefix = 'wp_';
	public function prepare( $query ) { return $query; }
	public function get_row() { return null; }
}
$GLOBALS['wpdb'] = new TestWpdb();

require_once dirname( __DIR__ ) . '/includes/class-defaults.php';
require_once dirname( __DIR__ ) . '/includes/class-settings.php';
require_once dirname( __DIR__ ) . '/includes/class-integrations.php';
require_once dirname( __DIR__ ) . '/includes/class-navigation.php';
require_once dirname( __DIR__ ) . '/includes/class-safe-mode.php';
require_once dirname( __DIR__ ) . '/includes/class-layout.php';
require_once dirname( __DIR__ ) . '/includes/class-home-feed.php';
