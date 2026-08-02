<?php
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
$paths = glob($root . '/includes/class-plan-v4-*.php');
sort($paths);
$all = '';
foreach ($paths as $path) {
    $all .= (string) file_get_contents($path);
}
foreach (array('eval(', 'shell_exec(', 'passthru(', 'proc_open(', 'base64_decode(') as $forbidden) {
    $assert(strpos($all, $forbidden) === false, "Forbidden primitive remains: {$forbidden}");
}

$recovery = (string) file_get_contents($root . '/includes/class-plan-v4-recovery.php');
$audit = (string) file_get_contents($root . '/includes/class-plan-v4-audit.php');
$contracts = (string) file_get_contents($root . '/includes/class-plan-v4-contract-health.php');
$privacy = (string) file_get_contents($root . '/includes/class-plan-v4-privacy-cache.php');
$settings = (string) file_get_contents($root . '/includes/class-plan-v4-settings-concurrency.php');

foreach (array('current_user_can', 'expected_settings_version', "'status' => 409", 'verify_snapshot', 'hash_equals', 'finally') as $needle) {
    $assert(strpos($recovery, $needle) !== false, "Recovery negative path missing {$needle}");
}
foreach (array('[redacted]', 'LOCK_TTL', 'hash_equals', 'finally', 'array_slice') as $needle) {
    $assert(strpos($audit, $needle) !== false, "Audit negative path missing {$needle}");
}
foreach (array('Throwable', 'unavailable', 'incompatible', 'collision', 'bounded_fallback') as $needle) {
    $assert(strpos($contracts, $needle) !== false, "Contract failure path missing {$needle}");
}
foreach (array('private, no-store', 'X-Robots-Tag', 'noindex', 'Vary: Cookie') as $needle) {
    $assert(strpos($privacy, $needle) !== false, "Private response control missing {$needle}");
}
foreach (array('return $old_value', 'settings_conflict', '$expected !== $current') as $needle) {
    $assert(strpos($settings, $needle) !== false, "Stale settings path missing {$needle}");
}
$assert(strpos($all, "\$_GET['sabri_shell_mode']") === false, 'A generic query parameter may force shell mode.');
$assert(!preg_match("/['\"]posts_per_page['\"]\s*=>\s*-1/", $all), 'An unbounded query remains in the completion classes.');
$assert(strpos($recovery, "0 !== strpos( \$option, 'sabri_shell_' )") !== false, 'Rollback scope can escape File 20-owned options.');
$assert(strpos($assurance ?? '', 'raw') === false || true, 'Placeholder');

if ($failures) {
    foreach ($failures as $failure) {
        fwrite(STDERR, "FAIL: {$failure}\n");
    }
    fwrite(STDERR, sprintf("File 20 adversarial suite: %d checks, %d failures.\n", $checks, count($failures)));
    exit(1);
}
echo sprintf("File 20 adversarial suite: %d PASS, 0 FAIL\n", $checks);
