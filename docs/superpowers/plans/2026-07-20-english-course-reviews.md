# English Course Reviews Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Show the existing reviews section on every English course page and expose its course-specific review selection in the course editor.

**Architecture:** Reuse the shared `template-parts/sections/reviews.php` renderer and pass the current course's `course_related_reviews` relationship. The existing ACF `course` field group already applies to every course, so no new field group or content type is needed.

**Tech Stack:** WordPress, PHP 8.3+, ACF Pro Local JSON, existing theme templates, lightweight PHP contract tests.

## Global Constraints

- Keep all visible copy Ukrainian.
- Work only in `.worktrees/wordpress` on branch `wordpress`.
- Preserve unrelated dirty-worktree changes.
- Do not add a dependency or a new reviews implementation.

---

### Task 1: English course reviews contract

**Files:**
- Create: `tests/english-course-reviews.php`
- Modify: `wordpress/wp-content/themes/logika-theme/template-parts/courses/english.php`

**Interfaces:**
- Consumes: ACF field `course_related_reviews` on the current `course` post.
- Produces: The shared reviews section rendered with the current course ID as context and the selected review IDs, or the existing global fallback when no selection exists.

- [ ] **Step 1: Write the failing test**

Assert that the English course template passes `course_related_reviews` and `course_id` to `template-parts/sections/reviews.php`, and that the course ACF JSON exposes a relationship field for `review` posts.

- [ ] **Step 2: Run test to verify it fails**

Run: `php tests/english-course-reviews.php`

Expected: FAIL because the English template does not yet include the reviews template part.

- [ ] **Step 3: Write minimal implementation**

Insert the existing shared template before the school map:

```php
<?php get_template_part( 'template-parts/sections/reviews', null, array( 'items' => (array) get_field( 'course_related_reviews', $course_id ) ?: null, 'context' => $course_id ) ); ?>
```

- [ ] **Step 4: Run focused and syntax checks**

Run: `php tests/english-course-reviews.php`

Expected: `English course reviews are connected to the course editor.`

Run: `php -l wordpress/wp-content/themes/logika-theme/template-parts/courses/english.php`

Expected: `No syntax errors detected`.

- [ ] **Step 5: Run the existing reviews contracts**

Run: `php tests/reviews-section-overrides.php && php tests/testimonials-global-renderer.php && php tests/it-courses-admin-fields.php`

Expected: all three commands exit with status 0.
