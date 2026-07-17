# IT Courses Admin Layout Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make IT Courses fields appear in the correct admin tabs and make card image controls discoverable.

**Architecture:** Keep field values and renderer contracts unchanged. Reorder existing ACF Local JSON fields, restrict the broad testimonial-image group away from the IT Courses template, and retain course images on each course CPT.

**Tech Stack:** WordPress 7.0.1, ACF Pro Local JSON, PHP 8.3, DDEV.

## Global Constraints

- ACF source of truth: `wordpress/wp-content/plugins/logika-core/acf-json/`.
- Keep existing ACF field names and keys stable.
- Do not invent portfolio or review content.

---

### Task 1: Admin field placement

**Files:**

- Modify: `wordpress/wp-content/plugins/logika-core/acf-json/group_logika_page_it_courses.json`
- Modify: `wordpress/wp-content/plugins/logika-core/acf-json/group_logika_testimonials_images.json`
- Modify: `wordpress/wp-content/plugins/logika-core/acf-json/group_logika_course.json`
- Create: `tests/it-courses-admin-fields.php`

- [ ] Write a failing static test requiring the IT Courses review image fields to follow the Reviews tab, the catalog images to follow the Catalog tab, and a course Basic tab to precede card images.
- [ ] Run `ddev exec php /var/www/html/tests/it-courses-admin-fields.php` and verify failure.
- [ ] Reorder Local JSON fields without renaming their keys; scope the generic testimonial image group away from the IT Courses page template.
- [ ] Run the same test and verify success.
- [ ] Run `ddev exec php /var/www/html/tests/course-content.php` and `ddev exec php /var/www/html/tests/course-cards.php`.
