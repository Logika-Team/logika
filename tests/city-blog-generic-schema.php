<?php

declare(strict_types=1);

$root   = dirname( __DIR__ ) . '/wordpress/wp-content/plugins/logika-core/acf-json/';
$errors = array();

$required = array(
	'group_logika_city' => array( 'city_home_hero_title' => 'text', 'city_home_hero_text' => 'textarea', 'city_home_hero_image' => 'image', 'city_home_locations_title' => 'text', 'city_home_cta_title' => 'text', 'city_home_media_title' => 'text', 'city_home_faq_title' => 'text' ),
	'group_logika_page_media_center' => array( 'media_center_blog_title' => 'text', 'media_center_blog_sort_new_label' => 'text', 'media_center_blog_sort_old_label' => 'text', 'media_center_blog_years_label' => 'text' ),
);
foreach ( $required as $key => $fields ) {
	$group = json_decode( (string) file_get_contents( $root . $key . '.json' ), true, 512, JSON_THROW_ON_ERROR );
	$by_name = array_column( $group['fields'] ?? array(), null, 'name' );
	foreach ( $fields as $name => $type ) {
		if ( ( $by_name[ $name ]['type'] ?? '' ) !== $type ) {
			$errors[] = "{$key} is missing {$name}:{$type}.";
		}
	}
}

$generic_file = $root . 'group_logika_generic_page.json';
if ( ! is_file( $generic_file ) ) {
	$errors[] = 'Generic Page ACF group is missing.';
} else {
	$generic = json_decode( (string) file_get_contents( $generic_file ), true, 512, JSON_THROW_ON_ERROR );
	$builder = array_values( array_filter( $generic['fields'] ?? array(), static fn( array $field ): bool => 'generic_sections' === ( $field['name'] ?? '' ) ) )[0] ?? array();
	$layouts = array_column( array_values( $builder['layouts'] ?? array() ), 'name' );
	foreach ( array( 'hero', 'rich_text', 'gallery', 'course_selection', 'reviews', 'faq', 'partners', 'school_map', 'cta' ) as $layout ) {
		if ( ! in_array( $layout, $layouts, true ) ) {
			$errors[] = "Generic builder is missing {$layout}.";
		}
	}
}

foreach ( array( 'front-page.php' => 'renderHome', 'single-city.php' => 'renderHome', 'templates/page-builder.php' => 'Logika_Theme_Generic_Page::render', 'templates/page-blog.php' => 'media_center_blog_title' ) as $file => $contract ) {
	$path = dirname( __DIR__ ) . '/wordpress/wp-content/themes/logika-theme/' . $file;
	$contents = is_file( $path ) ? (string) file_get_contents( $path ) : '';
	if ( ! str_contains( $contents, $contract ) ) {
		$errors[] = "{$file} is missing {$contract}.";
	}
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "City, Blog and generic Pages expose the approved ACF architecture.\n";
