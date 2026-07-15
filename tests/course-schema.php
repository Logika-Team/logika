<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$course = get_page_by_path( 'test-course', OBJECT, 'course' );
if ( ! $course || ! class_exists( 'Logika_Theme_Course_Schema' ) ) {
	fwrite( STDERR, "Course schema is not available.\n" );
	exit( 1 );
}

update_field( 'course_short_description', 'Опис для Course schema.', $course->ID );
update_field( 'course_age_min', 9, $course->ID );
update_field( 'course_age_max', 12, $course->ID );
$schema = Logika_Theme_Course_Schema::build( $course->ID );

if ( 'Course' !== $schema['@type'] || 'Опис для Course schema.' !== $schema['description'] || '9-12' !== $schema['typicalAgeRange'] ) {
	fwrite( STDERR, "Course schema does not use ACF data.\n" );
	exit( 1 );
}

echo "Course schema uses ACF data.\n";
