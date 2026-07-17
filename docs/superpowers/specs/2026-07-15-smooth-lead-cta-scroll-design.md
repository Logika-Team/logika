# Smooth lead CTA scroll

## Goal

On the homepage, CTAs targeting `/#lead-form` scroll smoothly to the hero lead form instead of jumping there.

## Design

- Keep the canonical target URL `/#lead-form` so CTAs on inner pages still navigate to the homepage form.
- In `assets/js/main.js`, intercept only same-origin links whose hash is `#lead-form` when that element exists on the current page.
- Prevent the default anchor jump and use `scrollIntoView({ behavior: 'smooth' })`.
- Do not intercept modified clicks, non-primary clicks, or requests for reduced motion.

## Verification

- A static theme test asserts that the handler and its accessibility guard are present.
- Run the theme routing and source-page test scripts through DDEV.
