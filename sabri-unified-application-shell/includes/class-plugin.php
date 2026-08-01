<?php
/**
 * Main plugin coordinator.
 *
 * @package SabriUnifiedApplicationShell
 */

namespace Sabri\UnifiedShell;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers all public and admin services.
 */
final class Plugin {
	/**
	 * Singleton instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'admin_init', array( Settings::class, 'register' ) );
		add_action( 'init', array( __CLASS__, 'maybe_upgrade' ), 1 );
		Navigation::register_cache_hooks();
		HomeFeed::register();
		Renderer::register();
		add_action( 'init', array( __CLASS__, 'maybe_flush_rewrite_rules' ), 99 );

		if ( is_admin() ) {
			Admin::register();
		}
	}

	/**
	 * Apply idempotent settings-schema upgrades.
	 *
	 * @return void
	 */
	public static function maybe_upgrade() {
		$current = get_option( Defaults::OPTION_NAME, array() );
		$schema  = is_array( $current ) && isset( $current['schema_version'] ) ? absint( $current['schema_version'] ) : 0;
		if ( $schema >= Defaults::SCHEMA_VERSION ) {
			return;
		}
		Settings::ensure_defaults();
		Navigation::invalidate_cache();
		Integrations::invalidate_cache();
	}

	/**
	 * Activation callback.
	 *
	 * @return void
	 */
	public static function activate() {
		Snapshot::capture_activation_snapshot();
		Settings::ensure_defaults();
		Navigation::invalidate_cache();
		update_option( 'sabri_shell_flush_rewrite_rules', 1, false );
	}

	/**
	 * Deactivation callback.
	 *
	 * @return void
	 */
	public static function deactivate() {
		Navigation::invalidate_cache();
		Integrations::invalidate_cache();
	}

	/**
	 * Run a scheduled one-time rewrite-rule flush.
	 *
	 * @return void
	 */
	public static function maybe_flush_rewrite_rules() {
		if ( ! get_option( 'sabri_shell_flush_rewrite_rules' ) ) {
			return;
		}

		if ( function_exists( 'flush_rewrite_rules' ) ) {
			flush_rewrite_rules( false );
		}

		delete_option( 'sabri_shell_flush_rewrite_rules' );
	}
}
