<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$city = get_page_by_path( 'test-city', OBJECT, 'city' );
if ( ! $city || ! class_exists( 'Logika_Theme_City_Schema' ) ) {
	fwrite( STDERR, "City schema support is not available.\n" );
	exit( 1 );
}

update_field( 'city_lat', 50.4501, $city->ID );
update_field( 'city_lng', 30.5234, $city->ID );
update_field( 'city_schema_local_business', array( 'name' => 'Logika Київ', 'telephone' => '+380931234567', 'address' => 'Київ, вул. Тестова, 10' ), $city->ID );
$schema = Logika_Theme_City_Schema::build( $city->ID );

if ( 'LocalBusiness' !== $schema['@type'] || 'Logika Київ' !== $schema['name'] || 50.4501 !== $schema['geo']['latitude'] ) {
	fwrite( STDERR, "City LocalBusiness schema does not use ACF data.\n" );
	exit( 1 );
}

echo "City LocalBusiness schema uses ACF data.\n";
