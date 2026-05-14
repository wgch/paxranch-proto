# Pax Ranch House — Nuxt 3 Conversion Brief

This brief is written for an AI coding agent (Claude Code) to convert a static HTML/CSS prototype into a production-ready Nuxt 3 application. The prototype lives alongside this file in the same directory.

## Context

**Pax Ranch House** is a private 2-farmhouse retreat on a working ranch in Gilgil, Kenya. Guests book an entire farmhouse (or both) — no shared lobby, no other guests. The website's design language is adapted from [delaire.co.za](https://www.delaire.co.za/): luxury hospitality, cinematic imagery, serif display type, muted earth-tone palette, slow editorial pacing.

A static HTML prototype has already been designed and is the source of truth for layout, copy, colors, typography, and interaction patterns. Your job is to convert it to Nuxt 3 **without redesigning it** — preserve the visual output exactly, but restructure the code idiomatically.

## Source files

**Use the `pax-ranch-site/` folder as the source of truth** — it contains the prototype with the real Pax Ranch photography already wired in. Do NOT use the older loose HTML files in the parent directory; those still reference Unsplash placeholder URLs.

```
pax-ranch-site/
  index.html          Home
  the-estate.html     The Estate
  farmhouses.html     The Farmhouses (Main House + Cottage + Exclusive Use)
  experiences.html    Experiences (5 numbered experience blocks)
  dining.html         Dining (chef, garden, sample day)
  journal.html        Journal (editorial grid)
  booking.html        Reserve (tabbed: availability calendar + enquiry form)
  contact.html        Contact (details + form)
  shared.css          Shared stylesheet for all sub-pages
  images/             40 web-optimized real Pax Ranch photos (~23MB total)
```

`index.html` has its styles inline (it was built first). All other pages link `shared.css`. When porting to Nuxt, consolidate everything into one global stylesheet.

### Images — already real, already optimized

The prototype no longer uses stock photography. All `<img src>` and `background-image` references point to local files in `pax-ranch-site/images/` — real photos of the actual property, already resized (max 2400px), auto-oriented, metadata-stripped, and quality-tuned. **Copy the entire `images/` folder into `public/images/` and the existing relative paths (`images/hero-home.jpg` etc.) will mostly work as-is** — just verify the leading path resolves from Nuxt's `public/` root.

Filenames are semantic and map directly to the component specs below: `hero-{page}.jpg`, `home-*.jpg`, `estate-*.jpg`, `house-main-*.jpg`, `house-cottage-*.jpg`, `experience-*.jpg`, `exp-*.jpg` (home-page grid), `dining-*.jpg`, `journal-*.jpg`, `quote-band.jpg`.

**Known photography gaps** — the supplied photo set had no horse, guest-room interior, or food/dining images. The current site uses the best available substitutes in those spots (stables signage for horseback riding, a red barn and a tree-shadow door for dining, building exteriors for the farmhouses). These are intentional placeholders pending a proper shoot — leave a `<!-- TODO: replace with commissioned photo -->` comment at each of these locations rather than swapping in stock. Affected: the horseback experience block, both farmhouse interior galleries, and the dining page section images.

When you install `@nuxt/image` (see Stretch goals), the optimization step is mostly redundant for these files but still gives you responsive srcsets for free.

## Target stack

- **Nuxt 3** (latest stable), TypeScript
- **Vue 3** `<script setup>` syntax throughout
- No UI framework — plain CSS is intentional and matches the brand
- **Google Fonts**: Cormorant Garamond (300/400/500/600) + Jost (300/400/500), loaded via `nuxt.config.ts` → `app.head.link`
- No state management library needed
- **Nuxt Content** (optional nice-to-have) for the Journal — see "Stretch goals"

## Project structure to produce

```
nuxt.config.ts
app.vue
assets/
  css/
    main.css              ← consolidated design tokens + base styles from shared.css + index.html inline
layouts/
  default.vue             ← wraps <AppHeader /> + <slot /> + <AppFooter />
pages/
  index.vue
  the-estate.vue
  farmhouses.vue
  experiences.vue
  dining.vue
  journal.vue
  booking.vue
  contact.vue
components/
  AppHeader.vue           ← the fixed nav (scrolled state via composable)
  AppFooter.vue           ← the 4-column footer
  PageHero.vue            ← full-bleed hero with bg image, eyebrow, title slot
  SplitBlock.vue          ← 2-column image/copy block, `reverse` prop
  ExperienceCard.vue      ← numbered image card with gradient shade + label
  ExperienceBlock.vue     ← used on experiences.vue — number, eyebrow, heading, body, image, `flip` prop
  AppButton.vue           ← variants: default, light, solid
  FormField.vue           ← underline-style label + input/textarea/select
  SectionHeader.vue       ← eyebrow + h2 + divider (the repeating intro pattern)
  QuoteBand.vue           ← parallax quote section (home page)
  BookingCalendar.vue     ← 2-month view with booked/selected states
  EnquiryForm.vue         ← the long form used on booking + contact
composables/
  useScrollReveal.ts      ← IntersectionObserver wrapper → adds .in class
  useScrollNav.ts         ← toggles nav .scrolled class past 60px
public/
  images/                 ← leave README explaining image replacement (see below)
```

