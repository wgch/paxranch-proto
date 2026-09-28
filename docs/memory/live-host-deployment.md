---
name: live-host-deployment
description: paxranch.com is GoDaddy Apache behind a bot challenge; manual uploads; non-world-readable files 403; probing sensitive paths trips the challenge
metadata:
  node_type: memory
  type: project
  originSessionId: ca52226e-4a4f-43c3-8159-744a6ebb4d0c
  modified: 2026-09-28T08:13:53.787Z
---

**Live host:** paxranch.com is Apache on GoDaddy shared hosting, behind a bot-protection JavaScript challenge (page title "One moment, please…"). Nothing in the repo deploys — uploads are manual via cPanel. As of 2026-09-28 the live site is still the **pre-redesign** version (none of PRs #1–#10 is deployed), and the live `index.html` matched no git commit. Open pre-deploy blockers live in `docs/tracker.md`, not here. See [[design-system-citw]] for what the redesign contains.

**Footgun — Apache 403s any file that isn't world-readable.** On 2026-09-27 `booking.html`, `contact.html` and `project-brief.md` returned 403; all three were mode 600 in the local copy while every 644 file served fine. The owner reset permissions in cPanel on 2026-09-28 and both pages were verified live in a real browser — so owner-only permissions surviving the upload is the working explanation (never seen on the server directly).
- Before any upload this must print nothing: `find . \( -path ./.git -o -path ./.qa -o -name .env \) -prune -o \( -type d -not -perm -o+rx -o -type f -not -perm -o+r \) -print` (local `images/` was 700 with 41 photos at 600 until 2026-09-27 — it would have 403'd every image).
- Never upload internal files: `project-brief.md` (deleted from the server 2026-09-28), `docs/`, `.qa/`, `.claude/`, `.vercel/`, `images/gallery.zip`.

**Host behaviour:** a missing file returns **500, not 404**. So a 500 on a file you expect to exist means "not uploaded". `enquiry.php` is live: a GET returns `{"ok":false,"error":"Method not allowed"}` (405) before touching `.env` or sending anything — a safe liveness check. Don't test by submitting a form: that sends a real email.

**How to probe safely:** never burst-request sensitive paths (`/.env`, `/.git/…`). On 2026-09-27 that tripped the bot challenge for this Mac's IP — which is also the owner's IP — and from then on curl received the challenge page (HTTP 200, `utf-8`, ~7KB) for *every* path, so every later result was invalid. The tell is `utf-8` versus Apache's own `iso-8859-1` error pages. Verify in a real browser (it passes the JS challenge) or pace requests.

**Why:** the 403 broke the live Reserve and Contact flows, and conclusions drawn from challenge-page data (e.g. "robots.txt and enquiry.php are missing") were nearly reported as findings — the second one was false.
**How to apply:** before a deploy, run the permissions check and upload only public files; after it, spot-check pages in a real browser.
