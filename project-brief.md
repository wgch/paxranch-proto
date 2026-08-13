# Pax Ranch House — Nuxt 3 Conversion Brief

This brief is written for an AI coding agent (Claude Code) to convert a static HTML/CSS prototype into a production-ready Nuxt 3 application. The prototype lives alongside this file in the same directory.

> **Revised 2026-08-13.** The prototype was redesigned to the Collection in the Wild language (fonts, palette, header, buttons, footer). This brief reflects the current design; `shared.css` and `docs/memory/design-system-citw.md` are the authoritative references if anything here drifts.

## Context

**Pax Ranch House** is a private 2-farmhouse retreat on a working ranch in Gilgil, Kenya. Guests book an entire farmhouse (or both) — no shared lobby, no other guests. The website's design language is adapted from [collectioninthewild.com](https://www.collectioninthewild.com/wild-villas/): luxury safari-lodge hospitality, cinematic imagery, classic old-style serif headings in muted greens, a warm ivory/camel palette, ornamental keyline buttons, and slow editorial pacing.

A static HTML prototype has already been designed and is the source of truth for layout, copy, colors, typography, and interaction patterns. Your job is to convert it to Nuxt 3 **without redesigning it** — preserve the visual output exactly, but restructure the code idiomatically.

## Source files

**The repo root is the prototype and the source of truth** — real Pax Ranch photography, current design system, no placeholder images. (An earlier revision of this brief pointed at a `pax-ranch-site/` subfolder; that folder no longer exists.)

```
index.html          Home
the-estate.html     The Estate
farmhouses.html     The Farmhouses (Main House + Cottage + Exclusive Use)
experiences.html    Experiences (5 numbered experience blocks)
dining.html         Dining (chef, garden, sample day)
gallery.html        Gallery (lazy-loaded masonry grid + lightbox)
journal.html        Journal (editorial grid)
booking.html        Reserve (tabbed: availability calendar + enquiry form)
enquiry.html        Guest Enquiry (split-screen stepped flow: property picker → details form)
contact.html        Contact (details + form)
shared.css          The design system + all shared styles (all pages link it)
site.js             Shared behaviour: nav, scroll state, reveals, footer newsletter, carousels
enquiry.js          Form submission (posts to enquiry.php, inline status states)
enquiry.php         Resend-backed mail endpoint (API key in .env — never commit it)
images/             Real Pax Ranch photos (~64MB incl. gallery/, gallery/thumbs/,
                    gallery/full/, logo/)
```

All pages link `shared.css`; `index.html` no longer carries inline styles. Page-specific `<style>` blocks remain in a few pages (farmhouses meta/gallery grid, experiences blocks, booking calendar, gallery lightbox, journal grid) — port those into their page components.

### Images — already real, already optimized

All `<img src>` and `background-image` references point to local files in `images/` — real photos of the actual property, resized, auto-oriented, metadata-stripped. **Copy the entire `images/` folder into `public/images/` and the existing relative paths (`images/hero-home.jpg` etc.) will mostly work as-is** — just verify the leading path resolves from Nuxt's `public/` root.

Filenames are semantic and map directly to the component specs below: `hero-{page}.jpg`, `home-*.jpg`, `estate-*.jpg`, `house-main-*.jpg`, `house-cottage-*.jpg`, `experience-*.jpg`, `exp-*.jpg` (home-page grid), `dining-*.jpg`, `journal-*.jpg`, `gallery/gallery-NN.jpg` (+ `gallery/thumbs/`, `gallery/full/` variants), `logo/pax-logo-transparent-500.png` (header badge, footer watermark). `quote-band.jpg` is currently unused (the photo quote band was replaced by the framed testimonial).

**Known photography gaps** — the supplied photo set had no horse, guest-room interior, or food/dining images. The current site uses the best available substitutes in those spots (stables signage for horseback riding, a red barn and a tree-shadow door for dining, building exteriors for the farmhouses). These are intentional placeholders pending a proper shoot — leave a `<!-- TODO: replace with commissioned photo -->` comment at each of these locations rather than swapping in stock. Affected: the horseback experience block, both farmhouse interior galleries, and the dining page section images.

