from pathlib import Path
import re

root = Path('sabri-unified-application-shell')


def read(rel):
    return (root / rel).read_text(encoding='utf-8')


def write(rel, text):
    (root / rel).write_text(text, encoding='utf-8')


def replace_once(text, old, new, label):
    count = text.count(old)
    if count != 1:
        raise SystemExit(f'{label}: expected exactly one occurrence, found {count}')
    return text.replace(old, new, 1)


# Release identity.
p = root / 'sabri-unified-application-shell.php'
text = p.read_text(encoding='utf-8')
text = text.replace('Version: 1.3.0', 'Version: 1.3.1')
text = text.replace("define( 'SABRI_SHELL_VERSION', '1.3.0' );", "define( 'SABRI_SHELL_VERSION', '1.3.1' );")
p.write_text(text, encoding='utf-8')

# Remove dormant native WordPress search fallback and make the fixed direct
# desktop set conservative; all remaining primary items stay in More.
rel = 'includes/class-renderer.php'
text = read(rel)
legacy_search = '''
\t/**
\t * Render search form.
\t *
\t * @return void
\t */
\tprivate static function render_search() {
\t\t$query = get_search_query();
\t\techo '<form class="sabri-shell-search" role="search" method="get" action="' . esc_url( home_url( '/' ) ) . '">';
\t\techo '<label class="screen-reader-text" for="sabri-shell-search-field">' . esc_html__( 'Search', 'sabri-unified-application-shell' ) . '</label>';
\t\techo '<input id="sabri-shell-search-field" type="search" name="s" value="' . esc_attr( $query ) . '" placeholder="' . esc_attr__( 'Search', 'sabri-unified-application-shell' ) . '">';
\t\techo '<button type="submit" aria-label="' . esc_attr__( 'Submit search', 'sabri-unified-application-shell' ) . '"><span aria-hidden="true">&#8981;</span></button>';
\t\techo '</form>';
\t}
'''
if legacy_search not in text:
    raise SystemExit('renderer: dormant WordPress search fallback block not found')
text = text.replace(legacy_search, '\n', 1)
text = replace_once(text, '$direct = array_slice( $visible, 0, 8 );', '$direct = array_slice( $visible, 0, 6 );', 'renderer direct nav')
text = replace_once(text, '$more   = array_slice( $visible, 8 );', '$more   = array_slice( $visible, 6 );', 'renderer more nav')
write(rel, text)

# Latest directive has one top nav + drawer, no duplicate bottom strip.
rel = 'includes/class-defaults.php'
text = read(rel)
text, n = re.subn(r"\n\t\t\t\t'bottom_nav'\s*=>\s*true,", '', text)
if n < 1:
    raise SystemExit('defaults: expected stale destination bottom_nav metadata')
write(rel, text)

