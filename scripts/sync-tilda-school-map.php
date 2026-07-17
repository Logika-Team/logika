<?php

$cities_by_region = require __DIR__ . '/tilda-school-map-cities.php';
$aliases = array(
	'Корсунь' => 'Корсунь-Шевченковский',
	'Звягель' => 'Новоград-Волинський',
);
$cities = get_posts(
	array(
		'post_type'      => 'city',
		'post_status'    => 'any',
		'posts_per_page' => -1,
	)
);
$by_label = array();

foreach ( $cities as $city ) {
	foreach ( array( $city->post_title, (string) get_field( 'city_selected_label', $city->ID ) ) as $label ) {
		$by_label[ mb_strtolower( $label, 'UTF-8' ) ][ $city->ID ] = $city;
	}
}

$targets = array();
foreach ( $cities_by_region as $region => $labels ) {
	foreach ( $labels as $label ) {
		$matches = array_values( $by_label[ mb_strtolower( $label, 'UTF-8' ) ] ?? $by_label[ mb_strtolower( $aliases[ $label ] ?? '', 'UTF-8' ) ] ?? array() );
		if ( 1 !== count( $matches ) ) {
			fwrite( STDERR, "Cannot resolve Tilda map city: {$label}\n" );
			exit( 1 );
		}
		$targets[] = array( 'city' => $matches[0], 'label' => $label, 'region' => $region );
	}
}

if ( 98 !== count( $targets ) || 98 !== count( array_unique( array_map( static fn( array $target ): int => $target['city']->ID, $targets ) ) ) ) {
	fwrite( STDERR, "Tilda map must resolve exactly 98 unique cities.\n" );
	exit( 1 );
}

foreach ( $cities as $city ) {
	update_post_meta( $city->ID, 'city_show_on_map', '0' );
}

foreach ( $targets as $target ) {
	$term = term_exists( $target['region'], 'region' );
	if ( ! $term ) {
		$term = wp_insert_term( $target['region'], 'region' );
	}
	if ( is_wp_error( $term ) ) {
		fwrite( STDERR, $term->get_error_message() . "\n" );
		exit( 1 );
	}
	wp_set_object_terms( $target['city']->ID, array( (int) ( is_array( $term ) ? $term['term_id'] : $term ) ), 'region' );
	update_field( 'city_selected_label', $target['label'], $target['city']->ID );
	update_post_meta( $target['city']->ID, 'city_show_on_map', '1' );
}

echo "Synced 98 Tilda map cities.\n";
