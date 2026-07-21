<?php

declare(strict_types=1);

require dirname( __DIR__ ) . '/wordpress/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';

use Logika\Core\SvgUploads;

$errors = array();

// An administrator must be able to pick .svg in the media library.
wp_set_current_user( 1 );
$mimes = get_allowed_mime_types();
if ( ( $mimes['svg'] ?? '' ) !== 'image/svg+xml' ) {
	$errors[] = 'Expected image/svg+xml to be an allowed upload mime type for an administrator.';
}

// WordPress must not blank out the extension for an .svg file.
$tmp = wp_tempnam( 'logika-svg-test.svg' );
$logo = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 60"><rect width="120" height="60" fill="#f00"/></svg>';
file_put_contents( $tmp, $logo );
$checked = wp_check_filetype_and_ext( $tmp, 'logo.svg' );
if ( 'svg' !== $checked['ext'] || 'image/svg+xml' !== $checked['type'] ) {
	$errors[] = 'Expected wp_check_filetype_and_ext() to keep ext=svg / type=image/svg+xml.';
}

// The sanitizer must strip executable content but keep the drawing.
$hostile = '<?xml version="1.0"?><!DOCTYPE svg [<!ENTITY xxe SYSTEM "file:///etc/passwd">]>'
	. '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 10" onload="alert(1)">'
	. '<script>alert(2)</script>'
	. '<a href="javascript:alert(3)"><circle cx="5" cy="5" r="4" fill="#0f0"/></a>'
	. '<image href="https://evil.example/track.png"/>'
	. '<foreignObject><b xmlns="http://www.w3.org/1999/xhtml">x</b></foreignObject>'
	. '</svg>';
$clean = SvgUploads::sanitizeMarkup( $hostile );

if ( null === $clean ) {
	$errors[] = 'Expected the sanitizer to accept a well-formed (if hostile) SVG.';
} else {
	foreach ( array( '<script', 'onload', 'javascript:', 'foreignObject', 'evil.example', 'ENTITY' ) as $needle ) {
		if ( false !== stripos( $clean, $needle ) ) {
			$errors[] = sprintf( 'Expected the sanitizer to strip "%s".', $needle );
		}
	}
	if ( false === strpos( $clean, '<circle' ) ) {
		$errors[] = 'Expected the sanitizer to preserve the actual drawing.';
	}
}

// Non-SVG payloads must be rejected outright.
if ( null !== SvgUploads::sanitizeMarkup( '<html><body>not an svg</body></html>' ) ) {
	$errors[] = 'Expected non-SVG markup to be rejected.';
}

// A real upload must land in the media library with usable dimensions.
$upload = wp_upload_bits( 'logika-svg-test.svg', null, $logo );
if ( ! empty( $upload['error'] ) ) {
	$errors[] = 'Expected wp_upload_bits() to accept an SVG: ' . $upload['error'];
} else {
	$id = wp_insert_attachment(
		array( 'post_title' => 'logika-svg-test', 'post_mime_type' => 'image/svg+xml', 'post_status' => 'inherit' ),
		$upload['file']
	);
	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( (int) $id, $upload['file'] ) );

	$meta = wp_get_attachment_metadata( (int) $id );
	if ( ( $meta['width'] ?? 0 ) !== 120 || ( $meta['height'] ?? 0 ) !== 60 ) {
		$errors[] = 'Expected SVG attachment metadata to carry the viewBox dimensions (120x60).';
	}

	wp_delete_attachment( (int) $id, true );
}

@unlink( $tmp );

if ( $errors ) {
	echo "FAIL\n" . implode( "\n", $errors ) . "\n";
	exit( 1 );
}

echo "OK: svg uploads\n";
