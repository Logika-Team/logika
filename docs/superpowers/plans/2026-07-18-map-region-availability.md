# Map Region Availability Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Show Sumy as unavailable and Zaporizhzhia as unavailable-looking but selectable on the school map.

**Architecture:** Keep region availability in `camp-map.js`. Sumy receives the existing non-interactive unavailable state; Zaporizhzhia keeps its existing city selection handler and receives a separate visual-only class. Existing city API data remains unchanged.

**Tech Stack:** Vanilla JavaScript, SCSS, Gulp, PHP smoke tests, DDEV WordPress.

## Global Constraints

- Do not add dependencies or new UI controls.
- Keep the public city API and the existing `Запоріжжя` city record unchanged.
- Update source and served theme assets together.

---

### Task 1: Preserve city selection while correcting region states

**Files:**
- Modify: `source/js/camp-map.js`
- Modify: `source/scss/blocks/sections/school-map.scss`
- Modify: `tests/city-selection-assets.php`

**Interfaces:**
- Consumes: `regionNames`, `citiesByRegion`, and the existing `path[data-region]` click handler.
- Produces: `is-unavailable` for non-clickable Sumy and `is-city-only` for clickable grey Zaporizhzhia.

- [x] **Step 1: Write the failing test**

```php
if ( ! str_contains( $map, "new Set(['crimea', 'donetsk', 'luhansk', 'kherson', 'sumy'])" ) || ! str_contains( $map, "new Set(['zaporizhia'])" ) || ! str_contains( $styles, 'path.is-city-only' ) ) {
	throw new RuntimeException( 'Map must distinguish unavailable regions from selectable grey regions.' );
}
```

- [x] **Step 2: Run test to verify it fails**

Run: `ddev exec php tests/city-selection-assets.php`

Expected: FAIL because Sumy and Zaporizhzhia do not yet have distinct states.

- [x] **Step 3: Write minimal implementation**

```js
const unavailableRegions = new Set(['crimea', 'donetsk', 'luhansk', 'kherson', 'sumy']);
const cityOnlyRegions = new Set(['zaporizhia']);

if (cityOnlyRegions.has(regionId)) path.classList.add('is-city-only');
```

```scss
path.is-city-only {
  fill: #d9d9d9;
}
```

- [x] **Step 4: Build served assets and run the test**

Run: `npm run backend && ddev exec php tests/city-selection-assets.php`

Expected: the served JS/CSS receive the state and the test passes.

- [x] **Step 5: Verify the public result in DDEV**

Run: `curl -fsS http://logika.ddev.site/wp-json/logika/v1/cities | jq -e '.[] | select(.label == "Запоріжжя" and .show_on_map == true)'`

Expected: one public `Запоріжжя` city remains available.
