<?php
/**
 * Third independent corrective hardening pass for Future Shell v5.
 *
 * This layer reconciles the current 1.4.2 shell with later-approved File 00,
 * File 01 and File 02 contracts without taking ownership of their native data.
 *
 * @package SabriUnifiedApplicationShell
 */

namespace Sabri\UnifiedShell;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Fresh adversarial corrections discovered after the second ten-round audit. */
final class FutureShellV5ThirdHardening {
	const CONTRACT_VERSION  = '1.0.3';
	const MAX_PRIVATE_PATHS = 128;
	const BRAND_FALLBACK    = '#087a4e';

	/** Register final third-pass controls. */
	public static function register() {
		/* One final virtual-asset owner: older handlers are no longer runtime fallbacks. */
		remove_action( 'template_redirect', array( FutureShellV5::class, 'serve_virtual_assets' ), 0 );
		remove_action( 'template_redirect', array( FutureShellV5Hardening::class, 'serve_hardened_service_worker' ), -20 );
		remove_action( 'template_redirect', array( FutureShellV5SecondHardening::class, 'serve_virtual_assets' ), -30 );
		add_action( 'template_redirect', array( __CLASS__, 'serve_virtual_assets' ), -40 );

		/* Final privacy facts must be present before browser Future Shell code executes. */
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_final_context' ), 134 );

		/* Latest auth/membership/foundation tasks remain Minimal and never enter public history. */
		add_filter( 'sabri_shell_layout_mode', array( __CLASS__, 'force_sensitive_task_layout' ), 1000, 2 );
		add_filter( 'sabri_shell_private_request', array( __CLASS__, 'force_private_headers' ), 1000, 2 );

		/* Reconcile the latest File 01/File 02 scope descriptions in the public contract registry. */
		add_filter( 'sabri_shell_contract_registry', array( __CLASS__, 'harmonize_latest_contracts' ), 1000 );

		/* The latest governing brand fallback is Sabri Green when File 25 is unavailable. */
		remove_action( 'wp_head', array( FutureShellV5SecondHardening::class, 'head_links' ), 4 );
		add_action( 'wp_head', array( __CLASS__, 'head_links' ), 4 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'apply_continuity_brand_fallback' ), 135 );

