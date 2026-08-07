from pathlib import Path

root = Path('sabri-unified-application-shell')

main = root / 'sabri-unified-application-shell.php'
text = main.read_text(encoding='utf-8')
marker = "require_once SABRI_SHELL_PATH . 'includes/class-four-plan-harmonization.php';\n"
if "class-publishing-dashboard-entry.php" not in text:
    if marker not in text:
        raise SystemExit('main plugin harmonization require marker missing')
    text = text.replace(marker, marker + "require_once SABRI_SHELL_PATH . 'includes/class-publishing-dashboard-entry.php';\n")
reg = "\t\tSabri\\UnifiedShell\\FourPlanHarmonization::register();\n"
if "PublishingDashboardEntry::register" not in text:
    if reg not in text:
        raise SystemExit('main plugin harmonization register marker missing')
    text = text.replace(reg, reg + "\t\tSabri\\UnifiedShell\\PublishingDashboardEntry::register();\n")
main.write_text(text, encoding='utf-8')

renderer = root / 'includes/class-renderer.php'
text = renderer.read_text(encoding='utf-8')
text = text.replace("\t\t\tself::render_search();", "\t\t\tFourPlanHarmonization::render_search();")
text = text.replace(
    "\n\t\tif ( ! empty( $settings['mobile']['bottom_nav'] ) ) {\n\t\t\tself::render_mobile_bottom_nav( $settings, $nav );\n\t\t}\n",
    "\n\t\t/* The superseded duplicate mobile bottom strip is intentionally not rendered. */\n"
)
old_nav = """\tprivate static function render_primary_nav( array $nav ) {
\t\t$primary_keys = array( 'home', 'news', 'founder', 'learn', 'encyclopedia', 'doctors', 'clinic', 'video_wall', 'reels', 'pdf_library', 'radar', 'ai', 'network', 'marketplace' );
\t\techo '<nav class=\"sabri-shell-primary-nav\" aria-label=\"' . esc_attr__( 'Primary navigation', 'sabri-unified-application-shell' ) . '\" data-sabri-shell-component=\"primary-nav\">';
\t\techo '<ul>';
\t\tforeach ( $primary_keys as $key ) {
\t\t\tif ( empty( $nav[ $key ] ) || ! self::item_visible_to_user( $nav[ $key ] ) ) {
\t\t\t\tcontinue;
\t\t\t}
\t\t\tself::render_nav_item( $nav[ $key ] );
\t\t}
\t\techo '</ul>';
\t\techo '</nav>';
\t}
"""
new_nav = """\tprivate static function render_primary_nav( array $nav ) {
\t\t$primary_keys = array( 'home', 'news', 'founder', 'learn', 'encyclopedia', 'doctors', 'clinic', 'video_wall', 'reels', 'pdf_library', 'radar', 'ai', 'network', 'marketplace' );
\t\t$visible = array();
\t\tforeach ( $primary_keys as $key ) {
\t\t\tif ( ! empty( $nav[ $key ] ) && self::item_visible_to_user( $nav[ $key ] ) ) {
\t\t\t\t$visible[] = $nav[ $key ];
\t\t\t}
\t\t}
\t\t$direct = array_slice( $visible, 0, 8 );
\t\t$more   = array_slice( $visible, 8 );
\t\techo '<nav class=\"sabri-shell-primary-nav\" aria-label=\"' . esc_attr__( 'Primary navigation', 'sabri-unified-application-shell' ) . '\" data-sabri-shell-component=\"primary-nav\">';
\t\techo '<ul>';
\t\tforeach ( $direct as $item ) {
\t\t\tself::render_nav_item( $item );
\t\t}
\t\tif ( ! empty( $more ) ) {
\t\t\t$more_active = false;
\t\t\tforeach ( $more as $item ) {
\t\t\t\tif ( Navigation::is_active_url( $item['url'] ) ) {
\t\t\t\t\t$more_active = true;
\t\t\t\t\tbreak;
\t\t\t\t}
\t\t\t}
\t\t\techo '<li class=\"sabri-shell-nav-more\"><details><summary' . ( $more_active ? ' aria-current=\"page\"' : '' ) . '>' . esc_html__( 'More', 'sabri-unified-application-shell' ) . '</summary><ul class=\"sabri-shell-nav-more-menu\">';
\t\t\tforeach ( $more as $item ) {
\t\t\t\tself::render_nav_item( $item );
\t\t\t}
\t\t\techo '</ul></details></li>';
\t\t}
\t\techo '</ul>';
\t\techo '</nav>';
\t}
"""
if 'sabri-shell-nav-more' not in text:
    if old_nav not in text:
        raise SystemExit('primary nav block not found')
    text = text.replace(old_nav, new_nav)
