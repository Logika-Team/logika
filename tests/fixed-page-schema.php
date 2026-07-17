<?php

declare(strict_types=1);

$root   = dirname( __DIR__ ) . '/wordpress/wp-content/plugins/logika-core/acf-json/';
$errors = array();
$groups = array(
	'group_logika_page_about' => array( 'about_directions_items' => 'repeater', 'about_outcome_items' => 'repeater', 'about_gallery' => 'gallery', 'about_media_items' => 'repeater', 'about_onboarding_items' => 'repeater', 'about_map_title' => 'text', 'about_cta_title' => 'text', 'about_reviews_title' => 'text', 'about_faq_title' => 'text' ),
	'group_logika_page_it_courses' => array( 'it_courses_age_categories' => 'repeater', 'it_courses_map_title' => 'text', 'it_courses_cta_title' => 'text', 'it_courses_faq_title' => 'text' ),
	'group_logika_page_english_courses' => array( 'english_courses_marquee_items' => 'repeater', 'english_courses_test_image' => 'image', 'english_courses_about_text' => 'wysiwyg', 'english_courses_map_title' => 'text', 'english_courses_cta_title' => 'text', 'english_courses_faq_title' => 'text' ),
	'group_logika_page_faq' => array( 'faq_page_map_title' => 'text', 'faq_page_cta_image' => 'image' ),
	'group_logika_page_media_center' => array( 'media_center_cta_title' => 'text', 'media_center_faq_title' => 'text', 'media_center_featured_faq' => 'relationship' ),
	'group_logika_legal' => array( 'legal_intro_title' => 'text', 'legal_intro_text' => 'wysiwyg', 'legal_sections' => 'repeater', 'legal_gallery' => 'gallery' ),
);

foreach ( $groups as $key => $required ) {
	$file = $root . $key . '.json';
	if ( ! is_file( $file ) ) {
		$errors[] = "Missing {$key}.";
		continue;
	}
	$group  = json_decode( (string) file_get_contents( $file ), true, 512, JSON_THROW_ON_ERROR );
	$fields = array_column( $group['fields'] ?? array(), null, 'name' );
	foreach ( $required as $name => $type ) {
		if ( ( $fields[ $name ]['type'] ?? '' ) !== $type ) {
			$errors[] = "{$key} is missing {$name}:{$type}.";
		}
	}
	if ( 'group_logika_legal' !== $key && count( array_filter( $group['fields'] ?? array(), static fn( array $field ): bool => 'tab' === ( $field['type'] ?? '' ) ) ) < 6 ) {
		$errors[] = "{$key} does not group logical sections with Tabs.";
	}
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Fixed pages and Legal expose complete section schemas.\n";
