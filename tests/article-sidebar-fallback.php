<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$post_id = (int) wp_insert_post( array( 'post_type' => 'post', 'post_status' => 'publish', 'post_title' => 'Тест сайдбару статті' ) );

if ( $post_id <= 0 ) {
	fwrite( STDERR, "Cannot create sidebar test article.\n" );
	exit( 1 );
}

register_shutdown_function( static function () use ( $post_id ): void { wp_delete_post( $post_id, true ); } );
update_field( 'article_sidebar_enabled', 1, $post_id );

$markup = Logika_Theme_Article_Page::render( $post_id );
$expected = array( 'Зимовий табір у Буковелі', 'Забронювати місце', 'Популярні курси', 'Візуальне програмування', 'Геймдизайн', 'Основи штучного інтелекту', 'Створення веб-сайтів' );

foreach ( $expected as $text ) {
	if ( ! str_contains( $markup, $text ) ) {
		fwrite( STDERR, "Missing default article sidebar content: {$text}\n" );
		exit( 1 );
	}
}

$styles = (string) file_get_contents( dirname( __DIR__ ) . '/wordpress/wp-content/themes/logika-theme/assets/css/style.css' );
foreach ( array( 'article-section__headings', 'article-section__aside' ) as $selector ) {
	if ( ! preg_match( '/\.' . $selector . '\s*\{[^}]*position:\s*sticky;[^}]*top:\s*calc\(var\(--header-height\) \+ 20px\);/s', $styles ) ) {
		fwrite( STDERR, "Article sidebars must stay below the sticky header.\n" );
		exit( 1 );
	}
}

echo "Article sidebar renders the restored default campaign and courses.\n";
