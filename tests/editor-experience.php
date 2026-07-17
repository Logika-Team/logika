<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$errors = array();
$global_fields = acf_get_fields( 'group_logika_global' );
$phone_country = $global_fields ? current( array_filter( $global_fields, static fn( array $item ): bool => 'form_phone_country_default' === $item['name'] ) ) : false;
if ( $phone_country ) {
	$errors[] = 'Phone country fallback must be fixed to UA, not editor-configurable.';
}
foreach ( array( 'city', 'branch', 'course', 'camp', 'review', 'faq_item' ) as $type ) {
	if ( post_type_supports( $type, 'editor' ) ) {
		$errors[] = "{$type} still exposes the empty block editor.";
	}
}
foreach ( array( 'group_logika_city', 'group_logika_course', 'group_logika_camp' ) as $group_key ) {
	$group = acf_get_field_group( $group_key );
	if ( ! $group || 'acf_after_title' !== $group['position'] ) {
		$errors[] = "{$group_key} is not positioned under the title.";
	}
}
$review_fields = acf_get_fields( 'group_logika_review' );
foreach ( array( 'review_author_name', 'review_author_role', 'review_text' ) as $field_name ) {
	$field = $review_fields ? current( array_filter( $review_fields, static fn( array $item ): bool => $field_name === $item['name'] ) ) : false;
	if ( ! $field || empty( $field['placeholder'] ) && empty( $field['default_value'] ) && empty( $field['instructions'] ) ) {
		$errors[] = "{$field_name} does not guide editors with editable Ukrainian text.";
	}
}
$home_fields = acf_get_fields( 'group_logika_home' );
$home_gallery = $home_fields ? current( array_filter( $home_fields, static fn( array $item ): bool => 'home_image_gallery' === $item['name'] ) ) : false;
if ( ! $home_gallery || 'gallery' !== $home_gallery['type'] || empty( $home_gallery['instructions'] ) ) {
	$errors[] = 'Homepage images are not exposed through an editor-facing ACF Pro gallery.';
}
foreach ( array( 'home_hero_boy_image', 'home_hero_character_image' ) as $field_name ) {
	$field = $home_fields ? current( array_filter( $home_fields, static fn( array $item ): bool => $field_name === $item['name'] ) ) : false;
	if ( ! $field || 'image' !== $field['type'] || empty( $field['instructions'] ) ) {
		$errors[] = "{$field_name} is not an editor-facing homepage image field.";
	}
}
foreach ( array( 'home_programming_title', 'home_transformation_title', 'home_onboarding_title', 'home_locations_title', 'home_faq_items', 'home_certificates_title', 'home_partners_title' ) as $field_name ) {
	$field = $home_fields ? current( array_filter( $home_fields, static fn( array $item ): bool => $field_name === $item['name'] ) ) : false;
	if ( ! $field || empty( $field['label'] ) || empty( $field['instructions'] ) ) {
		$errors[] = "{$field_name} is not exposed as a section text field on the homepage editor.";
	}
}
$age_placeholder = $home_fields ? current( array_filter( $home_fields, static fn( array $item ): bool => 'home_form_age_placeholder' === $item['name'] ) ) : false;
if ( ! $age_placeholder || 'text' !== $age_placeholder['type'] || empty( $age_placeholder['label'] ) || empty( $age_placeholder['instructions'] ) ) {
	$errors[] = 'Homepage form age placeholder is not editor-facing.';
}
$age_options = $home_fields ? current( array_filter( $home_fields, static fn( array $item ): bool => 'home_form_age_options' === $item['name'] ) ) : false;
$age_option_subfields = $age_options && isset( $age_options['sub_fields'] ) ? $age_options['sub_fields'] : array();
foreach ( array( 'value', 'label' ) as $subfield_name ) {
	$subfield = current( array_filter( $age_option_subfields, static fn( array $item ): bool => $subfield_name === $item['name'] ) );
	if ( ! $age_options || 'repeater' !== $age_options['type'] || empty( $age_options['label'] ) || empty( $age_options['instructions'] ) || ! $subfield || 'text' !== $subfield['type'] ) {
		$errors[] = "home_form_age_options.{$subfield_name} is not an editor-facing age dropdown subfield.";
	}
}
foreach ( array( 'home_transformation_before_image', 'home_transformation_after_image', 'home_certificates_image', 'home_partners_items' ) as $field_name ) {
	$field = $home_fields ? current( array_filter( $home_fields, static fn( array $item ): bool => $field_name === $item['name'] ) ) : false;
	if ( ! $field || empty( $field['label'] ) || empty( $field['instructions'] ) ) {
		$errors[] = "{$field_name} is not exposed as a section image preview field on the homepage editor.";
	}
}
$home_repeaters = array(
	'home_programming_courses' => array( 'image', 'icon' ),
	'home_english_levels' => array( 'image' ),
	'home_onboarding_steps' => array( 'image' ),
	'home_partners_items' => array( 'image' ),
);
foreach ( $home_repeaters as $repeater_name => $subfield_names ) {
	$repeater = $home_fields ? current( array_filter( $home_fields, static fn( array $item ): bool => $repeater_name === $item['name'] ) ) : false;
	$subfields = $repeater && isset( $repeater['sub_fields'] ) ? $repeater['sub_fields'] : array();
	foreach ( $subfield_names as $subfield_name ) {
		$subfield = current( array_filter( $subfields, static fn( array $item ): bool => $subfield_name === $item['name'] ) );
		if ( ! $subfield || 'image' !== $subfield['type'] || empty( $subfield['instructions'] ) ) {
			$errors[] = "{$repeater_name}.{$subfield_name} is not an editor-facing section image preview.";
		}
	}
}
foreach ( array( 'home', 'about', 'faq', 'it-courses', 'english-courses', 'media-center' ) as $page_slug ) {
	$page = get_page_by_path( $page_slug );
	if ( ! $page ) {
		$errors[] = "Page {$page_slug} is missing from the WordPress Pages breakdown.";
		continue;
	}

	if ( use_block_editor_for_post( $page ) ) {
		$errors[] = "Managed page {$page_slug} still opens the empty block editor instead of the ACF editor surface.";
	}
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Custom content uses the ACF-first editor experience.\n";
