# Tilda Reviews Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Import parent reviews from the local Tilda export into WordPress and render the existing testimonial blocks from approved review records.

**Architecture:** A repeatable CLI importer uses a stable source ID to create or update `review` posts, attaches local Tilda images to the Media Library, and fills ACF fields. The theme replaces only static testimonials sections with markup generated from approved records.

**Tech Stack:** PHP 8.3, WordPress, ACF Pro Local JSON, DDEV/WP-CLI.

## Global Constraints

- Work only in `/home/sbaikov/Desktop/Projects/logika/.worktrees/wordpress` and preserve unrelated changes.
- Public copy and admin labels are Ukrainian.
- Import only local `/home/sbaikov/Documents/tilda-export` files and use DDEV for database writes.

---

### Task 1: Import contract

**Files:**
- Create: `tests/tilda-reviews.php`
- Create: `scripts/import-tilda-reviews.php`

- [x] Add a failing static contract asserting idempotent source identity and a review renderer.
- [x] Run `ddev exec php tests/tilda-reviews.php` and verify failure.
- [x] Implement the importer with `review_external_id`, attachment reuse, and a created/updated/skipped report.
- [x] Re-run the contract and verify it passes.

### Task 2: Editorial fields and rendering

**Files:**
- Modify: `wordpress/wp-content/plugins/logika-core/acf-json/group_logika_review.json`
- Create: `wordpress/wp-content/themes/logika-theme/src/Testimonials.php`
- Modify: `wordpress/wp-content/themes/logika-theme/functions.php`
- Modify: `wordpress/wp-content/themes/logika-theme/src/SourceMarkup.php`

- [x] Add only stable editorial ACF fields needed by the card: display order, card label, and optional video URL.
- [x] Render published, approved reviews in display-order order with escaped text, image, rating, and related-course label.
- [x] Replace only `.testimonials-section` source markup when review data is available.
- [x] Re-run the contract and validate PHP syntax.

### Task 3: Runtime import and verification

**Files:**
- Modify: `docs/guidelines/plan.md`
- Modify: `docs/changelog/2026-07.md`

- [x] Run the import through DDEV twice and ensure the second run creates no duplicate source IDs.
- [x] Sync/check ACF Local JSON and verify review records and attachments with DDEV WP-CLI.
- [x] Open the local page and confirm real review text replaces the placeholder cards.
