# Marketing Page Content Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make all unique editor-owned text on five static marketing pages editable through their existing page ACF groups.

**Architecture:** Keep source markup as the visual fallback. Extend each existing Local JSON group with section-owned fields/repeaters, seed only missing page meta, and render non-empty values through `Logika_Theme_Page_Content`. CPT-backed courses, FAQ, reviews, posts and city data remain outside page fields.

**Tech Stack:** WordPress, PHP 8.3, ACF Pro Local JSON, DDEV, existing standalone PHP tests.

## Global Constraints

- Keep all public copy Ukrainian.
- Do not rename existing ACF names or keys.
- Do not overwrite an editor value when seeding defaults.
- Escape text by output context and keep source markup as empty-field fallback.
- Touch only files owned by this change in the dirty `wordpress` worktree.

---

### Task 1: Cover page-owned ACF rendering

**Files:**
- Modify: `tests/marketing-pages-acf.php`
- Modify: `docs/guidelines/plan.md`

**Interfaces:**
- Consumes: `logika_theme_render_source_page( string $source ): void`
- Produces: regression coverage for a new page text field and fallback behavior.

- [x] Add one representative missing field per page to the test matrix and assert its replacement is rendered.
- [x] Run `ddev exec php /var/www/html/tests/marketing-pages-acf.php`; confirm it fails because the fields are absent.

### Task 2: Version the page-owned content model

**Files:**
- Modify: `wordpress/wp-content/plugins/logika-core/acf-json/group_logika_page_about.json`
- Modify: `wordpress/wp-content/plugins/logika-core/acf-json/group_logika_page_it_courses.json`
- Modify: `wordpress/wp-content/plugins/logika-core/acf-json/group_logika_page_english_courses.json`
- Modify: `wordpress/wp-content/plugins/logika-core/acf-json/group_logika_page_faq.json`
- Modify: `wordpress/wp-content/plugins/logika-core/acf-json/group_logika_page_media_center.json`
- Create: `scripts/seed-marketing-page-content.php`

**Interfaces:**
- Consumes: existing page slugs and ACF field names.
- Produces: stable Local JSON fields and an idempotent missing-value seed.

- [x] Add fields grouped by visible page section; use repeaters only for homogeneous cards/rows.
- [x] Add a seed map of new field name, page slug, and source default; skip non-empty values.
- [x] Run `./scripts/wp-mcp.sh acf json sync --dry-run` and review only the five intended groups.

### Task 3: Render the content model

**Files:**
- Modify: `wordpress/wp-content/themes/logika-theme/src/PageContent.php`

**Interfaces:**
- Consumes: page ACF values keyed by source page.
- Produces: source markup with non-empty editor values substituted safely.

- [x] Add explicit field-to-source-default mappings for the new single texts.
- [x] Render valid repeater rows only when present; preserve source markup otherwise.
- [x] Use `esc_html()` for text and `wp_kses_post()` only for designated rich copy.

### Task 4: Seed and verify runtime behavior

**Files:**
- Modify: `tests/marketing-pages-acf.php`
- Modify: `docs/changelog/2026-07.md`
- Modify: `/home/sbaikov/Obsidian/obsidian-backup/Obsidian vault/Projects/logika-school/docs/changelog/2026-07.md`

**Interfaces:**
- Consumes: Local JSON, seed script, `PageContent` renderer.
- Produces: seeded page content and verified frontend output.

- [x] Run the seed twice through `ddev exec php /var/www/html/scripts/seed-marketing-page-content.php` and verify the second run changes no existing values.
- [x] Run `ddev exec php /var/www/html/tests/marketing-pages-acf.php` and `curl -kfsS https://logika.ddev.site/it-courses/`.
- [x] Run `graphify update .`, inspect the focused diff, and record the dated project changelog entries.
