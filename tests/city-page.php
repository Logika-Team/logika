<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$city = get_page_by_path( 'test-city', OBJECT, 'city' );
$city_id = $city ? (int) $city->ID : (int) wp_insert_post( array( 'post_type' => 'city', 'post_name' => 'test-city', 'post_title' => 'Тестове місто', 'post_status' => 'publish' ) );
$branch = get_page_by_path( 'test-branch', OBJECT, 'branch' );
$branch_id = $branch ? (int) $branch->ID : (int) wp_insert_post( array( 'post_type' => 'branch', 'post_name' => 'test-branch', 'post_title' => 'Тестова філія', 'post_status' => 'publish' ) );

update_field( 'city_intro', 'Вступ із ACF для міської сторінки.', $city_id );
update_field( 'city_seo_text', '<p>SEO-текст із ACF для міста.</p>', $city_id );
update_field( 'branch_city_id', $city_id, $branch_id );
update_field( 'branch_address', 'вул. Тестова, 10', $branch_id );
update_field( 'branch_is_active', 1, $branch_id );

$course = get_page_by_path( 'test-city-course', OBJECT, 'course' );
$camp   = get_page_by_path( 'test-city-camp', OBJECT, 'camp' );
$course_id = $course ? (int) $course->ID : (int) wp_insert_post( array( 'post_type' => 'course', 'post_name' => 'test-city-course', 'post_title' => 'Курс для тестового міста', 'post_status' => 'publish' ) );
$camp_id   = $camp ? (int) $camp->ID : (int) wp_insert_post( array( 'post_type' => 'camp', 'post_name' => 'test-city-camp', 'post_title' => 'Табір для тестового міста', 'post_status' => 'publish' ) );
update_field( 'city_related_courses', array( $course_id ), $city_id );
update_field( 'city_related_camps', array( $camp_id ), $city_id );

$review = get_page_by_path( 'test-city-review', OBJECT, 'review' );
$faq    = get_page_by_path( 'test-city-faq', OBJECT, 'faq_item' );
$review_id = $review ? (int) $review->ID : (int) wp_insert_post( array( 'post_type' => 'review', 'post_name' => 'test-city-review', 'post_title' => 'Відгук для тестового міста', 'post_status' => 'publish' ) );
$faq_id    = $faq ? (int) $faq->ID : (int) wp_insert_post( array( 'post_type' => 'faq_item', 'post_name' => 'test-city-faq', 'post_title' => 'FAQ для тестового міста', 'post_status' => 'publish' ) );
update_field( 'review_author_name', 'Олена', $review_id );
update_field( 'review_text', '<p>Відгук із ACF для міста.</p>', $review_id );
update_field( 'review_is_approved', 1, $review_id );
update_field( 'faq_question', 'Чи є FAQ для міста?', $faq_id );
update_field( 'faq_answer', '<p>Так, це відповідь із ACF.</p>', $faq_id );
update_field( 'faq_is_active', 1, $faq_id );
update_field( 'city_related_reviews', array( $review_id ), $city_id );
update_field( 'city_related_faq', array( $faq_id ), $city_id );

$output = Logika_Theme_City_Page::render_branches( $city_id );

if ( ! str_contains( $output, 'Тестова філія' ) || ! str_contains( $output, 'вул. Тестова, 10' ) ) {
	fwrite( STDERR, "City branches are not rendered from ACF relations.\n" );
	exit( 1 );
}

$related = Logika_Theme_City_Page::render_related( $city_id, 'city_related_courses', 'Курси у місті' );

if ( ! str_contains( $related, 'Курс для тестового міста' ) ) {
	fwrite( STDERR, "City courses are not rendered from ACF relations.\n" );
	exit( 1 );
}

if ( ! str_contains( Logika_Theme_City_Page::render_reviews( $city_id ), 'Відгук із ACF для міста.' ) || ! str_contains( Logika_Theme_City_Page::render_faq( $city_id ), 'Чи є FAQ для міста?' ) ) {
	fwrite( STDERR, "City reviews or FAQ are not rendered from ACF relations.\n" );
	exit( 1 );
}

echo "City branches use WordPress content.\n";
