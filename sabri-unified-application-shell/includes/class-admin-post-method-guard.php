<?php
/**
 * HTTP method boundary for destructive File 20 admin-post actions.
 *
 * The hardened recovery handlers already enforce capability and action-specific
 * nonces. This final wrapper additionally enforces the governing rule that
 * repair, rollback and Emergency state changes are POST-only server actions.
 *
 * @package SabriUnifiedApplicationShell
 */
namespace Sabri\UnifiedShell;
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class AdminPostMethodGuard {
	const CONTRACT_VERSION = '1.0.0';

	/** Replace the existing hardened callbacks only after they have registered. */
	public static function register() {
		if ( ! is_admin() ) { return; }
		$actions = array(
			'sabri_shell_repair'    => 'handle_admin_repair',
			'sabri_shell_rollback'  => 'handle_admin_rollback',
			'sabri_shell_emergency' => 'handle_admin_emergency',
		);
		foreach ( $actions as $action => $method ) {
			remove_action( 'admin_post_' . $action, array( FutureShellV5EighthHardening::class, $method ) );
			add_action( 'admin_post_' . $action, array( __CLASS__, $method ) );
		}
		add_filter( 'sabri_shell_system_check_sections', array( __CLASS__, 'system_check' ), 112 );
	}

	/** Reject GET and every other non-POST transport before nonce/action handling. */
	private static function require_post_method() {
		$method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) : '';
		if ( 'POST' === $method ) { return; }
		wp_die(
			esc_html__( 'This File 20 action accepts POST requests only.', 'sabri-unified-application-shell' ),
			esc_html__( 'Method Not Allowed', 'sabri-unified-application-shell' ),
			array( 'response' => 405 )
		);
	}

	public static function handle_admin_repair() {
		self::require_post_method();
		FutureShellV5EighthHardening::handle_admin_repair();
	}

	public static function handle_admin_rollback() {
		self::require_post_method();
		FutureShellV5EighthHardening::handle_admin_rollback();
	}

	public static function handle_admin_emergency() {
		self::require_post_method();
		FutureShellV5EighthHardening::handle_admin_emergency();
	}

	public static function system_check( $sections ) {
		$sections = is_array( $sections ) ? $sections : array();
		$sections['admin_post_method_guard'] = array(
			'label' => __( 'Destructive admin HTTP method guard', 'sabri-unified-application-shell' ),
			'contract_version' => self::CONTRACT_VERSION,
			'repair' => 'POST-only-plus-existing-capability-and-nonce',
			'rollback' => 'POST-only-plus-existing-capability-and-nonce',
			'emergency' => 'POST-only-plus-existing-capability-and-nonce',
		);
		return $sections;
	}
}
