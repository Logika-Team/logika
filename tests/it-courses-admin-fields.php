<?php

declare(strict_types=1);

$root       = dirname( __DIR__ );
$page_group = json_decode( (string) file_get_contents( $root . '/wordpress/wp-content/plugins/logika-core/acf-json/group_logika_page_it_courses.json' ), true, 512, JSON_THROW_ON_ERROR );
$course     = json_decode( (string) file_get_contents( $root . '/wordpress/wp-content/plugins/logika-core/acf-json/group_logika_course.json' ), true, 512, JSON_THROW_ON_ERROR );
$reviews    = json_decode( (string) file_get_contents( $root . '/wordpress/wp-content/plugins/logika-core/acf-json/group_logika_testimonials_images.json' ), true, 512, JSON_THROW_ON_ERROR );

$keys = static fn( array $group ): array => array_column( $group['fields'], 'key' );
$page = $keys( $page_group );
$base = $keys( $course );

if ( array_search( 'field_it_courses_tab_sections', $page, true ) >= array_search( 'field_it_courses_catalog_cards', $page, true ) || array_search( 'field_it_courses_tab_texts', $page, true ) >= array_search( 'field_it_courses_reviews_title', $page, true ) || array_search( 'field_it_courses_testimonials_image_1', $page, true ) <= array_search( 'field_it_courses_reviews_title', $page, true ) || array_search( 'field_course_tab_basics', $base, true ) >= array_search( 'field_course_card_image', $base, true ) ) {
	fwrite( STDERR, 'IT Courses admin fields are not grouped by their visible section.' . PHP_EOL );
	exit( 1 );
}

$page_rules = $reviews['location'][0] ?? array();
if ( ! in_array( array( 'param' => 'page_template', 'operator' => '!=', 'value' => 'templates/page-it-courses.php' ), $page_rules, true ) ) {
	fwrite( STDERR, 'Shared review images still appear before IT Courses fields.' . PHP_EOL );
	exit( 1 );
}

echo 'IT Courses admin fields are grouped by editable section.' . PHP_EOL;
