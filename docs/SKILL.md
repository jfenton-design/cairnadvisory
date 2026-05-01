# SKILL — Designing in the Cairn Advisory system

Use this checklist any time you build a new artifact (site page, deck slide, email, proposal, social asset, etc.) in the Cairn Advisory brand.

## 1 · Read first
- `README.md` — the system overview
- `tokens.css` — every color, font, spacing, and shadow value
- `reference/cairnadvisory-site.html` — the canonical applied example. **When uncertain, mimic this file.**
- `previews/components.html` — exact recipes for buttons, cards, eyebrows, etc.

## 2 · Always use tokens
Never hard-code a hex, font stack, or pixel value that exists in `tokens.css`. Reach for the variable. If a needed value is missing, propose a token addition rather than inlining a one-off.

## 3 · The non-negotiables
1. **CAIRN is Figtree 300, uppercase, letter-spaced.** No bold. No medium. No semibold. Ever.
2. **ADVISORY is Figtree 500, uppercase, letter-spaced to match CAIRN's visual width** above or beside it. The site has a measure-and-match script (see `reference/cairnadvisory-site.html`'s `<script>` block — copy it verbatim into any wordmark surface).
3. **Rules are 0.5px, color `#D8C8A8` on light, `#2A2018` on dark.** Never 1px. Never solid black.
4. **No emoji.** Use small Figtree-500 letterforms inside hairline boxes for icon stand-ins (see industry cards in `previews/components.html`).
5. **No exclamation marks.** Anywhere. Including microcopy.
6. **The mark sits on linen, dark umber, slate, or amber only.** On dark, apply `mix-blend-mode: screen`. On linen, use `cairn-mark-vibrant.png` straight (no blend mode).
7. **No text below 12px.** Sole exception: the calibrated `ADVISORY` glyph inside the lockup.
8. **Watercolor is companion, never headline.** Slate + amber palette only. Opacity 6–20%. Never on UI controls. See section 11.

## 4 · Layout defaults
- Section padding: `120px 96px` desktop, scale down for narrower viewports
- Container max-width: ~1200px
- Prose measure: ~640px — never wider
- Generous vertical rhythm — Cairn whispers; let things breathe

## 5 · Type defaults
- Hero: Figtree 300, `clamp(56px, 7vw, 96px)`, `letter-spacing: -0.02em`, `line-height: 1.04`. Use one italic phrase for emphasis (`<em>`).
- Section title: Figtree 300, `clamp(44px, 5vw, 64px)`, `letter-spacing: -0.02em`, sentence case
- Eyebrow: Figtree 500, 10px, `letter-spacing: 0.22em`, uppercase, with a 36px amber rule before it
- Body: DM Sans 300, 17px, `line-height: 1.85`, color `#5C4A30`
- Label / button: Figtree 500–600, 11px, `letter-spacing: 0.14em`, uppercase

## 6 · Color usage
- **Default surface** is linen (`#F8F5F0`). Cards step up to warm white (`#FDFAF6`).
- **Dark sections** use `#1E1C17` (dark umber). Never pure black.
- **Amber (`#C4A87A`)** is the warm accent — use it for the 28–40px rule under service cards, the eyebrow line, the CTA button on dark, and stat-number superscripts. Resist using it for body text.
- **Slate (`#6B8A9A`)** is the cool accent — primarily for the ADVISORY label and small descriptors.
- **Body text on light is umber-toned (`#5C4A30`)**, not gray.

## 7 · Buttons
- **Primary (light context):** ironwood fill, parchment text. Hover → obsidian.
- **Primary (on photo / dark):** amber fill, ironwood text. Hover → parchment.
- **Ghost:** parchment text with a 0.5px parchment underline. Hover → solid parchment underline.
- **Link:** umber text with a 0.5px amber underline.
- All caps, Figtree 500–600, 0.14em letter-spacing, 3px radius.

## 8 · Voice when writing copy
- Operators first, technology second. Lead with business outcomes.
- Specific over general. "Reduce churn by 20%" not "drive efficiency."
- Confident without being loud. No hype words ("revolutionary", "leverage", "ideate", "transformation").
- Warm, not casual. Never "Hey" / "Awesome" / "We got you."
- Use em-dashes generously — they sound like Jacob.

## 9 · Common surfaces — quick recipes

**Hero with photo**
- Photo: cool, overcast, natural light. Cairns or weathered surfaces.
- Overlay: bottom-anchored gradient, 0% → 5% → 55% → 82% ironwood
- Content: bottom-anchored, max-width 900px, 96px side padding
- Eyebrow → headline (with one italic emphasis word) → 19px subhead → primary + ghost button pair

**Card row** (services / industries / approach)
- 14–20px gap between cards
- 14px radius, 0.5px sandstone border, linen or warm-white fill
- Numbered top-left in Figtree 300 + 3–4px letter-spacing slate
- 28px amber rule at the bottom of service cards

**Stat card**
- 36px padding, 12px radius, linen fill, 0.5px sandstone border
- Number: Figtree 300, 56px, ironwood; superscript unit (+, M, %) in 28px amber
- Label: 13–14px DM Sans, umber

**Email / proposal / business card**
- See `previews/components.html` for fixtures and `cairn_brand_book_compressed.html` if available

## 10 · Before shipping
- Did I use only tokens?
- Is every CAIRN Figtree 300?
- Is every ADVISORY width-matched to its CAIRN?
- Are all rules 0.5px?
- Is there any emoji, exclamation mark, or jargon hiding in my copy?
- Does the artifact feel like the reference site? If not, why not?

## 11 · Watercolor system

The mark's watercolor is a recurring brand fingerprint, used selectively beyond the logo. Reach for it on editorial and outbound surfaces — blog headers, newsletters, proposals, long-form pages — not on UI controls or quantitative content.

### When to use
- Blog post and field-note headers — single stone fragment top-right
- Newsletter / email signoffs — small fragment with the wordmark
- Section dividers in long-form content — painted rule in place of hairline
- Hero or feature backgrounds — soft wash gradient (never gradient on top of photography)
- Editorial openers — amber drop cap or slate numeral with soft glow

### When NOT to use
- Buttons, form controls, or any interactive element
- Charts, dashboards, tables, or any quantitative surface
- Logos, wordmarks, or pages where the actual mark is already visible
- Anywhere it would compete with a photograph for attention

### Tokens
- `--wc-slate-rgb`, `--wc-amber-rgb`, `--wc-sienna-rgb` — the only allowed pigments
- `--wc-whisper` (0.06), `--wc-subtle` (0.12), `--wc-confident` (0.20) — opacity stops; never exceed 0.20 in body content
- `--wc-wash-amber`, `--wc-wash-slate`, `--wc-wash-mixed` — canonical background-image gradients

### Authoring new pieces
New fragments (single stones, brushed strokes, painted shapes) are generated via AI. Use the prompt recipe in `previews/watercolor.html` exactly — it specifies palette, technique, and reference style. Output must be transparent PNG. Save new assets to `assets/watercolor/`.

### Rules of restraint
- One watercolor element per surface, maximum
- Never both a wash AND a fragment in the same view
- Print: test at the published opacity on uncoated paper before shipping
- If unsure whether something needs watercolor — it doesn't