When you install `@nuxt/image` (see Stretch goals), the optimization step is mostly redundant for these files but still gives you responsive srcsets for free.

## Target stack

- **Nuxt 3** (latest stable), TypeScript
- **Vue 3** `<script setup>` syntax throughout
- No UI framework — plain CSS is intentional and matches the brand
- **Google Fonts**: Lora (400/500 + italics, heading fallback) + Outfit (300/400/500), loaded via `nuxt.config.ts` → `app.head.link`. Headings prefer the **system font `'Iowan Old Style'`** (ships with macOS/iOS — the exact face the reference site licenses); Lora is the cross-platform fallback. Do not self-host Iowan Old Style BT unless a license is purchased.
- No state management library needed
- **Nuxt Content** (optional nice-to-have) for the Journal — see "Stretch goals"

## Project structure to produce

```
nuxt.config.ts
app.vue
assets/
  css/
    main.css              ← design tokens + base styles from shared.css
layouts/
  default.vue             ← wraps <AppHeader /> + <slot /> + <AppFooter />
pages/
  index.vue
  the-estate.vue
  farmhouses.vue
  experiences.vue
  dining.vue
  gallery.vue
  journal.vue
  booking.vue
  enquiry.vue
  contact.vue
components/
  AppHeader.vue           ← solid ivory bar, split nav, overlapping logo badge
  AppFooter.vue           ← camel footer, watermark shield, 4 columns
  PageHero.vue            ← full-bleed hero below the header bar
  SplitBlock.vue          ← 2-column image/copy block, `reverse` prop
  ExperienceCard.vue      ← numbered image card with gradient shade + label
  ExperienceBlock.vue     ← used on experiences.vue — number, eyebrow, heading, body, image, `flip` prop
  AppButton.vue           ← ornamental keyline frame; variants: default (camel), light (white), solid (olive)
  FormField.vue           ← underline-style label + input/textarea/select
  SectionHeader.vue       ← eyebrow + h2 + divider (the repeating intro pattern)
  FeatureSplit.vue        ← statement text (rule-label + camel serif h2) beside a PhotoCard, `flip` prop
  PhotoCard.vue           ← white-matted image card with centred kicker/title/tags caption + button
  CardCarousel.vue        ← scroll-snap card row with circular prev/next arrows (home experiences)
  TestimonialFrame.vue    ← ornate double-keyline quote frame on camel (SVG flourish ornaments)
  GalleryGrid.vue         ← lazy-loaded thumbs masonry + lightbox (gallery.vue)
  BookingCalendar.vue     ← 2-month view with booked/selected states
  EnquiryForm.vue         ← the long form used on booking + contact
composables/
  useScrollReveal.ts      ← IntersectionObserver wrapper → adds .in class
  useScrollNav.ts         ← toggles nav .scrolled class past 60px (shadow only)
public/
  images/                 ← leave README explaining image replacement (see below)
server/
  api/enquiry.post.ts     ← port of enquiry.php (Resend; env var placeholders)
```

## Design tokens (put in `assets/css/main.css` as CSS custom properties)

```css
:root {
  /* Palette — Collection in the Wild adaptation */
  --ivory:      #f8f4f0;   /* page background */
  --ivory-2:    #f1eae2;   /* alternate section background */
  --camel:      #b7916f;   /* primary accent — buttons, eyebrows, nav links */
  --camel-2:    #cca77f;   /* light camel — footer background */
  --sage:       #5f6859;   /* heading colour */
  --olive:      #3f5431;   /* deep green — dark bands, hovers, booking intro */
  --terracotta: #92695d;   /* secondary accent, error text */
  --gray:       #4f5052;   /* body text */
  --ink:        #2d2c2b;   /* near-black */
}
```

`shared.css` also aliases the pre-redesign names (`--cream`, `--bone`, `--sand`, `--clay`, `--gold`, `--charcoal`, `--muted`, `--forest`) to these values because inline styles in the HTML still reference them. When porting, prefer migrating usages to the new names; keep the aliases only while any old references remain.

