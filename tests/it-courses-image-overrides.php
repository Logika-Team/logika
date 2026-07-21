<?php

declare(strict_types=1);

$root   = dirname( __DIR__ );
$script = (string) file_get_contents( $root . '/wordpress/wp-content/plugins/logika-core/assets/js/image-overrides.js' );
$errors = array();

if ( ! str_contains( $script, "field.get('type') !== 'image'" ) ) {
	$errors[] = 'Image override controls no longer enhance every ACF image field generically.';
}

$groups = array(
	'group_logika_page_it_courses.json' => array( 'field_it_courses_catalog_card_image' ),
	'group_logika_course.json' => array( 'field_course_card_image', 'field_course_project_student_image' ),
);

foreach ( $groups as $file => $expected_keys ) {
	$group = json_decode( (string) file_get_contents( $root . '/wordpress/wp-content/plugins/logika-core/acf-json/' . $file ), true, 512, JSON_THROW_ON_ERROR );
	$keys  = array();
	$visit = static function ( array $fields ) use ( &$visit, &$keys ): void {
		foreach ( $fields as $field ) {
			if ( 'image' === ( $field['type'] ?? '' ) ) {
				$keys[] = (string) ( $field['key'] ?? '' );
			}
			$visit( (array) ( $field['sub_fields'] ?? array() ) );
		}
	};
	$visit( (array) ( $group['fields'] ?? array() ) );

	foreach ( $expected_keys as $field_key ) {
		if ( ! in_array( $field_key, $keys, true ) ) {
			$errors[] = "{$file} no longer registers image field {$field_key}.";
		}
	}
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "IT Courses and course image fields have replacement controls via the generic image field enhancer.\n";
