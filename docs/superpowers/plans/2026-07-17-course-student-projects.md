# Course Student Projects Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Render the approved editable student-project carousel after the process section on every course page.

**Architecture:** Reuse the existing homepage portfolio DOM/classes and renderer shape in the fixed course-page transformation. Extend the existing per-course ACF repeater rather than introducing a new entity or global setting.

**Tech Stack:** WordPress, PHP 8, ACF Pro Local JSON, DDEV.

## Global Constraints

- All public copy is Ukrainian.
- ACF schema lives in `wordpress/wp-content/plugins/logika-core/acf-json/`.
- Keep existing field names stable and escape all ACF output by context.
- Seed the approved default cards only when a course has no editor-provided projects.
- Do not change unrelated dirty worktree files.

---

### Task 1: Define and prove the editable course portfolio contract

**Files:**
- Modify: `wordpress/wp-content/plugins/logika-core/acf-json/group_logika_course.json`
- Create: `tests/course-student-projects.php`

**Interfaces:**
- Consumes: `group_logika_course` and `course_projects` ACF repeater.
- Produces: featured and standard-card fields available to the course renderer.

- [ ] **Step 1: Write the failing test**

Assert that `course_projects` is a repeater with `variant`, `student_name`, `student_age`, `course`, `topic`, `description`, `student_image`, `project_image`, `video_url`, `cta_label`, and `cta_url` subfields.

- [ ] **Step 2: Run test to verify it fails**

Run: `ddev exec php /var/www/html/tests/course-student-projects.php`

Expected: FAIL because the course repeater lacks one or more carousel fields.

- [ ] **Step 3: Add the minimum Local JSON subfields**

Keep existing `project_title`, `project_description`, and `project_image` fields. Add only the card fields listed above, with image fields returning attachment IDs and Ukrainian editor instructions.

- [ ] **Step 4: Run test to verify it passes**

Run: `ddev exec php /var/www/html/tests/course-student-projects.php`

Expected: PASS for the ACF schema assertion.

### Task 2: Render the shared portfolio contract in course source markup

**Files:**
- Modify: `wordpress/wp-content/themes/logika-theme/src/PageContent.php`
- Modify: `wordpress/wp-content/themes/logika-theme/source-pages/it-course.php`
- Modify: `tests/course-student-projects.php`

**Interfaces:**
- Consumes: `course_projects` rows from Task 1.
- Produces: `.portfolio-section__card` and `.portfolio-section__card--featured` cards in the course HTML.

- [ ] **Step 1: Extend the failing test**

Save one standard row and one featured row to a course, render its fixed source page, and assert the student name, description, video URL, CTA label, standard-card class, and featured-card class are present.

- [ ] **Step 2: Run test to verify it fails**

Run: `ddev exec php /var/www/html/tests/course-student-projects.php`

Expected: FAIL because the course renderer outputs only generic cards.

- [ ] **Step 3: Implement the smallest shared card markup**

Transform course rows into the existing homepage portfolio card DOM. Use `esc_html()`, `esc_attr()`, `esc_url()`, attachment IDs, and omit incomplete rows. Keep the portfolio section absent when no valid card exists.

- [ ] **Step 4: Run test to verify it passes**

Run: `ddev exec php /var/www/html/tests/course-student-projects.php`

Expected: PASS with both variants and saved editable values in rendered HTML.

### Task 3: Verify the public course page

**Files:**
- Modify: `docs/plan.md` if present in the worktree; otherwise no documentation change beyond this plan.

- [ ] **Step 1: Run focused regression checks**

Run: `ddev exec php /var/www/html/tests/course-student-projects.php && ddev exec php /var/www/html/tests/homepage-student-projects.php`

Expected: both commands print their success messages.

- [ ] **Step 2: Verify the DDEV route**

Open `https://logika.ddev.site/courses/programming-start/` at desktop and narrow widths. Confirm the carousel appears immediately after the process section and remains horizontally scrollable.

- [ ] **Step 3: Update Graphify**

Run: `graphify update .`

Expected: graph files reflect the changed source and test relationships.
