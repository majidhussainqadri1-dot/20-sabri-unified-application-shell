# File 20 Release Directory

This directory preserves **historical release material**. It is not the authoritative source of the current installable candidate.

The current repository candidate is **1.4.9** in PR #24. Current installable artifacts are generated from the exact final GitHub head by `.github/workflows/corrective-quality.yml` as `20-sabri-unified-application-shell-1.4.9-NINTH-TEN-ROUND-HARDENED.zip` together with its SHA-256, source manifest and ninth-audit test report.

Do not upload an older source-controlled ZIP merely because it appears in this directory. Use the exact-head CI artifact only after the required quality and baseline-integrity workflows are green, then complete Hostinger staging, backup/restore/rollback rehearsal and Founder acceptance before production promotion.

The exact original 1.0.0 baseline remains immutable in `SOURCE-ARCHIVE/`. Repository/CI evidence does not imply staging, live or operational acceptance.