		add_filter( 'sabri_shell_system_check_sections', array( __CLASS__, 'system_check' ), 70 );
	}

	/** Latest known sensitive front-end route families from canonical companion plans. */
	private static function baseline_private_paths() {
		return array(
			'/messages', '/network', '/smail', '/appointments', '/security', '/security-center', '/verification',
			'/doctor-onboarding', '/doctor-verification', '/account', '/account-security', '/account-passkeys',
			'/resolve-account', '/notifications', '/settings', '/login', '/register', '/signup', '/logout',
			'/forgot-password', '/reset-password', '/complete-profile', '/publishing-dashboard', '/newsroom', '/notes',
			'/marketplace/dashboard', '/marketplace/deal', '/membership-application', '/membership-status',
			'/guardian-consent', '/membership-security', '/platform-system-check', '/platform-foundation/status',
			'/wp-admin', '/wp-login.php', '/wp-json',
		);
	}

	/** Routes that are task-focused under the latest File 00/File 01/File 02 contracts. */
	private static function sensitive_task_paths() {
		return array(
			'/account-security', '/account-passkeys', '/resolve-account', '/membership-application', '/membership-status',
			'/guardian-consent', '/membership-security', '/platform-system-check', '/platform-foundation/status',
		);
	}

	/** Return the WordPress home path used as the PWA/service-worker scope. */
	private static function scope_path() {
		$path = wp_parse_url( home_url( '/' ), PHP_URL_PATH );
		$path = is_string( $path ) && '' !== $path ? $path : '/';
		return trailingslashit( '/' . ltrim( $path, '/' ) );
	}

	/** Normalize a same-origin path prefix without retaining query/fragment data. */
	private static function normalize_path( $path ) {
		if ( ! is_string( $path ) ) {
			return '';
		}
		$path = wp_parse_url( trim( $path ), PHP_URL_PATH );
		if ( ! is_string( $path ) || '' === $path ) {
			return '';
		}
		$path = preg_replace( '#/+#', '/', '/' . ltrim( $path, '/' ) );
		$path = untrailingslashit( (string) $path );
		return strtolower( $path );
	}

	/** Build a bounded protected-path policy and explicitly expose truncation. */
	private static function private_policy() {
		$settings = FutureShellV5::settings();
		$external = apply_filters( 'sabri_shell_future_private_path_fragments', array() );
		$paths    = array_merge(
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
		$total    = count( $out );
		$complete = $total <= self::MAX_PRIVATE_PATHS;
		$bounded  = array_values( array_slice( $out, 0, self::MAX_PRIVATE_PATHS, true ) );
		return array(
			'paths'         => $bounded,
			'total'         => $total,
			'complete'      => $complete,
			'overflow_count'=> max( 0, $total - self::MAX_PRIVATE_PATHS ),
		);
	}

	/** Scope protected relative prefixes to root or a WordPress subdirectory. */
	private static function scoped_private_paths( array $policy ) {
		$scope_root = untrailingslashit( self::scope_path() );
		$scope_root = '/' === $scope_root ? '' : strtolower( $scope_root );
		$out = array();
		foreach ( $policy['paths'] as $path ) {
			$scoped = $path;
			if ( '' !== $scope_root && $path !== $scope_root && 0 !== strpos( $path, $scope_root . '/' ) ) {
				$scoped = $scope_root . $path;
			}
			$out[ $scoped ] = $scoped;
		}
		return array_values( $out );
	}

	/** Whether a normalized request path lies under one protected prefix. */
	private static function path_is_private( $path, array $policy ) {
		$path = self::normalize_path( $path );
		if ( '' === $path ) {
			return true;
		}
		if ( ! $policy['complete'] ) {
			return true;
		}
		foreach ( self::scoped_private_paths( $policy ) as $private ) {
			if ( $path === $private || 0 === strpos( $path, $private . '/' ) ) {
				return true;
			}
		}
		return false;
	}

	/** Conservative public-route classification for local Recent/Resume only. */
	private static function current_route_public( array $policy ) {
		if ( ! $policy['complete'] || is_admin() || wp_doing_ajax() || Layout::MINIMAL === Layout::current_mode() || is_404() || is_preview() || ! empty( $_GET ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only privacy classification.
			return false;
		}
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- path only.
		if ( self::path_is_private( $request_uri, $policy ) ) {
			return false;
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

	/** Final browser context; privacy-sensitive convenience features fail closed on policy overflow. */
	public static function enqueue_final_context() {
		if ( ! wp_script_is( 'sabri-shell-future-v5', 'enqueued' ) ) {
			return;
		}
		$policy  = self::private_policy();
		$payload = array(
			'thirdHardeningVersion' => self::CONTRACT_VERSION,
			'privacyPolicyComplete' => (bool) $policy['complete'],
			'privacyPolicyOverflow' => absint( $policy['overflow_count'] ),
			'currentRoutePublic'    => self::current_route_public( $policy ),
			'swScope'               => self::scope_path(),
			'privatePaths'          => self::scoped_private_paths( $policy ),
		);
		$code = 'window.SabriShellFutureV5ThirdHardening=' . wp_json_encode( $payload ) . ';'
			. 'window.SabriShellFutureV5=Object.assign({},window.SabriShellFutureV5||{},window.SabriShellFutureV5ThirdHardening);'
			. 'if(window.SabriShellFutureV5.privacyPolicyComplete===false){window.SabriShellFutureV5.features=Object.assign({},window.SabriShellFutureV5.features||{},{recent_resume:false,predictive_prefetch:false,smart_navigation:false});}';
		wp_add_inline_script( 'sabri-shell-future-v5', $code, 'before' );
	}

	/** Force newly approved private authentication/membership/foundation task routes to Minimal. */
	public static function force_sensitive_task_layout( $mode, $settings ) {
		unset( $settings );
		$request = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- path only.
		$path    = self::normalize_path( $request );
		foreach ( self::sensitive_task_paths() as $private ) {
			if ( $path === $private || 0 === strpos( $path, $private . '/' ) ) {
				return Layout::MINIMAL;
			}
		}
		return $mode;
	}

	/** Ensure latest private routes receive no-store/noindex protection from the common header layer. */
	public static function force_private_headers( $private, $path ) {
		if ( $private ) {
			return true;
		}
		$policy = self::private_policy();
		return self::path_is_private( $path, $policy );
	}

	/** Reconcile latest File 01 and File 02 ownership wording without creating native truth. */
	public static function harmonize_latest_contracts( $registry ) {
		$registry = is_array( $registry ) ? $registry : array();
		$common = array(
			'contract_version' => CentralPlanContract::CONTRACT_VERSION,
			'cache_policy'     => 'owner-aware-bounded',
		);
		$registry['01-B'] = array_merge( $common, array(
			'owner'            => 'Sabri Platform Foundation',
			'native_scope'     => 'bootstrap-registries-contracts-activation-shared-conventions',
			'file20_boundary'  => 'consume-foundation-registry-no-shell-or-search-truth',
			'criticality'      => 'required',
			'failure_behavior' => 'bounded-last-known-or-unavailable',
			'provider_baseline'=> 'foundation-contract-2.0.0-compatible',
		) );
		$registry['02'] = array_merge( $common, array(
			'owner'            => 'Authentication and Accounts',
			'native_scope'     => 'credentials-passkeys-sessions-risk-recovery-account-completion',
			'file20_boundary'  => 'minimal-task-layout-safe-route-mount-only',
			'criticality'      => 'required',
			'failure_behavior' => 'route-unavailable-no-authentication-fallback',
			'provider_baseline'=> 'modern-auth-1.3.0-candidate-staging-unaccepted',
		) );
		return $registry;
	}

	/** Resolve File 25 tokens, correcting only the explicit continuity fallback. */
	private static function visual_tokens() {
		$contract = CentralPlanContract::visual_contract();
		$tokens   = isset( $contract['tokens'] ) && is_array( $contract['tokens'] ) ? $contract['tokens'] : array();
		if ( empty( $contract['status'] ) || 'file25' !== $contract['status'] ) {
			$tokens['primary_color'] = self::BRAND_FALLBACK;
		}
		if ( empty( $tokens['background'] ) ) {
			$tokens['background'] = '#ffffff';
		}
		return $tokens;
	}

	/** Keep the CSS continuity color aligned with the latest governing green when File 25 is unavailable. */
	public static function apply_continuity_brand_fallback() {
		$contract = CentralPlanContract::visual_contract();
		if ( isset( $contract['status'] ) && 'file25' === $contract['status'] ) {
			return;
		}
		if ( wp_style_is( 'sabri-shell-central-plan-v4', 'enqueued' ) ) {
			wp_add_inline_style( 'sabri-shell-central-plan-v4', 'body.sabri-shell-enabled{--sabri-shell-primary:' . self::BRAND_FALLBACK . ';}' );
		}
	}

	/** File-25-aware PWA manifest with exact current continuity green. */
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
			'background_color' => $tokens['background'],
			'theme_color'      => isset( $tokens['primary_color'] ) ? $tokens['primary_color'] : self::BRAND_FALLBACK,
			'lang'             => get_bloginfo( 'language' ) ?: 'en-US',
			'icons'            => $icons,
		);
	}

	/** Final PWA handler with explicit fail-closed behavior when the privacy registry overflows. */
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

		$policy        = self::private_policy();
		$scope         = self::scope_path();
		$private_paths = self::scoped_private_paths( $policy );
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
		$source .= 'const POLICY_COMPLETE=' . ( $policy['complete'] ? 'true' : 'false' ) . ";\n";
		$source .= 'const CONTROL=' . wp_json_encode( $control_url ) . ";\n";
		$source .= 'const PLUGIN_PATH=' . wp_json_encode( $plugin_path ) . ";\n";
		$source .= "let lastControlCheck=0;\n";
		$source .= "const privatePath=p=>{p=String(p||'/').toLowerCase().replace(/\\/+$/,'')||'/';return !POLICY_COMPLETE||PRIVATE.some(x=>p===x||p.startsWith(x+'/'));};\n";
		$source .= "async function controlAlive(){const now=Date.now();if(now-lastControlCheck<60000)return true;try{const r=await fetch(CONTROL,{cache:'no-store',credentials:'same-origin'});if(!r.ok){await self.registration.unregister();for(const k of await caches.keys()){if(k.startsWith('sabri-shell-'))await caches.delete(k);}return false;}lastControlCheck=now;return true;}catch(e){return true;}}\n";
		$source .= "self.addEventListener('install',e=>self.skipWaiting());\n";
		$source .= "self.addEventListener('activate',e=>e.waitUntil((async()=>{for(const k of await caches.keys()){if(k!==CACHE&&k.startsWith('sabri-shell-'))await caches.delete(k);}await self.clients.claim();})()));\n";
		$source .= "self.addEventListener('fetch',e=>{const r=e.request;if(r.method!=='GET')return;const u=new URL(r.url);if(u.origin!==self.location.origin||privatePath(u.pathname))return;if(r.mode==='navigate'){e.respondWith((async()=>{if(!await controlAlive())return fetch(r);try{return await fetch(r,{cache:'no-store'});}catch(err){return new Response(OFFLINE,{status:503,headers:{'Content-Type':'text/html; charset=utf-8','Cache-Control':'no-store'}});}})());return;}if(u.pathname.startsWith(PLUGIN_PATH)){e.respondWith(caches.open(CACHE).then(async c=>{const hit=await c.match(r);if(hit)return hit;const res=await fetch(r);if(res.ok&&res.type==='basic')await c.put(r,res.clone());return res;}));}});\n";
		echo $source; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- generated JS with JSON-encoded values only.
		exit;
	}

	/** Manifest link/theme color with the current continuity fallback only when File 25 is absent. */
	public static function head_links() {
		if ( is_admin() || Layout::MINIMAL === Layout::current_mode() || ! FutureShellV5::feature_enabled( 'pwa_shell' ) ) {
			return;
		}
		$tokens = self::visual_tokens();
		$theme  = isset( $tokens['primary_color'] ) ? $tokens['primary_color'] : self::BRAND_FALLBACK;
		echo '<link rel="manifest" href="' . esc_url( home_url( '/sabri-shell-manifest.webmanifest' ) ) . '">';
		echo '<meta name="theme-color" content="' . esc_attr( $theme ) . '">';
	}

	/** Third-pass System Check evidence. */
	public static function system_check( $sections ) {
		$sections = is_array( $sections ) ? $sections : array();
		$policy   = self::private_policy();
		$sections['future_shell_v5_third_hardening'] = array(
			'label'                    => __( 'Future Shell v5 third-pass hardening', 'sabri-unified-application-shell' ),
			'contract_version'         => self::CONTRACT_VERSION,
			'privacy_policy_complete'  => (bool) $policy['complete'],
			'private_path_count'       => count( $policy['paths'] ),
			'private_path_total'       => absint( $policy['total'] ),
			'privacy_policy_overflow'  => absint( $policy['overflow_count'] ),
			'route_rememberable'       => self::current_route_public( $policy ),
			'virtual_asset_owner'      => 'future-shell-v5-third-hardening',
			'continuity_brand_fallback'=> self::BRAND_FALLBACK,
			'staging_accepted'         => false,
			'live_deployed'            => false,
		);
		return $sections;
	}
}
