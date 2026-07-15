<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$pages = array(
	'about'           => array( 'field' => 'about_hero_title', 'value' => 'About ACF test title' ),
	'it-courses'      => array( 'field' => 'it_courses_hero_title', 'value' => 'IT ACF test title' ),
	'english-courses' => array( 'field' => 'english_courses_hero_title', 'value' => 'English ACF test title' ),
	'faq'             => array( 'field' => 'faq_page_hero_title', 'value' => 'FAQ ACF test title' ),
	'media-center'    => array( 'field' => 'media_center_hero_title', 'value' => 'Media ACF test title' ),
);
$errors = array();

foreach ( $pages as $slug => $expectation ) {
	$page = get_page_by_path( $slug );

	if ( ! $page ) {
		$errors[] = "Missing {$slug} page.";
		continue;
	}

	$original = get_field( $expectation['field'], $page->ID );
	update_field( $expectation['field'], $expectation['value'], $page->ID );

	ob_start();
	logika_theme_render_source_page( 'english-courses' === $slug ? 'en-courses' : $slug );
	$markup = (string) ob_get_clean();

	if ( ! str_contains( $markup, $expectation['value'] ) ) {
		$errors[] = "{$slug} does not render {$expectation['field']}.";
	}

	if ( null === $original || false === $original || '' === $original ) {
		delete_post_meta( $page->ID, $expectation['field'] );
		delete_post_meta( $page->ID, '_' . $expectation['field'] );
	} else {
		update_field( $expectation['field'], $original, $page->ID );
	}
}

$faq_page = get_page_by_path( 'faq' );
$faq_item = get_posts( array( 'post_type' => 'faq_item', 'post_status' => 'publish', 'numberposts' => 1 ) )[0] ?? null;
if ( ! $faq_page || ! $faq_item ) {
	$errors[] = 'FAQ selection fixture is unavailable.';
} else {
	$original = get_field( 'faq_page_featured_faq', $faq_page->ID );
	update_field( 'faq_page_featured_faq', array( $faq_item->ID ), $faq_page->ID );
	ob_start();
	logika_theme_render_source_page( 'faq' );
	$markup = (string) ob_get_clean();
	if ( ! str_contains( $markup, (string) get_field( 'faq_question', $faq_item->ID ) ) ) {
		$errors[] = 'FAQ page does not render its selected FAQ entity.';
	}
	update_field( 'faq_page_featured_faq', $original, $faq_page->ID );
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Marketing pages render their page-specific ACF fields.\n";
