# Implementation Plan: Logika School

Date: 2026-07-10  
Project: Logika School  
Stack: WordPress + ACF Pro + custom theme + `logika-core` + `logika-leads`

## 1. How to use this plan

This file is the implementation checklist for the project.

Rules for agents and developers:

- mark a checkbox only when the task is actually completed;
- do not mark tasks based on intention, partial work, or unverified assumptions;
- if a task is blocked, leave it unchecked and add a short note under the relevant phase;
- keep tasks small enough to be reviewed;
- keep implementation aligned with `docs/architecture.md`, `docs/database.md`, `docs/api.md`, `docs/security.md`, `docs/testing.md` and `rules/rules.md`;
- use the repo-local `.agents/skills/acf-pro` skill for future ACF Pro field model, Local JSON, options page, and template output work;
- update this plan when scope changes.

### 2026-07-16: ACF-контент маркетингових сторінок

- [x] Перенести унікальні тексти сторінок About, IT Courses, English Courses, FAQ і Media Center до наявних ACF Local JSON груп.
- [x] Зберегти тексти CPT, спільних форм і карти у їхніх поточних джерелах даних.
- [x] Перевірити ACF dry-run sync, ідемпотентне початкове наповнення та рендеринг сторінок.

### 2026-07-16: Автори та перегляди статей

- [x] Додати окремий непублічний CPT автора статей, ACF-фото та вибір автора у записі.
- [x] Вивести Instagram, Telegram і YouTube з глобальних ACF-посилань у картці статті.
- [x] Додати захищений від повторного браузерного обліку REST-лічильник переглядів і перевірити його в DDEV.

### 2026-07-16: Lead CTA modal

- [x] Render one accessible lead-form modal through the shared footer hook.
- [x] Open it from all same-site `#lead-form` CTAs and support close button, backdrop click and Escape.
- [x] Preserve the selected English course as a hidden `course_id` in the modal lead submission.
- [x] Preserve the shared selected city as a hidden `city_id` in the modal lead submission.
- [x] Cover the modal contract with a regression test and verify it in local DDEV.

### 2026-07-15: Lead form error contrast

- [x] Set phone and submit error messages to the existing bright-red validation colour.

### 2026-07-15: Search in media centre

- [x] Extend the existing media feed API with a published-articles search parameter.
- [x] Connect the media-centre search form and cover both API and browser-script contracts.
- [x] Show debounced article suggestions directly below the search field.

### 2026-07-17: Static `main` page migration

- [x] Safely transfer only the confirmed homepage, camp, camps, course and FAQ static sections into the WordPress theme.
- [x] Preserve SourceMarkup/ACF rendering and verify the affected DDEV routes.

### 2026-07-17: Синхронізація карти шкіл з Tilda

- [x] Синхронізувати 98 актуальних міст, їхні назви та регіони з картою Tilda.
- [x] Показувати на карті лише синхронізовані міста; недоступні області зробити сірими.
- [x] Показувати Запорізьку область фіолетовою та інтерактивною, як місто Київ, без окремого маркера.
- [x] Не відкривати Запоріжжя автоматично на карті.

Checkbox meaning:

- `[ ]` not started or not complete;
- `[x]` complete and verified enough for current phase;
- `[~]` avoid using this marker in Markdown checklists; instead add a note under the task.

## 2. Phase 0: Project alignment and inputs

Goal: collect all missing business and technical inputs before heavy implementation.

- [ ] Confirm final domain and hosting strategy.
- [ ] Confirm staging environment requirements.
- [x] Confirm CRM vendor and integration method.
- [ ] Confirm whether CRM has sandbox/test mode.
- [ ] Get access to Tilda.
- [ ] Get access to domain/DNS.
- [ ] Get access to hosting or planned hosting account.
- [ ] Get access to CRM test credentials.
- [ ] Get access to analytics/GTM.
- [ ] Confirm primary language for MVP.
- [ ] Confirm whether IE11 support is still required.
- [ ] Confirm legal/privacy text for lead forms.
- [ ] Confirm retention policy for leads stored in WordPress.
- [ ] Confirm priority cities for launch and early indexing.

