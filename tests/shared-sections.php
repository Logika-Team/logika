<?php

declare(strict_types=1);

require dirname( __DIR__ ) . '/wordpress/wp-load.php';

function render_shared_part( string $name, array $args ): string {
	ob_start();
	get_template_part( "template-parts/sections/{$name}", null, $args );
	return (string) ob_get_clean();
}

$posts  = array();
$errors = array();
$images = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'posts_per_page' => 2, 'fields' => 'ids' ) );

try {
	$course = wp_insert_post( array( 'post_type' => 'course', 'post_status' => 'publish', 'post_title' => 'ACF Public Course' ) );
	$draft  = wp_insert_post( array( 'post_type' => 'course', 'post_status' => 'draft', 'post_title' => 'ACF Draft Course' ) );
	$faq    = wp_insert_post( array( 'post_type' => 'faq_item', 'post_status' => 'publish', 'post_title' => 'ACF FAQ' ) );
	$review = wp_insert_post( array( 'post_type' => 'review', 'post_status' => 'publish', 'post_title' => 'ACF Approved Review' ) );
	$hidden = wp_insert_post( array( 'post_type' => 'review', 'post_status' => 'publish', 'post_title' => 'ACF Hidden Review' ) );
	$posts  = array( $course, $draft, $faq, $review, $hidden );
	update_field( 'course_short_description', 'Опис тестового курсу', $course );
	update_field( 'faq_question', 'Активне питання ACF?', $faq );
	update_field( 'faq_answer', '<p>Активна відповідь.</p>', $faq );
	update_field( 'faq_is_active', 1, $faq );
	update_field( 'review_author_name', 'Схвалений автор', $review );
	update_field( 'review_text', 'Схвалений текст', $review );
	update_field( 'review_is_approved', 1, $review );
	update_field( 'review_author_name', 'Прихований автор', $hidden );
	update_field( 'review_is_approved', 0, $hidden );

	$checks = array(
		array( render_shared_part( 'course-selection', array( 'title' => 'Курси ACF', 'items' => array( $draft, $course ) ) ), array( 'Курси ACF', 'ACF Public Course', 'Опис тестового курсу' ), array( 'ACF Draft Course' ) ),
		array( render_shared_part( 'faq', array( 'section_title' => 'FAQ ACF', 'items' => array( $faq ) ) ), array( 'FAQ ACF', 'Активне питання ACF?', 'Активна відповідь.' ), array() ),
		array( render_shared_part( 'reviews', array( 'title' => 'Відгуки ACF', 'items' => array( $hidden, $review ) ) ), array( 'Відгуки ACF', 'Схвалений автор', 'Схвалений текст' ), array( 'Прихований автор' ) ),
		array( render_shared_part( 'cta', array( 'title' => 'CTA ACF', 'subtitle' => 'Підзаголовок CTA' ) ), array( 'CTA ACF', 'Підзаголовок CTA' ), array() ),
		array( render_shared_part( 'school-map', array( 'title' => 'Карта ACF', 'text' => 'Опис карти ACF' ) ), array( 'Карта ACF', 'Опис карти ACF', 'data-school-map' ), array() ),
		array( render_shared_part( 'partners', array( 'title' => 'Партнери ACF', 'items' => array( array( 'name' => 'Партнер', 'image' => $images[0] ?? 0, 'url' => 'https://example.com/partner' ) ) ) ), array( 'Партнери ACF', 'Партнер', 'https://example.com/partner' ), array() ),
		array( render_shared_part( 'gallery', array( 'title' => 'Галерея ACF', 'images' => $images ) ), array( 'Галерея ACF' ), array() ),
		array( render_shared_part( 'certificates', array( 'title' => 'Сертифікати ACF', 'images' => $images ) ), array( 'Сертифікати ACF' ), array() ),
	);
	foreach ( $checks as $check ) {
		list( $html, $present, $absent ) = $check;
		foreach ( $present as $text ) {
			if ( ! str_contains( $html, $text ) ) {
				$errors[] = "Shared section is missing {$text}.";
			}
		}
		foreach ( $absent as $text ) {
			if ( str_contains( $html, $text ) ) {
				$errors[] = "Shared section exposes {$text}.";
			}
		}
	}
	if ( count( $images ) > 1 ) {
		$gallery = $checks[6][0];
		if ( strpos( $gallery, (string) wp_get_attachment_image_url( (int) $images[0], 'large' ) ) > strpos( $gallery, (string) wp_get_attachment_image_url( (int) $images[1], 'large' ) ) ) {
			$errors[] = 'Gallery does not preserve ACF order.';
		}
	}
} finally {
	foreach ( $posts as $post_id ) {
		wp_delete_post( (int) $post_id, true );
	}
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Shared section template-parts render explicit ACF data.\n";
