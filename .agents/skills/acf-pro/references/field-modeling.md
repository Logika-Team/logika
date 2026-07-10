# ACF Pro Field Modeling

Use field groups to match editorial ownership, not template shape alone. A field group should answer: who edits this, where do they edit it, and which public behavior depends on it?

## Group Boundaries

- Use one field group per clear entity or settings surface: page template, CPT, taxonomy term, media attachment, user, block, or options page.
- Keep global settings out of entity field groups. Use options pages for shared phone numbers, social links, default images, CTA labels, schema defaults, and similar site-wide values.
- Keep per-post, per-city, per-course, or per-branch content on the entity itself.
- Avoid giant "all content" groups. They are harder to sync, review, migrate, and reason about.

## Field Naming

- Use stable snake_case field names that describe business meaning: `course_age_min`, `branch_google_maps_url`, `global_phone`.
- Do not rename field names without a migration plan for existing post meta/options.
- Avoid positional names such as `text_1`, `image_2`, or `block_left`.
- Use labels and instructions for editors; use field names for durable code contracts.

## Field Type Choices

| Need | Prefer | Avoid |
|---|---|---|
| Plain text | Text or Textarea | WYSIWYG for simple labels |
| Safe rich editorial copy | WYSIWYG with output allowlist | Raw unescaped HTML |
| Repeated simple rows | Repeater | Flexible Content |
| Mixed layout sections | Flexible Content | Many optional sibling groups |
| Single related entity | Post Object | Relationship with max 1 unless UX needs search/list |
| Multiple related entities | Relationship | Free-text IDs/slugs |
| Controlled choices | Select, Radio, Button Group, Taxonomy | Editor-entered magic strings |
| Shared fallback value | Options page field | Duplicated page fields |

## Repeaters and Flexible Content

- Use repeaters for stable row structures where every row has the same fields.
- Use nested repeaters sparingly; they are harder for editors and templates.
- Use flexible content only when editors truly choose between different block layouts.
- Keep flexible layouts small and named by business purpose, not visual position.
- For public pages, define what happens when a repeater has zero rows or an optional subfield is empty.

## Relationships

- Validate related post status before output; do not leak drafts, private posts, inactive entities, or noindex-only content unless intended.
- Use explicit ordering for relationship output when the public order matters.
- For bidirectional relationships, define the source of truth before implementing sync logic.
- When imports populate relationships, use stable external IDs or slugs rather than titles.

## Options Pages

- Use options pages for global, reusable, non-secret settings.
- Set a capability appropriate to the editors who should access the page.
- Do not put CRM keys, SMTP passwords, API tokens, private endpoints, or webhook secrets in ACF options.
- Watch autoload behavior for large option payloads; do not autoload large galleries, huge repeaters, or rarely used settings without a reason.

## Official References

- ACF field types and functions: https://www.advancedcustomfields.com/resources/
- ACF options pages: https://www.advancedcustomfields.com/resources/options-page/
- ACF Blocks: https://www.advancedcustomfields.com/resources/blocks/
