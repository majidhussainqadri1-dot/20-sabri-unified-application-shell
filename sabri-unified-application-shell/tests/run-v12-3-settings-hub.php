<?php
/**
 * V12.3 Settings Hub isolated WordPress contract regression — PHP 7.4+.
 * Source-only test: no real database, login, deployment or native producer.
 */
define( 'ABSPATH', __DIR__ . '/' );
define( 'SABRI_SHELL_URL', 'https://example.test/wp-content/plugins/sabri-unified-application-shell/' );
define( 'SABRI_SHELL_VERSION', '1.4.17' );
$GLOBALS['v12_user_id'] = 0;
$GLOBALS['v12_descriptor'] = null;
$GLOBALS['v12_descriptor_key'] = '';
$GLOBALS['v12_queued'] = array();
function is_user_logged_in() { return $GLOBALS['v12_user_id'] > 0; }
function get_current_user_id() { return $GLOBALS['v12_user_id']; }
function home_url( $path = '/' ) { return 'https://example.test' . $path; }
function wp_parse_url( $url ) { return parse_url( $url ); }
function esc_html( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' ); }
function esc_attr( $s ) { return esc_html( $s ); }
function esc_url( $s ) { return esc_attr( $s ); }
function esc_html__( $s, $domain = '' ) { return esc_html( $s ); }
function esc_attr__( $s, $domain = '' ) { return esc_attr( $s ); }
function add_shortcode( $shortcode, $cb ) { $GLOBALS['v12_shortcode'] = array( $shortcode, $cb ); }
function add_action( $hook, $cb ) { $GLOBALS['v12_hook'] = array( $hook, $cb ); }
function is_singular() { return ! empty( $GLOBALS['v12_is_singular'] ); }
function get_post() { return (object) array( 'post_content' => $GLOBALS['v12_content'] ?? '' ); }
function has_shortcode( $content, $shortcode ) { return false !== strpos( (string) $content, '[' . $shortcode ); }
function wp_enqueue_style( $handle ) { $GLOBALS['v12_queued'][] = $handle; }
function wp_enqueue_script( $handle ) { $GLOBALS['v12_queued'][] = $handle; }
function apply_filters( $name, $value, ...$args ) {
    if ( 'sabri_shell_v12_3_settings_control' === $name && ( $args[0] ?? '' ) === $GLOBALS['v12_descriptor_key'] ) { return $GLOBALS['v12_descriptor']; }
    return $value;
}
require_once dirname( __DIR__ ) . '/includes/class-settings-hub.php';

use Sabri\UnifiedShell\SettingsHub;
$fail = array();
$ok = static function ( $condition, $what ) use ( &$fail ) {
    echo ( $condition ? 'PASS: ' : 'FAIL: ' ) . $what . "\n";
    if ( ! $condition ) { $fail[] = $what; }
};
$expected = array(
    'account.profile','account.membership','account.credentials',
    'security.sessions','security.passkeys','security.recovery',
    'privacy.consent','privacy.analytics','privacy.requests',
    'language.ui','language.content','language.edition','language.reading',
    'display.theme','display.text','display.motion','display.captions',
    'notifications.quiet','notifications.channels','notifications.focus',
    'connection.visibility','connection.data','connection.playback',
);
$catalogue = SettingsHub::catalogue();
$keys = array();
foreach ( $catalogue as $group => $record ) {
    foreach ( $record['controls'] as $key => $meta ) {
        $keys[] = $key;
        $ok( 0 === strpos( $key, $group . '.' ), 'Control bound to canonical group ' . $key );
        $ok( ! empty( $meta['owners'] ), 'Owner set is never empty ' . $key );
    }
}
$ok( 7 === count( $catalogue ), 'Seven V12.3 canonical groups.' );
$ok( $keys === $expected && 23 === count( array_unique( $keys ) ), 'Exactly 23 approved control keys in order.' );

SettingsHub::register();
$ok( SettingsHub::SHORTCODE === $GLOBALS['v12_shortcode'][0], 'Additive shortcode registered without taking over native route.' );
$ok( 'wp_enqueue_scripts' === $GLOBALS['v12_hook'][0], 'Asset pre-enqueue hook registered.' );
$GLOBALS['v12_is_singular'] = true;
$GLOBALS['v12_content'] = 'Safe page with no hub.';
SettingsHub::maybe_enqueue_assets();
$ok( empty( $GLOBALS['v12_queued'] ), 'No assets loaded early on a page without the shortcode.' );
$GLOBALS['v12_content'] = 'Content [sabri_shell_settings_hub] here';
SettingsHub::maybe_enqueue_assets();
$ok( count( $GLOBALS['v12_queued'] ) === 2, 'Confirmed shortcode page enqueues CSS and JS early.' );
$GLOBALS['v12_queued'] = array();
$guest = SettingsHub::render();
$ok( 6 === substr_count( $guest, 'data-sabri-v12-control-key=' ), 'Guest only sees six public/device categories, no account controls.' );
$guest_again = SettingsHub::render();
preg_match( '/id="(sabri-v12-settings-search-[0-9]+)"/', $guest, $first_id );
preg_match( '/id="(sabri-v12-settings-search-[0-9]+)"/', $guest_again, $second_id );
$ok( ! empty( $first_id[1] ) && ! empty( $second_id[1] ) && $first_id[1] !== $second_id[1], 'Two shortcode instances have distinct search IDs.' );
$ok( false !== strpos( $guest, 'for="' . ( $first_id[1] ?? 'NO_ID' ) . '"' ), 'First instance label targets its own input.' );
$ok( false !== strpos( $guest_again, 'for="' . ( $second_id[1] ?? 'NO_ID' ) . '"' ), 'Second instance label targets its own input.' );
$ok( false !== strpos( $guest, 'id="sabri-v12-group-1-language"' ) && false !== strpos( $guest_again, 'id="sabri-v12-group-2-language"' ), 'Group anchor IDs are separately scoped per instance.' );
$ok( false === strpos( $guest, 'security.sessions' ) && false === strpos( $guest, 'privacy.requests' ), 'Guest DOM excludes private category keys/metadata.' );
$ok( false === strpos( $guest, '<a href="https://example.test/account/' ), 'No unverified owner links are displayed.' );
$ok( in_array( 'sabri-v12-settings-hub', $GLOBALS['v12_queued'], true ), 'Settings assets enqueued only when shortcode renders.' );

$GLOBALS['v12_user_id'] = 7;
$member = SettingsHub::render();
$ok( 6 === substr_count( $member, 'data-sabri-v12-control-key=' ), 'Unverified private controls stay out of member DOM despite the 23-key source catalogue.' );
$ok( false === strpos( $member, 'Saved' ), 'No fake persisted-Saved state.' );

$spec = $catalogue['notifications']['controls']['notifications.quiet'];
$GLOBALS['v12_descriptor_key'] = 'notifications.quiet';
$GLOBALS['v12_descriptor'] = array(
    'authorized' => true, 'status' => 'available', 'owner_file' => 'File19',
    'contract_version' => '1.2.3', 'url' => 'https://example.test/notifications/settings/',
    'scope' => 'account', 'effective_state' => 'off',
);
$entry = SettingsHub::approved_descriptor( 'notifications.quiet', $spec, 7 );
$ok( 'https://example.test/notifications/settings/' === ( $entry['url'] ?? '' ), 'Authoritative matching File19 account link may be shown.' );
$ok( 'account' === ( $entry['scope'] ?? '' ) && 'off' === ( $entry['effective_state'] ?? '' ), 'Bounded state and scope projected.' );
$member_with_one_owner = SettingsHub::render();
$ok( 7 === substr_count( $member_with_one_owner, 'data-sabri-v12-control-key=' ), 'Only the one native-authorized private control joins the six public entries.' );
$ok( false !== strpos( $member_with_one_owner, 'https://example.test/notifications/settings/' ), 'Exact owner link is visible when authorized.' );
$ok( false === strpos( $member_with_one_owner, 'security.passkeys' ), 'Unverified private controls never enter searchable metadata.' );
$ok( empty( SettingsHub::approved_descriptor( 'notifications.quiet', $spec, 0 ) ), 'Guest cannot claim a private link using forged provider true.' );

foreach ( array(
    'https://evil.example/notifications/settings/',
    'javascript:alert(1)',
    'https://example.test@evil.example/foo/',
    'https://example.test/private/?token=sensitive',
    'https://example.test/private/#token',
    'https://example.test/%252e%252e/wp-admin/',
    'https://example.test/%2f%2fevil.example/',
    '//example.test/path',
) as $untrusted ) {
    $GLOBALS['v12_descriptor']['url'] = $untrusted;
    $ok( empty( SettingsHub::approved_descriptor( 'notifications.quiet', $spec, 7 ) ), 'Reject unsafe/restricted owner URL: ' . $untrusted );
}
$GLOBALS['v12_descriptor']['url'] = 'https://example.test/notifications/settings/';
$GLOBALS['v12_descriptor']['owner_file'] = 'File99';
$ok( empty( SettingsHub::approved_descriptor( 'notifications.quiet', $spec, 7 ) ), 'Unapproved owner cannot mint links.' );
$GLOBALS['v12_descriptor']['owner_file'] = 'File19';
$GLOBALS['v12_descriptor']['contract_version'] = 'latest';
$ok( empty( SettingsHub::approved_descriptor( 'notifications.quiet', $spec, 7 ) ), 'Unversioned/latest provider is rejected.' );
$GLOBALS['v12_descriptor']['contract_version'] = '1.2.3';
$GLOBALS['v12_descriptor']['authorized'] = false;
$ok( empty( SettingsHub::approved_descriptor( 'notifications.quiet', $spec, 7 ) ), 'Revoked provider authorization removes link.' );

$generic_spec = $catalogue['privacy']['controls']['privacy.consent'];
$GLOBALS['v12_descriptor_key'] = 'privacy.consent';
$GLOBALS['v12_descriptor'] = array(
    'authorized' => true, 'status' => 'available', 'owner_file' => 'native-domain',
    'contract_version' => '1.2.3', 'url' => 'https://example.test/privacy/consent/',
    'scope' => 'account', 'effective_state' => 'unknown',
);
$ok( empty( SettingsHub::approved_descriptor( 'privacy.consent', $generic_spec, 7 ) ), 'Generic native-domain placeholder cannot authorize a control.' );
$src = (string) file_get_contents( dirname( __DIR__ ) . '/includes/class-settings-hub.php' );
$ok( ! preg_match( '/\b(update_option|add_option|delete_option|register_rest_route|wp_set_auth_cookie)\s*\(/', $src ), 'No independent writer, route or authority side effects.' );
if ( $fail ) { fwrite( STDERR, count( $fail ) . " Settings Hub source contract failure(s)\n" ); exit( 1 ); }
echo "File20 V12.3 Settings Hub contract regression PASS (staging/live NOT RUN).\n";
