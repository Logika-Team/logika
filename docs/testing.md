# Testing: Logika School testing strategy

## 1. Core principle

Testing in this project is for protecting business-critical scenarios, not for maximizing coverage percentages:

- cities, branches, courses and their relations;
- imports and idempotent re-imports;
- lead forms and CRM submission;
- SEO/schema and indexable landing pages;
- fallback logic for incomplete data;
- migration from static markup to dynamic WordPress templates.

Main rule:

> Test behavior through public interfaces, not implementation internals.

A good test answers: "What does user, editor, manager or system receive?"  
A bad test breaks on private refactors while user-facing behavior is unchanged.

## 2. Test development approach

Core cycle:

```text
Red -> Green -> Refactor
```

How to apply:

1. Red: write one test for one important behavior and make sure it fails.
2. Green: write minimal code to pass it.
3. Refactor: improve structure without behavior change.
4. Repeat for next behavior.

Do not write large packs for imagined architecture. In this project, work with vertical slices:

```text
city import test -> city import code -> next import behavior
lead form test -> lead storage code -> CRM adapter test -> retry code
city template test -> template data code -> schema test
```

## 3. Project test pyramid

### Unit tests

Validate pure logic without WP request lifecycle, browser, and real CRM.

Cover:

- validators;
- normalizers;
- import row mapping;
- slug generation;
- idempotency key generation;
- SEO title/description builders;
- schema builders;
- fallback selection;
- CRM payload mapping.

Example:

```text
Given city row with same slug
When mapper creates import identity
Then identity is stable across repeated runs
```

### Integration tests

Validate WordPress layer and module interactions.

Cover:

- CPT and taxonomy registration;
- ACF field availability;
- repositories reading/writing WordPress data;
- imports creating/updating records;
- form storage;
- REST/AJAX endpoints;
- permission checks;
- schema output from real post/page data.

Integration tests may use test WordPress DB but should not call real CRM.

### Template/HTML tests

Validate key HTML, not full page snapshots.

Cover:

- H1 presence;
- canonical URL;
- valid JSON-LD script;
- hidden form fields: `city_id`, `course_id`, `source_url`, UTM;
- no empty cards;
- fallback blocks for incomplete data;
- correct ACF-driven CTAs.

Do not create brittle full-page snapshots. Source markup will change during migration and those tests will create noise.

### Browser smoke tests

Run key flows from browser level.

Minimum before release:

- homepage opens;
- city page opens;
- course page opens;
- mobile menu opens and closes;
- city selector works;
- interactive map does not break page;
- form submits in test mode;
- successful submission state is visible;
- network/CRM error does not expose technical details.

### Visual checks

Use selectively when migrating complex blocks from static HTML to WordPress.

Check:

- hero;
- header/footer;
- course cards;
- FAQ block;
- forms;
- mobile layout;
- sliders;
- city map.

Visual checks do not replace functional tests; they catch layout regressions after component porting.

## 4. Tools

### PHP and WordPress

| Tool | Usage |
|---|---|
| PHPUnit | Unit/integration tests for `logika-core`, `logika-leads`, builders, validators, repositories |
| WordPress test suite | CPT, taxonomies, hooks, REST endpoints and test DB behavior |
| Brain Monkey or WP_Mock | Point tests for WordPress hooks/functions without full WordPress |
| PHPStan or Psalm | Static analysis for PHP |
| WordPress Coding Standards | Style, escaping, sanitization and WP best practices |

Recommendation:

- keep pure business logic in classes testable with normal PHPUnit;
- test WP hooks and template integration through integration tests;
- avoid mocking full WordPress where public behavior is testable directly.

### JavaScript and frontend

| Tool | Usage |
|---|---|
| npm scripts | run frontend build and checks |
| Stylelint | SCSS/CSS lint |
| node-w3c-validator | validate built HTML |
| Playwright | Browser smoke tests, forms, mobile menu, city selector |
| Lighthouse/PageSpeed | Performance smoke after major changes |

Current commands in repo:

```bash
npm run build
npm run html
```

Once WordPress layer is in place, add PHP/browser commands, for example:

```bash
composer test
composer analyse
npm run test:e2e
```

Command names can be adapted to actual setup, but should remain consistent across local, CI and staging.

## 5. Priority coverage

### City import

Required scenarios:

- creates new city;
- updates existing city by `external_id` or slug;
- second run does not duplicate city;
- row without required fields does not create record;
- `index_status` persists correctly;
- dry-run writes nothing to DB;
- logs include created/updated/skipped/failed.

Critical test:

