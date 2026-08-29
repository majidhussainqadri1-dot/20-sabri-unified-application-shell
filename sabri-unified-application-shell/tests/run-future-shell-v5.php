<?php
/** Comprehensive static regression for Future Shell v5 and all corrective layers. */
declare(strict_types=1);

$root = dirname( __DIR__ );
$read = static function ( $path ) use ( $root ) { return (string) file_get_contents( $root . '/' . $path ); };
$php      = $read('includes/class-future-shell-v5.php');
$hard     = $read('includes/class-future-shell-v5-hardening.php');
$client   = $read('includes/class-future-shell-v5-client-context.php');
$control  = $read('includes/class-future-shell-v5-control-guard.php');
$second   = $read('includes/class-future-shell-v5-second-hardening.php');
$third    = $read('includes/class-future-shell-v5-third-hardening.php');
$fourth   = $read('includes/class-future-shell-v5-fourth-hardening.php');
$fifth    = $read('includes/class-future-shell-v5-fifth-hardening.php');
$sixth    = $read('includes/class-future-shell-v5-sixth-hardening.php');
$seventh  = $read('includes/class-future-shell-v5-seventh-hardening.php');
$eighth   = $read('includes/class-future-shell-v5-eighth-hardening.php');
$ninth    = $read('includes/class-future-shell-v5-ninth-hardening.php');
$tenth    = $read('includes/class-future-shell-v5-tenth-hardening.php');
$eleventh = $read('includes/class-future-shell-v5-eleventh-hardening.php');
$resth    = $read('includes/class-second-eighty-rest-hardening.php');
$slots    = $read('includes/class-native-content-slots.php');
$plugin   = $read('includes/class-plugin.php');
$defaults = $read('includes/class-defaults.php');
$route    = $read('includes/class-route-security.php');
$nav      = $read('includes/class-navigation.php');
$system   = $read('includes/class-system-check.php');
$audit    = $read('includes/class-plan-v4-audit.php');
$recovery = $read('includes/class-plan-v4-recovery.php');
$safe     = $read('includes/class-safe-mode.php');
$snapshot = $read('includes/class-snapshot.php');
$js       = $read('assets/js/future-shell-v5.js');
$guard    = $read('assets/js/future-shell-v5-editable-guard.js');
$css      = $read('assets/css/future-shell-v5.css');
$main     = $read('sabri-unified-application-shell.php');
$uninstall= $read('uninstall.php');
$fail = array();
$assert = static function ( $ok, $label ) use ( &$fail ): void { if ( ! $ok ) { $fail[] = $label; } };

$features = array('command_palette','pwa_shell','offline_mode','data_saver','recent_resume','module_circuit_breaker','last_known_good','performance_guardian','smart_navigation','keyboard_accessibility','focus_mode','split_workspace','adaptive_foldable','view_transitions','predictive_prefetch','language_direction','accessibility_center','release_rings');
$assert( 18 === count( $features ), 'exact 18 feature ids' );
foreach ( $features as $feature ) { $assert( false !== strpos( $php, "'{$feature}'" ), 'feature ' . $feature ); }

$assert( false !== strpos( $main, '* Version: 1.4.16' ) && false !== strpos( $main, "define( 'SABRI_SHELL_VERSION', '1.4.16' );" ), 'release 1.4.16' );
foreach ( array('FutureShellV5::register();','FutureShellV5Hardening::register();','FutureShellV5ClientContext::register();','FutureShellV5ControlGuard::register();','FutureShellV5SecondHardening::register();','FutureShellV5ThirdHardening::register();','FutureShellV5FourthHardening::register();','FutureShellV5FifthHardening::register();','FutureShellV5SixthHardening::register();','FutureShellV5SeventhHardening::register();','FutureShellV5EighthHardening::register();','FutureShellV5NinthHardening::register();','FutureShellV5TenthHardening::register();','FutureShellV5EleventhHardening::register();','SecondEightyRestHardening::register();','SystemCheckDuplicateHardening::register();','RouteSecurity::register();') as $registration ) {
    $assert( false !== strpos( $main, $registration ), 'registered ' . $registration );
}

