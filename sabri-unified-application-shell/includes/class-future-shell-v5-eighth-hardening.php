<?php
/**
 * Eighth independent corrective hardening pass for Future Shell v5.
 *
 * Fresh review over merged 1.4.7. This layer closes post-merge gaps without
 * taking any native-domain authority away from canonical companion owners.
 *
 * @package SabriUnifiedApplicationShell
 */

namespace Sabri\UnifiedShell;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class FutureShellV5EighthHardening {
	const CONTRACT_VERSION = '1.0.8';

	public static function register() {
		/* File 25 is the only visual authority. Retire the stale File 20 editor. */
		add_action( 'admin_init', array( __CLASS__, 'retire_appearance_screen' ), 1 );
		add_action( 'admin_head', array( __CLASS__, 'admin_head_hardening' ), 999 );
		add_action( 'admin_notices', array( __CLASS__, 'render_hardened_admin_controls' ), 20 );
		add_action( 'admin_footer', array( __CLASS__, 'retire_legacy_admin_controls' ), 999 );

		/* Replace legacy one-click handlers with the hardened recovery lifecycle. */
		if ( is_admin() && class_exists( __NAMESPACE__ . '\\Admin', false ) ) {
			remove_action( 'admin_post_sabri_shell_repair', array( Admin::class, 'handle_repair' ) );
			remove_action( 'admin_post_sabri_shell_rollback', array( Admin::class, 'handle_rollback' ) );
			remove_action( 'admin_post_sabri_shell_emergency', array( Admin::class, 'handle_emergency' ) );
			add_action( 'admin_post_sabri_shell_repair', array( __CLASS__, 'handle_admin_repair' ) );
			add_action( 'admin_post_sabri_shell_rollback', array( __CLASS__, 'handle_admin_rollback' ) );
			add_action( 'admin_post_sabri_shell_emergency', array( __CLASS__, 'handle_admin_emergency' ) );
		}

		/* Final route classifier must work at web root and WordPress subdirectories. */
		add_filter( 'sabri_shell_layout_mode', array( __CLASS__, 'force_subdirectory_safe_sensitive_layout' ), 1500, 2 );

		/* Controlled, sanitized operator evidence export. */
		add_action( 'rest_api_init', array( __CLASS__, 'register_rest_routes' ) );

		add_filter( 'sabri_shell_system_check_sections', array( __CLASS__, 'system_check' ), 80 );
	}

