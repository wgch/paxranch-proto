# Pax Ranch House — tracker

_Last updated 2026-09-28._ Deployment is a manual upload through GoDaddy cPanel; nothing in this repo deploys. "Deployed" below means live on paxranch.com. Host behaviour and the upload checklist are in [docs/memory/live-host-deployment.md](memory/live-host-deployment.md).

## Open

### Must fix before uploading the redesign
- **Invented press names in the footer.** "East Africa Traveller", "The Safari Journal" and "Town & Country Escapes" are placeholders, marked TODO in the footer of all 9 pages. Publishing them would claim press coverage that doesn't exist. Replace them with real coverage or remove the Press block.
- **The newsletter signup has no backend.** The footer form shows a thank-you but stores nothing (`site.js`, "prototype only"), so every signup would be lost without anyone knowing. Connect it to a real list or remove it.
- **Placeholder phone number.** `+254 722 000 000` appears on every page: footer contact line, WhatsApp links, contact page, JSON-LD. It is **also on the live site now**. Needs the real number(s).
- **The booking calendar is fixed at May–June 2026.** It has a 12–16 May stay already selected and 8 invented "booked" dates (`booking.html`). This is **also live now**, so visitors see only past dates. Recommended: generate the months from today, drop the invented bookings, and present it as "preferred dates" for the enquiry. The site takes enquiries rather than bookings, and has no real availability data behind it.
- **The "Legal Information" link** in the footer points to `contact.html`. Needs a real page, or remove the link.
- **Run the pre-upload permissions check** from the memory note, and upload only the public site files.

### Other
- On the live host, missing files return HTTP 500 instead of 404. That hurts SEO. The fix is a working `ErrorDocument 404`, which needs checking against the server's configuration.
- Unused images: `images/quote-band.jpg` (unused since the testimonial replaced the quote band) and `images/logo/pax-badge.svg` (the rejected badge art). Remove them, or keep them deliberately.
- The live `index.html` matches no git commit, because it was uploaded from a working copy. After the next deploy, the live site should match a known commit.

## Done
- **2026-09-28: live 403 on `booking.html` / `contact.html` fixed.** The owner reset the file permissions in cPanel. I verified in a real browser that both pages serve their content, that `enquiry.php` is live (a GET gets the expected 405 JSON reply), and that `project-brief.md` has been deleted from the server. Diagnosis and memory: PR #11 and the follow-up that added this tracker. Not verified: a real form submission end to end, since that sends a live email.
- **PRs #1–#10 (the redesign):** design system, footer, testimonial, carousel, overlay menu, enquiry page, button states, logo plate. Merged to `main`; **not deployed**.
