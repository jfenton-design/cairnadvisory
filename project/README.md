# Cairn Advisory — Design System

> *A cairn is a simple thing — stones stacked by someone who walked the path before you, left there so you don't lose your way.*

This is the working design system for **Cairn Advisory**, the AI advisory practice founded by Jacob Fenton. It distills the brand identity into reusable tokens, components, and rules so every artifact — site, deck, email, proposal — feels like it came from the same hand.

---

## Brand at a glance

| | |
|---|---|
| **Mission** | Make AI implementation feel possible — not overwhelming — for operators who've built real things. |
| **Audience** | CEOs, presidents, and IT leaders of established businesses. |
| **Difference** | We've operated the businesses we advise. We build from the inside — not from a slide deck. |
| **Voice** | A senior operator who happens to know AI deeply. Direct, warm, never academic. |

## Standing rules (non-negotiable)

1. **CAIRN is always Figtree 300.** Never heavier. Never bolder.
2. **ADVISORY is letter-spaced to match the visual width of CAIRN above it** — in every lockup, application, and at every size. The site uses a runtime measure-and-match script; static lockups must be eyeballed to match.
3. **0.5px hairlines are the canonical rule weight.** Resist the temptation to thicken.
4. **No exclamation marks. No hype. No emoji.** Restraint is a form of confidence.
5. **The mark always floats.** Use `cairn-mark-float.png` on any surface — no tile, no border, no opacity reduction, no blend mode. Full pigment, every time.
6. **No text below 12px.** Labels, eyebrows, captions, and metadata all sit at the 12px floor or larger. The only exception is the calibrated `ADVISORY` glyph inside the lockup itself, which is sized to optically match CAIRN above it.
7. **Watercolor is companion, never headline.** Slate + amber palette only, 6–20% opacity, never on UI controls or quantitative content. See watercolor section below.

---

## Foundations

### Color
Pulled directly from the mark — cool slate of river stones, warm amber of sandstone, deep ironwood of shadow. Earthy without being rustic, modern without being cold. → [`previews/colors.html`](previews/colors.html)

| Token | Hex | Use |
|---|---|---|
| `--cairn-obsidian` | `#0E0B07` | Deepest shadow |
| `--cairn-ironwood` | `#2C1E10` | Primary text · headings |
| `--cairn-dark-bg` | `#1E1C17` | Dark backgrounds · cover |
| `--cairn-slate` | `#6B8A9A` | Accent · ADVISORY label |
| `--cairn-slate-lt` | `#8B9EA8` | Secondary text |
| `--cairn-amber` | `#C4A87A` | Rules · warm accents (sandstone) |
| `--cairn-umber` | `#8B6A48` | Body text · captions |
| `--cairn-parchment` | `#E8D8B8` | Reversed type on dark |
| `--cairn-linen` | `#F8F5F0` | Primary background |
| `--cairn-white` | `#FDFAF6` | Card surface (warm white) |

### Type
- **Figtree** — display, logo, eyebrow, button. Swiss grotesque neutrality at every weight (300 / 400 / 500 / 600).
- **DM Sans** — body copy and supporting text. Warm and legible at small sizes (300 / 400 / 500).
→ [`previews/type.html`](previews/type.html)

### Spacing · Radii · Rules
8-point base scale. Radii: 3 / 8 / 12 / 14. Hairlines: always 0.5px. → [`previews/spacing.html`](previews/spacing.html)

---

## Brand assets

### Logos & marks (`assets/logos/`)
- `cairn-mark-float.png` — **the canonical mark.** Transparent PNG, full pigment, floats on any surface (linen, obsidian, slate, amber). Use everywhere.
- `cairn-mark-vibrant.png` — the original watercolor on baked-in linen (legacy; use `-float` instead unless you specifically need the linen background)
- `cairn-mark.png` — older transparent variant, lower fidelity (deprecated)
- `cairn-mark-sm.png` — small / favicon-scale mark (transparent, deprecated)
- `cairn-lockup-linen.png` — primary horizontal lockup on linen
- `cairn-lockup-obsidian.png` — primary horizontal lockup on dark

**The mark always floats.** Place `cairn-mark-float.png` directly on any surface — no tile, no border, no blend mode, no opacity reduction. Same brightness, same contrast, every time.

### Imagery (`assets/imagery/`)
- `hero-cairn-coastal.jpg` — coastal cairn, default hero photo

→ [`previews/imagery.html`](previews/imagery.html) · [`previews/logos.html`](previews/logos.html)

---

## Voice & tone

Four principles + a say-this / not-this list. → [`previews/voice.html`](previews/voice.html)

- Operators first, technology second
- Confident without being loud
- Specific over general
- Warm, not casual

---

## Components

Buttons, eyebrows, stat cards, service cards, numbered approach items, industry cards, credential strip, contact links. All built from `tokens.css`. → [`previews/components.html`](previews/components.html)

---

## Files

```
tokens.css                    Single source of truth — colors, type, spacing, motion
README.md                     This document
SKILL.md                      How to use the system in new artifacts
assets/
  logos/                      Marks and lockups (PNG)
  imagery/                    Approved photography
previews/                     Asset-review preview cards (one per topic)
reference/
  cairnadvisory-site.html     The applied site — canonical reference for usage
```

## Using the system

1. Link `tokens.css` (or copy it into your file's `<head>`).
2. Import the fonts via the Google Fonts link inside `tokens.css`.
3. Reach for tokens (`var(--cairn-amber)`, `var(--font-display)`) before introducing new values.
4. When in doubt, look at `reference/cairnadvisory-site.html` — it is the canonical applied example.
5. Read `SKILL.md` before producing new artifacts in this brand.
