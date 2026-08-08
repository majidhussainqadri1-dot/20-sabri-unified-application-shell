<?php
/** Comprehensive static regression for Future Shell v5 and all corrective layers. */
declare(strict_types=1);

$root    = dirname( __DIR__ );
$php     = file_get_contents( $root . '/includes/class-future-shell-v5.php' );
$hard    = file_get_contents( $root . '/includes/class-future-shell-v5-hardening.php' );
$client  = file_get_contents( $root . '/includes/class-future-shell-v5-client-context.php' );
$control = file_get_contents( $root . '/includes/class-future-shell-v5-control-guard.php' );
$second  = file_get_contents( $root . '/includes/class-future-shell-v5-second-hardening.php' );
$third   = file_get_contents( $root . '/includes/class-future-shell-v5-third-hardening.php' );
$fourth  = file_get_contents( $root . '/includes/class-future-shell-v5-fourth-hardening.php' );
$fifth   = file_get_contents( $root . '/includes/class-future-shell-v5-fifth-hardening.php' );
$sixth   = file_get_contents( $root . '/includes/class-future-shell-v5-sixth-hardening.php' );
$seventh = file_get_contents( $root . '/includes/class-future-shell-v5-seventh-hardening.php' );
$eighth  = file_get_contents( $root . '/includes/class-future-shell-v5-eighth-hardening.php' );
$system  = file_get_contents( $root . '/includes/class-system-check.php' );
$audit   = file_get_contents( $root . '/includes/class-plan-v4-audit.php' );
$recovery= file_get_contents( $root . '/includes/class-plan-v4-recovery.php' );
$safe    = file_get_contents( $root . '/includes/class-safe-mode.php' );
$snapshot= file_get_contents( $root . '/includes/class-snapshot.php' );
$js      = file_get_contents( $root . '/assets/js/future-shell-v5.js' );
$guard   = file_get_contents( $root . '/assets/js/future-shell-v5-editable-guard.js' );
$css     = file_get_contents( $root . '/assets/css/future-shell-v5.css' );
$main    = file_get_contents( $root . '/sabri-unified-application-shell.php' );
$fail    = array();

$assert = static function ( $ok, $label ) use ( &$fail ): void { if ( ! $ok ) { $fail[] = $label; } };

$features = array(
    'command_palette','pwa_shell','offline_mode','data_saver','recent_resume','module_circuit_breaker',
    'last_known_good','performance_guardian','smart_navigation','keyboard_accessibility','focus_mode',
    'split_workspace','adaptive_foldable','view_transitions','predictive_prefetch','language_direction',
    'accessibility_center','release_rings'
);
$assert( count( $features ) === 18, 'exact 18 feature ids' );
foreach ( $features as $feature ) { $assert( false !== strpos( $php, "'{$feature}'" ), 'feature ' . $feature ); }

$assert( false !== strpos( $main, '* Version: 1.4.8' ) && false !== strpos( $main, "define( 'SABRI_SHELL_VERSION', '1.4.8' );" ), 'release 1.4.8' );
foreach ( array(
    'FutureShellV5::register();','FutureShellV5Hardening::register();','FutureShellV5ClientContext::register();',
    'FutureShellV5ControlGuard::register();','FutureShellV5SecondHardening::register();','FutureShellV5ThirdHardening::register();',
    'FutureShellV5FourthHardening::register();','FutureShellV5FifthHardening::register();','FutureShellV5SixthHardening::register();',
    'FutureShellV5SeventhHardening::register();','FutureShellV5EighthHardening::register();','SystemCheckDuplicateHardening::register();'
) as $registration ) { $assert( false !== strpos( $main, $registration ), 'registered ' . $registration ); }

