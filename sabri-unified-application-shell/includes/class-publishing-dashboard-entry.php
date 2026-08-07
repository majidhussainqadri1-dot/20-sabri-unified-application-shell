<?php
/**
 * Authorized UI entry points for File 23 Publishing Dashboard.
 *
 * @package SabriUnifiedApplicationShell
 */

namespace Sabri\UnifiedShell;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** File 20 owns presentation only; File 23 owns route, authorization and data. */
final class PublishingDashboardEntry {
	private static $registered = false;

	/** Register cross-file presentation hooks. */
	public static function register() {
		if ( self::$registered ) {
			return;
		}
		self::$registered = true;
		add_filter( 'sabri_public_experience/action_url', array( __CLASS__, 'profile_action_url' ), 999, 3 );
		add_action( 'admin_bar_menu', array( __CLASS__, 'add_admin_bar_entry' ), 85 );
		add_filter( 'body_class', array( __CLASS__, 'body_classes' ) );
	}

	/** Fail-closed current-principal authorization through File 23. */
	public static function can_access( $user_id = 0 ) {
		if ( ! is_user_logged_in() ) {
			return false;
		}
		$current = absint( get_current_user_id() );
		$user_id = $user_id ? absint( $user_id ) : $current;
		if ( ! $current || $user_id !== $current ) {
			return false;
		}
		$required = array(
			'SPDB_Membership_Guard' => array( 'is_user_approved', 'is_user_suspended', 'is_user_founder', 'can_user_publish', 'can_user_view_restricted_dashboard' ),
			'SPDB_Capabilities'     => array( 'current_user_can' ),
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
			$approved   = true === \SPDB_Membership_Guard::is_user_approved( $user_id );
			$suspended  = true === \SPDB_Membership_Guard::is_user_suspended( $user_id );
			$founder    = true === \SPDB_Membership_Guard::is_user_founder( $user_id );
			$publisher  = true === \SPDB_Membership_Guard::can_user_publish( $user_id );
			$view       = true === \SPDB_Membership_Guard::can_user_view_restricted_dashboard( $user_id );
			$capability = true === \SPDB_Capabilities::current_user_can( 'spdb_view_dashboard' );
		} catch ( \Throwable $error ) {
			unset( $error );
			return false;
		}
		return $approved && ! $suspended && ( $founder || $publisher ) && $view && $capability;
	}

	/** Resolve File 23's exact same-origin protected route. */
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
		return self::same_origin_url( $url );
	}

	/** Role-aware label. */
	public static function label() {
		try {
			if ( class_exists( 'SPDB_Membership_Guard' ) && is_callable( array( 'SPDB_Membership_Guard', 'is_user_founder' ) ) && true === \SPDB_Membership_Guard::is_user_founder( get_current_user_id() ) ) {
				return __( 'Publishing Dashboard', 'sabri-unified-application-shell' );
			}
		} catch ( \Throwable $error ) {
			unset( $error );
		}
		return __( 'My Publishing Dashboard', 'sabri-unified-application-shell' );
	}

	/** Provide File 25's own-profile action without granting authority. */
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

	/** Add a no-JavaScript WordPress account-toolbar entry. */
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
				'meta'   => array( 'class' => 'sabri-publishing-dashboard-admin-bar' ),
			)
		);
	}

	/** Add a truthful body marker only when the entry is authorized. */
	public static function body_classes( $classes ) {
		if ( self::can_access() ) {
			$classes[] = 'sabri-shell-has-publishing-dashboard-entry';
		}
		return array_values( array_unique( $classes ) );
	}

	/** Validate same scheme, host and port. */
	private static function same_origin_url( $candidate ) {
		if ( ! is_string( $candidate ) || '' === trim( $candidate ) ) {
			return '';
		}
		$url = esc_url_raw( $candidate, array( 'http', 'https' ) );
		if ( '' === $url ) {
			return '';
		}
		$home   = wp_parse_url( home_url( '/' ) );
		$target = wp_parse_url( $url );
		if ( ! is_array( $home ) || ! is_array( $target ) ) {
			return '';
		}
		$hs = strtolower( isset( $home['scheme'] ) ? (string) $home['scheme'] : '' );
		$ts = strtolower( isset( $target['scheme'] ) ? (string) $target['scheme'] : '' );
		$hh = strtolower( rtrim( isset( $home['host'] ) ? (string) $home['host'] : '', '.' ) );
		$th = strtolower( rtrim( isset( $target['host'] ) ? (string) $target['host'] : '', '.' ) );
		$hp = isset( $home['port'] ) ? absint( $home['port'] ) : ( 'https' === $hs ? 443 : 80 );
		$tp = isset( $target['port'] ) ? absint( $target['port'] ) : ( 'https' === $ts ? 443 : 80 );
		return in_array( $ts, array( 'http', 'https' ), true ) && $hs === $ts && '' !== $hh && $hh === $th && $hp === $tp ? $url : '';
	}
}
