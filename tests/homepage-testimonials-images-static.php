<?php

declare(strict_types=1);

$root     = dirname( __DIR__ );
$global    = json_decode( (string) file_get_contents( $root . '/wordpress/wp-content/plugins/logika-core/acf-json/group_logika_global.json' ), true, 512, JSON_THROW_ON_ERROR );
$renderer  = (string) file_get_contents( $root . '/wordpress/wp-content/themes/logika-theme/src/Testimonials.php' );
$fields    = array_column( $global['fields'] ?? array(), null, 'name' );
$errors    = array();

if ( 'gallery' !== ( $fields['global_reviews_gallery']['type'] ?? '' ) || 4 !== (int) ( $fields['global_reviews_gallery']['max'] ?? 0 ) ) {
	$errors[] = 'Global Options need one four-image testimonials gallery.';
}

foreach ( array( 'review_photo', 'testimonials-card__avatar', 'testimonials-card is-image', 'global_reviews_gallery' ) as $needle ) {
	if ( ! str_contains( $renderer, $needle ) ) {
		$errors[] = "Testimonials renderer is missing {$needle}.";
	}
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Testimonials photos use editable local settings with a global fallback.\n";
