# Template Output and Security

Treat ACF values as stored content that still needs context-aware output handling. ACF admin escaping does not make all front-end template output safe.

## Safe Retrieval Pattern

- Prefer `get_field()` and explicit escaping over direct output helpers when the template context matters.
- Check field return format before rendering images, links, relationships, files, galleries, and taxonomy terms.
- Normalize empty values before passing them into template parts.
- Keep fallback behavior close to the component that renders the field.

Examples:

```php
$heading = get_field( 'hero_heading' );
if ( $heading ) {
    echo '<h1>' . esc_html( $heading ) . '</h1>';
}

$copy = get_field( 'hero_copy' );
if ( $copy ) {
    echo wp_kses_post( $copy );
}

$url = get_field( 'cta_url' );
if ( $url ) {
    echo '<a href="' . esc_url( $url ) . '">';
}
```

## Escape by Context

| Output context | Escape with |
|---|---|
| Plain text node | `esc_html()` |
| HTML attribute | `esc_attr()` |
| URL | `esc_url()` |
| Rich editor copy | `wp_kses_post()` or stricter project allowlist |
| Inline JS data | `wp_json_encode()` plus safe script handling |
| CSS value | Avoid dynamic CSS; otherwise strict allowlist |

Do not output arbitrary WYSIWYG, iframe, SVG, shortcode, or oEmbed content without a project-approved allowlist.

## Field Type Notes

- Image/File: render by attachment ID when possible; validate attachment exists; escape URLs and alt text.
- Link: handle URL, title, and target separately; escape each by context.
- Relationship/Post Object: verify `publish` status and expected post type before output.
- Taxonomy: verify term exists and escape name/link.
- Repeater/Flexible Content: guard each optional subfield and skip empty rows/layouts.
- Options: use `get_field( 'field_name', 'option' )`; do not assume global settings exist.

## ACF Forms and Shortcodes

- Front-end ACF forms need nonce, capability/permission checks, allowed fields, spam/rate controls when public, and server-side validation.
- Do not use ACF front-end forms for public lead capture unless the project explicitly accepts that architecture.
- Avoid exposing the ACF shortcode to untrusted users. If shortcode use is required, confirm the current ACF version behavior and project policy.

## Review Checklist

- Every ACF value has context-aware escaping or a documented safe allowlist.
- Templates handle missing fields, deleted related posts, empty repeaters, missing images, and changed return formats.
- Public queries exclude drafts/private/inactive content unless explicitly intended.
- Structured data uses only visible public content.
- REST/AJAX responses expose only fields intended for public or authenticated consumers.

## Official References

- ACF HTML escaping: https://www.advancedcustomfields.com/resources/html-escaping/
- ACF functions: https://www.advancedcustomfields.com/resources/
