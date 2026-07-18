# Media Section Mobile Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Match the extracted Figma mobile Media Center layout in the existing homepage section.

**Architecture:** Keep the current PHP/SourceMarkup structure and Figma assets. Apply responsive behavior in the existing section stylesheet: one-column flow below 767px, content-driven cards, and responsive artwork/CTA placement.

**Tech Stack:** WordPress theme, plain CSS, PHP source-level regression check, Playwright CLI.

## Global Constraints

- Modify only the Media Center visual surface.
- Preserve existing content, links, PHP, JavaScript, and assets.
- Match extracted mobile constraints: 16px horizontal inset, 20px stack gap, 20px card padding, 14px main-card media-to-copy gap.
- No new dependency or UI library.

### Task 1: Add the failing CSS contract check

**Files:**
- Create: `tests/media-section-mobile.sh`

- [x] **Step 1: Add a shell check for the expected mobile rules.**

The check reads the source stylesheet and fails until the stylesheet contains the mobile one-column flow, content-driven card sizing, 16px mobile inset, and no absolute race CTA positioning.

```sh
#!/usr/bin/env bash
set -euo pipefail

css="wordpress/wp-content/themes/logika-theme/assets/css/blocks/sections/media-section.css"

grep -q '@media(max-width:767px)' "$css"
grep -q 'grid-template-columns:1fr' "$css"
grep -q 'height:auto' "$css"
grep -q 'padding:20px 16px' "$css"
if grep -q '@media(max-width:767px).*top:358px' "$css"; then
  exit 1
fi
```

- [x] **Step 2: Run it and confirm RED.**

Run `bash tests/media-section-mobile.sh`.
Expected: non-zero exit because the new 767px contract is not present.

### Task 2: Implement the mobile layout

**Files:**
- Modify: `wordpress/wp-content/themes/logika-theme/assets/css/blocks/sections/media-section.css`

- [x] **Step 1: Add the 767px mobile rules.**

Append one focused media query that overrides fixed desktop geometry:

```css
@media(max-width:767px){
  .media-section{padding:40px 0}
  .media-section__wrapp{gap:30px}
  .media-section__top{gap:20px}
  .media-section__btns{flex-direction:column;align-items:stretch;gap:10px}
  .media-section__btn-about{width:100%;justify-content:center}
  .media-section__cards-layout{grid-template-columns:1fr;gap:20px}
  .media-section__news,.media-section__promos,.media-section__blog-list{gap:20px}
  .media-section__feature{height:auto;min-height:390px;padding:20px}
  .media-section__feature-tags{top:20px;left:20px;width:calc(100% - 40px);height:auto;flex-wrap:wrap}
  .media-section__feature-tags span,.media-section__feature-tags span:first-child,.media-section__feature-tags span:nth-child(2){width:auto;max-width:100%;height:auto;padding:6px 13px;font-size:12px}
  .media-section__feature .media-section__figma-art{transform:none;object-fit:contain}
  .media-section__feature-copy{gap:10px}
  .media-section__contest,.media-section__promo,.media-section__race{min-height:0;height:auto;padding:20px;border-radius:20px}
  .media-section__contest{gap:20px}
  .media-section__contest .media-section__figma-art{inset:auto 0 0 auto;width:55%;height:55%;object-fit:contain}
  .media-section__contest h3{font-size:30px}
  .media-section__promo{gap:20px}
  .media-section__promo h3{font-size:36px}
  .media-section__promo h4{font-size:20px}
  .media-section__promo .media-section__figma-art{inset:0 0 auto auto;width:45%;height:auto}
  .media-section__race{gap:16px}
  .media-section__race .media-section__figma-art{inset:0 0 auto auto;width:48%;height:auto}
  .media-section__race h3{margin-top:10px;font-size:36px}
  .media-section__race p{position:relative!important;top:auto!important;left:auto;width:auto;height:auto}
  .media-section__race .btn{position:relative;top:auto;left:auto;width:100%;height:auto}
  .media-section__blog-list{display:grid;grid-template-columns:1fr;grid-column:auto}
  .media-section__post{width:100%;height:auto;gap:12px}
  .media-section__post>img,.media-section__post h3{width:100%;height:auto}
  .media-section__post>img{aspect-ratio:225/143}
  .media-section__post h3{min-height:0}
}
```

- [x] **Step 2: Run the contract check and confirm GREEN.**

Run `bash tests/media-section-mobile.sh`.
Expected: exit code 0.

### Task 3: Verify the live renderer

**Files:**
- No further source changes unless verification finds a concrete layout defect.

- [x] **Step 1: Confirm the local WordPress URL is reachable.**

Run `curl -I --max-time 10 https://logika.ddev.site/`.

- [x] **Step 2: Use Playwright at 320px, 375px, and 767px.**

For each width, inspect `.media-section__cards-layout` and assert `document.documentElement.scrollWidth <= window.innerWidth`, then capture a screenshot under `output/playwright/`.

- [x] **Step 3: Run the final source and PHP checks.**

Run `bash tests/media-section-mobile.sh` and `php -l wordpress/wp-content/themes/logika-theme/src/SourceMarkup.php`.
