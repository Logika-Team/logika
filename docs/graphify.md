# Graphify Initialization Report

Date: 2026-07-10
Project: Logika School
Graphify version: `0.9.12`
Graph artifact: `graphify-out/graph.json`
Graph report: `graphify-out/GRAPH_REPORT.md`

## Initialization summary

- Installed/updated official PyPI package `graphifyy` and CLI `graphify`.
- Registered project-scoped Graphify skills in `.codex/skills/graphify` and `.agents/skills/graphify`.
- Registered Graphify hook config in `.codex/hooks.json` through `graphify install --project --platform codex`.
- Built the initial graph from the requested folders and files only; `build/` was not indexed because it is generated output.
- Used local deterministic extraction: Graphify AST for code plus explicit HTML/SCSS/PHP/docs relationship extraction. No LLM/API semantic backend was configured, so token cost is `0`.
- Encoded documentation-derived relationships as Graphify-compatible `INFERRED` edges with `evidence_type=documented`; direct file references are `EXTRACTED`; unimplemented HTML-to-WP-template mappings are `AMBIGUOUS`.

## Indexed folders
- `docs/`
- `source/`
- `rules/`
- `plugins/`
- `README.md`
- `AGENTS.md`

## Indexed documents
- `AGENTS.md`
- `README.md`
- `docs/api.md`
- `docs/content-model.md`
- `docs/database.md`
- `docs/deployment.md`
- `docs/design-system.md`
- `docs/edge-cases.md`
- `docs/features.md`
- `docs/links.md`
- `docs/plan.md`
- `docs/project.md`
- `docs/security.md`
- `docs/tech-stack.md`
- `docs/testing.md`
- `plugins/advanced-custom-fields-pro/README.md`
- `plugins/advanced-custom-fields-pro/readme.txt`
- `rules/architecture.md`
- `rules/rules.md`
- `rules/structure.md`

## Indexed HTML files
- `source/about.html`
- `source/article.html`
- `source/camp.html`
- `source/camps.html`
- `source/en-courses.html`
- `source/faq.html`
- `source/index.html`
- `source/it-course.html`
- `source/it-courses.html`
- `source/media-center.html`
- `source/partials/footer.html`
- `source/partials/head.html`
- `source/partials/header.html`

## Indexed CSS files
Includes source SCSS and vendor CSS files inside the requested scope.
- `plugins/advanced-custom-fields-pro/assets/build/css/acf-dark.min.css`
- `plugins/advanced-custom-fields-pro/assets/build/css/acf-field-group.min.css`
- `plugins/advanced-custom-fields-pro/assets/build/css/acf-global.min.css`
- `plugins/advanced-custom-fields-pro/assets/build/css/acf-input.min.css`
- `plugins/advanced-custom-fields-pro/assets/build/css/pro/acf-pro-field-group.min.css`
- `plugins/advanced-custom-fields-pro/assets/build/css/pro/acf-pro-input.min.css`
- `plugins/advanced-custom-fields-pro/assets/build/css/pro/acf-styles-in-iframe-for-blocks.min.css`
- `plugins/advanced-custom-fields-pro/assets/inc/datepicker/jquery-ui.css`
- `plugins/advanced-custom-fields-pro/assets/inc/datepicker/jquery-ui.min.css`
- `plugins/advanced-custom-fields-pro/assets/inc/select2/3/select2.css`
- `plugins/advanced-custom-fields-pro/assets/inc/select2/4/select2.css`
- `plugins/advanced-custom-fields-pro/assets/inc/select2/4/select2.min.css`
- `plugins/advanced-custom-fields-pro/assets/inc/timepicker/jquery-ui-timepicker-addon.css`
- `plugins/advanced-custom-fields-pro/assets/inc/timepicker/jquery-ui-timepicker-addon.min.css`
- `source/scss/blocks/_footer.scss`
- `source/scss/blocks/_header.scss`
- `source/scss/blocks/_mobile-menu.scss`
- `source/scss/blocks/_modal.scss`
- `source/scss/blocks/sections/advantages-section.scss`
- `source/scss/blocks/sections/banner-section.scss`
- `source/scss/blocks/sections/certificates-section.scss`
- `source/scss/blocks/sections/cta-section.scss`
- `source/scss/blocks/sections/english-section.scss`
- `source/scss/blocks/sections/faq-section.scss`
- `source/scss/blocks/sections/marquee-section.scss`
- `source/scss/blocks/sections/media-section.scss`
- `source/scss/blocks/sections/onboarding-section.scss`
- `source/scss/blocks/sections/partners-section.scss`
- `source/scss/blocks/sections/portfolio-section.scss`
- `source/scss/blocks/sections/services-section.scss`
- `source/scss/blocks/sections/testimonials-section.scss`
- `source/scss/blocks/sections/transformation-section.scss`
- `source/scss/components/_accordion.scss`
- `source/scss/components/_breadcrumbs.scss`
- `source/scss/components/_pagination.scss`
- `source/scss/components/_search.scss`
- `source/scss/components/_star-ratings.scss`
- `source/scss/components/cards/_english-level.scss`
- `source/scss/components/forms/_main-form.scss`
- `source/scss/general/_blocks.scss`
- `source/scss/general/_buttons.scss`
- `source/scss/general/_components.scss`
- `source/scss/general/_custom-icon.scss`
- `source/scss/general/_fonts.scss`
- `source/scss/general/_global.scss`
- `source/scss/general/_loader.scss`
- `source/scss/general/_mixins.scss`
- `source/scss/general/_modals.scss`
- `source/scss/general/_range.scss`
- `source/scss/general/_select.scss`
- `source/scss/general/_swiper.scss`
- `source/scss/general/_typography.scss`
- `source/scss/general/_vars.scss`
- `source/scss/mixins/_breakpoint.scss`
- `source/scss/mixins/_burger.scss`
- `source/scss/mixins/_disable-mob-hover.scss`
- `source/scss/mixins/_mini.scss`
- `source/scss/mixins/_overlay.scss`
- `source/scss/style.scss`

