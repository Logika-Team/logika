<?php

declare(strict_types=1);

$root     = dirname(__DIR__);
$archive  = (string) file_get_contents( $root . '/wordpress/wp-content/plugins/logika-core/acf-json/group_logika_camp_archive.json' );
$camp     = (string) file_get_contents( $root . '/wordpress/wp-content/plugins/logika-core/acf-json/group_logika_camp.json' );
$renderer = (string) file_get_contents( $root . '/wordpress/wp-content/themes/logika-theme/src/PageContent.php' );
$modal    = (string) file_get_contents( $root . '/wordpress/wp-content/themes/logika-theme/template-parts/components/camp-modal.php' );
$page     = (string) file_get_contents( $root . '/wordpress/wp-content/themes/logika-theme/source-pages/camps.php' );
$errors   = array();

foreach ( array( 'camp_archive_formats', 'camp_card_dates', 'camp_extra_sections' ) as $field ) {
	if ( ! str_contains( $archive . $camp, '"name": "' . $field . '"' ) ) {
		$errors[] = "Tilda summer camps require {$field} in ACF.";
	}
}

foreach ( array( "get_field( 'camp_archive_formats'", 'camp_card_dates', 'camp_card_description', 'get_permalink( $camp_id )' ) as $marker ) {
	if ( ! str_contains( $modal, $marker ) ) {
		$errors[] = "Camp modal is missing {$marker}.";
	}
}

if ( ! str_contains( $renderer, 'applyCampExtraSections' ) ) {
	$errors[] = 'Camp detail renderer is missing applyCampExtraSections.';
}

foreach ( array( 'camp-formats__item-season', 'Літо', 'Осінь', 'Зима', 'Весна', 'data-path="camps"' ) as $marker ) {
	if ( ! str_contains( $page, $marker ) ) {
		$errors[] = "Camp season selector is missing {$marker}.";
	}
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

require $root . '/wordpress/wp-load.php';

$slugs = array( 'greece-2026', 'emily-resort-2026', 'carpathians-2026', 'city-camps-2026' );
$ids   = array();
foreach ( $slugs as $slug ) {
	$post = get_page_by_path( $slug, OBJECT, 'camp' );
	if ( ! $post || ! get_field( 'camp_card_image', $post->ID ) || ! get_field( 'camp_gallery', $post->ID ) ) {
		$errors[] = "Tilda camp {$slug} is missing imported editable media.";
		continue;
	}
	$ids[] = (int) $post->ID;
}

if ( $ids !== array_values( array_map( 'intval', (array) get_field( 'camp_archive_formats', 'camp_archive' ) ) ) ) {
	$errors[] = 'Camp archive must keep the four Tilda formats in source order.';
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Tilda summer camp formats contract is valid.\n";
