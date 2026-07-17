<?php

declare(strict_types=1);

require dirname( __DIR__ ) . '/wordpress/wp-load.php';

$fixtures = array();
$front_id = (int) get_option( 'page_on_front' );
$original = get_field( 'home_programming_title', $front_id );
$errors   = array();

try {
	$city      = wp_insert_post( array( 'post_type' => 'city', 'post_status' => 'publish', 'post_title' => 'City Home fixture' ) );
	$review    = wp_insert_post( array( 'post_type' => 'review', 'post_status' => 'publish', 'post_title' => 'City review fixture' ) );
	$hidden    = wp_insert_post( array( 'post_type' => 'review', 'post_status' => 'publish', 'post_title' => 'Hidden review fixture' ) );
	$faq       = wp_insert_post( array( 'post_type' => 'faq_item', 'post_status' => 'publish', 'post_title' => 'City FAQ fixture' ) );
	$inactive  = wp_insert_post( array( 'post_type' => 'faq_item', 'post_status' => 'publish', 'post_title' => 'Inactive FAQ fixture' ) );
	$fixtures  = array( $city, $review, $hidden, $faq, $inactive );

	update_field( 'home_programming_title', 'Home fallback fixture', $front_id );
	update_field( 'city_home_hero_title', 'City override fixture', $city );
	update_field( 'review_author_name', 'Selected city review', $review );
	update_field( 'review_text', 'Visible city review fixture', $review );
	update_field( 'review_is_approved', 1, $review );
	update_field( 'review_author_name', 'Rejected city review', $hidden );
	update_field( 'review_text', 'Hidden city review fixture', $hidden );
	update_field( 'review_is_approved', 0, $hidden );
	update_field( 'faq_question', 'Selected city FAQ fixture?', $faq );
	update_field( 'faq_answer', 'Visible city answer fixture', $faq );
	update_field( 'faq_is_active', 1, $faq );
	update_field( 'faq_question', 'Inactive city FAQ fixture?', $inactive );
	update_field( 'faq_answer', 'Hidden city answer fixture', $inactive );
	update_field( 'faq_is_active', 0, $inactive );
	update_field( 'city_related_reviews', array( $review, $hidden ), $city );
	update_field( 'city_related_faq', array( $faq, $inactive ), $city );

	ob_start();
	Logika_Theme_City_Page::renderHome( $city );
	$html = (string) ob_get_clean();

	foreach ( array( 'City override fixture', 'Home fallback fixture', 'Visible city review fixture', 'Selected city FAQ fixture?', 'Visible city answer fixture' ) as $expected ) {
		if ( ! str_contains( $html, $expected ) ) {
			$errors[] = "City Home is missing {$expected}.";
		}
	}
	foreach ( array( 'Hidden city review fixture', 'Inactive city FAQ fixture?', 'Hidden city answer fixture' ) as $unexpected ) {
		if ( str_contains( $html, $unexpected ) ) {
			$errors[] = "City Home exposes {$unexpected}.";
		}
	}
} finally {
	update_field( 'home_programming_title', $original, $front_id );
	foreach ( $fixtures as $fixture ) {
		wp_delete_post( (int) $fixture, true );
	}
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "City Home applies local overrides with Home fallback and public entity gates.\n";