## Indexed JavaScript files
- `plugins/advanced-custom-fields-pro/assets/build/js/acf-escaped-html-notice.min.js`
- `plugins/advanced-custom-fields-pro/assets/build/js/acf-field-group.min.js`
- `plugins/advanced-custom-fields-pro/assets/build/js/acf-input.min.js`
- `plugins/advanced-custom-fields-pro/assets/build/js/acf-internal-post-type.min.js`
- `plugins/advanced-custom-fields-pro/assets/build/js/acf.min.js`
- `plugins/advanced-custom-fields-pro/assets/build/js/pro/acf-datastore.min.js`
- `plugins/advanced-custom-fields-pro/assets/build/js/pro/acf-field-bindings.min.js`
- `plugins/advanced-custom-fields-pro/assets/build/js/pro/acf-pro-blocks.min.js`
- `plugins/advanced-custom-fields-pro/assets/build/js/pro/acf-pro-field-group.min.js`
- `plugins/advanced-custom-fields-pro/assets/build/js/pro/acf-pro-input.min.js`
- `plugins/advanced-custom-fields-pro/assets/build/js/pro/acf-pro-ui-options-page.min.js`
- `plugins/advanced-custom-fields-pro/assets/inc/color-picker-alpha/wp-color-picker-alpha.js`
- `plugins/advanced-custom-fields-pro/assets/inc/color-picker-alpha/wp-color-picker-alpha.min.js`
- `plugins/advanced-custom-fields-pro/assets/inc/select2/3/select2.js`
- `plugins/advanced-custom-fields-pro/assets/inc/select2/3/select2.min.js`
- `plugins/advanced-custom-fields-pro/assets/inc/select2/4/select2.full.js`
- `plugins/advanced-custom-fields-pro/assets/inc/select2/4/select2.full.min.js`
- `plugins/advanced-custom-fields-pro/assets/inc/select2/4/select2.js`
- `plugins/advanced-custom-fields-pro/assets/inc/select2/4/select2.min.js`
- `plugins/advanced-custom-fields-pro/assets/inc/timepicker/jquery-ui-timepicker-addon.js`
- `plugins/advanced-custom-fields-pro/assets/inc/timepicker/jquery-ui-timepicker-addon.min.js`
- `source/js/main.js`
- `source/js/rangeSlider.js`
- `source/js/swiper.js`

