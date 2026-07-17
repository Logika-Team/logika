# Homepage Course Chip Links Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make the four 14–17 homepage course chips open their corresponding published course pages.

**Architecture:** Reuse the existing `home_programming_courses` ACF repeater and its nested `chips` URL field. Seed the four URLs in both homepage seed scripts and preserve the renderer's escaped URL and empty-value fallback.

**Tech Stack:** WordPress 7, PHP 8.3, ACF Pro Local JSON, DDEV.

## Global Constraints

- Public copy and editor labels remain Ukrainian.
- Do not add fields, dependencies, or a title-to-course lookup.
- Keep `/courses/programming-projects/` as the fallback for an empty editor URL.

---

### Task 1: Seed canonical URLs for homepage chips

**Files:**
- Modify: `scripts/seed-home-texts.php`
- Modify: `scripts/seed-home-section-images.php`
- Create: `tests/homepage-course-chip-targets.php`

**Interfaces:**
- Consumes: `home_programming_courses[3].chips[][label,url]`.
- Produces: four editable ACF URLs rendered as course links.

- [ ] **Step 1: Write the failing test**

Create `tests/homepage-course-chip-targets.php` to assert these URLs in the real ACF rows and rendered homepage:

```php
array(
	'/courses/python-expert/',
	'/courses/python-advanced/',
	'/courses/frontend/',
	'/courses/computer-literacy-14/',
)
```

- [ ] **Step 2: Run test to verify it fails**

Run: `ddev exec php /var/www/html/tests/homepage-course-chips.php`

Expected: failure because the four persisted chip URLs are empty and render the shared fallback.

- [ ] **Step 3: Write minimal implementation**

In the fourth `home_programming_courses` row in both seed scripts, add `chips` entries with each existing label and canonical `/courses/{slug}/` path. Keep labels and renderer code unchanged.

- [ ] **Step 4: Run tests to verify it passes**

Run: `ddev exec wp --path=wordpress eval-file /var/www/html/scripts/seed-home-texts.php && ddev exec wp --path=wordpress eval-file /var/www/html/scripts/seed-home-section-images.php && ddev exec php /var/www/html/tests/homepage-course-chip-targets.php && ddev exec php /var/www/html/tests/course-page.php`

Expected: both commands exit 0.

- [ ] **Step 5: Commit**

```bash
git add scripts/seed-home-texts.php scripts/seed-home-section-images.php tests/homepage-course-chip-targets.php
git commit -m "feat: link homepage chips to courses"
```
