# Media Section Mobile Design

## Goal

Make the homepage Media Center section responsive on mobile widths while preserving the existing WordPress markup, content, links, and Figma artwork.

## Design

Use the existing `.media-section__cards-layout` DOM and make its mobile layout a single vertical flow. Match the locally extracted Figma frame `Media center – Mobile` (`320:177963`): 16px horizontal container inset, 20px section stack gap, 20px content padding, 14px main-card media-to-copy gap, and full-width CTA controls. Replace fixed mobile heights and absolute CTA coordinates with content-driven sizing; keep artwork decorative and clipped inside each card.

## Scope

- Modify `wordpress/wp-content/themes/logika-theme/assets/css/blocks/sections/media-section.css` only.
- Keep PHP, JavaScript, ACF data, URLs, and asset paths unchanged.
- Preserve desktop and tablet grid behavior.
- Cover 320px through 767px viewport widths without horizontal overflow.

## Validation

- Add a small source-level regression check for the extracted mobile constraints.
- Run the check in a failing-first then passing cycle.
- Verify the live homepage at 320px, 375px, and 767px with Playwright and inspect computed widths/overflow.
