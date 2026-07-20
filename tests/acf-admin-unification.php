<?php

declare(strict_types=1);

$root = dirname( __DIR__ );
$json = $root . '/wordpress/wp-content/plugins/logika-core/acf-json/';

$read = static fn( string $file ): array => json_decode( (string) file_get_contents( $json . $file ), true, 512, JSON_THROW_ON_ERROR );
$field = static function ( array $group, string $name ): ?array {
	foreach ( $group['fields'] as $field ) {
		if ( $name === ( $field['name'] ?? '' ) ) {
			return $field;
		}
	}

	return null;
};

$errors  = array();
$global  = $read( 'group_logika_global.json' );
$reviews = $read( 'group_logika_review.json' );
$section = $read( 'group_logika_testimonials_images.json' );
$course  = $read( 'group_logika_course.json' );
$camp    = $read( 'group_logika_camp.json' );

$title = $field( $global, 'global_reviews_title' );
$gallery = $field( $global, 'global_reviews_gallery' );
if ( ! $title || ! $gallery || 'gallery' !== $gallery['type'] || 4 !== (int) $gallery['max'] || 'id' !== $gallery['return_format'] || 'medium' !== $gallery['preview_size'] ) {
	$errors[] = 'Global Options must own one editable reviews title and four-image gallery.';
}
if ( empty( $section['active'] ) || ! $field( $section, 'reviews_section_title' ) || 'gallery' !== ( $field( $section, 'reviews_section_gallery' )['type'] ?? '' ) ) {
	$errors[] = 'The reusable reviews section must contain editable local title and gallery controls.';
}
if ( ! $field( $course, 'course_visual_variant' ) || ! $field( $course, 'course_hero_benefits' ) ) {
	$errors[] = 'Course hero controls must be editable through ACF.';
}
foreach ( array( 'field_course_tab_technical', 'field_camp_tab_basics', 'field_camp_tab_technical' ) as $key ) {
	$groups = str_contains( $key, 'course' ) ? $course : $camp;
	if ( ! in_array( $key, array_column( $groups['fields'], 'key' ), true ) ) {
		$errors[] = "{$key} is required to separate editor content from technical fields.";
	}
}

$tabs = array_values( array_map( static fn( array $item ): string => 'tab' === ( $item['type'] ?? '' ) ? (string) $item['label'] : '', $reviews['fields'] ) );
foreach ( array( 'Автор', 'Вміст', 'Зв’язки', 'Публікація', 'Технічні дані' ) as $tab ) {
	if ( ! in_array( $tab, $tabs, true ) ) {
		$errors[] = "Review editor is missing the {$tab} tab.";
	}
}

foreach ( glob( $json . 'group_logika_*.json' ) as $file ) {
	$group = json_decode( (string) file_get_contents( $file ), true, 512, JSON_THROW_ON_ERROR );
	if ( empty( $group['active'] ) ) {
		continue;
	}
	foreach ( $group['fields'] as $item ) {
		if ( preg_match( '/^testimonials_image_[1-4]$/', (string) ( $item['name'] ?? '' ) ) ) {
			$errors[] = basename( $file ) . ' still exposes duplicated testimonial images.';
		}
	}
}

foreach ( array( 'home', 'page_about', 'page_it_courses', 'page_english_courses', 'page_faq', 'camp_archive', 'city', 'course', 'camp' ) as $name ) {
	$group = $read( "group_logika_{$name}.json" );
	$clone = array_filter( $group['fields'], static fn( array $item ): bool => 'clone' === ( $item['type'] ?? '' ) && in_array( 'group_logika_testimonials_images', (array) ( $item['clone'] ?? array() ), true ) );
	if ( ! $clone ) {
		$errors[] = "group_logika_{$name} must expose the shared reviews section in its editor.";
	}
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "ACF admin uses shared reviews controls with local overrides.\n";
