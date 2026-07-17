# Database: MySQL schema for Logika School

Date: 2026-07-10
Project: Logika School
Stack: WordPress + MySQL + ACF Pro + `logika-core` + `logika-leads`

## 1. Core principle

The database is built around standard WordPress model, but not all data is stored the same way.

Rule:

> Content entities are stored in WordPress CPT/taxonomies/meta. Operational data with frequent status updates, retries and logs should be stored in dedicated MySQL tables.

This preserves compatibility with WordPress admin, SEO plugins, ACF and standard templates, while preventing `wp_postmeta` from becoming storage for queues, logs and CRM statuses.

## 2. MySQL baseline

Recommended baseline:

- MySQL 8.0+;
- charset: `utf8mb4`;
- collation: `utf8mb4_unicode_ci` or host-recommended MySQL collation for WordPress;
- storage engine: InnoDB;
- strict SQL mode enabled where possible;
- all tables use WordPress prefix: `$wpdb->prefix`.

Examples below use `wp_` prefix. In production code, do not hardcode `wp_`; build table names from `$wpdb->prefix`.

## 3. Data separation

| Data type | Storage | Why |
|---|---|---|
| Pages/posts/media | WordPress core tables | Native CMS behavior |
| Cities, courses, camps, reviews, FAQ | CPTs in `wp_posts` + `wp_postmeta` + taxonomies | Editor workflows, templates, SEO, ACF |
| ACF fields | `wp_postmeta`, `wp_options`, ACF Local JSON in Git | ACF standard model |
| Global settings | `wp_options` via ACF Options | Phones, socials, fallbacks, schema |
| Leads | `wp_logika_leads` | Reliability, statuses, search, retry, privacy |
| Lead events | `wp_logika_lead_events` | Audit trail without lead row bloat |
| CRM attempts | `wp_logika_lead_attempts` | Send history and retry records |
| Imports | `wp_logika_import_runs`, `wp_logika_import_items` | Idempotency, dry-run, diagnostics |
| Rate limit / anti-spam | transients or `wp_logika_rate_limits` | Depends on load and hosting |

## 4. WordPress core tables

Core tables in use:

| Table | Purpose in project |
|---|---|
| `wp_posts` | pages, posts, media, CPTs: city, branch, course, camp, review, faq_item |
| `wp_postmeta` | ACF fields and technical meta for CPTs |
| `wp_terms` | dictionaries: regions, directions, age groups, formats |
| `wp_term_taxonomy` | WordPress taxonomy metadata |
| `wp_term_relationships` | CPT to term relations |
| `wp_options` | global settings, ACF Options, plugin schema version |
| `wp_users` | administrators, editors, managers |
| `wp_usermeta` | roles, capabilities, user settings |

Direct SQL queries to core tables are allowed only when WordPress API is insufficient and there is a measurable reason. Default is to use `WP_Query`, taxonomy API, metadata API and ACF API.

## 5. CPTs and taxonomies

### 5.1. CPTs

| CPT | Purpose | Main links |
|---|---|---|
| `city` | city landing page | branches, courses, camps, reviews, faq |
| `branch` | branch/address | belongs to city |
| `course` | programming or English course | cities, faq, reviews |
| `camp` | IT camp | city, course, dates |
| `review` | user testimonial | city, course |
| `faq_item` | reusable FAQ entries | city, course, page context |
| `media_item` or standard `post` | articles/media | related city/course |

### 5.2. Taxonomies

| Taxonomy | For CPT | Examples |
|---|---|---|
| `region` | `city`, `branch` | Kyiv region, Lviv region |
| `course_direction` | `course`, `camp` | programming, english |
| `age_group` | `course`, `camp` | 7-8, 9-11, 12-14, 15-17 |
| `learning_format` | `course`, `camp`, `branch` | online, offline, mixed |
| `faq_context` | `faq_item` | city, course, global |

## 6. ACF/meta naming

ACF field names should be stable and business meaningful.

Examples:

```text
city_external_id
city_region
city_lat
city_lng
city_index_status
city_intro
city_seo_title
city_seo_description
city_schema_local_business

branch_city_id
branch_external_id
branch_address
branch_address_hash
branch_lat
branch_lng
branch_is_active

course_external_id
course_age_min
course_age_max
course_direction
course_format
course_cta_label
```

