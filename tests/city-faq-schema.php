<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$city = get_page_by_path( 'test-city', OBJECT, 'city' );
if ( ! $city || ! class_exists( 'Logika_Theme_City_Faq_Schema' ) ) {
	fwrite( STDERR, "City FAQ schema is not available.\n" );
	exit( 1 );
}

$schema = Logika_Theme_City_Faq_Schema::build( $city->ID );
if ( 'FAQPage' !== $schema['@type'] || 'Чи є FAQ для міста?' !== $schema['mainEntity'][0]['name'] ) {
	fwrite( STDERR, "City FAQ schema does not use visible FAQ data.\n" );
	exit( 1 );
}

echo "City FAQ schema uses active ACF FAQ data.\n";