$assert( false !== strpos( $php, 'FourPlanHarmonization::file26_search_contract()' ), 'File26 search preserved' );
$assert( false !== strpos( $php, "case 'limited'" ) && false !== strpos( $php, "case 'staging'" ), 'release ring base states present' );
$assert( false !== strpos( $hard, "\$ring    = 'disabled';" ), 'stored invalid ring fails closed' );
$assert( false !== strpos( $fifth, "CONTRACT_VERSION = '1.0.5'" ) && false !== strpos( $fifth, 'sabri_shell_future_internal_principal_allowed' ) && false !== strpos( $fifth, 'PHP_INT_MAX' ), 'fifth final ring evaluator' );
$assert( false !== strpos( $control, 'sabri_shell_invalid_release_ring' ) && false !== strpos( $control, "'status' => 400" ), 'invalid REST ring rejected' );
$assert( false !== strpos( $control, 'block_legacy_auto_restore' ) && false !== strpos( $control, 'PHP_INT_MAX' ), 'legacy auto recovery blocked' );
$assert( false !== strpos( $control, 'finally {' ) && false !== strpos( $control, 'catch ( \\Throwable $error )' ), 'guarded recovery cleanup' );
$assert( false !== strpos( $hard, "remove_action( 'wp_footer', array( FutureShellV5::class, 'render' ), 80 )" ), 'ring-aware render replaces base render' );

$assert( false !== strpos( $php, 'sabri-shell-sw' ) && false !== strpos( $php, 'sabri-shell-manifest' ), 'PWA virtual routes' );
$assert( false !== strpos( $js, 'cfg.swScope' ) && false !== strpos( $js, 'serviceWorker.register(cfg.swUrl, { scope: scope })' ), 'PWA site scope' );
$assert( false !== strpos( $hard, 'self.registration.unregister()' ), 'PWA self-unregister' );
$assert( false !== strpos( $main, "FutureShellV5Hardening', 'deactivate'" ) && false !== strpos( $hard, 'flush_rewrite_rules( false )' ), 'PWA deactivation cleanup' );
$assert( false !== strpos( $client, 'sabri_shell_future_private_path_fragments' ), 'external private-path provider hook' );
$assert( false !== strpos( $second, "remove_action( 'init', array( FutureShellV5ClientContext::class, 'ensure_private_path_policy' ), 6 )" ), 'dynamic private policy not persisted' );
$assert( false !== strpos( $third, 'MAX_PRIVATE_PATHS' ) && false !== strpos( $third, 'privacyPolicyComplete' ) && false !== strpos( $third, 'POLICY_COMPLETE' ), 'bounded privacy overflow fail closed' );
$assert( false !== strpos( $third, "remove_action( 'template_redirect', array( FutureShellV5::class, 'serve_virtual_assets' ), 0 )" ) && false !== strpos( $third, "add_action( 'template_redirect', array( __CLASS__, 'serve_virtual_assets' ), -40 )" ), 'single final PWA owner' );
$assert( false !== strpos( $third, "BRAND_FALLBACK    = '#087a4e'" ), 'Sabri Green continuity fallback' );

$assert( false !== strpos( $fourth, 'notifications-runtime-3.0.0-intelligent-attention-os' ), 'File19 one-notification provider compatibility' );
$assert( false !== strpos( $fourth, 'home-news-runtime-1.0.1-ng30-amended' ), 'File21 native Home/News compatibility' );
$assert( false !== strpos( $fourth, '/.well-known/webauthn' ) && false === strpos( $third, "'/.well-known/webauthn'" ), 'public WebAuthn endpoint classification' );
$assert( false !== strpos( $fourth, 'incident-containment' ) && false !== strpos( $fourth, 'native-modules-enforce' ), 'File24 historical render/enforce boundary' );

$assert( false !== strpos( $sixth, "CONTRACT_VERSION = '1.0.6'" ), 'sixth contract' );
$assert( false !== strpos( $sixth, 'membership-core-1.2.13' ) && false !== strpos( $sixth, "'cf01_contract'              => '1.0.0'" ), 'File00 current targets' );
$assert( false !== strpos( $sixth, 'foundation-runtime-2.0.0-future-foundation-18' ) && false !== strpos( $sixth, "'foundation_contract'      => '2.0.0'" ), 'File01 current targets' );
$assert( false !== strpos( $sixth, 'modern-auth-runtime-1.3.1-third-ten-round-reviewed' ) && false !== strpos( $sixth, "'auth_event_projection_contract'    => '1.1.0'" ), 'File02 current targets' );
$assert( false !== strpos( $sixth, 'AuthenticationCompromiseReported.v1' ) && false !== strpos( $sixth, 'AuthenticationLockdownEnabled.v1' ) && false !== strpos( $sixth, 'RecoveryChangeCoolingStarted.v1' ), 'File02 security events' );
$assert( false !== strpos( $sixth, 'file02-executes-file00-dual-control-receipt-required-file20-renders-only' ), 'File02 dual-control boundary' );
$assert( false !== strpos( $sixth, 'future-security-runtime-0.99.0' ) && false !== strpos( $sixth, 'F24-FUT-001..F24-FUT-025' ), 'File24 current future-security targets' );
$assert( false !== strpos( $sixth, 'declared-compatibility-target-not-runtime-detection' ) && false !== strpos( $sixth, "'runtime_presence_must_be_verified' => true" ), 'sixth compatibility truth boundary' );

