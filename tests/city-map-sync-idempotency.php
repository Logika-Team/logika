<?php

declare(strict_types=1);

require dirname( __DIR__ ) . '/wordpress/wp-load.php';

$city_id = (int) wp_insert_post( array( 'post_type' => 'city', 'post_status' => 'draft', 'post_title' => 'Ручне місто поза Tilda' ) );
register_shutdown_function( static fn() => wp_delete_post( $city_id, true ) );
update_post_meta( $city_id, 'city_show_on_map', '1' );

ob_start();
require dirname( __DIR__ ) . '/scripts/sync-tilda-school-map.php';
require dirname( __DIR__ ) . '/scripts/sync-tilda-school-map.php';
ob_end_clean();

if ( '1' !== get_post_meta( $city_id, 'city_show_on_map', true ) ) {
	fwrite( STDERR, "Tilda map sync must preserve manually managed cities.\n" );
	exit( 1 );
}

echo "Tilda map sync preserves manually managed cities.\n";
