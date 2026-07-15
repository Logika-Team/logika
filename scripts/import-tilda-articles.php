<?php

declare(strict_types=1);

require dirname( __DIR__ ) . '/wordpress/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$source_root = rtrim( (string) ( getenv( 'TILDA_EXPORT_DIR' ) ?: '/tmp/logika-tilda-export' ), '/' );

if ( ! is_dir( $source_root . '/files' ) || ! is_dir( $source_root . '/images' ) ) {
	fwrite( STDERR, "Не знайдено експорт Tilda: {$source_root}\n" );
	exit( 1 );
}

$articles = array(
	'digitalhygiene'             => 17200052,
	'top10itprofessions'         => 17413646,
	'specialtiesanduniversities' => 17595370,
	'vision'                     => 17804242,
	'studyingforchildren'        => 17962761,
	'digitaletiquette'           => 18148474,
	'laptopforprogramming'       => 18693160,
	'kidsandphones'              => 18868824,
	'parentalcontrol'            => 19057974,
	'appsforprogramming'         => 19366093,
	'concentration'              => 22182722,
	'developmentofyoungprogrammers' => 22732439,
	'videogames'                 => 23015383,
);

function logika_tilda_article_record( string $html, string $title ): string {
	$title_offset = strpos( $html, $title );
	if ( false !== $title_offset ) {
		$record_offset = strrpos( substr( $html, 0, $title_offset ), '<div id="rec' );
		$next_record   = strpos( $html, '<div id="rec', $title_offset );
		if ( false !== $record_offset ) {
			return substr( $html, $record_offset, ( false === $next_record ? strlen( $html ) : $next_record ) - $record_offset );
		}
	}

	preg_match_all( '~<div id="rec[^\"]+"[^>]*data-record-type="396"~u', $html, $records, PREG_OFFSET_CAPTURE );
	$article_record = '';
	foreach ( $records[0] as $record ) {
		$next_record = strpos( $html, '<div id="rec', $record[1] + 1 );
		$candidate   = substr( $html, $record[1], ( false === $next_record ? strlen( $html ) : $next_record ) - $record[1] );
		if ( strlen( $candidate ) > strlen( $article_record ) ) {
			$article_record = $candidate;
		}
	}

	return $article_record;
}

function logika_tilda_article_elements( string $record ): array {
	$elements = array();
	preg_match_all( '~<div class=.t396__elem[^>]*data-elem-type=.text.[^>]*>\s*<div class=.tn-atom.field=.tn_text_[^>]*>(.*?)</div>~su', $record, $matches, PREG_SET_ORDER );

	foreach ( $matches as $match ) {
		$text = trim( wp_kses( html_entity_decode( $match[1], ENT_QUOTES | ENT_HTML5, 'UTF-8' ), array( 'a' => array( 'href' => true, 'target' => true ), 'br' => array(), 'em' => array(), 'strong' => array(), 'b' => array() ) ) );
		if ( '' === wp_strip_all_tags( $text ) || ! preg_match( '~data-field-top-value=["\']([0-9]+)["\']~', $match[0], $top ) ) {
			continue;
		}

		$elements[] = array(
			'type'   => 'text',
			'top'    => (int) $top[1],
			'text'   => $text,
			'strong' => str_contains( $match[1], '<strong>' ),
		);
	}

	preg_match_all( '~<div class=.t396__elem[^>]*data-elem-type=.image.[^>]*>~su', $record, $images, PREG_OFFSET_CAPTURE );
	foreach ( $images[0] as $index => $image ) {
		$segment_end = $images[0][ $index + 1 ][1] ?? strlen( $record );
		$segment     = substr( $record, $image[1], $segment_end - $image[1] );
		if ( ! preg_match( '~data-field-top-value=["\']([0-9]+)["\']~', $image[0], $top ) || ! preg_match_all( '~(?:data-original|src)=["\']([^"\']+)["\']~', $segment, $paths ) ) {
			continue;
		}

		$path = str_replace( '-__resize__20x__', '', (string) end( $paths[1] ) );
		if ( ! is_string( $path ) || ! preg_match( '~^images/.+\.(?:jpe?g|png|webp)$~i', $path ) ) {
			continue;
		}

		$elements[] = array( 'type' => 'image', 'top' => (int) $top[1], 'path' => $path );
	}

	usort( $elements, static fn( array $left, array $right ): int => $left['top'] <=> $right['top'] );
	return $elements;
}

