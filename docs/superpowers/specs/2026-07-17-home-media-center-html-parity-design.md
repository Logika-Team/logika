# Home Media Center HTML Parity Design

**Goal:** Restore the homepage Media Center in the WordPress theme using the unchanged source HTML layout.

## Scope

- Replace only the `Медіа-центр` `media-section` in `wordpress/wp-content/themes/logika-theme/source-pages/index.php`.
- Copy that section from `source/index.html` verbatim, retaining its existing classes, links, SVG references, and asset paths.
- Restore the matching Media Center SCSS from the current root source and rebuild the existing theme stylesheet, removing the stale red debug rule that overrides the section layout.
- Keep `SourceMarkup` free of the previous rule that removes this section.
- Verify the generated homepage contains the original section and remains valid PHP.

## Rejected alternatives

1. Keep the custom card layout: it has already diverged from the source design.
2. Add a new PHP template or ACF schema: no dynamic content was requested.

## Acceptance criteria

- Homepage has one `Медіа-центр` heading and the original source markup.
- The rendered section has no red debug background or fixed debug height.
- The school-benefits `media-section` remains unchanged.
- No unrelated dirty worktree changes are modified.