# Welcome: first eligible invocation once per browser session; dismissal still
# suppresses for 30 days.
rel = 'includes/class-four-plan-harmonization.php'
text = read(rel)
text = replace_once(
    text,
    "\tconst WELCOME_COOKIE         = 'sabri_shell_welcome_dismissed_at';\n\tconst WELCOME_STORAGE_KEY    = 'sabriShellWelcomeDismissedAt';",
    "\tconst WELCOME_COOKIE         = 'sabri_shell_welcome_dismissed_at';\n\tconst WELCOME_SESSION_COOKIE = 'sabri_shell_welcome_seen_session';\n\tconst WELCOME_STORAGE_KEY    = 'sabriShellWelcomeDismissedAt';\n\tconst WELCOME_SESSION_KEY    = 'sabriShellWelcomeSeenSession';\n\n\t/** @var bool Whether the current request has evaluated welcome eligibility. */\n\tprivate static $welcome_prepared = false;\n\n\t/** @var bool Whether File 13 may be invoked on this exact request. */\n\tprivate static $welcome_invoke = false;",
    'welcome constants',
)
text = replace_once(
    text,
    "\t\tadd_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ), 120 );\n\t\tadd_action( 'wp_body_open', array( __CLASS__, 'invoke_welcome_intro' ), 2 );",
    "\t\tadd_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ), 120 );\n\t\tadd_action( 'wp', array( __CLASS__, 'prepare_welcome_invocation' ), 98 );\n\t\tadd_action( 'wp_body_open', array( __CLASS__, 'invoke_welcome_intro' ), 2 );",
    'welcome registration',
)
text = replace_once(
    text,
    "\t\t\t\t\t'storageKey'      => self::WELCOME_STORAGE_KEY,\n\t\t\t\t\t'intervalSeconds' => self::WELCOME_INTERVAL_DAYS * DAY_IN_SECONDS,",
    "\t\t\t\t\t'storageKey'      => self::WELCOME_STORAGE_KEY,\n\t\t\t\t\t'sessionKey'      => self::WELCOME_SESSION_KEY,\n\t\t\t\t\t'intervalSeconds' => self::WELCOME_INTERVAL_DAYS * DAY_IN_SECONDS,",
    'welcome localization',
)
old_invoke = '''\t/**
\t * Invoke File 13 only when the centrally owned frequency gate is eligible.
\t *
\t * File 13 remains the visual/content owner. File 20 emits a versioned hook;
\t * if no provider is registered, no substitute intro is fabricated.
\t */
\tpublic static function invoke_welcome_intro() {
\t\tif ( ! self::welcome_eligible() ) {
\t\t\treturn;
\t\t}
\t\tif ( ! has_action( 'sabri_shell_welcome_intro_invoke' ) ) {
\t\t\treturn;
\t\t}
\t\t$context = array(
\t\t\t'contract_version' => self::CONTRACT_VERSION,
\t\t\t'owner'            => 'file-20-frequency-control',
\t\t\t'interval_days'    => self::WELCOME_INTERVAL_DAYS,
\t\t\t'dismiss_action'   => 'sabri_shell_welcome_dismiss',
\t\t\t'dismiss_nonce'    => wp_create_nonce( 'sabri_shell_welcome_dismiss' ),
\t\t\t'storage_key'      => self::WELCOME_STORAGE_KEY,
\t\t);
\t\tdo_action( 'sabri_shell_welcome_intro_invoke', $context );
\t}
'''
new_invoke = '''\t/**
\t * Prepare welcome before template output so a session cookie can be written
\t * without a headers-sent race. Seeing the intro marks only this session;
\t * Skip/Close/Continue starts the separate 30-day suppression interval.
\t */
\tpublic static function prepare_welcome_invocation() {
\t\tif ( self::$welcome_prepared ) {
\t\t\treturn;
\t\t}
\t\tself::$welcome_prepared = true;
\t\tself::$welcome_invoke   = false;

\t\tif ( ! self::welcome_eligible() || ! has_action( 'sabri_shell_welcome_intro_invoke' ) ) {
\t\t\treturn;
\t\t}

\t\tself::$welcome_invoke = true;
\t\tself::mark_welcome_seen_for_session();
\t}

\t/**
\t * Invoke File 13 only when this exact request was prepared as eligible.
\t * File 13 remains visual/content owner; File 20 fabricates no substitute.
\t */
\tpublic static function invoke_welcome_intro() {
\t\tif ( ! self::$welcome_invoke || ! has_action( 'sabri_shell_welcome_intro_invoke' ) ) {
\t\t\treturn;
\t\t}
\t\t$context = array(
\t\t\t'contract_version' => self::CONTRACT_VERSION,
\t\t\t'owner'            => 'file-20-frequency-control',
\t\t\t'interval_days'    => self::WELCOME_INTERVAL_DAYS,
\t\t\t'dismiss_action'   => 'sabri_shell_welcome_dismiss',
\t\t\t'dismiss_nonce'    => wp_create_nonce( 'sabri_shell_welcome_dismiss' ),
\t\t\t'storage_key'      => self::WELCOME_STORAGE_KEY,
\t\t\t'session_key'      => self::WELCOME_SESSION_KEY,
\t\t);
\t\tdo_action( 'sabri_shell_welcome_intro_invoke', $context );
\t}
'''
text = replace_once(text, old_invoke, new_invoke, 'welcome invoke block')
text = replace_once(
    text,
    "\t\tif ( ! $eligible ) {\n\t\t\treturn false;\n\t\t}\n\t\t$last = self::welcome_last_dismissed_at();",
    "\t\tif ( ! $eligible ) {\n\t\t\treturn false;\n\t\t}\n\t\tif ( self::welcome_seen_this_session() ) {\n\t\t\treturn false;\n\t\t}\n\t\t$last = self::welcome_last_dismissed_at();",
    'welcome session gate',
)
marker = "\t/** Last welcome dismissal timestamp from the authoritative applicable state. */\n"
helpers = '''\t/** Whether the intro was already invoked in this browser session. */
\tprivate static function welcome_seen_this_session() {
\t\treturn isset( $_COOKIE[ self::WELCOME_SESSION_COOKIE ] )
\t\t\t&& '1' === sanitize_text_field( wp_unslash( $_COOKIE[ self::WELCOME_SESSION_COOKIE ] ) );
\t}

\t/** Mark only this browser session as having seen the intro. */
\tprivate static function mark_welcome_seen_for_session() {
\t\t$_COOKIE[ self::WELCOME_SESSION_COOKIE ] = '1';
\t\tif ( headers_sent() ) {
\t\t\treturn;
\t\t}
\t\tsetcookie(
\t\t\tself::WELCOME_SESSION_COOKIE,
\t\t\t'1',
\t\t\tarray(
\t\t\t\t'expires'  => 0,
\t\t\t\t'path'     => defined( 'COOKIEPATH' ) && COOKIEPATH ? COOKIEPATH : '/',
\t\t\t\t'domain'   => defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '',
\t\t\t\t'secure'   => is_ssl(),
\t\t\t\t'httponly' => true,
\t\t\t\t'samesite' => 'Lax',
\t\t\t)
\t\t);
\t}

'''
if marker not in text:
    raise SystemExit('welcome helper insertion marker missing')
