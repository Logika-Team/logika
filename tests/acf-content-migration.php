<?php

declare(strict_types=1);

require dirname( __DIR__ ) . '/wordpress/wp-load.php';

if ( ! class_exists( Logika\Core\ContentMigration::class ) ) {
	fwrite( STDERR, "ACF content migration service is missing.\n" );
	exit( 1 );
}

$marker     = 'theme/assets/img/about/hero-characters.png';
$before_ids = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'fields' => 'ids', 'posts_per_page' => -1, 'meta_key' => '_logika_source_path', 'meta_value' => $marker ) );
$page_id    = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'draft', 'post_title' => 'Migration fixture' ) );
$errors     = array();

try {
	update_field( 'about_hero_title', 'Редакторське значення fixture', $page_id );
	update_field( 'about_benefits_title', 'About ACF benefits title', $page_id );
	update_post_meta( $page_id, 'about_page_texts', 1 );
	update_post_meta( $page_id, 'about_page_texts_0_source', 'Чому тисячі батьків обирають Logika' );
	update_post_meta( $page_id, 'about_page_texts_0_value', 'Редакторська назва переваг' );

	$dry = Logika\Core\ContentMigration::migratePage( 'about', $page_id, true );
	if ( empty( $dry['dry_run'] ) || empty( $dry['changed'] ) ) {
		$errors[] = 'Migration dry-run writes data or reports no planned changes.';
	}

	$first  = Logika\Core\ContentMigration::migratePage( 'about', $page_id, false );
	$second = Logika\Core\ContentMigration::migratePage( 'about', $page_id, false );
	if ( empty( $first['changed'] ) || 0 !== (int) $second['changed'] ) {
		$errors[] = 'Migration is not idempotent.';
	}
	if ( 'Редакторське значення fixture' !== get_field( 'about_hero_title', $page_id ) ) {
		$errors[] = 'Migration overwrites editor content.';
	}
	if ( 'Редакторська назва переваг' !== get_field( 'about_benefits_title', $page_id ) ) {
		$errors[] = 'Migration does not preserve a known legacy editor replacement.';
	}
	if ( ! get_field( 'about_directions_items', $page_id ) ) {
		$errors[] = 'Migration does not create a complete source-preserving payload.';
	}
	$image_id = (int) get_field( 'about_hero_image', $page_id );
	if ( ! $image_id || $marker !== get_post_meta( $image_id, '_logika_source_path', true ) ) {
		$errors[] = 'Migration does not import images with a stable source marker.';
	}
	if ( 1 !== count( get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'fields' => 'ids', 'posts_per_page' => -1, 'meta_key' => '_logika_source_path', 'meta_value' => $marker ) ) ) ) {
		$errors[] = 'Migration duplicates source assets.';
	}
	if ( 1 !== (int) get_post_meta( $page_id, 'about_page_texts', true ) ) {
		$errors[] = 'Migration removes legacy rollback meta.';
	}
} finally {
	wp_delete_post( $page_id, true );
	if ( ! $before_ids ) {
		foreach ( get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'fields' => 'ids', 'posts_per_page' => -1, 'meta_key' => '_logika_source_path', 'meta_value' => $marker ) ) as $attachment_id ) {
			wp_delete_attachment( (int) $attachment_id, true );
		}
	}
}

$legacy_names = array( 'about_page_texts', 'it_courses_page_texts', 'english_courses_page_texts', 'faq_page_texts', 'media_center_page_texts' );
foreach ( glob( dirname( __DIR__ ) . '/wordpress/wp-content/plugins/logika-core/acf-json/*.json' ) ?: array() as $file ) {
	$json = (string) file_get_contents( $file );
	foreach ( $legacy_names as $legacy_name ) {
		if ( str_contains( $json, '"name": "' . $legacy_name . '"' ) ) {
			$errors[] = "Legacy field {$legacy_name} is still visible in Local JSON.";
		}
	}
}

$faq_page = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'draft', 'post_title' => 'FAQ migration fixture' ) );
$old_faq_image = wp_insert_attachment( array( 'post_title' => 'Old FAQ image', 'post_mime_type' => 'image/svg+xml', 'post_status' => 'inherit' ), '', $faq_page );
try {
	update_post_meta( $old_faq_image, '_logika_source_path', 'theme/assets/img/faq/faq-left-bg.svg' );
	update_field( 'faq_page_hero_image', $old_faq_image, $faq_page );
	Logika\Core\ContentMigration::migratePage( 'faq', $faq_page, false );
	$new_faq_image = (int) get_field( 'faq_page_hero_image', $faq_page );
	if ( 'theme/assets/img/faq/faq-image.svg' !== get_post_meta( $new_faq_image, '_logika_source_path', true ) ) {
		$errors[] = 'Migration preserves the known incorrect FAQ hero placeholder.';
	}
} finally {
	wp_delete_post( $faq_page, true );
	wp_delete_attachment( $old_faq_image, true );
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "ACF content migration is safe, asset-aware and idempotent.\n";