function logika_tilda_import_image( string $source_root, string $relative_path, int $post_id, string $title ): int {
	$source = realpath( $source_root . '/' . ltrim( $relative_path, '/' ) );
	$root   = realpath( $source_root );
	if ( false === $source || false === $root || ! str_starts_with( $source, $root . DIRECTORY_SEPARATOR ) || ! is_file( $source ) ) {
		return 0;
	}

	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'meta_key'       => 'logika_tilda_image_source',
			'meta_value'     => $relative_path,
		)
	);
	if ( $existing ) {
		wp_update_post( array( 'ID' => $existing[0]->ID, 'post_parent' => $post_id ) );
		return (int) $existing[0]->ID;
	}

	$filetype = wp_check_filetype( $source );
	if ( empty( $filetype['type'] ) || ! str_starts_with( (string) $filetype['type'], 'image/' ) ) {
		return 0;
	}
	$size = wp_getimagesize( $source );
	if ( ! is_array( $size ) || $size[0] < 100 || $size[1] < 100 ) {
		return 0;
	}

	$uploads = wp_upload_dir();
	if ( ! empty( $uploads['error'] ) ) {
		return 0;
	}

	$filename = wp_unique_filename( $uploads['path'], basename( $source ) );
	$target   = trailingslashit( $uploads['path'] ) . $filename;
	if ( ! copy( $source, $target ) ) {
		return 0;
	}

	$attachment_id = wp_insert_attachment(
		array(
			'post_mime_type' => $filetype['type'],
			'post_title'     => $title,
			'post_status'    => 'inherit',
		),
		$target,
		$post_id
	);
	if ( is_wp_error( $attachment_id ) ) {
		@unlink( $target );
		return 0;
	}

	update_post_meta( $attachment_id, 'logika_tilda_image_source', $relative_path );
	wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $target ) );
	return (int) $attachment_id;
}

function logika_tilda_empty_placeholder( WP_Post $post ): bool {
	return '' === trim( $post->post_content )
		&& '' === trim( $post->post_excerpt )
		&& ! has_post_thumbnail( $post )
		&& '' === (string) get_post_meta( $post->ID, 'logika_tilda_article_source', true );
}

function logika_tilda_article_payload( array $elements, string $title, string $source_root, int $post_id ): array {
	$body       = array();
	$tags       = array();
	$summary    = '';
	$cover_id   = 0;
	$date       = '';
	$text_items = array_values( array_filter( $elements, static fn( array $element ): bool => 'text' === $element['type'] ) );

	foreach ( $elements as $index => $element ) {
		if ( 'text' === $element['type'] ) {
			$plain = trim( wp_strip_all_tags( $element['text'] ) );
			if ( $plain === $title || preg_match( '~^\d{2}\.\d{2}\.\d{4}$~', $plain ) ) {
				$date = preg_match( '~^\d{2}\.\d{2}\.\d{4}$~', $plain ) ? $plain : $date;
				continue;
			}
			if ( $element['top'] < 500 ) {
				continue;
			}
			if ( str_starts_with( $plain, '#' ) ) {
				$tags[] = trim( str_replace( '_', ' ', ltrim( $plain, '#' ) ) );
				continue;
			}

			$next = $text_items[ array_search( $element, $text_items, true ) + 1 ] ?? null;
			$is_heading = $element['strong'] || ( mb_strlen( $plain ) <= 100 && is_array( $next ) && mb_strlen( trim( wp_strip_all_tags( $next['text'] ) ) ) > 120 );
			$body[] = $is_heading ? '<h2>' . $element['text'] . '</h2>' : '<p>' . $element['text'] . '</p>';
			if ( '' === $summary && ! $is_heading ) {
				$summary = $plain;
			}
			continue;
		}

		$image_id = logika_tilda_import_image( $source_root, $element['path'], $post_id, $title );
		if ( $image_id <= 0 ) {
			continue;
		}
		if ( $element['top'] < 500 && $cover_id <= 0 ) {
			$cover_id = $image_id;
			continue;
		}
		$url = wp_get_attachment_image_url( $image_id, 'full' );
		if ( $url ) {
			$body[] = '<figure><img src="' . esc_url( $url ) . '" alt="' . esc_attr( $title ) . '" loading="lazy"></figure>';
		}
	}

	return array( 'content' => implode( "\n", $body ), 'cover_id' => $cover_id, 'date' => $date, 'summary' => $summary, 'tags' => array_filter( array_unique( $tags ) ) );
}

