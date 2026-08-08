# File 20 Release Directory

This directory preserves **historical release material**. It is not the authoritative source of the current installable candidate.

The old `20-sabri-unified-application-shell-1.1.0-RC1.zip` record is historical evidence only. The current repository candidate is **1.4.8** in PR #23 and current installable artifacts are generated from the exact GitHub head by `.github/workflows/corrective-quality.yml` as `20-sabri-unified-application-shell-1.4.8-EIGHTH-TEN-ROUND-HARDENED.zip` together with its SHA-256, source manifest and test report.

Do not upload an older source-controlled ZIP merely because it appears in this directory. Use the exact-head CI artifact after the required workflow is green, then complete Hostinger staging, backup/restore/rollback rehearsal and Founder acceptance before any production promotion.

The exact original 1.0.0 baseline remains immutable in `SOURCE-ARCHIVE/`. Repository/CI evidence does not imply staging, live or operational acceptance.
