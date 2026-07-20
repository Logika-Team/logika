# Homepage Student Projects Content Design

## Goal

Update the homepage student-projects carousel with the approved Tilda-export content while preserving one existing Maxym card with the current student photo.

## Scope

- Keep one standard card for `Максим, 12 років` with `maxym.jpg`.
- Keep the existing featured Maxym card and its current CTA/video behavior.
- Replace the duplicated standard Maxym cards in the static fallback with three student project cards:
  - Ян Корнієць, Львів — Tactical Strike Force;
  - Ілля Шляпников, Харків — Shadow Light 2;
  - Максим Кравченко, Дніпро — Chernobyl Horror.
- Use project illustrations for the three new standard cards because verified student portraits are unavailable.
- Preserve the existing ACF repeater contract and allow a standard card to render `project_image` when `student_image` is empty.

## Data and rendering

The source fallback remains in `source-pages/index.php`. ACF rows continue to replace the fallback when editors have configured `home_portfolio_items`. For standard ACF cards, `student_image` remains preferred; `project_image` is the explicit illustration fallback. No database migration or new dependency is required.

## Assets

Reuse the existing Maxym photo and copy three verified game-related illustrations from the Tilda export into the theme portfolio assets. Each meaningful image receives Ukrainian alt text describing the project, not an unverified student identity.

## Verification

- Run `ddev exec php tests/homepage-student-projects.php`.
- Run the relevant PHP syntax/static checks.
- Verify the rendered homepage contains one Maxym photo card, the featured card, and all three named projects.
- Check desktop and mobile carousel behavior at the local DDEV URL.
