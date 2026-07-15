<?php

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit( 1 );
}

function logika_marketing_asset( string $relative_path ): int {
	$existing = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'meta_key' => '_logika_theme_asset', 'meta_value' => $relative_path, 'fields' => 'ids', 'numberposts' => 1 ) );
	if ( $existing ) {
		return (int) $existing[0];
	}

	$source = get_stylesheet_directory() . '/assets/' . ltrim( $relative_path, '/' );
	if ( ! is_readable( $source ) ) {
		return 0;
	}

	$upload = wp_upload_bits( basename( $relative_path ), null, (string) file_get_contents( $source ) );
	if ( ! empty( $upload['error'] ) ) {
		return 0;
	}

	$type = wp_check_filetype( $upload['file'] );
	$id   = wp_insert_attachment( array( 'post_mime_type' => $type['type'], 'post_title' => pathinfo( $relative_path, PATHINFO_FILENAME ), 'post_status' => 'inherit' ), $upload['file'] );
	require_once ABSPATH . 'wp-admin/includes/image.php';
	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $upload['file'] ) );
	update_post_meta( $id, '_logika_theme_asset', $relative_path );

	return (int) $id;
}

function logika_marketing_page( string $slug ): int {
	$page = get_page_by_path( $slug );
	return $page ? (int) $page->ID : 0;
}

$directions = array( 'programming' => 'Програмування', 'english' => 'Англійська мова' );
foreach ( $directions as $slug => $name ) {
	if ( ! term_exists( $slug, 'course_direction' ) ) {
		wp_insert_term( $name, 'course_direction', array( 'slug' => $slug ) );
	}
}

$courses = array(
	array( 'programming-start', 'Перший крок у світ технологій', 'programming', 'Перші впевнені кроки у світі технологій.', 'img/services/service1.png' ),
	array( 'programming-projects', 'Від ігор до власних проєктів', 'programming', 'Створення власних проєктів і цифрових інструментів.', 'img/services/service2.png' ),
	array( 'programming-skills', 'Серйозні навички для серйозних цілей', 'programming', 'Практичне програмування та робота над реальними проєктами.', 'img/services/service3.png' ),
	array( 'programming-career', 'Перший крок у IT-кар’єру', 'programming', 'Практичні знання для подальшого розвитку в IT.', 'img/services/service4.png' ),
	array( 'english-a0', 'Рівень A0', 'english', 'Перші слова, фрази та знайомство з англійською.', 'img/english-courses/A0.svg' ),
	array( 'english-a1', 'Рівень A1', 'english', 'Базове спілкування та щоденні теми.', 'img/english-courses/A1.svg' ),
	array( 'english-a2', 'Рівень A2', 'english', 'Більше практики та словникового запасу.', 'img/english-courses/A2.svg' ),
	array( 'english-b1', 'Рівень B1', 'english', 'Вільніше спілкування та розуміння живої мови.', 'img/english-courses/B1.svg' ),
	array( 'english-b2', 'Рівень B2', 'english', 'Впевнене володіння мовою та спілкування.', 'img/english-courses/B2.svg' ),
);

$course_ids = array();
foreach ( $courses as [ $slug, $title, $direction, $description, $image ] ) {
	$course = get_page_by_path( $slug, OBJECT, 'course' );
	$id     = $course ? (int) $course->ID : wp_insert_post( array( 'post_type' => 'course', 'post_status' => 'publish', 'post_name' => $slug, 'post_title' => $title ) );
	wp_update_post( array( 'ID' => $id, 'post_title' => $title ) );
	wp_set_object_terms( $id, $direction, 'course_direction' );
	update_field( 'course_short_description', $description, $id );
	if ( $attachment = logika_marketing_asset( $image ) ) {
		update_field( 'course_card_image', $attachment, $id );
	}
	$course_ids[ $direction ][] = $id;
}

$faqs = array(
	'faq-trial-lesson' => array( 'Чи можна відвідати пробний урок?', 'Так, перший урок безкоштовний.' ),
	'faq-age' => array( 'Для якого віку підходять курси?', 'Курси розраховані на дітей і підлітків від 7 до 17 років.' ),
	'faq-format' => array( 'У якому форматі проходить навчання?', 'Доступні онлайн- та офлайн-заняття.' ),
);
$faq_ids = array();
foreach ( $faqs as $slug => [ $question, $answer ] ) {
	$faq = get_page_by_path( $slug, OBJECT, 'faq_item' );
	$id  = $faq ? (int) $faq->ID : wp_insert_post( array( 'post_type' => 'faq_item', 'post_status' => 'publish', 'post_name' => $slug, 'post_title' => $question ) );
	wp_update_post( array( 'ID' => $id, 'post_title' => $question ) );
	update_field( 'faq_question', $question, $id );
	update_field( 'faq_answer', '<p>' . esc_html( $answer ) . '</p>', $id );
	update_field( 'faq_is_active', 1, $id );
	$faq_ids[] = $id;
}

foreach ( array( 'it-courses' => 'programming', 'english-courses' => 'english' ) as $slug => $direction ) {
	if ( $page_id = logika_marketing_page( $slug ) ) {
		update_field( 'it-courses' === $slug ? 'it_courses_featured_courses' : 'english_courses_featured_courses', $course_ids[ $direction ], $page_id );
	}
}

foreach ( array( 'about' => 'about_featured_faq', 'faq' => 'faq_page_featured_faq', 'it-courses' => 'it_courses_featured_faq', 'english-courses' => 'english_courses_featured_faq' ) as $slug => $field ) {
	if ( $page_id = logika_marketing_page( $slug ) ) {
		update_field( $field, $faq_ids, $page_id );
	}
}

foreach ( array( 'about' => array( 'about_hero_image', 'img/about/hero-characters.png' ), 'it-courses' => array( 'it_courses_hero_image', 'img/boy-character.svg' ), 'english-courses' => array( 'english_courses_hero_image', 'img/en-courses/en-courses.svg' ), 'faq' => array( 'faq_page_hero_image', 'img/faq/faq-left-bg.svg' ), 'media-center' => array( 'media_center_hero_image', 'img/media-promo.svg' ) ) as $slug => [ $field, $asset ] ) {
	if ( $page_id = logika_marketing_page( $slug ) ) {
		if ( $attachment = logika_marketing_asset( $asset ) ) {
			update_field( $field, $attachment, $page_id );
		}
	}
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::success( 'Marketing page content is synchronized.' );
}

echo "Marketing page content is synchronized.\n";
