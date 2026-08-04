<?php
/** Standalone regression suite for File 23 Publishing Dashboard entry points. */

declare(strict_types=1);

define('ABSPATH', __DIR__ . '/');
define('SABRI_SHELL_VERSION', '1.2.1');
define('SABRI_SHELL_URL', 'https://example.test/wp-content/plugins/sabri-unified-application-shell/');

$GLOBALS['pd_logged_in'] = false;
$GLOBALS['pd_current_user'] = 0;
$GLOBALS['pd_approved'] = false;
$GLOBALS['pd_suspended'] = false;
$GLOBALS['pd_founder'] = false;
$GLOBALS['pd_publisher'] = false;
$GLOBALS['pd_view_allowed'] = false;
$GLOBALS['pd_capability'] = false;
$GLOBALS['pd_route_url'] = 'https://example.test/publishing-dashboard/';
$GLOBALS['pd_hooks'] = [];
$GLOBALS['pd_styles'] = [];
$GLOBALS['pd_scripts'] = [];
$GLOBALS['pd_localized'] = [];

function is_user_logged_in(): bool { return (bool) $GLOBALS['pd_logged_in']; }
function get_current_user_id(): int { return (int) $GLOBALS['pd_current_user']; }
function absint($value): int { return max(0, (int) $value); }
function sanitize_key($value): string { return strtolower((string) preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) $value)); }
function esc_url_raw($value, $protocols = null): string {
    unset($protocols);
    $value = (string) $value;
    return preg_match('#^https?://#i', $value) === 1 ? $value : '';
}
function esc_url($value): string { return esc_url_raw($value); }
function esc_html($value): string { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function home_url($path = '/'): string { return 'https://example.test' . ($path === '' ? '/' : $path); }
function wp_parse_url($url, $component = -1) {
    return $component === -1 ? parse_url((string) $url) : parse_url((string) $url, $component);
}
function wp_unslash($value) { return $value; }
function __($text, $domain = null): string { unset($domain); return (string) $text; }
function add_filter($hook, $callback, $priority = 10, $accepted_args = 1): void {
    $GLOBALS['pd_hooks'][] = ['filter', $hook, $callback, $priority, $accepted_args];
}
function add_action($hook, $callback, $priority = 10, $accepted_args = 1): void {
    $GLOBALS['pd_hooks'][] = ['action', $hook, $callback, $priority, $accepted_args];
}
function wp_enqueue_style($handle, $src, $deps = [], $version = false): void {
    $GLOBALS['pd_styles'][$handle] = compact('src', 'deps', 'version');
}
function wp_enqueue_script($handle, $src, $deps = [], $version = false, $footer = false): void {
    $GLOBALS['pd_scripts'][$handle] = compact('src', 'deps', 'version', 'footer');
}
function wp_localize_script($handle, $object, $data): void {
    $GLOBALS['pd_localized'][$handle] = compact('object', 'data');
}

class SPDB_Membership_Guard {
    public static function is_user_approved($user_id): bool { return $user_id > 0 && (bool) $GLOBALS['pd_approved']; }
    public static function is_user_suspended($user_id): bool { return $user_id > 0 && (bool) $GLOBALS['pd_suspended']; }
    public static function is_user_founder($user_id): bool { return $user_id > 0 && (bool) $GLOBALS['pd_founder']; }
    public static function can_user_publish($user_id): bool { return $user_id > 0 && (bool) $GLOBALS['pd_publisher']; }
    public static function can_user_view_restricted_dashboard($user_id): bool { return $user_id > 0 && (bool) $GLOBALS['pd_view_allowed']; }
}
class SPDB_Capabilities {
    public static function current_user_can($capability): bool {
        return $capability === 'spdb_view_dashboard' && (bool) $GLOBALS['pd_capability'];
    }
}
class SPDB_Dashboard_Router {
    public static function route_url($view = 'overview'): string {
        return $view === 'overview' ? (string) $GLOBALS['pd_route_url'] : '';
    }
}
class WP_Admin_Bar {
    public array $nodes = [];
    public function add_node($node): void { $this->nodes[] = $node; }
}

require_once dirname(__DIR__) . '/includes/class-publishing-dashboard-entry.php';

use Sabri\UnifiedShell\PublishingDashboardEntry;

$passed = 0;
$failed = 0;
$assert = static function (bool $condition, string $message) use (&$passed, &$failed): void {
    if ($condition) {
        $passed++;
        echo "PASS: {$message}\n";
        return;
    }
    $failed++;
    echo "FAIL: {$message}\n";
};

PublishingDashboardEntry::register();
$hooks = $GLOBALS['pd_hooks'];
$assert(count(array_filter($hooks, static fn(array $hook): bool => $hook[1] === 'sabri_public_experience/action_url' && $hook[3] === 999 && $hook[4] === 3)) === 1, 'File 25 action URL is controlled at final priority with three arguments.');
$assert(count(array_filter($hooks, static fn(array $hook): bool => $hook[1] === 'wp_enqueue_scripts')) === 1, 'Public shell assets register once.');
$assert(count(array_filter($hooks, static fn(array $hook): bool => $hook[1] === 'admin_bar_menu')) === 1, 'Account-toolbar fallback registers once.');

$assert(PublishingDashboardEntry::can_access() === false, 'Logged-out user receives no entry.');

$GLOBALS['pd_logged_in'] = true;
$GLOBALS['pd_current_user'] = 7;
$GLOBALS['pd_approved'] = true;
$GLOBALS['pd_view_allowed'] = true;
$GLOBALS['pd_capability'] = true;
$GLOBALS['pd_publisher'] = true;
$assert(PublishingDashboardEntry::can_access(8) === false, 'A profile owner mismatch cannot produce another user entry.');
$assert(PublishingDashboardEntry::can_access(7) === true, 'Approved publishing Doctor with File 23 capability receives entry.');
$assert(PublishingDashboardEntry::url() === 'https://example.test/publishing-dashboard/', 'Exact same-site File 23 route is accepted.');
$assert(PublishingDashboardEntry::profile_action_url('https://example.test/old', 'follow', 7) === 'https://example.test/old', 'Unrelated File 25 actions remain untouched.');
$assert(PublishingDashboardEntry::profile_action_url('', 'publishing_dashboard', 8) === '', 'Another profile never receives the current user dashboard action.');
$assert(PublishingDashboardEntry::profile_action_url('', 'publishing_dashboard', 7) === 'https://example.test/publishing-dashboard/', 'Own File 25 profile receives exact dashboard route.');

$GLOBALS['pd_suspended'] = true;
$assert(PublishingDashboardEntry::can_access() === false, 'Suspended account is denied even when stale capability remains.');
$GLOBALS['pd_suspended'] = false;
$GLOBALS['pd_approved'] = false;
$assert(PublishingDashboardEntry::can_access() === false, 'Pending or unapproved account is denied.');
$GLOBALS['pd_approved'] = true;
$GLOBALS['pd_publisher'] = false;
$GLOBALS['pd_founder'] = false;
$assert(PublishingDashboardEntry::can_access() === false, 'Non-Founder non-publisher is denied.');
$GLOBALS['pd_founder'] = true;
$assert(PublishingDashboardEntry::can_access() === true, 'Approved Founder receives entry.');

$GLOBALS['pd_route_url'] = 'https://evil.example/publishing-dashboard/';
$assert(PublishingDashboardEntry::url() === '', 'Cross-origin route is rejected after File 23 resolution.');
$GLOBALS['pd_route_url'] = 'https://example.test/publishing-dashboard/';

PublishingDashboardEntry::enqueue_assets();
$assert(isset($GLOBALS['pd_styles']['sabri-shell-publishing-dashboard-entry']), 'Entry stylesheet is enqueued for an authorized account.');
$assert(isset($GLOBALS['pd_scripts']['sabri-shell-publishing-dashboard-entry']), 'Entry script is enqueued for an authorized account.');
$localized = $GLOBALS['pd_localized']['sabri-shell-publishing-dashboard-entry']['data'] ?? [];
$assert(($localized['url'] ?? '') === 'https://example.test/publishing-dashboard/', 'Localized route remains exact and same-site.');
$assert(($localized['label'] ?? '') === 'Publishing Dashboard', 'Founder receives Founder label.');

$bar = new WP_Admin_Bar();
PublishingDashboardEntry::add_admin_bar_entry($bar);
$assert(count($bar->nodes) === 1 && ($bar->nodes[0]['parent'] ?? '') === 'my-account', 'Toolbar entry is mounted under the account menu.');

$source = file_get_contents(dirname(__DIR__) . '/includes/class-publishing-dashboard-entry.php');
$script = file_get_contents(dirname(__DIR__) . '/assets/js/publishing-dashboard-entry.js');
$css = file_get_contents(dirname(__DIR__) . '/assets/css/publishing-dashboard-entry.css');
$plugin = file_get_contents(dirname(__DIR__) . '/includes/class-plugin.php');
$bootstrap = file_get_contents(dirname(__DIR__) . '/sabri-unified-application-shell.php');
$readme = file_get_contents(dirname(__DIR__) . '/readme.txt');

$assert(strpos($source, "SPDB_Membership_Guard::is_user_approved") !== false && strpos($source, "SPDB_Capabilities::current_user_can( 'spdb_view_dashboard' )") !== false, 'Exact File 23 authority and capability contracts are required.');
$assert(strpos($source, "SPDB_Dashboard_Router::route_url( 'overview' )") !== false, 'No duplicate or guessed dashboard route is owned by File 20.');
$assert(strpos($source, "sabri_public_experience/action_url") !== false, 'File 25 dormant profile action is activated through its public contract.');
$assert(preg_match('/(?<!::)\bcurrent_user_can\s*\(/', $source) !== 1 && strpos($source, 'get_user_meta(') === false, 'No raw WordPress capability or metadata fallback widens File 23 authority.');
$assert(strpos($script, 'data-sabri-publishing-dashboard-entry') !== false && strpos($script, 'MutationObserver') !== false && strpos($script, '5000') !== false, 'Idempotent bounded shell mounting is present.');
$assert(strpos($script, 'innerHTML') === false && strpos($script, 'outerHTML') === false && strpos($script, 'document.write') === false, 'Entry mounting avoids unsafe HTML injection APIs.');
$assert(substr_count($css, '{') === substr_count($css, '}') && strpos($css, ':focus-visible') !== false && strpos($css, 'forced-colors') !== false, 'CSS is balanced and includes accessibility states.');
$assert(strpos($plugin, 'PublishingDashboardEntry::register();') !== false, 'Main File 20 coordinator registers the integration.');
$assert(strpos($bootstrap, '* Version: 1.2.1') !== false && strpos($bootstrap, "define( 'SABRI_SHELL_VERSION', '1.2.1' );") !== false, 'Plugin header and runtime constant agree on 1.2.1.');
$assert(strpos($readme, 'Stable tag: 1.2.1') !== false, 'WordPress stable tag agrees on 1.2.1.');

printf("Publishing Dashboard entry tests: %d passed, %d failed.\n", $passed, $failed);
exit($failed > 0 ? 1 : 0);
