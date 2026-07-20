# Homepage Student Projects Content Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax.

**Goal:** Keep one Maxym photo card and add three Tilda-sourced student project cards with game illustrations to the homepage carousel.

**Architecture:** Preserve the existing source-page fallback and ACF repeater override. Add a standard-card image fallback from `student_image` to `project_image`, then replace duplicated static Maxym cards with three project cards.

**Tech Stack:** WordPress, PHP 8.3, ACF Pro Local JSON, existing PHP templates and CSS assets.

## Global Constraints

- Work only in `/home/sbaikov/Desktop/Projects/logika/.worktrees/wordpress`.
- Keep existing dirty files untouched unless explicitly listed below.
- Keep the site copy Ukrainian except for original project names.
- Add no dependency and no database migration.
- Escape all dynamic text, URLs and attachment output.
- Use existing carousel CSS and asset conventions.

---

### Task 1: Lock the rendered content contract

**Files:**
- Modify: `tests/homepage-student-projects.php`

**Interfaces:**
- Consumes: `logika_theme_render_source_page('index')`.
- Produces: assertions for one Maxym photo card and the three named projects.

- [ ] **Step 1: Add failing assertions**

Add these checks after the existing dynamic-card checks:

```php
foreach ( array( 'Ян Корнієць', 'Tactical Strike Force', 'Ілля Шляпников', 'Shadow Light 2', 'Максим Кравченко', 'Chernobyl Horror' ) as $value ) {
	if ( ! str_contains( $homepage, $value ) ) {
		$errors[] = "Homepage fallback is missing {$value}.";
	}
}
```

- [ ] **Step 2: Run the test and confirm the failure**

Run: `ddev exec php tests/homepage-student-projects.php`

Expected: failure for the three new project names because the source fallback still contains duplicated Maxym cards.

### Task 2: Update the fallback carousel and image fallback

**Files:**
- Modify: `wordpress/wp-content/themes/logika-theme/source-pages/index.php:1017-1077`
- Modify: `wordpress/wp-content/themes/logika-theme/src/SourceMarkup.php:502-505`
- Modify: `wordpress/wp-content/plugins/logika-core/acf-json/group_logika_home.json:1691-1729`

**Interfaces:**
- Consumes: existing `home_portfolio_items` rows and static source markup.
- Produces: one standard Maxym photo card, one existing featured card, and three standard project cards. Standard ACF rows prefer `student_image` and fall back to `project_image`.

- [ ] **Step 1: Replace the three duplicated standard fallback cards**

Keep the first Maxym standard card and featured card. Replace the other three standard cards with:

```html
<li class="portfolio-section__card">
  <h3>Ян Корнієць, Львів</h3>
  <div class="portfolio-section__photo">
    <img src="img/portfolio/tactical-strike-force.png" alt="Ілюстрація проєкту Tactical Strike Force" loading="lazy">
    <span class="portfolio-section__tag portfolio-section__tag--course">Геймдизайн</span>
    <span class="portfolio-section__tag portfolio-section__tag--topic">Roblox</span>
  </div>
  <p>Ян створив Tactical Strike Force — власну бойову гру в Roblox і посів перше місце на конкурсі проєктів.</p>
</li>
<li class="portfolio-section__card">
  <h3>Ілля Шляпников, Харків</h3>
  <div class="portfolio-section__photo">
    <img src="img/portfolio/shadow-light-2.png" alt="Ілюстрація проєкту Shadow Light 2" loading="lazy">
    <span class="portfolio-section__tag portfolio-section__tag--course">Геймдизайн</span>
    <span class="portfolio-section__tag portfolio-section__tag--topic">Roblox</span>
  </div>
  <p>Ілля розробив Shadow Light 2 — власну гру в Roblox і посів друге місце на конкурсі проєктів.</p>
</li>
<li class="portfolio-section__card">
  <h3>Максим Кравченко, Дніпро</h3>
  <div class="portfolio-section__photo">
    <img src="img/portfolio/chernobyl-horror.png" alt="Ілюстрація проєкту Chernobyl Horror" loading="lazy">
    <span class="portfolio-section__tag portfolio-section__tag--course">Геймдизайн</span>
    <span class="portfolio-section__tag portfolio-section__tag--topic">Roblox</span>
  </div>
  <p>Максим створив Chernobyl Horror — власну гру та посів третє місце на конкурсі проєктів.</p>
</li>
```

- [ ] **Step 2: Use project illustrations for standard ACF rows without portraits**

Replace the standard-card image line with:

```php
$image = self::portfolioImage( $row['student_image'] ?? 0, $name . ', учень курсу ' . $course, 'medium', '' );
if ( '' === $image ) {
	$image = self::portfolioImage( $row['project_image'] ?? 0, 'Ілюстрація проєкту ' . $name, 'medium', '' );
}
```

Update the ACF `project_image` conditional logic so it is available for both `standard` and `featured` rows, while keeping `student_image` standard-only.

- [ ] **Step 3: Run the test and confirm the content passes**

Run: `ddev exec php tests/homepage-student-projects.php`

Expected: `Homepage student projects are editable and render correctly.`

### Task 3: Add the three verified illustrations

**Files:**
- Create: `wordpress/wp-content/themes/logika-theme/assets/img/portfolio/tactical-strike-force.png`
- Create: `wordpress/wp-content/themes/logika-theme/assets/img/portfolio/shadow-light-2.png`
- Create: `wordpress/wp-content/themes/logika-theme/assets/img/portfolio/chernobyl-horror.png`

**Interfaces:**
- Consumes: matching source assets from `/home/sbaikov/Documents/tilda-export/project917000/images/`.
- Produces: theme-local assets referenced by the fallback markup.

- [ ] **Step 1: Copy only the selected Tilda illustrations**

Use the existing export assets:

```text
tild6134-3337-4638-a461-636262623861__89789692.png
tild3161-3563-4565-a233-316436336335__89784373.png
tild3630-6532-4563-b939-373764656131__noroot.png
```

Copy them to the three target names above. Do not modify the external export.

- [ ] **Step 2: Verify the assets**

Run: `identify wordpress/wp-content/themes/logika-theme/assets/img/portfolio/*.png`

Expected: all three new files are readable PNG images.

### Task 4: Verify runtime and graph state

**Files:**
- Modify: generated `graphify-out/**` through `graphify update .` only.

- [ ] **Step 1: Run syntax and project tests**

Run:

```bash
php -l wordpress/wp-content/themes/logika-theme/src/SourceMarkup.php
ddev exec php tests/homepage-student-projects.php
```

- [ ] **Step 2: Refresh Graphify**

Run: `graphify update .`

- [ ] **Step 3: Check the diff boundary**

Run: `git status --short` and confirm only the listed content, asset, test, plan and graph files changed; preserve all pre-existing modifications.
