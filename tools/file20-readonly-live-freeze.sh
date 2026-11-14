#!/usr/bin/env bash
# File20 V12.3 S0: read-only Hostinger/WordPress exact-deployed-state inventory.
# Authorized administrator ONLY: bash tools/file20-readonly-live-freeze.sh /absolute/wordpress/root
# No plugin activation, option update, migration, restore, log export, or sensitive value printing.
set -euo pipefail
umask 077

if [[ $# -ne 1 ]]; then echo 'Provide exactly one absolute WordPress root.' >&2; exit 2; fi
WP_ROOT="$1"
if [[ "$WP_ROOT" != /* || ! -f "$WP_ROOT/wp-load.php" || ! -d "$WP_ROOT/wp-content/plugins" ]]; then
  echo 'Refusing unverified WordPress root.' >&2; exit 2
fi
for c in wp sha256sum find sort xargs mktemp chmod php date; do
  if ! command -v "$c" >/dev/null 2>&1; then
    echo "Refusing: required read-only tool $c missing." >&2; exit 2
  fi
done

OUT="$(mktemp /tmp/file20-readonly-evidence-XXXXXXXX.txt)"
chmod 600 "$OUT"
ro_wp() { wp --path="$WP_ROOT" --skip-plugins --skip-themes "$@"; }
ro_db() { ro_wp db query "$1" --skip-column-names; }

{
  echo 'FILE 20 V12.3: READ-ONLY LIVE REALITY FREEZE'
  date -u '+utc_snapshot=%Y-%m-%dT%H:%M:%SZ'
  echo 'No deployment, migration, access-control change or private-data export performed.'
  echo '=== WordPress and CLI PHP (web PHP must be verified separately) ==='
  ro_wp core version || echo 'WORDPRESS_CORE_UNAVAILABLE'
  php -r 'echo "PHP_CLI=",PHP_VERSION,"\n";' || echo 'PHP_CLI_UNAVAILABLE'

  echo '=== Plugin metadata (name/status/version only) ==='
  ro_wp plugin list --fields=name,status,version --format=csv || echo 'PLUGIN_LIST_UNAVAILABLE'
  echo '=== Active theme metadata (name/status/version only) ==='
  ro_wp theme list --status=active --fields=name,status,version --format=csv || echo 'ACTIVE_THEME_UNAVAILABLE'
  echo '=== Known File20 plugin slugs ==='
  for slug in sabri-unified-application-shell 20-sabri-unified-application-shell; do
    printf 'slug=%s\n' "$slug"
    ro_wp plugin get "$slug" --fields=name,status,version --format=csv || echo 'MISSING_OR_INACCESSIBLE'
  done

  echo '=== Settings schema and row version only; no raw settings ==='
  ro_wp eval '$s=get_option("sabri_shell_settings",array()); echo "settings_schema_version=".(is_array($s)&&isset($s["schema_version"])?absint($s["schema_version"]):"MISSING")."\n";' || echo 'SETTINGS_SCHEMA_UNKNOWN'
  ro_wp eval '$v=get_option("sabri_shell_settings_row_version",null); echo "settings_row_version=".(is_numeric($v)?absint($v):"MISSING")."\n";' || echo 'SETTINGS_ROW_VERSION_UNKNOWN'
  echo 'WP-CLI skips ordinary plugins/themes, but may still initialize mu-plugins. Review that risk before operator invocation.'

  echo '=== Whitelisted option names/lengths ONLY; values never printed ==='
  prefix="$(ro_wp db prefix 2>/dev/null || true)"
  if [[ "$prefix" =~ ^[A-Za-z0-9_]+$ ]]; then
    table_name="$prefix""options"
    ro_db "SELECT option_name, LENGTH(option_value) AS bytes FROM $table_name WHERE option_name IN ('sabri_shell_settings','sabri_shell_settings_row_version','sabri_shell_future_v5','sabri_shell_future_rewrite_version','sabri_shell_flush_rewrite_rules','sabri_shell_activation_snapshot','sabri_shell_plan_v4_snapshots') ORDER BY option_name" || echo 'OPTIONS_METADATA_UNAVAILABLE'
  else
    echo 'DB_PREFIX_MISSING_OR_INVALID; no raw query'
  fi

  echo '=== Exact deployed file names and SHA256 only (no contents) ==='
  for slug in sabri-unified-application-shell 20-sabri-unified-application-shell; do
    dir="$WP_ROOT/wp-content/plugins/$slug"
    if [[ -L "$dir" ]]; then echo "SYMLINK_NOT_FOLLOWED=$slug"; continue; fi
    if [[ ! -d "$dir" ]]; then echo "ABSENT_PLUGIN_DIRECTORY=$slug"; continue; fi
    echo "FOUND_PLUGIN_DIRECTORY=$slug"
    ( cd "$dir"; find . -type f -print0 | LC_ALL=C sort -z | xargs -0 -r sha256sum ) || echo 'HASH_INVENTORY_FAILED'
    if [[ -f "$dir/MANIFEST.sha256" ]]; then
      ( cd "$dir"; sha256sum --status -c MANIFEST.sha256 ) && echo 'MANIFEST_SELF_CHECK_PASS' || echo 'MANIFEST_FAILED_OR_PATH_MISMATCH'
    else
      echo 'MANIFEST_ABSENT; artifact parity not established'
    fi
  done

  echo '=== Limits ==='
  echo 'No option contents, patient records, user data, session tokens, secrets or logs collected.'
  echo 'Web PHP, active runtime hooks, complete migration history, backup and real Live journeys remain unverified.'
  echo 'Repository HEAD, Deployed Version, DB Version, Migration State and Live Verification are separate fields.'
} > "$OUT" 2>&1

printf 'PRIVATE_READONLY_REPORT=%s\n' "$OUT"
echo 'Keep report private, outside web root; redact before sharing with authorized administrators.' >&2