$assert( false !== strpos( $seventh, "CONTRACT_VERSION = '1.0.7'" ) && false !== strpos( $seventh, 'CONDITIONAL_COUNT = 6' ), 'seventh contract exact conditional count' );
foreach ( array( 'CF-01','CF-02','CF-03','CF-04','CF-05','CF-06' ) as $cf ) { $assert( false !== strpos( $seventh, "\$registry['{$cf}']" ), 'conditional registry ' . $cf ); }
$assert( false !== strpos( $seventh, 'declared-conditional-contract-target-not-runtime-detection' ) && false !== strpos( $seventh, 'conditional_activation_required' ), 'conditional activation/runtime truth' );
$assert( false !== strpos( $seventh, 'single-free-tier-voluntary-donation-no-donor-advantage' ) && false !== strpos( $seventh, "'zero_commission'" ), 'CF03 current financial law' );
$assert( false !== strpos( $seventh, 'dormant-unless-new-founder-change-control-and-cf03-activation-evidence' ), 'CF03 paid collection dormant' );
$assert( false !== strpos( $seventh, '/clinic/records' ) && false !== strpos( $seventh, '/support/cases' ) && false !== strpos( $seventh, '/checkout' ) && false !== strpos( $seventh, '/insights' ) && false !== strpos( $seventh, '/settings/language' ), 'CF sensitive path coverage' );
$assert( false !== strpos( $seventh, "'/api/localization/v1', '/media/d'" ) && false !== strpos( $seventh, 'native-cache-and-range-authority-preserved' ), 'CF04 delivery minimal but native cache authority' );
$assert( false !== strpos( $seventh, 'file-17-cf-04-after-activation' ) && false !== strpos( $seventh, '1073741824' ), 'File17/CF04 transfer 1GB' );
$assert( false !== strpos( $seventh, 'native-owners-file24-cf04-after-activation' ), 'download native/File24/CF04 ownership' );
$assert( false !== strpos( $seventh, 'file-20-shell-surface-cf-06-locale-provider-after-activation' ) && false !== strpos( $seventh, 'existing-approved-language-provider-or-honest-unavailable' ), 'CF06 provider-only localization boundary' );

$assert( false !== strpos( $eighth, "CONTRACT_VERSION = '1.0.8'" ), 'eighth corrective contract' );
$assert( false !== strpos( $eighth, 'appearance-owned-by-file25' ), 'File25 visual ownership admin retirement' );
$assert( false !== strpos( $eighth, 'force_subdirectory_safe_sensitive_layout' ), 'subdirectory sensitive task layout' );
$assert( false !== strpos( $eighth, '/system-check/export' ), 'sanitized System Check export' );
$assert( false !== strpos( $system, "apply_filters( 'sabri_shell_system_check_sections'" ) && false !== strpos( $system, "'severity'" ) && false !== strpos( $system, "'remediation'" ), 'structured System Check sections' );
$assert( false !== strpos( $audit, 'ANCHOR_OPTION' ) && false !== strpos( $audit, 'rehash_events' ) && false !== strpos( $audit, 'sabri_shell_audit_chain_invalid' ), 'audit retention and privacy integrity' );
$assert( false !== strpos( $recovery, 'repair_action_diff' ) && false !== strpos( $recovery, 'Settings::ensure_defaults();' ), 'repair dry-run diff and normalization' );
$assert( false !== strpos( $recovery, "'schema_compatible'" ) && false !== strpos( $recovery, 'FutureShellV5::OPTION' ), 'schema-compatible recovery snapshot' );
$assert( false !== strpos( $safe, 'QUERY_NONCE_ACTION' ) && false !== strpos( $safe, 'EMERGENCY_META_OPTION' ) && false !== strpos( $safe, 'PlanV4PrivacyCache::purge' ), 'nonce Safe Mode and emergency lifecycle' );
$assert( false !== strpos( $snapshot, 'FORMAT_VERSION = 2' ) && false !== strpos( $snapshot, "'future_settings'" ) && false === strpos( $snapshot, "update_option( 'page_on_front'" ), 'activation snapshot File20-only integrity' );

