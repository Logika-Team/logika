<?php

declare(strict_types=1);

require dirname( __DIR__ ) . '/wordpress/wp-load.php';

$errors = array();
$cases  = array(
	'it-courses' => array( 'source' => 'it-courses', 'field' => 'it_courses_catalog_cards', 'subfield' => 'image' ),
	'faq' => array( 'source' => 'faq', 'field' => 'faq_page_hero_icon' ),
	'media-center' => array( 'source' => 'media-center', 'field' => 'media_center_benefits', 'subfield' => 'image' ),
);

foreach ( $cases as $slug => $case ) {
	$page = get_page_by_path( $slug );
	$value = $page ? get_field( $case['field'], $page->ID ) : null;
	$image = isset( $case['subfield'] ) ? (int) ( $value[0][ $case['subfield'] ] ?? 0 ) : (int) $value;
	$url = $image ? wp_get_attachment_image_url( $image, 'large' ) : false;
	ob_start();
	logika_theme_render_source_page( $case['source'] );
	$html = (string) ob_get_clean();
	if ( ! $url || ! str_contains( $html, $url ) ) {
		$errors[] = "{$slug} does not render {$case['field']}.";
	}
}

$camp = get_posts( array( 'post_type' => 'camp', 'post_status' => 'publish', 'posts_per_page' => 1 ) )[0] ?? null;
$hero = $camp ? array_map( 'absint', (array) get_field( 'camp_hero_images', $camp->ID ) ) : array();
$url  = $hero ? wp_get_attachment_image_url( $hero[0], 'large' ) : false;
if ( $camp ) {
	ob_start();
	Logika_Theme_Source_Markup::renderPage( 'camp', $camp->ID );
	$html = (string) ob_get_clean();
	if ( ! $url || ! str_contains( $html, $url ) ) {
		$errors[] = 'Camp hero gallery does not render its ACF images.';
	}
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "New image section fields render without changing source structure.\n";
