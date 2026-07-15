<?php

declare(strict_types=1);

require dirname( __DIR__ ) . '/wordpress/wp-load.php';

$articles = array(
	'digitalhygiene',
	'top10itprofessions',
	'specialtiesanduniversities',
	'vision',
	'studyingforchildren',
	'digitaletiquette',
	'laptopforprogramming',
	'kidsandphones',
	'parentalcontrol',
	'appsforprogramming',
	'concentration',
	'developmentofyoungprogrammers',
	'videogames',
);

$errors = array();

foreach ( $articles as $slug ) {
	$post = get_page_by_path( $slug, OBJECT, 'post' );
	if ( ! $post instanceof WP_Post || 'publish' !== $post->post_status ) {
		$errors[] = "Tilda article is not published: {$slug}";
		continue;
	}

	if ( '' === trim( $post->post_content ) || str_contains( $post->post_content, 't396__' ) ) {
		$errors[] = "Tilda article content was not normalized: {$slug}";
	}

	$cover_id = (int) get_field( 'article_cover_image', $post->ID );
	if ( $cover_id <= 0 || 'attachment' !== get_post_type( $cover_id ) || $cover_id !== (int) get_post_thumbnail_id( $post->ID ) ) {
		$errors[] = "Tilda article cover is missing from the media library: {$slug}";
	}
}

$imported = get_posts(
	array(
		'post_type'      => 'post',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'meta_key'       => 'logika_tilda_article_source',
	)
);

if ( count( $articles ) !== count( $imported ) ) {
	$errors[] = 'Tilda article import created duplicates or skipped a source article.';
}

if ( get_post_meta( 1, 'logika_tilda_article_source', true ) ) {
	$errors[] = 'Tilda import overwrote the existing default post.';
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Tilda articles are published with media-library covers.\n";
