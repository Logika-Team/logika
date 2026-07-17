# City Map Editor Flow Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Дати контент-менеджеру короткий і передбачуваний шлях «місто → карта → адреса філії», заблокувати дублікати міських URL та ідемпотентно об’єднати поточний дублікат Берестина.

**Architecture:** Наявні CPT залишаються власниками даних: `city` зберігає регіон і прапорець карти, `branch` зберігає адресу. ACF Local JSON описує редакторський UX, `AdminUi` додає лише prefill, validation і стабільний hash, а `ContentMigration` виконує контрольоване об’єднання міста без перезапису непорожніх значень.

**Tech Stack:** WordPress 7.0, PHP 8.3+, ACF Pro 6.x Local JSON, DDEV, WP-CLI, поточний PHP contract-test harness.

## Global Constraints

- Весь новий адміністративний текст — українською.
- Публічна верстка, REST URL, CSS-класи та порядок секцій не змінюються.
- Наявні ACF field keys і meta names зберігаються.
- `branch` залишається єдиним власником адреси; адреса не дублюється в `city`.
- Імпортовані `city_show_on_map=0` не змінюються, крім підтвердженого Берестина.
- Нові залежності не додаються.

---

### Task 1: ACF editor contract for city and branch

**Files:**
- Modify: `wordpress/wp-content/plugins/logika-core/acf-json/group_logika_city.json`
- Modify: `wordpress/wp-content/plugins/logika-core/acf-json/group_logika_branch.json`
- Modify: `tests/city-editor-experience.php`

**Interfaces:**
- Produces: `field_city_show_on_map` as True/False meta `city_show_on_map`, default `1`.
- Produces: branch tabs `Основне`, `Карта і контакти`, `Технічне` while preserving every existing branch field key/name.

- [ ] **Step 1: Extend the editor contract test and verify RED**

Add assertions that `field_city_show_on_map` is a `true_false` field with default `1`; branch main fields are ordered after a Ukrainian quick guide; `branch_address_hash` is not required and is read-only; `branch_is_active` defaults to `1`.

Run:

```bash
ddev exec php /var/www/html/tests/city-editor-experience.php
```

Expected: FAIL mentioning the missing city map field and branch editor workflow.

- [ ] **Step 2: Add the minimal Local JSON fields and grouping**

Insert after `field_city_region`:

```json
{
  "key": "field_city_show_on_map",
  "label": "Показувати на карті",
  "name": "city_show_on_map",
  "type": "true_false",
  "default_value": 1,
  "ui": 1,
  "instructions": "Увімкнено для нових міст. Після збереження додайте адресу філії за посиланням нижче."
}
```

Reorder the existing branch fields without changing keys/names: quick-guide message; `Основне` tab with `branch_city_id`, `branch_address`, `branch_is_active`; `Карта і контакти` with lat/lng, phone, schedule, maps URL; `Технічне` with external ID and read-only, non-required address hash. Add concrete Ukrainian examples and empty-state instructions.

- [ ] **Step 3: Sync only the two groups and verify GREEN**

```bash
./scripts/wp-mcp.sh acf json sync --dry-run
./scripts/wp-mcp.sh acf json sync --key=group_logika_city
./scripts/wp-mcp.sh acf json sync --key=group_logika_branch
ddev exec php /var/www/html/tests/city-editor-experience.php
```

Expected: two intended pending groups before sync, then PASS.

---

### Task 2: Quick branch link, prefill, hash, and duplicate prevention

**Files:**
- Modify: `wordpress/wp-content/plugins/logika-core/src/AdminUi.php`
- Modify: `wordpress/wp-content/plugins/logika-core/src/CitySlug.php`
- Create: `tests/city-branch-editor-flow.php`

**Interfaces:**
- Produces: `CitySlug::fromTitle(string $title): string`.
- Produces: `AdminUi::branchAddressHash(int $city_id, string $address): string`.
- Produces: `AdminUi::findDuplicateCity(string $title, string $custom_slug, int $exclude_id): ?WP_Post`.
- Consumes: `city_id` query argument for `field_branch_city_id` prefill.

- [ ] **Step 1: Write a runtime test and verify RED**

Create fixtures for two cities and one branch. Assert:

```php
CitySlug::fromTitle( 'Біла Церква' ) === 'bila-tserkva';
AdminUi::branchAddressHash( $city_id, '  вул. Шкільна,  10 ' )
    === AdminUi::branchAddressHash( $city_id, 'вул. шкільна, 10' );
AdminUi::findDuplicateCity( $title, '', $other_id )->ID === $city_id;
```

Set `$_GET['city_id']` to the valid fixture and assert prepared `field_branch_city_id['value']` equals it; set an invalid/non-city ID and assert no prefill. Set global city post and assert the prepared map field instructions contain `post-new.php?post_type=branch&city_id=<ID>`.

Run:

```bash
ddev exec php /var/www/html/tests/city-branch-editor-flow.php
```

Expected: FAIL because methods/hooks do not exist.

- [ ] **Step 2: Extract canonical title transliteration**

Implement in `CitySlug`:

```php
public static function fromTitle( string $title ): string {
    return sanitize_title( strtr( mb_strtolower( $title, 'UTF-8' ), self::TRANSLITERATION ) );
}
```

Change `CitySlug::for()` to call `fromTitle()` when `city_url_slug` is empty.

- [ ] **Step 3: Register the minimal editor hooks**

In `AdminUi::register()` add field-specific prepare filters, `acf/save_post` at priority 20, and `acf/validate_save_post`. Implement:

