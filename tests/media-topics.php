<?php

declare(strict_types=1);

require dirname( __DIR__ ) . '/wordpress/wp-load.php';

$page   = get_page_by_path( 'media-center' );
$post   = get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 1, 'fields' => 'ids' ) );
$before = $page ? get_field( 'media_center_tags', (int) $page->ID ) : null;

if ( ! $page || ! $post ) {
	throw new RuntimeException( 'Потрібні сторінка Медіа-центру та щонайменше одна опублікована стаття.' );
}

try {
	update_field(
		'field_media_center_tags',
		array(
			array( 'label' => 'Акції', 'url' => '#media-offers' ),
			array( 'label' => 'Тестова тема', 'url' => '/media-center/articles/' ),
		),
		(int) $page->ID
	);

	$media = Logika_Theme_Page_Content::apply( file_get_contents( get_theme_file_path( 'source-pages/media-center.php' ) ), 'media-center', (int) $page->ID );
	preg_match( '#<ul class="tags">.*?</ul>#s', $media, $matches );
	$chips = $matches[0] ?? '';
	if ( ! str_contains( $chips, '>Тестова тема<' ) || ! str_contains( $chips, 'href="#media-offers"' ) || str_contains( $chips, 'Logika Блог' ) ) {
		throw new RuntimeException( 'Теги Медіа-центру мають братися з поля сторінки, а не з верстки.' );
	}

	preg_match( '#<ul class="tags">.*?</ul>#s', Logika_Theme_Article_Page::render( (int) $post[0] ), $matches );
	$article = $matches[0] ?? '';
	if ( ! str_contains( $article, '>Тестова тема<' ) || ! str_contains( $article, esc_url( home_url( '/media-center/#media-offers' ) ) ) ) {
		throw new RuntimeException( 'Сторінка статті має показувати ті самі теги, а якорі — вести на Медіа-центр.' );
	}
} finally {
	update_field( 'field_media_center_tags', $before, (int) $page->ID );
}

echo "Media topics are editable on the Media Center page and shared with articles.\n";