Exit criteria:

- [ ] Required accesses are available or explicitly documented as blocked.
- [x] CRM integration path is known.
- [ ] MVP language and SEO indexing rules are agreed.

## 3. Phase 1: Source HTML inventory

Goal: understand the provided frontend source and map it to WordPress components.

- [x] Identify all source HTML pages.
- [ ] Identify canonical examples for homepage, city, course, camp, contacts and FAQ pages.
- [x] Identify reusable components: header, footer, hero, cards, forms, FAQ, sliders, map, city selector.
- [x] Identify existing form states: default, validation error, success, loading.
- [x] Identify existing empty states for lists.
- [x] Identify JS modules and libraries used by current markup.
- [x] Identify SCSS structure and global tokens.
- [x] Identify image, icon and font assets.
- [x] Create component-to-template mapping.
- [x] Document frontend gaps or missing states.

Note: transferred sources are `index`, `about`, `article`, `camp`, `camps`, `en-courses`, `faq`, `it-course`, `it-courses` and `media-center`. Only `index` contains Logika content. The other nine supplied HTML files contain the same English online-pharmacy FAQ placeholder, so canonical layouts for those routes are blocked pending actual HTML from the layout developer. The source also has no canonical contacts page, form success/error views, or complete empty-state set.

Exit criteria:

- [ ] Every major HTML block has a planned WordPress destination.
- [ ] Missing UI states are documented.
- [ ] No city/course content is planned as hardcoded final HTML.

## 4. Phase 2: WordPress skeleton

Goal: create the base WordPress implementation structure.

- [x] Set up local WordPress environment.
- [x] Create custom theme `logika-theme`.
- [x] Create plugin `logika-core`.
- [ ] Decide whether `logika-leads` starts immediately or after MVP base content.
- [x] Configure ACF Pro.
- [x] Enable ACF Local JSON under `logika-core`.
- [x] Configure permalink structure.
- [x] Add base theme supports.
- [x] Add menus.
- [ ] Add image sizes.
- [x] Add asset enqueue structure.
- [x] Keep `functions.php` thin.

Exit criteria:

- [x] WordPress loads locally.
- [x] Theme activates without fatal errors.
- [x] `logika-core` activates without fatal errors.
- [x] ACF field groups can sync through Local JSON.

## 5. Phase 3: Data model

Goal: implement the core editable data model.

### CPT and taxonomy setup

- [x] Register CPT `city`.
- [x] Register CPT `branch`.
- [x] Register CPT `course`.
- [x] Register CPT `camp`.
- [x] Register CPT `review`.
- [x] Register CPT `faq_item`.
- [x] Register taxonomy `region`.
- [x] Register taxonomy `course_direction`.
- [x] Register taxonomy `age_group`.
- [x] Register taxonomy `learning_format`.
- [x] Register taxonomy `faq_context`.
- [x] Configure rewrite rules for CPTs.
- [x] Flush rewrite rules only on plugin activation/deactivation.

### ACF field groups

- [x] Create city field group.
- [x] Create branch field group.
- [x] Create course field group.
- [x] Create camp field group.
- [x] Create review field group.
- [x] Create FAQ field group.
- [x] Create global options page.
- [x] Add SEO/GEO/AEO fields.
- [x] Add schema input fields.
- [x] Add fallback content fields.
- [x] Add admin helper labels/instructions.
- [x] Add dynamic article editorial fields, shared media-centre controls and a fixed post template.

### Admin usability

- [ ] Add useful admin columns for cities.
- [ ] Add useful admin columns for branches.
- [ ] Add useful admin columns for courses.
- [ ] Add filters for city/region/status where useful.
- [ ] Limit manager role access where needed.

Exit criteria:

- [x] Manager can create and edit main entities from admin.
- [x] Fields are stored in ACF Local JSON.
- [x] Data model supports city, branch, course, camp, review and FAQ relations.