$assert( false !== strpos( $php, 'FourPlanHarmonization::file26_search_contract()' ), 'File26 search preserved' );
$assert( false !== strpos( $fifth, "CONTRACT_VERSION = '1.0.5'" ) && false !== strpos( $fifth, 'sabri_shell_future_internal_principal_allowed' ) && false !== strpos( $fifth, 'PHP_INT_MAX' ), 'five-state final release ring evaluator' );
$assert( false !== strpos( $control, 'sabri_shell_invalid_release_ring' ) && false !== strpos( $control, "'status' => 400" ), 'invalid release ring fails closed' );
$assert( false !== strpos( $third, 'MAX_PRIVATE_PATHS' ) && false !== strpos( $third, 'privacyPolicyComplete' ) && false !== strpos( $third, 'POLICY_COMPLETE' ), 'private path overflow fails closed' );
$assert( false !== strpos( $third, "BRAND_FALLBACK    = '#087a4e'" ), 'Sabri green continuity fallback' );
$assert( false !== strpos( $fourth, 'notifications-runtime-3.0.0-intelligent-attention-os' ) && false !== strpos( $fourth, 'home-news-runtime-1.0.1-ng30-amended' ), 'File19/File21 compatibility preserved' );
$assert( false !== strpos( $sixth, 'declared-compatibility-target-not-runtime-detection' ) && false !== strpos( $sixth, 'native-owners-enforce-file24-assesses-governs-file20-renders' ), 'File24/current compatibility truth preserved' );
foreach ( array('CF-01','CF-02','CF-03','CF-04','CF-05','CF-06') as $cf ) { $assert( false !== strpos( $seventh, "\$registry['{$cf}']" ), 'conditional registry ' . $cf ); }
$assert( false !== strpos( $seventh, 'single-free-tier-voluntary-donation-no-donor-advantage' ) && false !== strpos( $seventh, '1073741824' ), 'financial and 1GB transfer law preserved' );
$assert( false !== strpos( $eighth, "CONTRACT_VERSION = '1.0.8'" ) && false !== strpos( $eighth, 'appearance-owned-by-file25' ) && false !== strpos( $eighth, '/system-check/export' ), 'eighth hardening preserved' );
$assert( false !== strpos( $ninth, "CONTRACT_VERSION = '1.0.9'" ) && false !== strpos( $ninth, "'approved_feature_count' => 18" ), 'ninth hardening preserved' );
$assert( false !== strpos( $tenth, "CONTRACT_VERSION = '1.0.10'" ) && false !== strpos( $tenth, "'approved_feature_count' => 18" ), 'tenth hardening exact scope' );
$assert( false !== strpos( $eleventh, "CONTRACT_VERSION = '1.0.11'" ) && false !== strpos( $eleventh, "'approved_feature_count' => 18" ), 'eleventh hardening exact scope' );
$assert( false !== strpos( $eleventh, 'sabri_messages' ) && false !== strpos( $eleventh, 'block_core_registration_fallback' ), 'File17 Messages and High-Trust signup corrections' );
$assert( false !== strpos( $eleventh, 'consume-foundation-registry-no-shell-or-search-truth' ), 'File01 foundation never owns Search truth' );
$assert( false !== strpos( $resth, "'Cache-Control', 'private, no-store, max-age=0'" ), 'sensitive File20 REST evidence is no-store' );

$assert( false !== strpos( $defaults, 'const SCHEMA_VERSION       = 5;' ) && false === strpos( $defaults, "'appearance'            => array(" ), 'schema5 with no fresh File20 visual state' );
$assert( false === strpos( $plugin, 'HomeFeed::register();' ) && false !== strpos( $tenth, "'auto_insert' => false" ), 'File21 owns Home feed and legacy File20 state is inert' );
foreach ( array('sabri_shell_home_before_main','sabri_shell_home_main','sabri_shell_home_after_main','sabri_shell_home_right_sidebar','sabri_shell_news_main') as $slot ) { $assert( false !== strpos( $slots, $slot ), 'native File21 slot ' . $slot ); }
$assert( false !== strpos( $slots, "'' !== trim( \$main ) ? \$main : \$content" ), 'native main output replaces legacy page fallback' );

$assert( false !== strpos( $safe, 'emergency_direct_write_blocked' ) && false !== strpos( $safe, 'emergency_write_authorized' ), 'single-authority Emergency lifecycle' );
$assert( false !== strpos( $safe, 'FutureShellV5TenthHardening::critical_health_state( $health )' ), 'Emergency re-enable consumes critical health truth' );
$assert( false !== strpos( $safe, 'wp_validate_redirect' ) && false !== strpos( $safe, 'QUERY_NONCE_ACTION' ), 'Safe Mode nonce link remains same-site' );
$assert( false !== strpos( $tenth, 'membership-core-1.2.18-reviewed-head' ) && false !== strpos( $tenth, "'production_safe_implied' => false" ), 'latest File00 audit truth without false production safety' );
$assert( false !== strpos( $tenth, 'file26_search_surface_only' ) && false !== strpos( $tenth, "'file20_fallback' => false" ), 'no File20/WordPress search ownership fallback' );
$assert( false !== strpos( $tenth, 'bind_actual_provider_versions' ) && false !== strpos( $tenth, 'probe_file25_contract_shape' ) && false !== strpos( $tenth, 'probe_file01b_contract_shape' ), 'actual provider semantic versions checked' );
$assert( false !== strpos( $tenth, 'critical_health_state' ) && false !== strpos( $tenth, 'healthy-only-when-critical-contracts-verified' ), 'System Check cannot false-green critical providers' );

