# Content Model: Logika School CMS

Date: 2026-07-10
Project: Logika School
Stack: WordPress + ACF Pro + `logika-theme` + `logika-core` + `logika-leads`

## 1. Purpose

This document is the source of truth for the WordPress CMS data structure.

It defines:

- public page types;
- Custom Post Types;
- taxonomies;
- ACF Pro field groups;
- field types;
- repeaters;
- flexible content usage;
- relationship fields;
- options pages;
- entity relations.

Rule:

> If content is expected to be edited by a manager, SEO specialist, or content editor, it must live in WordPress content, ACF fields, CPTs, taxonomies, or options. It must not be hardcoded in PHP templates.

## 2. Scope boundaries

Included in this content model:

- marketing pages;
- city landing pages;
- branches and addresses;
- courses;
- camps;
- reviews;
- FAQ;
- news/offers/articles through standard WordPress posts;
- lead forms and form context;
- SEO/GEO/AEO fields;
- schema inputs;
- global site settings.

Not included in this content model:

- student account;
- LMS;
- lesson scheduling slots;
- payments;
- homework, progress, grades;
- teacher/internal education workflows;
- complex CRM lead distribution rules;
- multilingual model for MVP.

## 3. WordPress pages

Pages are used for unique editorial pages and templates that are not repeatable entities.

| Page | Template | Data owner | Notes |
|---|---|---|---|
| Homepage | `front-page.php` | page ACF + global options + CPT queries | main marketing entry |
| Contacts | `page-contacts.php` | page ACF + options + branches/cities | contact information and lead form |
| FAQ page | `page-faq.php` | `faq_item` CPT + page ACF | global FAQ landing |
| Generic content page | default/page template | WordPress page + page ACF | used only for non-system pages |

Rules:

- do not create a separate WordPress page for every city;
- city pages are generated from CPT `city`;
- course pages are generated from CPT `course`;
- camp pages are generated from CPT `camp`;
- content that repeats across pages should be modeled as CPT or option fields.

### Homepage student projects

The front page owns the ordered `home_portfolio_items` ACF repeater. Each row describes one student project: card type (`standard` or `featured`), student name and age, course, topic, description, and the relevant image. A featured row may additionally include a video-review URL and CTA label/URL. The source markup remains the public fallback when the repeater has no rows.

## 4. Custom Post Types

### 4.1. `city`

Purpose:

- represents a local city landing page;
- generates URL `/cities/{city-slug}/`;
- provides city context for local blocks, forms, SEO and schema.

Archive:

- not required for MVP unless a public city index page is explicitly added.

Single template:

```text
single-city.php
```

Required ACF field group:

```text
City Fields
```

Fields:

| Field name | Type | Required | Notes |
|---|---|---:|---|
| `city_external_id` | Text | no | stable key for imports when available |
| `city_region` | Taxonomy or Select | yes | should map to `region` taxonomy |
| `city_lat` | Number | no | map coordinate |
| `city_lng` | Number | no | map coordinate |
| `city_index_status` | Select | yes | `index`, `noindex`, `review` |
| `city_selected_label` | Text | no | label shown in city selector |
| `city_intro` | Textarea/WYSIWYG | no | top intro copy |
| `city_seo_text` | WYSIWYG | no | SEO text visible on page |
| `city_seo_title` | Text | no | manual SEO title override |
| `city_seo_description` | Textarea | no | manual meta description override |
| `city_schema_local_business` | Group | no | LocalBusiness schema inputs |
| `city_fallback_image` | Image | no | optional city-specific fallback image |
| `city_related_courses` | Relationship | no | links to `course` |
| `city_related_camps` | Relationship | no | links to `camp` |
| `city_related_reviews` | Relationship | no | links to `review` |
| `city_related_faq` | Relationship | no | links to `faq_item` |

Recommended field details:

- `city_index_status` options:
  - `index`;
  - `noindex`;
  - `review`.
