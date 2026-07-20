# About page ACF editing

## Goal

Editors manage every visible text, image, alt text, link and repeated card on the WordPress page `about` through ACF, while `/about/` preserves its existing order, markup contracts and styling.

## Scope

- Extend `group_logika_page_about.json` on the `about` page only.
- Keep singleton content as text, WYSIWYG, image, link or textarea fields.
- Keep repeated content as named repeaters: stats, directions, outcomes, gallery, benefit cards and onboarding steps.
- Extend the existing `Logika_Theme_Page_Content` adapter; do not add a second About renderer.
- Keep relationship fields for reviews, FAQ and posts.

## Rendering

`SourceMarkup` continues to load the source page and `PageContent` replaces its content from ACF. Empty optional rows are omitted. Empty fields retain the current source markup as a public fallback. All ACF output is escaped by context; image fields use WordPress media attachment IDs.

## Validation

- Add a focused PHP test covering the About ACF field contract and repeater replacement.
- Run the WordPress regression suite, Local JSON sync dry-run and DDEV smoke check for `/about/`.
