<?php

declare(strict_types=1);

$root   = dirname( __DIR__ );
$errors = array();
$groups = array(
	'group_logika_page_it_courses.json' => array( 'it_courses_catalog_cards' ),
	'group_logika_page_faq.json' => array( 'faq_page_hero_image', 'faq_page_hero_icon' ),
	'group_logika_page_media_center.json' => array( 'media_center_hero_image', 'media_center_hero_background_image', 'media_center_benefits' ),
	'group_logika_camp.json' => array( 'camp_hero_images' ),
	'group_logika_course.json' => array( 'course_hero_background_image', 'course_hero_character_image', 'course_learn_background_image', 'course_learn_character_image', 'course_process_background_image', 'course_faq_left_background_image', 'course_faq_right_background_image', 'course_cta_character_image', 'course_cta_top_background_image', 'course_cta_bottom_background_image' ),
);

foreach ( $groups as $file => $expected ) {
	$group = json_decode( (string) file_get_contents( $root . '/wordpress/wp-content/plugins/logika-core/acf-json/' . $file ), true, 512, JSON_THROW_ON_ERROR );
	$names = array();
	$visit = static function ( array $fields ) use ( &$visit, &$names ): void {
		foreach ( $fields as $field ) {
			$names[] = (string) ( $field['name'] ?? '' );
			$visit( (array) ( $field['sub_fields'] ?? array() ) );
		}
	};
	$visit( (array) ( $group['fields'] ?? array() ) );
	foreach ( $expected as $name ) {
		if ( ! in_array( $name, $names, true ) ) {
			$errors[] = "{$file} is missing image slot {$name}.";
		}
	}
}

$content = (string) file_get_contents( $root . '/wordpress/wp-content/themes/logika-theme/src/PageContent.php' );
foreach ( array( 'faq_page_hero_icon', 'media_center_hero_background_image', 'it_courses_catalog_cards', 'camp_hero_images', 'course_hero_background_image', 'course_cta_bottom_background_image' ) as $field ) {
	if ( ! str_contains( $content, $field ) ) {
		$errors[] = "Public source markup does not read {$field}.";
	}
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Every major image-bearing section has explicit ACF image slots.\n";
