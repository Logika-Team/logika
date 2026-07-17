<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$single = file_get_contents( get_template_directory() . '/single.php' ) ?: '';
$fields = file_get_contents( dirname( get_template_directory(), 2 ) . '/plugins/logika-core/acf-json/group_logika_post.json' ) ?: '';
$authors = file_get_contents( dirname( get_template_directory(), 2 ) . '/plugins/logika-core/acf-json/group_logika_article_author.json' ) ?: '';
$views = file_get_contents( get_template_directory() . '/assets/js/article-views.js' ) ?: '';

if ( ! str_contains( $single, 'Logika_Theme_Article_Page::render' ) || ! class_exists( 'Logika_Theme_Article_Page' ) || ! post_type_exists( 'article_author' ) || ! str_contains( $views, 'data-article-view-count' ) || ! str_contains( $fields, 'article_author' ) || ! str_contains( $authors, 'article_author_photo' ) || ! str_contains( $fields, 'article_cover_image' ) || ! str_contains( $fields, 'article_related_posts' ) || ! str_contains( $fields, 'article_faq_items' ) || ! function_exists( 'acf_get_field' ) ) {
	fwrite( STDERR, "Posts do not use the dynamic article template and editorial fields.\n" );
	exit( 1 );
}

foreach ( array( 'article_author', 'article_author_photo', 'article_cover_image', 'article_related_posts', 'article_faq_items', 'media_center_topics' ) as $field ) {
	if ( ! acf_get_field( $field ) ) {
		fwrite( STDERR, "Editorial field {$field} is not loaded from ACF Local JSON.\n" );
		exit( 1 );
	}
}

echo "Posts use the dynamic article template and editorial fields.\n";
