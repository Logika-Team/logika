# City Media Priority Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Prioritize newest selected-city articles before common articles everywhere articles are presented.

**Architecture:** Keep `Logika\Core\MediaApi` as the REST ordering source and suppress only its ACF featured-post override while a city is selected. Make the homepage media query use the same city-first, common-fallback ordering when a city cookie is active; preserve editor selection without a city.

**Tech Stack:** WordPress 7.0, PHP 8.3+, ACF Pro, DDEV integration scripts.

## Global Constraints

- Keep all public copy in Ukrainian.
- Reuse `CityPostTags` visibility queries; add no dependencies.
- Preserve category, search, visibility, and date-sort behavior.
- Test first and run in DDEV.

---

### Task 1: City-first REST ordering

**Files:**
- Modify: `tests/media-center-featured-post.php:5-39`
- Modify: `wordpress/wp-content/plugins/logika-core/src/MediaApi.php:43-93`

**Interfaces:**
- Consumes: `GET /logika/v1/media?city=<city_id>&featured=<post_id>`.
- Produces: City-tagged published cards first; common cards fill the rest; featured promotion only with no city.

- [ ] **Step 1: Write the failing test**

```php
wp_add_post_tags( $newest, array( \Logika\Core\CityPostTags::tagId( $city ) ) );
$request->set_param( 'city', $city );
$cards = \Logika\Core\MediaApi::index( $request )->get_data();
if ( $newest !== (int) ( $cards[0]['id'] ?? 0 ) ) {
	throw new RuntimeException( 'The newest selected-city article must outrank the configured featured article.' );
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `ddev exec php tests/media-center-featured-post.php`

Expected: exits non-zero because the configured featured article is currently prepended for a selected city.

- [ ] **Step 3: Write minimal implementation**

```php
return new WP_REST_Response( self::prioritize( array_merge( $local, $common ), $city_id ? 0 : $featured, $search, $limit ) );
```

- [ ] **Step 4: Run test to verify it passes**

Run: `ddev exec php tests/media-center-featured-post.php && ddev exec php tests/media-api.php`

Expected: both commands exit 0.

### Task 2: Homepage city-first media cards

**Files:**
- Modify: `tests/city-post-tags.php:47-73`
- Modify: `wordpress/wp-content/themes/logika-theme/src/SourceMarkup.php:703-736`

**Interfaces:**
- Consumes: `logika_city` cookie and front-page `home_media_posts` ACF selection.
- Produces: Up to three city-first cards with common cards filling the remainder when a city is selected.

- [ ] **Step 1: Write the failing test**

```php
if ( strpos( $local_markup, get_the_title( $posts[3] ) ) > strpos( $local_markup, get_the_title( $posts[1] ) ) ) {
	throw new RuntimeException( 'Homepage media cards must sort selected-city articles by newest date.' );
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `ddev exec php tests/city-post-tags.php`

Expected: exits non-zero because the current ACF relationship order is retained.

- [ ] **Step 3: Write minimal implementation**

```php
$visibility = array(
	'relation' => 'OR',
	array( 'key' => 'post_hide_from_blog', 'compare' => 'NOT EXISTS' ),
	array( 'key' => 'post_hide_from_blog', 'value' => '0', 'compare' => '=' ),
);
if ( $city_id ) {
	$local = get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 3, 'orderby' => 'date', 'order' => 'DESC', 'fields' => 'ids', 'meta_query' => $visibility, 'tax_query' => CityPostTags::cityTaxQuery( $city_id ) ) );
	$common = count( $local ) < 3 ? get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 3 - count( $local ), 'orderby' => 'date', 'order' => 'DESC', 'fields' => 'ids', 'meta_query' => $visibility, 'tax_query' => CityPostTags::commonTaxQuery() ) ) : array();

	return array_map( 'absint', array_merge( $local, $common ) );
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `ddev exec php tests/city-post-tags.php && ddev exec php tests/home-media-center.php`

Expected: both commands exit 0.

### Task 3: Full verification

**Files:**
- Modify: `docs/plan.md`

- [ ] **Step 1: Run focused checks**

Run: `ddev exec php tests/media-center-featured-post.php && ddev exec php tests/media-api.php && ddev exec php tests/city-post-tags.php && ddev exec php tests/home-media-center.php && ddev exec php tests/media-center-articles.php`

Expected: every test prints its success line and exits 0.

- [ ] **Step 2: Verify in DDEV browser**

Open `/media-center/` and `/blog/` with a selected city. Confirm the first media card is the newest city-tagged article and common articles remain after local articles.

- [ ] **Step 3: Mark the project plan complete**

Change item 30 in `docs/plan.md` to `[x]` after the focused checks pass.
