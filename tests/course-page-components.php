<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$archive = file_get_contents( get_template_directory() . '/archive-course.php' ) ?: '';
$it = file_get_contents( get_template_directory() . '/source-pages/it-courses.php' ) ?: '';
$english = file_get_contents( get_template_directory() . '/source-pages/en-courses.php' ) ?: '';
$single = file_get_contents( get_template_directory() . '/single-course.php' ) ?: '';
$content = file_get_contents( get_template_directory() . '/src/PageContent.php' ) ?: '';

if ( ! str_contains( $archive, "Logika_Theme_Fixed_Page::render( 'it-courses'" ) || ! str_contains( $it, 'courses-section__items' ) || ! str_contains( $it, 'categories-section' ) || ! str_contains( $english, 'en-courses-section__items' ) || ! str_contains( $english, 'en-about-section' ) || ! str_contains( $single, "logika_theme_render_source_page( 'it-course' )" ) || ! str_contains( $content, 'applyCoursePage' ) || ! str_contains( $content, 'portfolio-section__viewport' ) ) {
	fwrite( STDERR, "Course pages do not keep the full main HTML structure.\n" );
	exit( 1 );
}

echo "Course pages keep the full main HTML structure.\n";