- `city_schema_local_business` group:
  - `name`;
  - `description`;
  - `telephone`;
  - `address`;
  - `price_range` if approved by business;
  - `same_as` only if city-specific profiles exist.

Relations:

- city has many branches through `branch_city_id`;
- city can relate to many courses;
- city can relate to many camps;
- city can relate to many reviews;
- city can relate to many FAQ items;
- city can relate to standard posts for local news/offers.

Rendering rules:

- H1 can use page title or ACF override if added later;
- if local reviews do not exist, render global reviews fallback;
- if local FAQ does not exist, render global FAQ fallback;
- if local branches do not exist, render online/contact CTA or agreed fallback;
- schema must include only visible page content.

### 4.2. `branch`

Purpose:

- stores school branch/address data;
- connects exact address and Google Maps marker to a city.

Public URL:

- no public single page required for MVP unless approved later.

Required ACF field group:

```text
Branch Fields
```

Fields:

| Field name | Type | Required | Notes |
|---|---|---:|---|
| `branch_external_id` | Text | no | import identity when available |
| `branch_city_id` | Post Object | yes | linked `city` |
| `branch_address` | Textarea | yes | human-readable address |
| `branch_address_hash` | Text | yes | stable dedupe key |
| `branch_lat` | Number | no | map coordinate |
| `branch_lng` | Number | no | map coordinate |
| `branch_phone` | Text | no | branch phone if different from global |
| `branch_schedule` | Repeater | no | schedule rows |
| `branch_google_maps_url` | URL | no | external map link |
| `branch_is_active` | True/False | yes | active branches only appear publicly |

Repeater: `branch_schedule`

| Subfield | Type | Notes |
|---|---|---|
| `day_label` | Text | e.g. Monday-Friday |
| `time_label` | Text | e.g. 10:00-19:00 |

Relations:

- branch belongs to one city;
- city page queries active branches for current city.

Deduplication:

- preferred key: `branch_external_id`;
- fallback key: `city_slug + address_hash`.

### 4.3. `course`

Purpose:

- represents programming or English course;
- powers course cards, course pages, city course lists and form context.

Archive template:

```text
archive-course.php
```

Single template:

```text
single-course.php
```

Required ACF field group:

```text
Course Fields
```

Fields:

| Field name | Type | Required | Notes |
|---|---|---:|---|
| `course_external_id` | Text | no | import identity when available |
| `course_age_min` | Number | no | minimum child age |
| `course_age_max` | Number | no | maximum child age |
| `course_direction` | Taxonomy | yes | `course_direction` taxonomy |
| `course_format` | Taxonomy or Select | yes | `learning_format` |
| `course_short_description` | Textarea | no | card excerpt |
| `course_program` | Repeater or Flexible Content | no | program/outcomes blocks |
| `course_projects` | Repeater | no | project/examples list |
| `course_cta_label` | Text | no | button label override |
| `course_cta_text` | Textarea | no | CTA support copy |
| `course_related_cities` | Relationship | no | cities where course is available |
| `course_related_faq` | Relationship | no | FAQ items |
| `course_related_reviews` | Relationship | no | reviews |
| `course_card_image` | Image | no | card image |
| `course_hero_image` | Image | no | hero image |
| `course_seo_title` | Text | no | manual SEO title override |
| `course_seo_description` | Textarea | no | manual meta description override |
| `course_schema_course` | Group | no | Course schema inputs |

Repeater: `course_program`

| Subfield | Type | Notes |
|---|---|---|
| `title` | Text | program block title |
| `description` | WYSIWYG/Textarea | visible program copy |
| `items` | Repeater | list of lessons/topics/outcomes |

Repeater inside `course_program`: `items`

| Subfield | Type | Notes |
|---|---|---|
| `item_text` | Text | one program item |

Repeater: `course_projects`

| Subfield | Type | Notes |
|---|---|---|
| `project_title` | Text | visible title |
| `project_description` | Textarea | short description |
| `project_image` | Image | optional |

