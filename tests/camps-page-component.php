<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$archive = file_get_contents( get_template_directory() . '/archive-camp.php' ) ?: '';
$page = file_get_contents( get_template_directory() . '/source-pages/camps.php' ) ?: '';

foreach ( array( "logika_theme_render_source_page( 'camps' )", 'camp-page-hero', 'camp-highlights', 'camp-formats', 'camp-history', 'camp-booking' ) as $marker ) {
	if ( ! str_contains( $archive . $page, $marker ) ) {
		fwrite( STDERR, "The full camps page is missing {$marker}.\n" );
		exit( 1 );
	}
}

echo "Camps page keeps the full main HTML structure.\n";
