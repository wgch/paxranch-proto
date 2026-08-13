---
name: design-system-citw
description: "Pax Ranch design language is adapted from collectioninthewild.com — tokens, font strategy, and the ornamental button frame all live in shared.css"
metadata: 
  node_type: memory
  type: project
  originSessionId: ca52226e-4a4f-43c3-8159-744a6ebb4d0c
  modified: 2026-08-13T18:39:53.060Z
---

As of 2026-08-13 the site's design language is adapted from **collectioninthewild.com** (user-chosen reference, replacing the earlier delaire.co.za direction). `project-brief.md` was rewritten the same day to match — brief, `shared.css`, and this note should stay in agreement; `shared.css` wins on conflict.

- **Tokens** (all in `shared.css` `:root`): ivory `#f8f4f0` bg, camel `#b7916f`, light-camel `#cca77f` (footer bg), sage `#5f6859` (headings), olive `#3f5431`, terracotta `#92695d`, gray `#4f5052` (body). Legacy var names (`--bone`, `--forest`, `--clay`, `--gold`, `--charcoal`, `--muted`) are kept as aliases pointing at the new values because inline styles across all 9 pages reference them — do not delete the aliases.
- **Fonts**: headings `'Iowan Old Style'` (ships with macOS/iOS — exact match to the reference's commercial IowanOldStyleBT) with **Lora** (Google) as the cross-platform fallback; body/UI **Outfit** (Google, exact match to reference). Buying Bitstream Iowan Old Style (~$40/style, MyFonts) would make headings pixel-identical on Windows/Android — pure font-stack change, no other code edits.
- **Ornamental button frame** (double keyline + side brackets): reference uses a PNG `border-image`; ours is an **original SVG recreation** (never copy their asset) inlined as data-URIs in `shared.css` custom properties `--frame-camel/--frame-olive/--frame-white`, applied via `border-image: var(--frame-*) 45 25 37 / 24px 14px 20px`. To recolor, duplicate the data-URI with a new stroke.
- **Header**: solid ivory fixed bar, split nav (4 links | 150px gap | 3 links + Reserve), logo badge absolutely positioned overlapping the hero. The badge must stay `position:absolute` — putting it in grid flow inflates the row and pushes links below the bar (bug fixed 2026-08-13).
- **Second wave (2026-08-13, same day)** recreated more CITW patterns: inset card-style home hero (ivory margin, headline bottom-left), `.feature-split` (statement w/ `.rule-label` + white `.photo-card` w/ centred caption), `.carousel` scroll-snap card row with circular arrows, `.testimonial` ornate framed quote (flourish SVG ornaments as multi-layer backgrounds on `.t-orn` — element is oversized `inset:-26px` so corners render outside the keylines), and the full CITW footer (link cols + Press placeholders + newsletter + contact line + separate ivory `.social-strip`). The old photo quote band was REMOVED (`quote-band.jpg` now unused). Shared JS was consolidated into `site.js` (nav/reveals/newsletter/carousels); only booking's toggle and gallery's lightbox remain inline.
- **Third wave (2026-08-13, same day)**: button hover/click = swap to a `-fill` twin of the frame SVG using the border-image **`fill` slice keyword** (`border-image: var(--frame-camel-fill) 45 25 37 fill / …`) — interior + side lobes painted, text inverts; the mobile dropdown was replaced by a CITW-style **full-screen overlay menu** (hamburger on ALL viewports, serif primary links, Farmhouses chevron → sub-panel past a divider, Escape/scroll-lock); and `enquiry.html` was added — a split-screen stepped Guest Enquiry flow (property radio step → details form) posting through the existing `enquiry.js`→`enquiry.php` path, with `?house=main|cottage|estate` preselection used by deep links (footer CTA, exclusive-use band, booking "Continue to Details"). Site is now 10 pages.
- `index.html` no longer carries inline CSS — everything is consolidated in `shared.css`.
