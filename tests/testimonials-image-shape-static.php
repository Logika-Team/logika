<?php

declare(strict_types=1);

$root   = dirname( __DIR__ );
$source = (string) file_get_contents( $root . '/source/scss/components/cards/_testimonials-card.scss' );
$section = (string) file_get_contents( $root . '/source/scss/blocks/sections/testimonials-section.scss' );
$theme  = (string) file_get_contents( $root . '/wordpress/wp-content/themes/logika-theme/functions.php' );
$errors = array();


foreach ( array( $source, $theme ) as $styles ) {
	$needles = $source === $styles
		? array( 'width: min(100%, 220px)', 'aspect-ratio: 1 / 1', 'border-radius: 50%', 'width: 100%', 'height: 100%', 'object-fit: cover' )
		: array( 'width:min(100%,220px)', 'aspect-ratio:1/1', 'border-radius:50%', 'width:100%', 'height:100%', 'object-fit:cover' );

	foreach ( $needles as $needle ) {
		if ( ! str_contains( $styles, $needle ) ) {
			$errors[] = "Testimonials image style is missing {$needle}.";
		}
	}
}

if ( ! str_contains( $section, "&__box {\n        width: 100%;" ) || ! str_contains( $section, 'repeat(6, minmax(0, 1fr))' ) || ! str_contains( $theme, '.testimonials-section__box{width:100%}' ) || ! str_contains( $theme, 'repeat(6,minmax(0,1fr))' ) ) {
	$errors[] = 'Testimonials grid must stay within its section container.';
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Testimonials photos use a circular square frame.\n";
