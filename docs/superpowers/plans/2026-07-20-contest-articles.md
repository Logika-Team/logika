# Contest Articles Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Publish the two top-level Tilda contests as WordPress blog posts and link the Media Center cards to them.

**Architecture:** Reuse the existing `post` renderer and ACF cover-image field. Import source media into the local WordPress media library, then replace only the two placeholder card URLs in the Media Center source markup.

**Tech Stack:** WordPress, ACF Pro, WP-CLI through DDEV, PHP 8.3.

## Global Constraints

- Public copy is Ukrainian only.
- Use `/home/sbaikov/Documents/tilda-export/project917000` as the source of truth.
- Do not add a CPT, plugin, dependency, or custom page template.
- Work only in `/home/sbaikov/Desktop/Projects/logika/.worktrees/wordpress`.

---

### Task 1: Import two article records and their source media

**Files:**
- Source: `/home/sbaikov/Documents/tilda-export/project917000/files/page127952493body.html`
- Source: `/home/sbaikov/Documents/tilda-export/project917000/files/page66526767body.html`
- Runtime: WordPress database through `ddev wp`

**Produces:** Published posts `logirace-2026` and `fantasy-games-2025`, imported attachments, and `article_cover_image` metadata.

- [x] Write an assertion that both posts are published, have content, and have `article_cover_image`; run it and observe failure.
- [x] Import original non-placeholder Tilda assets with `ddev wp media import`, create the posts with `ddev wp post create`, and preserve source text as semantic headings, paragraphs, and lists.
- [x] Set each cover as `_thumbnail_id` and `article_cover_image`; re-run the assertion and require exit code `0`.

### Task 2: Link the Media Center cards

**Files:**
- Modify: `wordpress/wp-content/themes/logika-theme/source-pages/media-center.php:54,75`
- Test: `tests/contest-article-links.php`

**Produces:** Card URLs `/media-center/articles/logirace-2026/` and `/media-center/articles/fantasy-games-2025/`.

- [x] Add a failing static assertion for both URLs and run `php tests/contest-article-links.php`.
- [x] Replace only the two `href="#"` values in the contest cards.
- [x] Re-run the static assertion and require exit code `0`.

### Task 3: Smoke-test the rendered paths

- [x] Require HTTP `200` for `/media-center/articles/logirace-2026/` and `/media-center/articles/fantasy-games-2025/` through DDEV.
- [x] Require both URLs to appear in rendered `/media-center/` markup.

## Review

- The scope is only the two approved top-level contest pages.
- The plan reuses existing posts, fields, media management, and template markup.
- No new infrastructure is required.