text = text.replace(marker, helpers + marker, 1)
write(rel, text)

# Client storage fallback and dynamic More rebalancing.
js = '''(function () {
    'use strict';

    var config = window.SabriShellFourPlan || {};
    var welcome = config.welcome || {};
    var storageKey = typeof welcome.storageKey === 'string' ? welcome.storageKey : 'sabriShellWelcomeDismissedAt';
    var sessionKey = typeof welcome.sessionKey === 'string' ? welcome.sessionKey : 'sabriShellWelcomeSeenSession';
    var interval = Number(welcome.intervalSeconds || (30 * 24 * 60 * 60));
    var resizeTimer = 0;

    function nowSeconds() {
        return Math.floor(Date.now() / 1000);
    }

    function storageRecentlyDismissed() {
        try {
            var value = Number(window.localStorage.getItem(storageKey) || 0);
            return value > 0 && (nowSeconds() - value) < interval;
        } catch (error) {
            return false;
        }
    }

    function sessionSeen() {
        try {
            return window.sessionStorage.getItem(sessionKey) === '1';
        } catch (error) {
            return false;
        }
    }

    function markSessionSeen() {
        try {
            window.sessionStorage.setItem(sessionKey, '1');
        } catch (error) {
            /* Storage failure must never block the site. */
        }
    }

    function rememberLocally() {
        try {
            window.localStorage.setItem(storageKey, String(nowSeconds()));
        } catch (error) {
            /* Storage failure must never block the site. */
        }
        markSessionSeen();
    }

    function notifyServer() {
        if (!welcome.ajaxUrl || !welcome.action || !welcome.nonce || typeof window.fetch !== 'function') {
            return;
        }
        var body = new URLSearchParams();
        body.set('action', welcome.action);
        body.set('nonce', welcome.nonce);
        window.fetch(welcome.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: body.toString()
        }).catch(function () {
            /* Server persistence failure is non-blocking; local fallback remains. */
        });
    }

    function dismiss() {
        rememberLocally();
        notifyServer();
    }

    function reconcileWelcome() {
        var nodes = document.querySelectorAll('[data-sabri-welcome-intro]');
        if (!nodes.length) {
            return;
        }
        if (storageRecentlyDismissed() || sessionSeen()) {
            nodes.forEach(function (node) {
                node.hidden = true;
                node.setAttribute('aria-hidden', 'true');
            });
            return;
        }
        markSessionSeen();
    }

    function rebalanceNavigation() {
        var nav = document.querySelector('.sabri-shell-primary-nav');
        if (!nav || window.matchMedia('(max-width: 1023px)').matches) {
            return;
        }
        var list = nav.querySelector('ul');
        if (!list) {
            return;
        }
        var more = Array.prototype.filter.call(list.children, function (child) {
            return child.classList && child.classList.contains('sabri-shell-nav-more');
        })[0];
        if (!more) {
            return;
        }
        var menu = more.querySelector('.sabri-shell-nav-more-menu');
        if (!menu) {
            return;
        }

        var direct = Array.prototype.filter.call(list.children, function (child) {
            return child !== more;
        });
        while (list.scrollWidth > list.clientWidth + 1 && direct.length > 4) {
            var item = direct.pop();
            item.setAttribute('data-sabri-nav-overflow-moved', '1');
            menu.insertBefore(item, menu.firstChild);
        }
    }

    document.addEventListener('click', function (event) {
        var trigger = event.target && event.target.closest ? event.target.closest('[data-sabri-welcome-dismiss]') : null;
        if (trigger) {
            dismiss();
        }
    }, true);

    document.addEventListener('sabri:welcome-dismissed', dismiss);
    document.addEventListener('sabri:welcome-continued', dismiss);
    document.addEventListener('sabri:welcome-skipped', dismiss);

    function ready() {
        reconcileWelcome();
        rebalanceNavigation();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', ready, { once: true });
    } else {
        ready();
    }

    window.addEventListener('resize', function () {
        window.clearTimeout(resizeTimer);
        resizeTimer = window.setTimeout(rebalanceNavigation, 80);
    }, { passive: true });
}());
'''
write('assets/js/four-plan-harmonization.js', js)

