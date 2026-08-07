from pathlib import Path
p = Path('sabri-unified-application-shell/tests/run-central-plan-v4.php')
text = p.read_text(encoding='utf-8')
text = text.replace('* Version: 1.3.0', '* Version: 1.3.1')
text = text.replace("SABRI_SHELL_VERSION', '1.3.0'", "SABRI_SHELL_VERSION', '1.3.1'")
text = text.replace('Plugin header must be 1.3.0.', 'Plugin header must be 1.3.1.')
text = text.replace('Runtime version must be 1.3.0.', 'Runtime version must be 1.3.1.')
p.write_text(text, encoding='utf-8')
