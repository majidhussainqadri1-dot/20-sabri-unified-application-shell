<?php
/**
 * Second independent corrective hardening pass for Future Shell v5.
 *
 * This layer addresses fresh post-1.4.1 findings without transferring any
 * native-domain ownership into File 20.
 *
 * @package SabriUnifiedApplicationShell
 */

namespace Sabri\UnifiedShell;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Fresh adversarial corrections discovered after the first ten-round audit. */
final class FutureShellV5SecondHardening {
	const CONTRACT_VERSION = '1.0.2';
	const MAX_CIRCUITS      = 64;

	/** Register second-pass controls after all first-pass Future Shell layers. */
	public static function register() {
		/* Dynamic privacy-provider paths must be evaluated, not silently persisted. */
		remove_action( 'init', array( FutureShellV5ClientContext::class, 'ensure_private_path_policy' ), 6 );

		/* Preserve omitted values before the first-pass sanitizer validates them. */
		add_filter( 'pre_update_option_' . FutureShellV5::OPTION, array( __CLASS__, 'preserve_partial_future_settings' ), 998, 2 );

		/* Own the final virtual-asset response so subdirectory scope and disable
		 * lifecycle are deterministic. The first-pass handlers remain fallbacks. */
		add_action( 'template_redirect', array( __CLASS__, 'serve_virtual_assets' ), -30 );
		remove_action( 'wp_head', array( FutureShellV5::class, 'head_links' ), 4 );
		add_action( 'wp_head', array( __CLASS__, 'head_links' ), 4 );

		/* Publish the final scope-aware privacy facts before the base browser code. */
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_client_guard' ), 133 );

		/* Circuit state is bounded operational metadata, not an unbounded log. */
		add_action( 'sabri_shell_module_failure', array( __CLASS__, 'bound_circuit_state' ), 15, 2 );
		add_action( 'sabri_shell_module_success', array( __CLASS__, 'bound_circuit_state' ), 15, 1 );
		add_filter( 'sabri_shell_system_check_sections', array( __CLASS__, 'system_check' ), 63 );

		/* An immersive native reader/player must not receive a second workspace. */
		add_filter( 'sabri_shell_split_workspace_available', array( __CLASS__, 'split_workspace_allowed' ), 999999 );
	}

	/**
	 * Preserve partial updates instead of resetting omitted feature rules to GA.
	 * Malformed explicit rules fail closed before the first-pass sanitizer runs.
	 *
	 * @param mixed $value Proposed Future Shell settings.
	 * @param mixed $old_value Existing Future Shell settings.
	 * @return mixed
	 */
	public static function preserve_partial_future_settings( $value, $old_value ) {
		if ( ! is_array( $value ) ) {
			return $value;
		}
		$old_value = is_array( $old_value ) ? $old_value : array();

		foreach ( array( 'auto_recovery', 'private_path_fragments' ) as $key ) {
			if ( ! array_key_exists( $key, $value ) && array_key_exists( $key, $old_value ) ) {
				$value[ $key ] = $old_value[ $key ];
			}
		}

		$old_features = isset( $old_value['features'] ) && is_array( $old_value['features'] ) ? $old_value['features'] : array();
		if ( ! array_key_exists( 'features', $value ) ) {
			$value['features'] = $old_features;
			return $value;
		}
		if ( ! is_array( $value['features'] ) ) {
			$value['features'] = array();
			foreach ( FutureShellV5::features() as $feature => $label ) {
				unset( $label );
				$value['features'][ $feature ] = array( 'ring' => 'disabled', 'percent' => 0 );
			}
			return $value;
		}

		foreach ( FutureShellV5::features() as $feature => $label ) {
			unset( $label );
			if ( ! array_key_exists( $feature, $value['features'] ) ) {
				if ( isset( $old_features[ $feature ] ) && is_array( $old_features[ $feature ] ) ) {
					$value['features'][ $feature ] = $old_features[ $feature ];
				}
				continue;
			}
			if ( ! is_array( $value['features'][ $feature ] ) ) {
				$value['features'][ $feature ] = array( 'ring' => 'disabled', 'percent' => 0 );
				continue;
			}
			if ( isset( $old_features[ $feature ] ) && is_array( $old_features[ $feature ] ) ) {
				$value['features'][ $feature ] = array_replace( $old_features[ $feature ], $value['features'][ $feature ] );
			}
		}
		return $value;
	}

