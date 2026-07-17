<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$files = array(
	$root . '/wordpress/wp-content/themes/logika-theme/template-parts/forms/lead.php' => 'name="course_id" value="<?php echo esc_attr( $course_id ); ?>"',
	$root . '/source/js/main.js' => 'courseInput.value = nextTrigger.dataset.logikaCourseId || \'\';',
	$root . '/wordpress/wp-content/themes/logika-theme/assets/js/main.js' => 'courseInput.value = nextTrigger.dataset.logikaCourseId || \'\';',
	$root . '/wordpress/wp-content/themes/logika-theme/src/PageContent.php' => 'data-logika-course-id="' . "' . esc_attr( (string) \$id ) . '" . '"',
	$root . '/wordpress/wp-content/themes/logika-theme/src/SourceMarkup.php' => 'applyEnglishCourseContext',
);

foreach ( $files as $path => $expected ) {
	$contents = (string) file_get_contents( $path );
	if ( ! str_contains( $contents, $expected ) ) {
		fwrite( STDERR, "Missing course modal context in {$path}\n" );
		exit( 1 );
	}
}

echo "Course modal context contracts passed.\n";
