# WordPress Architecture: ACF Pro + custom theme

## 1. Core architecture decision

The project is implemented as a manageable WordPress CMS:

- WordPress handles admin, URLs, users, media and base CMS logic.
- ACF Pro handles editable fields, field groups, repeaters, flexible content, option pages and ACF Blocks where needed.
- custom theme `logika-theme` handles UI, page templates, HTML/CSS/JS integration from the source repository and output rendering.
- separate plugin `logika-core` handles Custom Post Types, taxonomies, relations, ACF field registration, helper functions, SEO/schema, imports and business logic.
- all business content should be editable in WordPress admin: texts, headings, CTAs, images, FAQ, SEO/GEO/AEO fields, local blocks, schema inputs and fallback content.

Main principle:

> We do not copy static HTML pages as-is. We transform markup into a WordPress component system: `template-parts`, theme templates and dynamic data from ACF/CPT.

The theme must not contain final business content expected to be edited by client. In HTML/PHP only:

- structural markup;
- CSS class names;
- technical labels only when not marketing copy;
- emergency fallback text for missing states;
- developer-only comments.

All real text and editable blocks should come from ACF, CPT, WordPress pages/posts or option pages.

## 2. Changes required because source HTML is separate

Since markup is delivered from a separate repository with HTML components, workflow is simpler:

1. Do not design UI from scratch.
2. Do not debate visual builders.
3. Do not rewrite pages in WordPress from scratch.
4. Use provided HTML components as frontend source.
5. Decompose into reusable parts.
6. Integrate CSS/JS/assets into custom theme.
7. Replace static texts, cards, lists and mock data with WordPress loops and ACF fields.

Important:

- do not create 130 city HTML pages;
- do not migrate each HTML page to separate page template without data model;
- do not hardcode cities, courses, reviews and FAQ in JS/HTML as final data;
- do not scatter CPT/ACF logic into `functions.php`;
- do not couple data to theme so that editing disappears when theme changes.

## 3. Target layer structure

```text
User
  ↓
WordPress URL routing
  ↓
Custom theme template
  ↓
template-parts from existing HTML
  ↓
data from logika-core: CPT, taxonomies, ACF fields
  ↓
HTML + CSS + JS + SEO/schema + lead form
```

Responsibility split:

| Layer | Purpose | Location |
|---|---|---|
| WordPress core | CMS, users, media, URLs, editor | WordPress |
| `logika-theme` | Visual layer, templates, components, assets | `wp-content/themes/logika-theme` |
| `logika-core` | CPT, taxonomies, ACF fields, relations, helper logic, imports, schema | `wp-content/plugins/logika-core` |
| `logika-leads` | Forms, leads, webhook/CRM, send log | separate plugin or module after MVP |
| ACF Pro | Fields, option pages, repeaters, relationship fields, local JSON in Git | plugin + `acf-json` in Git |
| SEO plugin | Sitemap, metadata UI, breadcrumbs baseline | Rank Math or Yoast |

## 4. Why not Elementor/Bricks as foundation

A builder is not needed as primary stack because project is about repeatable entities:

- 130+ cities;
- branches;
- courses;
- camps;
- reviews;
- FAQ;
- media/promotions;
- lead forms;
- SEO fields and schema.

Builder-first mixes data and visual layer. It may work quickly at start, but gets expensive later: harder imports, weaker SEO control, reused reviews, fallback blocks and stable templates become difficult.

Builder can be used only in narrow cases:

- one-off promo pages;
- temporary campaigns;
- pages not tied to city/course/SEO template structure.

Primary path is custom theme + ACF Pro + CPT.

## 5. Project structure

Reference WordPress structure:

```text
wp-content/
  themes/
    logika-theme/
      assets/
        src/
        dist/
      template-parts/
        layout/
        sections/
        cards/
        forms/
        city/
        course/
      templates/
      front-page.php
      single-city.php
      archive-course.php
      single-course.php
      archive-camp.php
      single-camp.php
      archive.php
      single.php
      page-faq.php
      page-contacts.php
      functions.php
      theme.json
      screenshot.png

  plugins/
    logika-core/
      logika-core.php
      src/
        PostTypes/
        Taxonomies/
        Fields/
        Repositories/
        ViewModels/
        Schema/
        Import/
        Admin/
        Utils/
      acf-json/

    logika-leads/
      logika-leads.php
      src/
        Forms/
        Integrations/
        Storage/
        Admin/
```

