# Hide English Course Catalog Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Temporarily stop rendering the English course catalog while preserving its markup and content.

**Architecture:** Keep the shared catalog markup intact in the English course template and gate it behind a local boolean. A template contract verifies the catalog is absent from rendered output.

**Tech Stack:** WordPress, PHP 8.3, DDEV.

## Global Constraints

- Do not delete catalog markup, ACF fields, course data, or assets.
- Change only the shared English course template and its focused contract test.

---

### Task 1: Disable the catalog render

**Files:**
- Modify: `wordpress/wp-content/themes/logika-theme/template-parts/courses/english.php`
- Modify: `tests/english-b2-1-hero.php`

- [ ] Add a failing assertion that rendered English course markup excludes `english-course-catalog`.
- [ ] Run `ddev exec php tests/english-b2-1-hero.php` and verify it fails because the catalog renders.
- [ ] Add one local `false` render flag around the existing catalog conditional; keep all catalog markup unchanged.
- [ ] Re-run `ddev exec php tests/english-b2-1-hero.php` and verify it passes.