Relations:

- course can be available globally or linked to selected cities;
- course can link FAQ and reviews;
- course appears in lead form context through hidden fields.

Rendering rules:

- course cards and course pages must share the same source data;
- age and format should be visible on public pages;
- Course schema must not include empty or invisible fields.

### 4.4. `camp`

Purpose:

- represents IT camp or seasonal learning product;
- can be linked to one or many cities.

Archive template:

```text
archive-camp.php
```

Single template:

```text
single-camp.php
```

Required ACF field group:

```text
Camp Fields
```

Fields:

| Field name | Type | Required | Notes |
|---|---|---:|---|
| `camp_external_id` | Text | no | import identity when available |
| `camp_start_date` | Date Picker | no | if date is known |
| `camp_end_date` | Date Picker | no | if date is known |
| `camp_season` | Text or Select | no | seasonal label |
| `camp_format` | Taxonomy or Select | yes | `learning_format` |
| `camp_related_cities` | Relationship | no | available cities |
| `camp_related_course` | Post Object | no | related course |
| `camp_program` | Repeater or Flexible Content | no | program blocks |
| `camp_gallery` | Gallery | no | visual assets |
| `camp_cta_label` | Text | no | CTA label override |
| `camp_is_active` | True/False | yes | visibility control |
| `camp_expired_state_text` | Textarea | no | message for expired camp |
| `camp_seo_title` | Text | no | SEO title override |
| `camp_seo_description` | Textarea | no | SEO description override |

Repeater: `camp_program`

| Subfield | Type | Notes |
|---|---|---|
| `title` | Text | block title |
| `description` | WYSIWYG/Textarea | visible copy |
| `items` | Repeater | program items |

Relations:

- camp can belong to many cities;
- camp can link to one related course;
- lead form should preserve camp and city context.

Rendering rules:

- expired camps must have explicit visible state;
- dates and availability come from CMS;
- if no local camp exists for a city, render agreed fallback or hide block.

### 4.5. `review`

Purpose:

- stores testimonials for global, city-specific or course-specific use.

Public URL:

- no public single page required for MVP.

Required ACF field group:

```text
Review Fields
```

Fields:

| Field name | Type | Required | Notes |
|---|---|---:|---|
| `review_external_id` | Text | no | import/source identity when available |
| `review_author_name` | Text | yes | public display name |
| `review_author_role` | Text | no | parent/student/etc. |
| `review_text` | Textarea/WYSIWYG | yes | testimonial text |
| `review_rating` | Number | no | only if ratings are actually used |
| `review_photo` | Image | no | optional |
| `review_related_city` | Post Object | no | linked city |
| `review_related_course` | Post Object | no | linked course |
| `review_is_global` | True/False | yes | available as fallback |
| `review_is_approved` | True/False | yes | must be true for public output |

Relations:

- review can link to one city;
- review can link to one course;
- global reviews are fallback for city/course pages.

Rendering rules:

- only approved reviews appear publicly;
- if local reviews are missing, use global fallback;
- do not render empty review block.

### 4.6. `faq_item`

Purpose:

- stores reusable FAQ items for global pages, city pages, course pages and schema.

Public URL:

- no public single page required for MVP.

Required ACF field group:

```text
FAQ Fields
```

Fields:

| Field name | Type | Required | Notes |
|---|---|---:|---|
| `faq_question` | Text | yes | public question |
| `faq_answer` | WYSIWYG/Textarea | yes | public answer |
| `faq_context` | Taxonomy or Select | no | global, city, course |
| `faq_related_city` | Post Object | no | city-specific FAQ |
| `faq_related_course` | Post Object | no | course-specific FAQ |
| `faq_sort_order` | Number | no | manual ordering |
| `faq_is_active` | True/False | yes | public visibility |

Relations:

- FAQ can be global;
- FAQ can be linked to city;
- FAQ can be linked to course.

Rendering rules:

