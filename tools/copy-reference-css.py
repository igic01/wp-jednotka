"""Copy reference hero/header/button and logo-strip rules, scoped to their widgets.
Run from any directory after updating examples. Add Elementor-only styles to
hero.css; reference-hero.css is generated rather than manually redesigned.
"""
from pathlib import Path
import re

root = Path(__file__).resolve().parents[1]

def selectors(text):
    result, start, depth = [], 0, 0
    for i, char in enumerate(text):
        if char == '(': depth += 1
        elif char == ')': depth -= 1
        elif char == ',' and depth == 0:
            result.append(text[start:i].strip()); start = i + 1
    result.append(text[start:].strip())
    return result

def copy_rules(css, wanted, scope=".jn-hero", root_selector=".hero", keyframes=()):
    css = re.sub(r'/\*.*?\*/', '', css, flags=re.S)
    output, cursor = [], 0
    while cursor < len(css):
        opening = css.find('{', cursor)
        if opening < 0: break
        heading, depth, end = css[cursor:opening].strip(), 1, opening + 1
        while end < len(css) and depth:
            if css[end] == '{': depth += 1
            elif css[end] == '}': depth -= 1
            end += 1
        body = css[opening + 1:end - 1]
        if heading.startswith('@media'):
            nested = copy_rules(body, wanted, scope, root_selector, keyframes)
            if nested: output.append(heading + ' {\n' + nested + '\n}')
        elif heading.startswith('@keyframes') and heading.split()[-1] in keyframes:
            output.append(heading + ' {' + body + '}')
        elif not heading.startswith('@'):
            selected = [s for s in selectors(heading) if wanted(s)]
            if selected:
                scoped = [s.replace(root_selector, scope, 1) if s == root_selector or s.startswith(root_selector + '::') else scope + ' ' + s for s in selected]
                output.append(',\n'.join(scoped) + ' {' + body + '}')
        cursor = end
    return '\n\n'.join(output)

variables = (root / 'examples/css/variables.css').read_text().replace(':root', '.jn-hero').replace('../assets/fonts/', '../fonts/')
# Keep reference defaults exactly, while exposing these colors to existing controls.
variables = variables.replace('#09031a 0%', 'var(--jn-gradient-top, #09031a) 0%').replace('#522fc7 56%', 'var(--jn-gradient-middle, #522fc7) 56%').replace('#f4efff 100%', 'var(--jn-gradient-bottom, #f4efff) 100%')
main = copy_rules((root / 'examples/css/main.css').read_text(), lambda s: bool(re.search(r'\.(?:button(?:-|\b)|site-header|site-nav)', s)) and 'arrow-badge' not in s)
hero = copy_rules((root / 'examples/css/pages/home.css').read_text(), lambda s: bool(re.search(r'\.hero(?:-|\b)', s)))
(root / 'assets/css/reference-hero.css').write_text('/* Copied from examples/css/{variables,main,pages/home}.css.\n   Regenerate with: python3 tools/copy-reference-css.py */\n\n' + variables + '\n\n' + main + '\n\n' + hero + '\n')

# The logo strip has no JavaScript in the reference: its loop is pure CSS.
strip = copy_rules((root / 'examples/css/pages/home.css').read_text(), lambda s: '.logo-strip' in s, '.jn-logo-strip', '.logo-strip', ('logo-strip-move',))
strip = strip.replace('logo-strip-move', 'jn-logo-strip-move').replace('rgb(15 15 15 / 86%)', 'var(--jn-logo-strip-fade, rgb(15 15 15 / 86%))')
strip_variables = '\n'.join(re.findall(r'  --logo-strip-[^;]+;', (root / 'examples/css/variables.css').read_text()))
(root / 'assets/css/reference-logo-strip.css').write_text('/* Copied from examples/css/pages/home.css and variables.css. Regenerate with tools/copy-reference-css.py. */\n.jn-logo-strip {\n' + strip_variables + '\n}\n\n' + strip + '\n')

stats = copy_rules((root / 'examples/css/pages/home.css').read_text(), lambda s: bool(re.search(r'\.(?:stats-section|stats-grid|stat-item|testimonials-|testimonial-)', s)), '.jn-stats', '.stats-section', ('testimonial-drift',))
stats += '\n\n' + copy_rules((root / 'examples/css/pages/home.css').read_text(), lambda s: s == ':root', '.jn-stats', ':root')
stats = stats.replace('testimonial-drift', 'jn-testimonial-drift').replace('../../assets/testimonials/avatar-sheet.png', '../images/testimonials/avatar-sheet.png')
stats_vars = (root / 'examples/css/variables.css').read_text().replace(':root', '.jn-stats').replace('../assets/fonts/', '../fonts/')
(root / 'assets/css/reference-stats.css').write_text('/* Copied reference stats/testimonials. Regenerate with tools/copy-reference-css.py. */\n' + stats_vars + '\n\n' + stats + '\n')

