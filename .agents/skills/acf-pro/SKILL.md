---
name: acf-pro
description: "Use when working with Advanced Custom Fields Pro in WordPress projects: ACF field groups, Local JSON, acf-json, acf_add_local_field_group, options pages, repeaters, flexible content, relationship fields, ACF Blocks, get_field output, field sync, or ACF data model changes."
---

# ACF Pro

## Overview

Use this skill to design, implement, review, or migrate ACF Pro field models without losing editor data, breaking sync, or exposing unsafe template output.

## Core Workflow

1. Inspect the project first: read repo instructions, WordPress architecture docs, existing CPT/taxonomy code, current `acf-json` paths, and templates that call ACF fields.
2. Let project docs override this generic guidance. If docs define field names, Local JSON paths, language rules, plugin boundaries, or content ownership, follow them.
3. Choose the storage path before adding fields:
   - Local JSON for editor-managed field groups that should sync across environments.
   - PHP registration for locked/distributed field groups that should not be edited in admin.
   - Options pages only for shared global settings, not per-entity content.
4. Model fields around editor responsibility and rendering needs, then implement the smallest field group that supports the current behavior.
5. Render ACF values through safe helpers or explicit escaping; do not assume `get_field()` returns browser-safe HTML.
6. Validate with the project's WordPress/PHP tests, ACF sync check, and targeted template smoke checks.

## Reference Loading

- Read `references/field-modeling.md` when designing field groups, choosing field types, or deciding between repeaters, flexible content, relationships, and options pages.
- Read `references/local-json-and-registration.md` when configuring `acf-json`, syncing fields, registering fields in PHP, changing keys/names, or reviewing field persistence.
- Read `references/template-output-security.md` when rendering ACF values, reviewing templates, exposing fields through REST/AJAX, or handling rich text and front-end forms.

## Implementation Rules

- Keep CPTs, taxonomies, business fields, imports, and shared ACF configuration in the project plugin when the architecture separates business logic from the theme.
- Keep field names stable. Renaming an ACF `name` can make existing meta look missing unless a migration is included.
- Keep field keys unique and deterministic when registering via PHP.
- Prefer clear business names over generic names like `text_1`, `section_2`, or `content_block`.
- Do not store secrets in ACF options. Use environment/server configuration for API keys, webhook tokens, SMTP credentials, and private endpoints.
- Add fallback or empty-state behavior for optional fields before rendering them publicly.
- Use relationship, post object, taxonomy, and user fields only when templates validate status/capabilities before output.

## Validation Checklist

- ACF Pro availability is checked before calling ACF functions.
- Field groups are versioned through Local JSON or registered in code.
- Field group location rules match the intended post type, taxonomy, options page, user, media, or block.
- Required fields have admin instructions and public fallback behavior.
- Template output is escaped by context.
- Field changes are covered by focused tests or documented manual verification when the project has no WordPress test harness yet.