# Current release documentation.
write('README.md', '''# Sabri Unified Application Shell

Sabri Unified Application Shell is the canonical responsive WordPress application shell for the **Sabri Social Homeopathy Platform**.

- Version: `1.3.1`
- File 22 Create contract: `1.0.1`
- Central-plan contract: `1.0.0`
- Four-plan harmonization contract: `1.0.0`
- Status: repository/code/package/automated-QA candidate; Hostinger staging acceptance required
- Plugin slug/text domain: `sabri-unified-application-shell`
- Author: Dr. Allamah Majid Hussain Sabri Muhaddith Mursheed

## Canonical scope

File 20 owns the global header, single complete top navigation, mobile drawer, structural sidebars, route/layout resolver, integration slots, System Check, Complete Repair, Safe Mode, activation snapshots and rollback. It does **not** own native domain data or create duplicate membership, publishing, profile, communication, notification, clinic, marketplace, clinical, search/ranking or assurance backends.

## Version 1.3.1 — four-review closure

This release is a fresh four-review correction over 1.3.0 against Definitive Master Plan v3.0, recovered Founder directives, Continuous Value / Top-20 Superset, and File 20 v4.1. It closes the residual review findings:

1. Welcome Intro is invoked only on the first eligible request in a browser session; Skip/Close/Continue still starts the 30-day suppression interval.
2. File 26 remains the only global Search/Discovery/Ranking owner; the dormant native WordPress search fallback was removed.
3. Desktop primary navigation keeps one row and a conservative direct set, with overflow moved into the existing accessible **More** disclosure.
4. Superseded destination-level mobile-bottom-strip metadata was removed; mobile uses the canonical drawer without a duplicate bottom strip.
5. Release documentation, regression assertions and deterministic package identity are aligned to 1.3.1.

The existing 1.3.0 guarantees remain: green continuity fallback while File 25 owns visual tokens; one free tier; no donor advantage; File 23 authorized dashboard entry; File 26 fail-closed search mount; Smail/file-transfer/download UI-only contracts; Back + Home same-origin controls; File 00–26 registry; and no duplicate backend ownership.

## Layout constitution

- **Three:** Home, Worldwide Clinic directory, single doctor/clinic.
- **Two:** ordinary public pages, directories, profiles/timelines, knowledge pages and private applications.
- **Minimal:** authentication, registration, recovery, verification, feeds/system endpoints, Safe Mode and Repair.
- **Immersive:** Reels, full-screen video/live and PDF reader.

## Staging acceptance

1. Verify backup and isolated restore before installation.
2. Install/upgrade the exact candidate on Hostinger staging.
3. Activate real companion modules and run **Sabri Shell → System Check**.
4. Verify File 00/22 authorization, File 19 single bell, File 25 visual provider/fallback and File 26 search provider.
5. Test all four modes, 320–1920 px, 200–400% zoom, keyboard, screen reader, RTL/LTR, reduced motion and supported browsers.
6. Rehearse Safe Mode, Repair, backup/restore and rollback.
7. Record Founder acceptance before production promotion.

## Automated verification

The permanent GitHub workflow runs PHP 7.4/8.3 syntax, all regression/adversarial suites, JavaScript syntax, JSON/CSS/static boundaries, source/package parity, deterministic ZIP creation, manifest, SHA-256, ZIP CRC and clean extraction. Automated QA is not staging/live/operational acceptance.
''')