Rule:

- keep theme `functions.php` thin: enqueue assets, theme supports, menus, image sizes, theme file loading.
- CPT, ACF, schema, imports and business logic belong to `logika-core`, not theme.

## 6. How we move HTML components into the theme

Use existing HTML repository as frontend component source.

### 6.1. Inventory

Start with mapping:

| HTML component | WordPress destination | Data type |
|---|---|---|
| Header | `template-parts/layout/header.php` or `header.php` | WP menu + city selector |
| Footer | `template-parts/layout/footer.php` or `footer.php` | option fields |
| Hero | `template-parts/sections/hero.php` | ACF fields |
| City selector | `template-parts/city/city-selector.php` | CPT `city` |
| Interactive map | `template-parts/city/city-map.php` | CPT `city` + regions |
| Course card | `template-parts/cards/course-card.php` | CPT `course` |
| Camp card | `template-parts/cards/camp-card.php` | CPT `camp` |
| Review card | `template-parts/cards/review-card.php` | CPT `review` |
| FAQ accordion | `template-parts/sections/faq.php` | CPT `faq_item` or ACF repeater |
| Lead form | `template-parts/forms/lead-form.php` | form plugin/custom handler |
| Media card | `template-parts/cards/media-card.php` | WP posts |

### 6.2. Practical migration

For each component:

1. Take HTML from repository.
2. Keep classes and structure with minimal changes.
3. Remove mock data.
4. Add PHP variables or ViewModel.
5. Escape output: `esc_html`, `esc_url`, `wp_kses_post`, `esc_attr`.
6. Validate repeated component can handle:
   - long headings;
   - no image;
   - empty list;
   - different card counts;
   - mobile viewport.

Example pattern:

```php
// HTML:
// <h3>Python Start</h3>

// Becomes in template-part:
<h3><?= esc_html(get_the_title($course_id)); ?></h3>
```

If component is reused, pass explicit data, for example through `$args` in `get_template_part()`.

## 7. ACF Pro: where and how

ACF Pro is used for editable fields and editorial controls.

Main purpose in this project:

> Allow non-developers to edit all site content and manage SEO/GEO/AEO without breaking design and template structure.

This includes not only text but content affecting search, AI answers and local landing pages:

- H1/H2/H3;
- intro/lead text;
- SEO title and meta description;
- GEO/AEO answer blocks;
- FAQ;
- local city texts;
- benefits;
- CTA;
- button labels;
- course cards;
- reviews;
- trust bars;
- schema fields;
- canonical/noindex/index status;
- images and alt text;
- related courses, cities, camps and articles;
- global phones, social profiles, messengers and organization data.

## 7.1. ACF Local JSON

Must use Local JSON:

```text
wp-content/plugins/logika-core/acf-json/
```

Why:

- fields stored in Git;
- sync fields across local, staging, production;
- reduces risk of manual one-environment-only field changes;
- simplifies review of data model changes.

### 7.2. Option pages

Use ACF Options for global settings:

- phones;
- email;
- messengers;
- social links;
- global CTAs;
- global schema values.

## 8. Data models and business entities

### 8.1. Cities

- CPT `city`.
- city has intro, H1, SEO fields, map data, index status and local relationships.
- city can be `index`, `noindex` or `review`.

### 8.2. Branches

- CPT `branch` and relationship `branch_city_id`.
- branch stores address, coordinates, phone and schedule.
- one city can have many branches.
- branch list in city context and map.

### 8.3. Courses

- CPT `course`.
- course has age range, direction, format, program and FAQ links.
- course availability is city-aware when needed.
- course has schema inputs for `Course`.

### 8.4. Camps

- CPT `camp`.
- camps can be linked to one or many cities.
- camps have dates, season, format, program blocks and photos.

### 8.5. Reviews

- CPT `review`.
- review linked to city and/or course.
- can be moderated/approved.
- shown from local city reviews first.

