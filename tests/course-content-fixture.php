<?php

declare(strict_types=1);

require dirname(__DIR__) . '/scripts/data/tilda-courses.php';

$courses = logika_tilda_courses();

if ( count( $courses ) !== 13 ) {
	fwrite( STDERR, 'Expected thirteen standalone Tilda course rows.' . PHP_EOL );
	exit( 1 );
}

$external_ids = array_column( $courses, 'external_id' );
$slugs        = array_column( $courses, 'slug' );

if ( count( array_unique( $external_ids ) ) !== count( $external_ids ) || count( array_unique( $slugs ) ) !== count( $slugs ) ) {
	fwrite( STDERR, 'Course external IDs and slugs must be unique.' . PHP_EOL );
	exit( 1 );
}

foreach ( $courses as $course ) {
	foreach ( array( 'external_id', 'slug', 'title', 'short_description', 'program' ) as $field ) {
		if ( empty( $course[ $field ] ) ) {
			fwrite( STDERR, "Course {$course['slug']} is missing {$field}." . PHP_EOL );
			exit( 1 );
		}
	}
}

echo 'Course content fixture is valid.' . PHP_EOL;
