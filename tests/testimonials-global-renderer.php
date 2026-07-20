<?php

declare(strict_types=1);

$root      = dirname( __DIR__ );
$renderer  = (string) file_get_contents( $root . '/wordpress/wp-content/themes/logika-theme/src/Testimonials.php' );
$source    = (string) file_get_contents( $root . '/wordpress/wp-content/themes/logika-theme/src/SourceMarkup.php' );
$migration = (string) file_get_contents( $root . '/wordpress/wp-content/plugins/logika-core/src/ContentMigration.php' );
$errors    = array();

if ( ! str_contains( $renderer, "global_reviews_gallery" ) || ! str_contains( $renderer, "global_reviews_title" ) || ! str_contains( $renderer, 'reviews_section_gallery' ) || ! str_contains( $renderer, 'reviews_section_title' ) || ! str_contains( $source, '$section_context' ) ) {
	$errors[] = 'Testimonials renderer must prefer a local section configuration and retain global fallbacks.';
}
if ( str_contains( $renderer, 'image_context' ) || str_contains( $source, '$image_context' ) ) {
	$errors[] = 'Testimonials must use one named section context.';
}
if ( ! str_contains( $migration, "acf-migrate-reviews" ) || ! str_contains( $migration, 'migrateReviewsPresentation' ) ) {
	$errors[] = 'Review migration must have a dedicated idempotent WP-CLI command.';
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Testimonials use local settings with global fallbacks.\n";