### 8.6. FAQ

- CPT `faq_item`.
- relation to city/course/global context through relationship or taxonomy.
- used both for UI and schema output.

### 8.7. News and offers

- if posts are enough: use standard posts.
- if specific structure is needed, create CPT `media_item`.
- article type can be global or city-linked.

## 9. URL and routing rules

- canonical city URL format: `/cities/{city-slug}/`;
- city route should be registered in WordPress rewrite rules and cached carefully;
- noindex city pages should be excluded from XML sitemap.

Routing rules:

- canonical city URL always takes priority over cookie city context;
- cookie/localStorage city can adjust non-canonical blocks only;
- admin pages remain under standard WP URLs;
- old Tilda URLs redirect to canonical WordPress URLs via 301.

## 10. City context and caching

City context can be stored in:

- URL (`/cities/kyiv/`);
- session/cookie/localStorage fallback.

Rules:

- URL city has highest priority.
- cookie/localStorage used only when URL does not provide city.
- avoid caching mismatch where selected city appears on non-city pages with wrong canonical.
- if page cached by CDN, provide fallback for city-dependent JS initialization.

## 11. AJAX work in WordPress integration

Public side:

- do not duplicate REST with `admin-ajax.php`.
- use REST by default for new endpoints.
- form submission should call endpoint with hidden city/course/UTM fields.
- on cache miss, call short-lived token endpoint if needed.

### 11.1. What is AJAX for

Forms are used for:

- lead submission;
- map/city selector fetches (optional);
- optional camp search or city filters.

### 11.2. Security rules for AJAX

- each public endpoint validates nonce/token and source.
- sanitize all input;
- allowlist allowed actions.
- return standardized response envelope.
- do not expose server stack errors.

### 11.3. Endpoint registration

Preferred registration:

- `register_rest_route` under `logika/v1`;
- capability checks for admin routes;
- internal rate limiting.

If `admin-ajax.php` is required:

- keep action names explicit;
- use `nopriv` where appropriate.

### 11.4. Frontend contract for AJAX

For form submission:

- keep `POST /wp-json/logika/v1/leads`;
- show disabled state while submitting;
- return field-level messages;
- show success state;
- re-enable form on network error;
- never include CRM endpoint, token or retry business logic in frontend.

Important:

- repeated button click must not send duplicates;
- user should see clear message on network error;
- frontend validation is UX only, final checks on server;
- hidden fields provide context, but server must validate.

### 11.5. Admin AJAX

Admin AJAX/REST actions are needed for leads/settings:

- retry failed lead to CRM;
- mark lead as processed;
- test CRM connection;
- verify payload mapping;
- export limited lead list;
- update integration settings.

Each admin action must:

- check `current_user_can(...)`;
- verify nonce;
- accept only allowlist params;
- write audit log for critical actions;
- not return secrets in JSON;
- not rely only on hiding button in admin UI.

### 11.6. AJAX and caching

Because public pages can be cached, forms must work with page cache/CDN.

Rules:

- lead submission endpoint is never cached.
- page HTML can be cached.
- nonce/form token should not break due to long cache lifetime.
- if WordPress nonce conflicts with cache, use separate short-lived token or lazy refresh endpoint.
- success/error responses are not cacheable by browser/CDN.
- UTM and source URL should be captured by client on submit, not baked into cached HTML.

## 12. SEO and schema

SEO layer is built together with templates, not after.

SEO/GEO/AEO should be editor-managed, not a developer-only task after each content edit.

Editors should be able to:

- edit SEO title/description;
- edit H1 and intro;
- add answer-first blocks;
- edit FAQ;
- select related courses/cities/articles;
- edit local city content;
- edit city index status;
- update schema inputs;
- edit CTA and form copy;
- see required publish fields for index.

For city:

- `title`: template + manual override;
- `description`: template + manual override;
- `H1`: ACF field or generated;
- `canonical`: city URL;
- `breadcrumbs`;
- `LocalBusiness`;
- `Course`;
- `FAQPage`;
- `BreadcrumbList`;
- sitemap inclusion depends on `index_status`.

For course:

