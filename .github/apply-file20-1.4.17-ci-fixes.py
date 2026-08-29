from pathlib import Path
root=Path(__file__).resolve().parents[1]

def edit(rel, old, new):
    p=root/rel
    s=p.read_text(encoding='utf-8')
    n=s.count(old)
    if n!=1: raise SystemExit(f'{rel}: expected 1 match, got {n}: {old!r}')
    p.write_text(s.replace(old,new,1),encoding='utf-8')

edit('sabri-unified-application-shell/includes/class-four-plan-harmonization.php',
     '\t\tupdate_option( Defaults::OPTION_NAME, $current, false );',
     '\t\tSettings::update_programmatically( $current );')
edit('sabri-unified-application-shell/includes/class-future-shell-v5-control-guard.php',
     '\t\t\t\tupdate_option( Defaults::OPTION_NAME, $snapshot[\'settings\'], false );',
     '\t\t\t\tSettings::update_programmatically( $snapshot[\'settings\'] );')

p=root/'sabri-unified-application-shell/tests/run-four-plan-harmonization.php'
s=p.read_text(encoding='utf-8')
s=s.replace("strpos($main,'Version: 1.4.16')", "strpos($main,'Version: 1.4.17')", 1)
s=s.replace("strpos($readme,'Version: `1.4.16`')!==false&&strpos($readmetxt,'Stable tag: 1.4.16')!==false,'release docs 1.4.16'", "strpos($readme,'Version: `1.4.17`')!==false&&strpos($readmetxt,'Stable tag: 1.4.17')!==false,'release docs 1.4.17'", 1)
p.write_text(s,encoding='utf-8')

p=root/'sabri-unified-application-shell/tests/run-release-documentation-truth.php'
s=p.read_text(encoding='utf-8')
old="$assert( false !== strpos( $adapter, 'persist_settings_option' ) && false !== strpos( $adapter, 'Settings::enforce_owned_invariants' ) && false !== strpos( $adapter, 'remove_filter' ) && false !== strpos( $adapter, 'add_filter' ), 'runtime adapter contains bounded trusted sanitizer persistence path' );"
new="$settings = $read( 'includes/class-settings.php' );\n$assert( false !== strpos( $adapter, 'Settings::update_programmatically' ) && false === strpos( $adapter, 'persist_settings_option' ), 'runtime adapter delegates trusted persistence to canonical Settings owner' );\n$assert( false !== strpos( $settings, 'public static function update_programmatically' ) && false !== strpos( $settings, 'remove_filter' ) && false !== strpos( $settings, 'add_filter' ), 'canonical Settings owner contains bounded trusted sanitizer persistence path' );"
if old not in s: raise SystemExit('release documentation assertion not found')
s=s.replace(old,new,1)
p.write_text(s,encoding='utf-8')

(root/'.github/apply-file20-1.4.17-ci-fixes.py').unlink()
print('File20 1.4.17 CI/root-cause follow-up fixes applied')
