<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$errors = array();

foreach ( array( 'logirace-2026', 'fantasy-games-2025' ) as $slug ) {
	$post = get_page_by_path( $slug, OBJECT, 'post' );
	if ( ! $post || 'publish' !== $post->post_status || '' === trim( $post->post_content ) || (int) get_field( 'article_cover_image', $post->ID ) <= 0 ) {
		$errors[] = "Missing published contest article: {$slug}";
	}
}

$source = (string) file_get_contents( get_template_directory() . '/source-pages/media-center.php' );
foreach ( array( '/media-center/articles/logirace-2026/', '/media-center/articles/fantasy-games-2025/' ) as $url ) {
	if ( ! str_contains( $source, 'href="' . $url . '"' ) ) {
		$errors[] = "Missing Media Center link: {$url}";
	}
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Contest articles and Media Center links are configured.\n";