## Indexed PHP files
Total PHP files indexed: `336`.
- `plugins/advanced-custom-fields-pro/acf.php`
- `plugins/advanced-custom-fields-pro/assets/build/css/index.php`
- `plugins/advanced-custom-fields-pro/assets/build/css/pro/index.php`
- `plugins/advanced-custom-fields-pro/assets/build/index.php`
- `plugins/advanced-custom-fields-pro/assets/build/js/index.php`
- `plugins/advanced-custom-fields-pro/assets/build/js/pro/index.php`
- `plugins/advanced-custom-fields-pro/assets/images/field-states/index.php`
- `plugins/advanced-custom-fields-pro/assets/images/field-type-icons/index.php`
- `plugins/advanced-custom-fields-pro/assets/images/field-type-previews/index.php`
- `plugins/advanced-custom-fields-pro/assets/images/icons/index.php`
- `plugins/advanced-custom-fields-pro/assets/images/index.php`
- `plugins/advanced-custom-fields-pro/assets/inc/color-picker-alpha/index.php`
- `plugins/advanced-custom-fields-pro/assets/inc/datepicker/images/index.php`
- `plugins/advanced-custom-fields-pro/assets/inc/datepicker/index.php`
- `plugins/advanced-custom-fields-pro/assets/inc/index.php`
- `plugins/advanced-custom-fields-pro/assets/inc/select2/3/index.php`
- `plugins/advanced-custom-fields-pro/assets/inc/select2/4/index.php`
- `plugins/advanced-custom-fields-pro/assets/inc/select2/index.php`
- `plugins/advanced-custom-fields-pro/assets/inc/timepicker/index.php`
- `plugins/advanced-custom-fields-pro/includes/Updater/Updater.php`
- `plugins/advanced-custom-fields-pro/includes/Updater/index.php`
- `plugins/advanced-custom-fields-pro/includes/Updater/init.php`
- `plugins/advanced-custom-fields-pro/includes/acf-bidirectional-functions.php`
- `plugins/advanced-custom-fields-pro/includes/acf-field-functions.php`
- `plugins/advanced-custom-fields-pro/includes/acf-field-group-functions.php`
- `plugins/advanced-custom-fields-pro/includes/acf-form-functions.php`
- `plugins/advanced-custom-fields-pro/includes/acf-helper-functions.php`
- `plugins/advanced-custom-fields-pro/includes/acf-hook-functions.php`
- `plugins/advanced-custom-fields-pro/includes/acf-input-functions.php`
- `plugins/advanced-custom-fields-pro/includes/acf-internal-post-type-functions.php`
- `plugins/advanced-custom-fields-pro/includes/acf-meta-functions.php`
- `plugins/advanced-custom-fields-pro/includes/acf-post-functions.php`
- `plugins/advanced-custom-fields-pro/includes/acf-post-type-functions.php`
- `plugins/advanced-custom-fields-pro/includes/acf-taxonomy-functions.php`
- `plugins/advanced-custom-fields-pro/includes/acf-user-functions.php`
- `plugins/advanced-custom-fields-pro/includes/acf-utility-functions.php`
- `plugins/advanced-custom-fields-pro/includes/acf-value-functions.php`
- `plugins/advanced-custom-fields-pro/includes/acf-wp-functions.php`
- `plugins/advanced-custom-fields-pro/includes/admin/admin-internal-post-type-list.php`
- `plugins/advanced-custom-fields-pro/includes/admin/admin-internal-post-type.php`
- `plugins/advanced-custom-fields-pro/includes/admin/admin-notices.php`
- `plugins/advanced-custom-fields-pro/includes/admin/admin-options-pages-preview.php`
- `plugins/advanced-custom-fields-pro/includes/admin/admin-tools.php`
- `plugins/advanced-custom-fields-pro/includes/admin/admin-upgrade.php`
- `plugins/advanced-custom-fields-pro/includes/admin/admin.php`
- `plugins/advanced-custom-fields-pro/includes/admin/index.php`
- `plugins/advanced-custom-fields-pro/includes/admin/post-types/admin-field-group.php`
- `plugins/advanced-custom-fields-pro/includes/admin/post-types/admin-field-groups.php`
- `plugins/advanced-custom-fields-pro/includes/admin/post-types/admin-post-type.php`
- `plugins/advanced-custom-fields-pro/includes/admin/post-types/admin-post-types.php`
- `plugins/advanced-custom-fields-pro/includes/admin/post-types/admin-taxonomies.php`
- `plugins/advanced-custom-fields-pro/includes/admin/post-types/admin-taxonomy.php`
- `plugins/advanced-custom-fields-pro/includes/admin/post-types/index.php`
- `plugins/advanced-custom-fields-pro/includes/admin/tools/class-acf-admin-tool-export.php`
- `plugins/advanced-custom-fields-pro/includes/admin/tools/class-acf-admin-tool-import.php`
- `plugins/advanced-custom-fields-pro/includes/admin/tools/class-acf-admin-tool.php`
- `plugins/advanced-custom-fields-pro/includes/admin/tools/index.php`
- `plugins/advanced-custom-fields-pro/includes/admin/views/acf-field-group/conditional-logic.php`
- `plugins/advanced-custom-fields-pro/includes/admin/views/acf-field-group/field.php`
- `plugins/advanced-custom-fields-pro/includes/admin/views/acf-field-group/fields.php`
- `plugins/advanced-custom-fields-pro/includes/admin/views/acf-field-group/index.php`
- `plugins/advanced-custom-fields-pro/includes/admin/views/acf-field-group/list-empty.php`
- `plugins/advanced-custom-fields-pro/includes/admin/views/acf-field-group/location-group.php`
- `plugins/advanced-custom-fields-pro/includes/admin/views/acf-field-group/location-rule.php`
- `plugins/advanced-custom-fields-pro/includes/admin/views/acf-field-group/locations.php`
- `plugins/advanced-custom-fields-pro/includes/admin/views/acf-field-group/options.php`
- `plugins/advanced-custom-fields-pro/includes/admin/views/acf-field-group/pro-features.php`
- `plugins/advanced-custom-fields-pro/includes/admin/views/acf-post-type/advanced-settings.php`
- `plugins/advanced-custom-fields-pro/includes/admin/views/acf-post-type/basic-settings.php`
- `plugins/advanced-custom-fields-pro/includes/admin/views/acf-post-type/index.php`
- `plugins/advanced-custom-fields-pro/includes/admin/views/acf-post-type/list-empty.php`
- `plugins/advanced-custom-fields-pro/includes/admin/views/acf-taxonomy/advanced-settings.php`
- `plugins/advanced-custom-fields-pro/includes/admin/views/acf-taxonomy/basic-settings.php`
- `plugins/advanced-custom-fields-pro/includes/admin/views/acf-taxonomy/index.php`
- `plugins/advanced-custom-fields-pro/includes/admin/views/acf-taxonomy/list-empty.php`
- `plugins/advanced-custom-fields-pro/includes/admin/views/browse-fields-modal.php`
- `plugins/advanced-custom-fields-pro/includes/admin/views/escaped-html-notice.php`
- `plugins/advanced-custom-fields-pro/includes/admin/views/global/form-top.php`
- `plugins/advanced-custom-fields-pro/includes/admin/views/global/header.php`
- `plugins/advanced-custom-fields-pro/includes/admin/views/global/index.php`
- ... 256 more PHP files under `plugins/advanced-custom-fields-pro/`.