## 6. Phase 4: Database and migrations

Goal: add operational tables for imports and leads where WordPress posts/meta are not enough.

- [ ] Add schema version option for `logika-core`.
- [x] Add schema version option for `logika-leads`.
- [ ] Create import run table.
- [ ] Create import item table.
- [x] Create lead table.
- [x] Create lead attempt table.
- [x] Create lead event table.
- [ ] Add indexes for idempotency and admin filters.
- [ ] Add migration runner.
- [ ] Make migrations idempotent.
- [ ] Add safe upgrade path for schema changes.
- [ ] Add retention/anonymization plan for leads.

Exit criteria:

- [ ] Plugin activation creates required tables.
- [ ] Re-running migrations does not break data.
- [ ] Lead and import tables match `docs/database.md`.

## 7. Phase 5: Import and deduplication

Goal: make content import repeatable and safe.

- [ ] Define import format for cities.
- [ ] Define import format for branches.
- [ ] Define import format for courses.
- [ ] Define import format for camps if needed.
- [ ] Implement stable identity keys.
- [ ] Implement city import.
- [ ] Implement branch import.
- [ ] Implement course import.
- [ ] Implement dry-run mode.
- [ ] Implement import logs.
- [ ] Implement skipped/failed row reporting.
- [ ] Implement dedupe for cities by `external_id` or slug.
- [ ] Implement dedupe for branches by `external_id` or `city_slug + address_hash`.
- [ ] Implement dedupe for courses by `external_id` or `course_slug + locale`.
- [ ] Add review queue or report for ambiguous duplicates.

Exit criteria:

- [ ] Same import can run three times without duplicate entities.
- [ ] Import report shows created, updated, skipped and failed counts.
- [ ] Ambiguous records are not guessed silently.

## 8. Phase 6: Theme layout migration

Goal: move static frontend layout into WordPress theme structure.

- [x] Port header markup.
- [x] Port footer markup.
- [x] Port global assets.
- [x] Port base typography/styles.
- [x] Port buttons.
- [x] Port form styles.
- [x] Port card components.
- [x] Port accordion component.
- [x] Port slider components.
- [x] Port trust/proof sections.
- [x] Replace static asset paths with WordPress helpers.
- [x] Keep existing CSS class names where possible.

Note: all ten supplied pages are now executable PHP files under `logika-theme/source-pages/`; `SourceMarkup` includes each page main section and resolves theme asset and WordPress route URLs. The visual baseline is `main/build/*.html`; ACF/CPT data must be inserted only inside that existing structure in phases 7-10.

Exit criteria:

- [x] Header and footer render from theme.
- [x] Assets load through WordPress enqueue.
- [x] Core visual language matches source markup.

## 9. Phase 7: Homepage

Goal: create editable homepage assembled from theme components and ACF/options data.

- [x] Implement homepage template.
- [x] Connect hero fields.
- [x] Connect primary lead form placement.
- [x] Add ACF-configurable child age dropdown to the homepage lead form.
- [ ] Connect trust bar.
- [ ] Connect course sections without changing source markup.
- [ ] Connect English section without changing source markup.
- [ ] Connect city selector/map entry point.
- [ ] Connect FAQ block without changing source markup.
- [ ] Connect CTA sections.
- [ ] Add source-faithful fallback states for course and FAQ blocks.

Exit criteria:

- [ ] Homepage loads from WordPress template.
- [ ] Main homepage content is editable from admin.
- [x] Lead form includes source context.

## 10. Phase 8: City pages

Goal: implement the central dynamic page type.

- [x] Implement `single-city.php`.
- [x] Configure `/cities/{slug}/` URL.
- [x] Render city H1/intro from ACF.
- [x] Render city branches.
- [ ] Render city map.
- [x] Render city courses.
- [x] Render city camps.
- [x] Render city reviews with fallback.
- [x] Render city FAQ with fallback.
- [x] Render city SEO text.
- [x] Add city lead form with hidden context.
- [x] Apply `index_status` logic.
- [x] Add canonical URL logic.
- [x] Add city schema output.

