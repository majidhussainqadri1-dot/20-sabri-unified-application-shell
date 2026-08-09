<?php
/**
 * Corrective hardening for Future Shell v5 after the ten-round File 20 audit.
 *
 * This layer deliberately narrows behavior. It never creates a native-domain
 * backend and it keeps File 25 as visual-system owner.
 *
 * @package SabriUnifiedApplicationShell
 */

namespace Sabri\UnifiedShell;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fail-closed corrective layer for the eighteen Future Shell enhancements.
 */
final class FutureShellV5Hardening {
	const CONTRACT_VERSION = '1.0.1';
	const RECENTS_VERSION   = 2;

	/**
	 * Register corrective hooks after FutureShellV5::register().
	 *
	 * @return void
	 */
	public static function register() {
		/* The last-known-good snapshot must contain the previous accepted state,
		 * not the just-written state that may itself be the failing change. */
		remove_action( 'update_option_' . Defaults::OPTION_NAME, array( FutureShellV5::class, 'capture_lkg' ), 30 );
		add_action( 'update_option_' . Defaults::OPTION_NAME, array( __CLASS__, 'capture_previous_lkg' ), 30, 3 );

		add_filter( 'pre_update_option_' . FutureShellV5::OPTION, array( __CLASS__, 'sanitize_future_settings' ), 999, 2 );
		add_filter( 'sabri_shell_future_feature_enabled', array( __CLASS__, 'narrow_feature_enablement' ), 999999, 3 );
		add_filter( 'sabri_shell_auto_recovery_allowed', array( __CLASS__, 'allow_current_lkg_only' ), 999999, 2 );

		/* Serve the privacy-hardened service worker before the legacy v5 handler. */
		add_action( 'template_redirect', array( __CLASS__, 'serve_hardened_service_worker' ), -20 );

		/* Replace the unconditional Future Shell footer markup with ring-aware output. */
		remove_action( 'wp_footer', array( FutureShellV5::class, 'render' ), 80 );
		add_action( 'wp_footer', array( __CLASS__, 'render' ), 80 );

		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'inject_client_context' ), 131 );
		add_action( 'init', array( __CLASS__, 'ensure_lkg_is_current' ), 5 );
		add_filter( 'sabri_shell_system_check_sections', array( __CLASS__, 'system_check' ), 61 );
	}

	/**
	 * Protected route prefixes that can never be removed by configuration.
	 *
	 * @return array<int,string>
	 */
	private static function mandatory_private_paths() {
		return array(
			'/messages',
			'/network',
			'/appointments',
			'/security',
			'/verification',
			'/account',
			'/wp-admin',
			'/wp-login.php',
			'/wp-json',
		);
	}

	/**
	 * Normalize one route-prefix value.
	 *
	 * @param mixed $path Candidate path.
	 * @return string
	 */
	private static function normalize_private_path( $path ) {
		if ( ! is_string( $path ) ) {
			return '';
		}
		$path = trim( wp_parse_url( $path, PHP_URL_PATH ) ?: '' );
		if ( '' === $path ) {
			return '';
		}
		$path = '/' . ltrim( $path, '/' );
		return untrailingslashit( $path );
	}

	/**
	 * Build a bounded, de-duplicated privacy list.
	 *
	 * @param mixed $configured Configured path list.
	 * @return array<int,string>
	 */
	private static function private_paths( $configured = array() ) {
		$paths = array_merge( self::mandatory_private_paths(), is_array( $configured ) ? $configured : array() );
		$out   = array();
		foreach ( $paths as $path ) {
			$normalized = self::normalize_private_path( $path );
			if ( '' !== $normalized && strlen( $normalized ) <= 160 ) {
				$out[ $normalized ] = $normalized;
			}
		}
		return array_values( array_slice( $out, 0, 64, true ) );
	}

	/**
	 * Sanitize Future Shell release-ring and privacy settings before persistence.
	 * Unknown/corrupt rings fail closed instead of silently becoming General.
	 *
	 * @param mixed $value     Proposed value.
	 * @param mixed $old_value Current stored value.
	 * @return array<string,mixed>
	 */
	public static function sanitize_future_settings( $value, $old_value ) {
		unset( $old_value );
		$value         = is_array( $value ) ? $value : array();
		$defaults      = FutureShellV5::defaults();
		$allowed_rings = array( 'disabled', 'internal', 'staging', 'limited', 'general' );
		$clean         = $defaults;
		$clean['contract_version'] = FutureShellV5::CONTRACT_VERSION;

		foreach ( FutureShellV5::features() as $feature => $label ) {
			unset( $label );
			$rule    = isset( $value['features'][ $feature ] ) && is_array( $value['features'][ $feature ] ) ? $value['features'][ $feature ] : array();
			$ring    = isset( $rule['ring'] ) ? sanitize_key( $rule['ring'] ) : $defaults['features'][ $feature ]['ring'];
			$percent = isset( $rule['percent'] ) ? min( 100, max( 0, absint( $rule['percent'] ) ) ) : 100;
			if ( ! in_array( $ring, $allowed_rings, true ) ) {
				$ring    = 'disabled';
				$percent = 0;
			}
			$clean['features'][ $feature ] = array( 'ring' => $ring, 'percent' => $percent );
		}

		$clean['auto_recovery']          = isset( $value['auto_recovery'] ) ? (bool) $value['auto_recovery'] : true;
		$clean['private_path_fragments'] = self::private_paths( isset( $value['private_path_fragments'] ) ? $value['private_path_fragments'] : array() );
		return $clean;
	}

	/**
	 * Re-evaluate the ring at the last filter priority and only narrow the result.
	 * This prevents invalid rings or an earlier broadening filter from turning a
	 * disabled/internal/staging/limited feature into a public General feature.
	 *
	 * @param bool  $enabled Earlier decision.
	 * @param string $feature Feature id.
	 * @param array  $rule Ring rule.
	 * @return bool
	 */
	public static function narrow_feature_enablement( $enabled, $feature, $rule ) {
		$feature = sanitize_key( $feature );
		if ( ! isset( FutureShellV5::features()[ $feature ] ) || ! is_array( $rule ) ) {
			return false;
		}

		$ring    = isset( $rule['ring'] ) ? sanitize_key( $rule['ring'] ) : '';
		$percent = isset( $rule['percent'] ) ? min( 100, max( 0, absint( $rule['percent'] ) ) ) : 0;
		$allowed = false;

		switch ( $ring ) {
			case 'disabled':
				$allowed = false;
				break;
			case 'internal':
				$allowed = is_user_logged_in() && current_user_can( 'manage_options' );
				break;
			case 'staging':
				$environment = function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production';
				$allowed = in_array( $environment, array( 'local', 'development', 'staging' ), true );
				break;
			case 'limited':
				if ( is_user_logged_in() && $percent > 0 ) {
					$bucket  = absint( crc32( (string) get_current_user_id() . '|' . $feature ) ) % 100;
					$allowed = $bucket < $percent;
				}
				break;
			case 'general':
				$allowed = true;
				break;
			default:
				$allowed = false;
				break;
		}

		return (bool) $enabled && $allowed;
	}

	/**
	 * Snapshot the value that was known before an update.
	 *
	 * @param mixed  $old_value Previous settings.
	 * @param mixed  $value     New settings.
	 * @param string $option    Option name.
	 * @return void
	 */
	public static function capture_previous_lkg( $old_value, $value, $option ) {
		unset( $value, $option );
		if ( ! FutureShellV5::feature_enabled( 'last_known_good' ) || ! is_array( $old_value ) ) {
			return;
		}
		$schema = isset( $old_value['schema_version'] ) ? absint( $old_value['schema_version'] ) : 0;
		if ( Defaults::SCHEMA_VERSION !== $schema ) {
			return;
		}
		FutureShellV5::capture_lkg( array(), $old_value );
	}

	/**
	 * Accept automatic recovery only from the current File 20 + settings schema.
	 *
	 * @param bool  $allowed Existing allowance.
	 * @param mixed $context Failure context.
	 * @return bool
	 */
	public static function allow_current_lkg_only( $allowed, $context ) {
		unset( $context );
		if ( ! $allowed ) {
			return false;
		}
		$snapshot = get_option( FutureShellV5::LKG_OPTION, array() );
		if ( ! is_array( $snapshot ) || (string) ( $snapshot['plugin_version'] ?? '' ) !== SABRI_SHELL_VERSION ) {
			return false;
		}
		$settings = isset( $snapshot['settings'] ) && is_array( $snapshot['settings'] ) ? $snapshot['settings'] : array();
		return isset( $settings['schema_version'] ) && Defaults::SCHEMA_VERSION === absint( $settings['schema_version'] );
	}

	/**
	 * Rotate a stale LKG after migrations complete so cross-version restoration
	 * cannot inject an incompatible settings schema.
	 *
	 * @return void
	 */
	public static function ensure_lkg_is_current() {
		if ( ! FutureShellV5::feature_enabled( 'last_known_good' ) ) {
			return;
		}
		$current  = Settings::get();
		$snapshot = get_option( FutureShellV5::LKG_OPTION, array() );
		$valid    = is_array( $snapshot )
			&& (string) ( $snapshot['plugin_version'] ?? '' ) === SABRI_SHELL_VERSION
			&& isset( $snapshot['settings']['schema_version'] )
			&& Defaults::SCHEMA_VERSION === absint( $snapshot['settings']['schema_version'] );
		if ( ! $valid && is_array( $current ) && isset( $current['schema_version'] ) && Defaults::SCHEMA_VERSION === absint( $current['schema_version'] ) ) {
			FutureShellV5::capture_lkg( array(), $current );
		}
	}

	/**
	 * Return the WordPress home path used as the least-privilege SW scope.
	 *
	 * @return string
	 */
	private static function scope_path() {
		$path = wp_parse_url( home_url( '/' ), PHP_URL_PATH );
		$path = is_string( $path ) && '' !== $path ? $path : '/';
		return trailingslashit( '/' . ltrim( $path, '/' ) );
	}

	/**
	 * Current page is safe for local Recent/Resume capture.
	 * Conservative failure means "do not remember".
	 *
	 * @return bool
	 */
	private static function current_route_public() {
		if ( is_admin() || wp_doing_ajax() || Layout::MINIMAL === Layout::current_mode() || is_404() || is_preview() || ! empty( $_GET ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only privacy classification.
			return false;
		}
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- URL path only.
		$path        = wp_parse_url( $request_uri, PHP_URL_PATH );
		$path        = is_string( $path ) ? '/' . ltrim( $path, '/' ) : '/';
		foreach ( self::private_paths( FutureShellV5::settings()['private_path_fragments'] ?? array() ) as $private ) {
			if ( $path === $private || 0 === strpos( $path, trailingslashit( $private ) ) ) {
				return false;
			}
		}
		if ( is_singular() ) {
			$post = get_queried_object();
			if ( ! $post instanceof \WP_Post || 'publish' !== get_post_status( $post ) ) {
				return false;
			}
			$type = get_post_type_object( $post->post_type );
			return $type && ! empty( $type->publicly_queryable );
		}
		return is_front_page() || is_home() || is_archive();
	}

	/**
	 * Add privacy/public-route and SW-scope facts after the original localization.
	 *
	 * @return void
	 */
	public static function inject_client_context() {
		if ( ! wp_script_is( 'sabri-shell-future-v5', 'enqueued' ) ) {
			return;
		}
		$settings = FutureShellV5::settings();
		$payload  = array(
			'hardeningVersion'  => self::CONTRACT_VERSION,
			'currentRoutePublic'=> self::current_route_public(),
			'swScope'            => self::scope_path(),
			'privatePaths'       => self::private_paths( $settings['private_path_fragments'] ?? array() ),
			'recentsVersion'     => self::RECENTS_VERSION,
		);
		$code = 'window.SabriShellFutureV5=Object.assign({},window.SabriShellFutureV5||{},' . wp_json_encode( $payload ) . ');';
		wp_add_inline_script( 'sabri-shell-future-v5', $code, 'after' );
	}

	/**
	 * Serve a scope-bounded service worker whose private-path list comes from the
	 * same server-side settings used by Recent/Resume and prefetch.
	 *
	 * @return void
	 */
	public static function serve_hardened_service_worker() {
		if ( ! get_query_var( FutureShellV5::SW_QUERY ) || ! FutureShellV5::feature_enabled( 'pwa_shell' ) ) {
			return;
		}
		$settings      = FutureShellV5::settings();
		$private_paths = self::private_paths( $settings['private_path_fragments'] ?? array() );
		$scope         = self::scope_path();
		$offline       = '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Offline</title></head><body><main><h1>Sabri Homeopathy</h1><p>You are offline. Reconnect to continue.</p></main></body></html>';
		$control_url   = home_url( '/sabri-shell-manifest.webmanifest' );
		$plugin_path   = wp_parse_url( SABRI_SHELL_URL, PHP_URL_PATH );
		$plugin_path   = is_string( $plugin_path ) ? trailingslashit( $plugin_path ) : '/wp-content/plugins/sabri-unified-application-shell/';

		nocache_headers();
		header( 'Content-Type: application/javascript; charset=utf-8' );
		header( 'Cache-Control: no-store, max-age=0' );
		header( 'Service-Worker-Allowed: ' . $scope );

		$source  = "const CACHE='sabri-shell-v141-static';\n";
		$source .= 'const OFFLINE=' . wp_json_encode( $offline ) . ";\n";
		$source .= 'const PRIVATE=' . wp_json_encode( $private_paths ) . ";\n";
		$source .= 'const CONTROL=' . wp_json_encode( $control_url ) . ";\n";
		$source .= 'const PLUGIN_PATH=' . wp_json_encode( $plugin_path ) . ";\n";
		$source .= "let lastControlCheck=0;\n";
		$source .= "const privatePath=p=>PRIVATE.some(x=>p===x||p.startsWith(x.endsWith('/')?x:x+'/'));\n";
		$source .= "async function controlAlive(){const now=Date.now();if(now-lastControlCheck<60000)return true;try{const r=await fetch(CONTROL,{cache:'no-store',credentials:'same-origin'});if(!r.ok){await self.registration.unregister();for(const k of await caches.keys()){if(k.startsWith('sabri-shell-'))await caches.delete(k);}return false;}lastControlCheck=now;return true;}catch(e){return true;}}\n";
		$source .= "self.addEventListener('install',e=>self.skipWaiting());\n";
		$source .= "self.addEventListener('activate',e=>e.waitUntil((async()=>{for(const k of await caches.keys()){if(k!==CACHE&&k.startsWith('sabri-shell-'))await caches.delete(k);}await self.clients.claim();})()));\n";
		$source .= "self.addEventListener('fetch',e=>{const r=e.request;if(r.method!=='GET')return;const u=new URL(r.url);if(u.origin!==self.location.origin||privatePath(u.pathname))return;if(r.mode==='navigate'){e.respondWith((async()=>{if(!await controlAlive())return fetch(r);try{return await fetch(r,{cache:'no-store'});}catch(err){return new Response(OFFLINE,{status:503,headers:{'Content-Type':'text/html; charset=utf-8','Cache-Control':'no-store'}});}})());return;}if(u.pathname.startsWith(PLUGIN_PATH)){e.respondWith(caches.open(CACHE).then(async c=>{const hit=await c.match(r);if(hit)return hit;const res=await fetch(r);if(res.ok&&res.type==='basic')await c.put(r,res.clone());return res;}));}});\n";
		echo $source; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- generated JavaScript with JSON-encoded values only.
		exit;
	}

	/**
	 * Ring-aware footer markup. Disabled features produce no matching control.
	 *
	 * @return void
	 */
	public static function render() {
		if ( is_admin() || ! in_array( Layout::current_mode(), array( Layout::TWO, Layout::THREE ), true ) ) {
			return;
		}
		$command = FutureShellV5::feature_enabled( 'command_palette' );
		$offline = FutureShellV5::feature_enabled( 'offline_mode' );
		$recent  = FutureShellV5::feature_enabled( 'recent_resume' );
		$a11y    = FutureShellV5::feature_enabled( 'accessibility_center' );
		$lang    = FutureShellV5::feature_enabled( 'language_direction' );
		$data    = FutureShellV5::feature_enabled( 'data_saver' );

		if ( $offline ) {
			echo '<div id="sabri-shell-connectivity" class="sabri-shell-connectivity" role="status" aria-live="polite" hidden></div>';
		}
		if ( $command ) {
			echo '<dialog id="sabri-shell-command-palette" class="sabri-shell-command-palette" aria-labelledby="sabri-shell-command-title"><form method="dialog"><button class="sabri-shell-dialog-close" value="close" aria-label="' . esc_attr__( 'Close', 'sabri-unified-application-shell' ) . '">&times;</button></form><h2 id="sabri-shell-command-title">' . esc_html__( 'Quick Command', 'sabri-unified-application-shell' ) . '</h2><label class="screen-reader-text" for="sabri-shell-command-input">' . esc_html__( 'Search commands', 'sabri-unified-application-shell' ) . '</label><input id="sabri-shell-command-input" type="search" autocomplete="off" placeholder="' . esc_attr__( 'Search or type a command', 'sabri-unified-application-shell' ) . '"><div id="sabri-shell-command-results" aria-live="polite"></div></dialog>';
		}
		if ( $a11y || $lang ) {
			echo '<dialog id="sabri-shell-accessibility-center" class="sabri-shell-accessibility-center" aria-labelledby="sabri-shell-a11y-title"><form method="dialog"><button class="sabri-shell-dialog-close" value="close" aria-label="' . esc_attr__( 'Close', 'sabri-unified-application-shell' ) . '">&times;</button></form><h2 id="sabri-shell-a11y-title">' . esc_html__( 'Accessibility and Reading Preferences', 'sabri-unified-application-shell' ) . '</h2>';
			if ( $a11y ) {
				echo '<div class="sabri-shell-pref-grid">';
				foreach ( array( 'font' => __( 'Larger text', 'sabri-unified-application-shell' ), 'contrast' => __( 'High contrast', 'sabri-unified-application-shell' ), 'focus' => __( 'Strong focus', 'sabri-unified-application-shell' ), 'spacing' => __( 'Comfort spacing', 'sabri-unified-application-shell' ), 'motion' => __( 'Reduce motion', 'sabri-unified-application-shell' ) ) as $key => $label ) {
					echo '<button type="button" data-sabri-pref="' . esc_attr( $key ) . '" aria-pressed="false">' . esc_html( $label ) . '</button>';
				}
				if ( $data ) {
					echo '<button type="button" data-sabri-pref="data" aria-pressed="false">' . esc_html__( 'Data saver', 'sabri-unified-application-shell' ) . '</button>';
				}
				echo '</div>';
			}
			if ( $lang ) {
				echo '<div class="sabri-shell-language-quick"><h3>' . esc_html__( 'Language', 'sabri-unified-application-shell' ) . '</h3>';
				$switcher = Integrations::language_switcher();
				if ( $switcher ) {
					echo wp_kses_post( $switcher );
				} else {
					echo '<p>' . esc_html__( 'Language provider is not currently available.', 'sabri-unified-application-shell' ) . '</p>';
				}
				echo '</div>';
			}
			echo '</dialog>';
		}
		if ( $recent ) {
			echo '<dialog id="sabri-shell-recent-center" class="sabri-shell-recent-center" aria-labelledby="sabri-shell-recent-title"><form method="dialog"><button class="sabri-shell-dialog-close" value="close" aria-label="' . esc_attr__( 'Close', 'sabri-unified-application-shell' ) . '">&times;</button></form><h2 id="sabri-shell-recent-title">' . esc_html__( 'Recent and Resume', 'sabri-unified-application-shell' ) . '</h2><div id="sabri-shell-recent-list"></div><button type="button" data-sabri-clear-recents>' . esc_html__( 'Clear local history', 'sabri-unified-application-shell' ) . '</button></dialog>';
		}
		if ( FutureShellV5::feature_enabled( 'split_workspace' ) && apply_filters( 'sabri_shell_split_workspace_available', false ) ) {
			echo '<aside id="sabri-shell-split-workspace" class="sabri-shell-split-workspace" aria-label="' . esc_attr__( 'Secondary workspace', 'sabri-unified-application-shell' ) . '" hidden><button type="button" data-sabri-split-close aria-label="' . esc_attr__( 'Close secondary workspace', 'sabri-unified-application-shell' ) . '">&times;</button>';
			do_action( 'sabri_shell_split_workspace_render' );
			echo '</aside>';
		}
	}

	/**
	 * Add corrective evidence without turning staging/live state green.
	 *
	 * @param mixed $sections Existing sections.
	 * @return array<string,mixed>
	 */
	public static function system_check( $sections ) {
		$sections = is_array( $sections ) ? $sections : array();
		$sections['future_shell_v5_hardening'] = array(
			'label'               => __( 'Future Shell v5 corrective hardening', 'sabri-unified-application-shell' ),
			'contract_version'    => self::CONTRACT_VERSION,
			'private_path_count'  => count( self::private_paths( FutureShellV5::settings()['private_path_fragments'] ?? array() ) ),
			'lkg_current_version' => self::allow_current_lkg_only( true, array() ),
			'route_rememberable'  => self::current_route_public(),
			'staging_accepted'    => false,
			'live_deployed'       => false,
		);
		return $sections;
	}

	/**
	 * Server-side deactivation cleanup. Existing browsers also self-unregister
	 * the hardened worker on the next online navigation if the control manifest
	 * is no longer served.
	 *
	 * @return void
	 */
	public static function deactivate() {
		delete_option( 'sabri_shell_future_rewrite_version' );
		delete_option( 'sabri_shell_flush_rewrite_rules' );
		global $wp_rewrite;
		if ( is_object( $wp_rewrite ) && isset( $wp_rewrite->extra_rules_top ) && is_array( $wp_rewrite->extra_rules_top ) ) {
			unset( $wp_rewrite->extra_rules_top['^sabri-shell-sw\\.js$'], $wp_rewrite->extra_rules_top['^sabri-shell-manifest\\.webmanifest$'] );
		}
		if ( function_exists( 'flush_rewrite_rules' ) ) {
			flush_rewrite_rules( false );
		}
	}
}