Do not:

- use generic names like `text_1`, `block_2`, `field_new`;
- rename fields without migration;
- store lead queue status in `wp_postmeta`.

## 7. Entity relationships

### 7.1. City -> Branch

Primary approach:

- `branch` is stored as CPT;
- city relation stored in meta `branch_city_id`;
- optionally use `region` taxonomy additionally.

Branch-by-city queries should use indexed meta key. If high load appears, add a lookup table later; WordPress meta queries are enough initially.

### 7.2. City <-> Course

Options:

1. ACF relationship field on `city` or `course`;
2. taxonomy/grouping by term when courses are shared across many cities;
3. dedicated lookup table later if price, schedule or per city-course availability appears.

For MVP:

- use ACF relationship or post object;
- do not create `city_courses` custom table early.

If relation needs its own attributes, such as price, schedule, local CTA or availability, create dedicated table:

```text
wp_logika_city_courses
```

### 7.3. Course -> FAQ / City -> FAQ

For reusable FAQ:

- `faq_item` as CPT;
- links via ACF relationship or taxonomy `faq_context`;
- for schema output only FAQs actually visible on page.

### 7.4. Reviews

`review` as CPT:

- city/course links via meta or ACF relationship;
- publishing approval is separate boolean field;
- unpublished reviews are never shown publicly.

## 8. Custom tables for leads

Leads should not be stored only as CPT when reliable CRM retry, status history, fast filtering and audit trail are needed.

### 8.1. `wp_logika_leads`

Lead table:

