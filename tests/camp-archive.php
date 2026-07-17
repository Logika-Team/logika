<?php

declare(strict_types=1);

require dirname( __DIR__ ) . '/wordpress/wp-load.php';

$fields = array( 'camp_archive_hero_title', 'camp_archive_benefits_title', 'camp_archive_benefits', 'camp_archive_formats_title', 'camp_archive_booking_title', 'camp_archive_history_title', 'camp_archive_history_text', 'camp_archive_gallery', 'camp_archive_reviews', 'camp_archive_faq' );
$before = array();
$posts  = array();
$errors = array();
foreach ( $fields as $field ) {
	$before[ $field ] = get_field( $field, 'camp_archive' );
}

try {
	$active   = wp_insert_post( array( 'post_type' => 'camp', 'post_status' => 'publish', 'post_title' => 'Активний табір fixture' ) );
	$inactive = wp_insert_post( array( 'post_type' => 'camp', 'post_status' => 'publish', 'post_title' => 'Неактивний табір fixture' ) );
	$draft    = wp_insert_post( array( 'post_type' => 'camp', 'post_status' => 'draft', 'post_title' => 'Чернетка табору fixture' ) );
	$posts    = array( $active, $inactive, $draft );
	update_field( 'camp_is_active', 1, $active );
	update_field( 'camp_is_active', 0, $inactive );
	update_field( 'camp_is_active', 1, $draft );
	update_field( 'camp_archive_hero_title', 'Архів таборів fixture', 'camp_archive' );
	update_field( 'camp_archive_benefits_title', 'Переваги fixture', 'camp_archive' );
	update_field( 'camp_archive_benefits', array( array( 'title' => 'Перевага fixture', 'text' => 'Опис переваги' ) ), 'camp_archive' );
	update_field( 'camp_archive_formats_title', 'Формати fixture', 'camp_archive' );
	update_field( 'camp_archive_booking_title', 'Бронювання fixture', 'camp_archive' );
	update_field( 'camp_archive_history_title', 'Історія fixture', 'camp_archive' );
	update_field( 'camp_archive_history_text', '<p>Текст історії fixture.</p>', 'camp_archive' );

	ob_start();
	Logika_Theme_Camp_Archive::render();
	$html = (string) ob_get_clean();
	foreach ( array( 'Архів таборів fixture', 'Перевага fixture', 'Активний табір fixture', 'Бронювання fixture', 'Історія fixture' ) as $expected ) {
		if ( ! str_contains( $html, $expected ) ) {
			$errors[] = "Camp archive is missing {$expected}.";
		}
	}
	foreach ( array( 'Неактивний табір fixture', 'Чернетка табору fixture' ) as $hidden ) {
		if ( str_contains( $html, $hidden ) ) {
			$errors[] = "Camp archive exposes {$hidden}.";
		}
	}
} finally {
	foreach ( $posts as $post_id ) {
		wp_delete_post( (int) $post_id, true );
	}
	foreach ( $before as $field => $value ) {
		update_field( $field, $value, 'camp_archive' );
	}
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Camp archive uses Options and public Camp entities.\n";
