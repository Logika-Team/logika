# Rules: Idempotency and TDD for Logika School Development

## 1. Core principle

Development must support safe repeat execution:

- rerun city import;
- resynchronize ACF fields;
- rerun migration;
- re-send failed webhook;
- rebuild theme;
- rerun tests;
- redeploy same commit.

If repeat execution causes duplicates, data loss, or unexpected changes, that operation is unsafe.

## 2. What idempotency means in this project

Idempotency means:

> Running the same code with the same inputs multiple times keeps the final system state stable and predictable.

Examples:

| Operation | Incorrect | Correct |
|---|---|---|
| City import | each run creates new `city` | city updates by `external_id` or slug |
| Branch import | duplicates addresses | existing branch updates by `external_id` |
| ACF sync | fields changed only manually in admin | field groups in `acf-json` and applied from Git |
| Lead webhook | repeated send creates mess | resend uses idempotency key |
| SEO/schema | each render produces different markup | schema is deterministic from current fields |
| Seed data | repeated seeding creates entries | seed checks for existing records |
| Deploy | deploy depends on manual actions | deploy is repeatable and deterministic |

## 3. Where idempotency is mandatory

### 3.1. Data imports

All imports need stable keys:

- `external_id` from table/CRM when available;
- slug when no external id exists;
- `city_slug + address_hash` for branches;
- `course_slug + locale` for courses.

Rules:

- always check existing record before creating;
- update existing entry when key matches;
- do not auto-change slug after publish without explicit flag;
- write logs: created, updated, skipped, failed;
- support dry-run;
- validate required fields before DB write.

Minimal importer contract:

```text
input row -> validate -> resolve existing entity -> create/update/skip -> log result
```

### 3.2. ACF Pro and field groups

ACF fields should be versioned.

Rules:

- enable ACF Local JSON;
- store field groups in `wp-content/plugins/logika-core/acf-json/`;
- do not keep critical fields only in production DB;
- change structure via Git;
- delete field only after verifying templates no longer use it;
- do not rename field names without migration.

Not allowed:

- changing production field names without commit;
- random field names like `text_1`, `block_2` for business data;
- mixing city, course and global settings into one group without reason.

### 3.3. CPTs and taxonomies

Register CPTs and taxonomies in `logika-core`, not theme.

Rules:

- CPT registration must be deterministic;
- flush rewrite rules only on plugin activate/deactivate, not every request;
- do not change CPT slug after launch without redirect plan;
- set capabilities explicitly if manager/SEO roles appear.

### 3.4. Forms and leads

Leads should never disappear.

Rules:

- save entry locally first;
- then send to external channel;
- keep status: pending, sent, failed, retrying;
- keep HTTP code/error message for webhook;
- resend should be safe;
- each lead should have `lead_id` or idempotency key;
- hidden fields should store city/course/source URL/UTM/referrer.

### 3.5. Theme templates

Template rendering should be deterministic for same data.

Rules:

- no random sort without explicit seed/order;
- do not rely on missing image, review or branch presence;
- every dynamic block needs empty/fallback state;
- escape all output;
- template loops should have explicit `orderby`;
- avoid raw SQL in small template parts unless necessary.

### 3.6. SEO and schema

SEO should be built from data, not manual template hacks.

Rules:

- title/description has template + override;
- canonical from current canonical URL;
- sitemap excludes `noindex`/`review` cities;
- schema only from visible content;
- JSON-LD should remain stable between renders when data unchanged.
- SEO/GEO/AEO fields must be editor-managed via ACF/CPT/options, not hardcoded in PHP.
- FAQ, answer blocks, local city texts, schema inputs and index status should all be editable content.

### 3.7. Deduplication (global policy)

Deduplication is required wherever duplicate persistence or sending can hurt operations.

General policy:

- deduplication must use stable identifiers first, then explicit fallback rules;
- if identity is unknown, record is put into review queue instead of guessing;
- deduplication must never erase valid variants of the same entity (for example different source leads with same phone but different consent).

### 3.7.1. Entity dedupe keys

Use explicit keys by entity:

