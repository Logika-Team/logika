# Student Projects Section Design

## Goal

Replace the empty homepage portfolio block with the approved Figma section for student projects.

## Scope

- Change `source/index.html` only inside the existing portfolio section in `main`.
- Keep the Ukrainian heading `Проєкти наших учнів`.
- Extract node `320:121360` from `/home/sbaikov/Downloads/Logika School UX_UI Design (2) (Copy).fig` with the local `figctx` converter.
- Copy the node's raster assets to `source/img/portfolio/` and reference them from the source markup.
- Use the existing `portfolio-section.scss` partial and existing Swiper dependency for the horizontal carousel.
- Build the generated page after the source change; do not hand-edit `build/index.html`.

## Layout

The section contains a centered heading and a horizontally scrollable series of project cards. The featured violet card includes course and student information, a project photo, a video CTA, and a yellow trial-lesson button. Standard cards contain the student portrait, course tags, name/age, and project summary.

## Responsive and Accessibility Requirements

- Cards remain usable through horizontal swipe/scroll on narrow viewports.
- Decorative images use empty `alt`; meaningful project and student images use Ukrainian alternative text.
- CTA controls retain semantic links or buttons and visible keyboard focus.
- No new dependency is added; reduced-motion preferences are respected by avoiding nonessential animation.

## Verification

- Confirm that `figctx pack` returns the expected node, assets, and typography tokens.
- Run the existing frontend build.
- Validate the produced HTML.
- Open the generated homepage and check desktop and narrow viewport layout.
