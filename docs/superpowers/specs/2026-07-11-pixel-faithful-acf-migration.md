# Pixel-faithful ACF migration

## Goal

Preserve the supplied HTML as the visual contract while making manager-owned text and images editable through ACF Pro.

## Scope

- The supplied HTML in the repository's `main/build/` is the canonical visual baseline. The theme keeps a checked-in runtime copy only because the parent worktree is unavailable in deployment.
- Do not replace a source section with simplified markup. Dynamic templates must retain its element hierarchy, CSS classes, SVG usage, `picture` structure, controls, and item order.
- Migrate each page section one at a time: homepage, shared header/footer, static pages, archives, and single templates.
- Add an ACF field only when it maps to a visible editor-owned value. Preserve a visual fallback until initial content exists.
- The CPT editor is ACF-first: no blank Gutenberg canvas; field groups render below the title.

## Non-goals

- Do not edit `build/`; it is a supplied source artifact.
- Do not invent a new visual system or replace supplied assets.
- Do not use raw HTML fields as a substitute for modeled content.

## Verification

- Rendered route has the same section sequence and structural class signature as its source page.
- ACF fixture values replace the intended text/image without changing layout classes.
- Browser visual checks compare each migrated route with the supplied HTML baseline at desktop and mobile widths.
- PHP smoke, ACF sync, and `git diff --check` stay green.