## Detected components
- `accordion`
- `advantages section`
- `banner section`
- `banner-section`
- `blocks`
- `breadcrumbs`
- `breakpoint`
- `btn`
- `burger`
- `buttons`
- `certificates section`
- `certificates-section`
- `components`
- `cta section`
- `cta-section`
- `custom icon`
- `disable mob hover`
- `editor`
- `english level`
- `english section`
- `english-section`
- `faq section`
- `faq-section`
- `footer`
- `h3`
- `h4`
- `h5`
- `header`
- `loader`
- `location-section`
- `locations-section`
- `main form`
- `marquee section`
- `marquee-section`
- `media section`
- `media-section`
- `mini`
- `mobile menu`
- `modal`
- `modals`
- `onboarding section`
- `onboarding-section`
- `overlay`
- `pagination`
- `partners section`
- `partners-section`
- `portfilio-section`
- `portfolio section`
- `portfolio-section`
- `range`
- `search`
- `select`
- `services section`
- `services-section`
- `star ratings`
- `status-after`
- `status-before`
- `swiper`
- `swiper-btn`
- `swiper-button-next`
- `swiper-button-prev`
- `swiper-container`
- `testimonials section`
- `testimonials-section`
- `transformation section`
- `transformation-section`

## Detected layouts
- `footer`
- `head`
- `header`

## Detected templates
These are documented planned WordPress templates from `docs/content-model.md`; template PHP files are not implemented yet.
- `archive-camp.php`
- `archive-course.php`
- `archive.php`
- `front-page.php`
- `page-contacts.php`
- `page-faq.php`
- `single-camp.php`
- `single-city.php`
- `single-course.php`

