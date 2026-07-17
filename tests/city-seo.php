<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$city = get_page_by_path( 'test-city', OBJECT, 'city' );
if ( ! $city || ! class_exists( 'Logika_Theme_City_Seo' ) ) {
	fwrite( STDERR, "City SEO support is not available.\n" );
	exit( 1 );
}

update_field( 'city_index_status', 'noindex', $city->ID );

if ( ! Logika_Theme_City_Seo::should_noindex( $city->ID ) ) {
	fwrite( STDERR, "City noindex status is ignored.\n" );
	exit( 1 );
}

echo "City index status controls public robots output.\n";
