<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';
require dirname(__DIR__) . '/scripts/data/tilda-courses.php';
require dirname(__DIR__) . '/scripts/seed-tilda-courses.php';

$courses = logika_tilda_courses();
$first   = logika_seed_tilda_courses( $courses );
$second  = logika_seed_tilda_courses( $courses );

if ( $first['created'] + $first['updated'] !== 13 || $second['created'] !== 0 || $second['updated'] !== 13 ) {
	fwrite( STDERR, 'Tilda course import is not idempotent.' . PHP_EOL );
	exit( 1 );
}

foreach ( $courses as $course ) {
	$post = get_posts(
		array(
			'post_type'      => 'course',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => 'course_external_id',
			'meta_value'     => $course['external_id'],
		)
	);

	$course_id = (int) ( $post[0] ?? 0 );
	if ( ! $course_id || get_field( 'course_short_description', $course_id ) !== $course['short_description'] || count( (array) get_field( 'course_program', $course_id ) ) !== count( $course['program'] ) ) {
		fwrite( STDERR, "Imported course {$course['slug']} is incomplete." . PHP_EOL );
		exit( 1 );
	}
}

$catalog = get_page_by_path( 'it-courses', OBJECT, 'page' );
$rows    = $catalog ? (array) get_field( 'it_courses_age_categories', $catalog->ID ) : array();
$linked  = array_merge( ...array_map( static fn ( array $row ): array => (array) ( $row['courses'] ?? array() ), $rows ) );
$first_course = (int) ( $rows[0]['courses'][0] ?? 0 );

if ( count( array_unique( array_map( 'intval', $linked ) ) ) < 13 ) {
	fwrite( STDERR, 'The IT courses catalog does not link all imported courses.' . PHP_EOL );
	exit( 1 );
}

if ( 'Комп’ютерна грамотність' !== get_the_title( $first_course ) ) {
	fwrite( STDERR, 'The 7–8 catalog block must begin with Computer Literacy.' . PHP_EOL );
	exit( 1 );
}

$legacy_course = get_page_by_path( 'programming-start', OBJECT, 'course' );
if ( $legacy_course && in_array( (int) $legacy_course->ID, array_map( 'intval', (array) ( $rows[0]['courses'] ?? array() ) ), true ) ) {
	fwrite( STDERR, 'The 7–8 catalog block must not contain the legacy programming-start course.' . PHP_EOL );
	exit( 1 );
}

$python_start = get_page_by_path( 'python-start', OBJECT, 'course' );
ob_start();
Logika_Theme_Source_Markup::renderPage( 'it-course', $python_start ? (int) $python_start->ID : 0 );
$course_markup = (string) ob_get_clean();

if ( ! str_contains( $course_markup, 'Вчимося кодити на Python' ) || ! str_contains( $course_markup, 'Модуль 1.' ) ) {
	fwrite( STDERR, 'An imported course page does not render its Tilda content.' . PHP_EOL );
	exit( 1 );
}

echo 'Tilda course import is idempotent, linked to the catalog, and rendered by the course template.' . PHP_EOL;
