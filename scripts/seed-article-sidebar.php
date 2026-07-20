<?php

declare(strict_types=1);

require dirname( __DIR__ ) . '/wordpress/wp-load.php';

$courses = array_filter( array_map( static function ( string $slug ): int {
	$post = get_page_by_path( $slug, OBJECT, 'course' );
	return $post ? (int) $post->ID : 0;
}, array( 'visual-programming', 'game-design', 'artificial-intelligence', 'websites' ) ) );

foreach ( get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids' ) ) as $post_id ) {
	if ( get_field( 'article_promo_title', $post_id ) || get_field( 'article_promo_description', $post_id ) || get_field( 'article_popular_courses', $post_id ) ) { continue; }
	update_field( 'article_sidebar_enabled', 1, $post_id );
	update_field( 'article_promo_title', 'Зимовий табір у Буковелі', $post_id );
	update_field( 'article_promo_description', 'Зимові канікули з користю: IT-навички, нові друзі та яскраві враження у святковій атмосфері.', $post_id );
	update_field( 'article_popular_courses_title', 'Популярні курси', $post_id );
	update_field( 'article_popular_courses', $courses, $post_id );
}

echo "Article sidebars seeded.\n";