	/** Canonical protected prefixes, expressed relative to the WordPress home scope. */
	private static function baseline_private_paths() {
		return array(
			'/messages', '/network', '/smail', '/appointments', '/security', '/verification',
			'/doctor-onboarding', '/doctor-verification', '/account', '/notifications', '/settings',
			'/login', '/register', '/signup', '/logout', '/forgot-password', '/reset-password',
			'/complete-profile', '/publishing-dashboard', '/newsroom', '/notes',
			'/marketplace/dashboard', '/marketplace/deal', '/wp-admin', '/wp-login.php', '/wp-json',
		);
	}

	/** Return the least-privilege WordPress home scope. */
	private static function scope_path() {
		$path = wp_parse_url( home_url( '/' ), PHP_URL_PATH );
		$path = is_string( $path ) && '' !== $path ? $path : '/';
		return trailingslashit( '/' . ltrim( $path, '/' ) );
	}

	/** Normalize a path prefix. */
	private static function normalize_path( $path ) {
		if ( ! is_string( $path ) ) {
			return '';
		}
		$path = wp_parse_url( trim( $path ), PHP_URL_PATH );
		if ( ! is_string( $path ) || '' === $path ) {
			return '';
		}
		$path = '/' . ltrim( $path, '/' );
		$path = untrailingslashit( $path );
		return strtolower( $path );
	}

	/**
	 * Build the final protected list from baseline, explicit settings and current
	 * provider contracts. External provider state is never written into options.
	 */
	private static function relative_private_paths() {
		$settings = FutureShellV5::settings();
		$external = apply_filters( 'sabri_shell_future_private_path_fragments', array() );
		$paths = array_merge(
			self::baseline_private_paths(),
			isset( $settings['private_path_fragments'] ) && is_array( $settings['private_path_fragments'] ) ? $settings['private_path_fragments'] : array(),
			is_array( $external ) ? $external : array()
		);
		$out = array();
		foreach ( $paths as $path ) {
			$path = self::normalize_path( $path );
			if ( '' !== $path && strlen( $path ) <= 200 ) {
				$out[ $path ] = $path;
			}
		}
		return array_values( array_slice( $out, 0, 96, true ) );
	}

	/** Convert protected prefixes to actual same-origin paths under home scope. */
	private static function scoped_private_paths() {
		$scope_root = untrailingslashit( self::scope_path() );
		$scope_root = '/' === $scope_root ? '' : strtolower( $scope_root );
		$out = array();
		foreach ( self::relative_private_paths() as $path ) {
			$scoped = $path;
			if ( '' !== $scope_root && $path !== $scope_root && 0 !== strpos( $path, $scope_root . '/' ) ) {
				$scoped = $scope_root . $path;
			}
			$out[ $scoped ] = $scoped;
		}
		return array_values( $out );
	}

