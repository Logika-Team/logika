<?php

declare(strict_types=1);

require dirname( __DIR__ ) . '/wordpress/wp-load.php';

$errors = array();
$posts  = array();

$camps_page = get_page_by_path( 'camps' );
if ( ! $camps_page ) {
	fwrite( STDERR, "The /camps/ page fixture does not exist; cannot test the camp archive.\n" );
	exit( 1 );
}

$before = (array) get_field( 'camp_archive_formats', $camps_page->ID );

try {
	$active   = wp_insert_post( array( 'post_type' => 'camp', 'post_status' => 'publish', 'post_title' => 'Активний табір fixture' ), true );
	$inactive = wp_insert_post( array( 'post_type' => 'camp', 'post_status' => 'publish', 'post_title' => 'Неактивний табір fixture' ), true );
	$draft    = wp_insert_post( array( 'post_type' => 'camp', 'post_status' => 'draft', 'post_title' => 'Чернетка табору fixture' ), true );
	$posts    = array( $active, $inactive, $draft );

	update_field( 'camp_is_active', 1, $active );
	Logika\Core\CampArchiveSync::sync( $active );

	update_field( 'camp_is_active', 0, $inactive );
	Logika\Core\CampArchiveSync::sync( $inactive );

	update_field( 'camp_is_active', 1, $draft );
	Logika\Core\CampArchiveSync::sync( $draft );

	$formats = array_map( 'intval', (array) get_field( 'camp_archive_formats', $camps_page->ID ) );

	if ( ! in_array( $active, $formats, true ) ) {
		$errors[] = 'Publishing an active camp did not add it to camp_archive_formats.';
	}
	if ( in_array( $inactive, $formats, true ) ) {
		$errors[] = 'An inactive camp was added to camp_archive_formats.';
	}
	if ( in_array( $draft, $formats, true ) ) {
		$errors[] = 'A draft camp was added to camp_archive_formats.';
	}

	update_field( 'camp_is_active', 0, $active );
	Logika\Core\CampArchiveSync::sync( $active );
	$formats_after_deactivation = array_map( 'intval', (array) get_field( 'camp_archive_formats', $camps_page->ID ) );
	if ( in_array( $active, $formats_after_deactivation, true ) ) {
		$errors[] = 'Deactivating a camp did not remove it from camp_archive_formats.';
	}
} finally {
	foreach ( $posts as $post_id ) {
		if ( is_numeric( $post_id ) ) {
			wp_delete_post( (int) $post_id, true );
		}
	}
	update_field( 'camp_archive_formats', $before, $camps_page->ID );
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Publishing an active camp syncs it into the /camps/ archive automatically.\n";
