<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';
require dirname(__DIR__) . '/scripts/data/tilda-courses.php';
require dirname(__DIR__) . '/scripts/seed-tilda-courses.php';

logika_seed_tilda_courses( logika_tilda_courses() );

ob_start();
Logika_Theme_Source_Markup::renderPage( 'it-courses' );
$output = (string) ob_get_clean();

foreach ( array( 'Комп’ютерна грамотність', 'Візуальне програмування', 'Python Start', 'Python Expert' ) as $title ) {
	if ( ! str_contains( $output, $title ) ) {
		fwrite( STDERR, "Catalog does not render {$title}." . PHP_EOL );
		exit( 1 );
	}
}

if ( ! str_contains( $output, 'Вчимося кодити на Python' ) || ! str_contains( $output, '/courses/python-start/' ) ) {
	fwrite( STDERR, 'Catalog card content or course link is missing.' . PHP_EOL );
	exit( 1 );
}

echo 'IT courses catalog renders imported cards and links.' . PHP_EOL;
