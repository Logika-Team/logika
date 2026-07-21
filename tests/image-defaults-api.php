<?php

declare(strict_types=1);

require dirname( __DIR__ ) . '/wordpress/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/user.php';

$errors = array();
$images = get_posts( array( 'post_type' => 'attachment', 'post_mime_type' => 'image', 'posts_per_page' => 1, 'fields' => 'ids' ) );
if ( ! $images ) {
	fwrite( STDERR, "No image attachment is available for the image defaults test.\n" );
	exit( 1 );
}
$attachment_id = (int) $images[0];

$course_id = wp_insert_post( array( 'post_type' => 'course', 'post_status' => 'draft', 'post_title' => 'Image defaults fixture' ) );

register_shutdown_function(
	static function () use ( $course_id ): void {
		wp_delete_post( $course_id, true );
	}
);

$_POST['acf'] = array( 'field_course_hero_image' => (string) $attachment_id );
do_action( 'acf/save_post', $course_id );
unset( $_POST['acf'] );

$stored = get_post_meta( $course_id, '_logika_default_image', true );
if ( ( $stored['field_course_hero_image'] ?? null ) !== $attachment_id ) {
	$errors[] = 'The first submitted image value was not captured as the field default.';
}

$_POST['acf'] = array( 'field_course_hero_image' => '0' );
do_action( 'acf/save_post', $course_id );
unset( $_POST['acf'] );

$stored_again = get_post_meta( $course_id, '_logika_default_image', true );
if ( ( $stored_again['field_course_hero_image'] ?? null ) !== $attachment_id ) {
	$errors[] = 'A later save overwrote the already-captured default.';
}

$editor_id = wp_insert_user(
	array(
		'user_login' => 'image-defaults-test-' . $course_id,
		'user_pass'  => wp_generate_password(),
		'role'       => 'editor',
	)
);
register_shutdown_function( static fn() => wp_delete_user( $editor_id ) );

wp_set_current_user( $editor_id );
$request  = new WP_REST_Request( 'GET', '/logika/v1/image-defaults' );
$request->set_param( 'post', $course_id );
$response = rest_do_request( $request );
wp_set_current_user( 0 );

if ( 200 !== $response->get_status() ) {
	$errors[] = 'The image defaults endpoint rejected an editor with edit_post capability.';
} else {
	$data = $response->get_data();
	if ( ! isset( $data['field_course_hero_image']['id'] ) || $attachment_id !== (int) $data['field_course_hero_image']['id'] ) {
		$errors[] = 'The image defaults endpoint did not return the captured default attachment.';
	}
}

$subscriber_id = wp_insert_user(
	array(
		'user_login' => 'image-defaults-sub-' . $course_id,
		'user_pass'  => wp_generate_password(),
		'role'       => 'subscriber',
	)
);
register_shutdown_function( static fn() => wp_delete_user( $subscriber_id ) );

wp_set_current_user( $subscriber_id );
$denied_request = new WP_REST_Request( 'GET', '/logika/v1/image-defaults' );
$denied_request->set_param( 'post', $course_id );
$denied_response = rest_do_request( $denied_request );
wp_set_current_user( 0 );

if ( 403 !== $denied_response->get_status() ) {
	$errors[] = 'A user without edit_post capability could read image defaults for another post.';
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Image defaults are captured on first save and served only to editors with edit_post capability.\n";
