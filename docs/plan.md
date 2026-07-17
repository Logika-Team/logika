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

Checkbox meaning:

- `[ ]` not started or not complete;
- `[x]` complete and verified enough for current phase;
- `[~]` avoid using this marker in Markdown checklists; instead add a note under the task.

## 2. Phase 0: Project alignment and inputs

Goal: collect all missing business and technical inputs before heavy implementation.

- [ ] Confirm final domain and hosting strategy.
- [ ] Confirm staging environment requirements.
- [ ] Confirm CRM vendor and integration method.
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
- [ ] CRM integration path is known.
- [ ] MVP language and SEO indexing rules are agreed.

## 3. Phase 1: Source HTML inventory

Goal: understand the provided frontend source and map it to WordPress components.

- [ ] Identify all source HTML pages.
- [ ] Identify canonical examples for homepage, city, course, camp, contacts and FAQ pages.
- [ ] Identify reusable components: header, footer, hero, cards, forms, FAQ, sliders, map, city selector.
- [ ] Identify existing form states: default, validation error, success, loading.
- [ ] Identify existing empty states for lists.
- [ ] Identify JS modules and libraries used by current markup.
- [ ] Identify SCSS structure and global tokens.
- [ ] Identify image, icon and font assets.
- [ ] Create component-to-template mapping.
- [ ] Document frontend gaps or missing states.

Exit criteria:

- [ ] Every major HTML block has a planned WordPress destination.
- [ ] Missing UI states are documented.
- [ ] No city/course content is planned as hardcoded final HTML.

## 4. Phase 2: WordPress skeleton

Goal: create the base WordPress implementation structure.

- [ ] Set up local WordPress environment.
- [ ] Create custom theme `logika-theme`.
- [ ] Create plugin `logika-core`.
- [ ] Decide whether `logika-leads` starts immediately or after MVP base content.
- [ ] Configure ACF Pro.
- [ ] Enable ACF Local JSON under `logika-core`.
- [ ] Configure permalink structure.
- [ ] Add base theme supports.
- [ ] Add menus.
- [ ] Add image sizes.
- [ ] Add asset enqueue structure.
- [ ] Keep `functions.php` thin.

Exit criteria:

- [ ] WordPress loads locally.
- [ ] Theme activates without fatal errors.
- [ ] `logika-core` activates without fatal errors.
- [ ] ACF field groups can sync through Local JSON.

## 5. Phase 3: Data model

Goal: implement the core editable data model.

### CPT and taxonomy setup

- [ ] Register CPT `city`.
- [ ] Register CPT `branch`.
- [ ] Register CPT `course`.
- [ ] Register CPT `camp`.
- [ ] Register CPT `review`.
- [ ] Register CPT `faq_item`.
- [ ] Register taxonomy `region`.
- [ ] Register taxonomy `course_direction`.
- [ ] Register taxonomy `age_group`.
- [ ] Register taxonomy `learning_format`.
- [ ] Register taxonomy `faq_context`.
- [ ] Configure rewrite rules for CPTs.
- [ ] Flush rewrite rules only on plugin activation/deactivation.

### ACF field groups

- [ ] Create city field group.
- [ ] Create branch field group.
- [ ] Create course field group.
- [ ] Create camp field group.
- [ ] Create review field group.
- [ ] Create FAQ field group.
- [ ] Create global options page.
- [ ] Add SEO/GEO/AEO fields.
- [ ] Add schema input fields.
- [ ] Add fallback content fields.
- [ ] Add admin helper labels/instructions.

### Admin usability

- [ ] Add useful admin columns for cities.
- [ ] Add useful admin columns for branches.
- [ ] Add useful admin columns for courses.
- [ ] Add filters for city/region/status where useful.
- [ ] Limit manager role access where needed.

Exit criteria:

- [ ] Manager can create and edit main entities from admin.
- [ ] Fields are stored in ACF Local JSON.
- [ ] Data model supports city, branch, course, camp, review and FAQ relations.

## 6. Phase 4: Database and migrations

Goal: add operational tables for imports and leads where WordPress posts/meta are not enough.

- [ ] Add schema version option for `logika-core`.
- [ ] Add schema version option for `logika-leads`.
- [ ] Create import run table.
- [ ] Create import item table.
- [ ] Create lead table.
- [ ] Create lead attempt table.
- [ ] Create lead event table.
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

- [ ] Port header markup.
- [ ] Port footer markup.
- [ ] Port global assets.
- [ ] Port base typography/styles.
- [ ] Port buttons.
- [ ] Port form styles.
- [ ] Port card components.
- [ ] Port accordion component.
- [ ] Port slider components.
- [ ] Port trust/proof sections.
- [ ] Replace static asset paths with WordPress helpers.
- [ ] Keep existing CSS class names where possible.

