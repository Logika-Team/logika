# Review Image Reset Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Preserve each review's original photo and allow editors to restore it.

**Architecture:** Keep `review_photo` as the current public value and save `review_original_photo` once as private post meta. Extend the existing image-override admin script rather than add another script.

**Tech Stack:** WordPress 7, PHP 8.3, ACF Pro 6, jQuery ACF input API.

## Global Constraints

- Keep the public review template unchanged.
- Keep editor text in Ukrainian.
- Use DDEV for runtime validation.

---

### Task 1: Preserve and restore the original review photo

**Files:**
- Modify: `wordpress/wp-content/plugins/logika-core/src/HomepageImageOverrides.php`
- Modify: `wordpress/wp-content/plugins/logika-core/assets/js/homepage-image-overrides.js`
- Test: `tests/review-image-overrides.php`
- Test: `tests/review-image-overrides-runtime.php`

- [x] **Step 1: Write failing tests** for the review field, original meta and reset render call.
- [x] **Step 2: Verify red** with `ddev exec php /var/www/html/tests/review-image-overrides.php`.
- [x] **Step 3: Save the source ID once** on `acf/save_post` before and after ACF persists the current image.
- [x] **Step 4: Extend the existing override panel** to show the original image and render it on reset.
- [x] **Step 5: Verify green** with both review tests, the homepage regression test, PHP lint and ACF JSON dry-run sync.

### Task 2: Make every homepage testimonial photo editable

**Files:**
- Modify: `wordpress/wp-content/plugins/logika-core/acf-json/group_logika_home.json`
- Modify: `wordpress/wp-content/themes/logika-theme/src/Testimonials.php`
- Test: `tests/homepage-testimonials-images-static.php`
- Test: `tests/homepage-testimonials-images-runtime.php`

- [x] **Step 1: Write a failing static contract** for a four-item gallery, review avatar and decorative-card rendering.
- [x] **Step 2: Verify red** with `ddev exec php /var/www/html/tests/homepage-testimonials-images-static.php`.
- [x] **Step 3: Add `home_testimonials_image_1` … `home_testimonials_image_4`** as four ordered image fields.
- [x] **Step 4: Render the gallery and `review_photo`** into the existing cards without changing their markup hierarchy.
- [x] **Step 5: Verify green** with static and DDEV rendering tests plus ACF JSON dry-run sync.

### Task 3: Provide testimonial photos in every context

**Files:**
- Create: `wordpress/wp-content/plugins/logika-core/acf-json/group_logika_testimonials_images.json`
- Modify: `wordpress/wp-content/plugins/logika-core/acf-json/group_logika_camp_archive.json`
- Modify: `wordpress/wp-content/themes/logika-theme/src/Testimonials.php`
- Modify: `wordpress/wp-content/themes/logika-theme/src/SourceMarkup.php`
- Modify: `wordpress/wp-content/plugins/logika-core/src/ContentMigration.php`
- Test: `tests/testimonials-images-context-static.php`

- [x] **Step 1: Write a failing context-field contract.**
- [x] **Step 2: Add four per-entity image fields and the camp archive equivalent.**
- [x] **Step 3: Pass page, city, course, camp or archive context into the shared renderer.**
- [x] **Step 4: Idempotently seed blank contexts from the existing design asset.**
- [x] **Step 5: Verify ACF JSON, PHP, DDEV and the image-field contract.**
