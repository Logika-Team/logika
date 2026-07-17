<?php

declare(strict_types=1);

$scss = (string) file_get_contents( dirname( __DIR__ ) . '/source/scss/blocks/sections/categories-section.scss' );
$css  = (string) file_get_contents( dirname( __DIR__ ) . '/wordpress/wp-content/themes/logika-theme/assets/css/blocks/sections/categories-section.css' );
$theme = (string) file_get_contents( dirname( __DIR__ ) . '/wordpress/wp-content/themes/logika-theme/functions.php' );
$errors = array();

if ( ! str_contains( $scss, 'background-color: var(--violet-100);' ) || ! str_contains( $scss, '&.swiper-button-disabled {' ) || ! str_contains( $scss, 'background-color: var(--grey-100);' ) ) {
	$errors[] = 'Category slider SCSS does not distinguish available and disabled controls.';
}

if ( ! str_contains( $css, '.categories-section__controls-btn{width:52px;height:52px;border-radius:50%;background-color:var(--violet-100)' ) || ! str_contains( $css, '.categories-section__controls-btn.swiper-button-disabled{background-color:var(--grey-100)}' ) ) {
	$errors[] = 'Category slider CSS does not distinguish available and disabled controls.';
}

if ( ! str_contains( $theme, '.categories-section__controls-btn{background-color:var(--violet-100)}.categories-section__controls-btn.swiper-button-disabled{background-color:var(--grey-100)}' ) ) {
	$errors[] = 'The active theme stylesheet does not contain the category slider controls override.';
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Category slider controls reflect available scroll directions.\n";
