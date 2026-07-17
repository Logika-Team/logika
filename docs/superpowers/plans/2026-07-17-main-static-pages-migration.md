# Main Static Pages Migration Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Safely migrate the confirmed five `main` page updates into the WordPress theme without merging divergent branches.

**Architecture:** Keep `Logika_Theme_Source_Markup` as the single renderer. Replace only the five theme source-page documents with their current `main` equivalents, then add their missing source assets and compiled styles under the existing theme asset tree.

**Tech Stack:** PHP 8.3+, WordPress custom theme, static HTML/SCSS, Gulp, DDEV.

## Global Constraints

- Preserve existing ACF substitutions, form normalisation, routes, and dirty worktree changes.
- Keep all visible copy Ukrainian and add no dependencies.
- Do not merge `main` into `wordpress`.

---

### Task 1: Protect the migration contract

**Files:**
- Create: `tests/main-static-pages-migration.php`
- Modify: `docs/guidelines/plan.md`

- [ ] **Step 1: Write the failing test**

Assert that the theme's five source pages contain the new block classes (`media-section`, `details-section`, `gallery-section`, `trips-section`, `course-banner-section`, `learn-section`, `process-section`, `faq-banner-section`) and that each referenced theme asset exists.

- [ ] **Step 2: Run the test to verify it fails**

Run: `php tests/main-static-pages-migration.php`

Expected: failure because the WordPress source pages are behind `main`.

- [ ] **Step 3: Port the smallest required files**

Update only the five `source-pages` documents and their direct CSS/image dependencies, leaving renderer and ACF/PHP files untouched.

- [ ] **Step 4: Run the test to verify it passes**

Run: `php tests/main-static-pages-migration.php`

Expected: all required section and asset assertions pass.

### Task 2: Verify rendered WordPress parity

**Files:**
- Modify: `docs/guidelines/plan.md`

- [ ] **Step 1: Run targeted checks**

Run the static migration check and existing related page checks.

- [ ] **Step 2: Build and smoke-test**

Run the existing theme build, then request `/`, `/faq/`, `/camps/`, `/it-courses/`, and a representative course route through DDEV.

