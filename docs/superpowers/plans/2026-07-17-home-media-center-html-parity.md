# Home Media Center HTML Parity Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Restore the original homepage Media Center HTML in the WordPress theme.

**Architecture:** The homepage renderer reads `source-pages/index.php` and applies only generic asset/link rewrites. Replace the divergent section there with the matching static-source section; do not add templates, data models, or runtime logic.

**Tech Stack:** PHP 8.3, WordPress theme, DDEV, source HTML.

## Global Constraints

- Preserve all unrelated dirty worktree changes.
- Public copy remains Ukrainian.
- Add no dependency or new abstraction.

---

### Task 1: Restore the Media Center source markup

**Files:**
- Modify: `tests/theme-source-pages.php`
- Modify: `wordpress/wp-content/themes/logika-theme/source-pages/index.php`

**Interfaces:**
- Consumes: `logika_theme_render_source_page( 'index' )`.
- Produces: homepage HTML containing the original `media-section` headed `Медіа-центр`.

- [ ] **Step 1: Write the failing contract**

Replace the stale empty-layout markers with the card-layout markers from the current root source:

```php
foreach ( array( 'media-section__cards-layout', 'media-section__news', 'media-section__promos', 'media-section__blog-list', 'media-section__background' ) as $marker ) {
	if ( ! str_contains( $homepage, $marker ) ) {
		$errors[] = "Homepage media center is missing {$marker}.";
	}
}
```

- [ ] **Step 2: Run the contract before the fix**

Run: `ddev exec php /var/www/html/tests/theme-source-pages.php`

Expected: failure reporting a missing original Media Center marker.

- [ ] **Step 3: Restore the minimal implementation**

Copy the complete `Медіа-центр` `<section class="media-section">` from `/home/sbaikov/Desktop/Projects/logika/source/index.html` into the matching section in `wordpress/wp-content/themes/logika-theme/source-pages/index.php`, leaving every other section untouched. The worktree's `source/index.html` is stale and must not be used.

- [ ] **Step 4: Run verification**

Run: `ddev exec php -l /var/www/html/wordpress/wp-content/themes/logika-theme/source-pages/index.php && ddev exec php /var/www/html/tests/theme-source-pages.php && curl -fsS http://logika.ddev.site/ | rg -q 'media-section__cards-layout'`

Expected: PHP syntax passes, the source-page contract passes, and the served homepage contains the card-layout marker.

### Task 2: Restore the Media Center stylesheet

**Files:**
- Modify: `tests/theme-source-pages.php`
- Modify: `source/scss/blocks/sections/media-section.scss`
- Modify: `wordpress/wp-content/themes/logika-theme/assets/css/style.css`

**Interfaces:**
- Consumes: the root source stylesheet at `/home/sbaikov/Desktop/Projects/logika/source/scss/blocks/sections/media-section.scss`.
- Produces: the existing theme stylesheet without the debug rule that gives every media child a red background and fixed height.

- [ ] **Step 1: Write the failing contract**

Require the loaded theme CSS not to contain the debug selector:

```php
if ( str_contains( $theme_css, '.media-section__box>div{height:400px;background-color:red}' ) ) {
	$errors[] = 'Homepage media center still contains the red debug layout rule.';
}
```

- [ ] **Step 2: Run the contract before the fix**

Run: `ddev exec php /var/www/html/tests/theme-source-pages.php`

Expected: failure reporting the red debug layout rule.

- [ ] **Step 3: Restore the source stylesheet and generated theme CSS**

Replace `source/scss/blocks/sections/media-section.scss` with its current root-source counterpart, run `npx gulp build`, then copy the generated `build/css/style.css` into the existing theme `assets/css/style.css`.

- [ ] **Step 4: Run verification**

Run: `ddev exec php /var/www/html/tests/theme-source-pages.php && curl -fsS http://logika.ddev.site/ | rg -q 'media-section__cards-layout' && git diff --check -- tests/theme-source-pages.php source/scss/blocks/sections/media-section.scss wordpress/wp-content/themes/logika-theme/assets/css/style.css`

Expected: contract and served-homepage checks pass; no scoped whitespace errors.