## Design tokens (put in `assets/css/main.css` as CSS custom properties)

```css
:root {
  /* Palette */
  --cream:   #f4efe6;   /* primary background */
  --bone:    #ebe4d6;   /* secondary background */
  --sand:    #d8cdb7;
  --clay:    #b08968;
  --gold:    #a6854c;   /* eyebrow accent */
  --ink:     #2b2620;   /* primary text */
  --charcoal:#3a342c;   /* dark surfaces */
  --muted:   #7a716a;
}
```

Typography:
- Headings: `'Cormorant Garamond', serif`, weight 300–400, line-height 1.15, letter-spacing .01em
- Body: `'Jost', sans-serif`, weight 300, line-height 1.7, letter-spacing .02em
- Eyebrows: Jost, uppercase, 0.72rem, letter-spacing 0.4em, color `var(--gold)`
- Buttons: Jost, uppercase, 0.72rem, letter-spacing 0.3em

Spacing system:
- Section vertical padding: 120px (`padding: 120px 48px`)
- Horizontal max-width container: 1200px
- Narrow prose container: 720px
- Mobile breakpoint: 860px (use `@media(max-width:860px)`)

Motion:
- Hero slow-zoom on load: `transform:scale(1.05) → scale(1)`, 14s ease-out
- Scroll reveal: `opacity 0 → 1` + `translateY(30px) → 0`, 1.2s ease, triggered at threshold 0.15
- Nav transition: background/color/padding over 0.4–0.5s when `.scrolled`
- Hover on image: `transform: scale(1) → scale(1.04)` over 1.5s

## Component specs (the ones worth calling out)

### AppHeader.vue
Fixed top nav. Transparent white text by default; after 60px of scroll, background turns `var(--cream)` and text turns `var(--ink)`. Use `useScrollNav` composable. Brand is text-only ("PAX RANCH HOUSE", Cormorant Garamond, letter-spacing .25em, uppercase). Menu items link to the 6 main pages; a "Reserve" CTA button on the right.

The booking page should mount with `.scrolled` already applied (dark text on cream) because its hero is a dark band rather than full-bleed photography — pass an optional `forceScrolled` prop.

### PageHero.vue
Props: `image` (URL), `eyebrow` (string), `title` (slot, allows `<em>` for italics). Renders a 70vh min-520px section with a background image, gradient overlay `linear-gradient(180deg,rgba(0,0,0,.2) 0%,rgba(0,0,0,.05) 50%,rgba(0,0,0,.55) 100%)`, centered content aligned to bottom, slow zoom animation.

The home page uses a larger 100vh variant — expose a `variant: "home" | "page"` prop.

### SplitBlock.vue
Props: `image` (URL), `alt`, `reverse` (boolean), plus `eyebrow`, `heading`, and default slot for body copy. Grid `1fr 1fr` with 80px gap, image aspect-ratio 4:5 with slow scale-on-hover, collapses to single column under 860px.

### ExperienceBlock.vue (experiences.vue)
Full-bleed 2-column block (no container). Props: `number` ("— 01"), `eyebrow`, `heading`, `image`, `flip`. Text side padded 80px/60px. Image side is `min-height:400px` with background-image cover. 5 of these stack on the experiences page, alternating flip.

### BookingCalendar.vue
Two-month grid. Internal state: `selectedRange: { start, end }`, `bookedDates: Date[]`. Render cells with classes: `off` (prev/next month), `booked` (unavailable), `sel` (selected endpoints), `range` (in-between). On click, set start → then end → then clear. Above the calendar: a `<select>` for the house (Main House / Cottage / Whole Estate). Below: a summary panel showing selected range, house, nights, and a computed total ($1,450/night Main, $1,050/night Cottage, $2,300/night both).

Seed with plausible booked dates so the prototype still feels real. A "Continue to Details" button routes to `/contact?preset=...`.

### EnquiryForm.vue
Used on `booking.vue` (enquiry tab) and `contact.vue`. Reactive form object. On submit: `e.preventDefault()`, show a success toast/inline confirmation — no backend wiring needed yet, but structure it so swapping in a `$fetch('/api/enquiry', ...)` call is a one-line change. Consider a Nuxt server route stub at `server/api/enquiry.post.ts` that just logs and returns `{ ok: true }`.

## Page-by-page notes

**index.vue** — Home. Full-height hero, then 4 sections: welcome narrow, farmhouses split, ranch split (reverse), experiences grid (3-up), quote band, final dual-CTA block. Wire the two CTAs to `/booking` and `/contact`.

**the-estate.vue** — Page hero, narrow intro, two splits (The Land, The Farm), a cream→bone location section, CTA to booking.

