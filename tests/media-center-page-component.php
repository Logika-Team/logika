<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$page = file_get_contents( get_template_directory() . '/source-pages/media-center.php' ) ?: '';

foreach ( array( 'archive-section', 'articles-section', 'offers-section', 'media-section', 'faq-section', 'cta-section' ) as $section ) {
	if ( ! str_contains( $page, $section ) ) {
		fwrite( STDERR, "The full media center page is missing {$section}.\n" );
		exit( 1 );
	}
}

foreach ( array( 'href="#media-offers"', 'href="#media-news"', 'href="#media-articles"', 'id="media-offers"', 'id="media-news"', 'id="media-articles"' ) as $anchor ) {
	if ( ! str_contains( $page, $anchor ) ) {
		fwrite( STDERR, "Media center topic anchor is missing {$anchor}.\n" );
		exit( 1 );
	}
}

echo "Media center keeps the full main HTML structure.\n";
