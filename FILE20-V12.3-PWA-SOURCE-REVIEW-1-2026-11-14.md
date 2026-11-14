# File20 V12.3 — PWA static-cache Source Review 1 (frozen)

**Scope:** PR #50 ServiceWorker allowlist only; new unmerged source feature, NOT Live defect diagnosis. WordPress/Hostinger installed code, DB, migration and browser state unknown.

| ID | Severity | Evidence from generated source | Root cause | Acceptance regression |
|---|---|---|---|---|
| F20-SW-R1-001 | P1 | asset `css/foo.css` requested as `script`, or `js/foo.js` requested as `style` passes generic destination OR | Folder/extension not tied to Request.destination | negative VM tests; require matching CSS→style, JS→script |
| F20-SW-R1-002 | P1 | Same-origin static request may follow redirect to private data; response `res.type=basic` and `res.ok` can still be true | No `res.redirected/res.url` identity check | deny redirects and response URL mismatch |
| F20-SW-R1-003 | P1 | Origin `Vary: Cookie` or HTML disguised as CSS can still be cached despite `Cache-Control` omission | Only `private/no-store` checked | reject Vary Cookie/Authorization and mismatched response content-type |
| F20-SW-R1-004 | P2 | Dot-leading `.secret.css` matches broad static filename pattern | Static file allowlist permits hidden leading-dot files | deny hidden-dot names and `..` segments |

Review round 1 will be closed ONLY after all 4 source findings have exact commit fixes, emitted-SW parser and VM tests green on immutable latest head. This audit is not full File20 G9 two-round acceptance, and does not verify real offline/PWA installations.
