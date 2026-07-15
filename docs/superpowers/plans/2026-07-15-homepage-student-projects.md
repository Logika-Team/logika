# Homepage Student Projects Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the homepage student-projects placeholder with the supplied source section and make its title and cards editable through ACF Pro.

**Architecture:** Keep the source-page migration pattern: `source-pages/index.php` holds the default markup and assets, while `Logika_Theme_Source_Markup` replaces the card list when the front-page ACF repeater has rows. The Local JSON group in `logika-core` owns editor data; empty fields retain the source fallback.

**Tech Stack:** WordPress, ACF Pro Local JSON, PHP 8.3, existing SCSS/Gulp assets.

## Global Constraints

- Work only in `.worktrees/wordpress` on the existing `wordpress` branch.
- Keep Ukrainian editor labels and site copy.
- Add no dependency or database migration.
- Escape text and URLs; render attachment images through WordPress helpers.
- Preserve the source fallback when no ACF rows exist.

---

### Task 1: Define the public and editor contracts

**Files:**
- Create: `tests/homepage-student-projects.php`
- Modify: `wordpress/wp-content/plugins/logika-core/acf-json/group_logika_home.json`
- Modify: `docs/guidelines/content-model.md`

**Interfaces:**
- Consumes: `group_logika_home` and WordPress front-page ACF storage.
- Produces: `home_portfolio_items`, a repeater of `variant`, `student_name`, `student_age`, `course`, `topic`, `description`, `student_image`, `project_image`, `video_url`, `cta_label`, and `cta_url`.

- [ ] **Step 1: Write the failing test**

```php
$field = current( array_filter( acf_get_fields( 'group_logika_home' ), static fn( array $item ): bool => 'home_portfolio_items' === $item['name'] ) );
if ( ! $field || 'repeater' !== $field['type'] ) {
    $errors[] = 'Homepage projects are not editable through an ACF repeater.';
}
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `cd .worktrees/wordpress && ddev exec php tests/homepage-student-projects.php`

Expected: `Homepage projects are not editable through an ACF repeater.`

- [ ] **Step 3: Add the Local JSON field and content-model entry**

Add the named repeater beneath the homepage projects title, with Ukrainian instructions and a fixed `standard`/`featured` choice. Document that the homepage page owns the ordered project cards and uses the supplied static cards as fallback.

- [ ] **Step 4: Run the test to verify it passes**

Run: `cd .worktrees/wordpress && ddev exec php tests/homepage-student-projects.php`

Expected: the ACF contract passes; the test still reports the missing source section until Task 2.

### Task 2: Render and style the section

**Files:**
- Modify: `wordpress/wp-content/themes/logika-theme/source-pages/index.php`
- Modify: `wordpress/wp-content/themes/logika-theme/src/SourceMarkup.php`
- Modify: `source/scss/blocks/sections/portfolio-section.scss`
- Create: `source/img/portfolio/maxym.jpg`
- Create: `source/img/portfolio/computer-game.png`
- Modify: `wordpress/wp-content/themes/logika-theme/assets/**` through the existing build/sync flow

**Interfaces:**
- Consumes: `home_portfolio_title` and `home_portfolio_items` from the front page.
- Produces: accessible project cards, with a static fallback and an ACF-driven card list.

- [ ] **Step 1: Keep the failing test focused on rendered behavior**

```php
ob_start();
logika_theme_render_source_page( 'index' );
$homepage = ob_get_clean();
if ( ! str_contains( $homepage, 'portfolio-section__card--featured' ) ) {
    $errors[] = 'Homepage does not render the featured student project card.';
}
```

- [ ] **Step 2: Implement the smallest renderer**

Replace the placeholder with the source card markup, then replace only the `portfolio-section__slider` contents when the ACF repeater has valid rows. Use `esc_html()`, `esc_url()`, and `wp_get_attachment_image()`.

- [ ] **Step 3: Build and sync generated assets**

Run: `cd .worktrees/wordpress && npm run build`

Expected: the portfolio CSS and image assets are generated, then copied to the theme asset directory by the project’s existing sync convention.

- [ ] **Step 4: Verify the complete contract**

Run: `cd .worktrees/wordpress && ddev exec php tests/homepage-student-projects.php && ddev exec php tests/theme-source-pages.php`

Expected: both scripts exit 0.

- [ ] **Step 5: Browser smoke**

Open `http://logika.ddev.site/` at desktop and narrow mobile widths. Confirm the section has regular and featured cards, its horizontal track is scrollable, and the trial CTA reaches `#lead-form`.