- FAQ schema may include only visible FAQ items;
- inactive FAQ items are never output publicly;
- page-specific FAQ should be shown before global fallback when relevant.

## 5. Standard WordPress posts

Use standard `post` for news, offers, articles and media content unless a future approved requirement needs a dedicated CPT.

Purpose:

- local news;
- promotions/offers;
- articles;
- media/news center content.

Required ACF field group:

```text
Post Editorial Fields
```

Fields:

| Field name | Type | Required | Notes |
|---|---|---:|---|
| `post_related_city` | Post Object or Relationship | no | local content by city |
| `post_related_course` | Post Object or Relationship | no | course-related article |
| `post_is_offer` | True/False | no | marks promotional content |
| `post_answer_first_summary` | Textarea | no | AEO-friendly summary |
| `post_author_expert` | Text | no | author/expert display |
| `post_seo_title` | Text | no | manual SEO override if SEO plugin does not cover |
| `post_seo_description` | Textarea | no | manual SEO override if SEO plugin does not cover |

### 5.1. Fixed article template

The standard post editor owns the title, slug, date, tags and main article content. The `Post Editorial Fields` group adds the article-specific UI: cover, AEO summary, author role/photo, optional reading-time and view overrides, sidebar promotion, selected courses, manually ordered related posts, CTA and inline FAQ.

- `article_cover_image` is required for a newly prepared article; if an older post has no cover, the theme uses `global_fallback_image` or an image-free layout.
- H2/H3 headings from the native editor form the public table of contents automatically; editors do not maintain duplicate anchors manually.
- `article_popular_courses` and `article_related_posts` store relations only. The public template reads title, URL and image from published source entities and skips drafts/private posts.
- Article FAQ is local to the post; global media-centre search labels and topic links belong to Global Site Settings.
- The theme renders this model. `logika-core` owns the ACF Local JSON schema.

Rules:

- local city news/offers should be linked to `city`;
- if selected city has local posts, show them first;
- if no local posts exist, show global posts;
- fallback must not create duplicates.

## 6. Taxonomies

### 6.1. `region`

Used by:

- `city`;
- `branch`.

Fields:

| Field name | Type | Required | Notes |
|---|---|---:|---|
| `region_map_code` | Text | no | SVG/path/map identifier |
| `region_is_active` | True/False | yes | active in city map |
| `region_sort_order` | Number | no | ordering in UI |

### 6.2. `course_direction`

Used by:

- `course`;
- `camp`.

Known values:

- programming;
- english.

### 6.3. `age_group`

Used by:

- `course`;
- `camp`.

Examples:

- 7-8;
- 9-11;
- 12-14;
- 15-17.

### 6.4. `learning_format`

Used by:

- `course`;
- `camp`;
- `branch`.

Known values:

- online;
- offline;
- mixed.

### 6.5. `faq_context`

Used by:

- `faq_item`.

Known values:

- global;
- city;
- course.

## 7. ACF Options Pages

### 7.1. Global Site Settings

Purpose:

- global business information and fallbacks.

Fields:

| Field name | Type | Required | Notes |
|---|---|---:|---|
| `global_phone` | Text | yes | primary phone |
| `global_email` | Email | no | public email |
| `global_messengers` | Repeater | no | messenger links |
| `global_social_links` | Repeater | no | social profiles |
| `global_default_city` | Post Object | no | optional default city |
| `global_fallback_image` | Image | no | fallback for missing images |
| `global_footer_text` | WYSIWYG/Textarea | no | footer copy |
| `global_privacy_policy_url` | URL | yes | used near forms |

Repeater: `global_messengers`

| Subfield | Type | Notes |
|---|---|---|
| `label` | Text | visible/admin label |
| `url` | URL | messenger link |
| `icon` | Image or Select | depends on implementation |

Repeater: `global_social_links`

| Subfield | Type | Notes |
|---|---|---|
| `label` | Text | network name |
| `url` | URL | profile URL |
| `icon` | Image or Select | depends on implementation |

