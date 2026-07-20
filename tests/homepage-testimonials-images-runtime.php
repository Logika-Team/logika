<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$page_id = (int) get_option( 'page_on_front' );
require_once ABSPATH . 'wp-admin/includes/image.php';

function logika_testimonials_image( int $page_id, int $index ): int {
	$upload = wp_upload_dir();
	$file   = trailingslashit( $upload['path'] ) . "testimonials-image-{$index}.png";
	file_put_contents( $file, base64_decode( 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMB/ax3p1sAAAAASUVORK5CYII=' ) );
	$id = wp_insert_attachment( array( 'post_title' => "Testimonials image {$index}", 'post_mime_type' => 'image/png', 'post_status' => 'inherit' ), $file, $page_id );
	wp_update_attachment_metadata( (int) $id, wp_generate_attachment_metadata( (int) $id, $file ) );

	return (int) $id;
}

$photos = array_map( static fn( int $index ): int => logika_testimonials_image( $page_id, $index ), range( 1, 4 ) );
$photo  = $photos[0];

$original = get_field( 'global_reviews_gallery', 'option' );
$review   = wp_insert_post( array( 'post_type' => 'review', 'post_status' => 'publish', 'post_title' => 'Testimonials image fixture' ) );
register_shutdown_function(
	static function () use ( $original, $review, $photos ): void {
		if ( $original ) {
			update_field( 'global_reviews_gallery', $original, 'option' );
		} else {
			delete_field( 'global_reviews_gallery', 'option' );
		}
		wp_delete_post( $review, true );
		foreach ( $photos as $photo ) {
			wp_delete_attachment( $photo, true );
		}
	}
);
update_field( 'review_author_name', 'Фото fixture', $review );
update_field( 'review_text', 'Текст fixture', $review );
update_field( 'review_is_approved', 1, $review );
update_field( 'review_photo', $photo, $review );
update_field( 'global_reviews_gallery', $photos, 'option' );

$markup = (string) file_get_contents( get_template_directory() . '/source-pages/index.php' );
$output = Logika_Theme_Testimonials::apply( $markup, array( $review ) );
foreach ( $photos as $decor ) {
	if ( ! str_contains( $output, (string) wp_get_attachment_image( $decor, 'medium', false, array( 'width' => 220, 'height' => 220, 'alt' => '' ) ) ) ) {
		fwrite( STDERR, "Homepage does not render all four global testimonial photos.\n" );
		exit( 1 );
	}
}

if ( ! str_contains( $output, (string) wp_get_attachment_image_url( $photo, 'thumbnail' ) ) ) {
	fwrite( STDERR, "Review photo is not rendered as the testimonial avatar.\n" );
	exit( 1 );
}

echo "Homepage renders all configurable testimonial photos.\n";
