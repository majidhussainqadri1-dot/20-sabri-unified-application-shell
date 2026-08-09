<?php
/** Dedicated regression for the second fresh eighty-round File 20 audit. */
declare(strict_types=1);
$root=dirname(__DIR__);
$read=static function(string $p) use($root): string { $v=@file_get_contents($root.'/'.$p); if(!is_string($v)){fwrite(STDERR,"Missing {$p}\n");exit(1);} return $v; };
$main=$read('sabri-unified-application-shell.php');
$route=$read('includes/class-route-security.php');
$eleventh=$read('includes/class-future-shell-v5-eleventh-hardening.php');
$resth=$read('includes/class-second-eighty-rest-hardening.php');
$system=$read('includes/class-system-check.php');
$safe=$read('includes/class-safe-mode.php');
$concurrency=$read('includes/class-plan-v4-settings-concurrency.php');
$control=$read('includes/class-future-shell-v5-control-guard.php');
$future=$read('includes/class-future-shell-v5.php');
$uninstall=$read('uninstall.php');
$fail=[];
$a=static function(bool $ok,string $label) use(&$fail): void { if(!$ok){$fail[]=$label;} };
$a(strpos($main,'* Version: 1.4.12')!==false && strpos($main,"SABRI_SHELL_VERSION', '1.4.12")!==false,'release 1.4.12');
$a(strpos($main,'FutureShellV5EleventhHardening::register();')!==false && strpos($eleventh,"CONTRACT_VERSION = '1.0.11'")!==false,'eleventh hardening registered');
$a(strpos($main,'SecondEightyRestHardening::register();')!==false,'REST hardening registered');
$a(strpos($route,'validated_path')!==false && strpos($route,'rawurldecode')!==false,'absolute/relative path canonicalization');
$a(strpos($eleventh,'page_id_collision_policy')!==false && strpos($eleventh,'url_to_postid')!==false,'Page-ID collision gate');
$a(strpos($eleventh,"array( 'sabri_messages', 'sabri_communication' )")!==false,'File17 Messages shortcodes');
$a(strpos($eleventh,'sn_network_page_id')!==false && strpos($eleventh,'not-canonical-page-id-evidence')!==false,'Network fallback not Messages canonical Page-ID');
$a(strpos($eleventh,'block_core_registration_fallback')!==false,'open WP registration fallback blocked');
$a(strpos($eleventh,'configured_url_policy')!==false && strpos($eleventh,'https_or_relative_url')!==false,'configured URL policy');
$a(strpos($eleventh,'consume-foundation-registry-no-shell-or-search-truth')!==false,'File01 no Search truth');
$a(strpos($eleventh,'file21_provider_only_home_right_slot')!==false,'provider-only File21 right slot responsive');
$a(strpos($system,'doctor_roles')===false && strpos($system,'doctor-verification-authority')!==false,'no stale doctor-role diagnostic');
$a(strpos($eleventh,"array( 'file-20-shell', 'file-00-identity' )")!==false,'critical provider presence gate');
$a(strpos($resth,"'Cache-Control', 'private, no-store, max-age=0'")!==false,'sensitive REST no-store');
$a(strpos($safe,'wp_validate_redirect')!==false && strpos($safe,'QUERY_NONCE_ACTION')!==false,'Safe Mode nonce same-site');
$a(strpos($concurrency,"const LOCK_OPTION = 'sabri_shell_settings_update_lock'")!==false && strpos($concurrency,'concurrent-settings-write')!==false,'Settings API serialization');
$a(strpos($control,"LKG_LOCK_OPTION = 'sabri_shell_future_lkg_restore_lock'")!==false && strpos($control,"'serialized' => true")!==false,'LKG restore serialization');
$a(strpos($uninstall,'sabri_shell_settings_update_lock')!==false && strpos($uninstall,'sabri_shell_future_lkg_restore_lock')!==false,'explicit purge includes new locks');
$features=['command_palette','pwa_shell','offline_mode','data_saver','recent_resume','module_circuit_breaker','last_known_good','performance_guardian','smart_navigation','keyboard_accessibility','focus_mode','split_workspace','adaptive_foldable','view_transitions','predictive_prefetch','language_direction','accessibility_center','release_rings'];
$a(count($features)===18,'exact 18 features'); foreach($features as $f){$a(strpos($future,"'{$f}'")!==false,'feature '.$f);}
if($fail){fwrite(STDERR,"Second eighty-round consolidation FAIL: ".implode('; ',$fail)."\n");exit(1);} echo "Second fresh eighty-round consolidation: route, ownership, diagnostics, REST privacy, concurrency and LKG controls PASS\n";