Exit criteria:

- [ ] Header and footer render from theme.
- [ ] Assets load through WordPress enqueue.
- [ ] Core visual language matches source markup.

## 9. Phase 7: Homepage

Goal: create editable homepage assembled from theme components and ACF/options data.

- [ ] Implement homepage template.
- [ ] Connect hero fields.
- [ ] Connect primary lead form placement.
- [ ] Connect trust bar.
- [ ] Connect course sections.
- [ ] Connect English section.
- [ ] Connect city selector/map entry point.
- [ ] Connect FAQ block.
- [ ] Connect CTA sections.
- [ ] Add fallback states for missing optional content.

Exit criteria:

- [ ] Homepage loads from WordPress template.
- [ ] Main homepage content is editable from admin.
- [ ] Lead form includes source context.

## 10. Phase 8: City pages

Goal: implement the central dynamic page type.

- [ ] Implement `single-city.php`.
- [ ] Configure `/cities/{slug}/` URL.
- [ ] Render city H1/intro from ACF.
- [ ] Render city branches.
- [ ] Render city map.
- [ ] Render city courses.
- [ ] Render city camps.
- [ ] Render city reviews with fallback.
- [ ] Render city FAQ with fallback.
- [ ] Render city SEO text.
- [ ] Add city lead form with hidden context.
- [ ] Apply `index_status` logic.
- [ ] Add canonical URL logic.
- [ ] Add city schema output.

Exit criteria:

- [ ] Published city page returns 200.
- [ ] City page uses city-specific data.
- [ ] Missing local data does not break layout.
- [ ] City form submits city context.

## 11. Phase 9: Course and camp pages

Goal: implement reusable templates for learning products.

### Courses

- [ ] Implement course archive.
- [ ] Implement `single-course.php`.
- [ ] Render course hero.
- [ ] Render age, format and direction.
- [ ] Render program/outcomes.
- [ ] Render FAQ.
- [ ] Render course CTA form with course context.
- [ ] Add Course schema.

### Camps

- [ ] Implement camp archive.
- [ ] Implement `single-camp.php`.
- [ ] Render dates/season.
- [ ] Render city/format data.
- [ ] Render camp program.
- [ ] Render gallery.
- [ ] Handle expired camp state.
- [ ] Add camp lead form context.

Exit criteria:

- [ ] Course pages render from CPT.
- [ ] Camp pages render from CPT.
- [ ] Forms preserve course/camp context.

## 12. Phase 10: City selector and interactive map

Goal: implement city context and map-driven selection.

- [ ] Implement city selector in header.
- [ ] Store selected city in browser.
- [ ] Resolve city from direct `/cities/{slug}/` URL.
- [ ] Make city URL context higher priority than stored city.
- [ ] Implement interactive region map.
- [ ] Load city list from WordPress data.
- [ ] Show cities by selected region.
- [ ] Update navbar after city selection.
- [ ] Update local blocks where supported.
- [ ] Update Google Maps/address block after city selection.
- [ ] Ensure selected city does not break canonical/cache rules.

Exit criteria:

- [ ] City selector works from navbar.
- [ ] Map selection sets city context.
- [ ] Direct city URL sets city context.
- [ ] Cached pages do not expose wrong canonical city.

## 13. Phase 11: Lead forms and CRM

Goal: implement reliable lead collection and delivery.

- [ ] Define final lead DTO.
- [ ] Implement public lead endpoint.
- [ ] Add nonce or form token validation.
- [ ] Add server-side validation.
- [ ] Add allowlist for form fields.
- [ ] Add rate limiting.
- [ ] Add honeypot or anti-spam mechanism.
- [ ] Save lead locally before CRM send.
- [ ] Implement CRM client adapter.
- [ ] Implement CRM payload mapper.
- [ ] Implement timeout policy.
- [ ] Implement retry policy.
- [ ] Implement idempotency key handling.
- [ ] Implement duplicate lead handling.
- [ ] Implement admin lead list.
- [ ] Implement admin retry action.
- [ ] Implement CRM test action.
- [ ] Add audit events for critical lead actions.

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
- [ ] Implement canonical logic.
- [ ] Implement `noindex` logic from city status.
- [ ] Exclude `noindex` entities from sitemap.
- [ ] Implement `BreadcrumbList` schema.
- [ ] Implement `LocalBusiness` schema where applicable.
- [ ] Implement `Course` schema.
- [ ] Implement `FAQPage` schema from visible FAQ only.
- [ ] Create Tilda URL inventory.
- [ ] Create redirect map.
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
- [ ] Prepare `.env.example` without real secrets.
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

## 20. Agent update protocol

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
