# Local JSON and PHP Registration

ACF field definitions should be reproducible across local, staging, and production. Choose one source of truth for each field group and document it in the project.

## Local JSON

- Prefer Local JSON for field groups that editors can manage in wp-admin and developers need in Git.
- Check the project-specific `acf-json` path before adding groups. Themes often use `theme/acf-json`; plugin-led architectures often use a plugin path.
- Make the `acf-json` directory writable in environments where field groups are edited.
- Review JSON diffs like code. A small UI field change can rewrite keys, locations, choices, and conditional logic.
- Sync field groups through the ACF admin or project deployment process; do not rely on production-only DB changes.

Common filters:

```php
add_filter( 'acf/settings/save_json', function ( string $path ): string {
    return plugin_dir_path( __FILE__ ) . 'acf-json';
} );

add_filter( 'acf/settings/load_json', function ( array $paths ): array {
    $paths[] = plugin_dir_path( __FILE__ ) . 'acf-json';
    return $paths;
} );
```

Use project-specific paths and namespacing instead of copying this snippet blindly.

## PHP Registration

Use PHP registration when field definitions must ship as locked code or be generated consistently.

- Register fields on `acf/init` so ACF is loaded before ACF functions run.
- Guard calls with `function_exists( 'acf_add_local_field_group' )` or equivalent when needed.
- Keep group keys and field keys unique. Duplicate keys can override earlier definitions.
- Keep generated PHP readable; remove unused default settings when safe.
- Remember that field groups registered via code are not normally editable in the Field Groups admin UI.

Minimal pattern:

```php
add_action( 'acf/init', function (): void {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

    acf_add_local_field_group( [
        'key' => 'group_example_settings',
        'title' => 'Example Settings',
        'fields' => [
            [
                'key' => 'field_example_heading',
                'label' => 'Heading',
                'name' => 'example_heading',
                'type' => 'text',
            ],
        ],
        'location' => [
            [
                [
                    'param' => 'options_page',
                    'operator' => '==',
                    'value' => 'example-settings',
                ],
            ],
        ],
    ] );
} );
```

## Changing Existing Fields

- Adding a field is usually safe when templates handle empty values.
- Changing labels and instructions is safe when field names and keys stay stable.
- Changing type, return format, choices, or relationship target can break templates and existing data; inspect current usages first.
- Renaming a field name requires a migration or compatibility read path.
- Deleting a field requires proving templates, imports, exports, REST responses, and admin workflows no longer use it.

## Sync Review Checklist

- The intended `acf-json` files changed and no unrelated groups were rewritten.
- No field key was accidentally regenerated for an existing field.
- Location rules point to the intended post type, taxonomy, block, template, user, media, or options page.
- Return formats match template expectations.
- Required fields have editor instructions and public fallback behavior.

## Official References

- ACF Local JSON: https://www.advancedcustomfields.com/resources/local-json/
- Register fields via PHP: https://www.advancedcustomfields.com/resources/register-fields-via-php/
- ACF options pages: https://www.advancedcustomfields.com/resources/options-page/