## Detected CPTs
Project CPTs are documented planned entities. ACF internal CPTs are detected from `plugins/advanced-custom-fields-pro/acf.php`.
- `CPT: branch`
- `CPT: camp`
- `CPT: city`
- `CPT: course`
- `CPT: faq_item`
- `CPT: review`
- `acf-field`
- `acf-field-group`

## Detected Taxonomies
Project taxonomies are documented planned entities from `docs/content-model.md`.
- `Taxonomy: age_group`
- `Taxonomy: course_direction`
- `Taxonomy: faq_context`
- `Taxonomy: learning_format`
- `Taxonomy: region`

## Detected ACF Field Groups
Project field groups are documented planned entities from `docs/content-model.md`; no project `acf-json` implementation exists yet.
- `ACF field group: Branch Fields`
- `ACF field group: City Fields`
- `ACF field group: Course Fields`
- `ACF field group: Fallback Content`
- `ACF field group: Form Settings`
- `ACF field group: Global CTA Settings`
- `ACF field group: Global Site Settings`
- `ACF field group: SEO and Schema Defaults`

## Detected Plugins
- `Advanced Custom Fields Pro`
- `logika-core`
- `logika-leads`

## Detected REST Endpoints
Project REST endpoints are documented planned contracts from `docs/api.md`; ACF REST integration is detected from the vendor plugin.
- `ACF REST API integration`
- `CRM server-to-server integration`
- `admin lead actions`
- `city reference endpoints`
- `course reference endpoints`
- `lead submit endpoint`

## Entity statistics

- Total graph nodes: `7251`
- Total graph relationships: `11255`
- Communities: `874`
- `graphify_ast`: `6556`
- `doc_section`: `350`
- `acf_field`: `72`
- `ui_component`: `66`
- `style`: `45`
- `asset`: `39`
- `documentation`: `18`
- `implementation_phase`: `18`
- `html_page`: `10`
- `wordpress_template`: `9`
- `feature`: `9`
- `wordpress_cpt`: `8`
- `acf_field_group`: `8`
- `indexed_scope`: `6`
- `rest_endpoint`: `6`
- `database_table`: `6`
- `wordpress_taxonomy`: `5`
- `acf_options_page`: `5`
- `layout_partial`: `3`
- `layout`: `3`
- `wordpress_plugin`: `3`
- `project`: `1`
- `graphify_config`: `1`
- `form`: `1`
- `wordpress_graph`: `1`
- `wordpress_theme`: `1`
- `rest_api_namespace`: `1`

## Relationship statistics

By confidence:
- `EXTRACTED`: `8505`
- `INFERRED`: `2742`
- `AMBIGUOUS`: `8`

Top relationship types:
- `contains`: `4078`
- `calls`: `3806`
- `method`: `1773`
- `extends`: `386`
- `contains_section`: `350`
- `indirect_call`: `127`
- `inherits`: `94`
- `uses_component`: `87`
- `belongs_to_field_group`: `72`
- `uses_asset`: `65`
- `depends_on_field`: `44`
- `styled_by`: `39`
- `uses_javascript`: `32`
- `uses_layout`: `30`
- `documented_by`: `27`
- `references_document`: `18`
- `defines_phase`: `18`
- `owns_template`: `18`
- `uses_css`: `16`
- `imports_style`: `12`
- `uses_taxonomy`: `10`
- `contains_feature`: `9`
- `planned_in`: `9`
- `imports`: `8`
- `candidate_source_for_template`: `8`
- `contains_acf_field_group`: `8`
- `owns_field_group`: `8`
- `indexes`: `6`
- `contains_cpt`: `6`
- `owns_cpt`: `6`

## Dependency graph summary

- HTML pages are connected to source layout partials, source SCSS, source JavaScript, reusable class-derived components, and referenced assets.
- SCSS entrypoint `source/scss/style.scss` is connected to its imported partials; component/style files are connected back to component nodes.
- WordPress templates, CPTs, taxonomies, ACF field groups, ACF fields, options pages, features, API endpoints, database tables, testing docs, security docs and edge-case docs are connected from documentation.
- Vendor ACF Pro is connected to its detected internal CPT registrations and REST API integration.
- Project-specific WordPress implementation entities are marked as documented/planned because the custom theme/plugin PHP files do not exist yet.

Selected dependency edges:

