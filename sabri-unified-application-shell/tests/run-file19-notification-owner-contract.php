<?php
/** File 20 -> File 19 notification surface and Safe Mode owner-contract regression. */
declare(strict_types=1);
$root = dirname(__DIR__);
$plugin = (string) file_get_contents($root . '/includes/class-plugin.php');
foreach (array(
    "add_filter( 'sun_file20_safe_mode_active'",
    "add_filter( 'sun_file20_notification_surface_state'",
    'SafeMode::disabled()',
    "Integrations::destination_url( 'notifications' )",
    "'contract'    => 'file20.notifications.surface.v1'",
    "'owner'       => 'file-20'",
) as $needle) {
    if (false === strpos($plugin, $needle)) {
        fwrite(STDERR, "Missing File 19 shell owner contract invariant: {$needle}\n");
        exit(1);
    }
}
if (substr_count($plugin, "add_filter( 'sun_file20_safe_mode_active'") !== 1
    || substr_count($plugin, "add_filter( 'sun_file20_notification_surface_state'") !== 1) {
    fwrite(STDERR, "File 19 shell owner contract must be registered exactly once.\n");
    exit(1);
}
echo "File20 File19 notification owner contract regression PASS\n";