Exit criteria:

- [ ] Published city page returns 200.
- [ ] City page uses city-specific data.
- [ ] Missing local data does not break layout.
- [ ] City form submits city context.

## 11. Phase 9: Course and camp pages

Goal: implement reusable templates for learning products.

### Courses

- [x] Implement course archive.
- [x] Implement `single-course.php`.
- [x] Render course hero.
- [x] Render age, format and direction.
- [x] Render program/outcomes.
- [x] Render FAQ.
- [x] Render course CTA form with course context.
- [x] Add Course schema.

### Camps

- [x] Implement camp archive.
- [x] Implement `single-camp.php`.
- [x] Render dates/season.
- [x] Render city/format data.
- [x] Render camp program.
- [ ] Render gallery.
- [x] Handle expired camp state.
- [x] Add camp lead form context.

Exit criteria:

- [ ] Course pages render from CPT.
- [ ] Camp pages render from CPT.
- [ ] Forms preserve course/camp context.

## 12. Phase 10: City selector and interactive map

Goal: implement city context and map-driven selection.

- [x] Implement city selector in header.
- [x] Store selected city in browser.
- [x] Resolve city from direct `/cities/{slug}/` URL.
- [x] Make city URL context higher priority than stored city.
- [x] Implement interactive region map.
- [x] Load city list from WordPress data.
- [x] Show cities by selected region.
- [x] Load map city branches from WordPress and reuse the hero form in online mode.
- [x] Update navbar after city selection.
- [ ] Update local blocks where supported.
- [x] Update Google Maps/address block after city selection.
- [ ] Ensure selected city does not break canonical/cache rules.

Exit criteria:

- [ ] City selector works from navbar.
- [ ] Map selection sets city context.
- [ ] Direct city URL sets city context.
- [ ] Cached pages do not expose wrong canonical city.

## 13. Phase 11: Lead forms and CRM

Goal: implement reliable lead collection and delivery.

- [x] Define final lead DTO.
- [x] Implement public lead endpoint.
- [x] Add nonce or form token validation.
- [x] Add server-side validation.
- [x] Add allowlist for form fields.
- [x] Add rate limiting.
- [x] Add honeypot or anti-spam mechanism.
- [x] Save lead locally before CRM send.
- [ ] Implement CRM client adapter.
- [x] Add short-lived form-token endpoint for cached public forms.
- [x] Add CF-IPCountry phone-code endpoint with fixed UA fallback and no-cache response.
- [x] Add server-only CRM provider interface, Null provider and retry queue.
- [x] Add WordPress admin lead list with masked PII and protected CSV export.
- [ ] Implement CRM payload mapper.
- [ ] Implement timeout policy.
- [ ] Implement retry policy.
- [x] Implement idempotency key handling.
- [x] Implement duplicate lead handling.
- [x] Implement admin lead list.
- [ ] Implement admin retry action.
- [ ] Implement CRM test action.
- [x] Add audit events for critical lead actions.

### Kommo CRM implementation plan

Decision recorded 2026-07-11: use a private Kommo integration for the school's
single CRM account. The first version authenticates with its server-only
long-lived token; it does not add a browser OAuth flow, Marketplace widget, or
credentials to WordPress options. Kommo OAuth is a separate future task only if
the site must connect multiple independent Kommo accounts.

- [x] Research Kommo authentication, lead creation, duplicate control, pipeline
  lookup, custom fields, rate limits and error responses in the official docs.
- [x] Select `POST /api/v4/leads/complex` as the delivery contract: create one
  lead and one linked contact, pass the local `lead_id` as Kommo `request_id`,
  and persist Kommo's returned lead/contact IDs and `merged` flag.
