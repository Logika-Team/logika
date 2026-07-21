<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$course = get_page_by_path( 'english-b2', OBJECT, 'course' );
if ( ! $course ) {
	fwrite( STDERR, "English B2 fixture is missing.\n" );
	exit( 1 );
}

ob_start();
get_template_part( 'template-parts/courses/english', null, array( 'course_id' => $course->ID ) );
$markup = (string) ob_get_clean();
$catalog = preg_match( '~<section class="english-course-catalog">.*?</section>~s', $markup, $matches ) ? $matches[0] : '';

if ( str_contains( $catalog, 'Рівень B2.1' ) ) {
	fwrite( STDERR, "B2.1 must not appear in the English course catalog.\n" );
	exit( 1 );
}

echo "B2.1 is hidden from the English course catalog.\n";