```php
public static function branchAddressHash( int $city_id, string $address ): string {
    $address = mb_strtolower( trim( preg_replace( '/\s+/u', ' ', $address ) ?? $address ), 'UTF-8' );
    return hash( 'sha256', $city_id . '|' . $address );
}
```

`prepareCityMapField()` appends the escaped quick-create branch URL only for a persisted city. `prepareBranchCityField()` validates the `city_id` query parameter is a `city` and assigns `$field['value']`. `saveBranchHash()` checks a numeric branch post ID, reads city/address after ACF save, and updates the hash only when it differs.

`findDuplicateCity()` compares `CitySlug::for()` across non-trash city records, excluding the current ID. `validateUniqueCity()` reads sanitized `post_title`, the submitted URL field and current post ID, then calls `acf_add_validation_error()` with a Ukrainian edit link when a duplicate exists.

Extend `titlePlaceholder()` with `Назва філії або району, наприклад Центр` for `branch`.

- [ ] **Step 4: Verify GREEN and regressions**

```bash
ddev exec php /var/www/html/tests/city-branch-editor-flow.php
ddev exec php /var/www/html/tests/city-editor-experience.php
ddev exec php /var/www/html/tests/city-api.php
```

Expected: all PASS; repeated branch save yields the same hash.

---

### Task 3: Idempotent Berestyn consolidation

**Files:**
- Modify: `wordpress/wp-content/plugins/logika-core/src/ContentMigration.php`
- Create: `tests/city-duplicate-migration.php`

**Interfaces:**
- Produces: `ContentMigration::migrateCitySlug(string $slug, bool $dry_run = false): array`.
- Uses marker option `logika_city_merge_<md5(slug)>_v1`.

- [ ] **Step 1: Write fixture migration test and verify RED**

Create two cities sharing a canonical custom slug, give the older city one nonempty value and the newer city a different nonempty value, attach a branch to the newer city, then call `migrateCitySlug()`.

Assert canonical selection prefers a city with `city_external_id`, empty-only field copying, branch reassignment, duplicate trashing, and `changed > 0`. Call it again and assert `changed === 0`. Remove the marker and fixtures in shutdown cleanup.

Run:

```bash
ddev exec php /var/www/html/tests/city-duplicate-migration.php
```

Expected: FAIL because `migrateCitySlug()` does not exist.

- [ ] **Step 2: Implement one controlled merge path**

Implement `migrateCitySlug()` using `start()`, a sanitized slug and a marker option. Query non-trash cities, compare through `CitySlug::for()`, prefer a record with nonempty `city_external_id`, then the lowest ID.

For each duplicate:

```php
foreach ( acf_get_fields( 'group_logika_city' ) ?: array() as $field ) {
    if ( empty( $field['name'] ) || 'city_show_on_map' === $field['name'] ) continue;
    if ( self::emptyValue( get_field( $field['name'], $canonical_id ) ) ) {
        $value = get_field( $field['name'], $duplicate_id );
        if ( ! self::emptyValue( $value ) ) update_field( $field['key'], $value, $canonical_id );
    }
}
```

Reassign `branch_city_id` references to the canonical ID and move the duplicate to trash. In non-dry-run mode set the marker after a completed merge. For slug `berestyn`, explicitly set canonical `city_show_on_map` to `1` after validation of the canonical title.

- [ ] **Step 3: Verify fixture RED→GREEN and execute Berestyn once**

```bash
ddev exec php /var/www/html/tests/city-duplicate-migration.php
ddev wp eval 'print_r( Logika\Core\ContentMigration::migrateCitySlug( "berestyn", true ) );'
ddev wp eval 'print_r( Logika\Core\ContentMigration::migrateCitySlug( "berestyn" ) );'
ddev wp eval 'print_r( Logika\Core\ContentMigration::migrateCitySlug( "berestyn" ) );'
```

Expected: fixture PASS; dry-run reports intended changes; first live run merges №3003 into canonical №877; second run reports `changed => 0`.

---

### Task 4: Final contracts and documentation

**Files:**
- Modify: `docs/guidelines/content-model.md`
- Modify: `docs/guidelines/plan.md`
- Modify: `docs/changelog/CHANGELOG.md` if present, otherwise the current changelog file for 2026-07.

**Interfaces:**
- Documents the canonical two-step city/branch editor flow and duplicate prevention.

- [ ] **Step 1: Update only changed contracts**

Document `city_show_on_map`, quick branch creation, automatic address hash, duplicate URL validation and the Berestyn migration marker. Mark the corresponding plan checklist only after runtime verification.

- [ ] **Step 2: Run the complete gate**

```bash
ddev exec php -l /var/www/html/wordpress/wp-content/plugins/logika-core/src/AdminUi.php
ddev exec php -l /var/www/html/wordpress/wp-content/plugins/logika-core/src/CitySlug.php
ddev exec php -l /var/www/html/wordpress/wp-content/plugins/logika-core/src/ContentMigration.php
ddev exec php /var/www/html/tests/city-editor-experience.php
ddev exec php /var/www/html/tests/city-branch-editor-flow.php
ddev exec php /var/www/html/tests/city-duplicate-migration.php
ddev exec php /var/www/html/tests/city-api.php
ddev exec php /var/www/html/tests/city-page.php
ddev exec php /var/www/html/tests/routing.php
./scripts/wp-mcp.sh acf json sync --dry-run
curl -fsS http://logika.ddev.site/wp-json/logika/v1/cities | jq '.[] | select(.slug=="berestyn")'
git diff --check
graphify update .
```

Expected: all tests PASS; ACF sync clean; exactly one public Berestyn has `show_on_map=true`; no whitespace errors; graph updated.
