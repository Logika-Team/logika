<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$fixtures = array( 'dynamic-article-test' => 'post', 'dynamic-related-test' => 'post', 'dynamic-draft-test' => 'post', 'dynamic-article-author' => 'article_author' );

foreach ( $fixtures as $slug => $type ) {
	if ( get_page_by_path( $slug, OBJECT, $type ) ) {
		fwrite( STDERR, "Test fixture {$slug} must not remain in the Media Center database.\n" );
		exit( 1 );
	}
}

echo "Media Center has no leaked article test fixtures.\n";