```sql
CREATE TABLE wp_logika_leads (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  lead_id VARCHAR(64) NOT NULL,
  idempotency_key VARCHAR(191) NOT NULL,
  form_id VARCHAR(80) NOT NULL,
  status VARCHAR(32) NOT NULL DEFAULT 'pending',
  crm_status VARCHAR(32) NOT NULL DEFAULT 'not_sent',
  crm_lead_id VARCHAR(191) NULL,
  crm_attempts INT UNSIGNED NOT NULL DEFAULT 0,
  name VARCHAR(160) NOT NULL,
  phone VARCHAR(40) NOT NULL,
  phone_hash CHAR(64) NOT NULL,
  child_age TINYINT UNSIGNED NULL,
  city_id BIGINT UNSIGNED NULL,
  city_slug VARCHAR(200) NULL,
  course_id BIGINT UNSIGNED NULL,
  course_slug VARCHAR(200) NULL,
  source_url TEXT NULL,
  referrer TEXT NULL,
  utm_source VARCHAR(191) NULL,
  utm_medium VARCHAR(191) NULL,
  utm_campaign VARCHAR(191) NULL,
  utm_term VARCHAR(191) NULL,
  utm_content VARCHAR(191) NULL,
  consent_accepted TINYINT(1) NOT NULL DEFAULT 0,
  consent_text_version VARCHAR(80) NULL,
  payload_json LONGTEXT NULL,
  last_error_code VARCHAR(80) NULL,
  last_error_message TEXT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  sent_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY lead_id (lead_id),
  UNIQUE KEY idempotency_key (idempotency_key),
  KEY status_created (status, created_at),
  KEY crm_status_created (crm_status, created_at),
  KEY phone_hash_created (phone_hash, created_at),
  KEY city_created (city_id, created_at),
  KEY course_created (course_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

Rules:

- `lead_id` is public ID for API and admin;
- `idempotency_key` protects against duplicates;
- `phone_hash` enables duplicate checks without plain-phone index;
- `payload_json` stores normalized payload and must not contain secrets;
- do not store full CRM response if it may contain extra personal data or tokens.

`status` values:

| Status | Meaning |
|---|---|
| `pending` | lead saved, sending not completed |
| `sent` | lead sent to CRM |
| `failed` | sending finished with error |
| `retrying` | lead waiting for retry |
| `archived` | lead hidden from active work |

`crm_status` values:

| Status | Meaning |
|---|---|
| `not_sent` | not sent yet |
| `accepted` | CRM accepted |
| `rejected` | CRM rejected payload |
| `timeout` | timeout |
| `error` | other error |

### 8.2. `wp_logika_lead_attempts`

CRM send attempt history.

```sql
CREATE TABLE wp_logika_lead_attempts (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  lead_id VARCHAR(64) NOT NULL,
  attempt_no INT UNSIGNED NOT NULL,
  status VARCHAR(32) NOT NULL,
  http_status SMALLINT UNSIGNED NULL,
  error_code VARCHAR(80) NULL,
  error_message TEXT NULL,
  request_id VARCHAR(80) NULL,
  started_at DATETIME NOT NULL,
  finished_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY lead_attempt (lead_id, attempt_no),
  KEY lead_id (lead_id),
  KEY status_started (status, started_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

Do not store CRM tokens, auth headers or full request/response with personal data in this table.

### 8.3. `wp_logika_lead_events`

Audit trail for leads.

```sql
CREATE TABLE wp_logika_lead_events (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  lead_id VARCHAR(64) NOT NULL,
  event_type VARCHAR(80) NOT NULL,
  actor_type VARCHAR(32) NOT NULL,
  actor_id BIGINT UNSIGNED NULL,
  message TEXT NULL,
  meta_json LONGTEXT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY lead_created (lead_id, created_at),
  KEY event_created (event_type, created_at),
  KEY actor_created (actor_type, actor_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

Example `event_type` values:

- `lead.created`;
- `crm.send.started`;
- `crm.send.accepted`;
- `crm.send.failed`;
- `crm.retry.manual`;
- `lead.archived`;
- `admin.note.added`.

## 9. Import tables

Imports must be idempotent and diagnosable.

### 9.1. `wp_logika_import_runs`

```sql
CREATE TABLE wp_logika_import_runs (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  import_id VARCHAR(64) NOT NULL,
  import_type VARCHAR(80) NOT NULL,
  source_name VARCHAR(191) NULL,
  source_hash CHAR(64) NULL,
  status VARCHAR(32) NOT NULL DEFAULT 'pending',
  mode VARCHAR(32) NOT NULL DEFAULT 'apply',
  total_items INT UNSIGNED NOT NULL DEFAULT 0,
  created_count INT UNSIGNED NOT NULL DEFAULT 0,
  updated_count INT UNSIGNED NOT NULL DEFAULT 0,
  skipped_count INT UNSIGNED NOT NULL DEFAULT 0,
  failed_count INT UNSIGNED NOT NULL DEFAULT 0,
  started_at DATETIME NOT NULL,
  finished_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY import_id (import_id),
  KEY type_started (import_type, started_at),
  KEY status_started (status, started_at),
  KEY source_hash (source_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

Modes:

- `dry_run`;
- `apply`.

### 9.2. `wp_logika_import_items`

```sql
CREATE TABLE wp_logika_import_items (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  import_id VARCHAR(64) NOT NULL,
  entity_type VARCHAR(80) NOT NULL,
  entity_key VARCHAR(191) NOT NULL,
  wp_post_id BIGINT UNSIGNED NULL,
  action VARCHAR(32) NOT NULL,
  status VARCHAR(32) NOT NULL,
  message TEXT NULL,
  row_hash CHAR(64) NULL,
  row_number INT UNSIGNED NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY import_entity (import_id, entity_type, entity_key),
  KEY entity_key (entity_type, entity_key),
  KEY status_created (status, created_at),
  KEY wp_post_id (wp_post_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

`action` values:

- `created`;
- `updated`;
- `skipped`;
- `failed`.

## 10. Optional lookup tables

Do not create lookup tables too early. But if query pressure appears, dedicated tables are valid.

### 10.1. `wp_logika_city_courses`

Needed only if city-course relation needs dedicated attributes.

```sql
CREATE TABLE wp_logika_city_courses (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  city_id BIGINT UNSIGNED NOT NULL,
  course_id BIGINT UNSIGNED NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  local_cta VARCHAR(191) NULL,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY city_course (city_id, course_id),
  KEY course_active (course_id, is_active),
  KEY city_active_sort (city_id, is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

Until price, schedules or city-course availability is added separately, keep using ACF relationship.

## 11. Indexes

Add indexes for actual query patterns.

Mandatory indexes:

- unique `lead_id`;
- unique `idempotency_key`;
- `status + created_at` for lead listing;
- `crm_status + created_at` for retry queue;
- `phone_hash + created_at` for duplicate search;
- `import_type + started_at` for import history;
- `entity_type + entity_key` for imported entities;
- `city_id + created_at` and `course_id + created_at` for lead filters.

Do not index:

- full `TEXT` payload;
- raw URLs without need;
- every ACF meta field;
- personal data unless necessary.

For phone search, use `phone_hash`, not plain phone index.

## 12. Migrations

Migrations are owned by plugin that owns the table:

- `logika-core` owns import tables and optional lookup tables;
- `logika-leads` owns lead tables.

Schema versions are stored in `wp_options`:

```text
logika_core_db_version
logika_leads_db_version
```

Rules:

- migrations are idempotent;
- repeat execution does not break state;
- create/update tables with `dbDelta` when compatible;
- break complex changes into steps;
- require deprecation period before column deletion;
- destructive migrations require backup and release note;
- migrations should not depend on current theme;
- run migrations on plugin activation and when old schema version is detected.

Example order:

```text
read db version
  ↓
if tables missing -> create with dbDelta
  ↓
if old version -> apply incremental migrations
  ↓
update option with new schema version
  ↓
write migration log event
```

## 13. Working with `dbDelta`

For WordPress compatibility:

- SQL must match `dbDelta` requirements;
- each field definition should be on its own line;
- `PRIMARY KEY` may require double spaces depending on tooling;
- do not rely on `dbDelta` for any destructive change;
- verify actual table structure after migration.

For complex changes:

- add new column;
- backfill in batches;
- switch code to new column;
- keep old column for at least one release;
- drop old column in later migration.

## 14. Transactions and concurrency

Critical operations:

- lead creation;
- CRM attempt insertion;
- retry failed leads;
- import row with idempotency key.

Rules:

- use unique constraints as final duplicate guard;
- on duplicate key treat as existing record, not a fatal error;
- update `crm_attempts` atomically on retry;
- do not hold long transaction during external CRM HTTP request;
- save lead and attempt before external request, update status after response.

Recommended lead flow:

```text
validate request
  ↓
insert lead with idempotency_key
  ↓
insert crm attempt started
  ↓
send CRM request outside long DB lock
  ↓
update attempt result
  ↓
update lead status
```

## 15. Retention and privacy

Leads contain personal data.

Rules:

- define retention period for leads in WordPress;
- old leads are archived, deleted or anonymized;
- keep minimal auditability while reducing personal data surface.

Minimal anonymization example:

- clear `name`;
- clear `phone`;
- keep `phone_hash` if needed for anti-duplicate analytics;
- keep aggregate city/course/form/source fields;
- clear comment and raw payload.

## 16. Backup and restore

Before production release:

- backup is enabled;
- restore tested on staging;
- migrations verified on production-like copy;
- rollback plan prepared for plugin schema version;
- CRM retry queue after restore must not send mass duplicates.

After restore:

- verify `logika_leads` statuses;
- verify which attempts were already sent to CRM;
- do not run automatic retry without idempotency review.

## 17. Schema testing

Cover:

- tables created on plugin activation;
- repeat activation does not break schema;
- unique `idempotency_key` prevents duplicates;
- importing city twice does not duplicate;
- retry increments `crm_attempts`;
- failed CRM attempt stores error code;
- admin query by `status + created_at` works;
- noindex city excluded from sitemap;
- old leads can be anonymized.

## 18. What not to do at the start

Do not add without separate design:

- separate table for each ACF entity;
- full replacement of WordPress posts/meta model;
- custom ORM over WordPress layer;
- complex event-sourcing architecture;
- sharding;
- full-text search on all fields;
- storing CRM secrets in DB without encryption;
- public access to lead tables;
- required foreign keys to WordPress core tables.

Foreign keys may be used in custom tables, but be careful with WordPress core tables because plugins/imports/migrations are often better handled with looser coupling.
