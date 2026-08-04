<?php
/**
 * Authorized entry points for File 23 Publishing Dashboard.
 *
 * @package SabriUnifiedApplicationShell
 */

namespace Sabri\UnifiedShell;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Exposes File 23's existing protected route through File 20 and File 25 UI.
 *
 * This class owns navigation presentation only. File 23 remains the route,
 * authorization, capability, data and operational owner.
 */
final class PublishingDashboardEntry {
	/**
	 * Whether hooks have already been registered.
	 *
	 * @var bool
	 */
	private static $registered = false;

	/**
	 * Register integration hooks.
	 *
	 * @return void
	 */
	public static function register() {
		if ( self::$registered ) {
			return;
		}
		self::$registered = true;

		add_filter( 'sabri_public_experience/action_url', array( __CLASS__, 'profile_action_url' ), 999, 3 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ), 40 );
		add_action( 'admin_bar_menu', array( __CLASS__, 'add_admin_bar_entry' ), 85 );
		add_filter( 'body_class', array( __CLASS__, 'body_classes' ) );
	}

	/**
	 * Whether the current principal may receive a Publishing Dashboard link.
	 *
	 * The decision is deliberately stricter than route discovery: File 23 must
	 * be active, File 00 must approve the principal, the account must not be
	 * suspended, and File 23's own capability decision must pass.
	 *
	 * @param int $user_id Optional user ID. Only the current principal is valid.
	 * @return bool
	 */
	public static function can_access( $user_id = 0 ) {
		if ( ! function_exists( 'is_user_logged_in' ) || ! is_user_logged_in() ) {
			return false;
		}

		$current = absint( get_current_user_id() );
		$user_id = $user_id ? absint( $user_id ) : $current;
		if ( ! $current || $user_id !== $current ) {
			return false;
		}

		$required = array(
			'SPDB_Membership_Guard' => array(
				'is_user_approved',
				'is_user_suspended',
				'is_user_founder',
				'can_user_publish',
				'can_user_view_restricted_dashboard',
			),
			'SPDB_Capabilities' => array( 'current_user_can' ),
			'SPDB_Dashboard_Router' => array( 'route_url' ),
		);

		foreach ( $required as $class_name => $methods ) {
			if ( ! class_exists( $class_name ) ) {
				return false;
			}
			foreach ( $methods as $method ) {
				if ( ! is_callable( array( $class_name, $method ) ) ) {
					return false;
				}
			}
		}

		try {
			$approved = true === \SPDB_Membership_Guard::is_user_approved( $user_id );
			$suspended = true === \SPDB_Membership_Guard::is_user_suspended( $user_id );
			$founder = true === \SPDB_Membership_Guard::is_user_founder( $user_id );
			$publisher = true === \SPDB_Membership_Guard::can_user_publish( $user_id );
			$view_allowed = true === \SPDB_Membership_Guard::can_user_view_restricted_dashboard( $user_id );
			$capability = true === \SPDB_Capabilities::current_user_can( 'spdb_view_dashboard' );
		} catch ( \Throwable $error ) {
			unset( $error );
			return false;
		}

		return $approved
			&& ! $suspended
			&& ( $founder || $publisher )
			&& $view_allowed
			&& $capability;
	}

	/**
	 * Resolve the exact File 23 protected route and enforce same-site identity.
	 *
	 * @return string
	 */
	public static function url() {
		if ( ! self::can_access() ) {
			return '';
		}

		try {
			$url = \SPDB_Dashboard_Router::route_url( 'overview' );
		} catch ( \Throwable $error ) {
			unset( $error );
			return '';
		}

		return self::same_site_url( $url );
	}

	/**
	 * Provide the dormant File 25 own-profile action with File 23's exact URL.
	 *
	 * @param mixed  $url Existing URL candidate.
	 * @param mixed  $action Action key.
	 * @param mixed  $user_id Profile owner ID.
	 * @return string
	 */
	public static function profile_action_url( $url, $action, $user_id ) {
		if ( 'publishing_dashboard' !== sanitize_key( (string) $action ) ) {
			return is_string( $url ) ? $url : '';
		}

		$current = absint( get_current_user_id() );
		if ( ! $current || absint( $user_id ) !== $current ) {
			return '';
		}

		return self::url();
	}

	/**
	 * Enqueue File 20-owned UI mounting for the sidebar and account menu.
	 *
	 * @return void
	 */
	public static function enqueue_assets() {
		$url = self::url();
		if ( '' === $url ) {
			return;
		}

		$version = defined( 'SABRI_SHELL_VERSION' ) ? SABRI_SHELL_VERSION : '1.2.1';
		wp_enqueue_style(
			'sabri-shell-publishing-dashboard-entry',
			SABRI_SHELL_URL . 'assets/css/publishing-dashboard-entry.css',
			array( 'sabri-shell' ),
			$version
		);
		wp_enqueue_script(
			'sabri-shell-publishing-dashboard-entry',
			SABRI_SHELL_URL . 'assets/js/publishing-dashboard-entry.js',
			array(),
			$version,
			true
		);
		wp_localize_script(
			'sabri-shell-publishing-dashboard-entry',
			'SabriShellPublishingDashboard',
			array(
				'url'          => esc_url_raw( $url ),
				'label'        => self::label(),
				'sectionLabel' => __( 'Publishing', 'sabri-unified-application-shell' ),
				'currentPath'  => self::request_path(),
			)
		);
	}

	/**
	 * Add a no-JavaScript shortcut inside WordPress's account toolbar menu.
	 *
	 * @param \WP_Admin_Bar $admin_bar Admin bar instance.
	 * @return void
	 */
	public static function add_admin_bar_entry( $admin_bar ) {
		if ( ! is_object( $admin_bar ) || ! is_callable( array( $admin_bar, 'add_node' ) ) ) {
			return;
		}
		$url = self::url();
		if ( '' === $url ) {
			return;
		}
		$admin_bar->add_node(
			array(
				'id'     => 'sabri-publishing-dashboard',
				'parent' => 'my-account',
				'title'  => esc_html( self::label() ),
				'href'   => esc_url( $url ),
				'meta'   => array(
					'class' => 'sabri-publishing-dashboard-admin-bar',
				),
			)
		);
	}

	/**
	 * Add a scoped class only when the authorized entry exists.
	 *
	 * @param array<int,string> $classes Existing body classes.
	 * @return array<int,string>
	 */
	public static function body_classes( $classes ) {
		if ( self::can_access() ) {
			$classes[] = 'sabri-shell-has-publishing-dashboard-entry';
		}
		return array_values( array_unique( $classes ) );
	}

	/**
	 * Role-aware visible label.
	 *
	 * @return string
	 */
	private static function label() {
		try {
			if ( class_exists( 'SPDB_Membership_Guard' )
				&& is_callable( array( 'SPDB_Membership_Guard', 'is_user_founder' ) )
				&& true === \SPDB_Membership_Guard::is_user_founder( get_current_user_id() )
			) {
				return __( 'Publishing Dashboard', 'sabri-unified-application-shell' );
			}
		} catch ( \Throwable $error ) {
			unset( $error );
		}
		return __( 'My Publishing Dashboard', 'sabri-unified-application-shell' );
	}

	/**
	 * Validate an HTTP(S) URL against the current site's scheme, host and port.
	 *
	 * @param mixed $candidate URL candidate.
	 * @return string
	 */
	private static function same_site_url( $candidate ) {
		if ( ! is_string( $candidate ) || '' === trim( $candidate ) ) {
			return '';
		}
		$url = esc_url_raw( $candidate, array( 'http', 'https' ) );
		if ( '' === $url ) {
			return '';
		}
		$home = wp_parse_url( home_url( '/' ) );
		$target = wp_parse_url( $url );
		if ( ! is_array( $home ) || ! is_array( $target ) ) {
			return '';
		}
		$home_scheme = strtolower( (string) ( $home['scheme'] ?? '' ) );
		$target_scheme = strtolower( (string) ( $target['scheme'] ?? '' ) );
		$home_host = strtolower( rtrim( (string) ( $home['host'] ?? '' ), '.' ) );
		$target_host = strtolower( rtrim( (string) ( $target['host'] ?? '' ), '.' ) );
		$home_port = isset( $home['port'] ) ? absint( $home['port'] ) : ( 'https' === $home_scheme ? 443 : 80 );
		$target_port = isset( $target['port'] ) ? absint( $target['port'] ) : ( 'https' === $target_scheme ? 443 : 80 );
		if ( ! in_array( $target_scheme, array( 'http', 'https' ), true )
			|| $home_scheme !== $target_scheme
			|| '' === $home_host
			|| $home_host !== $target_host
			|| $home_port !== $target_port
		) {
			return '';
		}
		return $url;
	}

	/**
	 * Return the current request path for accessible active-state rendering.
	 *
	 * @return string
	 */
	private static function request_path() {
		$request = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';
		$path = wp_parse_url( $request, PHP_URL_PATH );
		return is_string( $path ) && '' !== $path ? $path : '/';
	}
}
