<?php

declare(strict_types=1);

$root   = dirname( __DIR__ );
$json   = $root . '/wordpress/wp-content/plugins/logika-core/acf-json/';
$shared = json_decode( (string) file_get_contents( $json . 'group_logika_testimonials_images.json' ), true, 512, JSON_THROW_ON_ERROR );
$it     = json_decode( (string) file_get_contents( $json . 'group_logika_page_it_courses.json' ), true, 512, JSON_THROW_ON_ERROR );
$render = (string) file_get_contents( $root . '/wordpress/wp-content/themes/logika-theme/src/Testimonials.php' );
$source = (string) file_get_contents( $root . '/wordpress/wp-content/themes/logika-theme/src/SourceMarkup.php' );
$errors = array();

$fields = array_column( $shared['fields'] ?? array(), null, 'name' );
if ( empty( $shared['active'] ) || 'text' !== ( $fields['reviews_section_title']['type'] ?? '' ) || 'gallery' !== ( $fields['reviews_section_gallery']['type'] ?? '' ) || 4 !== (int) ( $fields['reviews_section_gallery']['max'] ?? 0 ) ) {
	$errors[] = 'The reusable reviews section must provide an editable title and four-image gallery.';
}

$it_fields = $it['fields'] ?? array();
$clone     = array_filter( $it_fields, static fn( array $field ): bool => 'clone' === ( $field['type'] ?? '' ) && in_array( 'group_logika_testimonials_images', (array) ( $field['clone'] ?? array() ), true ) );
$reviews_tab = array_filter( $it_fields, static fn( array $field ): bool => 'tab' === ( $field['type'] ?? '' ) && 'Відгуки' === ( $field['label'] ?? '' ) );
if ( ! $reviews_tab || ! $clone ) {
	$errors[] = 'IT Courses must expose a dedicated Reviews tab with the reusable section controls.';
}

if ( ! str_contains( $render, 'reviews_section_title' ) || ! str_contains( $render, 'reviews_section_gallery' ) || ! str_contains( $render, 'section_context' ) || ! str_contains( $source, '$section_context' ) ) {
	$errors[] = 'Testimonials must prefer the current page section settings before global fallbacks.';
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Reviews section overrides are available and rendered.\n";
