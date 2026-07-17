# Marketing Page Content Design

## Goal

Move all unique, editor-owned text from the static source for About, IT Courses, English Courses, FAQ, and Media Center into the existing page-specific ACF Local JSON groups without changing markup or duplicating CMS entities.

## Scope

- Add only page-owned text fields and repeaters to the five existing `group_logika_page_*.json` groups.
- Seed every new field from the current source-page default once, without overwriting existing editor values.
- Extend `Logika_Theme_Page_Content` to substitute non-empty ACF values into the current source markup with context-appropriate escaping.
- Keep courses, FAQ items, reviews, posts/offers, city-map content, lead-field labels, header, and footer under their existing owners.

## Data model

Each page group receives fields grouped by its visible sections. Single headings, labels, and support copy use text or textarea fields. Stable page-specific rows use repeaters: trust-bar rows, marquee labels, benefit cards, process steps, English levels, and promotional cards. Existing relationship fields continue to render selected CPT data.

The fallback is the source HTML. An empty ACF value leaves the current static string visible, so adding fields cannot blank a public page.

## Rendering

`Logika_Theme_Page_Content::apply()` remains the sole page-content adapter. It will use an explicit per-page default-to-field map for single strings and render repeaters only when they contain valid rows. Plain text uses `esc_html()`; rich source paragraphs use `wp_kses_post()` only where a WYSIWYG field is intentionally used. No raw editor HTML is injected.

## Initial content and verification

An idempotent seed script fills only missing ACF values from the source defaults. A focused PHP test updates representative new fields from each page and asserts that the rendered output changes, while an empty value preserves the source fallback. Local JSON is checked with the project ACF dry-run sync before applying any database write.

## Non-goals

- No redesign or source-markup rewrite.
- No new CPT, plugin, options page, page builder, or JavaScript dependency.
- No migration of header/footer/global lead-form copy in this change.