Typography:
- Headings: `'Iowan Old Style', 'Iowan Old Style BT', 'Lora', Georgia, serif`, weight 400, line-height 1.2–1.28, sentence case, color `var(--sage)` (white on heroes/dark bands)
- Body: `'Outfit', sans-serif`, weight 300–400, 16px, line-height 1.65, letter-spacing .01em, color `var(--gray)`
- Eyebrows: Outfit, uppercase, 0.74rem, letter-spacing .16em, color `var(--camel)`
- Buttons & nav links: Outfit, uppercase, 0.72–0.8rem, letter-spacing .045–.05em — tracking is deliberately tight; do not reintroduce the old .3em spacing

Spacing system:
- Section vertical padding: 110px (`padding: 110px 48px`; 76px 22px under 860px)
- Horizontal max-width container: 1200px
- Narrow prose container: 760px
- Breakpoints: 1250px (nav compress), 1024px (hamburger menu), 860px (stacking), 700px (forms/meta)

Motion:
- Hero slow-zoom on load: `transform:scale(1.05) → scale(1)`, 14s ease-out
- Scroll reveal: `opacity 0 → 1` + `translateY(30px) → 0`, 1.2s ease, triggered at threshold 0.15
- Nav: solid at all times; `.scrolled` (past 60px) only adds a soft shadow
- Hover on image: `transform: scale(1) → scale(1.04)` over 1.5s
- All of the above disabled under `prefers-reduced-motion: reduce` (already in shared.css — preserve)

## The ornamental button frame

The signature button treatment is a **double-keyline frame with elliptical side brackets**, drawn as three inline-SVG data-URIs in `shared.css` (`--frame-camel`, `--frame-olive`, `--frame-white`) and applied with:

```css
border: 0;
border-image: var(--frame-camel) 45 25 37 / 24px 14px 20px;
```

**Hover/click states**: each colorway has a `-fill` twin (`--frame-camel-fill` etc.) where the interior region — inner keyline plus the side lobes — is painted solid; on `:hover`/`:focus-visible` the border-image swaps to the twin **with the `fill` slice keyword** (`border-image: var(--frame-camel-fill) 45 25 37 fill / 24px 14px 20px`) and the text inverts. Mapping: default → camel fill/white text; `light` → white fill/camel text; `solid` → olive fill/ivory text. `:active` adds a 1px translateY press. The gap between the keylines stays transparent so the page background shows through, matching the reference's plaque effect.

This artwork is **original** (the reference site uses a licensed PNG; ours is a redrawn SVG — do not substitute theirs). To add a colorway, duplicate the data-URIs and change the `stroke`/`fill`. The nav "Reserve" CTA uses the same frame + states at slightly smaller padding.

## Component specs (the ones worth calling out)

### AppHeader.vue + MenuOverlay.vue
**Solid ivory bar at all times** (no transparent-over-photo state), fixed, 76px tall (66px under 1024px); `body` carries matching top padding. Layout is a 3-column grid — `1fr 150px 1fr`:
- Left: a **hamburger button (always visible, all viewports)** then the `<ul>` — The Estate / Farmhouses / Experiences / Dining. Items separated by 3px square camel dots (`li + li::before`). Outfit, uppercase, .8rem, tracking .045em, camel; hover olive.
- Center: the **logo badge** — a 104px ivory circle (`position:absolute`, centered, `top:16px`) with soft shadow and the Pax shield (`images/logo/pax-logo-transparent-500.png`), deliberately overlapping down onto the hero image below. Keep it absolutely positioned: placing it in grid flow inflates the row and pushes the links below the bar.
- Right: Gallery / Journal / Contact + the framed "Reserve" CTA.
- `.scrolled` (past 60px, via `useScrollNav`) adds only a box-shadow. The old `forceScrolled` prop is obsolete — the bar is always solid.
- Under 1024px the inline link lists hide; the hamburger + Reserve remain (badge shrinks to 76px).

