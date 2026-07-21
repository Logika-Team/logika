# About page ACF implementation plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make every visible About-page text, content image, CTA label and repeatable card editable on the WordPress page using ACF while preserving `/about/` markup and fallbacks.

**Architecture:** Keep `source-pages/about.php` as the public markup source. Version the page fields in `logika-core/acf-json`, seed only missing values through `ContentMigration`, and hydrate the source HTML through `Logika_Theme_Page_Content` with context-safe escaping.

**Tech Stack:** WordPress 7.0, PHP 8.3+, ACF Pro 6.x, ACF Local JSON, DDEV.

## Global Constraints

- The editor surface is the page with template `templates/page-about.php`.
- All admin labels and default copy are Ukrainian.
- Preserve current DOM classes, lead-form attributes and route behaviour.
- Use named, stable snake_case fields; do not introduce Flexible Content or a second renderer.
- Keep empty fields non-destructive: render the existing source markup fallback.
- Escape text, rich text, URLs and image attributes by output context.
- Do not touch unrelated dirty files in the `wordpress` worktree.

---

### Task 1: Prove the missing About-page ACF contract

**Files:**
- Create: `tests/about-page-acf.php`
- Test: `tests/about-page-acf.php`

**Interfaces:**
- Consumes: `group_logika_page_about.json`, the `about` page, `Logika_Theme_Page_Content::apply(string, string, int): string`.
- Produces: a focused non-zero exit check when any About content field or its source-markup replacement is missing.

- [ ] **Step 1: Write the failing test**

```php
$required = array(
	'about_hero_form_title', 'about_hero_form_text', 'about_hero_cta_label',
	'about_history_cta_label',
	'about_map_offline_label', 'about_map_online_label', 'about_map_city_label',
	'about_cta_submit_label', 'about_certificates_title', 'about_certificates_text',
	'about_certificates_button_label', 'about_certificates_image',
	'about_partners_title', 'about_partners_items',
);

foreach ( $required as $name ) {
	if ( ! in_array( $name, $field_names, true ) ) {
		$errors[] = "Missing About ACF field {$name}.";
	}
}
```

- [ ] **Step 2: Run the test and verify it fails**

Run: `ddev exec php /var/www/html/tests/about-page-acf.php`

Expected: FAIL with at least `Missing About ACF field about_hero_cta_label.`.

- [ ] **Step 3: Add the rendering assertion to the same test**

```php
update_field( 'about_history_cta_label', 'Редагований пробний урок', $about_id );
$rendered = Logika_Theme_Page_Content::apply( $source, 'about', $about_id );
if ( ! str_contains( $rendered, 'Редагований пробний урок' ) ) {
	$errors[] = 'About history CTA does not render its ACF label.';
}
```

The test must restore the original field value in a `finally` block so reruns leave the database unchanged.

- [ ] **Step 4: Commit the red test**

```bash
git add tests/about-page-acf.php
git commit -m "test: cover about page ACF contract"
```

### Task 2: Complete the versioned About field group and migration defaults

**Files:**
- Modify: `wordpress/wp-content/plugins/logika-core/acf-json/group_logika_page_about.json`
- Modify: `wordpress/wp-content/plugins/logika-core/src/ContentMigration.php:274-311`

**Interfaces:**
- Consumes: the existing `about` page ACF group and `ContentMigration::fill(string, mixed, int): void`.
- Produces: stable field names and idempotent initial values for all remaining About-only content.

- [ ] **Step 1: Add deterministic ACF fields, retaining all existing keys and names**

Add these named fields to the existing Ukrainian tabs:

```text
Hero: about_hero_form_title, about_hero_form_text, about_hero_cta_label,
      about_hero_consent_text, about_hero_background_image,
      about_hero_pattern_image, about_hero_character_image
History: about_history_cta_label
Onboarding rows: cta_label (sub-field of about_onboarding_items)
Map: about_map_offline_label, about_map_online_label, about_map_city_label
CTA: about_cta_submit_label, about_cta_consent_text,
     about_cta_character_image, about_cta_top_background_image,
     about_cta_bottom_background_image
Certificates: about_certificates_title, about_certificates_subtitle,
              about_certificates_text, about_certificates_button_label,
              about_certificates_image, about_certificates_background_image
Partners: about_partners_title, about_partners_items[name,image]
```

