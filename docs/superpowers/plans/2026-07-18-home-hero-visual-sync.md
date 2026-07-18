# Homepage Hero Visual Sync Implementation Plan

**Goal:** Align the homepage hero copy and visual details with the supplied reference screenshot.

**Architecture:** Keep the existing source-page renderer and ACF field contract. Update the hero fallback/seed text, apply the existing `--light-blue` token to the form badge, and strengthen only the homepage trust-bar shadow in both SCSS and the WordPress runtime CSS.

**Tech Stack:** WordPress, PHP 8.3, ACF Pro, SCSS, compiled CSS, DDEV.

## Global Constraints

- Modify only the homepage hero surface in the `wordpress` worktree.
- Reuse existing CSS tokens and classes; add no dependencies or new ACF fields.
- Preserve existing ACF overrides and form behavior.
- Verify the live route at `http://logika.ddev.site/` and run the existing homepage/source-page checks.

### Task 1: Align homepage hero copy and styles

**Files:**
- Modify: `wordpress/wp-content/themes/logika-theme/source-pages/index.php`
- Modify: `wordpress/wp-content/themes/logika-theme/src/SourceMarkup.php`
- Modify: `wordpress/wp-content/themes/logika-theme/template-parts/sections/hero.php`
- Modify: `scripts/seed-home-texts.php`
- Modify: `source/scss/blocks/sections/banner-section.scss`
- Modify: `wordpress/wp-content/themes/logika-theme/assets/css/blocks/sections/banner-section.css`
- Modify: `wordpress/wp-content/themes/logika-theme/assets/css/style.css`

- [x] Set the default hero title to `Програмування та англійська мова для дітей 7-30 років` in every active fallback/seed path.
- [x] Keep the form badge text `Перший урок — безкоштовно.` and set it explicitly to `var(--light-blue)`.
- [x] Set the homepage subtitle to the existing purple heading token.
- [x] Increase only `.banner-section__bar` shadow opacity/spread.
- [x] Update the local front-page ACF value so the live homepage uses the approved copy.

### Task 2: Verify the homepage

- [x] Run PHP syntax checks and the existing homepage source-page test.
- [x] Fetch the live homepage and assert the approved copy, `#DDF0FB` token, and stronger shadow are present.
- [x] Run `graphify update .` and inspect the final diff without staging unrelated work.
