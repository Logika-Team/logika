# Branch Sidebar Route Icon Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add the supplied route icon to every school-map branch card.

**Architecture:** Reuse the existing `camp-map.js` branch renderer and `school-map.css`. The theme localizes one additional asset URL; the API and branch content stay unchanged.

**Tech Stack:** WordPress custom theme, vanilla JavaScript, CSS, SVG, DDEV, Playwright CLI.

## Global Constraints

- Keep the site Ukrainian.
- Do not add a dependency or a new ACF field.
- Preserve the existing card click, keyboard, label, and address behavior.
- Use the existing DDEV WordPress runtime for verification.

---

### Task 1: Add and expose the SVG asset

**Files:**
- Create: `wordpress/wp-content/themes/logika-theme/assets/img/icons/solar_route-outline.svg`
- Modify: `wordpress/wp-content/themes/logika-theme/functions.php:96`

- [x] Copy the approved SVG into the theme asset directory and add `branchIconUrl` to the existing `logikaThemeAssets` localization.
- [x] Run `php -l wordpress/wp-content/themes/logika-theme/functions.php` inside DDEV.

### Task 2: Render and style the icon

**Files:**
- Modify: `wordpress/wp-content/themes/logika-theme/assets/js/camp-map.js:31,82-98`
- Modify: `wordpress/wp-content/themes/logika-theme/assets/css/blocks/sections/school-map.css` in the branch-list rules.

- [x] Create a decorative 32×32 `<img>` after each branch address/title content, using the localized asset URL.
- [x] Add right padding and absolute centering so the icon sits at the right edge without overlapping text.
- [x] Keep the existing `<li>` click and keyboard listeners unchanged.

### Task 3: Verify the rendered route

**Files:**
- Test: DDEV homepage school map and `/cities/dnipro/`.

- [x] Open `http://logika.ddev.site`, select Дніпропетровська область, then Дніпро.
- [x] Confirm the icon appears for all eight cards and the existing labels/addresses remain visible.
- [x] Run `git diff --check` and inspect the browser-rendered route.
