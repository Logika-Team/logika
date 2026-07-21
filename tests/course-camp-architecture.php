<?php

declare(strict_types=1);

require dirname( __DIR__ ) . '/wordpress/wp-load.php';

$root   = dirname( __DIR__ ) . '/wordpress/wp-content/plugins/logika-core/acf-json/';
$errors = array();
$groups = array(
	'group_logika_course' => array( 'course_visual_variant' => 'select', 'course_hero_benefits' => 'repeater', 'course_hero_additional_text' => 'textarea', 'course_hero_cta_label' => 'text', 'course_program_anchor_label' => 'text', 'course_hero_background_image' => 'image', 'course_hero_character_image' => 'image', 'course_learn_title' => 'text', 'course_learn_items' => 'repeater', 'course_learn_background_image' => 'image', 'course_learn_character_image' => 'image', 'course_process_title' => 'text', 'course_process_background_image' => 'image', 'course_portfolio_title' => 'text', 'course_faq_items' => 'repeater', 'course_map_title' => 'text', 'course_cta_title' => 'text', 'course_cta_submit_label' => 'text', 'course_cta_character_image' => 'image', 'course_cta_top_background_image' => 'image', 'course_cta_bottom_background_image' => 'image', 'course_general_faq_title' => 'text', 'course_faq_left_background_image' => 'image', 'course_faq_right_background_image' => 'image', 'course_show_in_catalog' => 'true_false', 'logika_is_template' => 'true_false' ),
	'group_logika_camp' => array( 'camp_hero_text' => 'textarea', 'camp_hero_facts' => 'repeater', 'camp_card_image' => 'image', 'camp_card_description' => 'textarea', 'camp_benefits' => 'repeater', 'camp_activities' => 'repeater', 'camp_trips' => 'repeater', 'camp_details' => 'repeater', 'camp_includes' => 'repeater', 'camp_booking_title' => 'text', 'camp_booking_benefits' => 'repeater', 'camp_booking_form_title' => 'textarea', 'camp_related_reviews' => 'relationship', 'camp_related_faq' => 'relationship', 'logika_is_template' => 'true_false' ),
	'group_logika_camp_archive' => array( 'camp_archive_hero_title' => 'text', 'camp_archive_benefits' => 'repeater', 'camp_archive_gallery' => 'gallery', 'camp_archive_reviews' => 'relationship', 'camp_archive_faq' => 'relationship' ),
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
	if ( count( array_filter( $group['fields'] ?? array(), static fn( array $field ): bool => 'tab' === ( $field['type'] ?? '' ) ) ) < 6 ) {
		$errors[] = "{$key} does not expose section Tabs.";
	}
}

$course_field = array( 'type' => 'relationship', 'post_type' => array( 'course' ), 'instructions' => 'Оберіть курс.' );
$prepared     = class_exists( 'Logika\\Core\\AdminUi' ) ? Logika\Core\AdminUi::prepareCourseField( $course_field ) : $course_field;
if ( ! str_contains( (string) ( $prepared['instructions'] ?? '' ), 'edit.php?post_type=course' ) ) {
	$errors[] = 'Course relationships have no native quick-create link.';
}
foreach ( array( 'single-course.php' => "logika_theme_render_source_page( 'it-course' )", 'single-camp.php' => "logika_theme_render_source_page( 'camp' )", 'templates/page-it-courses.php' => "Logika_Theme_Fixed_Page::render( 'it-courses'", 'templates/page-camps.php' => "Logika_Theme_Fixed_Page::render( 'camps'" ) as $file => $call ) {
	if ( ! str_contains( (string) file_get_contents( dirname( __DIR__ ) . '/wordpress/wp-content/themes/logika-theme/' . $file ), $call ) ) {
		$errors[] = "{$file} does not use {$call}.";
	}
}

foreach ( array( 'Logika\\Core\\PostDuplicator', 'Logika\\Core\\CourseCatalogSync', 'Logika\\Core\\CampArchiveSync' ) as $class ) {
	if ( ! class_exists( $class ) ) {
		$errors[] = "{$class} is not registered.";
	}
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Course and Camp use complete reusable ACF architecture.\n";
