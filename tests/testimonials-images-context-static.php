<?php

declare(strict_types=1);

$root     = dirname( __DIR__ );
$group    = json_decode( (string) file_get_contents( $root . '/wordpress/wp-content/plugins/logika-core/acf-json/group_logika_testimonials_images.json' ), true, 512, JSON_THROW_ON_ERROR );
$renderer = (string) file_get_contents( $root . '/wordpress/wp-content/themes/logika-theme/src/Testimonials.php' );
$source   = (string) file_get_contents( $root . '/wordpress/wp-content/themes/logika-theme/src/SourceMarkup.php' );
$errors   = array();

foreach ( range( 1, 4 ) as $index ) {
	if ( ! in_array( "testimonials_image_{$index}", array_column( $group['fields'] ?? array(), 'name' ), true ) ) {
		$errors[] = "Shared testimonial image {$index} is missing.";
	}
}

foreach ( array( 'image_context', 'testimonials_image_' ) as $needle ) {
	if ( ! str_contains( $renderer, $needle ) && ! str_contains( $source, $needle ) ) {
		$errors[] = "Testimonials context rendering is missing {$needle}.";
	}
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Every testimonials context has independent image fields.\n";