- city: `external_id` (preferred), fallback `slug`;
- branch: `external_id` (preferred), fallback `city_slug + address_hash`;
- course: `external_id` (preferred), fallback `course_slug + locale`;
- camp: `external_id` (preferred), fallback combination of `slug + start_date + city_ids`;
- review: `external_id` if available, fallback `source_system + source_id + item_hash`.

Dedup rules:

- keys must be generated in one place (single helper/service).
- keys must be stable across runs and environments.
- matching logic should be case-insensitive for text-based identifiers.
- if key is missing, do not create new records from partial data; add to import report as `skipped`.

### 3.7.2. Lead dedupe

Lead dedupe is mandatory before CRM send and before local persistence.

Priority order:

1. explicit `idempotency_key` if provided by frontend;
2. phone + source_url + course + city + normalized phone-day window (if configured);
3. CRM provider reference if present.

Rules:

- duplicate detection must be query-efficient and index-backed;
- duplicates should be marked with same `lead_id` in admin notes;
- duplicate submission should return `duplicate_received` public status, not create new local lead row;
- duplicate processing must not trigger new CRM attempts;
- duplicate events should still be auditable for operator review.

### 3.7.3. Sending dedupe

Before any outbound action:

- verify there is no in-flight or successfully sent lead with same identity;
- do not send again if `crm_status = accepted`;
- on timeout/retry, send by stored payload only if retry window allows;
- every send attempt is logged with `attempt_no`.

Rules:

- outgoing jobs must be idempotent with same `idempotency_key`;
- if key collision is detected, keep the last stable payload version and do not duplicate attempts beyond policy limits;
- never deduplicate only in frontend JS; server is source of truth.

### 3.7.4. Dedup checks in CI/Tests

Each import and lead pipeline test set should verify dedupe behavior:

- duplicate input rows do not duplicate DB rows;
- duplicates are logged in skip/duplicate states;
- duplicates do not create extra outbound CRM attempts;
- dedupe metrics are visible in logs/reports.

Minimum acceptance for dedupe:

- rerunning the same import 3 times results in one stable entity set;
- same lead payload repeated 3 times results in one accepted local lead + one or zero outbound CRM requests depending on policy.

## 4. TDD approach

Development uses the cycle:

```text
Red -> Green -> Refactor
```

Explanation:

1. Red: write a failing test that captures expected behavior.
2. Green: write minimal code to pass.
3. Refactor: improve structure while keeping behavior.

TDD goal here is not percentage but protecting critical areas:

- imports for 130+ cities;
- city/branch/course relations;
- forms and webhook logic;
- SEO/schema;
- template fallback logic;
- permissions and admin checks;
- HTML component conversion to dynamic template-parts.

## 5. What to test first

### 5.1. City import

Tests:

- new city created if slug not found;
- existing city updates when slug exists;
- no duplicate on rerun;
- invalid rows skipped;
- required fields validate;
- row without city name skipped;
- `index_status` is saved;
- logs include created/updated/skipped/failed.

Critical test:

```text
Given CSV with city kyiv
When import runs twice
Then only one city with slug kyiv exists
And second run updates existing city
```

### 5.2. Branch import

Tests:

- branch linked to correct city;
- repeated import does not duplicate address;
- branch without city is not created;
- coordinates validated;
- inactive branch does not show on city page.

### 5.3. City template

Tests:

- `/cities/kyiv/` returns 200;
- H1 uses ACF override or fallback;
- branch list shown only for current city;
- no local reviews fallback to global;
- form gets hidden `city_id` and `city_name`;
- `review/noindex` city excluded from sitemap.

### 5.4. Course template

Tests:

- course page opens;
- age, format and direction render correctly;
- CTA comes from ACF;
- FAQ for course appears in visible HTML;
- `Course` schema has no empty fields.

### 5.5. Forms

Tests:

- valid lead is saved locally;
- invalid phone is not sent;
- UTM and source URL saved;
- webhook receives required payload;
- CRM error keeps lead in `failed`;
- retry does not create second lead.

### 5.6. SEO/schema

Tests:

