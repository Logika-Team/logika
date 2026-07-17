# Lead Modal Migration Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the WordPress lesson lead modal with the ready-made white/yellow modal from the pulled `main` branch while preserving the shared hero lead pipeline.

**Architecture:** Keep `assets/js/main.js` as the WordPress modal controller and `assets/js/leads.js` as the single country-code, age-select, validation and REST-submit pipeline. Adapt the pulled modal markup/styles/assets into the existing WordPress component and add only a presentation variant to the shared lead form so hidden context, token and idempotency fields remain identical to the hero form.

**Tech Stack:** WordPress 7.0, PHP 8.3, custom classic theme, `intl-tel-input` 20.1.0, existing REST lead endpoint, Gulp/Sass source build, DDEV.

## Global Constraints

- Replace only the lesson/application modal; leave the camp-shift modal unchanged.
- Use the pulled `source/partials/modals.html`, modal SCSS and modal image/icon as the visual source.
- Reuse the existing `data-logika-lead-form`, `leads.js`, REST token endpoint and lead endpoint.
- Country code must use the existing `data-logika-phone-input`/`intl-tel-input` contract.
- Child age must use `Logika_Theme_Lead_Form::render_age_select()` and the existing accessible age behavior.
- Public text remains Ukrainian; no new dependency or endpoint.
- Preserve unrelated dirty worktree changes and stage only files belonging to this feature.

---

### Task 1: Add the modal contract regression test

**Files:**
- Create: `tests/lead-modal.php`
- Modify: `docs/guidelines/plan.md`

**Interfaces:**
- Consumes: WordPress bootstrap and `logika_theme_render_lead_modal()`.
- Produces: a focused executable contract that fails while the old purple modal is rendered and passes after the pulled modal is integrated.

- [x] **Step 1: Write the failing test**

Create `tests/lead-modal.php`:

```php
<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

ob_start();
logika_theme_render_lead_modal();
$markup = (string) ob_get_clean();
$errors = array();

foreach (
	array(
		'class="modal"',
		'class="modal__container is-lesson"',
		'data-target="lesson"',
		'name="name"',
		'data-logika-phone-input',
		'data-logika-age-select',
		'name="child_age"',
		'data-logika-lead-form',
		'name="form_id" value="trial_lesson"',
		'name="idempotency_key"',
		'img/modal-image.webp',
	) as $marker
) {
	if ( ! str_contains( $markup, $marker ) ) {
		$errors[] = "Modal is missing {$marker}.";
	}
}

if ( str_contains( $markup, 'class="lead-modal"' ) ) {
	$errors[] = 'The legacy purple lead modal is still rendered.';
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Lesson lead modal contract is valid.\n";
```

- [x] **Step 2: Run the test to verify it fails**

Run: `ddev exec php tests/lead-modal.php`

Expected: FAIL because the current component still contains `data-logika-lead-modal` and does not contain the pulled `modal__container` structure.

- [x] **Step 3: Add the plan checklist entry**

Append under the existing `Lead CTA modal` section in `docs/guidelines/plan.md`:

```markdown
### 2026-07-17: Перенесення готової модалки заявки

- [x] Замінити lesson-модалку на готову біло-жовту розмітку з pulled main.
- [x] Зберегти спільні country code, вік дитини, token, idempotency та REST submit.
- [x] Перевірити всі CTA та живе надсилання заявки в DDEV.
```

- [x] **Step 4: Run the test again**

Run: `ddev exec php tests/lead-modal.php`

Expected: The same RED result remains until the component implementation in Task 2.

---

### Task 2: Port the ready-made modal and reuse the lead form contract

**Files:**
- Modify: `wordpress/wp-content/themes/logika-theme/template-parts/components/lead-modal.php`
- Modify: `wordpress/wp-content/themes/logika-theme/template-parts/forms/lead.php`
- Copy: `source/img/modal-image.webp` to `wordpress/wp-content/themes/logika-theme/assets/img/modal-image.webp`
- Copy: `source/img/sprite/icon-close.svg` to `wordpress/wp-content/themes/logika-theme/assets/img/sprite/icon-close.svg`

**Interfaces:**
- Consumes: `Logika_Theme_Lead_Form::render_age_select()`, `get_template_part()` args and the existing hidden lead fields.
- Produces: `.modal` → `.modal__container[data-target="lesson"]` markup containing a `data-logika-lead-form` compatible with `leads.js`.

