<?php

declare(strict_types=1);


$theme = dirname(__DIR__) . '/wordpress/wp-content/themes/logika-theme';
$css   = (string) file_get_contents( $theme . '/assets/css/blocks/sections/services-section.css' );
$theme_functions = (string) file_get_contents( $theme . '/functions.php' );

foreach ( array( 'aspect-ratio:588/431', '.services-section__item-image{position:absolute;bottom:0' ) as $rule ) {

	if ( ! str_contains( preg_replace( '/\s+/', '', $theme_functions ), $rule ) ) {

		fwrite( STDERR, "Course card must keep its background size when an image is taller than the card.\n" );
		exit( 1 );
	}
}

if ( ! str_contains( $theme_functions, '.services-section__items>li:nth-child(3) .services-section__item-image{transform:scale(.9)' ) ) {

	fwrite( STDERR, "The third course card image must be smaller.\n" );
	exit( 1 );
}

if ( ! str_contains( $theme_functions, '.services-section__items>li:nth-child(4) .services-section__item-image{transform:scale(.9)' ) ) {

	fwrite( STDERR, "The fourth course card image must be smaller.\n" );
	exit( 1 );
}

if ( ! str_contains( $theme_functions, '.services-section__item-btns{position:relative;z-index:2}' ) ) {

	fwrite( STDERR, "Course card buttons must stay above uploaded images.\n" );
	exit( 1 );
}

foreach ( array( 2, 4 ) as $index ) {
	foreach ( array( ".services-section__items>li:nth-child({$index}) .services-section__item-ages{top:auto;right:auto;bottom:0;left:-67px}", ".services-section__items>li:nth-child({$index}) .services-section__item-icon{top:43px;right:-67px;bottom:auto;left:auto}" ) as $rule ) {

		if ( ! str_contains( $theme_functions, $rule ) ) {

			fwrite( STDERR, "Course cards with media on the right must swap their age badge and icon.\n" );
			exit( 1 );
		}
	}
}

echo "Course card background remains fixed while uploaded images may overflow.\n";