	/** Prevent direct use of File 20's superseded Appearance editor. */
	public static function retire_appearance_screen() {
		if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) { return; }
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only navigation guard.
		$tab  = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only navigation guard.
		if ( 'sabri-shell' !== $page || 'appearance' !== $tab ) { return; }
		wp_safe_redirect( add_query_arg( array( 'page' => 'sabri-shell', 'sabri_shell_notice' => 'appearance-owned-by-file25' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	/** Hide superseded visual/recovery controls; hardened controls are rendered separately. */
	public static function admin_head_hardening() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		echo '<style id="sabri-shell-eighth-admin-hardening">.sabri-shell-admin .nav-tab[href*="tab=appearance"]{display:none!important}.sabri-shell-hardened-ops{margin:12px 0;padding:16px;background:#fff;border:1px solid #dcdcde}.sabri-shell-hardened-ops table{max-width:1200px}.sabri-shell-hardened-ops code{white-space:normal}</style>';
	}

	/** Render current hardened operator controls before the legacy page body. */
	public static function render_hardened_admin_controls() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only admin routing.
		$tab  = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only admin routing.
		if ( 'sabri-shell' !== $page ) { return; }

		$notice = isset( $_GET['sabri_shell_notice'] ) ? sanitize_key( wp_unslash( $_GET['sabri_shell_notice'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display-only status token.
		$messages = array(
			'hardened-repair-success'   => __( 'Hardened File 20 repair completed and was audited.', 'sabri-unified-application-shell' ),
			'hardened-repair-failed'    => __( 'Hardened repair did not complete; review System Check and retained recovery evidence.', 'sabri-unified-application-shell' ),
			'hardened-rollback-success' => __( 'Schema-compatible File 20 rollback completed and was audited.', 'sabri-unified-application-shell' ),
			'hardened-rollback-failed'  => __( 'Rollback was blocked or failed; the pre-existing state/evidence was retained.', 'sabri-unified-application-shell' ),
			'hardened-emergency-on'     => __( 'Emergency Disable is active with audited review metadata.', 'sabri-unified-application-shell' ),
			'hardened-emergency-off'    => __( 'The shell passed the re-enable gate and cache purge.', 'sabri-unified-application-shell' ),
			'hardened-emergency-failed' => __( 'Emergency state transition was blocked by validation or health evidence.', 'sabri-unified-application-shell' ),
		);
		if ( isset( $messages[ $notice ] ) ) {
			echo '<div class="notice notice-info"><p>' . esc_html( $messages[ $notice ] ) . '</p></div>';
		}

		if ( 'repair' === $tab ) {
			$actions = PlanV4Recovery::repair_actions( array() );
			$preview = PlanV4Recovery::preview_repair( array_keys( $actions ) );
			echo '<div class="sabri-shell-hardened-ops"><h2>' . esc_html__( 'Hardened Repair — Dry Run', 'sabri-unified-application-shell' ) . '</h2>';
			echo '<p>' . esc_html__( 'Review the exact/planned File-20-only operations below. Uncheck any action you do not want executed.', 'sabri-unified-application-shell' ) . '</p>';
			echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
			wp_nonce_field( 'sabri_shell_repair' );
			echo '<input type="hidden" name="action" value="sabri_shell_repair"><input type="hidden" name="expected_settings_version" value="' . esc_attr( $preview['settings_row_version'] ) . '">';
			echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'Run', 'sabri-unified-application-shell' ) . '</th><th>' . esc_html__( 'Action', 'sabri-unified-application-shell' ) . '</th><th>' . esc_html__( 'Dry-run diff', 'sabri-unified-application-shell' ) . '</th></tr></thead><tbody>';
			foreach ( $preview['operations'] as $operation ) {
				$key = sanitize_key( $operation['action'] );
				$diff = wp_json_encode( $operation['diff'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
				echo '<tr><td><input type="checkbox" name="repair_actions[]" value="' . esc_attr( $key ) . '" checked></td><td>' . esc_html( isset( $actions[ $key ] ) ? $actions[ $key ] : $key ) . '</td><td><code>' . esc_html( $diff ) . '</code></td></tr>';
			}
			echo '</tbody></table>';
			submit_button( __( 'Execute Selected Hardened Repair', 'sabri-unified-application-shell' ), 'primary', 'submit', false );
			echo '</form>';

			$snapshots = PlanV4Recovery::snapshot_list();
			echo '<hr><h2>' . esc_html__( 'Hardened Rollback', 'sabri-unified-application-shell' ) . '</h2>';
			if ( ! $snapshots ) {
				echo '<p>' . esc_html__( 'No Plan v4 recovery snapshot is available yet.', 'sabri-unified-application-shell' ) . '</p>';
			} else {
				echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
				wp_nonce_field( 'sabri_shell_rollback' );
				echo '<input type="hidden" name="action" value="sabri_shell_rollback"><label><strong>' . esc_html__( 'Snapshot', 'sabri-unified-application-shell' ) . '</strong> <select name="snapshot_id" required>';
				foreach ( array_reverse( $snapshots ) as $snapshot ) {
					$preview_rollback = PlanV4Recovery::preview_rollback( $snapshot['id'] );
					$compatible = ! is_wp_error( $preview_rollback ) && ! empty( $preview_rollback['compatible'] );
					$label = $snapshot['created_at'] . ' | ' . $snapshot['reason'] . ' | schema ' . $snapshot['schema_version'] . ' | ' . ( $compatible ? 'compatible' : 'read-only/incompatible' );
					echo '<option value="' . esc_attr( $snapshot['id'] ) . '" ' . disabled( $compatible, false, false ) . '>' . esc_html( $label ) . '</option>';
				}
				echo '</select></label> ';
				submit_button( __( 'Rollback Selected Compatible Snapshot', 'sabri-unified-application-shell' ), 'secondary', 'submit', false );
				echo '</form>';
			}
			echo '</div>';
		}

		if ( 'safe-mode' === $tab ) {
			$meta = SafeMode::emergency_metadata();
			echo '<div class="sabri-shell-hardened-ops"><h2>' . esc_html__( 'Hardened Safe Mode and Emergency Controls', 'sabri-unified-application-shell' ) . '</h2>';
			$safe_url = SafeMode::query_safe_mode_url( home_url( '/' ) );
			if ( $safe_url ) {
				echo '<p><a class="button" href="' . esc_url( $safe_url ) . '" target="_blank" rel="noopener">' . esc_html__( 'Open nonce-bound Safe Mode', 'sabri-unified-application-shell' ) . '</a></p>';
			}
			if ( $meta ) { echo '<p><strong>' . esc_html__( 'Emergency evidence', 'sabri-unified-application-shell' ) . ':</strong> <code>' . esc_html( wp_json_encode( $meta ) ) . '</code></p>'; }
			echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="margin-bottom:12px;">';
			wp_nonce_field( 'sabri_shell_emergency' );
			echo '<input type="hidden" name="action" value="sabri_shell_emergency"><input type="hidden" name="disable" value="1">';
			echo '<label>' . esc_html__( 'Reason', 'sabri-unified-application-shell' ) . ' <input type="text" name="reason" required maxlength="300"></label> ';
			echo '<label>' . esc_html__( 'Review in hours', 'sabri-unified-application-shell' ) . ' <input type="number" name="review_hours" min="1" max="168" value="24"></label> ';
			submit_button( __( 'Emergency Disable with Evidence', 'sabri-unified-application-shell' ), 'delete', 'submit', false );
			echo '</form>';
			echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
			wp_nonce_field( 'sabri_shell_emergency' );
			echo '<input type="hidden" name="action" value="sabri_shell_emergency"><input type="hidden" name="disable" value="0">';
			echo '<label>' . esc_html__( 'Re-enable note', 'sabri-unified-application-shell' ) . ' <input type="text" name="reason" maxlength="300"></label> ';
			submit_button( __( 'Run Health Gate and Re-enable', 'sabri-unified-application-shell' ), 'secondary', 'submit', false );
			echo '</form></div>';
		}
	}

	/** Hide the superseded one-click forms and replace stale plain-query Safe Mode copy. */
	public static function retire_legacy_admin_controls() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended.
		if ( 'sabri-shell' !== $page ) { return; }
		$safe_url = SafeMode::query_safe_mode_url( home_url( '/' ) );
		$script = "document.querySelectorAll('.sabri-shell-admin form').forEach(function(f){var a=f.querySelector('input[name=action]');if(a&&['sabri_shell_repair','sabri_shell_rollback','sabri_shell_emergency'].indexOf(a.value)!==-1){f.style.display='none';}});";
		if ( $safe_url ) {
			$script .= "document.querySelectorAll('.sabri-shell-admin p').forEach(function(p){if(p.textContent.indexOf('?sabri_shell_safe=1')!==-1){p.innerHTML='Safe Mode query access is nonce-bound. Use the hardened Safe Mode button above.';}});";
		}
		echo '<script id="sabri-shell-retire-legacy-admin-controls">' . $script . '</script>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- constant code plus no user data.
	}

	private static function require_admin_nonce( $action ) {
		if ( ! current_user_can( 'manage_options' ) ) { wp_die( esc_html__( 'You do not have permission to manage the Sabri Shell.', 'sabri-unified-application-shell' ) ); }
		check_admin_referer( $action );
	}

	public static function handle_admin_repair() {
		self::require_admin_nonce( 'sabri_shell_repair' );
		$actions = isset( $_POST['repair_actions'] ) && is_array( $_POST['repair_actions'] ) ? array_map( 'sanitize_key', wp_unslash( $_POST['repair_actions'] ) ) : array();
		$expected = isset( $_POST['expected_settings_version'] ) ? absint( $_POST['expected_settings_version'] ) : 0;
		$result = PlanV4Recovery::execute_repair( $actions, $expected );
		$notice = is_wp_error( $result ) ? 'hardened-repair-failed' : 'hardened-repair-success';
		wp_safe_redirect( add_query_arg( array( 'page' => 'sabri-shell', 'tab' => 'repair', 'sabri_shell_notice' => $notice ), admin_url( 'admin.php' ) ) );
		exit;
	}

	public static function handle_admin_rollback() {
		self::require_admin_nonce( 'sabri_shell_rollback' );
		$id = isset( $_POST['snapshot_id'] ) ? sanitize_text_field( wp_unslash( $_POST['snapshot_id'] ) ) : '';
		$result = '' === $id ? new \WP_Error( 'sabri_shell_snapshot_missing' ) : PlanV4Recovery::execute_rollback( $id );
		$notice = is_wp_error( $result ) ? 'hardened-rollback-failed' : 'hardened-rollback-success';
		wp_safe_redirect( add_query_arg( array( 'page' => 'sabri-shell', 'tab' => 'repair', 'sabri_shell_notice' => $notice ), admin_url( 'admin.php' ) ) );
		exit;
	}

	public static function handle_admin_emergency() {
		self::require_admin_nonce( 'sabri_shell_emergency' );
		$disable = ! empty( $_POST['disable'] );
		$reason = isset( $_POST['reason'] ) ? sanitize_text_field( wp_unslash( $_POST['reason'] ) ) : '';
		$hours = isset( $_POST['review_hours'] ) ? absint( $_POST['review_hours'] ) : 24;
		$result = SafeMode::set_emergency_disabled( $disable, $reason, $hours );
		$notice = is_wp_error( $result ) ? 'hardened-emergency-failed' : ( $disable ? 'hardened-emergency-on' : 'hardened-emergency-off' );
		wp_safe_redirect( add_query_arg( array( 'page' => 'sabri-shell', 'tab' => 'safe-mode', 'sabri_shell_notice' => $notice ), admin_url( 'admin.php' ) ) );
		exit;
	}

	/** Normalize the current request to a site-relative, lower-case path. */
	private static function current_relative_path() {
		$request = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- path-only classification.
		$path = wp_parse_url( (string) $request, PHP_URL_PATH );
		$root = wp_parse_url( home_url( '/' ), PHP_URL_PATH );
		$path = is_string( $path ) && '' !== $path ? '/' . trim( preg_replace( '#/+#', '/', $path ), '/' ) : '/';
		$root = is_string( $root ) && '' !== $root ? '/' . trim( preg_replace( '#/+#', '/', $root ), '/' ) : '/';
		$path = strtolower( '/' === $path ? '/' : untrailingslashit( $path ) );
		$root = strtolower( '/' === $root ? '/' : untrailingslashit( $root ) );
		if ( '/' !== $root && ( $path === $root || 0 === strpos( $path, $root . '/' ) ) ) { $path = substr( $path, strlen( $root ) ); $path = '' === $path ? '/' : $path; }
		return $path;
	}

	private static function path_matches( $path, $prefix ) { return $path === $prefix || 0 === strpos( $path, $prefix . '/' ); }

	public static function force_subdirectory_safe_sensitive_layout( $mode, $settings ) {
		unset( $settings );
		$path = self::current_relative_path();
		$private_tasks = array( '/account-security', '/account-passkeys', '/resolve-account', '/membership-application', '/membership-status', '/guardian-consent', '/membership-security', '/platform-system-check', '/platform-foundation/status' );
		foreach ( $private_tasks as $prefix ) { if ( self::path_matches( $path, $prefix ) ) { return Layout::MINIMAL; } }
		return $mode;
	}

	public static function register_rest_routes() {
		register_rest_route( 'sabri-shell/v1', '/system-check/export', array( 'methods' => \WP_REST_Server::READABLE, 'callback' => array( __CLASS__, 'rest_system_check_export' ), 'permission_callback' => array( __CLASS__, 'can_manage' ) ) );
	}

	public static function can_manage() { return is_user_logged_in() && current_user_can( 'manage_options' ); }

	public static function rest_system_check_export() {
		$response = rest_ensure_response( SystemCheck::export() );
		if ( is_object( $response ) && is_callable( array( $response, 'header' ) ) ) { $response->header( 'Cache-Control', 'private, no-store, max-age=0' ); $response->header( 'X-Robots-Tag', 'noindex, noarchive' ); }
		return $response;
	}

	public static function system_check( $sections ) {
		$sections = is_array( $sections ) ? $sections : array();
		$sections['future_shell_v5_eighth_hardening'] = array(
			'label' => __( 'Future Shell v5 eighth-pass hardening', 'sabri-unified-application-shell' ),
			'contract_version' => self::CONTRACT_VERSION,
			'visual_owner' => 'file-25',
			'file20_appearance_editor' => 'retired',
			'legacy_appearance_data' => 'preserved-migration-only',
			'sensitive_task_subdirectory_safe' => true,
			'system_check_export' => 'authenticated-sanitized-bounded-no-store',
			'admin_repair_owner' => 'plan-v4-recovery-dry-run-diff-snapshot-audit',
			'admin_rollback_owner' => 'plan-v4-recovery-version-and-schema-compatible',
			'emergency_lifecycle' => 'reason-actor-time-review-audit-health-cache-gated',
			'staging_accepted' => false,
			'live_deployed' => false,
		);
		return $sections;
	}
}
