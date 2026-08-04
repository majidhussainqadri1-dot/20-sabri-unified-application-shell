<?php
/** Static regression checks for File 20 central-plan v4 harmonization. */
declare(strict_types=1);

$root = dirname(__DIR__);
$checks = 0;
$failures = array();

$assert = static function (bool $condition, string $message) use (&$checks, &$failures): void {
	++$checks;
	if (!$condition) {
		$failures[] = $message;
	}
};

$read = static function (string $relative) use ($root): string {
	$path = $root . '/' . $relative;
	$data = @file_get_contents($path);
	if (!is_string($data)) {
		fwrite(STDERR, "Unable to read {$relative}\n");
		exit(1);
	}
	return $data;
};

$main = $read('sabri-unified-application-shell.php');
$contract = $read('includes/class-central-plan-contract.php');
$layout = $read('includes/class-layout.php');
$assets = $read('includes/class-assets.php');
$css = $read('assets/css/shell-central-plan-v4.css');

$assert(strpos($main, '* Version: 1.2.1') !== false, 'Plugin header must be 1.2.1.');
$assert(strpos($main, "define( 'SABRI_SHELL_VERSION', '1.2.1' )") !== false, 'Runtime version must be 1.2.1.');
$assert(strpos($main, 'CentralPlanContract::register()') !== false, 'Central-plan contract must register.');
$assert(strpos($main, "SABRI_SHELL_CREATE_CONTRACT_VERSION', '1.0.1'") !== false, 'File 22 Create contract must remain 1.0.1.');

foreach (array('00', '01-A', '01-B', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25') as $file) {
	$assert(strpos($contract, "'{$file}'") !== false, "Missing canonical contract for File {$file}.");
}
$assert(strpos($contract, 'sabri_shell_file25_visual_contract') !== false, 'File 25 visual contract hook missing.');
$assert(strpos($contract, 'file-20-continuity-fallback') !== false, 'Truthful continuity fallback missing.');
$assert(strpos($contract, 'retire_appearance_tab') !== false, 'Legacy Appearance editor retirement missing.');
$assert(strpos($contract, "'visual_owner'] = 'file-25'") !== false, 'File 25 visual owner marker missing.');

foreach (array('const THREE', 'const TWO', 'const MINIMAL', 'const IMMERSIVE') as $mode) {
	$assert(strpos($layout, $mode) !== false, "Missing layout mode: {$mode}.");
}
$assert(strpos($layout, "'publishing-dashboard'") === false, 'Publishing Dashboard must remain a two-column private application, not Minimal.');
$assert(strpos($layout, "'security-center'") === false, 'Security Center must not be forced into Minimal layout.');
$assert(strpos($layout, "! empty( \$_GET['user'] )") === false, 'Public profiles must not be forced into the three-column layout by query parameter.');
$assert(strpos($layout, "\$_GET[ \$query_key ]") === false, 'A generic query string must not force Immersive mode.');
$assert(strpos($layout, 'sabri_shell_is_immersive_context') !== false, 'Immersive context extension hook missing.');

$assert(strpos($assets, "\$settings['appearance']") === false, 'File 20 assets must not own File 25 appearance settings.');
$assert(strpos($assets, 'CentralPlanContract::visual_contract()') !== false, 'Assets must expose the resolved visual provider.');
$assert(strpos($assets, '--sabri-shell-primary:') === false, 'Base Assets class must not write File 25 visual tokens.');

$assert(strpos($css, 'flex-wrap: nowrap !important') !== false, 'Desktop primary navigation must remain one no-wrap line.');
$assert(strpos($css, 'inline-size: max-content') !== false, 'Primary nav needs bounded horizontal overflow width.');
$assert(strpos($css, 'sabri-shell-layout-immersive') !== false, 'Immersive presentation rules missing.');
$assert(strpos($css, ':focus-visible') !== false, 'Visible keyboard focus correction missing.');
$assert(substr_count($css, '{') === substr_count($css, '}'), 'Central-plan CSS braces are unbalanced.');

if ($failures) {
	foreach ($failures as $failure) {
		fwrite(STDERR, "FAIL: {$failure}\n");
	}
	fwrite(STDERR, sprintf("File 20 central-plan v4: %d checks, %d failures.\n", $checks, count($failures)));
	exit(1);
}

echo sprintf("File 20 central-plan v4: %d PASS, 0 FAIL\n", $checks);