	/** Conservative public-route classification with subdirectory-aware prefixes. */
	private static function current_route_public() {
		if ( is_admin() || wp_doing_ajax() || Layout::MINIMAL === Layout::current_mode() || is_404() || is_preview() || ! empty( $_GET ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only privacy classification.
			return false;
		}
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- path only.
		$path = self::normalize_path( $request_uri );
		foreach ( self::scoped_private_paths() as $private ) {
			if ( $path === $private || 0 === strpos( $path, $private . '/' ) ) {
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

	/** File 25-owned visual tokens, with the existing continuity fallback contract. */
	private static function visual_tokens() {
		return CentralPlanContract::visual_tokens();
	}

	/** Build a File-25-aware PWA manifest without creating a visual owner in File 20. */
	private static function manifest() {
		$tokens = self::visual_tokens();
		$icons = array();
		$site_icon = get_option( 'site_icon' );
		if ( $site_icon ) {
			foreach ( array( 192, 512 ) as $size ) {
				$url = wp_get_attachment_image_url( $site_icon, array( $size, $size ) );
				if ( $url ) {
					$icons[] = array( 'src' => $url, 'sizes' => $size . 'x' . $size, 'type' => 'image/png', 'purpose' => 'any maskable' );
				}
			}
		}
		return array(
			'name'             => get_bloginfo( 'name' ) ?: 'Sabri Social Homeopathy Platform',
			'short_name'       => 'Sabri Homeopathy',
			'start_url'        => home_url( '/' ),
			'scope'            => home_url( '/' ),
			'display'          => 'standalone',
			'background_color' => isset( $tokens['background'] ) ? $tokens['background'] : '#ffffff',
			'theme_color'      => isset( $tokens['primary_color'] ) ? $tokens['primary_color'] : '#16803a',
			'lang'             => get_bloginfo( 'language' ) ?: 'en-US',
			'icons'            => $icons,
		);
	}

	/**
	 * Final PWA virtual-asset handler. A disabled PWA returns 410 instead of
	 * falling through to a 200 HTML page, allowing installed workers to retire.
	 */
	public static function serve_virtual_assets() {
		$is_sw       = (bool) get_query_var( FutureShellV5::SW_QUERY );
		$is_manifest = (bool) get_query_var( FutureShellV5::MANIFEST_QUERY );
		if ( ! $is_sw && ! $is_manifest ) {
			return;
		}
		if ( ! FutureShellV5::feature_enabled( 'pwa_shell' ) ) {
			status_header( 410 );
			nocache_headers();
			header( 'Cache-Control: no-store, max-age=0' );
			header( 'Content-Type: text/plain; charset=utf-8' );
			echo 'Sabri Shell PWA is disabled.'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- constant text.
			exit;
		}
		if ( $is_manifest ) {
			nocache_headers();
			header( 'Cache-Control: no-store, max-age=0' );
			header( 'Content-Type: application/manifest+json; charset=utf-8' );
			echo wp_json_encode( self::manifest(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON response.
			exit;
		}

		$scope         = self::scope_path();
		$private_paths = self::scoped_private_paths();
		$offline       = '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Offline</title></head><body><main><h1>Sabri Homeopathy</h1><p>You are offline. Reconnect to continue.</p></main></body></html>';
		$control_url   = home_url( '/sabri-shell-manifest.webmanifest' );
		$plugin_path   = wp_parse_url( SABRI_SHELL_URL, PHP_URL_PATH );
		$plugin_path   = is_string( $plugin_path ) ? trailingslashit( $plugin_path ) : trailingslashit( $scope . 'wp-content/plugins/sabri-unified-application-shell' );
		$cache_version = strtolower( preg_replace( '/[^a-z0-9]+/i', '', SABRI_SHELL_VERSION ) );

		nocache_headers();
		header( 'Content-Type: application/javascript; charset=utf-8' );
		header( 'Cache-Control: no-store, max-age=0' );
		header( 'Service-Worker-Allowed: ' . $scope );

		$source  = "const CACHE='sabri-shell-v" . $cache_version . "-static';\n";
		$source .= 'const OFFLINE=' . wp_json_encode( $offline ) . ";\n";
		$source .= 'const PRIVATE=' . wp_json_encode( $private_paths ) . ";\n";
		$source .= 'const CONTROL=' . wp_json_encode( $control_url ) . ";\n";
		$source .= 'const PLUGIN_PATH=' . wp_json_encode( $plugin_path ) . ";\n";
		$source .= "let lastControlCheck=0;\n";
		$source .= "const privatePath=p=>{p=String(p||'/').toLowerCase().replace(/\\/+$/,'')||'/';return PRIVATE.some(x=>p===x||p.startsWith(x+'/'));};\n";
		$source .= "async function controlAlive(){const now=Date.now();if(now-lastControlCheck<60000)return true;try{const r=await fetch(CONTROL,{cache:'no-store',credentials:'same-origin'});if(!r.ok){await self.registration.unregister();for(const k of await caches.keys()){if(k.startsWith('sabri-shell-'))await caches.delete(k);}return false;}lastControlCheck=now;return true;}catch(e){return true;}}\n";
		$source .= "self.addEventListener('install',e=>self.skipWaiting());\n";
		$source .= "self.addEventListener('activate',e=>e.waitUntil((async()=>{for(const k of await caches.keys()){if(k!==CACHE&&k.startsWith('sabri-shell-'))await caches.delete(k);}await self.clients.claim();})()));\n";
		$source .= "self.addEventListener('fetch',e=>{const r=e.request;if(r.method!=='GET')return;const u=new URL(r.url);if(u.origin!==self.location.origin||privatePath(u.pathname))return;if(r.mode==='navigate'){e.respondWith((async()=>{if(!await controlAlive())return fetch(r);try{return await fetch(r,{cache:'no-store'});}catch(err){return new Response(OFFLINE,{status:503,headers:{'Content-Type':'text/html; charset=utf-8','Cache-Control':'no-store'}});}})());return;}if(u.pathname.startsWith(PLUGIN_PATH)){e.respondWith(caches.open(CACHE).then(async c=>{const hit=await c.match(r);if(hit)return hit;const res=await fetch(r);if(res.ok&&res.type==='basic')await c.put(r,res.clone());return res;}));}});\n";
		echo $source; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- generated JS with JSON-encoded values only.
		exit;
	}

	/** Manifest link and theme color derive from File 25's validated token contract. */
	public static function head_links() {
		if ( is_admin() || Layout::MINIMAL === Layout::current_mode() || ! FutureShellV5::feature_enabled( 'pwa_shell' ) ) {
			return;
		}
		$tokens = self::visual_tokens();
		$theme  = isset( $tokens['primary_color'] ) ? $tokens['primary_color'] : '#16803a';
		echo '<link rel="manifest" href="' . esc_url( home_url( '/sabri-shell-manifest.webmanifest' ) ) . '">';
		echo '<meta name="theme-color" content="' . esc_attr( $theme ) . '">';
	}

	/** Final pre-boot privacy context and editable-shortcut guard dependency. */
	public static function enqueue_client_guard() {
		if ( ! wp_script_is( 'sabri-shell-future-v5', 'enqueued' ) ) {
			return;
		}
		$payload = array(
			'secondHardeningVersion' => self::CONTRACT_VERSION,
			'currentRoutePublic'     => self::current_route_public(),
			'swScope'                => self::scope_path(),
			'privatePaths'           => self::scoped_private_paths(),
		);
		wp_add_inline_script(
			'sabri-shell-future-v5',
			'window.SabriShellFutureV5SecondHardening=' . wp_json_encode( $payload ) . ';window.SabriShellFutureV5=Object.assign({},window.SabriShellFutureV5||{},window.SabriShellFutureV5SecondHardening);',
			'before'
		);

		wp_register_script(
			'sabri-shell-future-v5-editable-guard',
			SABRI_SHELL_URL . 'assets/js/future-shell-v5-editable-guard.js',
			array(),
			SABRI_SHELL_VERSION,
			true
		);
		wp_enqueue_script( 'sabri-shell-future-v5-editable-guard' );
		$wp_scripts = wp_scripts();
		if ( isset( $wp_scripts->registered['sabri-shell-future-v5'] ) ) {
			$deps = (array) $wp_scripts->registered['sabri-shell-future-v5']->deps;
			if ( ! in_array( 'sabri-shell-future-v5-editable-guard', $deps, true ) ) {
				$deps[] = 'sabri-shell-future-v5-editable-guard';
				$wp_scripts->registered['sabri-shell-future-v5']->deps = $deps;
			}
		}
	}

	/** Keep circuit metadata bounded and discard expired or malformed states. */
	public static function bound_circuit_state( $module = '', $context = array() ) {
		unset( $module, $context );
		$all = get_option( FutureShellV5::CIRCUIT_OPTION, array() );
		if ( ! is_array( $all ) ) {
			update_option( FutureShellV5::CIRCUIT_OPTION, array(), false );
			return;
		}
		$now = time();
		$clean = array();
		foreach ( $all as $key => $state ) {
			$key = sanitize_key( $key );
			if ( '' === $key || ! is_array( $state ) ) {
				continue;
			}
			$opened = absint( $state['opened_at'] ?? 0 );
			$last   = absint( $state['last_failure_at'] ?? 0 );
			if ( $opened && ( $now - $opened ) > FutureShellV5::CIRCUIT_COOLDOWN ) {
				continue;
			}
			if ( ! $opened && $last && ( $now - $last ) > FutureShellV5::CIRCUIT_COOLDOWN ) {
				continue;
			}
			$state['failures']        = min( FutureShellV5::CIRCUIT_THRESHOLD, absint( $state['failures'] ?? 0 ) );
			$state['opened_at']       = $opened;
			$state['last_failure_at'] = $last;
			$clean[ $key ] = $state;
		}
		uasort( $clean, static function ( $a, $b ) {
			$at = max( absint( $a['opened_at'] ?? 0 ), absint( $a['last_failure_at'] ?? 0 ) );
			$bt = max( absint( $b['opened_at'] ?? 0 ), absint( $b['last_failure_at'] ?? 0 ) );
			return $bt <=> $at;
		} );
		$clean = array_slice( $clean, 0, self::MAX_CIRCUITS, true );
		if ( $clean !== $all ) {
			update_option( FutureShellV5::CIRCUIT_OPTION, $clean, false );
		}
	}

	/** Correct stale circuit health and expose second-pass evidence. */
	public static function system_check( $sections ) {
		$sections = is_array( $sections ) ? $sections : array();
		self::bound_circuit_state();
		$all = get_option( FutureShellV5::CIRCUIT_OPTION, array() );
		$all = is_array( $all ) ? $all : array();
		$open = array();
		foreach ( $all as $module => $state ) {
			if ( is_array( $state ) && ! empty( $state['opened_at'] ) && ( time() - absint( $state['opened_at'] ) ) <= FutureShellV5::CIRCUIT_COOLDOWN ) {
				$open[] = sanitize_key( $module );
			}
		}
		if ( isset( $sections['future_shell_v5'] ) && is_array( $sections['future_shell_v5'] ) ) {
			$sections['future_shell_v5']['open_circuits'] = $open;
			$sections['future_shell_v5']['circuit_state_count'] = count( $all );
		}
		$sections['future_shell_v5_second_hardening'] = array(
			'label'                  => __( 'Future Shell v5 second-pass hardening', 'sabri-unified-application-shell' ),
			'contract_version'       => self::CONTRACT_VERSION,
			'private_path_count'     => count( self::scoped_private_paths() ),
			'home_scope'             => self::scope_path(),
			'route_rememberable'     => self::current_route_public(),
			'circuit_state_count'    => count( $all ),
			'open_circuits'          => $open,
			'staging_accepted'       => false,
			'live_deployed'          => false,
		);
		return $sections;
	}

	/** Split Workspace is a desktop application aid, not an immersive overlay. */
	public static function split_workspace_allowed( $available ) {
		if ( Layout::MINIMAL === Layout::current_mode() || Layout::IMMERSIVE === Layout::current_mode() ) {
			return false;
		}
		return (bool) $available;
	}
}
