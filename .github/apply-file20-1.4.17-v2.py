from pathlib import Path

root = Path(__file__).resolve().parents[1]
source_path = root / '.github/apply-file20-1.4.17.py'
source = source_path.read_text(encoding='utf-8')

start = source.index('# 11. QA workflow/package identity.')
end = source.index('# 12. Advance only current-release assertions')
source = source[:start] + source[end:]

old_cleanup = "for rel in ('.github/apply-file20-1.4.17.py', '.github/workflows/apply-file20-1.4.17.yml'):"
new_cleanup = "for rel in ('.github/apply-file20-1.4.17.py', '.github/apply-file20-1.4.17-v2.py'):"
if old_cleanup not in source:
    raise SystemExit('Expected transformer cleanup block was not found')
source = source.replace(old_cleanup, new_cleanup, 1)

exec(compile(source, str(source_path), 'exec'), {'__file__': str(source_path), '__name__': '__main__'})