`uses_layout`
- `about.html` -> `footer.html` (EXTRACTED)
- `about.html` -> `head.html` (EXTRACTED)
- `about.html` -> `header.html` (EXTRACTED)
- `article.html` -> `footer.html` (EXTRACTED)
- `article.html` -> `head.html` (EXTRACTED)
- `article.html` -> `header.html` (EXTRACTED)
- `camp.html` -> `footer.html` (EXTRACTED)
- `camp.html` -> `head.html` (EXTRACTED)

`uses_css`
- `about.html` -> `source/scss/style.scss` (EXTRACTED)
- `article.html` -> `source/scss/style.scss` (EXTRACTED)
- `camp.html` -> `source/scss/style.scss` (EXTRACTED)
- `camps.html` -> `source/scss/style.scss` (EXTRACTED)
- `en-courses.html` -> `source/scss/style.scss` (EXTRACTED)
- `faq.html` -> `source/scss/style.scss` (EXTRACTED)
- `index.html` -> `source/scss/style.scss` (EXTRACTED)
- `it-course.html` -> `source/scss/style.scss` (EXTRACTED)

`uses_javascript`
- `about.html` -> `main.js` (EXTRACTED)
- `about.html` -> `swiper.js` (EXTRACTED)
- `article.html` -> `main.js` (EXTRACTED)
- `article.html` -> `swiper.js` (EXTRACTED)
- `camp.html` -> `main.js` (EXTRACTED)
- `camp.html` -> `swiper.js` (EXTRACTED)
- `camps.html` -> `main.js` (EXTRACTED)
- `camps.html` -> `swiper.js` (EXTRACTED)

`uses_asset`
- `about.html` -> `source/img/sprite/sprite.svg#agile-down` (EXTRACTED)
- `about.html` -> `source/js/main.js` (EXTRACTED)
- `about.html` -> `source/js/swiper.js` (EXTRACTED)
- `article.html` -> `source/img/sprite/sprite.svg#agile-down` (EXTRACTED)
- `article.html` -> `source/js/main.js` (EXTRACTED)
- `article.html` -> `source/js/swiper.js` (EXTRACTED)
- `camp.html` -> `source/img/sprite/sprite.svg#agile-down` (EXTRACTED)
- `camp.html` -> `source/js/main.js` (EXTRACTED)

`styled_by`
- `accordion` -> `source/scss/components/_accordion.scss` (EXTRACTED)
- `main form` -> `source/scss/components/forms/_main-form.scss` (EXTRACTED)
- `english level` -> `source/scss/components/cards/_english-level.scss` (EXTRACTED)
- `footer` -> `source/scss/blocks/_footer.scss` (EXTRACTED)
- `header` -> `source/scss/blocks/_header.scss` (EXTRACTED)
- `mobile menu` -> `source/scss/blocks/_mobile-menu.scss` (EXTRACTED)
- `modal` -> `source/scss/blocks/_modal.scss` (EXTRACTED)
- `advantages section` -> `source/scss/blocks/sections/advantages-section.scss` (EXTRACTED)

`targets_cpt`
- `ACF field group: City Fields` -> `CPT: city` (INFERRED)
- `ACF field group: Branch Fields` -> `CPT: branch` (INFERRED)
- `ACF field group: Course Fields` -> `CPT: course` (INFERRED)

`rendered_by_template`
- `CPT: city` -> `single-city.php` (INFERRED)
- `CPT: course` -> `archive-course.php` (INFERRED)
- `CPT: course` -> `single-course.php` (INFERRED)
- `CPT: camp` -> `archive-camp.php` (INFERRED)
- `CPT: camp` -> `single-camp.php` (INFERRED)
- `CPT: faq_item` -> `page-faq.php` (INFERRED)

`uses_taxonomy`
- `CPT: city` -> `Taxonomy: region` (INFERRED)
- `CPT: branch` -> `Taxonomy: learning_format` (INFERRED)
- `CPT: branch` -> `Taxonomy: region` (INFERRED)
- `CPT: course` -> `Taxonomy: age_group` (INFERRED)
- `CPT: course` -> `Taxonomy: course_direction` (INFERRED)
- `CPT: course` -> `Taxonomy: learning_format` (INFERRED)
- `CPT: camp` -> `Taxonomy: age_group` (INFERRED)
- `CPT: camp` -> `Taxonomy: course_direction` (INFERRED)

