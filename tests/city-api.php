<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

register_shutdown_function(
	static function (): void {
		foreach ( array( 'active-map-branch', 'inactive-map-branch', 'draft-map-branch' ) as $slug ) {
			$branch = get_page_by_path( $slug, OBJECT, 'branch' );
			if ( $branch instanceof WP_Post ) {
				wp_delete_post( $branch->ID, true );
			}
		}

		$city = get_page_by_path( 'map-api-city', OBJECT, 'city' );
		if ( $city instanceof WP_Post ) {
			wp_delete_post( $city->ID, true );
		}
	}
);

$request = new WP_REST_Request( 'GET', '/logika/v1/cities' );
$response = rest_do_request( $request );
$cities = $response->get_data();

if ( 200 !== $response->get_status() || ! isset( $cities[0]['url'], $cities[0]['label'] ) ) {
	fwrite( STDERR, "City selector API is not available.\n" );
	exit( 1 );
}

$labels = array_column( $cities, 'label' );
if ( ! in_array( 'Київ', $labels, true ) || ! in_array( 'Львів', $labels, true ) || in_array( 'Тестове місто', $labels, true ) ) {
	fwrite( STDERR, "City selector API does not expose seeded public cities cleanly.\n" );
	exit( 1 );
}

if ( array_intersect( array( 'Академмістечко', 'Контрактова', 'Онлайн', 'Місто для карти' ), $labels ) || in_array( 'Інші міста', array_column( array_column( $cities, 'region' ), 'label' ), true ) ) {
	fwrite( STDERR, "City selector API exposes invalid city or region entries.\n" );
	exit( 1 );
}

$kyiv = current( array_filter( $cities, static fn( array $city ): bool => 'Київ' === $city['label'] ) );
$bila_tserkva = current( array_filter( $cities, static fn( array $city ): bool => 'Біла Церква' === $city['label'] ) );
if ( empty( $kyiv['region']['label'] ) || 'місто Київ' !== $kyiv['region']['label'] || empty( $bila_tserkva['region']['label'] ) || 'Київська область' !== $bila_tserkva['region']['label'] ) {
	fwrite( STDERR, "City selector API does not expose city regions for grouped selector UI.\n" );
	exit( 1 );
}

$map_cities = array_values( array_filter( $cities, static fn( array $city ): bool => true === ( $city['show_on_map'] ?? null ) ) );
if ( 98 !== count( $map_cities ) || true !== ( $kyiv['show_on_map'] ?? null ) ) {
	fwrite( STDERR, "Map must expose the 98 current Tilda cities.\n" );
	exit( 1 );
}

foreach ( array( 'Донецька область', 'Луганська область', 'Херсонська область' ) as $region ) {
	if ( array_filter( $map_cities, static fn( array $city ): bool => $region === ( $city['region']['label'] ?? '' ) ) ) {
		fwrite( STDERR, "Map must not expose cities in {$region}.\n" );
		exit( 1 );
	}
}

$zaporizhzhia = array_values( array_filter( $map_cities, static fn( array $city ): bool => 'Запорізька область' === ( $city['region']['label'] ?? '' ) ) );
if ( array( 'Запоріжжя' ) !== array_column( $zaporizhzhia, 'label' ) ) {
	fwrite( STDERR, "Map must expose only Zaporizhzhia in the Zaporizhzhia region.\n" );
	exit( 1 );
}

$novyi_rozdil = current( array_filter( $cities, static fn( array $city ): bool => 'Новий Розділ' === $city['label'] ) );
if ( 'Львівська область' !== ( $novyi_rozdil['region']['label'] ?? '' ) ) {
	fwrite( STDERR, "Новий Розділ must belong to Львівська область.\n" );
	exit( 1 );
}

$map_city = get_page_by_path( 'map-api-city', OBJECT, 'city' );
$map_city_id = $map_city ? (int) $map_city->ID : (int) wp_insert_post( array( 'post_type' => 'city', 'post_name' => 'map-api-city', 'post_title' => 'Місто для карти', 'post_status' => 'publish' ) );

foreach ( array( 'active-map-branch' => array( 'Активна філія', 'publish', 1 ), 'inactive-map-branch' => array( 'Неактивна філія', 'publish', 0 ), 'draft-map-branch' => array( 'Чернетка філії', 'draft', 1 ) ) as $slug => $branch_data ) {
	list( $title, $status, $active ) = $branch_data;
	$branch = get_page_by_path( $slug, OBJECT, 'branch' );
	$branch_id = $branch ? (int) $branch->ID : (int) wp_insert_post( array( 'post_type' => 'branch', 'post_name' => $slug, 'post_title' => $title, 'post_status' => $status ) );
	wp_update_post( array( 'ID' => $branch_id, 'post_status' => $status, 'post_title' => $title ) );
	update_field( 'branch_city_id', $map_city_id, $branch_id );
	update_field( 'branch_address', 'вул. Тестова, 10', $branch_id );
	update_field( 'branch_is_active', $active, $branch_id );
}

$branch_response = rest_do_request( new WP_REST_Request( 'GET', '/logika/v1/cities/' . $map_city_id . '/branches' ) );
$branches = $branch_response->get_data();

if ( 200 !== $branch_response->get_status() || array( 'Активна філія' ) !== array_column( $branches, 'label' ) ) {
	fwrite( STDERR, "Map branch API must expose only active published branches.\n" );
	exit( 1 );
}

echo "City selector and map APIs expose public city data and active branches.\n";
