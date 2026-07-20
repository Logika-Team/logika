<?php

declare(strict_types=1);

$root     = dirname( __DIR__ );
$template = (string) file_get_contents( $root . '/wordpress/wp-content/themes/logika-theme/template-parts/courses/english.php' );
$course   = json_decode( (string) file_get_contents( $root . '/wordpress/wp-content/plugins/logika-core/acf-json/group_logika_course.json' ), true, 512, JSON_THROW_ON_ERROR );
$fields   = array_column( $course['fields'] ?? array(), null, 'name' );
$errors   = array();
$course_location = array_filter(
	array_merge( ... ( $course['location'] ?? array() ) ),
	static fn( array $rule ): bool => 'post_type' === ( $rule['param'] ?? '' ) && '==' === ( $rule['operator'] ?? '' ) && 'course' === ( $rule['value'] ?? '' )
);

$review_field = $fields['course_related_reviews'] ?? array();
if ( ! $course_location || 'relationship' !== ( $review_field['type'] ?? '' ) || ! in_array( 'review', (array) ( $review_field['post_type'] ?? array() ), true ) ) {
	$errors[] = 'Course editor must expose a relationship field for review posts.';
}

if ( ! str_contains( $template, "template-parts/sections/reviews" ) || ! str_contains( $template, "course_related_reviews" ) || ! str_contains( $template, "'context' => \$course_id" ) ) {
	$errors[] = 'English course pages must render the shared reviews section with course-specific selections.';
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "English course reviews are connected to the course editor.\n";
