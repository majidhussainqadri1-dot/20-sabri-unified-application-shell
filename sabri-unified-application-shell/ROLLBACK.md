# Rollback

File 20 provides two current recovery mechanisms: the integrity-protected activation snapshot and the Plan v4 preview/execute recovery snapshots. Both are intentionally bounded to File-20-owned state. They are not a general WordPress/database rollback facility.

## Current Automatic Rollback Scope

Plan v4 automatic rollback may restore only the current File-20-owned allowlist:

- `sabri_shell_settings` (`Defaults::OPTION_NAME`);
- legacy File20-owned `sabri_unified_shell_settings` when present in a compatible snapshot;
- File20 Future Shell settings (`FutureShellV5::OPTION`);
- `sabri_shell_four_plan_migration`;
- `sabri_shell_future_rewrite_version`;
- `sabri_shell_flush_rewrite_rules`.

The activation snapshot is likewise File-20-only and captures the current shell settings, Future Shell settings and rewrite-flush marker within its declared boundaries.

Automatic rollback **does not restore shared WordPress front-page options** such as `show_on_front`, `page_on_front`, or `page_for_posts`. Those are outside the current File20 automatic rollback allowlist. Any site-wide WordPress configuration recovery must use a separately verified backup/manual recovery plan.

Rollback must not delete or modify:

- posts, pages, users, media, or comments;
- messages or notification records;
- appointments, clinic records, or patient data;
- marketplace data;
- companion-plugin tables or options not owned by File 20;
- shared WordPress front-page configuration as an automatic File20 rollback side effect.

## Plan v4 Admin Rollback

1. Use the File20 Repair/Rollback control on the approved recovery environment.
2. Preview the selected snapshot first. The snapshot must pass integrity, current major-version, settings-schema and snapshot-format compatibility checks.
3. Execute only after the preview is current. The target is revalidated under the File20 recovery lock before the pre-rollback snapshot is created.
4. Verify the current Emergency Disable state remains unchanged and the settings-row version remains monotonic.
5. Verify each restored File20-owned option post-write, then run System Check and confirm the rollback smoke test passes.
6. Purge File20/LiteSpeed/Hostinger caches as required and verify navigation, authentication, File21 Home/News, notifications, doctor and clinic routes.
7. Retain the pre-rollback snapshot and audit evidence until operational acceptance.

## Emergency / Safe Mode

### Query Safe Mode

Do **not** rely on a raw URL containing only:

```text
?sabri_shell_safe=1
```

Current query Safe Mode is intentionally restricted to an authenticated administrator and requires a valid `_sabri_shell_safe_nonce` generated for the `sabri_shell_safe_mode` action. Use the product/admin flow that calls `SafeMode::query_safe_mode_url()` to generate the same-site nonce-bearing URL. A raw query without that nonce must fail closed.

### Emergency configuration fallback

When an operator-controlled configuration change is required, the emergency constant remains available:

```php
define( 'SABRI_SHELL_DISABLE', true );
```

This suppresses File20 shell rendering. It does not itself restore prior state, prove root cause, or constitute incident resolution.

## Live-Incident Boundary

For a production failure, first freeze the exact live runtime/deployed files and relevant database/configuration evidence. Repository code must not be assumed to be the deployed code. After any rollback or corrected deployment, re-test the original live symptom and confirm deployed parity before calling the incident Resolved.
