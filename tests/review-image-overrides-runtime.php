<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

use Logika\Core\HomepageImageOverrides;

$images = get_posts( array( 'post_type' => 'attachment', 'post_mime_type' => 'image', 'posts_per_page' => 1, 'fields' => 'ids' ) );
if ( ! $images ) {
	fwrite( STDERR, "No image attachment is available for the review photo test.\n" );
	exit( 1 );
}

$review_id = wp_insert_post( array( 'post_type' => 'review', 'post_status' => 'draft', 'post_title' => 'Review original photo fixture' ) );
update_post_meta( $review_id, 'review_photo', $images[0] );
HomepageImageOverrides::captureReviewOriginalPhoto( $review_id );
$original = (int) get_post_meta( $review_id, 'review_original_photo', true );
wp_delete_post( $review_id, true );

if ( (int) $images[0] !== $original ) {
	fwrite( STDERR, "Review original photo was not preserved.\n" );
	exit( 1 );
}

echo "Review original photo is preserved in WordPress.\n";
