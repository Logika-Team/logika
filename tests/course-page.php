<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$course = get_page_by_path( 'test-course', OBJECT, 'course' );
$course_id = $course ? (int) $course->ID : (int) wp_insert_post( array( 'post_type' => 'course', 'post_name' => 'test-course', 'post_title' => 'Тестовий курс', 'post_status' => 'publish' ) );

update_field( 'course_age_min', 9, $course_id );
update_field( 'course_age_max', 12, $course_id );
update_field( 'course_short_description', 'Опис із ACF для сторінки курсу.', $course_id );
update_field( 'course_program', array( array( 'title' => 'Перший модуль', 'description' => '<p>Опис модуля з ACF.</p>', 'items' => array( array( 'item_text' => 'Навичка з програми' ) ) ) ), $course_id );
$format = term_exists( 'Онлайн', 'learning_format' );
$format_id = is_array( $format ) ? $format['term_id'] : (int) $format;
if ( ! $format_id ) {
	$format = wp_insert_term( 'Онлайн', 'learning_format' );
	$format_id = is_wp_error( $format ) ? 0 : $format['term_id'];
}
wp_set_object_terms( $course_id, array( (int) $format_id ), 'learning_format' );
$faq = get_page_by_path( 'test-course-faq', OBJECT, 'faq_item' );
$faq_id = $faq ? (int) $faq->ID : (int) wp_insert_post( array( 'post_type' => 'faq_item', 'post_name' => 'test-course-faq', 'post_title' => 'FAQ курсу', 'post_status' => 'publish' ) );
update_field( 'faq_question', 'Чи є FAQ для курсу?', $faq_id );
update_field( 'faq_answer', '<p>Так, це відповідь курсу.</p>', $faq_id );
update_field( 'faq_related_course', $course_id, $faq_id );
update_field( 'faq_is_active', 1, $faq_id );

$output = Logika_Theme_Course_Page::render( $course_id );

if ( ! str_contains( $output, '9-12 років' ) || ! str_contains( $output, 'Онлайн' ) || ! str_contains( $output, 'Опис із ACF для сторінки курсу.' ) || ! str_contains( $output, 'Перший модуль' ) || ! str_contains( $output, 'Навичка з програми' ) || ! str_contains( $output, 'Чи є FAQ для курсу?' ) ) {
	fwrite( STDERR, "Course page does not render ACF fields.\n" );
	exit( 1 );
}

echo "Course page uses WordPress content.\n";
