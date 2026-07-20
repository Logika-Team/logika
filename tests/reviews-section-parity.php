<?php

declare(strict_types=1);

$root     = dirname( __DIR__ );
$renderer = (string) file_get_contents( $root . '/wordpress/wp-content/themes/logika-theme/src/SourceMarkup.php' );
$template = (string) file_get_contents( $root . '/wordpress/wp-content/themes/logika-theme/template-parts/sections/reviews.php' );
$source   = (string) file_get_contents( $root . '/wordpress/wp-content/themes/logika-theme/source-pages/index.php' );
$errors   = array();

foreach ( array( 'testimonials-card__avatar', 'testimonials-card__rating', 'testimonials-card__tag', 'testimonials-card__decor', 'testimonials-card__watch' ) as $class ) {
	if ( ! str_contains( $source, $class ) ) {
		$errors[] = "Homepage reviews source is missing {$class}.";
	}
}

if ( ! str_contains( $renderer, 'renderReviewsSection' ) || ! str_contains( $template, 'renderReviewsSection' ) ) {
	$errors[] = 'Course reviews must render the shared homepage reviews component.';
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Reviews keep homepage markup, assets, and styles.\n";