```text
Given CSV with city kyiv
When import runs twice
Then only one city with slug kyiv exists
And second run updates existing city
```

### Branch import

Required scenarios:

- branch linked to correct city;
- re-import does not duplicate address;
- branch without city is not created;
- inactive branch does not appear publicly;
- coordinates validate correctly;
- address hash is stable.

### Courses and camps

Required scenarios:

- course creates and updates by stable key;
- age, format and direction saved correctly;
- course links to required cities;
- CTA and FAQ available to template;
- `Course` schema has no empty or false values.

### Forms and leads

Required scenarios:

- valid lead is saved locally;
- invalid phone is rejected and not sent to CRM;
- unknown fields dropped;
- hidden fields persist city/course/source/UTM/referrer;
- CRM receives correct payload;
- CRM error marks lead as `failed` or `retrying`;
- user does not see CRM stack trace;
- resending failed lead does not duplicate record;
- idempotency key is stable.

### AJAX/REST endpoints

Required scenarios:

- endpoint accepts only required HTTP method;
- nonce/form token validation;
- rate limit enforcement;
- payload validated against allowlist;
- successful JSON has no secrets;
- validation returns field-level errors;
- admin actions validate `current_user_can(...)`.

### SEO and schema

Required scenarios:

- SEO title generated by template;
- manual override has priority;
- canonical URL is correct;
- JSON-LD is valid JSON;
- schema is built only from visible content;
- `noindex` city excluded from sitemap.

## 6. External services

Do not call production CRM in automated tests.

Rules:

- CRM client should have adapter/interface;
- tests must use fake/mock CRM client;
- validate payload, timeout, retry policy and idempotency key;
- keep separate manual or staging smoke for real CRM;
- do not use production CRM keys in local/CI.

For email, analytics, maps and CDN:

- integration layer calls should be covered;
- external requests replaced by fake adapter;
- secrets should not be required for unit/integration tests.

## 7. Test data

Test data must be small and clear.

Minimal fixture set:

- `city`: Kyiv, Lviv, city without branches, noindex city;
- `branch`: active branch, inactive branch, branch with invalid coordinates;
- `course`: programming course, English course, course without optional image;
- `faq`: global FAQ, city FAQ, course FAQ;
- `lead`: valid lead, invalid phone lead, duplicate retry lead;
- `crm`: success response, validation error, timeout, 500 error.

Rules:

- do not use production exports as fixtures without anonymization;
- fixtures must be idempotent;
- tests should create and clean needed state automatically;
- fixture names should describe the scenario.

## 8. Writing a good test

Format:

```text
Given: prepare state
When: do action through public interface
Then: verify visible outcome
```

Example:

```text
Given published city Kyiv with one active branch
When visitor opens /cities/kyiv/
Then page contains Kyiv H1
And lead form contains hidden city_id field
And inactive branches are not rendered
```

Rules:

- test name describes business behavior;
- one test = one main scenario;
- verify outcome, not private method call order;
- use mocks only on boundaries: CRM, email, external HTTP;
- avoid order-dependent tests;
- avoid sleep/timeouts when possible;
- fix time-dependent logic with controllable clocks.

## 9. Definition of Done for tests

A change is done when:

- new important behavior has test coverage;
- test covers public behavior;
- test fails without implementation or protects a discovered bug;
- relevant unit/integration/browser smoke tests pass;
- external services are mocked or in test mode;
- repeated operation does not create duplicates;
- no secrets and personal data in test output;
- manual validation is documented if automation is too expensive yet.

If no test is added, PR/notes must explicitly state reason:

```text
Test not added because this change affects only static documentation.
```

or:

```text
Automated test not added now: no WordPress harness yet. Form behavior verified manually on staging; scenario added for later automation.
```

## 10. Minimal pre-release checklist

Before release check:

- `npm run build` passes;
- `npm run html` passes or known issues are documented;
- PHP unit tests pass;
- WordPress integration tests pass;
- browser smoke tests pass on desktop and mobile;
- lead is saved locally;
- CRM test mode receives payload;
- CRM error does not drop lead;
- retry does not duplicate;
- sitemap does not include `noindex` entities;
- JSON-LD is valid;
- staging is non-indexable;
- production secrets not used in CI/local.

## 11. How to grow test suite

Add tests as real behavior appears:

1. Start with critical happy path.
2. Add highest-cost failure mode.
3. Add real edge cases (observed or likely).
4. Add regression tests for previously found bugs.

Do not build a broad abstract test framework in advance. Start with concrete tests for city import, forms, CRM, templates and SEO/schema. Common helpers are created only when repeated across multiple places.