write('readme.txt', '''=== Sabri Unified Application Shell ===
Contributors: majidhussainqadri1-dot
Tags: application shell, navigation, layout, accessibility, homeopathy
Requires at least: 6.0
Tested up to: 7.0.1
Requires PHP: 7.4
Stable tag: 1.3.1
License: GPLv2 or later
Text Domain: sabri-unified-application-shell

Responsive canonical application shell and integration layer for the Sabri Social Homeopathy Platform.

== Description ==

File 20 owns the global header, one complete top navigation with accessible More overflow, mobile drawer, structural sidebars, exact Three/Two/Minimal/Immersive layouts, integration slots, System Check, Complete Repair, Safe Mode, snapshots and rollback. It does not create duplicate native-domain backends.

Version 1.3.1 closes a fresh four-plan audit over 1.3.0: Welcome is once per eligible browser session with 30-day post-dismissal suppression; the dormant WordPress search fallback is removed in favor of File 26 fail-closed search; desktop navigation overflow is more conservative and dynamically rebalanced into More; stale duplicate-bottom-strip metadata is removed; and release documentation/tests/package identity are aligned.

Version 1.3.0 introduced the File 26 Search/Discovery/Ranking boundary, single complete top navigation, 30-day Welcome frequency control, central green continuity fallback under File 25 ownership, one free tier/no donor advantage, File 23 Publishing Dashboard entry and Smail/file-transfer/download UI-only contracts.

Hostinger staging testing and Founder acceptance are required before production deployment.

== Installation ==

1. Back up files and database and prove restore.
2. Install or upgrade on staging only.
3. Activate required companion modules.
4. Run Sabri Shell System Check.
5. Confirm File 00/22 authorization, File 19 one-bell, File 25 visual and File 26 search contracts.
6. Test Three, Two, Minimal and Immersive contexts at 320–1920px and 200–400% zoom.
7. Test keyboard, screen reader, RTL/LTR, reduced motion, Safe Mode, Repair and rollback.
8. Complete STAGING-ACCEPTANCE.md and Founder acceptance before production.

== Shortcode ==

Use [sabri_shell_home_feed] only as a chronological compatibility fallback feed. Automatic insertion is suppressed when an authoritative platform feed exists.

== Changelog ==

= 1.3.1 =
* Enforced first-eligible Welcome invocation once per browser session while preserving 30-day dismissal suppression.
* Removed dormant native WordPress global-search fallback; File 26 remains canonical and fail-closed.
* Reduced the fixed direct desktop navigation set and added client overflow rebalancing into More.
* Removed stale destination-level duplicate-bottom-strip metadata.
* Aligned README, review register, regression checks and deterministic package identity.

= 1.3.0 =
* Harmonized File 20 against later recovered directives and Top-20 plan.
* Added File 26 Search/Discovery/Ranking contract and fail-closed header search.
* Enforced one top navigation, More overflow, mobile drawer and no duplicate bottom strip.
* Added 30-day Welcome frequency control and green continuity fallback under File 25 ownership.
* Enforced one free tier and donor-neutral presentation.
* Added File 23 dashboard entry and Smail/file-transfer/download UI-only contracts.

= 1.2.0 =
* Added exact four-mode layout constitution and File 00–25 registry.
* Made File 25 canonical visual-token owner and retired File 20 visual writes.

= 1.1.2 =
* Closed authorization, public-projection privacy and bounded-discovery defects.

= 1.1.1 =
* Added File 22 Create contract and bounded File 21 layout recovery.

= 1.1.0 =
* Corrected integration, layout, cache, notification and recovery defects.

= 1.0.0 =
* Original baseline release.
''')

