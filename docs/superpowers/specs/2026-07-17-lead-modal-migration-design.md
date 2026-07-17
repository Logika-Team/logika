# Lead Modal Migration Design

> Status: proposed

## Goal

Replace the current purple WordPress lesson lead modal with the ready-made white/yellow modal from the pulled `main` branch, while preserving the existing WordPress lead submission pipeline and using the same country-code and child-age controls as the homepage hero form.

## Scope

- Replace only the lesson/application modal shown from existing site CTAs.
- Keep the camp-shift selection modal unchanged.
- Keep the current WordPress REST endpoint, form token, sanitization, validation, idempotency and CRM flow unchanged.
- Preserve all existing modal triggers, including links routed to `#lead-form`.
- Use Ukrainian public text and the pulled modal image/icon assets.

## Design

The WordPress component `template-parts/components/lead-modal.php` will use the pulled modal structure and visual styles as its source. The form will remain a WordPress lead form with `data-logika-lead-form`, `form_id`, consent, idempotency and context fields. The shared `leads.js` behavior will continue to initialize `intl-tel-input` from `data-logika-phone-input` and the existing accessible age select from `Logika_Theme_Lead_Form::render_age_select()`.

The form template will support a modal presentation variant so field markup and hidden lead contract remain shared with the hero form. The modal will receive the same `city_id`/`course_id` context contract where a trigger supplies it; absent context remains `0`.

The existing WordPress modal controller in `assets/js/main.js` will continue to own open/close, focus restoration, Escape/backdrop close and scroll locking. It will target the new modal component and will not import the static `source/js/modals.js`, because that script manages the separate static markup and the WordPress site already has a working controller.

## Files and responsibilities

- Modify `wordpress/wp-content/themes/logika-theme/template-parts/components/lead-modal.php`: replace purple wrapper with the pulled white/yellow modal structure.
- Modify `wordpress/wp-content/themes/logika-theme/template-parts/forms/lead.php`: add the modal presentation variant without duplicating lead fields or submission contract.
- Modify `wordpress/wp-content/themes/logika-theme/assets/js/main.js`: keep all current trigger paths opening the shared modal and pass optional course context safely.
- Modify `wordpress/wp-content/themes/logika-theme/assets/js/leads.js` only if the modal variant needs a selector-independent compatibility fix; reuse existing country, age, validation and submit code.
- Modify `wordpress/wp-content/themes/logika-theme/assets/css/style.css` and source style files only through the existing pulled modal styles/build path.
- Add the pulled modal image and close icon to the active theme assets.
- Add a focused PHP contract test for modal markup, shared lead fields, country input hook and age-select hook.

## Error and accessibility behavior

- Required name, phone and child age fields keep field-level validation and existing Ukrainian error messages.
- Invalid phone values remain rejected by `intl-tel-input` and the REST endpoint.
- Submission remains disabled while the token/lead request is in progress.
- Existing success/error status and reset behavior remain unchanged.
- The modal keeps `role="dialog"`, `aria-modal`, labelled title, keyboard Escape close, backdrop close and focus restoration.
- Privacy-policy link remains next to the submit action.

## Verification

- PHP syntax checks for every changed PHP file.
- Focused PHP contract test for modal markup and lead fields.
- `node --check` for changed JavaScript.
- Asset/build verification that the pulled modal image and styles are served by the WordPress theme.
- Live DDEV smoke check on the homepage and at least one inner page: open each existing lesson CTA, select a country, select child age, submit a test request, and verify the existing success/error status path.

## Explicitly deferred

- No redesign of the camp-shift modal.
- No new lead endpoint, CRM adapter, database schema, or third-party dependency.
- No broad rewrite of the static modal controller.
