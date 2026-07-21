<?php

declare(strict_types=1);

require dirname( __DIR__ ) . '/wordpress/wp-load.php';

$errors = array();
$posts  = array();

try {
	$source = wp_insert_post( array( 'post_type' => 'course', 'post_status' => 'publish', 'post_title' => 'Оригінальний курс fixture' ), true );
	$posts[] = $source;

	update_field( 'course_short_description', 'Опис оригіналу fixture', $source );
	update_field( 'course_age_min', 9, $source );
	update_field( 'course_age_max', 11, $source );
	update_field( 'course_external_id', 'tilda-fixture-123', $source );
	wp_set_object_terms( $source, 'english', 'course_direction' );

	$image_id = wp_insert_attachment( array( 'post_title' => 'fixture image', 'post_mime_type' => 'image/png', 'post_status' => 'inherit' ), false, $source );
	update_post_meta( $source, '_thumbnail_id', (int) $image_id );

	$duplicate = Logika\Core\PostDuplicator::duplicate( get_post( $source ) );
	$posts[]   = $duplicate;

	if ( 'draft' !== get_post_status( $duplicate ) ) {
		$errors[] = 'A duplicated course must start as a draft.';
	}
	if ( 'Опис оригіналу fixture' !== get_field( 'course_short_description', $duplicate ) ) {
		$errors[] = 'Duplicating a course did not copy its ACF fields.';
	}
	if ( 9 !== (int) get_field( 'course_age_min', $duplicate ) || 11 !== (int) get_field( 'course_age_max', $duplicate ) ) {
		$errors[] = 'Duplicating a course did not copy its age range.';
	}
	if ( '' !== (string) get_field( 'course_external_id', $duplicate ) ) {
		$errors[] = 'Duplicating a course must not copy the external import ID.';
	}
	if ( (int) $image_id !== get_post_thumbnail_id( $duplicate ) ) {
		$errors[] = 'Duplicating a course did not copy its featured image.';
	}
	if ( ! has_term( 'english', 'course_direction', $duplicate ) ) {
		$errors[] = 'Duplicating a course did not copy its taxonomy terms.';
	}
	if ( $source !== (int) get_post_meta( $duplicate, '_logika_duplicated_from', true ) ) {
		$errors[] = 'Duplicating a course did not record its source post.';
	}
} finally {
	foreach ( $posts as $post_id ) {
		if ( is_numeric( $post_id ) ) {
			wp_delete_post( (int) $post_id, true );
		}
	}
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Duplicating a course produces a ready-to-edit draft with fields, terms and image intact.\n";
