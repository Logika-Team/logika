# Pixel-faithful ACF migration Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make every supplied HTML page visually identical while exposing all manager-owned text and images through ACF.

**Architecture:** Use `main/build/*.html` as the visual baseline and keep its checked-in runtime copy in `source-pages/*.php`. Replace a static value only inside the matching source structure; never substitute a section with a shorter card or alternate nesting. Store field definitions in `logika-core/acf-json` and keep rendering in `logika-theme`.

**Tech Stack:** WordPress 7, PHP 8.3, ACF Pro Local JSON, DDEV, existing CSS/JS/assets.

## Global Constraints

- Ukrainian public copy only.
- Do not modify `build/`.
- Preserve supplied classes, nested elements, assets and controls.
- ACF fields use Local JSON and escaped output.
- Run a focused red-green test before every template change.

---

### Task 1: Establish homepage visual baseline

**Files:**
- Modify: `tests/theme-source-pages.php`
- Modify: `tests/homepage-data.php`
- Modify: `wordpress/wp-content/themes/logika-theme/src/SourceMarkup.php`

**Produces:** baseline assertions for `banner-section`, `services-section`, `english-section`, and `faq-section` class/section sequence.

- [x] Write assertions that compare the source and rendered homepage section order and class signatures.
- [x] Run `ddev exec php /var/www/html/tests/theme-source-pages.php` and verify red against the current simplified replacements.
- [x] Remove only the replacement that changes source structure; retain content substitution only when the template is structurally identical.
- [x] Run `ddev exec php /var/www/html/tests/theme-source-pages.php` and `ddev exec php /var/www/html/tests/homepage-data.php` until green.
- [x] Verify `/` in browser against the supplied homepage screenshot at desktop and mobile widths.

### Task 2: Rebuild homepage ACF sections inside source structure

**Files:**
- Modify: `wordpress/wp-content/plugins/logika-core/acf-json/group_logika_home.json`
- Modify: `wordpress/wp-content/themes/logika-theme/template-parts/sections/hero.php`
- Create or modify: `wordpress/wp-content/themes/logika-theme/template-parts/sections/*.php`
- Modify: `tests/homepage-data.php`

**Produces:** ACF-backed hero, trust, courses, English, CTA, and FAQ without changing the baseline markup.

- [ ] Add one failing fixture assertion for each section before adding fields.
- [ ] Add stable Local JSON field keys for the visible text/image/repeater values.
- [ ] Copy the exact source section markup into its PHP template and substitute only escaped ACF values.
- [ ] Use source asset URLs as fallbacks when media fields are empty.
- [ ] Run focused tests after each section and verify the homepage visually.

### Task 3: Migrate shared header/footer and static pages

**Files:**
- Modify: `source-pages/header.php`, `source-pages/footer.php`, `source-pages/about.php`, `source-pages/faq.php`, `source-pages/it-courses.php`, `source-pages/en-courses.php`, `source-pages/media-center.php`
- Modify: `wordpress/wp-content/plugins/logika-core/acf-json/*.json`
- Modify: `tests/theme-source-pages.php`

**Produces:** ACF-backed header/footer and each static WordPress page with unchanged visual structure.

- [ ] Add a failing structural test for one route at a time.
- [ ] Map each visible text/image to existing or new named ACF fields; use repeaters for repeated cards.
- [ ] Render escaped values inside the original elements rather than replacing page sections.
- [ ] Verify each page route in browser against its source HTML.

### Task 4: Bring CPT pages to source fidelity

**Files:**
- Modify: `src/CityPage.php`, `src/CoursePage.php`, `src/CampPage.php`, `single-city.php`, `single-course.php`, `single-camp.php`, `archive-course.php`, `archive-camp.php`
- Modify: `tests/city-page.php`, `tests/course-page.php`, `tests/camp-page.php`

**Produces:** city, course, and camp pages whose dynamic ACF values are displayed in source-faithful layouts.

- [ ] Write one failing DOM/class-signature test per page type.
- [ ] Match the closest supplied single/archive source page structure before adding dynamic data.
- [ ] Preserve `picture`, card media, slider controls, and CTA classes while rendering ACF/CPT values.
- [ ] Run PHP tests and browser route checks after each page type.

### Task 5: Final visual and editor verification

**Files:**
- Modify: `docs/plan.md`
- Modify: `README.md` only if editor workflow changes

**Produces:** a verified visual migration checklist and editor instructions.

- [ ] Run all `tests/*.php` through DDEV.
- [ ] Verify ACF Local JSON status is `In sync`.
- [ ] Check desktop/mobile screenshots for all ten supplied routes.
- [ ] Document remaining source gaps: contacts, city/map source, and form states.
- [ ] Update only evidence-backed checkboxes in `docs/plan.md`.
