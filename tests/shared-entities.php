<?php

declare(strict_types=1);

require dirname( __DIR__ ) . '/wordpress/wp-load.php';

$ids = array();

try {
	$course_public = wp_insert_post( array( 'post_type' => 'course', 'post_status' => 'publish', 'post_title' => 'Public course' ) );
	$course_draft  = wp_insert_post( array( 'post_type' => 'course', 'post_status' => 'draft', 'post_title' => 'Draft course' ) );
	$faq_active    = wp_insert_post( array( 'post_type' => 'faq_item', 'post_status' => 'publish', 'post_title' => 'Active FAQ' ) );
	$faq_inactive  = wp_insert_post( array( 'post_type' => 'faq_item', 'post_status' => 'publish', 'post_title' => 'Inactive FAQ' ) );
	$review_ok     = wp_insert_post( array( 'post_type' => 'review', 'post_status' => 'publish', 'post_title' => 'Approved review' ) );
	$review_hidden = wp_insert_post( array( 'post_type' => 'review', 'post_status' => 'publish', 'post_title' => 'Hidden review' ) );
	$ids           = array( $course_public, $course_draft, $faq_active, $faq_inactive, $review_ok, $review_hidden );
	update_field( 'field_faq_is_active', 1, $faq_active );
	update_field( 'field_faq_is_active', 0, $faq_inactive );
	update_field( 'field_review_is_approved', 1, $review_ok );
	update_field( 'field_review_is_approved', 0, $review_hidden );

	$checks = array(
		array( Logika_Theme_Entities::courses( array( $course_draft, $course_public ) ), array( $course_public ), 'Courses expose published entities only.' ),
		array( Logika_Theme_Entities::faqs( array( $faq_inactive, $faq_active ) ), array( $faq_active ), 'FAQ exposes active entities only.' ),
		array( Logika_Theme_Entities::reviews( array( $review_hidden, $review_ok ) ), array( $review_ok ), 'Reviews expose approved entities only.' ),
	);
	foreach ( $checks as $check ) {
		list( $actual, $expected, $message ) = $check;
		if ( $actual !== $expected ) {
			throw new RuntimeException( $message );
		}
	}
} finally {
	foreach ( $ids as $id ) {
		wp_delete_post( (int) $id, true );
	}
}

echo "Shared entities filter public content correctly.\n";