The hamburger opens **MenuOverlay.vue** — a full-screen ivory overlay (fade in, body scroll locked, Escape closes): "✕ Close Menu" top-left; large serif camel primary links (The Estate / Farmhouses / Experiences / Dining) where **Farmhouses is a chevron toggle** revealing serif sage sub-links (The Main House / The Cottage / Exclusive Use → farmhouses anchors `#main-house` `#cottage` `#exclusive`) in a right-hand panel past a thin vertical divider; below, an uppercase secondary list (Gallery / Journal / Rates & Availability / Contact & Directions) and a framed "Make an Enquiry" button → `/enquiry`.

### PageHero.vue
Props: `image` (URL), `eyebrow` (string), `title` (slot, allows `<em>` for italics). Renders a 66vh min-480px section **below the header bar** (not behind it) with a background image, gradient overlay `linear-gradient(180deg, rgba(20,16,10,.18) 0%, rgba(20,16,10,.08) 45%, rgba(20,16,10,.5) 100%)`, white centered content aligned to bottom, slow zoom animation. The header's logo badge overlaps the hero's top edge — no hero-side markup needed.

The home page uses an **inset card variant**: a 14px ivory margin frames the image (`padding:14px 14px 0` on the section), height `calc(100vh - 76px - 14px)` min 560px, and the eyebrow/headline/button sit **bottom-left** (left-aligned) with the scroll hint bottom-right. Expose a `variant: "home" | "page"` prop.

### SplitBlock.vue
Props: `image` (URL), `alt`, `reverse` (boolean), plus `eyebrow`, `heading`, and default slot for body copy. Grid `1fr 1fr` with 80px gap, image aspect-ratio 4:5 with slow scale-on-hover, collapses to single column under 860px.

### ExperienceBlock.vue (experiences.vue)
Full-bleed 2-column block (no container). Props: `number` ("— 01"), `eyebrow`, `heading`, `image`, `flip`. Number is serif italic camel. Text side padded 80px/60px. Image side is `min-height:400px` with background-image cover. 5 of these stack on the experiences page, alternating flip.

### GalleryGrid.vue (gallery.vue)
27 photos. CSS-columns masonry (3 / 2 / 1 columns at 1300/900/560px) of `images/gallery/thumbs/gallery-NN.jpg` with `loading="lazy"`; clicking opens a lightbox that swaps in `images/gallery/gallery-NN.jpg` full-size, with prev/next, counter, Escape/arrow keys, and body scroll lock. Port the logic from `gallery.html`'s inline script.

### BookingCalendar.vue
Two-month grid. Internal state: `selectedRange: { start, end }`, `bookedDates: Date[]`. Render cells with classes: `off` (prev/next month), `booked` (unavailable), `sel` (selected endpoints — olive bg, ivory text), `range` (in-between — sand). On click, set start → then end → then clear. Above the calendar: a `<select>` for the house (Main House / Cottage / Whole Estate). Below: a summary panel showing selected range, house, nights, and a computed total ($1,450/night Main, $1,050/night Cottage, $2,300/night both) — price in serif sage, "Continue to Details" as a solid-variant AppButton.

Seed with plausible booked dates so the prototype still feels real. A "Continue to Details" button routes to `/contact?preset=...`.

### EnquiryForm.vue
Used on `booking.vue` (enquiry tab) and `contact.vue`. Reactive form object; labels camel uppercase, camel underline inputs (olive on focus). The prototype already posts to `enquiry.php` via `enquiry.js` (Resend-backed, honeypot field, inline ok/err status states) — port that behaviour to a Nuxt server route at `server/api/enquiry.post.ts` with env var placeholders (`RESEND_API_KEY` lives in `.env`, never committed), and keep the client-side states.

