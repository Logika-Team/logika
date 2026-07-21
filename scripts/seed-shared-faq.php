<?php
/**
 * Makes the FAQ text identical on every page: the homepage `home_faq_items` rows
 * become canonical `faq_item` posts, and every FAQ relation points at them.
 *
 * Run: ddev wp eval-file scripts/seed-shared-faq.php
 */

$page_id = (int) get_option( 'page_on_front' );
if ( $page_id <= 0 ) {
	fwrite( STDERR, "Homepage is not configured.\n" );
	exit( 1 );
}

$home_items = array_values(
	array_filter(
		(array) get_field( 'home_faq_items', $page_id ),
		static fn( mixed $row ): bool => is_array( $row ) && ! empty( $row['question'] ) && ! empty( $row['answer'] )
	)
);
if ( ! $home_items ) {
	fwrite( STDERR, "Homepage FAQ (home_faq_items) is empty; run scripts/seed-home-texts.php first.\n" );
	exit( 1 );
}

$faq_ids = array();
foreach ( $home_items as $index => $item ) {
	$slug    = 'faq-home-' . ( $index + 1 );
	$post    = get_page_by_path( $slug, OBJECT, 'faq_item' );
	$post_id = $post ? (int) $post->ID : (int) wp_insert_post(
		array( 'post_type' => 'faq_item', 'post_status' => 'publish', 'post_name' => $slug, 'post_title' => (string) $item['question'] )
	);
	if ( ! $post_id ) {
		fwrite( STDERR, "Failed to create {$slug}.\n" );
		exit( 1 );
	}
	wp_update_post( array( 'ID' => $post_id, 'post_title' => (string) $item['question'], 'post_status' => 'publish' ) );
	update_field( 'faq_question', (string) $item['question'], $post_id );
	update_field( 'faq_answer', wpautop( (string) $item['answer'] ), $post_id );
	update_field( 'faq_sort_order', $index + 1, $post_id );
	update_field( 'faq_is_active', 1, $post_id );
	$faq_ids[] = $post_id;
}

// Legacy and camp-specific FAQ entries stay in the database but must not surface
// anywhere, otherwise pages without an explicit relation fall back to them.
foreach ( get_posts( array( 'post_type' => 'faq_item', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids', 'post__not_in' => $faq_ids ) ) as $legacy_id ) {
	update_field( 'faq_is_active', 0, (int) $legacy_id );
}

$targets = array(
	'about'           => 'about_featured_faq',
	'it-courses'      => 'it_courses_featured_faq',
	'english-courses' => 'english_courses_featured_faq',
	'faq'             => 'faq_page_featured_faq',
	'media-center'    => 'media_center_featured_faq',
	'camps'           => 'camp_archive_faq',
);
foreach ( $targets as $path => $field ) {
	$page = get_page_by_path( $path );
	if ( $page instanceof WP_Post ) {
		update_field( $field, $faq_ids, $page->ID );
	}
}

update_field( 'camp_archive_faq', $faq_ids, 'camp_archive' );
update_field( 'fallback_faq', $faq_ids, 'option' );

foreach ( array( 'camp' => 'camp_related_faq', 'course' => 'course_related_faq', 'city' => 'city_related_faq' ) as $post_type => $field ) {
	foreach ( get_posts( array( 'post_type' => $post_type, 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids' ) ) as $related_id ) {
		update_field( $field, $faq_ids, (int) $related_id );
	}
}

// English course pages render `course_faq_items` as their general FAQ section;
// IT courses keep that field for the "Програма курсу" accordion.
$plain_items = array_map(
	static fn( array $item ): array => array( 'question' => (string) $item['question'], 'answer' => wpautop( (string) $item['answer'] ) ),
	$home_items
);
foreach ( get_posts( array( 'post_type' => 'course', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids' ) ) as $course_id ) {
	if ( has_term( 'english', 'course_direction', (int) $course_id ) ) {
		update_field( 'course_faq_items', $plain_items, (int) $course_id );
	}
}

foreach ( get_posts( array( 'post_type' => 'post', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids' ) ) as $post_id ) {
	update_field( 'article_faq_items', $plain_items, (int) $post_id );
}

printf( "Shared FAQ seeded from the homepage: %d items.\n", count( $faq_ids ) );
