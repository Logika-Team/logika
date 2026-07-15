# Dynamic Map and Online Form Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace hardcoded map cities and branches with WordPress records, start the map neutral, and show the existing hero lead form in online mode.

**Architecture:** `Logika\\Core\\CityApi` retains city retrieval and adds a narrow lazy branch endpoint. `camp-map.js` fetches cities once, then branches only for the selected city. Online mode moves the already-bound hero form node into a temporary panel and restores it for offline mode.

**Tech Stack:** WordPress REST API, PHP 8.3, ACF Pro, vanilla JavaScript, SCSS, DDEV and Gulp.

## Global Constraints

- Public copy is Ukrainian only.
- Keep CPT, taxonomy and ACF field definitions unchanged.
- Expose only published cities plus published, active branches.
- Add no form, REST client library or static city/branch fallback data.
- Preserve dirty worktree changes outside the listed files.

---

### Task 1: Expose active branches for a selected published city

**Files:**
- Modify: `wordpress/wp-content/plugins/logika-core/src/CityApi.php`
- Modify: `tests/city-api.php`

**Interfaces:**
- Consumes: `GET /logika/v1/cities/{id}/branches` with integer `id`.
- Produces: `200` with branch objects `{id, label, address, lat, lng, map_url}`; unknown/unpublished city returns `404`.

- [x] **Step 1: Write a failing endpoint contract test.** Add a published test city, one active published branch, one inactive branch and one draft branch. Request the new route and assert `array_column( $branches, 'label' ) === array( 'Активна філія' )`.
- [x] **Step 2: Verify red.** Run `ddev exec php /var/www/html/tests/city-api.php`; it must fail because the route is absent.
- [x] **Step 3: Implement the route.** Register `'/cities/(?P<id>\\d+)/branches'` with `WP_REST_Server::READABLE`, `__return_true`, numeric `id` validation and callback `CityApi::branches`. Confirm the city is published, then query `branch` posts by `branch_city_id`, `branch_is_active = 1`, `post_status = publish`, title ASC. Return ACF fields `branch_address`, `branch_lat`, `branch_lng` and `branch_google_maps_url` as the documented object.
- [x] **Step 4: Verify green.** Run `ddev exec php /var/www/html/tests/city-api.php`; it must print `City selector and map APIs expose public city data and active branches.`
- [x] **Step 5: Commit.** Run `git add wordpress/wp-content/plugins/logika-core/src/CityApi.php tests/city-api.php && git commit -m "feat: expose map branches from WordPress"`.

### Task 2: Replace map hardcoding with REST state and reuse the hero form

**Files:**
- Modify: `source/js/camp-map.js`
- Modify: `wordpress/wp-content/themes/logika-theme/assets/js/camp-map.js`
- Modify: `wordpress/wp-content/themes/logika-theme/functions.php`
- Modify: `tests/map-assets.php`

**Interfaces:**
- Consumes: `window.logikaThemeAssets.mapUrl`, `citiesEndpoint`, `branchesEndpoint`, city records and `[data-logika-lead-form]`.
- Produces: neutral initial state, region-filtered cities, CMS-backed branch details and online/offline form movement.

- [x] **Step 1: Write a failing source contract.** Assert `camp-map.js` contains `citiesEndpoint`, `branchesEndpoint`, `fetchCities`, `moveHeroForm` and `restoreHeroForm`; assert it does not contain `const dnipro` or `selectRegion('dnipropetrovsk')`.
- [x] **Step 2: Verify red.** Run `ddev exec php /var/www/html/tests/map-assets.php`; it must fail on the old hardcoded/default-selection contract.
- [x] **Step 3: Implement minimal state.** Localize `citiesEndpoint = rest_url( 'logika/v1/cities' )` and `branchesEndpoint = rest_url( 'logika/v1/cities/' )`. Fetch and group city records by `region.slug`; register map paths only for nonempty groups. Leave every path inactive after SVG load. On region keyboard/click selection, render its city buttons. On city selection, fetch `${branchesEndpoint}${city.id}/branches`, render returned active branches, and set the iframe only from returned coordinates or `map_url`. Use controlled Ukrainian error/no-branch text instead of static data.
- [x] **Step 4: Reuse the hero form.** Keep `heroForm`, its parent and next sibling; `moveHeroForm()` appends it to a JS-created `.school-map__online` panel while hiding layout/details; `restoreHeroForm()` inserts it back before its saved sibling and restores layout/details. Do not clone the form.
- [x] **Step 5: Synchronize built asset and verify green.** Run `npm run build`, copy emitted `build/js/camp-map.js` to `wordpress/wp-content/themes/logika-theme/assets/js/camp-map.js`, then run `ddev exec php /var/www/html/tests/map-assets.php`; it must print `School map uses theme assets and dynamic map contracts.`
- [x] **Step 6: Commit.** Run `git add source/js/camp-map.js wordpress/wp-content/themes/logika-theme/assets/js/camp-map.js wordpress/wp-content/themes/logika-theme/functions.php tests/map-assets.php && git commit -m "feat: load school map data from WordPress"`.

### Task 3: Style the online panel and verify the user flow

**Files:**
- Modify: `source/scss/blocks/sections/school-map.scss`
- Modify: `wordpress/wp-content/themes/logika-theme/assets/css/blocks/sections/school-map.css`
- Modify: `docs/guidelines/plan.md`

**Interfaces:**
- Consumes: `.school-map__online[hidden]` and existing `.main-form` rules.
- Produces: a responsive online panel with the existing accessible hero form.

- [x] **Step 1: Write failing style assertions.** Make `tests/map-assets.php` require `&__online` and `[hidden]` in `school-map.scss`.
- [x] **Step 2: Verify red.** Run `ddev exec php /var/www/html/tests/map-assets.php`; it must fail because the panel styles are missing.
- [x] **Step 3: Implement panel styles.** Add only `.school-map__online` width/spacing plus `[hidden] { display: none; }`; reuse existing `main-form` styling and theme color variables. Run `npm run build` and copy emitted `build/css/blocks/sections/school-map.css` into the theme asset.
- [x] **Step 4: Verify all.** Run `ddev exec php /var/www/html/tests/city-api.php`, `ddev exec php /var/www/html/tests/map-assets.php` and `npm run build`. In the browser at `http://logika.ddev.site`, verify neutral first paint, region/city selection, admin-backed branches, online/offline form movement and no form submission.
- [x] **Step 5: Update roadmap and commit.** Mark map items complete only after the prior evidence, then run `git add source/scss/blocks/sections/school-map.scss wordpress/wp-content/themes/logika-theme/assets/css/blocks/sections/school-map.css docs/guidelines/plan.md && git commit -m "style: add school map online form panel"`.