- [x] **Step 1: Replace the component wrapper with the pulled lesson markup**

Use the pulled structure, with WordPress asset URLs and a modal-form presentation:

```php
<?php

declare(strict_types=1);

$assets = get_template_directory_uri() . '/assets';
?>
<div class="modal" data-logika-modal hidden>
	<div class="modal__container is-lesson" data-target="lesson">
		<div class="modal__wrapper" role="dialog" aria-modal="true" aria-labelledby="lead-modal-title">
			<button class="modal__close modal-close" type="button" aria-label="Закрити форму">
				<img width="24" height="24" src="<?php echo esc_url( $assets . '/img/sprite/icon-close.svg' ); ?>" alt="">
			</button>
			<div class="modal__lesson">
				<div class="modal__lesson-image"><img width="560" height="300" src="<?php echo esc_url( $assets . '/img/modal-image.webp' ); ?>" alt=""></div>
				<h2 class="visually-hidden" id="lead-modal-title">Перший урок — безкоштовно.</h2>
				<?php get_template_part( 'template-parts/forms/lead', null, array( 'class' => 'modal-form', 'presentation' => 'modal' ) ); ?>
			</div>
		</div>
	</div>
</div>
```

The final component must keep the existing `hidden` initial state and must not render the camp-shift container.

- [x] **Step 2: Extend the shared form template with the modal presentation**

In `template-parts/forms/lead.php`, derive `$is_modal = 'modal' === (string) ( $args['presentation'] ?? '' );`. For the modal branch, use the pulled labels/classes while retaining the same field names and hooks:

```php
<form class="<?php echo esc_attr( $form_class ); ?>" data-logika-lead-form novalidate>
	<div class="modal-form__labels">
		<label class="modal-form__label"><span>Ім’я</span><input class="modal-form__input main-form__input" type="text" name="name" placeholder="Ім’я Прізвище" required></label>
		<label class="modal-form__label"><span>Телефон у форматі 380:</span><div class="main-form__phone-wrap"><input class="modal-form__input main-form__input main-form__phone" type="tel" name="phone" placeholder="380" data-logika-phone-input aria-describedby="logika-phone-error" required><span class="main-form__phone-error" id="logika-phone-error" data-logika-phone-error hidden>Введіть коректний номер телефону</span></div></label>
		<label class="modal-form__label"><span>Вік дитини</span><?php echo Logika_Theme_Lead_Form::render_age_select(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></label>
	</div>
	<input type="hidden" name="form_id" value="trial_lesson"><input type="hidden" name="consent_accepted" value="1"><input type="hidden" name="consent_text_version" value="<?php echo esc_attr( get_field( 'form_privacy_text_version', 'option' ) ?: 'v1' ); ?>"><input type="hidden" name="idempotency_key" value=""><input type="hidden" name="city_id" value="<?php echo esc_attr( $city_id ); ?>"><input type="hidden" name="course_id" value="<?php echo esc_attr( $course_id ); ?>"><input class="main-form__honeypot" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
	<p class="modal-form__text main-form__text">Натискаючи, ви погоджуєтесь із <a href="<?php echo esc_url( $privacy_url ); ?>">Політикою конфіденційності</a></p>
	<button class="modal-form__btn main-form__btn btn btn--yellow" type="submit">Надіслати</button><p class="main-form__status" aria-live="polite"></p>
</form>
```

Keep the existing non-modal markup unchanged. The phone wrapper must retain `main-form__phone-wrap` and the input must retain `data-logika-phone-input` so the shared country picker and validation continue to initialize.

- [x] **Step 3: Copy the pulled binary/SVG assets**

Run:

```bash
cp /home/sbaikov/Desktop/Projects/logika/source/img/modal-image.webp wordpress/wp-content/themes/logika-theme/assets/img/modal-image.webp
cp /home/sbaikov/Desktop/Projects/logika/source/img/sprite/icon-close.svg wordpress/wp-content/themes/logika-theme/assets/img/sprite/icon-close.svg
```

Expected: both target files have the same SHA-256 as their pulled source files.

- [x] **Step 4: Run the contract test to verify it passes**

Run: `ddev exec php tests/lead-modal.php`

Expected: `Lesson lead modal contract is valid.`

---

### Task 3: Apply pulled styles, preserve triggers and verify the live flow

