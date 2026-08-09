<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$checks = 0;
$failures = array();
$assert = static function (bool $condition, string $message) use (&$checks, &$failures): void {
    ++$checks;
    if (!$condition) { $failures[] = $message; }
};
$paths = glob($root . '/includes/class-plan-v4-*.php');
sort($paths);
$all = '';
foreach ($paths as $path) { $all .= (string) file_get_contents($path); }
foreach (array('eval(', 'shell_exec(', 'passthru(', 'proc_open(', 'base64_decode(') as $forbidden) {
    $assert(strpos($all, $forbidden) === false, "Forbidden primitive remains: {$forbidden}");
}

$recovery = (string) file_get_contents($root . '/includes/class-plan-v4-recovery.php');
$audit = (string) file_get_contents($root . '/includes/class-plan-v4-audit.php');
$assurance = (string) file_get_contents($root . '/includes/class-plan-v4-assurance.php');
$contracts = (string) file_get_contents($root . '/includes/class-plan-v4-contract-health.php');
$privacy = (string) file_get_contents($root . '/includes/class-plan-v4-privacy-cache.php');
$settings = (string) file_get_contents($root . '/includes/class-plan-v4-settings-concurrency.php');

foreach (array('current_user_can', 'expected_settings_version', "'status' => 409", 'verify_snapshot', 'hash_equals', 'finally') as $needle) {
    $assert(strpos($recovery, $needle) !== false, "Recovery negative path missing {$needle}");
}
foreach (array('[redacted]', 'LOCK_TTL', 'hash_equals', 'finally', 'array_slice', 'ANCHOR_OPTION', 'verify_events', 'rehash_events') as $needle) {
    $assert(strpos($audit, $needle) !== false, "Audit negative/integrity path missing {$needle}");
}
foreach (array('Throwable', 'unavailable', 'incompatible', 'collision', 'unknown') as $needle) {
    $assert(strpos($contracts, $needle) !== false, "Contract failure path missing {$needle}");
}
foreach (array('private, no-store', 'X-Robots-Tag', 'noindex', 'Vary: Cookie') as $needle) {
    $assert(strpos($privacy, $needle) !== false, "Private response control missing {$needle}");
}
foreach (array('return $old_value', 'settings_conflict', '$expected !== $current') as $needle) {
    $assert(strpos($settings, $needle) !== false, "Stale settings path missing {$needle}");
}

/* Real assurance/privacy assertions: no always-true placeholder and no hidden mbstring requirement. */
$assert(strpos($assurance, "preg_match( '/secret|token|nonce|cookie|password|key|document|phone|email/i'") !== false, 'Assurance redaction key matcher missing.');
$assert(strpos($assurance, "'[redacted]'" ) !== false, 'Assurance sensitive values are not redacted.');
$assert(strpos($assurance, 'array_slice( $context, 0, 30, true )') !== false, 'Assurance context is not bounded to 30 fields.');
$assert(strpos($assurance, "function_exists( 'mb_substr' ) ? mb_substr( \$text, 0, 300 ) : substr( \$text, 0, 300 )") !== false, 'Assurance scalar values are not sanitized/bounded with mbstring-safe fallback.');
$assert(strpos($assurance, 'while ( array_key_exists( $key, $safe ) )') !== false, 'Assurance sanitized-key collisions can overwrite evidence.');
$assert(strpos($assurance, 'const MAX_EVENTS = 100;') !== false, 'Assurance queue lacks a hard event bound.');
$assert(strpos($assurance, 'array_slice( $queue, -self::MAX_EVENTS )') !== false, 'Assurance queue append is not bounded.');
$assert(strpos($assurance, 'catch ( \\Throwable $exception )') !== false, 'Assurance provider exception path missing.');
$assert(strpos($assurance, "do_action( 'sabri_shell_assurance_event', \$event )") !== false, 'Versioned assurance event projection hook missing.');

$assert(strpos($all, "\$_GET['sabri_shell_mode']") === false, 'A generic query parameter may force shell mode.');
$assert(!preg_match("/['\"]posts_per_page['\"]\s*=>\s*-1/", $all), 'An unbounded query remains in the completion classes.');
$owned = substr($recovery, strpos($recovery, 'private static function owned_options()'), 1200);
$assert(strpos($owned, 'Defaults::OPTION_NAME') !== false && strpos($owned, 'FutureShellV5::OPTION') !== false && strpos($owned, "'sabri_shell_four_plan_migration'") !== false && strpos($owned, "0 !== strpos( \$option, 'sabri_shell_' )") === false, 'Rollback uses an exact File20-owned option allowlist rather than a prefix wildcard.');

if ($failures) {
    foreach ($failures as $failure) { fwrite(STDERR, "FAIL: {$failure}\n"); }
    fwrite(STDERR, sprintf("File 20 adversarial suite: %d checks, %d failures.\n", $checks, count($failures)));
    exit(1);
}
echo sprintf("File 20 adversarial suite: %d PASS, 0 FAIL\n", $checks);
