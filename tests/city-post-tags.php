<?php

declare(strict_types=1);

require dirname( __DIR__ ) . '/wordpress/wp-load.php';

$suffix = (string) wp_generate_uuid4();
$cities = array();
$posts  = array();
$page   = (int) get_option( 'page_on_front' );
$before = $page ? get_post_meta( $page, 'home_media_posts', false ) : array();
$cookie = $_COOKIE['logika_city'] ?? null;
$related_before = array();

try {
	foreach ( array( 'Київ', 'Львів' ) as $index => $title ) {
		$cities[] = wp_insert_post( array( 'post_type' => 'city', 'post_status' => 'publish', 'post_title' => $title . ' ' . $suffix, 'post_name' => "city-tag-{$index}-{$suffix}" ) );
	}

	foreach ( array( 'Загальна', 'Київська', 'Львівська', 'Для кількох міст' ) as $index => $title ) {
		$posts[] = wp_insert_post( array( 'post_type' => 'post', 'post_status' => 'publish', 'post_title' => $title . ' ' . $suffix, 'post_name' => "city-tag-post-{$index}-{$suffix}" ) );
		$date = wp_date( 'Y-m-d H:i:s', time() - ( 4 - $index ) * 60, wp_timezone() );
		wp_update_post( array( 'ID' => $posts[ $index ], 'post_date' => $date, 'post_date_gmt' => get_gmt_from_date( $date ) ) );
	}
	update_post_meta( $posts[1], 'post_related_city', $cities[0] );
	update_post_meta( $posts[2], 'post_related_city', $cities[1] );
	update_post_meta( $posts[3], 'post_related_city', $cities[0] );

	\Logika\Core\CityPostTags::sync();
	$kyiv_tag = \Logika\Core\CityPostTags::tagId( $cities[0] );
	$lviv_tag = \Logika\Core\CityPostTags::tagId( $cities[1] );
	wp_add_post_tags( $posts[3], array( $lviv_tag ) );

	if ( ! $kyiv_tag || ! $lviv_tag || ! has_tag( $kyiv_tag, $posts[1] ) || ! has_tag( $lviv_tag, $posts[2] ) ) {
		throw new RuntimeException( 'City sync must create tags and migrate legacy city relations.' );
	}

	$local = new WP_REST_Request( 'GET', '/logika/v1/media' );
	$local->set_param( 'city', $cities[0] );
	$local->set_param( 'all', true );
	$local_titles = array_column( rest_do_request( $local )->get_data(), 'title' );
	if ( array_intersect( array( get_the_title( $posts[2] ) ), $local_titles ) || array_diff( array( get_the_title( $posts[0] ), get_the_title( $posts[1] ), get_the_title( $posts[3] ) ), $local_titles ) ) {
		throw new RuntimeException( 'A selected city must receive its tagged and common posts, never another city.' );
	}

	$general_titles = array_column( rest_do_request( new WP_REST_Request( 'GET', '/logika/v1/media' ) )->get_data(), 'title' );
	if ( array_intersect( array( get_the_title( $posts[1] ), get_the_title( $posts[2] ), get_the_title( $posts[3] ) ), $general_titles ) || ! in_array( get_the_title( $posts[0] ), $general_titles, true ) ) {
		throw new RuntimeException( 'Without a city only common posts may be visible.' );
	}

	if ( ! $page ) {
		throw new RuntimeException( 'Homepage is required to test server city visibility.' );
	}
	update_post_meta( $page, 'home_media_posts', $posts );
	unset( $_COOKIE['logika_city'] );
	ob_start();
	logika_theme_render_source_page( 'index' );
	$general_markup = (string) ob_get_clean();
	$_COOKIE['logika_city'] = (string) $cities[0];
	ob_start();
	logika_theme_render_source_page( 'index' );
	$local_markup = (string) ob_get_clean();
	if ( str_contains( $general_markup, get_the_title( $posts[1] ) ) || str_contains( $general_markup, get_the_title( $posts[2] ) ) || str_contains( $general_markup, get_the_title( $posts[3] ) ) || ! str_contains( $local_markup, get_the_title( $posts[1] ) ) || ! str_contains( $local_markup, get_the_title( $posts[3] ) ) || str_contains( $local_markup, get_the_title( $posts[2] ) ) ) {
		throw new RuntimeException( 'Server homepage cards must honour the selected-city cookie.' );
	}
	if ( strpos( $local_markup, get_the_title( $posts[3] ) ) > strpos( $local_markup, get_the_title( $posts[1] ) ) ) {
		throw new RuntimeException( 'Server homepage cards must show newest selected-city articles first.' );
	}

	$related_before = get_post_meta( $posts[0], 'article_related_posts', false );
	update_post_meta( $posts[0], 'article_related_posts', array( $posts[1], $posts[2], $posts[3] ) );
	$article_markup = Logika_Theme_Article_Page::render( $posts[0] );
	if ( ! str_contains( $article_markup, get_the_title( $posts[1] ) ) || ! str_contains( $article_markup, get_the_title( $posts[3] ) ) || str_contains( $article_markup, get_the_title( $posts[2] ) ) ) {
		throw new RuntimeException( 'Related articles must honour the selected city.' );
	}
} finally {
	if ( $posts ) {
		delete_post_meta( $posts[0], 'article_related_posts' );
		foreach ( $related_before as $value ) {
			add_post_meta( $posts[0], 'article_related_posts', $value );
		}
	}
	if ( $page ) {
		delete_post_meta( $page, 'home_media_posts' );
		foreach ( $before as $value ) {
			add_post_meta( $page, 'home_media_posts', $value );
		}
	}
	if ( null === $cookie ) {
		unset( $_COOKIE['logika_city'] );
	} else {
		$_COOKIE['logika_city'] = $cookie;
	}
	foreach ( $posts as $post_id ) {
		wp_delete_post( $post_id, true );
	}
	foreach ( $cities as $city_id ) {
		$tag_id = term_exists( \Logika\Core\CitySlug::for( $city_id ), 'post_tag' );
		if ( $tag_id ) {
			wp_delete_term( (int) ( is_array( $tag_id ) ? $tag_id['term_id'] : $tag_id ), 'post_tag' );
		}
		wp_delete_post( $city_id, true );
	}
}

echo "City tags filter API and server-rendered publications.\n";