Every image field uses `return_format: "id"`; every repeater sub-field has a unique `field_about_*` key and Ukrainian editor instructions.

- [ ] **Step 2: Extend the failing test with the full list above and run it**

Run: `ddev exec php /var/www/html/tests/about-page-acf.php`

Expected: schema checks pass; the history-CTA rendering assertion still fails.

- [ ] **Step 3: Seed only empty About fields**

Extend the `about` array in `ContentMigration::applyPage()` with source-equivalent Ukrainian values and current assets, for example:

```php
'about_hero_form_title' => 'Перший урок — безкоштовно.',
'about_hero_form_text' => 'Залиште заявку за 30 секунд — ми зателефонуємо і підберемо зручний час',
'about_history_cta_label' => 'Безкоштовний пробний урок',
'about_certificates_title' => 'Ура, подарункові сертифікати',
'about_partners_title' => 'Наші партнери',
```

Use `self::fill()` for every seed so a saved editor value is preserved on repeated migration runs.

- [ ] **Step 4: Verify JSON and migration idempotency**

Run:

```bash
php -r 'json_decode(file_get_contents("wordpress/wp-content/plugins/logika-core/acf-json/group_logika_page_about.json"), true, 512, JSON_THROW_ON_ERROR);'
./scripts/wp-mcp.sh logika acf-migrate --dry-run
```

Expected: valid JSON and a dry-run report without duplicate attachments or changed saved About values.

- [ ] **Step 5: Commit the schema and seeds**

```bash
git add wordpress/wp-content/plugins/logika-core/acf-json/group_logika_page_about.json wordpress/wp-content/plugins/logika-core/src/ContentMigration.php tests/about-page-acf.php
git commit -m "feat: complete about page ACF fields"
```

### Task 3: Hydrate every remaining About section through the existing adapter

**Files:**
- Modify: `wordpress/wp-content/themes/logika-theme/src/PageContent.php:8-169, 650-702, 723-800`
- Test: `tests/about-page-acf.php`

**Interfaces:**
- Consumes: About ACF values by ID and the existing source markup classes.
- Produces: `Logika_Theme_Page_Content::applyAboutPageFields(string, int): string` and `Logika_Theme_Page_Content::replacePatternField(string, int, string, string): string`, called only for source `about`.

- [ ] **Step 1: Keep the test red for each source section**

Add assertions that set one value per section (`about_hero_cta_label`, `about_history_skills`, onboarding `cta_label`, map labels, CTA submit label, certificates title/image, partner image) and confirm the rendered markup contains the new text or attachment URL.

- [ ] **Step 2: Implement one About-only adapter method**

```php
private static function replacePatternField( string $markup, int $page_id, string $field, string $pattern ): string {
	$value = trim( (string) get_field( $field, $page_id ) );
	return '' === $value ? $markup : self::replaceLeaf( $markup, $pattern, $value );
}

private static function applyAboutPageFields( string $markup, int $page_id ): string {
	$markup = self::replacePatternField( $markup, $page_id, 'about_hero_form_title', '#(<div class="main-form__title h5"><span>)(.*?)(</span>)#s' );
	$markup = self::replacePatternField( $markup, $page_id, 'about_history_cta_label', '#(<button class="about-history__btn[^>]*>)(.*?)(\s*<svg)#s' );
	$markup = self::replacePatternField( $markup, $page_id, 'about_cta_submit_label', '#(<button class="cta-form__btn[^>]*>)(.*?)(\s*<svg)#s' );
	return self::applyAboutPartners( self::applyAboutCertificates( self::applyAboutLabels( $markup, $page_id ), $page_id ), $page_id );
}
```

