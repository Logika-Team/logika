# FAQ Static Parity Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make the WordPress FAQ match `source/faq.html`, while retaining the homepage-backed map and lead CTA.

**Architecture:** The served FAQ is `source-pages/faq.php`, rendered by `Logika_Theme_Source_Markup`. Keep its existing `school-map` and `cta-section` blocks because they already match the homepage and use shared map/form runtime contracts. Replace only the stale banner and accordion markup with their static source counterparts.

**Tech Stack:** WordPress PHP template, existing CSS/JS assets, DDEV PHP smoke tests.

## Global Constraints

- Preserve the existing shared map and lead-form markup.
- Do not add dependencies or alter unrelated dirty worktree files.
- Keep all public text Ukrainian.

---

### Task 1: Restore static FAQ markup

**Files:**

- Modify: `wordpress/wp-content/themes/logika-theme/source-pages/faq.php`
- Modify: `tests/faq-page-component.php`

- [x] Add a failing source-contract test for the static FAQ banner and accordion.
- [x] Run `ddev exec php tests/faq-page-component.php` and confirm it fails on the old banner.
- [x] Replace the stale banner and accordion in `source-pages/faq.php` with the matching markup from `source/faq.html`.
- [x] Run `ddev exec php tests/faq-page-component.php tests/source-lead-forms.php tests/map-assets.php` and confirm every contract passes.
- [x] Request `/faq/` in DDEV and confirm the static FAQ banner, map, CTA and new assets are rendered.