- [ ] Obtain and record in the deployment secret manager: `LOGIKA_CRM_PROVIDER=kommo`,
  `LOGIKA_KOMMO_SUBDOMAIN`, `LOGIKA_KOMMO_LONG_LIVED_TOKEN`,
  `LOGIKA_KOMMO_PIPELINE_ID`, `LOGIKA_KOMMO_STATUS_ID`, and an optional
  `LOGIKA_KOMMO_RESPONSIBLE_USER_ID`. Never place these values in Git,
  ACF, WordPress options, browser configuration or logs.
- [ ] Obtain the Kommo custom-field mapping before production: contact field
  code/ID for `PHONE`, and only the agreed lead field code/ID for child age,
  city, course, camp, source URL and UTM values. Confirm whether each field is
  required at the selected pipeline stage and whether Duplicate control is
  enabled for the private integration.
- [ ] Extend `wordpress/wp-content/plugins/logika-leads/src/Crm.php` with a
  `KommoProvider`, a Kommo-only payload mapper, and a narrow configuration DTO.
  Keep `CrmProviderInterface` and the public form REST contract unchanged;
  select `kommo` only in `Logika_Leads_Crm_Factory`.
- [ ] Send a single-item JSON array to
  `https://{subdomain}.kommo.com/api/v4/leads/complex` with a Bearer token,
  explicit JSON headers and a five-second timeout. Map only normalized local
  fields: contact name and E.164 phone; lead name, pipeline/status/responsible
  user; and the agreed custom fields. Do not send consent text, raw request
  payloads or unapproved personal data.
- [ ] Upgrade `CrmResult`, `Schema.php` and `Service.php` to retain Kommo lead
  and contact IDs, HTTP status, Kommo `request_id` and the duplicate/merged
  result; write one masked row to `wp_logika_lead_attempts` for every outbound
  call. Bump the schema version and verify the `dbDelta` upgrade on existing
  local leads.
- [ ] Make delivery concurrency-safe: claim a due lead through the existing
  `sync_lock_until` field before sending, never resend `sent` or `rejected`
  leads, and release the lock after the attempt. A timeout becomes an
  `unknown` outcome for reconciliation instead of an immediate blind resend.
- [ ] Classify Kommo responses: `200` with a matching result is sent;
  `400`/`401`/validation errors are permanent and visible safely in admin;
  `429`, transport failures and `5xx` are retryable. Respect Kommo's
  `retry_after` when present and retain the existing bounded backoff queue;
  keep aggregate request rate below Kommo's seven requests per second per IP.
- [ ] Add an admin-only, nonce-protected connection test that reads pipelines
  and custom-field metadata without creating a lead, validates the configured
  pipeline/status/field IDs, and displays a safe result. Add a separately
  confirmed test-lead action only after the business owner agrees on the
  pipeline and cleanup policy.
- [ ] Add isolated tests with mocked `wp_remote_request`: payload mapping,
  token absence, accepted and merged responses, 400/401 rejection, 429 and
  5xx retry scheduling, timeout/unknown reconciliation, lock contention and
  idempotent repeated submission. Add a DDEV smoke that proves a locally saved
  lead remains available when Kommo is disabled.
- [ ] On staging, use a separate Kommo test account or an explicitly approved
  test pipeline, run the connection test and one controlled lead, then verify
  the local lead status, Kommo IDs, attempt/event logs and no PII/secrets in
  PHP logs. Production enablement requires the same check with one agreed test
  lead and a named first-day queue owner.

