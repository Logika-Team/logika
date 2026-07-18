# Tilda Summer Camp Formats Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace archive season placeholders with four editable summer-camp format cards and migrate their Tilda content/media into WordPress camp records.

**Architecture:** The archive stores its ordered format list in `camp_archive_formats`; each item is a published `camp` post, which remains the sole editor surface for card and detail-page data. `PageContent` reads the relationship in order, renders two direct actions per card, and uses the existing camp section fields for the detailed pages. Only source-specific material that has no existing section gets one typed repeater, never raw Tilda HTML.

**Tech Stack:** WordPress, ACF Pro Local JSON, PHP 8.3, DDEV, existing `SourceMarkup`/`PageContent` renderer.

## Global Constraints

- Keep the site copy in Ukrainian.
- Reuse the `camp` CPT, existing ACF repeaters, theme classes and form contract.
- Keep field definitions in `wordpress/wp-content/plugins/logika-core/acf-json/`.
- Do not put raw Tilda markup in ACF; import text and Media Library attachment IDs.
- Preserve old records by setting them inactive rather than deleting them.

---

### Task 1: Make the archive format list editor-managed

**Files:**
- Modify: `wordpress/wp-content/plugins/logika-core/acf-json/group_logika_camp_archive.json`
- Modify: `wordpress/wp-content/themes/logika-theme/src/PageContent.php`
- Modify: `wordpress/wp-content/themes/logika-theme/source-pages/camps.php`
- Modify: `wordpress/wp-content/themes/logika-theme/assets/css/blocks/sections/camp-formats.css`
- Test: `tests/tilda-summer-camp-formats.php`

- [x] Write a test requiring `camp_archive_formats`, four direct camp-card links and booking anchors.
- [x] Run `ddev exec php /var/www/html/tests/tilda-summer-camp-formats.php` and confirm it fails because the field/renderer are missing.
- [x] Add ordered `camp` relationship field `camp_archive_formats`; add card date and description fields to the camp field group only where existing fields cannot express the source data.
- [x] Replace the seasonal card scaffold with the source card anatomy: image, title, dates, detail link and `#camp-booking` action.
- [x] Render only published IDs selected by `camp_archive_formats`, preserving editor order and falling back to no cards when it is empty.
- [x] Re-run the focused test and existing `tests/camps-page-component.php`.

### Task 2: Import the four Tilda camps as editable records

**Files:**
- Create: `scripts/seed-tilda-summer-camps.php`
- Modify: `docs/plan.md`
- Test: `tests/tilda-summer-camp-formats.php`

- [x] Extend the test to require the four source slugs, imported images, exact card copy and active archive relationship.
- [x] Run the test and confirm it fails because the Tilda record mapping does not exist.
- [x] Add one idempotent WP-CLI seeder that creates/updates `greece-2026`, `emily-resort-2026`, `carpathians-2026` and `city-camps-2026`; maps hero, dates, facts, benefits, activities, detail galleries, included items, booking content, FAQ and source-specific content to ACF.
- [x] Copy the referenced source images from `Documents/tilda-export/project917000/images` to the local import directory, import them into the Media Library, and store attachment IDs in the mapped ACF fields.
- [x] Populate `camp_archive_formats` in screenshot order and mark only those four camps active; make prior seasonal records inactive without deleting their content.
- [x] Re-run the focused test and live WordPress content checks.

### Task 3: Verify the public archive and detailed routes

**Files:**
- Test: `tests/tilda-summer-camp-formats.php`

- [x] Run PHP syntax checks for each changed PHP file.
- [x] Run focused camp tests and the WordPress smoke suite.
- [x] Run ACF Local JSON dry-run sync.
- [x] Verify `/camps/` displays four source cards with four images and direct links; verify all four routes respond `200`.
- [ ] Take a final browser screenshot of the archive format section and compare the card count, buttons and image layout with the supplied reference.
