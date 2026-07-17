# Main Static Pages Migration Design

## Scope

Port only the confirmed `main` updates for `index`, `camp`, `camps`, `it-course`, and `faq` into the WordPress theme. Do not merge branches or change unrelated dirty worktree files.

## Approach

The theme will keep rendering the existing `source-pages/*.php` files through `Logika_Theme_Source_Markup`, so form normalisation, WordPress routes, and asset URL rewriting remain shared. The migration will update only the corresponding page markup, required images, and compiled theme styles; existing ACF substitutions stay in place.

## Verification

A focused regression check will require the new section classes and assets. The relevant PHP checks, theme build, and local DDEV routes will then be exercised.
