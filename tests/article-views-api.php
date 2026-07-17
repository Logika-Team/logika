<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$post_id = (int) wp_insert_post( array( 'post_type' => 'post', 'post_title' => 'Перегляди статті', 'post_status' => 'publish' ) );
$draft_id = (int) wp_insert_post( array( 'post_type' => 'post', 'post_title' => 'Чернетка переглядів', 'post_status' => 'draft' ) );
$cookie = 'logika_article_view_' . $post_id;
$errors = array();

register_shutdown_function(
	static function () use ( $post_id, $draft_id, $cookie ): void {
		unset( $_COOKIE[ $cookie ] );
		wp_delete_post( $post_id, true );
		wp_delete_post( $draft_id, true );
	}
);

if ( ! class_exists( Logika\Core\ArticleViews::class ) ) {
	fwrite( STDERR, "Article views endpoint is missing.\n" );
	exit( 1 );
}

unset( $_COOKIE[ $cookie ] );
$first = rest_do_request( new WP_REST_Request( 'POST', '/logika/v1/articles/' . $post_id . '/view' ) );
if ( 200 !== $first->get_status() || 1 !== (int) $first->get_data()['views'] || empty( $first->get_data()['counted'] ) || 'no-store' !== $first->get_headers()['Cache-Control'] ) {
	$errors[] = 'First anonymous article view was not counted safely.';
}

$_COOKIE[ $cookie ] = '1';
$repeat = rest_do_request( new WP_REST_Request( 'POST', '/logika/v1/articles/' . $post_id . '/view' ) );
if ( 200 !== $repeat->get_status() || 1 !== (int) $repeat->get_data()['views'] || ! empty( $repeat->get_data()['counted'] ) ) {
	$errors[] = 'Repeat article view must not increment the counter.';
}

$draft = rest_do_request( new WP_REST_Request( 'POST', '/logika/v1/articles/' . $draft_id . '/view' ) );
if ( 404 !== $draft->get_status() ) {
	$errors[] = 'Draft article view endpoint must not be public.';
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Article views API counts each browser once.\n";
