# Tech Stack: Logika School

## 1. High-level stack decision

The project has two technical layers:

1. Current frontend source — ready-made HTML/CSS/JS in `source/`, built through Gulp into `build/`.
2. Target production stack — WordPress CMS with custom theme `logika-theme`, ACF Pro and project plugins `logika-core` and `logika-leads`.

Core rule:

> We do not rewrite frontend from scratch. We use it as the source of components, styles, scripts and assets, then move business data into WordPress, ACF and CPT.

## 2. Runtime and CMS

| Technology | Version / baseline | Role | Why it is chosen |
|---|---:|---|---|
| WordPress | 7.0 | CMS, admin, URL routing, media, users | Needs managed CMS for editors, SEO and scalable landing pages |
| PHP | 8.3+ | WordPress runtime for theme and plugins | Modern baseline for WordPress development with better typing than older versions |
| MySQL | WordPress default 8.0+ baseline | Standard and well-supported storage for WordPress |
| ACF Pro | 6.x | Fields, repeaters, flexible content, option pages, ACF Blocks | Lets editors change content without visual builder chaos |
| Yoast SEO | stable current version | Sitemap, metadata UI, baseline SEO settings | Avoids building SEO manually |

Versions of WordPress plugins are defined by deployment process. In documentation we keep baseline, not a blanket claim of always using the absolute latest.

## 3. WordPress architecture modules

| Module | Location | Purpose | Why |
|---|---|---|---|
| `logika-theme` | `wp-content/themes/logika-theme` | Templates, `template-parts`, assets, CSS/JS includes | Theme handles only visual layer |
| `logika-core` | `wp-content/plugins/logika-core` | CPT, taxonomies, ACF fields, schema, imports, helper logic | Business logic should survive theme changes |
| `logika-leads` | `wp-content/plugins/logika-leads` or post-MVP module | Forms, leads, CRM/webhooks, retry, send logs | Leads and CRM integration need separate reliability requirements |
| ACF Local JSON | `wp-content/plugins/logika-core/acf-json/` | Field group versioning | Field definitions stay in Git rather than only DB |

Keep `functions.php` minimal: theme supports, menus, image sizes, asset enqueue, and file inclusion. CPT, ACF, schema, imports and CRM logic should not live in `functions.php`.

## 4. Current frontend builder

The current repository already contains a finished build pipeline.

| Technology | Version from `package.json` | Role | Why used |
|---|---:|---|---|
| Node.js | `20.x` | Build runtime | Already configured in `engines`; modern LTS for npm tools |
| npm | Node 20 lockstep | Install dependencies and run scripts | `package-lock.json` exists, so npm is default |
| Gulp | `^4.0.2` | Main build pipeline | Project is organized around Gulp tasks |
| Sass | `^1.45.1` | SCSS compilation | Styles live in `source/scss/`, best to avoid full rewrite |
| PostCSS | `^8.4.28` | CSS processing | For compatibility and post-processing |
| Autoprefixer | `^7.0.1` via `gulp-autoprefixer` | Vendor prefixes | Supports target browser compatibility |
| Webpack | `^5.65.0` + `webpack-stream` | JS bundling in Gulp pipeline | Already integrated |
| Babel | `^7.16.7` | JS transpilation | Helps compatibility for target browsers |
| BrowserSync | `^2.26.14` | Local development and live reload | Useful for HTML verification before WordPress migration |

Scripts:

| Command | Purpose |
|---|---|
| `npm run build` | Build with `gulp build` |
| `npm run html` | Validate `build/**/*.html` with W3C validator |
| `npm run cache` | Build with cache/revisioned assets |
| `npm run backend` | Prepare frontend output for backend integration |
| `npm run zip` | Build archive |

## 5. Frontend libraries

| Library | Version from `package.json` | Use case | Why chosen |
|---|---:|---|---|
| Swiper | `^10.2.0` | Sliders and carousels for reviews/courses/media | Already present, no need to add another carousel stack |
| Fancyapps UI | `^5.0.36` | Modals and galleries/lightbox | Covers popup/gallery use cases |
| AOS | `^2.3.4` | Scroll animations | Already connected; use sparingly |
| Inputmask | `^5.0.7` | Phone mask | Improves UX, backend validation is still mandatory |
| intl-tel-input | `^20.1.0` | Phone fields with country code | Useful for Ukrainian and international numbers |
| Just Validate | `^3.3.3` | Frontend validation | UX-level only; final checks are server-side |
| noUiSlider | `^15.7.1` | Ranges and filters | Use only if present in source markup |
| Smooth Scroll | `^16.1.3` | Smooth scrolling utility | Small UX helper |
| swiped-events | `^1.1.6` | Touch gestures | Adds mobile gestures without heavy framework |
| Lottie Web | `^5.12.2` | Lottie animations | Use if ready animations exist and performance allows |
| Popper | `^2.11.8` | Dropdown/tooltip positioning | Use only where needed |

Rule:

> Do not add a new JS library if current dependency already covers the need.

## 6. CSS and design system

Stack:

