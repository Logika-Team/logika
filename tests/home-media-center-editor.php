<?php

declare(strict_types=1);

$group = json_decode( (string) file_get_contents( dirname( __DIR__ ) . '/wordpress/wp-content/plugins/logika-core/acf-json/group_logika_home.json' ), true, 512, JSON_THROW_ON_ERROR );
$keys  = array_column( $group['fields'], 'key' );

if ( array_search( 'field_home_tab_media', $keys, true ) > array_search( 'field_home_media_title', $keys, true ) ) {
	throw new RuntimeException( 'Media Center title must be in the Media Center editor tab.' );
}

foreach ( array( 'field_home_media_text', 'field_home_media_lesson_link', 'field_home_media_archive_link', 'field_home_media_news', 'field_home_media_contest', 'field_home_media_offer', 'field_home_media_discount', 'field_home_media_race', 'field_home_media_posts' ) as $key ) {
	if ( false === array_search( $key, $keys, true ) ) {
		throw new RuntimeException( "Media Center editor is missing {$key}." );
	}
}

echo "Homepage Media Center fields share one editor tab.\n";