$created = 0;
$updated = 0;
$skipped = 0;

foreach ( $articles as $slug => $page_id ) {
	$html = file_get_contents( $source_root . '/files/page' . $page_id . 'body.html' );
	if ( false === $html || ! preg_match( '~<title>([^<]+)~u', (string) file_get_contents( $source_root . '/page' . $page_id . '.html' ), $title_match ) ) {
		++$skipped;
		fwrite( STDERR, "Не вдалося прочитати статтю Tilda: {$slug}\n" );
		continue;
	}

	$title   = trim( html_entity_decode( $title_match[1], ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
	$record  = logika_tilda_article_record( $html, $title );
	$source  = 'tilda:article:' . $page_id;
	$matches = get_posts( array( 'post_type' => 'post', 'post_status' => 'any', 'posts_per_page' => 1, 'meta_key' => 'logika_tilda_article_source', 'meta_value' => $source ) );
	$post     = current( $matches );
	$slug_post = get_page_by_path( $slug, OBJECT, 'post' );
	if ( $post instanceof WP_Post && $post->ID !== ( $slug_post instanceof WP_Post ? $slug_post->ID : 0 ) && $slug_post instanceof WP_Post && logika_tilda_empty_placeholder( $slug_post ) ) {
		wp_delete_post( $post->ID, true );
		$post = $slug_post;
	} elseif ( ! $post instanceof WP_Post && $slug_post instanceof WP_Post && logika_tilda_empty_placeholder( $slug_post ) ) {
		$post = $slug_post;
	}
	$inserted = $post instanceof WP_Post ? $post->ID : wp_insert_post( array( 'post_type' => 'post', 'post_name' => $slug, 'post_title' => $title, 'post_status' => 'publish' ), true );
	$post_id  = is_wp_error( $inserted ) ? 0 : (int) $inserted;
	if ( $post_id <= 0 || '' === $record ) {
		++$skipped;
		fwrite( STDERR, "Не вдалося підготувати статтю Tilda: {$slug}\n" );
		continue;
	}

	$payload = logika_tilda_article_payload( logika_tilda_article_elements( $record ), $title, $source_root, $post_id );
	if ( '' === $payload['content'] || $payload['cover_id'] <= 0 ) {
		++$skipped;
		fwrite( STDERR, "У статті Tilda бракує тексту або обкладинки: {$slug}\n" );
		continue;
	}

	$date = DateTimeImmutable::createFromFormat( '!d.m.Y', $payload['date'] ) ?: new DateTimeImmutable();
	wp_update_post(
		array(
			'ID'           => $post_id,
			'post_name'    => $slug,
			'post_title'   => $title,
			'post_content' => wp_kses_post( $payload['content'] ),
			'post_excerpt' => wp_trim_words( $payload['summary'], 32, '…' ),
			'post_date'    => $date->format( 'Y-m-d 12:00:00' ),
			'post_status'  => 'publish',
		)
	);
	update_post_meta( $post_id, 'logika_tilda_article_source', $source );
	set_post_thumbnail( $post_id, $payload['cover_id'] );
	update_field( 'article_cover_image', $payload['cover_id'], $post_id );
	update_field( 'post_answer_first_summary', $payload['summary'], $post_id );
	wp_set_post_tags( $post_id, $payload['tags'], false );

	if ( $post instanceof WP_Post ) {
		++$updated;
	} else {
		++$created;
	}
}

echo "Статті Tilda: створено {$created}, оновлено {$updated}, пропущено {$skipped}.\n";
