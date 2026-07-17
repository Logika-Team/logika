<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$pages = array(
	'about' => array( 'source' => 'about', 'texts' => 'about_page_texts', 'fields' => array( 'about_benefits_title' => 'Чому тисячі батьків обирають Logika' ) ),
	'it-courses' => array( 'source' => 'it-courses', 'texts' => 'it_courses_page_texts', 'fields' => array( 'it_courses_reviews_title' => 'Довіра, підтверджена результатами' ) ),
	'english-courses' => array( 'source' => 'en-courses', 'texts' => 'english_courses_page_texts', 'fields' => array( 'english_courses_test_text' => 'Допоможемо визначити рівень підготовки, інтереси та формат навчання, щоб дитині було комфортно й цікаво навчатися з перших занять.' ) ),
	'faq' => array( 'source' => 'faq', 'texts' => 'faq_page_texts', 'fields' => array( 'faq_page_cta_title' => 'Підберемо курс саме для вашої дитини!' ) ),
	'media-center' => array( 'source' => 'media-center', 'texts' => 'media_center_page_texts', 'fields' => array( 'media_center_discount_title' => '-10%' ) ),
);
$seeded = 0;

foreach ( $pages as $slug => $config ) {
	$page = get_page_by_path( $slug );
	$path = get_template_directory() . '/source-pages/' . $config['source'] . '.php';

	if ( ! $page || ! is_readable( $path ) ) {
		continue;
	}

	foreach ( $config['fields'] as $field => $value ) {
		if ( '' === (string) get_field( $field, $page->ID ) ) {
			update_field( $field, $value, $page->ID );
		}
	}

	if ( get_field( $config['texts'], $page->ID ) ) {
		continue;
	}

	preg_match_all( '~>([^<>]+)<~', (string) file_get_contents( $path ), $matches );
	$rows = array_values( array_unique( array_filter( array_map( static fn( string $text ): string => trim( $text ), $matches[1] ?? array() ), static fn( string $text ): bool => '' !== $text && ! str_starts_with( $text, '@include(' ) ) ) );

	update_field( $config['texts'], array_map( static fn( string $text ): array => array( 'source' => $text, 'value' => $text ), $rows ), $page->ID );
	$seeded += count( $rows );
}

echo "Seeded {$seeded} page text rows.\n";