$assert( false !== strpos( $js, 'sabriShellRecentPublicRoutesV' ) && false !== strpos( $js, 'canonicalLocalUrl' ), 'local recents privacy versioning' );
$assert( false !== strpos( $js, "candidate === prefix || candidate.indexOf(prefix + '/') === 0" ), 'private prefix boundary matcher' );
$assert( false !== strpos( $js, 'publicAnchorUrl(anchor)' ) && false !== strpos( $js, "anchor.closest('.sabri-shell-primary-nav')" ), 'pins public nav only' );
$assert( false !== strpos( $js, 'sabri:shell-performance' ) && false === strpos( $js, 'sendBeacon' ) && false !== strpos( $js, 'observer.disconnect()' ), 'performance local and bounded' );
$assert( false !== strpos( $js, 'Object.keys(prefetched).length >= 3' ) && false !== strpos( $js, 'dataSaverActive()' ), 'bounded safe prefetch' );
$assert( false !== strpos( $js, 'isEditableTarget' ) && false !== strpos( $guard, 'event.stopImmediatePropagation()' ), 'editable keyboard guard' );
$assert( false !== strpos( $js, "document.querySelector('[data-sabri-context-back]')" ) && false === strpos( $js, 'history.back()' ), 'safe Back behavior' );
$assert( false !== strpos( $js, 'dialogReturnFocus' ) && false !== strpos( $js, 'restoreDialogFocus' ), 'dialog focus restoration' );
$assert( false !== strpos( $js, 'features.language_direction' ), 'language/direction command' );
$assert( false !== strpos( $js, "matchMedia('(min-width: 1024px)')" ) && false !== strpos( $second, 'Layout::IMMERSIVE === Layout::current_mode()' ), 'split workspace desktop non-immersive' );
$assert( false !== strpos( $css, '.sabri-context-navigation' ), 'focus mode context navigation' );
$assert( false === strpos( $css, '.sabri-shell-data-saver *' ) && false !== strpos( $css, '[data-sabri-decorative-background]' ), 'data saver preserves native content' );
$assert( false === strpos( $css, '--sabri-shell-v5-accent' ) && false === strpos( $css, 'filter:contrast(' ), 'File25 visual ownership' );
$assert( false !== strpos( $second, 'status_header( 410 )' ), 'PWA disabled route retirement' );
$assert( false !== strpos( $second, "preg_replace( '/[^a-z0-9]+/i', '', SABRI_SHELL_VERSION )" ), 'PWA cache follows plugin version' );
$assert( false !== strpos( $second, 'preserve_partial_future_settings' ), 'partial settings preserve values' );
$assert( false !== strpos( $css, '@supports (view-transition-name:none)' ) && false !== strpos( $css, '@view-transition' ), 'progressive view transitions' );
$assert( false !== strpos( $css, 'env(safe-area-inset-left)' ) && false !== strpos( $css, 'horizontal-viewport-segments:2' ), 'foldable safe-area support' );

$all_shell = $php . $hard . $client . $control . $second . $third . $fourth . $fifth . $sixth . $seventh . $eighth . $system . $audit . $recovery . $safe . $snapshot;
$assert( false === strpos( $all_shell, 'CREATE TABLE' ) && false === strpos( $all_shell, 'dbDelta(' ) && false === strpos( $all_shell, 'INSERT INTO' ), 'no foreign database backend anywhere in Future Shell/recovery layers' );

if ( $fail ) {
    fwrite( STDERR, "Future Shell v5 comprehensive FAIL: " . implode( '; ', $fail ) . "\n" );
    exit( 1 );
}
echo "Future Shell v5: 18/18 enhancements + eight corrective passes + CF-01..CF-06 conditional harmonization + recovery/diagnostics hardening PASS\n";