old_profile = """\t\t\tif ( $profile_url ) {
\t\t\t\techo '<a href=\"' . esc_url( $profile_url ) . '\">' . esc_html__( 'Profile', 'sabri-unified-application-shell' ) . '</a>';
\t\t\t}
\t\t\techo '<a href=\"' . esc_url( wp_logout_url( home_url( '/' ) ) . '\">' . esc_html__( 'Log out', 'sabri-unified-application-shell' ) . '</a>';
"""
new_profile = """\t\t\tif ( $profile_url ) {
\t\t\t\techo '<a href=\"' . esc_url( $profile_url ) . '\">' . esc_html__( 'Profile', 'sabri-unified-application-shell' ) . '</a>';
\t\t\t}
\t\t\t$publishing_url = PublishingDashboardEntry::url();
\t\t\tif ( $publishing_url ) {
\t\t\t\techo '<a href=\"' . esc_url( $publishing_url ) . '\">' . esc_html( PublishingDashboardEntry::label() ) . '</a>';
\t\t\t}
\t\t\techo '<a href=\"' . esc_url( wp_logout_url( home_url( '/' ) ) . '\">' . esc_html__( 'Log out', 'sabri-unified-application-shell' ) . '</a>';
"""
if 'PublishingDashboardEntry::label()' not in text:
    if old_profile not in text:
        raise SystemExit('profile block not found')
    text = text.replace(old_profile, new_profile)
old_card = "\t\tself::render_user_card();\n\n\t\t$groups = Defaults::groups();"
new_card = """\t\tself::render_user_card();
\t\t$publishing_url = PublishingDashboardEntry::url();
\t\tif ( $publishing_url ) {
\t\t\techo '<nav class=\"sabri-shell-account-tools\" aria-label=\"' . esc_attr__( 'Publishing', 'sabri-unified-application-shell' ) . '\"><a href=\"' . esc_url( $publishing_url ) . '\">' . esc_html( PublishingDashboardEntry::label() ) . '</a></nav>';
\t\t}

\t\t$groups = Defaults::groups();"""
if 'sabri-shell-account-tools' not in text:
    if old_card not in text:
        raise SystemExit('sidebar card marker not found')
    text = text.replace(old_card, new_card)
renderer.write_text(text, encoding='utf-8')

defaults = root / 'includes/class-defaults.php'
text = defaults.read_text(encoding='utf-8')
text = text.replace('const SCHEMA_VERSION       = 2;', 'const SCHEMA_VERSION       = 3;')
text = text.replace("\t\t\t\t'bottom_nav'               => true,", "\t\t\t\t'bottom_nav'               => false,")
text = text.replace("'primary_color' => '#ff8a1f'", "'primary_color' => '#15803d'")
defaults.write_text(text, encoding='utf-8')

settings = root / 'includes/class-settings.php'
text = settings.read_text(encoding='utf-8')
text = text.replace("\t\t\tcase 'appearance':\n\t\t\t\t$output['appearance'] = self::sanitize_appearance_group( isset( $input['appearance'] ) && is_array( $input['appearance'] ) ? $input['appearance'] : array(), $existing['appearance'] );\n\t\t\t\tbreak;", "\t\t\tcase 'appearance':\n\t\t\t\t/* File 25 owns appearance; File 20 refuses parallel visual writes. */\n\t\t\t\tbreak;")
text = text.replace("\t\t$output['bottom_nav']        = self::bool_from_input( $input, 'bottom_nav', $existing['bottom_nav'] );", "\t\t$output['bottom_nav']        = false;")
text = text.replace("'#ff8a1f'", "'#15803d'")
settings.write_text(text, encoding='utf-8')

central = root / 'includes/class-central-plan-contract.php'
text = central.read_text(encoding='utf-8')
text = text.replace("'primary_color' => '#ff8a1f'", "'primary_color' => '#15803d'")
text = text.replace('/** Canonical File 00-25 responsibility registry. */', '/** Canonical File 00-26 responsibility registry. */')
row25 = "\t\t\t'25' => array( 'Sabri Unified Global Visual Experience and Design System', 'design-tokens-profiles-timelines-visual-qa', 'consume-visual-contract-no-second-shell', 'visual-provider', 'continuity-fallback' ),\n"
row26 = row25 + "\t\t\t'26' => array( 'Search Discovery and Ranking', 'federated-search-discovery-ranking-explanations-index-contracts', 'validated-header-search-mount-only', 'shared-capability', 'search-hidden-no-native-fallback' ),\n"
if "'26' => array( 'Search Discovery and Ranking'" not in text:
    if row25 not in text:
        raise SystemExit('File 25 registry row not found')
    text = text.replace(row25, row26)
