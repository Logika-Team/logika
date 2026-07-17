<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$kyiv = get_page_by_path( 'kyiv', OBJECT, 'city' );
$kyiv_id = $kyiv instanceof WP_Post ? (int) $kyiv->ID : (int) wp_insert_post( array( 'post_type' => 'city', 'post_name' => 'kyiv', 'post_title' => 'Київ', 'post_status' => 'publish' ) );
update_field( 'city_url_slug', 'kyiv', $kyiv_id );

if ( ! str_ends_with( (string) Logika_Theme_Routing::legacyCityUrl( 'map/kuiv' ), '/cities/kyiv/' ) ) {
	fwrite( STDERR, 'Legacy /map/kuiv must redirect to canonical Kyiv URL.' . PHP_EOL );
	exit( 1 );
}

if ( null !== Logika_Theme_Routing::legacyCityUrl( 'map/unknown-city' ) ) {
	fwrite( STDERR, 'Unknown legacy map URL must not redirect.' . PHP_EOL );
	exit( 1 );
}

echo "Legacy city redirects are resolved.\n";
