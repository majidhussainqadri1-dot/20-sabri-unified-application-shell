# File 20 — Eighty Fresh Independent Corrective Reviews — 2026-08-09

## Audit basis

- Baseline: merged `main` File20 `1.4.10` at `7b4019091d1f83ef4cd9dc3f559abb2b3a95955d`.
- Candidate produced by this audit: `1.4.11`, settings schema `5`.
- Governing scope: File20 v5.0 + consolidated ownership/routing/recovery/QA law + Future Shell v5 exact 18-feature amendment + current cross-file evidence.
- Earlier green CI was treated as historical evidence only.
- Rule after every review: identify defect → correct the source/evidence immediately → continue against corrected state.
- Repository QA does not imply Hostinger staging, Founder acceptance, live deployment or operational acceptance.

## Round record

| Round | Result | Review result / correction |
|---:|---|---|
| 1 | DEFECT | Local File20 HomeFeed producer still existed; retired runtime ownership. |
| 2 | DEFECT | Residual local-feed defaults/shortcode state remained; forced inert File21-compatible migration state. |
| 3 | DEFECT | Appearance admin surface remained; File25-only visual ownership restored. |
| 4 | DEFECT | `header.allowed_roles` created parallel role authority; removed. |
| 5 | DEFECT | Configured Page-ID route lacked owner compatibility; owner/access/visibility validation added. |
| 6 | DEFECT | Shortcode fallback could choose an arbitrary first match; uniqueness/collision state added. |
| 7 | DEFECT | Archive precedence lacked complete public/native-owner proof; hardened. |
| 8 | DEFECT | Active-route path-only comparison could mark external routes active; origin-aware comparison added. |
| 9 | DEFECT | Navigation cache missed companion option add/delete events; invalidation expanded. |
| 10 | DEFECT | Current locale/epoch navigation transient could survive invalidation; explicit deletion added. |
| 11 | DEFECT | Published page mapping could accept non-Page posts; restricted to valid published Pages. |
| 12 | DEFECT | Login redirect accepted insufficiently normalized caller URL; strict internal same-origin redirect added. |
| 13 | DEFECT | Public profile URL could fall back to generic WP author archive; native File03/provider-only behavior enforced. |
| 14 | DEFECT | Create URL provider output lacked same-origin validation; hardened. |
| 15 | DEFECT | Internal destination URLs were not uniformly same-origin validated; normalized. |
| 16 | DEFECT | File00 membership version input did not require strict semantic version shape; added. |
| 17 | DEFECT | Malformed/`0.0.0` provider version could bypass minimum checks; fail-safe semver health added. |
| 18 | DEFECT | Arbitrary configured function names could masquerade as integrations; versioned provider contract required. |
| 19 | DEFECT | Shortcode discovery used large synchronous scans and lacked robust collision cache; bounded discovery/cache lifecycle added. |
| 20 | DEFECT | Doctor discovery used broad WordPress roles; removed domain discovery from shell. |
| 21 | DEFECT | Clinic sidebar duplicated Doctor Finder/filter semantics; removed domain ownership. |
| 22 | DEFECT | Home sidebar directly queried posts/products/research; removed domain queries. |
| 23 | DEFECT | Shell authored clinic emergency-notice content; removed clinical-content ownership. |
| 24 | DEFECT | WordPress comments were labeled as Reviews; removed false review semantics. |
| 25 | DEFECT | Raw maintenance query could force Minimal mode; query trigger removed. |
| 26 | DEFECT | Minimal matcher could match arbitrary URL segments; anchored site-relative matching added. |
| 27 | DEFECT | Immersive matcher could classify nested ordinary paths; anchored matching added. |
| 28 | NO DEFECT | Print → Minimal classification matched governing scope. |
| 29 | DEFECT | Context navigation rendered in SafeMode/Minimal/Immersive; suppressed. |
| 30 | DEFECT | Context Back invented unverified first-segment fallback; replaced with resolved navigation/safe Home fallback. |
| 31 | DEFECT | Context history persisted query/hash URLs; canonical local route persistence added. |
| 32 | NO DEFECT | Same-origin bounded context stack (20) remained correct. |
| 33 | DEFECT | Skip-link depended on JavaScript relocation; server-rendered target added. |
| 34 | DEFECT | Drawer lacked modal-dialog semantics; role/aria-modal semantics added. |
| 35 | NO DEFECT | Drawer focus trap, focus restore and Escape handling were retained. |
| 36 | DEFECT | Recovery snapshot schema fingerprint used current target rather than captured stored state; corrected. |
| 37 | DEFECT | Rollback accepted broad `sabri_shell_*` option prefix; exact owned allowlist enforced. |
| 38 | DEFECT | Schema upgrade lacked pre-upgrade recovery snapshot; added. |
| 39 | DEFECT | Activation rollback could bypass concurrency/Emergency safety; preserved monotonic and Emergency state. |
| 40 | DEFECT | Obsolete Admin Repair/Rollback/Emergency handlers/forms still shipped; retired duplicate paths. |
| 41 | DEFECT | Per-page override sanitizer omitted Immersive; corrected. |
| 42 | DEFECT | Doctor public data could fall back to WP user/display name; provider-only projection enforced. |
| 43 | DEFECT | Retired `class-home-feed.php` WP_Query backend still shipped; deleted from source/package. |
| 44 | DEFECT | Dead duplicate mobile bottom-nav renderer methods remained; removed. |
| 45 | DEFECT | Base renderer still referenced legacy appearance theme/density state; structural-only body classes retained. |
| 46 | DEFECT | Local Home shortcode migration blanked value instead of moving to File21-compatible provider shortcode; corrected. |
| 47 | DEFECT | Concurrency token depended on admin-footer JavaScript; server-side Settings API concurrency gate enforced. |
| 48 | DEFECT | Repair preview normalization differed from invariant-enforced execution; unified. |
| 49 | DEFECT | Stale Page-ID repair checked publication only, not owner compatibility; corrected. |
| 50 | DEFECT | Rollback smoke test only checked layout enum; stronger shell-owned state smoke validation added. |
| 51 | DEFECT | Removed emergency-notice defaults left sanitizer/Admin references; references retired. |
| 52 | DEFECT | Four-plan CSS redefined File25 primary token; visual-token override removed. |
| 53 | DEFECT | Base CSS retained dead File20 dark/system theme ownership; removed. |
| 54 | DEFECT | Doctor subject inferred from raw query/WP author; native provider subject required. |
| 55 | DEFECT | Shell invented appointment `doctor_id` semantics; removed. |
| 56 | DEFECT | Shell invented messaging user-query semantics; removed. |
| 57 | DEFECT | Shell authored generic clinical “Medical Safety” content; removed. |
| 58 | DEFECT | System Check treated activation-snapshot presence as PASS without integrity; integrity evidence added. |
| 59 | DEFECT | System Check flattened operational/hardening states to informational output; truthful state/severity propagated. |
| 60 | DEFECT | Duplicate-shell detection was too narrow; broadened. |
| 61 | DEFECT | System Check did not verify File21 native slot publisher registration; runtime evidence added. |
| 62 | DEFECT | Route override accepted encoded dot/slash/backslash ambiguity; rejected. |
| 63 | DEFECT | Same-site route comparison did not fully normalize scheme/port; corrected. |
| 64 | DEFECT | External allowlisted host could accept unexpected nonstandard port; restricted. |
| 65 | DEFECT | Base recovery health could false-green if latest hardener absent; critical health fails closed. |
| 66 | DEFECT | Programmatic settings writes could bypass schema-5 invariants; option-level invariant filter added. |
| 67 | DEFECT | Hardened LKG restore did not advance concurrency and had Emergency edge case; guarded current-snapshot restore corrected. |
| 68 | NO DEFECT | Protected-path overflow remained bounded and fail-closed. |
| 69 | NO DEFECT | Final PWA virtual-asset ownership/scope remained single-owner and subdirectory-safe. |
| 70 | DEFECT | REST feature update could omit/unknown release ring and default to General; ring is now required/validated fail-closed. |
| 71 | DEFECT | Public legacy `restore_lkg()` exposed a direct restore path; delegated to guarded restore. |
| 72 | DEFECT | Circuit-breaker read/modify/write race remained; mutation lock added. |
| 73 | NO DEFECT | Welcome once-per-session / dismissal behavior remained within governing contract. |
| 74 | DEFECT | Custom hide-selector sanitizer allowed catastrophic broad selectors; concrete class/id anchoring required. |
| 75 | DEFECT | Dynamic shortcode discovery transients were untracked for invalidation/uninstall; bounded registry cleanup added. |
| 76 | DEFECT | Scheduled maintenance could report success when audit pruning failed; degraded/failure evidence added. |
| 77 | NO DEFECT | Audit chain/retention/privacy-erasure flow passed this review; no stronger cryptographic claim is inferred. |
| 78 | DEFECT | File24 assurance queue had read/modify/write race; owner-token lock added. |
| 79 | DEFECT | Release identity/docs/tests/workflow/package gates were stale for 1.4.11/schema5/eighty-round source; synchronized. |
| 80 | DEFECT | Exact-head CI exposed an undefined Renderer Create gate, ordinary shell/UI leakage into Immersive mode, a stale deleted-HomeFeed bootstrap require, and multiple preservation assertions still bound to superseded schema/implementation spellings. Runtime and QA contracts were corrected; Round 80 remains classified as a defect even after the corrected exact head turns green. |