**Files:**
- Create: `wordpress/wp-content/themes/logika-theme/assets/css/lead-modal.css`
- Modify: `wordpress/wp-content/themes/logika-theme/functions.php`
- Modify: `wordpress/wp-content/themes/logika-theme/assets/js/main.js`
- Modify: `wordpress/wp-content/themes/logika-theme/assets/js/leads.js`
- Modify: `tests/lead-modal.php`
- Modify: `docs/guidelines/plan.md`

**Interfaces:**
- Consumes: pulled modal SCSS/CSS, current `#lead-form` CTA routing, `logika-leads.js` and DDEV REST endpoints.
- Produces: every existing lesson CTA opens the new modal; form submit still reaches `/wp-json/logika/v1/leads`.

- [x] **Step 1: Add a trigger contract assertion**

Extend `tests/lead-modal.php` to render the homepage and assert that the shared modal trigger route remains present:

```php
ob_start();
logika_theme_render_source_page( 'index' );
$home_markup = (string) ob_get_clean();
if ( ! str_contains( $home_markup, '/#lead-form' ) && ! str_contains( $home_markup, 'href="#lead-form"' ) ) {
	$errors[] = 'Homepage does not retain a lesson lead CTA target.';
}
```

- [x] **Step 2: Run the focused test before the trigger/style change**

Run: `ddev exec php tests/lead-modal.php`

Expected: PASS for the modal contract; if the homepage assertion fails, preserve the existing `#lead-form` routing before continuing.

- [x] **Step 3: Add only the pulled lesson modal styles**

Port the compiled selectors from `source/scss/general/_modals.scss` and `source/scss/components/forms/_modal-form.scss` into the dedicated `assets/css/lead-modal.css`, enqueue it after the theme stylesheet, and keep the existing `.main-form` rules. Add only modal-specific overrides for `.modal-form`, `.modal-form__input`, `.modal-form__labels`, `.modal__lesson-image`, the close button and responsive behavior. Do not add the static camp modal markup or a new dependency.

- [x] **Step 4: Update the WordPress modal controller to target the new wrapper**

Keep scroll locking and focus restoration, but change selectors so the controller uses `[data-logika-modal]`, `.modal-close`, and the `.modal` backdrop boundary. Preserve course context assignment to the hidden `course_id` input and intercept all same-site lesson links whose resolved hash is `#lead-form`.

- [x] **Step 5: Keep the shared submit payload complete**

After generating the hidden idempotency key, copy it into the `data` object before the JSON request. This keeps the modal and hero forms on the same server-side deduplication contract.

- [x] **Step 6: Verify JavaScript and PHP syntax**

Run:

```bash
node --check wordpress/wp-content/themes/logika-theme/assets/js/main.js
node --check wordpress/wp-content/themes/logika-theme/assets/js/leads.js
ddev exec php -l wordpress/wp-content/themes/logika-theme/template-parts/components/lead-modal.php
ddev exec php -l wordpress/wp-content/themes/logika-theme/template-parts/forms/lead.php
ddev exec php -l tests/lead-modal.php
```

Expected: every command exits with code 0 and reports no syntax errors.

- [x] **Step 7: Verify the built/live asset contract**

Run:

```bash
ddev exec php tests/lead-modal.php
curl -ksS https://logika.ddev.site/ | rg 'class="modal"|modal-image.webp|data-logika-phone-input|data-logika-age-select|data-logika-lead-form'
```

Expected: the served homepage contains the new modal, pulled image, phone hook, age hook and lead-form hook; it does not contain `data-logika-lead-modal`.

- [x] **Step 8: Browser smoke test**

On `https://logika.ddev.site/` and one inner page, verify:

1. Header/footer and body CTA open the white/yellow lesson modal.
2. Close button, backdrop click and Escape close it and restore scroll/focus.
3. Phone field opens the existing country picker and changing country updates the dial code.
4. Age dropdown has 7–17 values and writes the selected value to `child_age`.
5. Empty/invalid fields show existing validation; valid test data reaches the existing success state.

- [x] **Step 9: Mark project checklist complete**

Change the three new `docs/guidelines/plan.md` items to `[x]` only after the focused test and live smoke checks pass.

---

## Self-review checklist

- [x] No task introduces a second lead endpoint or country/age implementation.
- [x] Camp-shift modal remains out of scope and unmodified.
- [x] All code paths retain sanitization, validation, token and idempotency behavior.
- [x] No unrelated dirty files are staged.
- [x] Completion claims are made only after fresh command and browser evidence.