### AppFooter.vue
Background `var(--camel-2)`, white text. A giant watermark of the Pax shield (`logo/pax-logo-transparent-500.png`, ~560px, opacity .10) sits left-of-centre behind the content. Grid `1.15fr .7fr .95fr 1.5fr`:
1. **Visit** links + **Press** placeholder mentions (`<!-- TODO: real press coverage -->`) + a light-variant framed "Make an Enquiry" button.
2. **Journal** / **Gallery** as standalone serif link headings.
3. **Plan** links (Reserve, Rates, Contact & Directions, Getting Here → `contact#getting-here`).
4. **Newsletter** behind a vertical hairline: serif invitation heading, Name+Surname underline inputs side by side, Email input, framed white "Sign Up" button, inline thank-you status (prototype-only — no backend; port as a stub server route later).
Below the grid: a centred contact line (`tel | WhatsApp | mailto`, weight 500) and a fine-print line with a Legal Information link (TODO: real legal page). After the footer, a separate **ivory social strip**: circular camel-filled icon badges (Instagram, TikTok, WhatsApp). Column headings serif white ~1.35rem; links Outfit uppercase .72rem. Newsletter column drops below a hairline at ≤1100px; single column at ≤860px.

## Page-by-page notes

**index.vue** — Home. Inset-card hero, then: welcome narrow, farmhouses FeatureSplit (PhotoCard left, statement right), ranch FeatureSplit (flipped), experiences CardCarousel (5 cards + arrows + framed CTA), TestimonialFrame ("Some places don't announce themselves…"), final dual-CTA block. Wire the two CTAs to `/booking` and `/contact`.

**the-estate.vue** — Page hero, narrow intro, two splits (The Land, The Farm), an ivory→ivory-2 location section, CTA to booking.

**farmhouses.vue** — Page hero, narrow intro, then a `<HouseBlock>` for each house (Main House, Cottage) with split + meta stats row (Bedrooms / Sleeps / Staffing / From — camel labels, serif sage values) + a 5-image editorial gallery grid (`2fr 1fr 1fr` x 2 rows, first cell spans 2 rows). Dark **olive** "Exclusive Use" band at the bottom with a light-variant button. The meta stats and gallery are specific enough that a `<HouseBlock>` component is worth extracting.

**experiences.vue** — Page hero, narrow intro, 5 alternating `<ExperienceBlock>`s (Horseback, ATV/4x4, Walking Safaris, Farm Life, Day Excursions), then a "Wellbeing" ivory-2 closing note.

**dining.vue** — Page hero, narrow intro, two splits (Garden, Where to Dine), then a "Sample Day" section with 4 rows (Breakfast / Lunch / Sundowners / Dinner) — each row is `grid-template-columns: 140px 1fr` with the meal name in serif italic sage.

**gallery.vue** — Shorter page hero (55vh), intro, `<GalleryGrid>`.

**journal.vue** — Shorter page hero (55vh), 6-post 3-column grid. Each post: image (4:5), camel date eyebrow, h3 title, 1-line excerpt. **Stretch**: wire to Nuxt Content — post bodies as markdown in `content/journal/`.

**booking.vue** — **Olive** intro band (contour-line watermark) with a toggle between "Check Availability" and "Make an Enquiry" (active tab: ivory bg, olive text). Use `ref('avail' | 'enq')` + `v-if`. No special nav handling needed — the header is always solid. "Continue to Details" routes to `/enquiry?house=main`.

**enquiry.vue** — the CITW-style **Guest Enquiry** flow, no site header/footer (own mini header: small shield logo left, "Back to Site" right). Split screen: left panel with serif camel "Guest Enquiry" title + short rule; right 42% column is a full-height sticky photo (`hero-booking.jpg`; becomes a 200px top banner ≤900px). Step 1: "Select your property:" custom radio list — serif names in per-property colours (Main House camel, Cottage sage, Whole Estate terracotta) with uppercase sub-labels — framed Continue (validates a selection). Step 2: personalised prompt ("Tell us about your stay at <em>X</em>"), the standard enquiry form posting through the same submission path as booking/contact (`enquiry.php` → port to `server/api/enquiry.post.ts`), ‹ Back link, framed solid Send Enquiry. Supports `?house=main|cottage|estate` preselection — used by deep links across the site (footer CTA, exclusive-use band, booking summary).

