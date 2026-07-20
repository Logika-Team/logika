<?php

declare(strict_types=1);

$root     = dirname( __DIR__ );
$group    = json_decode( (string) file_get_contents( $root . '/wordpress/wp-content/plugins/logika-core/acf-json/group_logika_global.json' ), true, 512, JSON_THROW_ON_ERROR );
$renderer = (string) file_get_contents( $root . '/wordpress/wp-content/themes/logika-theme/src/Testimonials.php' );
$source   = (string) file_get_contents( $root . '/wordpress/wp-content/themes/logika-theme/src/SourceMarkup.php' );
$errors   = array();

if ( ! in_array( 'global_reviews_gallery', array_column( $group['fields'] ?? array(), 'name' ), true ) ) {
	$errors[] = 'Global testimonial gallery is missing.';
}

if ( ! str_contains( $renderer, 'global_reviews_gallery' ) || ! str_contains( $renderer, 'reviews_section_gallery' ) || ! str_contains( $source, '$section_context' ) || str_contains( $renderer, 'image_context' ) || str_contains( $source, '$image_context' ) ) {
	$errors[] = 'Testimonials must use local galleries with one global fallback.';
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Every testimonials context supports a local gallery with global fallback.\n";
