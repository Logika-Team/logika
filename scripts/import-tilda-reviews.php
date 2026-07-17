<?php

declare(strict_types=1);

require dirname( __DIR__ ) . '/wordpress/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$source_root = rtrim( (string) ( getenv( 'TILDA_EXPORT_DIR' ) ?: '/home/sbaikov/Documents/tilda-export/project917000' ), '/' );

if ( ! is_dir( $source_root . '/files' ) || ! is_dir( $source_root . '/images' ) ) {
	fwrite( STDERR, "Не знайдено експорт Tilda: {$source_root}\n" );
	exit( 1 );
}

function logika_tilda_review_attachment( string $source_root, string $relative_path, int $post_id, string $title ): int {
	$source = realpath( $source_root . '/' . ltrim( $relative_path, '/' ) );
	$root   = realpath( $source_root );
	if ( false === $source || false === $root || ! str_starts_with( $source, $root . DIRECTORY_SEPARATOR ) || ! is_file( $source ) ) {
		return 0;
	}

	$existing = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'posts_per_page' => 1, 'meta_key' => 'logika_tilda_image_source', 'meta_value' => $relative_path ) );
	if ( $existing ) {
		wp_update_post( array( 'ID' => $existing[0]->ID, 'post_parent' => $post_id ) );
		return (int) $existing[0]->ID;
	}

	$filetype = wp_check_filetype( $source );
	$size     = wp_getimagesize( $source );
	$uploads  = wp_upload_dir();
	if ( empty( $filetype['type'] ) || ! str_starts_with( (string) $filetype['type'], 'image/' ) || ! is_array( $size ) || $size[0] < 40 || $size[1] < 40 || ! empty( $uploads['error'] ) ) {
		return 0;
	}

	$target = trailingslashit( $uploads['path'] ) . wp_unique_filename( $uploads['path'], basename( $source ) );
	if ( ! copy( $source, $target ) ) {
		return 0;
	}

	$attachment_id = wp_insert_attachment( array( 'post_mime_type' => $filetype['type'], 'post_title' => $title, 'post_status' => 'inherit' ), $target, $post_id );
	if ( is_wp_error( $attachment_id ) ) {
		@unlink( $target );
		return 0;
	}
	update_post_meta( $attachment_id, 'logika_tilda_image_source', $relative_path );
	wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $target ) );
	return (int) $attachment_id;
}

