<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$pages = array(
	'about'           => array( 'field' => 'about_benefits_title', 'value' => 'About ACF benefits title' ),
	'it-courses'      => array( 'field' => 'it_courses_reviews_title', 'value' => 'IT ACF reviews title' ),
	'english-courses' => array( 'field' => 'english_courses_test_text', 'value' => 'English ACF test text' ),
	'faq'             => array( 'field' => 'faq_page_cta_title', 'value' => 'FAQ ACF CTA title' ),
	'media-center'    => array( 'field' => 'media_center_discount_title', 'value' => 'Media ACF discount title' ),
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

$about_page = get_page_by_path( 'about' );
$about_rows = $about_page ? (array) get_field( 'about_page_texts', $about_page->ID ) : array();
if ( ! $about_page || ! $about_rows ) {
	$errors[] = 'About page text fixture is unavailable.';
} else {
	$original_rows = $about_rows;
	$about_rows[0]['value'] = 'About ACF additional text';
	update_field( 'about_page_texts', $about_rows, $about_page->ID );
	ob_start();
	logika_theme_render_source_page( 'about' );
	$markup = (string) ob_get_clean();
	if ( ! str_contains( $markup, 'About ACF additional text' ) ) {
		$errors[] = 'About does not render an additional page text.';
	}
	update_field( 'about_page_texts', $original_rows, $about_page->ID );
}

$faq_page = get_page_by_path( 'faq' );
$faq_item = get_posts( array( 'post_type' => 'faq_item', 'post_status' => 'publish', 'numberposts' => 1 ) )[0] ?? null;
if ( ! $faq_page || ! $faq_item ) {
	$errors[] = 'FAQ selection fixture is unavailable.';
} else {
	$original = get_field( 'faq_page_featured_faq', $faq_page->ID );
	$original_active = get_field( 'faq_is_active', $faq_item->ID );
	update_field( 'faq_page_featured_faq', array( $faq_item->ID ), $faq_page->ID );
	update_field( 'faq_is_active', 0, $faq_item->ID );
	ob_start();
	logika_theme_render_source_page( 'faq' );
	$markup = (string) ob_get_clean();
	if ( ! str_contains( $markup, (string) get_field( 'faq_question', $faq_item->ID ) ) ) {
		$errors[] = 'FAQ page does not render its selected FAQ entity.';
	}
	update_field( 'faq_page_featured_faq', $original, $faq_page->ID );
	update_field( 'faq_is_active', $original_active, $faq_item->ID );
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Marketing pages render their page-specific ACF fields.\n";
