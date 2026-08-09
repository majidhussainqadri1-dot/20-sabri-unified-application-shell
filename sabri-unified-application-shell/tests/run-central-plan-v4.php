<?php
/** Static regression checks for File 20 central-plan harmonization. */
declare(strict_types=1);

$root = dirname(__DIR__);
$checks = 0;
$failures = array();
$assert = static function (bool $condition, string $message) use (&$checks, &$failures): void { ++$checks; if (!$condition) { $failures[] = $message; } };
$read = static function (string $relative) use ($root): string { $path = $root . '/' . $relative; $data = @file_get_contents($path); if (!is_string($data)) { fwrite(STDERR, "Unable to read {$relative}\n"); exit(1); } return $data; };

$main = $read('sabri-unified-application-shell.php');
$contract = $read('includes/class-central-plan-contract.php');
$seventh = $read('includes/class-future-shell-v5-seventh-hardening.php');
$eighth = $read('includes/class-future-shell-v5-eighth-hardening.php');
$ninth = $read('includes/class-future-shell-v5-ninth-hardening.php');
$tenth = $read('includes/class-future-shell-v5-tenth-hardening.php');
$layout = $read('includes/class-layout.php');
$assets = $read('includes/class-assets.php');
$css = $read('assets/css/shell-central-plan-v4.css');

$assert(strpos($main, '* Version: 1.4.11') !== false, 'Plugin header must be 1.4.11.');
$assert(strpos($main, "define( 'SABRI_SHELL_VERSION', '1.4.11' )") !== false, 'Runtime version must be 1.4.11.');
$assert(strpos($main, 'CentralPlanContract::register()') !== false, 'Central-plan contract must register.');
$assert(strpos($main, 'FutureShellV5SeventhHardening::register()') !== false, 'Seventh conditional-module layer must register.');
$assert(strpos($main, 'FutureShellV5EighthHardening::register()') !== false && strpos($eighth, "CONTRACT_VERSION = '1.0.8'") !== false, 'Eighth corrective layer must remain registered.');
$assert(strpos($main, 'FutureShellV5NinthHardening::register()') !== false && strpos($ninth, "CONTRACT_VERSION = '1.0.9'") !== false, 'Ninth corrective layer must remain registered.');
$assert(strpos($main, 'FutureShellV5TenthHardening::register()') !== false && strpos($tenth, "CONTRACT_VERSION = '1.0.10'") !== false, 'Tenth corrective layer must register.');
$assert(strpos($main, "SABRI_SHELL_CREATE_CONTRACT_VERSION', '1.0.1'") !== false, 'File 22 Create contract must remain 1.0.1.');

foreach (array('00', '01-A', '01-B', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26') as $file) { $assert(strpos($contract, "'{$file}'") !== false, "Missing canonical contract for File {$file}."); }
foreach (array('CF-01', 'CF-02', 'CF-03', 'CF-04', 'CF-05', 'CF-06') as $file) { $assert(strpos($seventh, "\$registry['{$file}']") !== false, "Missing conditional contract for {$file}."); }
$assert(strpos($seventh, 'declared-conditional-contract-target-not-runtime-detection') !== false, 'Conditional contracts must not imply runtime detection.');
$assert(strpos($seventh, "'conditional_activation_required'  => true") !== false, 'Conditional modules must require explicit activation.');
$assert(strpos($contract, 'sabri_shell_file25_visual_contract') !== false, 'File 25 visual contract hook missing.');
$assert(strpos($contract, 'file-20-continuity-fallback') !== false, 'Truthful continuity fallback missing.');
$assert(strpos($contract, 'retire_appearance_tab') !== false, 'Historical central File25 retirement marker missing.');
$assert(strpos($contract, "'visual_owner'] = 'file-25'") !== false, 'File 25 visual owner marker missing.');
$assert(strpos($eighth, 'appearance-owned-by-file25') !== false, 'Current File20 Appearance route retirement missing.');
$assert(strpos($tenth, 'remove_filter( \'body_class\', array( Renderer::class, \'body_classes\' )') !== false, 'Legacy File20 visual runtime class source retired.');

foreach (array('const THREE', 'const TWO', 'const MINIMAL', 'const IMMERSIVE') as $mode) { $assert(strpos($layout, $mode) !== false, "Missing layout mode: {$mode}."); }
$assert(strpos($layout, "'publishing-dashboard'") === false, 'Publishing Dashboard must remain a two-column private application, not Minimal.');
$assert(strpos($layout, "'security-center'") === false, 'Security Center must not be forced into Minimal layout.');
$assert(strpos($layout, "! empty( \$_GET['user'] )") === false, 'Public profiles must not be forced into the three-column layout by query parameter.');
$assert(strpos($layout, "\$_GET[ \$query_key ]") === false, 'A generic query string must not force Immersive mode.');
$assert(strpos($layout, 'sabri_shell_is_immersive_context') !== false, 'Immersive context extension hook missing.');
$assert(strpos($eighth, 'force_subdirectory_safe_sensitive_layout') !== false, 'Final subdirectory-safe sensitive task layout correction missing.');
$assert(strpos($assets, "\$settings['appearance']") === false, 'File 20 assets must not own File 25 appearance settings.');
$assert(strpos($assets, 'CentralPlanContract::visual_contract()') !== false, 'Assets must expose the resolved visual provider.');
$assert(strpos($assets, '--sabri-shell-primary:') === false, 'Base Assets class must not write File 25 visual tokens.');
$assert(strpos($css, 'flex-wrap: nowrap !important') !== false, 'Desktop primary navigation must remain one no-wrap line.');
$assert(strpos($css, 'inline-size: max-content') !== false, 'Primary nav base CSS retains deterministic intrinsic-width handling; latest More-menu guardrail may override it.');
$assert(strpos($css, 'sabri-shell-layout-immersive') !== false, 'Immersive presentation rules missing.');
$assert(strpos($css, ':focus-visible') !== false, 'Visible keyboard focus correction missing.');
$assert(substr_count($css, '{') === substr_count($css, '}'), 'Central-plan CSS braces are unbalanced.');

if ($failures) { foreach ($failures as $failure) { fwrite(STDERR, "FAIL: {$failure}\n"); } fwrite(STDERR, sprintf("File 20 central-plan: %d checks, %d failures.\n", $checks, count($failures))); exit(1); }
echo sprintf("File 20 central-plan/tenth harmonization: %d PASS, 0 FAIL\n", $checks);