function logika_tilda_review_value( string $html, string $class ): string {
	if ( ! preg_match( '~<div[^>]*class=["\'][^"\']*' . preg_quote( $class, '~' ) . '[^"\']*["\'][^>]*>(.*?)</div>~su', $html, $match ) ) {
		return '';
	}
	return trim( wp_kses_post( html_entity_decode( $match[1], ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ) );
}

function logika_tilda_review_key( string $name, string $text ): string {
	$value = mb_strtolower( trim( wp_strip_all_tags( $name . ' ' . $text ) ) );
	return sha1( (string) preg_replace( '~\s+~u', ' ', $value ) );
}

function logika_tilda_dedupe_reviews(): void {
	$seen = array();
	foreach ( get_posts( array( 'post_type' => 'review', 'post_status' => 'any', 'posts_per_page' => -1, 'orderby' => 'ID', 'order' => 'ASC' ) ) as $review ) {
		if ( ! str_starts_with( (string) get_post_meta( $review->ID, 'review_external_id', true ), 'tilda:' ) ) {
			continue;
		}

		$key = logika_tilda_review_key( (string) get_post_meta( $review->ID, 'review_author_name', true ), (string) get_post_meta( $review->ID, 'review_text', true ) );
		if ( isset( $seen[ $key ] ) ) {
			wp_delete_post( $review->ID, true );
			continue;
		}
		$seen[ $key ] = $review->ID;
	}
}

function logika_tilda_existing_review( string $external_id, string $content_key ): ?WP_Post {
	$matches = get_posts( array( 'post_type' => 'review', 'post_status' => 'any', 'posts_per_page' => 1, 'meta_key' => 'review_external_id', 'meta_value' => $external_id ) );
	if ( $matches ) {
		return $matches[0];
	}

	foreach ( get_posts( array( 'post_type' => 'review', 'post_status' => 'any', 'posts_per_page' => -1 ) ) as $review ) {
		if ( str_starts_with( (string) get_post_meta( $review->ID, 'review_external_id', true ), 'tilda:' ) && $content_key === logika_tilda_review_key( (string) get_post_meta( $review->ID, 'review_author_name', true ), (string) get_post_meta( $review->ID, 'review_text', true ) ) ) {
			return $review;
		}
	}

	return null;
}

$created = 0;
$updated = 0;
$skipped = 0;

logika_tilda_dedupe_reviews();

foreach ( glob( $source_root . '/files/page*body.html' ) ?: array() as $file ) {
	preg_match( '~page(\d+)body\.html$~', $file, $page_match );
	$page_id = $page_match[1] ?? '';
	$html    = file_get_contents( $file );
	if ( ! $page_id || false === $html ) {
		continue;
	}

	$review_context = false;
	foreach ( preg_split( '~(?=<div id=["\']rec\d+["\'])~u', $html ) ?: array() as $record ) {
		$review_context = $review_context || str_contains( mb_strtolower( $record ), 'відгуки' );
		if ( ! $review_context || ! str_contains( $record, 't798__text' ) || ! preg_match( '~<div id=["\']rec(\d+)["\']~', $record, $record_match ) ) {
			continue;
		}

		preg_match_all( '~<div class=["\']t-slds__item[^>]*>.*?(?=<div class=["\']t-slds__item|</ul>)~su', $record, $slides );
		foreach ( $slides[0] as $index => $slide ) {
			$text = logika_tilda_review_value( $slide, 't798__text' );
			$name = wp_strip_all_tags( logika_tilda_review_value( $slide, 't798__title' ) );
			if ( '' === $text || '' === $name ) {
				++$skipped;
				continue;
			}

			$content_key = logika_tilda_review_key( $name, $text );
			$external_id = 'tilda:review:' . $content_key;
			$review = logika_tilda_existing_review( $external_id, $content_key );
			$inserted = $review ? (int) $review->ID : wp_insert_post( array( 'post_type' => 'review', 'post_status' => 'publish', 'post_title' => $name ), true );
			$post_id = is_wp_error( $inserted ) ? 0 : (int) $inserted;
			if ( $post_id <= 0 ) {
				++$skipped;
				continue;
			}

			$photo = 0;
			if ( preg_match( '~<img(?=[^>]*t798__img)[^>]*>~u', $slide, $image ) && preg_match( '~(?:data-original|src)=["\']([^"\']+)~', $image[0], $path ) && str_starts_with( $path[1], 'images/' ) ) {
				$photo = logika_tilda_review_attachment( $source_root, $path[1], $post_id, $name );
			}

			wp_update_post( array( 'ID' => $post_id, 'post_title' => $name, 'post_status' => 'publish' ) );
			update_field( 'review_external_id', $external_id, $post_id );
			update_field( 'review_author_name', $name, $post_id );
			update_field( 'review_author_role', 'Відгук батьків', $post_id );
			update_field( 'review_text', $text, $post_id );
			update_field( 'review_rating', 5, $post_id );
			update_field( 'review_photo', $photo, $post_id );
			update_field( 'review_card_label', 'Відгук батьків', $post_id );
			update_field( 'review_display_order', $index + 1, $post_id );
			update_field( 'review_is_global', 1, $post_id );
			update_field( 'review_is_approved', 1, $post_id );
			$review ? ++$updated : ++$created;
		}
		$review_context = false;
	}
}

logika_tilda_dedupe_reviews();

echo "Відгуки Tilda: створено {$created}, оновлено {$updated}, пропущено {$skipped}.\n";