- SCSS in `source/scss/`
- component/section partials
- global variables in `source/scss/general/_vars.scss`
- mixins in `source/scss/mixins/` and `source/scss/general/_mixins.scss`
- built CSS in `build/css/`

Why:

- markup is already split into sections, components and general styles;
- WordPress migration can be done in chunks using `template-parts`;
- CSS classes should be preserved close to original to reduce frontend risk.

During WordPress migration:

- source SCSS remains truth source;
- `build/` can be used as reference/output, not for manual edits;
- CSS must not depend on mocked static content;
- components must tolerate long texts, empty states and varying card counts.

## 7. JavaScript architecture

Current JS:

- `source/js/main.js` — project-level interactivity;
- `source/js/rangeSlider.js` — range/filter UI;
- `source/js/swiper.js` — local slider code;
- `build/js/` — compiled assets.

Target rule:

- JS handles UI behavior only;
- business logic for leads, CRM, idempotency, retry and permissions is server-side;
- frontend forms submit only to WordPress endpoint, not directly to CRM;
- frontend validation never replaces server-side validation;
- scripts are loaded via `wp_enqueue_script`, public config passed through localized object.

## 8. Forms and CRM

Allowed form layer options:

| Option | When to choose | Why |
|---|---|---|
| Gravity Forms | If a fast, stable launch with admin UI is needed | Mature ecosystem: webhooks, entries, integrations |

Mandatory backend flow:

```text
Form UI
  ↓
WordPress AJAX/REST endpoint
  ↓
Validate + sanitize + anti-spam
  ↓
Save lead locally
  ↓
Send to CRM server-side
  ↓
Store status: pending / sent / failed / retrying
```

CRM secrets are stored only on server. Frontend must not know CRM endpoint token, webhook secret or private API key.

## 9. API and integrations

| Integration | Technology | Rule |
|---|---|---|
| Public lead submission | WordPress REST API or `admin-ajax.php` | REST API preferred for new custom code |
| CRM/webhook | Server-side WordPress/PHP HTTP client | Server-side only with timeout, retry and idempotency |
| City selection/map | CPT `city` + JS UI | City data must come from WordPress, not static JS |
| SEO/schema | SEO plugin + `logika-core` schema layer | Schema is built from visible content and ACF/CPT data |
| Analytics | GTM/GA/Meta Pixel by business decision | Do not send personal data via client-side events |

## 10. Browser support

Current `browserslist`:

```json
[
  "last 2 versions",
  "IE 11",
  "Firefox ESR"
]
```

Practical rule:

- confirm with stakeholders whether IE11 support is still required before starting WordPress migration;
- if IE11 is no longer required, update `browserslist` and simplify CSS/JS compatibility;
- do not remove browser compatibility currently relied on by the existing pipeline until the decision is formalized.

## 11. Quality tools

| Tool | Version from `package.json` | Purpose |
|---|---:|---|
| Stylelint | `^14.2.0` | SCSS/CSS checks |
| stylelint-config-standard-scss | `^3.0.0` | Base SCSS rules |
| stylelint-order | `^5.0.0` | CSS property order checks |
| node-w3c-validator | `^2.0.1` | HTML validation |
| SVGO | `^3.0.2` | SVG optimization |
| gulp-htmlmin / gulp-clean-css | `^5.0.1` / `^4.3.0` | HTML/CSS minification |
| Typograf | `^6.14.1` | Typography checks |

For WordPress layer add:

- PHPUnit for plugin unit/integration tests;
- WordPress Coding Standards for PHP;
- PHPStan or Psalm at reasonable strictness;
- Playwright or equivalent for key page and form smoke;
- verification that local lead is persisted if CRM fails.

## 12. Hosting and environments

Minimum environments:

- local;
- staging;
- production.

Requirements:

- staging blocked from indexing;
- production served over HTTPS only;
- per-environment secrets;
- staging CRM points to sandbox/test endpoint;
- backups enabled and restore verified;
- page cache/CDN must not cache AJAX/REST lead endpoints.

## 13. Why not another stack

Why not headless-first:

- project needs a practical CMS editor surface, not a separate frontend/backend platform;
- WordPress already handles pages, media, editors, SEO plugins and URL structure;
- headless approach adds complexity without clear MVP value here.

Why not Elementor/Bricks as core:

- project relies on repeatable entities (cities, branches, courses, camps, FAQ, reviews);
- builder mixes data and visual layers;
- harder to control SEO, schema, imports and local landing pages.

Why not rewrite frontend to React/Vue:

- ready HTML/SCSS/JS already exists;
- this is not required to be an SPA;
- WordPress templates plus small JS components are simpler and more reliable for this site.

## 14. What not to add without separate design

- personal cabinet;
- direct frontend CRM integration;
- second form plugin in parallel with first one;
- second carousel/lightbox stack without reason;
- page builder as main approach for systematic pages;
- production secret files in Git;
- raw SQL without `$wpdb->prepare`;
- new CPTs in theme instead of `logika-core`;
- public upload fields in forms without separate security architecture.