write('REVIEW-CORRECTIONS.md', '''# File 20 — Four-Plan Corrective Review Traceability

## Current status

Runtime `1.3.1` is the post-four-review repository candidate. Source/package/automated QA may be accepted after exact-head CI; Hostinger staging, real companion integration, browser/accessibility acceptance, backup/restore, rollback rehearsal, Founder approval, live deployment and operational monitoring remain separate gates.

## Four fresh review rounds — 7 August 2026

### Round 1 — Definitive Master Plan v3.0 / canonical architecture

Found and corrected: stale 1.2.0 documentation identity; dormant native WordPress search fallback despite File 26 ownership; stale destination bottom-strip metadata; and an overly large fixed direct desktop nav set that increased clipping risk at small desktop/zoom.

### Round 2 — Recovered Founder directives

Found and corrected: Welcome could re-invoke on refresh/internal navigation when the user had not clicked Skip/Close/Continue. A session-only seen marker is now written before template output, with sessionStorage fallback; dismissal still starts the separate 30-day interval. Documentation was also purged of superseded duplicate mobile-bottom-strip behavior.

### Round 3 — Continuous Value / Top-20 Superset

Found and corrected: historical review documentation still described orange branding and pre-1.3.0 behavior. Current records now reflect green continuity fallback, File 25 visual ownership, one free tier, no donor advantage, File 26 Search/Discovery/Ranking and one global navigation.

### Round 4 — File 20 v4.1 fresh adversarial / release integrity

Found and corrected: permanent QA did not prove once-per-session Welcome, absence of dormant native search, absence of stale destination bottom-nav metadata, current documentation version, or the new overflow-rebalance path. The regression/static/package gates now cover those cases and the release identity advances to 1.3.1.

## Post-correction invariants

- One canonical shell; no duplicate domain backend.
- One complete top navigation; mobile drawer; no duplicate bottom strip.
- File 26 owns global Search/Discovery/Ranking; File 20 only mounts a validated same-origin versioned contract and otherwise hides search.
- File 25 owns visual tokens; File 20 uses a green continuity fallback only when File 25 is unavailable/incompatible.
- File 19 owns notification truth/delivery; File 20 renders at most one header output.
- File 22 owns create orchestration; File 00 is final authorization authority.
- Welcome: first eligible request per browser session; Skip/Close/Continue suppresses for at least 30 days.
- One free tier; voluntary donation cannot affect shell privileges or ranking.
- Smail, verified 1GB file transfer and Download Manager are UI-entry/native-owner contracts only.
- Back + Home same-origin controls remain; no generic permanent Forward control.
- Safe Mode, System Check, Repair, snapshots and rollback remain File 20 responsibilities.

## External gates still open

WordPress 7.0.1/PHP 8.3 Hostinger staging; real provider contracts/data; responsive/browser/RTL/accessibility acceptance; LiteSpeed/cache acceptance; backup/restore and rollback rehearsal; Founder acceptance; live deployment; monitoring and incident operations.
''')

