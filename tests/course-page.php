<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$course = get_page_by_path( 'test-course', OBJECT, 'course' );
$course_id = $course ? (int) $course->ID : (int) wp_insert_post( array( 'post_type' => 'course', 'post_name' => 'test-course', 'post_title' => 'Тестовий курс', 'post_status' => 'publish' ) );

update_field( 'course_age_min', 9, $course_id );
update_field( 'course_age_max', 12, $course_id );
update_field( 'course_short_description', 'Опис із ACF для сторінки курсу.', $course_id );
update_field( 'course_hero_text', 'Перший текст hero із ACF.', $course_id );
update_field( 'course_hero_additional_text', 'Другий текст hero із ACF.', $course_id );
update_field( 'course_hero_cta_label', 'Записатися на курс', $course_id );
update_field( 'course_program_anchor_label', 'До програми', $course_id );
update_field( 'course_process_title', 'Процес із ACF', $course_id );
update_field( 'course_process_items', array( array( 'title' => 'Практика', 'text' => 'Етап із ACF.', 'cta_label' => 'Обрати курс' ) ), $course_id );
update_field( 'course_portfolio_title', 'Портфоліо із ACF', $course_id );
update_field( 'course_projects', array( array( 'project_title' => 'Проєкт учня', 'project_description' => 'Опис проєкту.' ) ), $course_id );
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
update_field( 'course_related_faq', array( $faq_id ), $course_id );

ob_start();
Logika_Theme_Source_Markup::renderPage( 'it-course', $course_id );
$output = (string) ob_get_clean();

if ( ! str_contains( $output, 'Перший текст hero із ACF.' ) || ! str_contains( $output, 'Другий текст hero із ACF.' ) || ! str_contains( $output, 'Обрати курс' ) || ! str_contains( $output, 'Проєкт учня' ) || ! str_contains( $output, 'Перший модуль' ) || ! str_contains( $output, 'Чи є FAQ для курсу?' ) || ! str_contains( $output, 'data-logika-course-id="' . $course_id . '"' ) ) {
	fwrite( STDERR, "Course source markup does not render ACF content and context.\n" );
	exit( 1 );
}

foreach ( array( 'course_learn_items', 'course_process_items', 'course_projects', 'course_program', 'course_related_faq' ) as $field ) {
	update_field( $field, array(), $course_id );
}

ob_start();
Logika_Theme_Source_Markup::renderPage( 'it-course', $course_id );
$empty_output = (string) ob_get_clean();

if ( str_contains( $empty_output, 'learn-section' ) || str_contains( $empty_output, 'process-section' ) || str_contains( $empty_output, 'portfolio-section' ) || substr_count( $empty_output, 'faq-section' ) > 1 ) {
	fwrite( STDERR, "Course source markup keeps empty optional sections.\n" );
	exit( 1 );
}

echo "Course source markup uses WordPress content.\n";
