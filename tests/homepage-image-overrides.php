<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

use Logika\Core\HomepageImageOverrides;

$page_id = (int) get_option( 'page_on_front' );
$original = get_field( 'home_hero_boy_image_override', $page_id );
$errors   = array();

require_once ABSPATH . 'wp-admin/includes/image.php';

$upload = wp_upload_dir();
$file   = trailingslashit( $upload['path'] ) . 'homepage-override-test.png';
file_put_contents( $file, base64_decode( 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMB/ax3p1sAAAAASUVORK5CYII=' ) );

$image_id = wp_insert_attachment(
	array(
		'post_title'     => 'Головна 90 — Дозволений override',
		'post_mime_type' => 'image/png',
		'post_status'    => 'inherit',
	),
	$file,
	$page_id
);
wp_update_attachment_metadata(
	(int) $image_id,
	array(
		'file'   => basename( $file ),
		'width'  => 440,
		'height' => 225,
	)
);

register_shutdown_function(
	static function () use ( $page_id, $original, $image_id ): void {
		if ( $original ) {
			update_field( 'home_hero_boy_image_override', $original, $page_id );
		} else {
			delete_field( 'home_hero_boy_image_override', $page_id );
		}

		wp_delete_attachment( (int) $image_id, true );
	}
);

$field = acf_get_field( 'field_home_hero_boy_image_override' );

if ( ! $field ) {
	$errors[] = 'Homepage hero override field is not registered.';
} else {
	$valid = HomepageImageOverrides::validateValue( true, $image_id, $field, 'acf[field_home_hero_boy_image_override]' );
	if ( true !== $valid ) {
		$errors[] = 'A valid hero override is rejected.';
	}

	wp_update_attachment_metadata( (int) $image_id, array( 'width' => 225, 'height' => 440 ) );
	$invalid = HomepageImageOverrides::validateValue( true, $image_id, $field, 'acf[field_home_hero_boy_image_override]' );
	if ( true === $invalid ) {
		$errors[] = 'An override with an invalid aspect ratio is accepted.';
	}

	wp_update_post( array( 'ID' => $image_id, 'post_mime_type' => 'image/gif' ) );
	$unsupported = HomepageImageOverrides::validateValue( true, $image_id, $field, 'acf[field_home_hero_boy_image_override]' );
	if ( true === $unsupported ) {
		$errors[] = 'An override with an unsupported format is accepted.';
	}
	wp_update_post( array( 'ID' => $image_id, 'post_mime_type' => 'image/png' ) );

	wp_update_attachment_metadata( (int) $image_id, array( 'width' => 440, 'height' => 225 ) );
	update_field( 'home_hero_boy_image_override', $image_id, $page_id );
	ob_start();
	logika_theme_render_source_page( 'index' );
	$homepage = (string) ob_get_clean();
	$url      = wp_get_attachment_image_url( (int) $image_id, 'full' );

	if ( ! $url || ! str_contains( $homepage, $url ) ) {
		$errors[] = 'Homepage does not render a valid hero override.';
	}

	delete_field( 'home_hero_boy_image_override', $page_id );
	ob_start();
	logika_theme_render_source_page( 'index' );
	$reset_homepage = (string) ob_get_clean();
	if ( $url && str_contains( $reset_homepage, $url ) ) {
		$errors[] = 'Clearing an override does not restore the source asset.';
	}
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Homepage image overrides preserve source assets and validate replacements.\n";