approach = copy_rules((root / 'examples/css/pages/home.css').read_text(), lambda s: bool(re.search(r'\.(?:approach-|gradient-wave-text)', s)), '.jn-approach', '.approach-section')
approach += '\n\n' + copy_rules((root / 'examples/css/pages/home.css').read_text(), lambda s: s == ':root', '.jn-approach', ':root')
approach_vars = (root / 'examples/css/variables.css').read_text().replace(':root', '.jn-approach').replace('../assets/fonts/', '../fonts/')
(root / 'assets/css/reference-approach.css').write_text('/* Copied reference approach section. Regenerate with tools/copy-reference-css.py. */\n' + approach_vars + '\n\n' + approach + '\n')

results = copy_rules((root / 'examples/css/main.css').read_text(), lambda s: s in ('.content-badge', '.content-badge-icon'), '.jn-results', '.results-section')
results += '\n\n' + copy_rules((root / 'examples/css/pages/home.css').read_text(), lambda s: bool(re.search(r'\.(?:results-|project-card(?:-|\b)|project-media)', s)), '.jn-results', '.results-section')
# Root descendants (the section heading) belong on the widget itself.
results = results.replace('.jn-results .results-section ', '.jn-results ')
results += '\n\n' + copy_rules((root / 'examples/css/pages/home.css').read_text(), lambda s: s == ':root', '.jn-results', ':root')
results_vars = (root / 'examples/css/variables.css').read_text().replace(':root', '.jn-results').replace('../assets/fonts/', '../fonts/')
(root / 'assets/css/reference-results.css').write_text('/* Copied reference results section. Regenerate with tools/copy-reference-css.py. */\n' + results_vars + '\n\n' + results + '\n')

services = copy_rules((root / 'examples/css/main.css').read_text(), lambda s: s in ('.content-badge', '.content-badge-icon'), '.jn-services', '.services-overview-section')
services += '\n\n' + copy_rules((root / 'examples/css/pages/home.css').read_text(), lambda s: bool(re.search(r'\.(?:services-overview-|service-(?:row|icon|copy|arrow))', s)), '.jn-services', '.services-overview-section')
services += '\n\n' + copy_rules((root / 'examples/css/pages/home.css').read_text(), lambda s: s == ':root', '.jn-services', ':root')
services_vars = (root / 'examples/css/variables.css').read_text().replace(':root', '.jn-services').replace('../assets/fonts/', '../fonts/')
(root / 'assets/css/reference-services.css').write_text('/* Copied reference services section. Regenerate with tools/copy-reference-css.py. */\n' + services_vars + '\n\n' + services + '\n')

clients = copy_rules((root / 'examples/css/main.css').read_text(), lambda s: s.startswith('.button'), '.jn-clients-footer', '.clients-cta-section')
clients += '\n\n' + copy_rules((root / 'examples/css/pages/home.css').read_text(), lambda s: bool(re.search(r'\.(?:clients-cta-|cursor-trail-|floating-project-card|site-footer-card|footer-)', s)), '.jn-clients-footer', '.clients-cta-section', ('cursor-trail-fade',))
clients = clients.replace('cursor-trail-fade', 'jn-clients-trail-fade').replace('#09031a 0%', 'var(--jn-cta-top, #09031a) 0%').replace('#5f2df5 46%', 'var(--jn-cta-middle, #5f2df5) 46%').replace('#ffffff 100%', 'var(--jn-cta-bottom, #ffffff) 100%')
clients += '\n\n' + copy_rules((root / 'examples/css/pages/home.css').read_text(), lambda s: s == ':root', '.jn-clients-footer', ':root')
clients_vars = (root / 'examples/css/variables.css').read_text().replace(':root', '.jn-clients-footer').replace('../assets/fonts/', '../fonts/')
(root / 'assets/css/reference-clients-footer.css').write_text('/* Copied reference client CTA and footer. Regenerate with tools/copy-reference-css.py. */\n' + clients_vars + '\n\n' + clients + '\n')
