<?php

declare(strict_types=1);

require dirname( __DIR__ ) . '/wordpress/wp-load.php';

$fixtures = array();
$errors   = array();

try {
	$page    = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Builder fixture', 'meta_input' => array( '_wp_page_template' => 'templates/page-builder.php' ) ) );
	$legacy  = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Legacy fixture', 'post_content' => '<p>Legacy post content fixture</p>', 'meta_input' => array( '_wp_page_template' => 'templates/page-builder.php' ) ) );
	$course  = wp_insert_post( array( 'post_type' => 'course', 'post_status' => 'publish', 'post_title' => 'Published course fixture' ) );
	$draft   = wp_insert_post( array( 'post_type' => 'course', 'post_status' => 'draft', 'post_title' => 'Draft course fixture' ) );
	$review  = wp_insert_post( array( 'post_type' => 'review', 'post_status' => 'publish', 'post_title' => 'Approved review fixture' ) );
	$hidden  = wp_insert_post( array( 'post_type' => 'review', 'post_status' => 'publish', 'post_title' => 'Rejected review fixture' ) );
	$faq     = wp_insert_post( array( 'post_type' => 'faq_item', 'post_status' => 'publish', 'post_title' => 'Active FAQ fixture' ) );
	$off_faq = wp_insert_post( array( 'post_type' => 'faq_item', 'post_status' => 'publish', 'post_title' => 'Inactive FAQ fixture' ) );
	$fixtures = array( $page, $legacy, $course, $draft, $review, $hidden, $faq, $off_faq );

	update_field( 'review_author_name', 'Approved review fixture', $review );
	update_field( 'review_text', 'Approved review text fixture', $review );
	update_field( 'review_is_approved', 1, $review );
	update_field( 'review_author_name', 'Rejected review fixture', $hidden );
	update_field( 'review_text', 'Rejected review text fixture', $hidden );
	update_field( 'review_is_approved', 0, $hidden );
	update_field( 'faq_question', 'Active FAQ fixture?', $faq );
	update_field( 'faq_answer', 'Active FAQ answer fixture', $faq );
	update_field( 'faq_is_active', 1, $faq );
	update_field( 'faq_question', 'Inactive FAQ fixture?', $off_faq );
	update_field( 'faq_answer', 'Inactive FAQ answer fixture', $off_faq );
	update_field( 'faq_is_active', 0, $off_faq );
	update_field(
		'generic_sections',
		array(
			array( 'acf_fc_layout' => 'hero', 'title' => 'Hero fixture', 'text' => 'Hero text fixture' ),
			array( 'acf_fc_layout' => 'rich_text', 'title' => 'Rich fixture', 'content' => '<p>Rich text fixture</p><script>unsafe()</script>' ),
			array( 'acf_fc_layout' => 'course_selection', 'title' => 'Courses fixture', 'items' => array( $course, $draft ) ),
			array( 'acf_fc_layout' => 'reviews', 'title' => 'Reviews fixture', 'items' => array( $review, $hidden ) ),
			array( 'acf_fc_layout' => 'faq', 'title' => 'FAQ fixture', 'items' => array( $faq, $off_faq ) ),
			array( 'acf_fc_layout' => 'school_map', 'title' => 'Map fixture', 'text' => 'Map text fixture' ),
			array( 'acf_fc_layout' => 'cta', 'title' => 'CTA fixture', 'subtitle' => 'CTA subtitle fixture' ),
		),
		$page
	);

	ob_start();
	Logika_Theme_Generic_Page::render( $page );
	$html = (string) ob_get_clean();
	ob_start();
	Logika_Theme_Generic_Page::render( $legacy );
	$legacy_html = (string) ob_get_clean();

	$position = -1;
	foreach ( array( 'Hero fixture', 'Rich fixture', 'Courses fixture', 'Reviews fixture', 'FAQ fixture', 'Map fixture', 'CTA fixture' ) as $expected ) {
		$next = strpos( $html, $expected );
		if ( false === $next || $next <= $position ) {
			$errors[] = "Generic layout order is broken at {$expected}.";
			break;
		}
		$position = $next;
	}
	foreach ( array( 'Published course fixture', 'Approved review text fixture', 'Active FAQ fixture?', 'Legacy post content fixture' ) as $expected ) {
		$haystack = 'Legacy post content fixture' === $expected ? $legacy_html : $html;
		if ( ! str_contains( $haystack, $expected ) ) {
			$errors[] = "Generic page is missing {$expected}.";
		}
	}
	foreach ( array( 'Draft course fixture', 'Rejected review text fixture', 'Inactive FAQ fixture?', '<script>unsafe()</script>' ) as $unexpected ) {
		if ( str_contains( $html, $unexpected ) ) {
			$errors[] = "Generic page exposes {$unexpected}.";
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

echo "Generic page renders reusable layouts, filters entities and preserves post_content fallback.\n";