# Changelog addendum.
rel = 'CHANGELOG.md'
text = read(rel)
if '## 1.3.1 — 2026-08-07' not in text:
    text = '# Changelog\n\n## 1.3.1 — 2026-08-07\n\n- Completed a fresh four-plan review over the merged 1.3.0 baseline.\n- Enforced once-per-session Welcome invocation plus 30-day post-dismissal suppression.\n- Removed dormant native WordPress search fallback and stale bottom-strip destination metadata.\n- Hardened desktop top-navigation overflow into the existing More disclosure.\n- Aligned release documentation, regression gates and deterministic package identity.\n\n' + text.replace('# Changelog\n', '', 1)
write(rel, text)

# Dedicated auditable four-round record.
write('FOUR-PLAN-REVIEW-2026-08-07.md', '''# File 20 — Four-Plan Review and Corrective Register

**Date:** 2026-08-07 (Pakistan Standard Time)  
**Corrective release:** 1.3.1  
**Repository baseline reviewed:** `537e22c3eedb239671441ba435ac4f1536f2dd4e` (merged 1.3.0)

## Governing corpus

1. Definitive Master Plan v3.0.
2. Consolidated Recovered Founder Directives v2.1 (later explicit directives control conflicts).
3. Continuous Value / Global Top-20 Superset v1.0.
4. File 20 v4.1 specific master plan.

## Review results

| Round | Focus | Defect observations | Corrected before next round |
|---|---|---:|---|
| 1 | Canonical architecture, ownership, release identity | 4 | Yes |
| 2 | Latest UX/navigation/Welcome/File26 directives | 2 | Yes |
| 3 | Top-20 value/business/brand/search consistency | 2 | Yes |
| 4 | Fresh adversarial QA, accessibility-risk and package truth | 5 | Yes |

The 13 observations include cross-cutting manifestations of **8 unique root causes**; they are not claimed as 13 unrelated bugs. Every identified repository-owned root cause was corrected and assigned regression/static evidence.

## Unique root causes closed

1. Stale release documentation/version identity.
2. Dormant native WordPress global-search fallback.
3. Welcome lacked a once-per-session seen gate before dismissal.
4. Stale destination-level bottom-navigation metadata survived after the later no-duplicate-strip directive.
5. Fixed direct desktop nav set was too aggressive for small desktop/zoom.
6. Historical review record retained superseded orange/pre-1.3 policy language.
7. Permanent QA lacked negative assertions for the new latest-directive invariants.
8. Deterministic package/report identity needed advancement after substantive corrective changes.

## Truth boundary

This register can establish repository source, review, deterministic package and automated-QA status only after exact-head CI. It **does not** establish Hostinger staging acceptance, live deployment or operational acceptance.
''')

# Extend permanent four-plan regression suite.
rel = 'tests/run-four-plan-harmonization.php'
test = read(rel)
test = test.replace('Version: 1.3.0', 'Version: 1.3.1').replace("SABRI_SHELL_VERSION', '1.3.0", "SABRI_SHELL_VERSION', '1.3.1")
needle = "$assert(strpos($harm, \"'smail'\") !== false && strpos($harm, \"'verified_file_transfer'\") !== false && strpos($harm, \"'download_manager'\") !== false, 'new UI-only ownership contracts declared');\n"
if needle not in test:
    raise SystemExit('four-plan test insertion marker missing')
