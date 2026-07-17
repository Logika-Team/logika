# Course Content Import Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Import standalone educational course copy from the Tilda export into editable ACF-backed WordPress courses and link those courses from `/it-courses/`.

**Architecture:** Keep one `course` CPT and the existing shared `single-course.php` source-page renderer. Store the migrated content in a versioned fixture and use an idempotent seed script to write CPT/ACF data and the page's age-category relationships.

**Tech Stack:** WordPress 7.0, PHP 8.3+, ACF Pro 6.x, custom `logika-core` plugin, `logika-theme`, DDEV/WP-CLI.

## Global Constraints

- Keep business content in WordPress/ACF, not hardcoded in theme templates.
- Store field schema in `wordpress/wp-content/plugins/logika-core/acf-json/`.
- Use stable `course_external_id` and slug matching; rerunning the seed must not duplicate courses.
- Preserve the existing source-page DOM/classes and output escaping.
- Exclude hackathon, event, `Copy of ...`, and duplicate campaign pages.
- Do not overwrite unrelated dirty worktree changes.

---

### Task 1: Add a failing content-fixture contract test

**Files:**
- Create: `tests/course-content-fixture.php`
- Create: `scripts/data/tilda-courses.php`

**Interfaces:**
- Produces `logika_tilda_courses(): array` with one row per included course.
- Each row contains `external_id`, `slug`, `title`, `age_min`, `age_max`, `short_description`, `hero_text`, `hero_additional_text`, `learn_items`, `process_items`, and `program`.

- [ ] **Step 1: Write the failing test**

Assert that the fixture returns the nine approved non-event course pages, that external IDs and slugs are unique, and that each row has at least one program module.

- [ ] **Step 2: Run the test to verify it fails**

Run: `php tests/course-content-fixture.php`

Expected: FAIL because the fixture does not exist yet.

- [ ] **Step 3: Add the minimal fixture contract**

Add the data function with the exact Tilda copy extracted from the approved educational pages. Keep rich text as safe HTML fragments intended for the ACF WYSIWYG field and keep module topics as plain strings.

- [ ] **Step 4: Run the test to verify it passes**

Run: `php tests/course-content-fixture.php`

Expected: `Course content fixture is valid.`

- [ ] **Step 5: Commit**

```bash
git add tests/course-content-fixture.php scripts/data/tilda-courses.php
git commit -m "feat: add tilda course content fixture"
```

### Task 2: Add the idempotent WordPress seed

**Files:**
- Create: `scripts/seed-tilda-courses.php`
- Test: `tests/course-content.php`

**Interfaces:**
- Consumes `logika_tilda_courses()`.
- Creates or updates `course` posts by `course_external_id`, then slug fallback.
- Writes ACF fields with `update_field()` and assigns `course_direction` and `learning_format` terms.
- Produces a stable summary of created, updated, and skipped rows.

- [ ] **Step 1: Write the failing integration test**

Create two fixture rows through the seed, run it twice, then assert that each external ID maps to exactly one published course and that `course_program` and `course_short_description` persist.

- [ ] **Step 2: Run the test to verify it fails**

Run: `ddev exec php tests/course-content.php`

Expected: FAIL because the seed command does not exist.

- [ ] **Step 3: Implement the minimal seed**

Use `get_posts()` for external-ID lookup, `get_page_by_path()` for slug fallback, `wp_insert_post()` only when neither exists, `update_post_meta()` for the stable external ID, `update_field()` for ACF values, and `wp_set_object_terms()` for the course direction/format. Do not delete existing course content that is outside the fixture.

- [ ] **Step 4: Run the test to verify it passes**

Run: `ddev exec php tests/course-content.php`

Expected: `Course content seed is idempotent.`

- [ ] **Step 5: Commit**

```bash
git add scripts/seed-tilda-courses.php tests/course-content.php
git commit -m "feat: seed tilda courses into acf"
```

### Task 3: Link real course cards from the IT courses page

**Files:**
- Modify: `wordpress/wp-content/plugins/logika-core/acf-json/group_logika_page_it_courses.json` only if the current relationship fields cannot represent the approved rows.
- Modify: `wordpress/wp-content/themes/logika-theme/src/PageContent.php` only if card rendering needs a narrow fallback fix.
- Test: `tests/course-cards.php`

**Interfaces:**
- Consumes the seeded published `course` IDs.
- Writes the existing `it_courses_age_categories` relationship rows for the four age ranges.
- Preserves current card markup and uses the course permalink for the details button.

- [ ] **Step 1: Write the failing card test**

Render the IT courses source page after seeding and assert that each approved course title appears in a card, each card has a `/courses/{slug}/` details link, and no placeholder-only card remains for a populated age group.

- [ ] **Step 2: Run the test to verify it fails**

Run: `ddev exec php tests/course-cards.php`

Expected: FAIL because the page relationships are not populated.

- [ ] **Step 3: Seed the existing age-category repeater**

Map courses to the current age categories and update the page ACF field in deterministic order. Keep the existing catalog age cards and asset fallbacks unchanged.

- [ ] **Step 4: Run the test to verify it passes**

Run: `ddev exec php tests/course-cards.php`

Expected: `Course cards render seeded courses.`

- [ ] **Step 5: Commit**

```bash
git add wordpress/wp-content/plugins/logika-core/acf-json/group_logika_page_it_courses.json wordpress/wp-content/themes/logika-theme/src/PageContent.php tests/course-cards.php
git commit -m "feat: link it course cards to course pages"
```

### Task 4: Smoke-test the public course routes

**Files:**
- Modify: only files needed to fix a failing smoke check.
- Test: `tests/course-page.php` and browser smoke output under `output/playwright/`.

- [ ] **Step 1: Run PHP rendering checks**

Run: `ddev exec php tests/course-page.php` and the fixture, seed, and card tests.

- [ ] **Step 2: Verify exact routes**

Open `/it-courses/`, then representative course routes from age groups including `/courses/programming-projects/` if that existing course remains published. Confirm title, age, unique copy, program accordion, CTA course context, and absence of empty optional sections.

- [ ] **Step 3: Verify source and status**

Run `git diff --check`, `git status --short`, and `graphify update .` from the worktree. Report any unrelated pre-existing dirty files without modifying them.

- [ ] **Step 4: Commit verification-only fixes if required**

```bash
git add <only-files-changed-for-this-feature>
git commit -m "test: verify imported course pages"
```
