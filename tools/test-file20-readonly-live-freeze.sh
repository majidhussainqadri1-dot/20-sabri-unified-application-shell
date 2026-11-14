#!/usr/bin/env bash
# Hermetic test: fake WordPress tree and wp binary only; never contacts a site.
set -euo pipefail
tool_dir="$(cd "$(dirname "$0")" && pwd)"
root="$(mktemp -d /tmp/file20-s0-mockroot-XXXXXXXX)"
mockbin="$(mktemp -d /tmp/file20-s0-mockbin-XXXXXXXX)"
trap 'rm -rf "$root" "$mockbin"' EXIT
mkdir -p "$root/wp-content/plugins/sabri-unified-application-shell" "$mockbin"
touch "$root/wp-load.php"
echo 'test-only static fixture' > "$root/wp-content/plugins/sabri-unified-application-shell/static-fixture.txt"
cat > "$mockbin/wp" <<'MOCK'
#!/usr/bin/env bash
set -euo pipefail
while [[ "$1" == --* ]]; do shift; done
cmd="$1"; shift
case "$cmd" in
  core) [[ "$1" == version ]] || exit 90; echo '6.8.2';;
  plugin)
    case "$1" in list|get) echo 'name,status,version'; echo 'sabri-unified-application-shell,active,1.4.17';; *) exit 91;; esac;;
  theme) [[ "$1" == list ]] || exit 92; echo 'name,status,version'; echo 'mocktheme,active,1.0';;
  eval)
    [[ "$1" == *'get_option('* ]] || exit 93
    if [[ "$1" == *'settings_row_version'* ]]; then echo 'settings_row_version=2'; else echo 'settings_schema_version=5'; fi;;
  db)
    case "$1" in prefix) echo 'wp_';; query) [[ "$2" == SELECT* ]] || exit 94; echo 'sabri_shell_settings 128';; *) exit 95;; esac;;
  *) echo "FORBIDDEN_WP_ACTION:$cmd" >&2; exit 96;;
esac
MOCK
chmod 755 "$mockbin/wp"
bash -n "$tool_dir/file20-readonly-live-freeze.sh"
output="$(PATH="$mockbin:$PATH" bash "$tool_dir/file20-readonly-live-freeze.sh" "$root")"
report="$(printf '%s\n' "$output" | sed -n 's/^PRIVATE_READONLY_REPORT=//p' | head -1)"
[[ -n "$report" && -f "$report" ]] || { echo 'Missing private report' >&2; exit 1; }
[[ "$(stat -c '%a' "$report")" == 600 ]] || { echo 'Unsafe report permissions' >&2; exit 1; }
grep -q 'settings_schema_version=5' "$report"
grep -q 'settings_row_version=2' "$report"
grep -q 'FOUND_PLUGIN_DIRECTORY=sabri-unified-application-shell' "$report"
grep -q 'static-fixture.txt' "$report"
! grep -q 'FORBIDDEN_WP_ACTION' "$report"
rm -f "$report"
echo 'File20 S0 read-only collector mock PASS: no mutation, whitelisted metadata and private report.'
