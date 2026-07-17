<?php

declare(strict_types=1);

require dirname( __DIR__ ) . '/wordpress/wp-load.php';

$fixtures = array();
$errors   = array();
$course   = wp_insert_post( array( 'post_type' => 'course', 'post_status' => 'publish', 'post_title' => 'Курс fixture' ) );
$faq      = wp_insert_post( array( 'post_type' => 'faq_item', 'post_status' => 'publish', 'post_title' => 'FAQ fixture' ) );
$review   = wp_insert_post( array( 'post_type' => 'review', 'post_status' => 'publish', 'post_title' => 'Review fixture' ) );
$post     = wp_insert_post( array( 'post_type' => 'post', 'post_status' => 'publish', 'post_title' => 'Article fixture' ) );
$fixtures = array( $course, $faq, $review, $post );

try {
	update_field( 'faq_question', 'Питання fixture?', $faq );
	update_field( 'faq_answer', '<p>Відповідь fixture.</p>', $faq );
	update_field( 'faq_is_active', 1, $faq );
	update_field( 'review_author_name', 'Автор fixture', $review );
	update_field( 'review_text', 'Відгук fixture', $review );
	update_field( 'review_is_approved', 1, $review );

	$pages = array(
		'about' => array( 'templates/page-about.php', 'about_hero_title', 'Source About', 'about-directions', array() ),
		'it-courses' => array( 'templates/page-it-courses.php', 'it_courses_hero_title', 'Source IT', 'courses-section__items', array( 'it_courses_featured_courses' => array( $course ) ) ),
		'en-courses' => array( 'templates/page-english-courses.php', 'english_courses_hero_title', 'Source English', 'en-courses-section__items', array( 'english_courses_featured_courses' => array( $course ) ) ),
		'faq' => array( 'templates/page-faq.php', 'faq_page_hero_title', 'Source FAQ', 'faq-banner-section__blocks', array( 'faq_page_featured_faq' => array( $faq ), 'faq_page_featured_reviews' => array( $review ) ) ),
		'media-center' => array( 'templates/page-media-center.php', 'media_center_hero_title', 'Source Media', 'archive-section__title', array( 'media_center_articles' => array( $post ) ) ),
	);
	foreach ( $pages as $kind => $config ) {
		list( $template, $title_field, $title, $source_class, $extra ) = $config;
		$page_id    = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $title, 'post_name' => 'fixture-' . $kind, 'meta_input' => array( '_wp_page_template' => $template ) ) );
		$fixtures[] = $page_id;
		update_field( $title_field, $title, $page_id );
		foreach ( $extra as $field => $value ) {
			update_field( $field, $value, $page_id );
		}
		ob_start();
		Logika_Theme_Fixed_Page::render( $kind, $page_id );
		$html = (string) ob_get_clean();
		if ( ! str_contains( $html, $title ) || ! str_contains( $html, $source_class ) ) {
			$errors[] = "{$kind} does not inject ACF data into source markup.";
		}
	}

	$legal_id   = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Legal fixture', 'meta_input' => array( '_wp_page_template' => 'templates/page-legal.php' ) ) );
	$fixtures[] = $legal_id;
	update_field( 'legal_intro_title', 'Юридичний вступ fixture', $legal_id );
	update_field( 'legal_sections', array( array( 'anchor' => 'section-fixture', 'heading' => 'Розділ fixture', 'content' => '<p>Текст fixture.</p>' ) ), $legal_id );
	ob_start();
	Logika_Theme_Fixed_Page::renderLegal( $legal_id );
	$legal = (string) ob_get_clean();
	foreach ( array( 'Юридичний вступ fixture', 'id="section-fixture"', 'Розділ fixture', 'Текст fixture.' ) as $expected ) {
		if ( ! str_contains( $legal, $expected ) ) {
			$errors[] = "Legal page is missing {$expected}.";
		}
	}
} finally {
	foreach ( $fixtures as $fixture ) {
		wp_delete_post( (int) $fixture, true );
	}
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Fixed pages preserve source markup while rendering ACF data.\n";
