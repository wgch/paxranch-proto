---
name: live-host-deployment
description: paxranch.com is GoDaddy Apache behind a bot challenge; manual uploads; non-world-readable files 403; probing sensitive paths trips the challenge
metadata:
  node_type: memory
  type: project
  originSessionId: ca52226e-4a4f-43c3-8159-744a6ebb4d0c
  modified: 2026-09-27T20:58:29.909Z
---

**Live host (verified 2026-09-27):** paxranch.com is Apache on GoDaddy shared hosting, behind a bot-protection JavaScript challenge (page title "One moment, please…"). Nothing in the repo deploys — uploads are manual. On 2026-09-27 the live site was still the **pre-redesign** version (Cormorant fonts, inline scripts); none of the Collection-in-the-Wild work (PRs #1–#10) was live, and the live `index.html` matched no git commit (uploaded from a working copy). See [[design-system-citw]] for what the redesign contains.

**Footgun — Apache 403s any file that isn't world-readable.** On 2026-09-27 `booking.html`, `contact.html` and `project-brief.md` returned 403 (Apache-native error page, repeated before the challenge kicked in; confirmed in a real browser). All three were mode 600 in the local copy; every 644 file served fine. Strong inference (not verified — no server access): the upload preserved owner-only permissions. Server fix: cPanel File Manager → Permissions → 0644 files / 0755 dirs; check the Permissions column first to confirm 0600.
- Local copy normalized the same day: `images/` was 700 with 41 photos at 600 — the next upload by the same method would have 403'd every image. Before any upload, this must print nothing: `find . \( -path ./.git -o -path ./.qa -o -name .env \) -prune -o \( -type d -not -perm -o+rx -o -type f -not -perm -o+r \) -print`
- `project-brief.md` is an internal agent brief — it should be **deleted from the server**, not chmod'ed (it was only hidden by the accidental 600). Same for `docs/`, `.qa/`, `.claude/`, `.vercel/`, `images/gallery.zip`: don't upload them.

**Host behaviour:** a missing file returns **500, not 404** (observed pre-challenge) — so the new-site files (`site.js`, `enquiry.html`) showing 500 just means "not uploaded yet".

**How to probe safely:** never burst-request sensitive paths (`/.env`, `/.git/…`). Doing so on 2026-09-27 tripped the bot challenge for this Mac's IP — which is also the owner's IP. After that, curl receives the challenge page (HTTP 200, `utf-8`, ~7KB) for *every* path, so every later result is invalid; the tell is `utf-8` versus Apache's own `iso-8859-1` error pages. Pace requests, or verify in a real browser (it passes the JS challenge automatically).

**Why:** a 403 on two linked pages broke the live site's Reserve and Contact flows, and the wrong conclusions from challenge-page data (e.g. "robots.txt and enquiry.php missing") were nearly reported as findings.
**How to apply:** before any deploy, run the permissions check above and upload only the site files; after a deploy, spot-check pages in a real browser or with paced requests.