### 7.2. Global CTA Settings

Purpose:

- reusable CTA labels and fallback CTA copy.

Fields:

| Field name | Type | Required | Notes |
|---|---|---:|---|
| `cta_primary_label` | Text | no | default primary CTA |
| `cta_secondary_label` | Text | no | default secondary CTA |
| `cta_trial_lesson_text` | Textarea | no | default trial lesson copy |
| `cta_consultation_text` | Textarea | no | default consultation copy |

### 7.3. Form Settings

Purpose:

- public lead form configuration that is safe to expose or render.

Fields:

| Field name | Type | Required | Notes |
|---|---|---:|---|
| `form_privacy_text_version` | Text | yes | stored with consent |
| `form_default_success_message` | Textarea | yes | user-facing message |
| `form_default_error_message` | Textarea | yes | safe error message |
| `form_allowed_ids` | Repeater | yes | allowlist of form IDs |

Repeater: `form_allowed_ids`

| Subfield | Type | Notes |
|---|---|---|
| `form_id` | Text | e.g. `hero_trial_lesson` |
| `label` | Text | admin label |

Rules:

- CRM secrets are not ACF Options fields;
- CRM credentials belong to secure environment/server config.

### 7.4. SEO and Schema Defaults

Purpose:

- global SEO/schema values and fallback templates.

Fields:

| Field name | Type | Required | Notes |
|---|---|---:|---|
| `seo_default_title_template` | Text | no | fallback title pattern |
| `seo_default_description_template` | Textarea | no | fallback description pattern |
| `schema_organization_name` | Text | yes | Organization schema |
| `schema_organization_logo` | Image | no | logo |
| `schema_organization_url` | URL | yes | site URL |
| `schema_same_as` | Repeater | no | official profiles |

Repeater: `schema_same_as`

| Subfield | Type | Notes |
|---|---|---|
| `url` | URL | official sameAs URL |

### 7.5. Fallback Content

Purpose:

- content used when local city/course data is missing.

Fields:

| Field name | Type | Required | Notes |
|---|---|---:|---|
| `fallback_reviews` | Relationship | no | global `review` items |
| `fallback_faq` | Relationship | no | global `faq_item` items |
| `fallback_no_branch_text` | Textarea | no | shown when city has no branches |
| `fallback_no_camp_text` | Textarea | no | shown when city has no camps |
| `fallback_no_course_text` | Textarea | no | shown when no local courses |

## 8. Flexible Content usage

Flexible Content is allowed only where editors need controlled section-level composition.

Allowed usage:

- homepage sections;
- generic page sections;
- optional course/camp program blocks if simple repeaters are not enough.

Recommended layout names:

- `hero`;
- `trust_bar`;
- `course_section`;
- `english_section`;
- `city_selector`;
- `faq_section`;
- `cta_section`;
- `text_block`;
- `media_block`.

Rules:

- do not use Flexible Content to rebuild systematic city/course templates manually;
- do not allow arbitrary HTML fields unless explicitly approved;
- section layouts should map to existing design components.

## 9. Relationship map

| Entity | Relation | Target | Storage |
|---|---|---|---|
| City | has many | Branch | `branch_city_id` on branch |
| City | has many | Course | `city_related_courses` or inverse course relationship |
| City | has many | Camp | `city_related_camps` or inverse camp relationship |
| City | has many | Review | `review_related_city` or city relationship |
| City | has many | FAQ | `faq_related_city` or city relationship |
| City | has many | Post | `post_related_city` |
| Course | has many | FAQ | `course_related_faq` or `faq_related_course` |
| Course | has many | Review | `course_related_reviews` or `review_related_course` |
| Course | has many | City | `course_related_cities` |
| Camp | has many | City | `camp_related_cities` |
| Camp | belongs to optional | Course | `camp_related_course` |
| Lead | references optional | City/Course/Camp | hidden fields + validated server-side |

Canonical direction rules:

