<?php

declare(strict_types=1);

require dirname( __DIR__ ) . '/wordpress/wp-load.php';

$suffix   = wp_generate_uuid4();
$featured = (int) wp_insert_post( array( 'post_type' => 'post', 'post_status' => 'publish', 'post_title' => 'Обрана головна стаття', 'post_date' => '2026-01-01 00:00:00' ) );
$newest   = (int) wp_insert_post( array( 'post_type' => 'post', 'post_status' => 'publish', 'post_title' => 'Новіша стаття', 'post_date' => '2026-01-02 00:00:00' ) );
$city     = (int) wp_insert_post( array( 'post_type' => 'city', 'post_status' => 'publish', 'post_title' => 'Місто головної статті ' . $suffix, 'post_name' => 'featured-city-' . $suffix ) );

try {
	\Logika\Core\CityPostTags::sync();
	wp_add_post_tags( $newest, array( \Logika\Core\CityPostTags::tagId( $city ) ) );
	$request = new WP_REST_Request( 'GET', '/logika/v1/media' );
	$request->set_param( 'featured', $featured );
	$response = \Logika\Core\MediaApi::index( $request );
	$cards    = $response->get_data();
	if ( ! is_array( $cards ) || $featured !== (int) ( $cards[0]['id'] ?? 0 ) ) {
		fwrite( STDERR, "The selected Media Center article must be first for every city.\n" );
		exit( 1 );
	}
	$request->set_param( 'city', $city );
	$cards = \Logika\Core\MediaApi::index( $request )->get_data();
	if ( ! is_array( $cards ) || $newest !== (int) ( $cards[0]['id'] ?? 0 ) ) {
		fwrite( STDERR, "The newest selected-city article must outrank the configured featured article.\n" );
		exit( 1 );
	}
	$functions = (string) file_get_contents( get_template_directory() . '/functions.php' );
	$script    = (string) file_get_contents( get_template_directory() . '/assets/js/media-center.js' );
	if ( ! str_contains( $functions, "'featuredPost'" ) || ! str_contains( $script, "url.searchParams.set('featured'" ) ) {
		fwrite( STDERR, "The Media Center selection must be sent from the page to the API.\n" );
		exit( 1 );
	}
} finally {
	wp_delete_post( $featured, true );
	wp_delete_post( $newest, true );
	wp_delete_post( $city, true );
	foreach ( get_terms( array( 'taxonomy' => 'post_tag', 'hide_empty' => false, 'meta_key' => '_logika_city_id', 'meta_value' => $city ) ) as $tag ) {
		wp_delete_term( (int) $tag->term_id, 'post_tag' );
	}
}

echo "Selected Media Center article is prioritized globally.\n";