- `Course`;
- `FAQPage`;
- `BreadcrumbList`;
- internal links to cities and form.

For articles:

- `Article`;
- author/expert;
- related courses;
- FAQ when available.

Rule:

> Schema should be built only from data that is visible to users on page. Do not mark content that is not present.

## 13. Assets and frontend build

Since markup already exists, do not force stack changes.

First inspect source:

- plain CSS or SCSS;
- Tailwind or not;
- Vite/Webpack/Gulp or plain build;
- vanilla JS or libraries;
- image/font asset structure.

Then:

- keep existing structure when valid;
- enqueue built assets with `wp_enqueue_style()` and `wp_enqueue_script()`;
- convert image paths to WordPress helpers;
- split critical interactive modules separately: menu, city selector, map, accordion, sliders, forms;
- avoid adding heavy libraries without real need.

## 14. Environments and server

For development, acceptable:

- LocalWP;
- DDEV;
- Docker;
- any local WordPress stack.

Nginx is not mandatory at integration phase.

Nginx/Apache required at production host:

- SSL;
- PHP-FPM;
- redirects;
- cache headers;
- gzip/brotli;
- static file protection;
- static asset serving.

This is infrastructure-level and should not lock theme/ACF architecture to a specific web server.

## 15. Implementation order

### Stage 1. Accept HTML repository

Outcome:

- page structure is known;
- component structure is known;
- asset dependencies are known;
- JS/CSS dependencies are known;
- key template list is known.

### Stage 2. Set up WordPress skeleton

Outcome:

- local WordPress;
- Git repository;
- `logika-theme`;
- `logika-core`;
- ACF Pro;
- ACF Local JSON;
- base permalink settings.

### Stage 3. Transfer layout

Outcome:

- header;
- footer;
- global assets;
- base template parts;
- homepage with mock or partially dynamic data.

### Stage 4. Build data model

Outcome:

- CPT `city`, `branch`, `course`, `camp`, `review`, `faq_item`;
- taxonomies;
- ACF field groups;
- option pages;
- admin columns;
- base test data.

### Stage 5. First dynamic template

Start with `single-city.php`, highest project risk.

Outcome:

- `/cities/{slug}/` opens;
- city data comes from ACF;
- branches and map work;
- form gets city context;
- fallback blocks do not break page;
- title/H1/canonical are correct.

### Stage 6. Remaining templates

After city:

- `archive-course.php`;
- `single-course.php`;
- `archive-camp.php`;
- `single-camp.php`;
- `page-contacts.php`;
- `page-faq.php`;
- `archive.php` / media center;
- `single.php`.

### Stage 7. Forms, CRM and SEO

Outcome:

- forms store leads;
- webhook/CRM works or fallback exists;
- sitemap is correct;
- noindex/review cities are excluded;
- schema is valid;
- old Tilda URLs are redirected.

## 16. What to request from HTML developer for source repo

Minimum list:

- repository URL;
- build/start command;
- build list of pages;
- list of missing pages;
- list of known bugs;
- list of used libraries;
- where components are located;
- where source CSS/SCSS is located;
- where source JS is located;
- which pages are canonical examples;
- whether mobile/tablet states exist;
- whether form error/success states exist;
- whether empty states exist for lists;
- whether loading states exist for interactive blocks.

## 17. Final operating rules

1. Do not rewrite HTML without reason.
2. Do not change design during integration except explicit adaptive fixes.
3. Do not store business data in theme.
4. Register CPTs and ACF in `logika-core`.
5. Move HTML components into `template-parts`.
6. Build city, course and camp pages from templates, not manual pages.
7. Use ACF Blocks only where editor really needs section-level controls.
8. Any repeated list must loop through data.
9. Forms must save lead before CRM outbound.
10. SEO URL should dominate over JS city state substitution.

## 18. Final architecture statement

Project architecture:

> Use ready HTML markup as frontend source, migrate it into custom WordPress theme `logika-theme`, and move all repeated data to `logika-core` + ACF Pro. Manager edits cities, branches, courses, camps, reviews, FAQ and global settings in admin. Theme only renders this data via templates and components. City and repetitive sections are generated from templates instead of hand-built pages.
