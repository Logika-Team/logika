<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$expected = array(
	'english-a2' => 'a2-eye.png',
	'english-b1' => 'b1-reader.png',
);

foreach ( $expected as $slug => $asset ) {
	$course = get_page_by_path( $slug, OBJECT, 'course' );
	ob_start();
	get_template_part( 'template-parts/courses/english', null, array( 'course_id' => $course->ID ) );
	$markup = (string) ob_get_clean();
	$asset_url = '/assets/img/english-levels/characters/' . $asset;

	foreach ( array( 'english-course-hero', 'english-course-outcomes' ) as $section ) {
		if ( ! preg_match( '~<section class="[^"]*' . $section . '[^"]*">.*?</section>~s', $markup, $matches ) || ! str_contains( $matches[0], $asset_url ) ) {
			fwrite( STDERR, "$slug must use $asset in $section.\n" );
			exit( 1 );
		}
	}
}

echo "English course character assets are correct.\n";