Official references: [private integration](https://developers.kommo.com/docs/private-integration),
[complex lead creation](https://developers.kommo.com/reference/complex-leads),
[pipelines](https://developers.kommo.com/reference/pipelines-list),
[custom fields](https://developers.kommo.com/reference/custom-field-by-entity),
and [API limits](https://developers.kommo.com/docs/limitations).

Note: the existing generic webhook providers must not be configured for Kommo.
Kommo is an API adapter with account-scoped credentials, not a webhook URL.

Exit criteria:

- [ ] Valid lead is saved locally.
- [ ] CRM failure does not lose lead.
- [ ] Duplicate submit does not duplicate CRM send.
- [ ] Admin can retry failed lead with permission check.

## 14. Phase 12: SEO, schema and redirects

Goal: preserve search value and make structured data data-driven.

- [ ] Configure SEO plugin baseline.
- [ ] Implement SEO title template logic.
- [ ] Implement manual SEO overrides.
- [x] Implement canonical logic.
- [x] Implement `noindex` logic from city status.
- [ ] Exclude `noindex` entities from sitemap.
- [ ] Implement `BreadcrumbList` schema.
- [x] Implement `LocalBusiness` schema where applicable.
- [x] Implement `Course` schema.
- [x] Implement `FAQPage` schema from visible FAQ only.
- [ ] Create Tilda URL inventory.
- [x] Import 13 Tilda blog articles with media-library images and original slugs.
- [ ] Create redirect map.
- [x] Redirect legacy `/map/{slug}` city URLs directly to canonical `/cities/{slug}/` URLs.
- [ ] Apply 301 redirects before launch.
- [ ] Check critical old URLs.

Exit criteria:

- [ ] Indexable city/course pages have valid metadata.
- [ ] Noindex pages are excluded correctly.
- [ ] JSON-LD is valid.
- [ ] Important Tilda URLs redirect to correct WordPress URLs.

## 15. Phase 13: Security hardening

Goal: protect admin, leads, secrets and custom endpoints.

- [ ] Enable separate admin accounts.
- [ ] Enable 2FA for privileged roles.
- [ ] Remove default/weak admin usernames.
- [ ] Limit login attempts.
- [ ] Store secrets outside Git.
- [x] Prepare `.env.example` without real secrets.
- [ ] Verify no CRM tokens reach frontend.
- [ ] Add capability checks to admin endpoints.
- [ ] Add nonce checks to state-changing actions.
- [ ] Escape all template output.
- [ ] Sanitize all request input.
- [ ] Protect uploads and media types.
- [ ] Disable debug display in production.
- [ ] Ensure staging is non-indexable.

Exit criteria:

- [ ] Public endpoints expose no secrets.
- [ ] Admin actions require capability and nonce.
- [ ] Production secrets are not in repository.

## 16. Phase 14: Testing and QA

Goal: verify critical behavior before release.

- [ ] Add unit tests for pure mappers/builders.
- [ ] Add import idempotency tests.
- [ ] Add lead validation tests.
- [ ] Add CRM fake-client tests.
- [ ] Add REST/AJAX endpoint tests.
- [ ] Add template tests for city page.
- [ ] Add SEO/schema tests.
- [ ] Add browser smoke for homepage.
- [ ] Add browser smoke for city page.
- [ ] Add browser smoke for course page.
- [ ] Add browser smoke for lead form success.
- [ ] Add browser smoke for lead form validation error.
- [ ] Run `npm run build`.
- [ ] Run `npm run html`.
- [ ] Run PHP tests when available.
- [ ] Document known QA exceptions.

Exit criteria:

- [ ] Critical tests pass.
- [ ] Known issues are documented with owner and priority.
- [ ] No blocker remains for staging review.

## 17. Phase 15: Staging release

Goal: prepare staging for stakeholder review and production readiness.

- [ ] Deploy WordPress theme to staging.
- [ ] Deploy `logika-core`.
- [ ] Deploy `logika-leads` if included.
- [ ] Sync ACF field groups.
- [ ] Run migrations.
- [ ] Import initial content.
- [ ] Configure staging CRM test mode.
- [ ] Configure staging analytics behavior.
- [ ] Verify forms on staging.
- [ ] Verify city selector on staging.
- [ ] Verify interactive map on staging.
- [ ] Verify redirects on staging.
- [ ] Verify staging noindex.
- [ ] Collect stakeholder feedback.

Exit criteria:

- [ ] Staging is reviewable.
- [ ] Forms work without using production CRM.
- [ ] Stakeholder blockers are resolved or deferred explicitly.

## 18. Phase 16: Production launch

Goal: launch safely with rollback path.

- [ ] Confirm production backup.
- [ ] Verify restore procedure.
- [ ] Configure production secrets.
- [ ] Configure production CRM.
- [ ] Configure production analytics.
- [ ] Apply redirects.
- [ ] Deploy theme/plugins.
- [ ] Run migrations.
- [ ] Import production content.
- [ ] Verify homepage.
- [ ] Verify priority city pages.
- [ ] Verify priority course pages.
- [ ] Verify lead form.
- [ ] Verify CRM receives test lead according to agreed policy.
- [ ] Verify sitemap.
- [ ] Verify robots/indexing state.
- [ ] Monitor logs after launch.

Exit criteria:

- [ ] Site is live on production domain.
- [ ] Critical pages open correctly.
- [ ] Lead flow works end-to-end.
- [ ] No critical SEO/indexing issue remains.

## 19. Phase 17: Post-launch stabilization

Goal: fix launch issues and prepare long-term maintenance.

- [ ] Review first-day error logs.
- [ ] Review CRM send logs.
- [ ] Review lead duplicates and retry queue.
- [ ] Review 404 logs.
- [ ] Review redirect misses.
- [ ] Review Core Web Vitals/PageSpeed.
- [ ] Review admin usability feedback.
- [ ] Document known post-MVP tasks.
- [ ] Prioritize improvements for next iteration.

Exit criteria:

- [ ] No unresolved critical production issue.
- [ ] Retry queue is clean or explained.
- [ ] Post-MVP backlog is documented.

## 20. CTA navigation

- [x] Route navigation CTAs to course catalogues, camps, media centre, or the lead form.
- [x] Verify canonical destinations in the WordPress runtime.
- [x] Place the lead-form anchor at the top of the homepage hero for smooth CTA scrolling.

## 21. Agent update protocol

When an agent completes work:

- update the relevant checkbox from `[ ]` to `[x]`;
- add a short note only if the task has non-obvious scope or limitation;
- do not mark dependent tasks automatically;
- do not mark QA/test tasks unless the check was actually run;
- keep unrelated tasks unchanged.

Example note:

```text
Note: CRM adapter implemented with fake client only; production credentials are still blocked.
```

## 22. Dropdown "Курси"

- [x] Add a focused source-page check for Ukrainian course labels and the minimalist menu contract.
- [x] Replace the two English labels and restyle only the desktop `.sub-menu` card without changing its hover interaction.
- [x] Build the theme assets and run the focused check.

## 23. Відгуки з Tilda

- [x] Додати ACF-поля для мітки картки, порядку показу та відеовідгуку.
- [x] Імпортувати унікальні відгуки Tilda разом із доступними фото до Медіафайлів.
- [x] Замінити статичні картки відгуків даними зі схваленого CPT `review`.

## 24. ACF маркетингових сторінок

- [x] Додати валідні page templates і Local JSON групи для п'яти поточних маркетингових сторінок.
- [x] Додати ідемпотентне початкове наповнення курсів, FAQ і зображень.
- [x] Перевірити рендеринг ACF і dry-run синхронізації JSON.

## 25. Проєкти учнів на головній

- [x] Перенести секцію проєктів з source-розмітки до теми WordPress.
- [x] Додати Local JSON повторювач ACF для редагування карток і безпечний fallback.
- [x] Зібрати assets та перевірити PHP-контракт і браузерний рендер.

## 26. Порядок міст

- [x] Перенести «Інші міста» та «Онлайн» у кінець списків у шапці й формах.
- [x] Нормалізувати ключ «Інші міста», щоб API-варианты не створювали дубль групи.

## 27. Медіа-центр на головній

- [x] Додати ACF-поля для текстів, зображень, посилань і фіксованих промо-карток секції.
- [x] Виводити до трьох обраних статей у порядку ACF; за порожньої добірки показувати останні доступні публікації.
