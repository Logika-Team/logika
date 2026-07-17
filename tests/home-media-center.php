<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

/** @return array<int, mixed> */
function logika_test_meta( int $post_id, string $key ): array {
	return get_post_meta( $post_id, $key, false );
}

/** @param array<int, mixed> $values */
function logika_restore_test_meta( int $post_id, string $key, array $values ): void {
	delete_post_meta( $post_id, $key );
	foreach ( $values as $value ) {
		add_post_meta( $post_id, $key, $value );
	}
}

$page  = (int) get_option( 'page_on_front' );
$posts = get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 4, 'orderby' => 'date', 'order' => 'DESC' ) );

if ( ! $page || count( $posts ) < 4 ) {
	fwrite( STDERR, "Homepage media test needs a front page and four published posts.\n" );
	exit( 1 );
}

$selected_before = logika_test_meta( $page, 'home_media_posts' );
$hidden_before   = logika_test_meta( $posts[1]->ID, 'post_hide_from_blog' );
$minutes_before  = logika_test_meta( $posts[2]->ID, 'article_reading_minutes' );
$views_before    = logika_test_meta( $posts[2]->ID, 'article_view_count' );
$errors          = array();

try {
	update_post_meta( $page, 'home_media_posts', array( $posts[2]->ID, $posts[1]->ID, $posts[0]->ID ) );
	update_post_meta( $posts[1]->ID, 'post_hide_from_blog', '1' );
	update_post_meta( $posts[2]->ID, 'article_reading_minutes', '7' );
	update_post_meta( $posts[2]->ID, 'article_view_count', '42' );

	ob_start();
	logika_theme_render_source_page( 'index' );
	$selected_markup = (string) ob_get_clean();
	$first_title     = get_the_title( $posts[2] );
	$third_title     = get_the_title( $posts[0] );
	$hidden_title    = get_the_title( $posts[1] );

	if ( ! str_contains( $selected_markup, $first_title ) || ! str_contains( $selected_markup, $third_title ) || str_contains( $selected_markup, $hidden_title ) || strpos( $selected_markup, $first_title ) > strpos( $selected_markup, $third_title ) ) {
		$errors[] = 'Homepage media cards do not keep selected published posts in order.';
	}

	if ( ! str_contains( $selected_markup, get_the_date( 'd.m.Y', $posts[2] ) ) || ! str_contains( $selected_markup, '7 хв' ) || ! str_contains( $selected_markup, '>42</span>' ) ) {
		$errors[] = 'Homepage media cards do not render article metadata.';
	}

	if ( str_contains( $selected_markup, 'src="http://img/media-center/figma/' ) || ! str_contains( $selected_markup, '/assets/img/media-center/figma/news.svg' ) ) {
		$errors[] = 'Homepage media artwork does not use theme asset URLs.';
	}

	delete_post_meta( $page, 'home_media_posts' );
	ob_start();
	logika_theme_render_source_page( 'index' );
	$fallback_markup = (string) ob_get_clean();

	if ( ! str_contains( $fallback_markup, get_the_title( $posts[0] ) ) || str_contains( $fallback_markup, 'blog-placeholder.png' ) ) {
		$errors[] = 'Homepage media cards do not fall back to current blog posts.';
	}
} finally {
	logika_restore_test_meta( $page, 'home_media_posts', $selected_before );
	logika_restore_test_meta( $posts[1]->ID, 'post_hide_from_blog', $hidden_before );
	logika_restore_test_meta( $posts[2]->ID, 'article_reading_minutes', $minutes_before );
	logika_restore_test_meta( $posts[2]->ID, 'article_view_count', $views_before );
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Homepage media center uses selected published posts and fallback cards.\n";