$assert( false !== strpos( $recovery, 'const SNAPSHOT_FORMAT = 2;' ) && false !== strpos( $recovery, "'exists' => \$value !== \$sentinel" ), 'presence-aware recovery snapshots' );
$assert( false !== strpos( $recovery, 'sabri_shell_stale_repair_locked' ) && false !== strpos( $recovery, 'plan_v4_quarantine_stale_page_bindings' ), 'locked repair concurrency and page-map repair' );
$assert( false !== strpos( $recovery, "'current_emergency_state_preserved' => true" ) && false !== strpos( $recovery, "'settings_row_version_monotonic' => true" ) && false !== strpos( $recovery, 'restore_option_entry' ), 'rollback safety state and post-write verification' );
$assert( false !== strpos( $route, "esc_url_raw( \$url, array( 'https' ) )" ) && false !== strpos( $route, 'sabri_shell_route_override_allowed_hosts' ) && false !== strpos( $route, "option_' . Defaults::OPTION_NAME" ), 'strict route override policy and read-time revalidation' );
$assert( false !== strpos( $route, 'validated_path' ) && false !== strpos( $route, 'rawurldecode' ), 'absolute and relative override paths share decoded validation' );
$assert( false === strpos( $nav, 'Integrations::destination_url( $key )' ) && false !== strpos( $nav, 'RouteSecurity::sanitize_override' ), 'canonical route resolver cannot be preempted by arbitrary companion URL callback' );
foreach ( range(1,5) as $priority ) { $assert( false !== strpos( $nav, "'source_priority'] = {$priority}" ), 'route priority ' . $priority ); }
$assert( false !== strpos( $uninstall, "empty( \$settings['delete_on_uninstall'] )" ) && false !== strpos( $uninstall, 'sabri_shell_plan_v4_snapshots' ) && false !== strpos( $uninstall, 'sabri_shell_future_lkg' ) && false !== strpos( $uninstall, 'sabri_shell_future_lkg_restore_lock' ), 'opt-in complete File20 uninstall cleanup' );

$assert( false !== strpos( $system, "apply_filters( 'sabri_shell_system_check_sections'" ) && false !== strpos( $system, "'severity'" ) && false !== strpos( $system, "'remediation'" ), 'structured System Check sections' );
$assert( false === strpos( $system, 'doctor_roles' ) && false !== strpos( $system, 'doctor-verification-authority' ), 'System Check uses canonical doctor verification ownership' );
$assert( false !== strpos( $audit, 'ANCHOR_OPTION' ) && false !== strpos( $audit, 'rehash_events' ) && false !== strpos( $audit, 'sabri_shell_audit_chain_invalid' ), 'audit retention/privacy integrity' );
$assert( false !== strpos( $snapshot, 'FORMAT_VERSION = 2' ) && false === strpos( $snapshot, "update_option( 'page_on_front'" ), 'activation snapshot integrity and no shared front-page restore' );

$assert( false !== strpos( $js, 'sabriShellRecentPublicRoutesV' ) && false !== strpos( $js, 'canonicalLocalUrl' ), 'local recents privacy versioning' );
$assert( false !== strpos( $js, 'Object.keys(prefetched).length >= 3' ) && false !== strpos( $js, 'dataSaverActive()' ), 'bounded safe prefetch' );
$assert( false !== strpos( $js, 'isEditableTarget' ) && false !== strpos( $guard, 'event.stopImmediatePropagation()' ), 'editable keyboard guard' );
$assert( false === strpos( $js, 'history.back()' ) && false !== strpos( $js, 'dialogReturnFocus' ), 'safe Back and focus restoration' );
$assert( false === strpos( $css, '--sabri-shell-v5-accent' ) && false === strpos( $css, 'filter:contrast(' ), 'File25 visual ownership in CSS' );
$assert( false !== strpos( $css, '@supports (view-transition-name:none)' ) && false !== strpos( $css, 'horizontal-viewport-segments:2' ), 'view transition/foldable progressive support' );

$combined = $php . $hard . $client . $control . $second . $third . $fourth . $fifth . $sixth . $seventh . $eighth . $ninth . $tenth . $eleventh . $resth . $slots . $plugin . $defaults . $route . $nav . $system . $audit . $recovery . $safe . $snapshot . $uninstall;
$assert( false === strpos( $combined, 'CREATE TABLE' ) && false === strpos( $combined, 'dbDelta(' ) && false === strpos( $combined, 'INSERT INTO' ), 'no foreign database backend anywhere in shell/recovery layers' );

if ( $fail ) {
    fwrite( STDERR, "Future Shell v5 comprehensive FAIL: " . implode( '; ', $fail ) . "\n" );
    exit(1);
}
echo "Future Shell v5: 18/18 enhancements + eleven corrective layers + native ownership + strict routing/recovery/health/Emergency hardening preserved under 1.4.16 PASS\n";