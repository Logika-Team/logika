# Media Section Card Sizing Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Збільшити спільні картки секції «Чому тисячі батьків обирають Logika» на WordPress-сайті до розміру з дизайн-референсу.

**Architecture:** Візуальна зміна залишається в SCSS секції `media-section`; HTML/ACF/PHP не змінюються. Після зміни синхронізується згенерований CSS, який підключається для головної та city-home маршрутів.

**Tech Stack:** WordPress classic theme, PHP 8.3+, SCSS/Gulp, DDEV, browser smoke.

## Global Constraints

- Працювати тільки в `/home/sbaikov/Desktop/Projects/logika/.worktrees/wordpress`.
- Не видаляти й не перезаписувати несуміжні локальні зміни.
- Увесь видимий контент сайту залишається українською.
- Не додавати JavaScript, залежності або окремі стилі для кожної сторінки.
- Source SCSS залишається джерелом стилів; `build/` і theme assets — generated output.

---

### Task 1: Зафіксувати baseline і computed-розміри

**Files:**
- Read: `/home/sbaikov/Desktop/Projects/logika/.worktrees/wordpress/source/scss/blocks/sections/media-section.scss`
- Read: `/home/sbaikov/Desktop/Projects/logika/.worktrees/wordpress/wordpress/wp-content/themes/logika-theme/assets/css/blocks/sections/media-section.css`
- Read: `/home/sbaikov/Desktop/Projects/logika/.worktrees/wordpress/wordpress/wp-content/themes/logika-theme/functions.php`

**Interfaces:**
- Consumes: live WordPress homepage route and shared `.media-section__cards` markup.
- Produces: measured desktop/mobile baseline for the shared card group.

- [ ] **Step 1: Confirm the DDEV route and stylesheet loading**

Run:

```bash
ddev status
curl -fsS http://logika.ddev.site/ | rg -o 'media-section__cards|media-section__card(-img)?' | sort | uniq -c
```

Expected: the site responds successfully and the homepage contains six card elements.

- [ ] **Step 2: Record the current CSS contract**

Run:

```bash
rg -n 'max-width: 980px|max-width: 680px|max-width: 340px|width: min\(85%, 290px\)' source/scss/blocks/sections/media-section.scss
```

Expected: the existing desktop, tablet and mobile limits are visible before editing.

### Task 2: Increase the shared card scale

**Files:**
- Modify: `/home/sbaikov/Desktop/Projects/logika/.worktrees/wordpress/source/scss/blocks/sections/media-section.scss`
- Modify: generated `/home/sbaikov/Desktop/Projects/logika/.worktrees/wordpress/build/css/blocks/sections/media-section.css`
- Modify: generated `/home/sbaikov/Desktop/Projects/logika/.worktrees/wordpress/wordpress/wp-content/themes/logika-theme/assets/css/blocks/sections/media-section.css`
- Modify: generated `/home/sbaikov/Desktop/Projects/logika/.worktrees/wordpress/wordpress/wp-content/themes/logika-theme/assets/css/style.css` only if the project build updates its bundled copy.

**Interfaces:**
- Consumes: baseline measurements from Task 1.
- Produces: one shared responsive CSS contract for all pages using the section.

- [ ] **Step 1: Update only the shared sizing declarations**

Keep the existing card colors, transforms, image positioning and breakpoints. Adjust only the desktop card-group width/card height/image cap required by the measured reference; keep tablet and mobile values explicit so the mobile layout remains one column.

- [ ] **Step 2: Regenerate the project CSS**

Run:

```bash
npm run build
```

Expected: Gulp completes without errors and the generated section CSS contains the same `.media-section__cards`, `.media-section__card` and `.media-section__card-img` sizing declarations as the source SCSS.

- [ ] **Step 3: Sync generated CSS into the WordPress theme if build does not do so automatically**

Copy only the generated media-section CSS output into the matching theme asset path, preserving all unrelated files and local changes.

### Task 3: Verify markup, syntax and live rendering

**Files:**
- Test: `/home/sbaikov/Desktop/Projects/logika/.worktrees/wordpress/tests/main-static-pages-migration.php`
- Test: `/home/sbaikov/Desktop/Projects/logika/.worktrees/wordpress/tests/about-page-component.php`
- Test: `/home/sbaikov/Desktop/Projects/logika/.worktrees/wordpress/tests/theme-source-pages.php`

**Interfaces:**
- Consumes: generated CSS and existing shared page markup.
- Produces: evidence that all affected routes render six cards at the intended size.

- [ ] **Step 1: Run syntax and existing static-page checks**

Run:

```bash
ddev exec php -l /var/www/html/wordpress/wp-content/themes/logika-theme/source-pages/index.php
ddev exec php -l /var/www/html/wordpress/wp-content/themes/logika-theme/source-pages/about.php
ddev exec php /var/www/html/tests/main-static-pages-migration.php
ddev exec php /var/www/html/tests/about-page-component.php
ddev exec php /var/www/html/tests/theme-source-pages.php
```

Expected: every command exits with code 0.

- [ ] **Step 2: Check whitespace for the scoped files**

Run:

```bash
git diff --check -- source/scss/blocks/sections/media-section.scss build/css/blocks/sections/media-section.css wordpress/wp-content/themes/logika-theme/assets/css/blocks/sections/media-section.css wordpress/wp-content/themes/logika-theme/assets/css/style.css
```

Expected: no output and exit code 0.

- [ ] **Step 3: Verify the live homepage and secondary page**

Open the exact DDEV routes in a real browser at desktop and mobile widths. Confirm:

```text
.media-section__cards has six direct .media-section__card children
.media-section__cards is three columns on desktop
.media-section__card-img is visibly larger than the baseline and matches the reference proportion
mobile keeps one column without horizontal overflow
```

### Task 4: Update project plan and report the narrow diff

**Files:**
- Modify: `/home/sbaikov/Desktop/Projects/logika/.worktrees/wordpress/docs/plan.md`

- [ ] **Step 1: Mark the card-sizing task as completed in the project plan**

Add one Ukrainian line stating that the shared «Чому тисячі батьків обирають Logika» card sizing was synchronized and checked in DDEV.

- [ ] **Step 2: Review the final scoped diff**

Run:

```bash
git diff --stat -- source/scss/blocks/sections/media-section.scss build/css/blocks/sections/media-section.css wordpress/wp-content/themes/logika-theme/assets/css/blocks/sections/media-section.css wordpress/wp-content/themes/logika-theme/assets/css/style.css docs/plan.md
```

Expected: only the shared style outputs and the project-plan line are changed for this task.