- branch-to-city relation is stored on branch;
- lead relationships are stored in operational lead tables, not as public CPT content;
- if the same relationship exists on both sides for editor convenience, implementation must choose one canonical source for queries.

## 10. Lead form content model

Lead form UI is rendered by template part and configured through safe fields/options.

Lead form can appear on:

- homepage;
- city page;
- course page;
- camp page;
- contacts page;
- footer/global CTA areas if approved.

Allowed form context fields:

- `form_id`;
- `city_id`;
- `city_slug`;
- `city_name`;
- `course_id`;
- `course_slug`;
- `camp_id`;
- `camp_slug`;
- `source_url`;
- `referrer`;
- UTM fields;
- consent version.

Rules:

- hidden fields are context hints, not trusted source-of-truth;
- server must validate city/course/camp existence;
- CRM status is never accepted from frontend;
- admin notes are never accepted from frontend.

## 11. SEO/GEO/AEO model

SEO fields are stored on each relevant content entity.

Required behavior:

- manual override has priority over generated template;
- generated title/description uses entity data;
- `city_index_status` controls noindex/sitemap behavior;
- schema is generated from visible content only.

Entity-level SEO fields:

- `city_seo_title`;
- `city_seo_description`;
- `course_seo_title`;
- `course_seo_description`;
- `camp_seo_title`;
- `camp_seo_description`;
- optional post SEO fields if SEO plugin fields are not sufficient.

AEO/GEO fields:

- city intro;
- city SEO text;
- answer-first summary for posts;
- FAQ items;
- schema inputs;
- related course/city/article relationships.

## 12. Import identity and dedupe model

Stable identity keys:

| Entity | Preferred key | Fallback key |
|---|---|---|
| City | `city_external_id` | `slug` |
| Branch | `branch_external_id` | `city_slug + branch_address_hash` |
| Course | `course_external_id` | `course_slug + locale` |
| Camp | `camp_external_id` | `slug + start_date + city_ids` |
| Review | `review_external_id` | `source_system + source_id + item_hash` |

Rules:

- importers must update existing entities when identity matches;
- importers must not create duplicates on repeated runs;
- ambiguous records should be skipped or reported, not guessed;
- import reports must include created, updated, skipped and failed counts.

## 13. CMS editing rules

Editors can manage:

- city content;
- branch addresses;
- course content;
- camp content;
- reviews;
- FAQ;
- news/offers/articles;
- SEO fields;
- global settings;
- fallback content.

Editors should not manage:

- CRM credentials;
- database schema;
- plugin activation;
- PHP templates;
- raw SQL;
- production secrets.

## 14. Template ownership

| Template | Primary data source |
|---|---|
| `front-page.php` | homepage page ACF + options + CPT queries |
| `single-city.php` | `city` + related branches/courses/camps/reviews/FAQ/posts |
| `archive-course.php` | `course` CPT |
| `single-course.php` | `course` CPT + related FAQ/reviews/cities |
| `archive-camp.php` | `camp` CPT |
| `single-camp.php` | `camp` CPT + related city/course |
| `page-contacts.php` | page ACF + options + branch/city data |
| `page-faq.php` | `faq_item` CPT + page ACF |
| `archive.php` / post templates | standard posts + related city/course fields |

Rules:

- templates render data;
- templates do not own business content;
- repeated blocks should be template parts;
- all dynamic output must be escaped.

## 15. Change control

Changing this content model requires documentation update before or with implementation.

Examples requiring update:

- adding new CPT;
- adding new taxonomy;
- renaming ACF field;
- changing relationship ownership;
- adding required field;
- changing URL structure;
- changing lead form context contract;
- adding multilingual support.

Do not rename ACF field names without migration plan.

## 16. Marketing pages

About, IT Courses, English Courses, FAQ and Media Center use page-specific ACF Local JSON groups bound to their WordPress page template. Static headings and media are page fields; reusable courses, FAQ, reviews and posts remain their own CPT/Post data and are selected in page relationship fields.