**contact.vue** — Short page hero, 2-column section (left: direct contact details — camel labels, serif sage values; right: EnquiryForm), then a "Getting Here" ivory-2 closing section. The 2-column grid collapses under 860px (class `contact-grid` — keep it a class, not inline styles, so the collapse works).

## Interaction behavior to preserve

- **Scroll reveal**: every `.reveal` element fades in + translates up once when scrolled into view. Implement once as `useScrollReveal()` composable or as a `v-reveal` directive registered globally. One observer per page, threshold 0.15, `rootMargin: '0px'`.
- **Nav scroll state**: `.scrolled` past 60px adds the bar shadow. Use `useScrollNav()`.
- **Hero slow zoom**: plays once on mount via CSS animation — no JS needed.
- **Card carousels**: scroll-snap track; circular arrows scroll by one card width (`site.js` in the prototype). Arrows hidden ≤860px (touch swipes instead).
- **Footer newsletter**: prevent-default submit with an inline thank-you status; structure so a `$fetch('/api/newsletter', ...)` swap is one line.
- **Reduced motion**: `prefers-reduced-motion: reduce` disables reveals and hero zoom — preserve.
- **Overlay menu**: the hamburger (all viewports) opens the full-screen menu — fade transition, body scroll lock, Escape/close button dismisses, Farmhouses chevron toggles the sub-panel, `aria-expanded` kept in sync.
- **Gallery lightbox**: Escape/arrow keys, click-outside to close, body scroll lock.
- All shared behaviour lives in `site.js` (per-page inline scripts remain only for the booking toggle and the gallery grid/lightbox).

## Acceptance criteria

1. `npm install && npm run dev` boots cleanly with no console errors.
2. All 10 pages render and are reachable via the nav or overlay menu.
3. Visual output matches the prototype pixel-for-pixel (same fonts, colors, spacing, hover states, scroll-reveal behavior). On macOS/iOS headings render in Iowan Old Style; elsewhere in Lora.
4. The booking page toggle works; the calendar visually shows the selected range and blocks out pre-booked dates; the summary updates.
5. Forms submit and display the inline success confirmation (server route may be a stub that returns `{ ok: true }`).
6. Lighthouse score ≥ 90 on Performance, Accessibility, Best Practices, SEO for the home page. (Known trade-off: camel-on-ivory nav links sit near the reference site's ~2.6:1 contrast — if Accessibility flags it, raise it with the owner rather than silently changing the palette.)
7. Nav shadow behavior, hero slow-zoom, scroll reveals, mobile menu, and gallery lightbox all work.
8. Mobile (< 1024px): nav collapses to the hamburger menu; (< 860px) splits stack, grids become single-column; no horizontal scroll at 390px wide.
9. `npm run build` completes successfully.

## Stretch goals (only if time permits after acceptance criteria are met)

- Wire Journal to `@nuxt/content` v2 — move post bodies into `content/journal/*.md` and generate individual post routes at `/journal/[slug]`.
- Install `@nuxt/image` and convert all `<img>` / `background-image` usages.
- Add SEO meta per page via `useSeoMeta()` — title, description, og:image (the prototype's per-page meta/OG/JSON-LD tags are the reference).
- Implement the enquiry server route against Resend for real (port `enquiry.php`) — leave env var placeholders.
- Add a `sitemap.xml` and `robots.txt` via `@nuxtjs/sitemap` (static versions exist at the root).

## Out of scope

- Do not redesign. If you think something could look better, leave a `<!-- TODO: -->` comment instead of changing it.
- Do not add a CMS admin panel.
- Do not wire a real payment gateway for bookings — this is enquiry-first.
- Do not add analytics SDKs without being asked.

## Handoff

Once converted, commit with a clean message like `feat: initial Nuxt 3 port of Pax Ranch House prototype`. Move the original HTML prototype into a `prototype/` subdirectory (or a `prototype` branch) so the client can compare against the converted version.