## Candidate after source corrections

- Runtime: **1.4.11**.
- Settings schema: **5**.
- Future Shell feature scope: **18/18**, unchanged.
- Historical File20 HomeFeed producer: **removed**.
- Deterministic package target: `20-sabri-unified-application-shell-1.4.11-EIGHTY-ROUND-HARDENED.zip`.
- Dedicated regression: `tests/run-eighty-round-consolidation.php`.

## Final closure rule

The final PR head must pass PHP 7.4/8.3 syntax, every repository regression/adversarial suite, JavaScript/JSON/CSS/static ownership/routing/recovery/privacy checks, deterministic production-package root/path/file-set/SHA/manifest/CRC parity, artifact upload and Baseline Archive Integrity. After merge, the same merged `main` commit must pass the quality and baseline workflows again.

Hostinger staging, real companion/browser/device/accessibility behavior, backup/restore/rollback rehearsal, Founder acceptance, live deployment and operational acceptance remain separate and are not claimed by this audit record.

## Round 80 corrective evidence

The first exact-head quality run for PR #26 failed on PHP 7.4/8.3. This was treated as a real Round-80 defect, not bypassed. Corrections include: exact CreateContract rendering; strict same-origin login redirect; Two/Three-only ordinary shell chrome/assets/Future-Shell controls; removal of the deleted HomeFeed test bootstrap dependency; schema-5 preservation assertions; current File21 feed migration truth; decoded route-security assertions; exact rollback allowlist assertions; current guarded LKG method evidence; and refreshed permanent static checks. The corrected exact head must pass both Quality and Baseline Archive Integrity before merge, followed by the same two gates on merged main.
