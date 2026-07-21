<?php

declare(strict_types=1);

require dirname( __DIR__ ) . '/wordpress/wp-load.php';

$errors = array();
$posts  = array();

$it_page      = get_page_by_path( 'it-courses' );
$english_page = get_page_by_path( 'english-courses' );
if ( ! $it_page || ! $english_page ) {
	fwrite( STDERR, "The /it-courses/ or /english-courses/ page fixture does not exist; cannot test the catalog sync.\n" );
	exit( 1 );
}

$before_it      = (array) get_field( 'it_courses_age_categories', $it_page->ID );
$before_english = (array) get_field( 'english_courses_featured_courses', $english_page->ID );

try {
	$programming = wp_insert_post( array( 'post_type' => 'course', 'post_status' => 'publish', 'post_title' => 'Курс програмування fixture' ), true );
	$english      = wp_insert_post( array( 'post_type' => 'course', 'post_status' => 'publish', 'post_title' => 'Курс англійської fixture' ), true );
	$hidden       = wp_insert_post( array( 'post_type' => 'course', 'post_status' => 'publish', 'post_title' => 'Прихований курс fixture' ), true );
	$posts        = array( $programming, $english, $hidden );

	update_field( 'course_age_min', 9, $programming );
	update_field( 'course_age_max', 11, $programming );
	Logika\Core\CourseCatalogSync::sync( $programming );

	wp_set_object_terms( $english, 'english', 'course_direction' );
	Logika\Core\CourseCatalogSync::sync( $english );

	update_field( 'course_show_in_catalog', false, $hidden );
	Logika\Core\CourseCatalogSync::sync( $hidden );

	$it_ids = array();
	foreach ( (array) get_field( 'it_courses_age_categories', $it_page->ID ) as $row ) {
		$it_ids = array_merge( $it_ids, array_map( 'intval', (array) ( $row['courses'] ?? array() ) ) );
	}
	$english_ids = array_map( 'intval', (array) get_field( 'english_courses_featured_courses', $english_page->ID ) );

	if ( ! in_array( $programming, $it_ids, true ) ) {
		$errors[] = 'Publishing a 9-11 year old programming course did not add it to it_courses_age_categories.';
	}
	if ( in_array( $english, $it_ids, true ) ) {
		$errors[] = 'An English-direction course leaked into it_courses_age_categories.';
	}
	if ( ! in_array( $english, $english_ids, true ) ) {
		$errors[] = 'Publishing an English course did not add it to english_courses_featured_courses.';
	}
	if ( in_array( $hidden, $it_ids, true ) || in_array( $hidden, $english_ids, true ) ) {
		$errors[] = 'A course with course_show_in_catalog=false was still added to the catalog.';
	}

	wp_update_post( array( 'ID' => $programming, 'post_status' => 'draft' ) );
	Logika\Core\CourseCatalogSync::sync( $programming );
	$it_ids_after_draft = array();
	foreach ( (array) get_field( 'it_courses_age_categories', $it_page->ID ) as $row ) {
		$it_ids_after_draft = array_merge( $it_ids_after_draft, array_map( 'intval', (array) ( $row['courses'] ?? array() ) ) );
	}
	if ( in_array( $programming, $it_ids_after_draft, true ) ) {
		$errors[] = 'Unpublishing a course did not remove it from it_courses_age_categories.';
	}
} finally {
	foreach ( $posts as $post_id ) {
		if ( is_numeric( $post_id ) ) {
			wp_delete_post( (int) $post_id, true );
		}
	}
	update_field( 'it_courses_age_categories', $before_it, $it_page->ID );
	update_field( 'english_courses_featured_courses', $before_english, $english_page->ID );
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Publishing a course syncs it into the it-courses/english-courses catalog automatically.\n";