`applyAboutLabels()` calls `replacePatternField()` for these exact field/markup pairs: `about_hero_title`/`.banner-section__info h1`, `about_hero_text`/`.banner-section__info h4`, `about_hero_form_text`/`.main-form__title` after its `<span>`, `about_hero_cta_label`/`.main-form__btn`, `about_hero_consent_text`/`.main-form__text`, `about_stats_title`/`#about-stats__title`, `about_outcomes_title`/`#about-outcomes-title`, `about_history_title`/`#about-history-title`, `about_media_title`/`.media-section__title`, `about_onboarding_title`/`.onboarding-section__title`, `about_map_title`/`#school-map-title`, `about_map_text`/`.school-map__heading p`, `about_map_offline_label`/`[data-map-mode="offline"]`, `about_map_online_label`/`[data-map-mode="online"]`, `about_map_city_label`/`.school-map__selector h3`, `about_cta_title`/`.cta-form__title`, `about_cta_subtitle`/`.cta-form__subtitle`, `about_cta_consent_text`/`.cta-form__text`, `about_faq_title`/`.faq-section__title`.

`applyAboutCertificates()` replaces the `certificates-section` `h2`, `h5`, `p`, button text, preview image and background image from the six `about_certificates_*` fields. `applyAboutPartners()` replaces `.partners-section__title` and `.partners-section__gallery` rows; each row uses `name` as its escaped image alt text and `image` as its attachment URL. Extend `hydrateListItem()` to set the first image `alt` only when a row contains `name` or `alt`.

Remove the `about` entry from `TEXT_FIELDS` so equal fallback text cannot be replaced in the wrong section. Call `applyAboutPageFields()` after `applyAboutImageRows()` only when `$source === 'about'`. Reuse `replaceListRows()` for onboarding and partners; broaden its CTA selector to handle the existing `<button class="... btn ...">` markup without changing the lead-form attributes.

- [ ] **Step 3: Apply images safely**

Add `'about' => 'about_gallery'` to `applyGallery()` so the existing gallery field replaces its slider slides. Replace only the exact About asset slot. Map `about_hero_image`, `about_hero_background_image`, `about_hero_pattern_image`, `about_hero_character_image`, `about_history_image`, `about_cta_image`, `about_cta_character_image`, `about_cta_top_background_image`, `about_cta_bottom_background_image`, `about_certificates_image` and `about_certificates_background_image` to their matching source asset paths. Use `wp_get_attachment_image_url((int) $image, 'large')`, `esc_url()` for `src`, and the Media Library attachment alt text for `alt`; retain source URLs when an image field is empty.

- [ ] **Step 4: Run the focused test until green**

Run: `ddev exec php /var/www/html/tests/about-page-acf.php`

Expected: `About page ACF fields render through the source-markup adapter.`

- [ ] **Step 5: Commit the renderer**

```bash
git add wordpress/wp-content/themes/logika-theme/src/PageContent.php tests/about-page-acf.php
git commit -m "feat: render full about page content from ACF"
```

### Task 4: Run runtime verification and record the finished migration

**Files:**
- Modify: `docs/guidelines/plan.md`
- Modify: `/home/sbaikov/Obsidian/obsidian-backup/Obsidian vault/Projects/logika-school/docs/changelog/2026-07.md`

**Interfaces:**
- Consumes: versioned Local JSON, the existing DDEV WordPress runtime and the About page route.
- Produces: verified ACF sync and a concise project record.

- [ ] **Step 1: Run project checks**

Run:

```bash
./scripts/release/run-wordpress-tests.sh
./scripts/wp-mcp.sh acf json sync --dry-run
ddev exec php /var/www/html/tests/about-page-acf.php
curl -fsS http://logika.ddev.site/about/ | rg -q 'about-history|about-certificates|partners-section'
```

Expected: every command exits `0`.

- [ ] **Step 2: Confirm the real editor surface**

Run: `ddev wp post list --path=wordpress --post_type=page --name=about --fields=ID,post_title,post_status --format=table`

Expected: one published About page using `templates/page-about.php`; its ACF tabs expose all fields from Task 2.

- [ ] **Step 3: Record completion**

Mark the new About ACF task complete in `docs/guidelines/plan.md` and append a dated Ukrainian bullet to the monthly Obsidian changelog. Do not modify unrelated checklist items.

- [ ] **Step 4: Commit verification documentation**

```bash
git add docs/guidelines/plan.md
git commit -m "docs: record about page ACF migration"
```
