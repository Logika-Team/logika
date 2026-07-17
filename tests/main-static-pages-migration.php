<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$theme  = get_template_directory();
$checks = array(
	'index'     => array( 'media-section__card--platform', 'media-section__card--game-method' ),
	'camp'      => array( 'trips-section__slider', 'details-section__items', 'gallery-section__slider' ),
	'camps'     => array( 'gallery-section__slider', 'camp-history' ),
	'it-course' => array( 'course-banner-section__wrapp', 'learn-section__items', 'process-section__items' ),
	'faq'       => array( 'faq-banner-section', 'href="#faq"', 'href="#lead-form"' ),
);

foreach ( $checks as $page => $markers ) {
	$markup = file_get_contents( "{$theme}/source-pages/{$page}.php" ) ?: '';

	foreach ( $markers as $marker ) {
		if ( ! str_contains( $markup, $marker ) ) {
			fwrite( STDERR, "{$page} is missing {$marker}.\n" );
			exit( 1 );
		}
	}
}

foreach ( array( 'details-section.css', 'gallery-section.css', 'trips-section.css', 'course-banner-section.css', 'learn-section.css', 'process-section.css' ) as $style ) {
	if ( ! is_readable( "{$theme}/assets/css/blocks/sections/{$style}" ) ) {
		fwrite( STDERR, "Theme is missing {$style}.\n" );
		exit( 1 );
	}
}

foreach ( array( 'img/details/details-icon1.svg', 'img/gallery/gallery.png', 'img/trips/trip-img1.png', 'img/course/it-course-image.svg', 'img/learn/learn-image.png' ) as $asset ) {
	if ( ! is_readable( "{$theme}/assets/{$asset}" ) ) {
		fwrite( STDERR, "Theme is missing {$asset}.\n" );
		exit( 1 );
	}
}

$script = file_get_contents( "{$theme}/assets/js/main.js" ) ?: '';
$functions = file_get_contents( "{$theme}/functions.php" ) ?: '';

foreach ( array( 'tripsSectionSlider', 'gallerySectionSlider' ) as $marker ) {
	if ( ! str_contains( $script, $marker ) ) {
		fwrite( STDERR, "Theme slider script is missing {$marker}.\n" );
		exit( 1 );
	}
}

foreach ( array( "array( 'trips-section', 'details-section', 'gallery-section' )", "array( 'course-banner-section', 'learn-section', 'process-section' )", 'wp_enqueue_style( "logika-{$section}"' ) as $style ) {
	if ( ! str_contains( $functions, $style ) ) {
		fwrite( STDERR, "Theme does not enqueue the required section styles: {$style}.\n" );
		exit( 1 );
	}
}

echo "Confirmed main static-page sections are present in the WordPress theme.\n";