`supports_feature`
- `lead submit endpoint` -> `Marketing site and lead capture` (INFERRED)
- `city reference endpoints` -> `City selection for localized content` (INFERRED)
- `course reference endpoints` -> `Dynamic city pages` (INFERRED)
- `admin lead actions` -> `Marketing site and lead capture` (INFERRED)
- `CRM server-to-server integration` -> `Marketing site and lead capture` (INFERRED)

`depends_on_database`
- `Marketing site and lead capture` -> `wp_logika_lead_attempts` (INFERRED)
- `Marketing site and lead capture` -> `wp_logika_lead_events` (INFERRED)
- `Marketing site and lead capture` -> `wp_logika_leads` (INFERRED)
- `Data model` -> `wp_logika_import_items` (INFERRED)
- `Data model` -> `wp_logika_import_runs` (INFERRED)

`has_testing_guidance`
- `Marketing site and lead capture` -> `Testing: Logika School testing strategy` (INFERRED)
- `Dynamic city pages` -> `Testing: Logika School testing strategy` (INFERRED)
- `City selection for localized content` -> `Testing: Logika School testing strategy` (INFERRED)
- `Interactive country/region map` -> `Testing: Logika School testing strategy` (INFERRED)

`has_security_guidance`
- `Marketing site and lead capture` -> `Security: Rules for Logika School` (INFERRED)
- `Dynamic city pages` -> `Security: Rules for Logika School` (INFERRED)
- `City selection for localized content` -> `Security: Rules for Logika School` (INFERRED)
- `Interactive country/region map` -> `Security: Rules for Logika School` (INFERRED)

## Validation

- Project architecture: PASS. `graphify explain "WordPress Knowledge Graph"` returned WP graph, plugins, CPTs, templates, features.
- Documentation: PASS. `graphify explain "Phase 3: Data model"` returned `docs/plan.md` phase ownership.
- HTML structure: PASS. `graphify explain "index.html"` returned layout partials, CSS, JS, components, and assets.
- WordPress templates: PASS. `graphify query "Which WordPress templates render CPT city, course, camp, and faq_item?"` returned template/CPT nodes.
- ACF entities: PASS. `graphify path "ACF field group: City Fields" "CPT: city"` returned a 1-hop `targets_cpt` path.
- Plugins: PASS. `graphify explain "Advanced Custom Fields Pro"` returned ACF plugin, internal CPT registrations, and REST integration.
- CPTs: PASS. `graphify explain "CPT: city"` returned `single-city.php`, taxonomy, and field dependencies.
- Taxonomies: PASS. `graphify explain "Taxonomy: course_direction"` returned course/camp links and `logika-core` ownership.
- Implementation phases: PASS. `graphify explain "Phase 3: Data model"` returned phase node and feature ownership.

## Warnings

- Graphify skipped `3` sensitive files during detection; file names are intentionally not listed.
- No project-specific WordPress theme/plugin implementation files were found for `logika-theme`, `logika-core`, or `logika-leads`; those entities come from documentation, not PHP source.
- No project `acf-json` field group files were found; ACF field groups and fields are modeled from `docs/content-model.md`.
- HTML to WordPress template mappings are candidate mappings only and are marked `AMBIGUOUS` until actual templates are created.
- `plugins/advanced-custom-fields-pro/` is vendor code and dominates AST node count; this is expected because `plugins/` was explicitly in scope.
- No LLM semantic backend was configured (`GEMINI_API_KEY` / `GOOGLE_API_KEY` absent), so docs/media semantic extraction was implemented deterministically from local files.
- Source HTML still contains non-Ukrainian placeholder text in some pages; this report does not modify site content.

## Recommended improvements

- After `logika-theme`, `logika-core`, and `logika-leads` are created, rerun Graphify so documented entities become code-detected entities.
- Add ACF Local JSON under the planned `logika-core/acf-json/` path and rerun Graphify to connect real field group JSON to templates and CPTs.
- Confirm the HTML-to-template mapping during Phase 1 inventory and replace `AMBIGUOUS` candidate edges with direct implementation edges once templates exist.
- Consider splitting vendor ACF Pro into a separate graph or tagging it as vendor if future queries become too noisy.
- Keep `docs/content-model.md`, `docs/api.md`, `docs/database.md`, `docs/testing.md`, and `docs/security.md` synchronized with implementation; these files are now graph anchors.