central.write_text(text, encoding='utf-8')

css = root / 'assets/css/shell.css'
css.write_text(css.read_text(encoding='utf-8').replace('#ff8a1f', '#15803d'), encoding='utf-8')

for path in [root / 'readme.txt', Path('README.md'), Path('STATUS.md')]:
    if path.exists():
        text = path.read_text(encoding='utf-8')
        text = text.replace('Stable tag: 1.2.0', 'Stable tag: 1.3.0')
        text = text.replace('Runtime 1.2.0', 'Runtime 1.3.0')
        path.write_text(text, encoding='utf-8')

changelog = root / 'CHANGELOG.md'
if changelog.exists():
    text = changelog.read_text(encoding='utf-8')
    if '## 1.3.0' not in text:
        text = "## 1.3.0 — 2026-08-07\n\n- Harmonized File 20 against all four governing plans.\n- Replaced native WordPress search with a fail-closed File 26 contract.\n- Removed the duplicate mobile bottom navigation and added one-row More overflow.\n- Added 30-day Welcome invocation/frequency control while preserving File 13 visual ownership.\n- Ported the authorized File 23 Publishing Dashboard entry onto current main.\n- Enforced File 25 visual ownership, green continuity fallback, single-free-tier and donor-neutral shell policy.\n- Added Smail/file-transfer/download UI-only ownership contracts; no duplicate backend.\n\n" + text
        changelog.write_text(text, encoding='utf-8')

test = root / 'tests/run-four-plan-harmonization.php'
test.write_text(r'''<?php
$root = dirname(__DIR__);
$checks = array();
$assert = static function ($condition, $label) use (&$checks) {
    if (!$condition) { fwrite(STDERR, "FAIL: {$label}\n"); exit(1); }
    $checks[] = $label;
};
$main = file_get_contents($root . '/sabri-unified-application-shell.php');
$renderer = file_get_contents($root . '/includes/class-renderer.php');
$harm = file_get_contents($root . '/includes/class-four-plan-harmonization.php');
$central = file_get_contents($root . '/includes/class-central-plan-contract.php');
$defaults = file_get_contents($root . '/includes/class-defaults.php');
$settings = file_get_contents($root . '/includes/class-settings.php');
$css = file_get_contents($root . '/assets/css/four-plan-harmonization.css');
$basecss = file_get_contents($root . '/assets/css/shell.css');
$assert(strpos($main, 'Version: 1.3.0') !== false && strpos($main, "SABRI_SHELL_VERSION', '1.3.0") !== false, 'release identity 1.3.0');
$assert(strpos($main, 'FourPlanHarmonization::register') !== false, 'harmonization registered');
$assert(strpos($main, 'PublishingDashboardEntry::register') !== false, 'File23 entry registered');
$assert(strpos($renderer, 'FourPlanHarmonization::render_search();') !== false, 'search delegated to File26 adapter');
$assert(substr_count($renderer, 'self::render_mobile_bottom_nav(') === 0, 'duplicate mobile bottom nav not invoked');
$assert(strpos($renderer, 'sabri-shell-nav-more') !== false, 'single-row More overflow rendered');
$assert(strpos($harm, 'WELCOME_INTERVAL_DAYS  = 30') !== false, '30-day Welcome rule');
$assert(strpos($harm, 'single-free-tier') !== false && strpos($harm, "donor_advantage']     = false") !== false, 'single free tier and donor neutrality');
$assert(strpos($harm, 'sabri_shell_file26_search_contract') !== false && strpos($harm, 'same_origin_url') !== false, 'File26 owner/version/same-origin search contract');
$assert(strpos($central, "'26' => array( 'Search Discovery and Ranking'") !== false, 'canonical registry includes File26');
$assert(strpos($defaults, 'const SCHEMA_VERSION       = 3;') !== false, 'settings schema migrated');
$assert(strpos($defaults, "'bottom_nav'               => false") !== false, 'bottom nav default disabled');
$assert(strpos($settings, "output['bottom_nav']        = false") !== false, 'bottom nav cannot be re-enabled');
$assert(strpos($css, '.sabri-shell-bottom-nav') !== false && strpos($css, 'display: none !important') !== false, 'CSS duplicate-nav guard');
$assert(strpos($basecss, '#ff8a1f') === false && strpos($central, '#ff8a1f') === false, 'legacy orange runtime fallback removed');
$assert(strpos($harm, "'smail'") !== false && strpos($harm, "'verified_file_transfer'") !== false && strpos($harm, "'download_manager'") !== false, 'new UI-only ownership contracts declared');
echo 'Four-plan harmonization assertions: ' . count($checks) . " passed\n";
'''.lstrip(), encoding='utf-8')
