<?php

declare(strict_types=1);

require dirname( __DIR__ ) . '/wordpress/wp-load.php';

$file   = dirname( __DIR__ ) . '/wordpress/wp-content/plugins/logika-core/acf-json/group_logika_page_media_center.json';
$group  = json_decode( (string) file_get_contents( $file ), true, 512, JSON_THROW_ON_ERROR );
$actual = array();
$tab    = '';

foreach ( $group['fields'] ?? array() as $field ) {
	if ( 'tab' === ( $field['type'] ?? '' ) ) {
		$tab = (string) $field['label'];
		$actual[ $tab ] = array();
	} elseif ( $tab && ! empty( $field['name'] ) ) {
		$actual[ $tab ][] = $field['name'];
	}
}

$expected = array(
	'Перший екран' => array( 'media_center_hero_title', 'media_center_hero_image', 'media_center_hero_background_image', 'media_center_tags', 'media_center_logirace_link', 'media_center_hackathon_link' ),
	'Переваги'    => array( 'media_center_benefits_title', 'media_center_benefits' ),
	'Новини'      => array( 'media_center_news_title', 'media_center_news' ),
	'Статті'      => array( 'media_center_articles_title', 'media_center_featured_post', 'media_center_articles' ),
	'Пропозиції'  => array( 'media_center_discount_title', 'media_center_offers' ),
	'Заклик до дії' => array( 'media_center_cta_title', 'media_center_cta_subtitle', 'media_center_cta_image' ),
	'FAQ'         => array( 'media_center_faq_title', 'media_center_featured_faq' ),
	'Медіаблог'   => array( 'media_center_blog_title', 'media_center_blog_sort_new_label', 'media_center_blog_sort_old_label', 'media_center_blog_years_label' ),
);

if ( $expected !== $actual ) {
	fwrite( STDERR, "Media Center fields must be visible inside their logical tabs.\n" );
	exit( 1 );
}

$page_id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'draft', 'post_title' => 'Media Center editor fixture' ) );
try {
	Logika\Core\ContentMigration::migratePage( 'media-center', $page_id, false );
	if ( ! get_field( 'media_center_featured_post', $page_id ) ) {
		fwrite( STDERR, "Media Center migration must populate the featured material.\n" );
		exit( 1 );
	}
	if ( 0 !== (int) Logika\Core\ContentMigration::migratePage( 'media-center', $page_id, false )['changed'] ) {
		fwrite( STDERR, "Media Center migration must be idempotent.\n" );
		exit( 1 );
	}
	update_field( 'media_center_logirace_link', 'https://example.test/logirace', $page_id );
	update_field( 'media_center_hackathon_link', 'https://example.test/hackathon', $page_id );
	ob_start();
	Logika_Theme_Fixed_Page::render( 'media-center', $page_id );
	$markup = (string) ob_get_clean();
	foreach ( array( 'https://example.test/logirace', 'https://example.test/hackathon' ) as $url ) {
		if ( ! str_contains( $markup, 'href="' . $url . '"' ) ) {
			fwrite( STDERR, "Media Center promo links must be editable through ACF.\n" );
			exit( 1 );
		}
	}
} finally {
	wp_delete_post( $page_id, true );
}

echo "Media Center fields are visible inside logical editor tabs.\n";
