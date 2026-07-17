# Tilda school map sync Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox syntax for tracking.

**Goal:** Make the WordPress school map match the 98 current Tilda cities and represent unavailable regions accurately.

**Architecture:** A repeatable WP-CLI sync script will own the 98-city Tilda mapping, correcting only map labels, the existing region relation, and one map-visibility meta flag. The API exposes that flag through the existing city response; the map filters it, renders unavailable regions gray, and overlays a single accessible Zaporizhzhia marker.

**Tech Stack:** PHP 8.3, WordPress REST API and WP-CLI, existing region taxonomy, vanilla JavaScript, SCSS, DDEV.

## Global Constraints

- Do not add dependencies or duplicate city data in browser code.
- Keep public labels Ukrainian and preserve current city URLs.
- Do not delete city records or modify the navbar selector.
- Use test-first changes and DDEV for database writes.

---

### Task 1: Sync Tilda map city data

**Files:**
- Create: scripts/sync-tilda-school-map.php
- Modify: tests/city-api.php

- [x] Add a failing REST test that expects exactly 98 show_on_map cities, Kyiv in місто Київ, no mapped cities in Donetsk/Luhansk/Kherson, and only Запоріжжя in Zaporizhzhia.
- [x] Implement an idempotent script containing every city from /home/sbaikov/Documents/tilda-export/project917000/files/page14117934body.html, grouped by its Tilda record. It updates the display label, sets the existing region term, sets city_show_on_map=1, and sets that meta to 0 on all remaining city records.
- [x] Abort before writing if a source city cannot be resolved uniquely, except documented legacy aliases for Корсунь and Звягель.
- [x] Run the script in DDEV, then run ddev exec php /var/www/html/tests/city-api.php.

### Task 2: Expose map visibility in the existing API

**Files:**
- Modify: wordpress/wp-content/plugins/logika-core/src/CityApi.php
- Modify: tests/city-api.php

- [x] Extend the failing test to require a boolean show_on_map field.
- [x] Add show_on_map from city_show_on_map post meta to the existing response mapper.
- [x] Re-run ddev exec php /var/www/html/tests/city-api.php until it passes.

### Task 3: Render the corrected map states

**Files:**
- Modify: source/js/camp-map.js
- Modify: wordpress/wp-content/themes/logika-theme/assets/js/camp-map.js
- Modify: source/scss/blocks/sections/school-map.scss
- Modify: wordpress/wp-content/themes/logika-theme/assets/css/blocks/sections/school-map.css
- Modify: tests/map-assets.php

- [x] Add failing asset assertions for show_on_map, unavailable-region handling, and school-map__city-marker.
- [x] Filter the existing shared city context to show_on_map records before grouping.
- [x] Make Crimea, Donetsk, Luhansk, Kherson, and Zaporizhzhia gray and non-interactive as regional paths.
- [x] Create one semantic button for Запоріжжя, position it from the Zaporizhzhia SVG path bounding box, and use the existing city-selection behavior on click or keyboard activation.
- [x] Build/copy only changed map assets and run ddev exec php /var/www/html/tests/map-assets.php.

### Task 4: Verify and document

**Files:**
- Modify: docs/guidelines/plan.md
- Modify: docs/changelog/2026-07.md

- [x] Confirm the city endpoint exposes exactly 98 show_on_map records.
- [x] Check the homepage in a browser: Kyiv is available, Crimea/Donetsk/Luhansk/Kherson are gray, and only the Zaporizhzhia marker is usable there.
- [x] Run focused API and asset checks, graphify update ., and git diff --check.
- [x] Record the completed task and concise monthly changelog entry.
