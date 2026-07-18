<?php

declare(strict_types=1);

require dirname( __DIR__ ) . '/wordpress/wp-load.php';

$city          = 0;
$fallback_city = 0;
$errors        = array();

try {
	$fallback_city = (int) wp_insert_post( array( 'post_type' => 'city', 'post_status' => 'publish', 'post_title' => 'Ірпінь' ) );
	ob_start();
	Logika_Theme_City_Page::renderHome( $fallback_city );
	$fallback_html = (string) ob_get_clean();
	if ( ! str_contains( $fallback_html, 'Програмування та англійська мова для дітей в м. Ірпінь' ) || ! str_contains( $fallback_html, 'Найбільша офлайн школа в місті Ірпінь. Доступний онлайн формат' ) ) {
		$errors[] = 'City homepage must use the resolved fallback hero.';
	}

	$city = (int) wp_insert_post( array( 'post_type' => 'city', 'post_status' => 'publish', 'post_title' => 'Ірпінь' ) );
	update_field( 'city_home_hero_title', 'ACF-заголовок Ірпеня', $city );
	update_field( 'city_home_hero_text', 'ACF-підзаголовок Ірпеня', $city );

	$cities  = rest_do_request( new WP_REST_Request( 'GET', '/logika/v1/cities' ) )->get_data();
	$payload = current( array_filter( $cities, static fn( array $item ): bool => $city === (int) $item['id'] ) );
	if ( array( 'title' => 'ACF-заголовок Ірпеня', 'text' => 'ACF-підзаголовок Ірпеня' ) !== ( $payload['hero'] ?? null ) ) {
		$errors[] = 'City API must expose the editor hero override.';
	}

	$cities  = rest_do_request( new WP_REST_Request( 'GET', '/logika/v1/cities' ) )->get_data();
	$payload = current( array_filter( $cities, static fn( array $item ): bool => $fallback_city === (int) $item['id'] ) );
	$hero    = $payload['hero'] ?? array();
	if ( 'Програмування та англійська мова для дітей в м. Ірпінь' !== ( $hero['title'] ?? '' ) || 'Найбільша офлайн школа в місті Ірпінь. Доступний онлайн формат' !== ( $hero['text'] ?? '' ) ) {
		$errors[] = 'City API must provide the resolved fallback hero.';
	}
} finally {
	foreach ( array( $city, $fallback_city ) as $fixture ) {
		if ( $fixture ) {
			wp_delete_post( $fixture, true );
		}
	}
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "City hero uses ACF overrides and a shared fallback.\n";
