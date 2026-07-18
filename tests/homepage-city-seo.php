<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

function logika_homepage_city_seo_test_image( string $name, int $parent_id ): int {
	$upload = wp_upload_dir();
	$file   = trailingslashit( $upload['path'] ) . $name;
	file_put_contents( $file, base64_decode( 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMB/ax3p1sAAAAASUVORK5CYII=' ) );
	$id = wp_insert_attachment( array( 'post_title' => $name, 'post_mime_type' => 'image/png', 'post_status' => 'inherit' ), $file, $parent_id );
	wp_update_attachment_metadata( (int) $id, wp_generate_attachment_metadata( (int) $id, $file ) );
	update_post_meta( (int) $id, '_wp_attachment_image_alt', 'Тестовий alt-текст' );

	return (int) $id;
}

$city_id  = (int) wp_insert_post( array( 'post_type' => 'city', 'post_name' => 'homepage-seo-city', 'post_title' => 'SEO тестове місто', 'post_status' => 'publish' ) );
$draft_id = (int) wp_insert_post( array( 'post_type' => 'city', 'post_name' => 'homepage-seo-draft', 'post_title' => 'Чернетка SEO міста', 'post_status' => 'draft' ) );
$images   = array(
	'illustration' => logika_homepage_city_seo_test_image( 'homepage-city-seo-illustration.png', $city_id ),
	'poster'       => logika_homepage_city_seo_test_image( 'homepage-city-seo-poster.png', $city_id ),
);

register_shutdown_function(
	static function () use ( $city_id, $draft_id, $images ): void {
		foreach ( $images as $image_id ) {
			wp_delete_attachment( $image_id, true );
		}
		wp_delete_post( $city_id, true );
		wp_delete_post( $draft_id, true );
	}
);

update_field( 'city_home_seo_title', 'Курси для SEO тестового міста', $city_id );
update_field( 'city_home_seo_description', "Перший абзац.\n\nДругий абзац.", $city_id );
update_field( 'city_home_seo_cta_label', 'Записатися', $city_id );
update_field( 'city_home_seo_illustration', $images['illustration'], $city_id );
update_field( 'city_home_seo_video_poster', $images['poster'], $city_id );
update_field( 'city_home_seo_video_caption', 'Відео про місто', $city_id );
update_field( 'city_home_seo_video_url', 'https://www.youtube.com/watch?v=test', $city_id );

$response = rest_do_request( new WP_REST_Request( 'GET', '/logika/v1/cities/' . $city_id . '/homepage-seo' ) );
$data     = $response->get_data();
$errors   = array();

if ( 200 !== $response->get_status() || 'Курси для SEO тестового міста' !== ( $data['title'] ?? '' ) || "Перший абзац.\n\nДругий абзац." !== ( $data['description'] ?? '' ) || 'https://www.youtube.com/watch?v=test' !== ( $data['video']['url'] ?? '' ) || empty( $data['illustration']['url'] ) || empty( $data['video']['poster']['url'] ) ) {
	$errors[] = 'Published city does not expose a complete homepage SEO section.';
}

delete_field( 'city_home_seo_video_url', $city_id );
$partial = rest_do_request( new WP_REST_Request( 'GET', '/logika/v1/cities/' . $city_id . '/homepage-seo' ) );
if ( 200 !== $partial->get_status() || 'https://www.youtube.com/watch?v=7QN3QcMHMQ4' !== ( $partial->get_data()['video']['url'] ?? '' ) ) {
	$errors[] = 'Homepage SEO content does not use the default video URL.';
}

$draft = rest_do_request( new WP_REST_Request( 'GET', '/logika/v1/cities/' . $draft_id . '/homepage-seo' ) );
if ( 404 !== $draft->get_status() ) {
	$errors[] = 'Draft city homepage SEO content is publicly accessible.';
}

$source = (string) file_get_contents( dirname(__DIR__) . '/wordpress/wp-content/themes/logika-theme/source-pages/index.php' );
if ( ! str_contains( $source, 'data-city-home-seo' ) || ! str_contains( $source, 'hidden' ) || str_contains( $source, 'Курси програмування<br>для дітей у Ніжині' ) || ! str_contains( $source, 'href="#lead-form"' ) ) {
	$errors[] = 'Homepage SEO section is not a hidden dynamic lead-form shell.';
}

$script = (string) file_get_contents( dirname(__DIR__) . '/wordpress/wp-content/themes/logika-theme/assets/js/homepage-city-seo.js' );
if ( ! str_contains( $source, 'data-city-home-seo-video-frame' ) || ! str_contains( $source, 'data-city-home-seo-video-play' ) || ! str_contains( $source, 'allowfullscreen' ) || ! str_contains( $script, 'youtube-nocookie.com/embed/' ) ) {
	$errors[] = 'Homepage SEO video does not provide a click-to-play YouTube embed.';
}

$styles = (string) file_get_contents( dirname(__DIR__) . '/source/scss/blocks/sections/nizhyn-school.scss' );
if ( ! str_contains( $styles, "&__video::before {\n    position: absolute;\n    z-index: 1;\n    pointer-events: none;" ) ) {
	$errors[] = 'Homepage SEO video decoration can intercept player controls.';
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Homepage city SEO API and markup handle complete city content safely.\n";
