<?php

declare(strict_types=1);

$root   = dirname( __DIR__ );
$source = (string) file_get_contents( $root . '/wordpress/wp-content/plugins/logika-core/src/HomepageImageOverrides.php' );
$script = (string) file_get_contents( $root . '/wordpress/wp-content/plugins/logika-core/assets/js/homepage-image-overrides.js' );
$errors = array();

foreach ( array( 'field_it_courses_catalog_card_image', 'field_course_card_image', 'field_course_project_student_image' ) as $field_key ) {
	if ( ! str_contains( $source, $field_key ) ) {
		$errors[] = "Missing image override registration: {$field_key}.";
	}
}

if ( ! str_contains( $script, 'settings.managedFields.indexOf(field.get(\'key\'))' ) ) {
	$errors[] = 'Image override controls do not enable registered course image fields.';
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "IT Courses image fields have replacement controls.\n";
