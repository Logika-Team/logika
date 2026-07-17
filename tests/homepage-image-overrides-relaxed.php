<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

use Logika\Core\HomepageImageOverrides;

$upload = wp_upload_dir();
$file   = trailingslashit( $upload['path'] ) . 'homepage-relaxed-override-test.png';

file_put_contents( $file, base64_decode( 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMB/ax3p1sAAAAASUVORK5CYII=' ) );
$image_id = wp_insert_attachment( array( 'post_mime_type' => 'image/png', 'post_status' => 'inherit' ), $file );
wp_update_attachment_metadata( (int) $image_id, array( 'width' => 529, 'height' => 629 ) );

register_shutdown_function( static fn() => wp_delete_attachment( (int) $image_id, true ) );

foreach ( array( 'field_home_programming_courses_image_override', 'field_home_programming_courses_icon_override' ) as $field_key ) {

	$field = acf_get_field( $field_key );
	$valid = $field ? HomepageImageOverrides::validateValue( true, $image_id, $field, "acf[field_home_programming_courses][row-1][{$field_key}]" ) : false;

	if ( ! $field || 0 !== (int) $field['min_width'] || 0 !== (int) $field['min_height'] || true !== $valid ) {

		fwrite( STDERR, "A PNG card asset must accept a portrait source image.\n" );
		exit( 1 );
	}
}

echo "Programming course previews accept PNG images regardless of dimensions.\n";
