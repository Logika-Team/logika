<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

use Logika\Core\ContentMigration;

$home_id = (int) get_option( 'page_on_front' );
$post_ids = get_posts( array( 'post_type' => 'post', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids' ) );
$home_faq = (array) get_field( 'home_faq_items', $home_id );
$article_faq = array_map( static fn( int $id ): array => (array) get_field( 'article_faq_items', $id ), $post_ids );
$source = array( array( 'question' => 'Питання з головної?', 'answer' => 'Так, це відповідь з головної.' ) );
$expected = array( array( 'question' => 'Питання з головної?', 'answer' => wpautop( 'Так, це відповідь з головної.' ) ) );
$errors = array();

try {
	update_field( 'home_faq_items', $source, $home_id );
	acf_flush_value_cache( $home_id, 'home_faq_items' );
	$report = ContentMigration::seedArticleFaqs();
	foreach ( $post_ids as $id ) {
		if ( $expected !== (array) get_field( 'article_faq_items', $id ) ) {
			$errors[] = "Article {$id} does not use the homepage FAQ.";
		}
	}
	if ( count( $post_ids ) !== $report['changed'] + $report['preserved'] ) {
		$errors[] = 'Homepage FAQ migration did not replace every article FAQ.';
	}
} finally {
	update_field( 'home_faq_items', $home_faq, $home_id );
	acf_flush_value_cache( $home_id, 'home_faq_items' );
	foreach ( $post_ids as $index => $id ) {
		update_field( 'article_faq_items', $article_faq[ $index ], $id );
	}
}

if ( $errors ) {
	fwrite( STDERR, implode( "\n", $errors ) . "\n" );
	exit( 1 );
}

echo "Article FAQ seeding passed.\n";