- city title built from template;
- manual SEO title has priority;
- canonical is correct;
- JSON-LD is valid JSON;
- noindex city not included in sitemap;
- schema has no data not visible on page.

## 6. Test levels

### Unit tests

Cover pure logic:

- slug generation;
- validation;
- import row mapping;
- schema builders;
- SEO title builders;
- idempotency key generation;
- fallback selection.

### Integration tests

Cover WordPress layer:

- CPT creation;
- ACF field access;
- WP_Query;
- template routing;
- form storage;
- webhook adapter with mock client.

### Snapshot/HTML tests

Use carefully.

Avoid:

- exact full HTML matching everywhere;
- pixel-perfect checks of every block;
- private getters/setters;
- methods without logic;
- vendor library internals (WordPress, ACF, form plugin, Swiper, Fancyapps);
- external plugin unit tests.

If behavior is business critical, test it; if it is only internal helper internals, avoid over-testing.

### Browser smoke

Real browser checks should run as a minimum before release.

Rules:

- lead submit success path;
- city selector updates page context;
- city form includes city/context fields.

## 7. External service rules

Do not call real CRM in automated tests.

Rules:

- CRM client must have adapter/interface;
- tests use fake/mock CRM client;
- validate payload, timeout, retry policy and idempotency key;
- keep manual/staging smoke against test CRM;
- do not use production CRM keys in local/CI.

For email, analytics, maps, CDN:

- automation checks that integration layer is invoked correctly;
- external requests replaced by fake adapter;
- secrets are not needed for unit/integration tests.

## 8. Test data

Test data should be small and understandable.

Minimal fixtures:

- `city`: Kyiv, Lviv, city without branches, noindex city;
- `branch`: active branch, inactive branch, invalid-coordinates branch;
- `course`: programming course, English course, course without optional image;
- `faq`: global FAQ, city FAQ, course FAQ;
- `lead`: valid lead, invalid phone lead, duplicate retry lead;
- `crm`: success, validation error, timeout, 500 error.

Rules:

- do not use production exports as fixtures without anonymization;
- fixtures must be idempotent;
- tests create required state and clean up after run;
- fixture names should describe scenario.

## 9. How to write a good test

Template:

```text
Given: arrange state
When: action through public interface
Then: verify visible result
```

Example:

```text
Given published city Kyiv with one active branch
When visitor opens /cities/kyiv/
Then page contains Kyiv H1
And lead form contains city_id hidden field
And inactive branches are not rendered
```

Rules:

- test name should describe business behavior;
- one test = one main scenario;
- verify outcome, not internal call order;
- use mocks only at boundaries: CRM, email, external HTTP;
- avoid order-dependent tests;
- avoid `sleep/timeouts` when possible;
- lock time if logic is time-sensitive.

## 10. Definition of Done for tests

Change is done if:

- new critical behavior has coverage;
- test checks public behavior;
- test fails without implementation or protects known bug;
- relevant unit/integration/browser smoke pass;
- external services mocked or in test mode;
- repeat operation does not create duplicates;
- no secrets or PII in test output;
- manual checks documented when automation is too expensive.

If no test is added, PR/note should explicitly state reason:

```text
Test not added because change is only static documentation.
```

or:

```text
No automated test now: no WordPress harness. Form behavior verified manually on staging; scenario documented for future automation.
```

## 11. Minimal pre-release checklist

Before release verify:

- `npm run build` passes;
- `npm run html` passes or known issues documented;
- PHP unit tests pass;
- WordPress integration tests pass;
- browser smoke passes on desktop/mobile;
- lead is saved locally;
- CRM test mode gets payload;
- CRM error does not drop leads;
- retry does not duplicate;
- sitemap excludes `noindex` entities;
- JSON-LD validates.

## 12. How to expand test suite

Add tests as real behavior appears:

1. happy path first.
2. most expensive failure mode next.
3. real edge cases already seen or likely.
4. regression tests for found bugs.

Do not build broad abstract test framework first. Start with concrete tests for city import, forms, CRM, templates and SEO/schema. Shared helpers appear only after repeated use.
