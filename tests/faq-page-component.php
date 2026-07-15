<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$page = file_get_contents( get_template_directory() . '/source-pages/faq.php' ) ?: '';

foreach ( array( 'banner-section', 'faq-section', 'testimonials-section', 'school-map', 'cta-section' ) as $section ) {
	if ( ! str_contains( $page, $section ) ) {
		fwrite( STDERR, "The full FAQ page is missing {$section}.\n" );
		exit( 1 );
	}
}

echo "FAQ page keeps the full main HTML structure.\n";
