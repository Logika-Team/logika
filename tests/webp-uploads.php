<?php

declare(strict_types=1);

require dirname( __DIR__ ) . '/wordpress/wp-load.php';

function logika_webp_test_attachment( string $source_path, string $name ): int {
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$upload = wp_upload_dir();
	$file = trailingslashit( $upload['path'] ) . $name;
	copy( dirname( __DIR__ ) . '/' . $source_path, $file );

	$id = wp_insert_attachment( array( 'post_title' => $name, 'post_mime_type' => 'image/png', 'post_status' => 'inherit' ), $file );
	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $file ) );

	return (int) $id;
}

$attachment_ids = array();
register_shutdown_function(
	static function () use ( &$attachment_ids ): void {
		foreach ( $attachment_ids as $id ) {
			wp_delete_attachment( $id, true );
		}
	}
);

$errors = array();

// A photo without an alpha channel: webp must be created and smaller than the source.
$photo_id = logika_webp_test_attachment(
	'wordpress/wp-content/themes/logika-theme/assets/img/testimonials/testimonial2.png',
	'webp-test-photo.png'
);
$attachment_ids[] = $photo_id;
$photo_file = get_attached_file( $photo_id );
$photo_webp = preg_replace( '/\.png$/', '.webp', $photo_file );

if ( ! file_exists( $photo_webp ) ) {
	$errors[] = 'Expected a .webp sibling to be created for the uploaded photo.';
} elseif ( filesize( $photo_webp ) >= filesize( $photo_file ) ) {
	$errors[] = 'Expected the generated webp to be smaller than the source PNG.';
}

$photo_meta = get_post_meta( $photo_id, '_logika_webp', true );
if ( ! is_array( $photo_meta ) || ! in_array( $photo_webp, $photo_meta, true ) ) {
	$errors[] = 'Expected _logika_webp post meta to record the generated webp path.';
}

// A PNG with an alpha channel: webp must be created and loadable.
$alpha_id = logika_webp_test_attachment( 'source/img/Partners/1+1.png', 'webp-test-alpha.png' );
$attachment_ids[] = $alpha_id;
$alpha_file = get_attached_file( $alpha_id );
$alpha_webp = preg_replace( '/\.png$/', '.webp', $alpha_file );

if ( ! file_exists( $alpha_webp ) ) {
	$errors[] = 'Expected a .webp sibling to be created for the uploaded transparent PNG.';
} else {
	$editor = wp_get_image_editor( $alpha_webp );
	if ( is_wp_error( $editor ) ) {
		$errors[] = 'Could not load the generated alpha webp with an image editor.';
	}
}

// picture() helper: <picture> with a webp source when webp exists.
$markup = \Logika\Core\WebpUploads::picture( $photo_id, 'full', array( 'class' => 'test-photo' ) );
if ( ! str_contains( $markup, '<picture>' ) || ! str_contains( $markup, 'type="image/webp"' ) || ! str_contains( $markup, 'class="test-photo"' ) ) {
	$errors[] = 'picture() must render a <picture> element with a webp source when a webp sibling exists.';
}

// picture() helper: falls back to a plain <img> when there is no webp sibling (SVG attachment).
$svg_file = trailingslashit( wp_upload_dir()['path'] ) . 'webp-test.svg';
file_put_contents( $svg_file, '<svg xmlns="http://www.w3.org/2000/svg" width="1" height="1"></svg>' );
$svg_id = (int) wp_insert_attachment( array( 'post_title' => 'webp-test.svg', 'post_mime_type' => 'image/svg+xml', 'post_status' => 'inherit' ), $svg_file );
$attachment_ids[] = $svg_id;
update_post_meta( $svg_id, '_wp_attachment_metadata', array( 'width' => 1, 'height' => 1, 'file' => basename( $svg_file ) ) );

$svg_markup = \Logika\Core\WebpUploads::picture( $svg_id );
if ( str_contains( $svg_markup, '<picture>' ) ) {
	$errors[] = 'picture() must not render a <picture> wrapper when no webp sibling exists.';
}

// Backfill is idempotent: re-running without --force should not recreate the webp file or grow the meta.
$created_on_rerun = \Logika\Core\WebpUploads::backfillAttachment( $photo_id );
$photo_meta_after_rerun = get_post_meta( $photo_id, '_logika_webp', true );
if ( 0 !== $created_on_rerun ) {
	$errors[] = 'backfillAttachment() should report 0 newly created files when the webp already exists.';
}
if ( $photo_meta_after_rerun !== $photo_meta ) {
	$errors[] = 'backfillAttachment() should not change recorded webp paths when nothing changed.';
}

// Deleting the attachment removes the generated webp file too.
wp_delete_attachment( $photo_id, true );
$attachment_ids = array_values( array_diff( $attachment_ids, array( $photo_id ) ) );
if ( file_exists( $photo_webp ) ) {
	$errors[] = 'Deleting the attachment must also delete its generated webp file.';
}

if ( $errors ) {
	foreach ( $errors as $error ) {
		fwrite( STDERR, $error . "\n" );
	}
	exit( 1 );
}

echo "webp-uploads: OK\n";