**farmhouses.vue** — Page hero, narrow intro, then a `<HouseBlock>` for each house (Main House, Cottage) with split + meta stats row (Bedrooms / Sleeps / Staffing / From) + a 5-image editorial gallery grid (`2fr 1fr 1fr` x 2 rows, first cell spans 2 rows). Dark charcoal "Exclusive Use" band at the bottom. The meta stats and gallery are specific enough that a `<HouseBlock>` component is worth extracting.

**experiences.vue** — Page hero, narrow intro, 5 alternating `<ExperienceBlock>`s (Horseback, ATV/4x4, Walking Safaris, Farm Life, Day Excursions), then a "Wellbeing" bone-background closing note.

**dining.vue** — Page hero, narrow intro, two splits (Garden, Where to Dine), then a "Sample Day" section with 4 rows (Breakfast / Lunch / Sundowners / Dinner) — each row is `grid-template-columns: 140px 1fr` with the meal name in Cormorant italic.

**journal.vue** — Shorter page hero (55vh), 6-post 3-column grid. Each post: image (4:5), date eyebrow, h3 title, 1-line excerpt. **Stretch**: wire to Nuxt Content — post bodies as markdown in `content/journal/`.

**booking.vue** — Dark-band intro with a toggle between "Check Availability" and "Make an Enquiry". Use `ref('avail' | 'enq')` + `v-if`. Nav needs `forceScrolled` since the page opens on a dark section.

**contact.vue** — Short page hero, 2-column section (left: direct contact details in Cormorant display type; right: EnquiryForm), then a "Getting Here" bone-background closing section.

## Interaction behavior to preserve

- **Scroll reveal**: every `.reveal` element fades in + translates up once when scrolled into view. Implement once as `useScrollReveal()` composable or as a `v-reveal` directive registered globally. One observer per page, threshold 0.15, `rootMargin: '0px'`.
- **Nav scroll state**: toggles `.scrolled` class once past 60px. Use `useScrollNav()`.
- **Hero slow zoom**: plays once on mount via CSS animation — no JS needed.
- **Parallax quote band**: uses `background-attachment: fixed` — keep as CSS, but check mobile behavior (`fixed` is unreliable on iOS; fall back to static on touch devices).

## Images

**Already done.** The prototype ships with real, web-optimized Pax Ranch photography in `pax-ranch-site/images/` (40 files, ~23MB). There are no Unsplash URLs left to replace. Your job is simply to carry these into the Nuxt project:

1. Copy `pax-ranch-site/images/` → `public/images/`.
2. The existing relative `src` paths (`images/hero-home.jpg`, etc.) should resolve once they're served from `public/` — verify and adjust the leading slash if needed (`/images/...`).
3. Optionally adopt `<NuxtImg>` (`@nuxt/image`) for responsive srcsets — see Stretch goals.

The four known photography gaps (horseback, farmhouse interiors, dining) currently use substitute Pax photos and are marked with `<!-- TODO: replace with commissioned photo -->`. Preserve those comments; do not substitute stock.

## Acceptance criteria

1. `npm install && npm run dev` boots cleanly with no console errors.
2. All 8 pages render and are reachable via the nav.
3. Visual output matches the prototype pixel-for-pixel (same fonts, colors, spacing, hover states, scroll-reveal behavior).
4. The booking page toggle works; the calendar visually shows the selected range and blocks out pre-booked dates; the summary updates.
5. Forms submit without errors and display a success confirmation (no backend required).
6. Lighthouse score ≥ 90 on Performance, Accessibility, Best Practices, SEO for the home page.
7. Nav scroll behavior, hero slow-zoom, and scroll reveals all work.
8. Mobile (< 860px): nav collapses appropriately, splits stack, grids become single-column, no horizontal scroll.
9. `npm run build` completes successfully.

## Stretch goals (only if time permits after acceptance criteria are met)

- Wire Journal to `@nuxt/content` v2 — move post bodies into `content/journal/*.md` and generate individual post routes at `/journal/[slug]`.
- Install `@nuxt/image` and convert all `<img>` / `background-image` usages.
- Add SEO meta per page via `useSeoMeta()` — title, description, og:image.
- Implement a working server route for the enquiry form that emails a mailbox via a transactional provider (Resend / Postmark) — leave env var placeholders.
- Add a `sitemap.xml` and `robots.txt` via `@nuxtjs/sitemap`.
- Respect `prefers-reduced-motion`: disable the hero zoom and scroll reveals when the user prefers reduced motion.

## Out of scope

- Do not redesign. If you think something could look better, leave a `<!-- TODO: -->` comment instead of changing it.
- Do not add a CMS admin panel.
- Do not wire a real payment gateway for bookings — this is enquiry-first.
- Do not add analytics SDKs without being asked.

## Handoff

Once converted, commit with a clean message like `feat: initial Nuxt 3 port of Pax Ranch House prototype`. Leave the original HTML prototype (`pax-ranch-site/`) untouched at the repo root — or move it to a `prototype/` subdirectory — in case the client wants to compare against the converted version.
