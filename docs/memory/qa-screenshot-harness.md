---
name: qa-screenshot-harness
description: How to visually verify this static site — Playwright scripts in .qa/; in-app browser pane screenshots are unreliable when hidden; system Firefox headless is broken
metadata: 
  node_type: memory
  type: project
  originSessionId: ca52226e-4a4f-43c3-8159-744a6ebb4d0c
  modified: 2026-08-13T17:25:35.051Z
---

Visual QA for this repo (static HTML, no test framework) runs via **Playwright scripts in `.qa/` (gitignored)**:

- `node .qa/shoot.mjs` — full-page PNGs of all 9 pages × desktop/mobile into `.qa/`. Requires the static server on :4173 (`python3 -m http.server 4173`, or the `.claude/launch.json` "static" config) and `PLAYWRIGHT_BROWSERS_PATH="$HOME/Library/Caches/ms-playwright"`.
- `node .qa/check.mjs` — asserts no horizontal overflow, no console errors, nav badge visible across 9 pages × 4 viewports. Run this after any CSS/layout change; it caught the contact-page mobile overflow (inline `grid-template-columns` overriding the responsive collapse — beware inline styles beating media queries).
- `.qa/crop.py src out x y w h` — crop regions from the big PNGs for close inspection.
- Deps: `playwright-core` npm-installed inside `.qa/`; Chromium headless shell cached in `~/Library/Caches/ms-playwright` (installed 2026-08-13).

**Why:** (2026-08-13, applies to every project.)

- The Claude in-app Browser pane, while hidden, reports `clientWidth 0`, times out on input actions, and serves stale screenshot frames after JS scrolls — geometry/screenshot readings from a hidden pane are garbage; only navigation-time frames are fresh.
- System Firefox headless `--screenshot` silently produces no file on this Mac (even for `about:blank`, likely conflicting with the user's running Firefox instance) — don't retry it.
- Full-page captures render `background-attachment:fixed` sections (home quote band) as flat color and skip `loading=lazy` images below the fold (gallery grid) — both are capture artifacts, not site bugs.
