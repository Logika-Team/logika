<?php

declare(strict_types=1);

$root = dirname( __DIR__ );
$importer = $root . '/scripts/import-tilda-reviews.php';
$renderer = $root . '/wordpress/wp-content/themes/logika-theme/src/Testimonials.php';
$source_markup = (string) file_get_contents( $root . '/wordpress/wp-content/themes/logika-theme/src/SourceMarkup.php' );

$errors = array();

if ( ! is_file( $importer ) || ! str_contains( (string) file_get_contents( $importer ), 'review_external_id' ) ) {
	$errors[] = 'Tilda review importer must use review_external_id for idempotency.';
}

if ( ! str_contains( (string) file_get_contents( $importer ), "str_starts_with( (string) get_post_meta( \$review->ID, 'review_external_id', true ), 'tilda:' )" ) ) {
	$errors[] = 'Tilda review importer must identify legacy Tilda records without SQL LIKE wildcards.';
}

if ( ! str_contains( (string) file_get_contents( $importer ), "get_post_meta( \$review->ID, 'review_text', true )" ) ) {
	$errors[] = 'Tilda review importer must hash raw review meta rather than formatted WYSIWYG output.';
}

if ( ! is_file( $renderer ) || ! str_contains( (string) file_get_contents( $renderer ), "'post_type'      => 'review'" ) ) {
	$errors[] = 'Testimonials renderer must query review posts.';
}

if ( ! str_contains( (string) file_get_contents( $renderer ), "'posts_per_page' => 12" ) ) {
	$errors[] = 'Testimonials renderer must keep the existing 12-card block size.';
}

if ( ! str_contains( $source_markup, 'Logika_Theme_Testimonials::apply' ) ) {
	$errors[] = 'Source markup must fill existing testimonial cards.';
}

if ( ! str_contains( (string) file_get_contents( $renderer ), 'testimonials-card__excerpt' ) || str_contains( (string) file_get_contents( $renderer ), '<section class="testimonials-section"><div class="container">' ) ) {
	$errors[] = 'Testimonials renderer must preserve the existing section structure.';
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Tilda review import and rendering contracts are present.\n";
