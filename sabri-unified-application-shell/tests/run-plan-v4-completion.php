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
$read = static function (string $relative) use ($root): string {
    $value = @file_get_contents($root . '/' . $relative);
    if (!is_string($value)) {
        fwrite(STDERR, "Unable to read {$relative}\n");
        exit(1);
    }
    return $value;
};

$files = array(
    'includes/class-plan-v4-audit.php',
    'includes/class-plan-v4-assurance.php',
    'includes/class-plan-v4-contract-health.php',
    'includes/class-plan-v4-context.php',
    'includes/class-plan-v4-settings-concurrency.php',
    'includes/class-plan-v4-privacy-cache.php',
    'includes/class-plan-v4-jobs.php',
    'includes/class-plan-v4-recovery.php',
);
$main = $read('sabri-unified-application-shell.php');
foreach ($files as $file) {
    $assert(is_file($root . '/' . $file), "Missing {$file}");
    $assert(strpos($main, basename($file)) !== false, "Bootstrap missing {$file}");
}

$audit = $read($files[0]);
$assurance = $read($files[1]);
$contracts = $read($files[2]);
$context = $read($files[3]);
$settings = $read($files[4]);
$privacy = $read($files[5]);
$jobs = $read($files[6]);
$recovery = $read($files[7]);

foreach (array('previous_hash', "hash( 'sha256'", 'MAX_EVENTS', 'register_exporter', 'register_eraser', 'LOCK_TTL') as $needle) {
    $assert(strpos($audit, $needle) !== false, "Audit missing {$needle}");
}
foreach (array('emergency_disable', 'rollback_failure', 'contract_collision', 'repair_failure', 'assurance_queue') as $needle) {
    $assert(strpos($assurance, $needle) !== false, "Assurance missing {$needle}");
}
foreach (array('file-01b-registry-search', 'file-00-identity', 'file-22-create', 'file-25-visual', 'collision', 'version_compare') as $needle) {
    $assert(strpos($contracts, $needle) !== false, "Contract health missing {$needle}");
}
foreach (array("'mode'", "'reason'", "'source'", "'route_key'", "'resolved_at'", 'sabri_shell_context_descriptor') as $needle) {
    $assert(strpos($context, $needle) !== false, "Context evidence missing {$needle}");
}
foreach (array('settings_row_version', 'settings_conflict', 'pre_update_option_', 'changed_groups', 'return $old_value') as $needle) {
    $assert(strpos($settings, $needle) !== false, "Settings concurrency missing {$needle}");
}
foreach (array('private, no-store', 'noindex', 'noarchive', 'Vary: Cookie', 'litespeed_purge_all') as $needle) {
    $assert(strpos($privacy, $needle) !== false, "Privacy/cache missing {$needle}");
}
foreach (array('wp_schedule_event', 'expires', 'bounded_reconciliation', 'assurance_sent', 'finally') as $needle) {
    $assert(strpos($jobs, $needle) !== false, "Jobs missing {$needle}");
}
foreach (array('/repair/preview', '/repair/execute', '/rollback/preview', '/rollback/execute', 'dry_run', 'pre-rollback', 'smoke_test', 'recovery_lock') as $needle) {
    $assert(strpos($recovery, $needle) !== false, "Recovery missing {$needle}");
}
$assert(strpos($recovery, "0 !== strpos( \$option, 'sabri_shell_' )") !== false, 'Rollback is not restricted to File 20-owned option prefixes.');
$assert(strpos($recovery, 'expected_settings_version') !== false, 'Repair lacks stale-preview protection.');
$assert(strpos($recovery, "'status' => 409") !== false, 'Recovery lacks conflict status paths.');

if ($failures) {
    foreach ($failures as $failure) {
        fwrite(STDERR, "FAIL: {$failure}\n");
    }
    fwrite(STDERR, sprintf("File 20 completion suite: %d checks, %d failures.\n", $checks, count($failures)));
    exit(1);
}
echo sprintf("File 20 completion suite: %d PASS, 0 FAIL\n", $checks);
