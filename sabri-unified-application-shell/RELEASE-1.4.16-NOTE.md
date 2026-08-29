# File 20 v1.4.16 — File01 Settings API Sanitizer Persistence Repair

## Live-first incident basis

The 1.4.16 correction exists because exact deployed File20 `1.4.15` reached a File01 reconciliation dry-run with zero blockers but the controlled apply failed on Live. File01 automatically compensated and verified restoration of all legacy mappings, the Founder option, page quarantine metadata and owner receipt stores.

Subsequent live forensics proved:

- Founder legacy page `164` is published and owner-compatible.
- The proposed `navigation.founder.page_id=164` survives every File20 specific/generic pre-update and read filter.
- Raw database and `get_option()` both remained at `0`; persistent object-cache truth matched DB.
- The registered WordPress Settings API `Settings::sanitize()` callback is tab-oriented and receives the adapter's programmatic `update_option()` value before File20 pre-update filters. Without `_active_tab`, it returns existing settings, swallowing the navigation write.

## Bounded correction

Version 1.4.16:

- advances the File01 reconciliation command contract to `1.0.1`;
- adds one private trusted File20 settings persistence helper inside the reconciliation adapter;
- explicitly applies `Settings::enforce_owned_invariants()`;
- temporarily suspends only the registered `Settings::sanitize` callback for the exact adapter-owned programmatic settings mutation;
- leaves every other WordPress/core/security/concurrency/pre-update filter active;
- restores the sanitizer in `finally`;
- uses the same trusted persistence path for execute compensation and rollback restoration;
- adds a permanent regression that reproduces the live sanitizer-swallow failure before proving corrected execute and rollback behavior.

No new feature, companion backend, database schema, content ownership, native-domain authority or nineteenth Future Shell enhancement is introduced.

## Evidence boundary

Repository/CI/package success is not Live resolution. Required sequence remains:

`green exact-head CI → deterministic 1.4.16 package → deploy → prove deployed parity → File01 dry-run blockers=0 → controlled reconciliation → verify applied state/receipts/routes → live System Check/recovery re-test`.

Until those live gates pass, the incident remains **remediation prepared, not operationally resolved**.
