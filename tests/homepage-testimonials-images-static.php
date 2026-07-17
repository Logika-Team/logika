<?php

declare(strict_types=1);

$root     = dirname( __DIR__ );
$home      = json_decode( (string) file_get_contents( $root . '/wordpress/wp-content/plugins/logika-core/acf-json/group_logika_home.json' ), true, 512, JSON_THROW_ON_ERROR );
$renderer  = (string) file_get_contents( $root . '/wordpress/wp-content/themes/logika-theme/src/Testimonials.php' );
$overrides = (string) file_get_contents( $root . '/wordpress/wp-content/plugins/logika-core/src/HomepageImageOverrides.php' );
$fields    = array_column( $home['fields'] ?? array(), null, 'name' );
$errors    = array();

foreach ( range( 1, 4 ) as $index ) {
	$field = $fields[ "home_testimonials_image_{$index}" ] ?? array();
	if ( 'image' !== ( $field['type'] ?? '' ) ) {
		$errors[] = "Homepage testimonials need image {$index}.";
	}
}

foreach ( array( 'review_photo', 'testimonials-card__avatar', 'testimonials-card is-image', 'home_testimonials_image_' ) as $needle ) {
	if ( ! str_contains( $renderer, $needle ) ) {
		$errors[] = "Testimonials renderer is missing {$needle}.";
	}
}

if ( ! str_contains( $overrides, 'testimonialDefaults' ) ) {
	$errors[] = 'Homepage image controls do not provide testimonial defaults.';
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Homepage testimonial photos are editor-managed.\n";