extra = '''$js = file_get_contents($root . '/assets/js/four-plan-harmonization.js');
$readme = file_get_contents($root . '/README.md');
$readmetxt = file_get_contents($root . '/readme.txt');
$review = file_get_contents($root . '/REVIEW-CORRECTIONS.md');
$assert(strpos($harm, 'WELCOME_SESSION_COOKIE') !== false && strpos($harm, 'prepare_welcome_invocation') !== false && strpos($harm, 'welcome_seen_this_session') !== false, 'Welcome once-per-session server gate');
$assert(strpos($js, 'sessionStorage') !== false && strpos($js, 'markSessionSeen') !== false, 'Welcome sessionStorage fallback');
$assert(strpos($js, 'rebalanceNavigation') !== false && strpos($js, 'data-sabri-nav-overflow-moved') !== false, 'adaptive More overflow rebalance');
$assert(strpos($renderer, 'get_search_query()') === false && strpos($renderer, 'name="s"') === false, 'dormant native WordPress search fallback removed');
$assert(strpos($renderer, 'array_slice( $visible, 0, 6 )') !== false && strpos($renderer, 'array_slice( $visible, 6 )') !== false, 'conservative direct navigation set');
$assert(!preg_match("/'bottom_nav'\\s*=>\\s*true/", $defaults), 'stale destination bottom-nav metadata removed');
$assert(strpos($readme, 'Version: `1.3.1`') !== false && strpos($readmetxt, 'Stable tag: 1.3.1') !== false, 'release documentation identity 1.3.1');
$assert(strpos($review, 'green continuity fallback') !== false && stripos($review, '#FF8A1F') === false, 'review register uses current visual-policy truth');
'''
test = test.replace(needle, needle + extra, 1)
write(rel, test)

# Permanent CI/package gate advances to 1.3.1 and asserts fresh-review closures.
wf = Path('.github/workflows/corrective-quality.yml')
text = wf.read_text(encoding='utf-8')
text = text.replace('1.3.0', '1.3.1')
text = text.replace('20-sabri-unified-application-shell-1.3.1-FOUR-PLAN', '20-sabri-unified-application-shell-1.3.1-FOUR-PLAN-R4')
static_anchor = "          grep -Fq 'WELCOME_INTERVAL_DAYS  = 30' sabri-unified-application-shell/includes/class-four-plan-harmonization.php\n"
static_extra = "          grep -Fq 'WELCOME_SESSION_COOKIE' sabri-unified-application-shell/includes/class-four-plan-harmonization.php\n          grep -Fq 'prepare_welcome_invocation' sabri-unified-application-shell/includes/class-four-plan-harmonization.php\n          grep -Fq 'rebalanceNavigation' sabri-unified-application-shell/assets/js/four-plan-harmonization.js\n          grep -Fq 'sessionStorage' sabri-unified-application-shell/assets/js/four-plan-harmonization.js\n          ! grep -Fq 'get_search_query()' sabri-unified-application-shell/includes/class-renderer.php\n          ! grep -Eq \"'bottom_nav'[[:space:]]*=>[[:space:]]*true\" sabri-unified-application-shell/includes/class-defaults.php\n          grep -Fq 'Version: `1.3.1`' sabri-unified-application-shell/README.md\n          grep -Fq 'Stable tag: 1.3.1' sabri-unified-application-shell/readme.txt\n"
if static_anchor not in text:
    raise SystemExit('quality workflow static anchor missing')
text = text.replace(static_anchor, static_anchor + static_extra, 1)
text = text.replace('- 30-day Welcome frequency-control contract: PASS', '- First-eligible once-per-session Welcome + 30-day post-dismissal contract: PASS')
text = text.replace('- Single top navigation, More overflow, mobile drawer, no duplicate bottom strip: PASS', '- Single top navigation, adaptive More overflow, mobile drawer, no duplicate bottom strip: PASS')
wf.write_text(text, encoding='utf-8')
