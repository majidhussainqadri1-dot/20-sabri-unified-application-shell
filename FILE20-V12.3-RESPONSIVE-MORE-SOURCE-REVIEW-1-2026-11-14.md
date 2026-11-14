# File20 V12.3 Responsive More — limited Source Review 1, frozen

**Scope:** Draft #48 source only (assets/js, CSS, PHP metadata, quality tests); not a Full Plugin Review, no real-browser/Live acceptance.

| ID | Severity | Exact defect / risk | Repro | Root cause |
|---|---|---|---|---|
| F20-NAV-R1-001 | P1 source | Width decision counts element widths but not CSS 4px gaps between LI, so measured "fits" can actually overflow near a breakpoint | Pure selection widths 14×80 with available around 1,120; CSS adds ≥52px | Width budget formula omits flex gaps |
| F20-NAV-R1-002 | P2 accessibility | Hidden active route is appended to More text, but explicit accessible `aria-label` and tooltip aren't synchronized after layout | Enter an active route present only in dropdown; check summary metadata | Only `textContent` and `aria-current` updated; truncation can obscure full current route |
| F20-NAV-R1-003 | P2 operational | DOM measurement requires real CSS font and viewport; source test alone cannot claim no overflow or RTL usability | 320,390,768,1024,1440 with RTL keyboard + zoom | Real-browser/renderer staging proof not available via disconnected Hostinger |

**Priority:** fix R1-001 and R1-002 with matching unit tests; preserve R1-003 as a **Staging Blocker**, not a false source-code defect closure. No deployed source change and no Live defect claimed.
