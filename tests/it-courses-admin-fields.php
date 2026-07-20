<?php

declare(strict_types=1);

$root       = dirname( __DIR__ );
$page_group = json_decode( (string) file_get_contents( $root . '/wordpress/wp-content/plugins/logika-core/acf-json/group_logika_page_it_courses.json' ), true, 512, JSON_THROW_ON_ERROR );
$course     = json_decode( (string) file_get_contents( $root . '/wordpress/wp-content/plugins/logika-core/acf-json/group_logika_course.json' ), true, 512, JSON_THROW_ON_ERROR );
$reviews    = json_decode( (string) file_get_contents( $root . '/wordpress/wp-content/plugins/logika-core/acf-json/group_logika_testimonials_images.json' ), true, 512, JSON_THROW_ON_ERROR );

$keys = static fn( array $group ): array => array_column( $group['fields'], 'key' );
$page = $keys( $page_group );
$base = $keys( $course );

if ( array_search( 'field_it_courses_tab_sections', $page, true ) >= array_search( 'field_it_courses_catalog_cards', $page, true ) || array_search( 'field_it_courses_tab_reviews', $page, true ) >= array_search( 'field_it_courses_reviews_section', $page, true ) || array_search( 'field_it_courses_reviews_section', $page, true ) >= array_search( 'field_it_courses_featured_reviews', $page, true ) || array_search( 'field_course_tab_hero', $base, true ) >= array_search( 'field_course_visual_variant', $base, true ) || array_search( 'field_course_tab_hero', $base, true ) >= array_search( 'field_course_hero_benefits', $base, true ) ) {
	fwrite( STDERR, 'IT Courses admin fields are not grouped by their visible section.' . PHP_EOL );
	exit( 1 );
}

if ( empty( $reviews['active'] ) || in_array( 'field_it_courses_testimonials_image_1', $page, true ) || in_array( 'field_it_courses_reviews_title', $page, true ) ) {
	fwrite( STDERR, 'IT Courses must use the shared local reviews controls without legacy duplicates.' . PHP_EOL );
	exit( 1 );
}

echo 'IT Courses admin fields are grouped by editable section.' . PHP_EOL;
