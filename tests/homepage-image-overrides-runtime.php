<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

use Logika\Core\HomepageImageOverrides;

$keys = array(
	'field_home_hero_boy_image_override',
	'field_home_hero_character_image_override',
	'field_home_trust_item_icon_override',
	'field_home_english_level_image_override',
	'field_home_programming_courses_image_override',
	'field_home_programming_courses_icon_override',
	'field_home_transformation_before_image_override',
	'field_home_transformation_after_image_override',
	'field_home_onboarding_steps_image_override',
	'field_home_certificates_image_override',
	'field_home_partners_items_image_override',
);

foreach ( $keys as $key ) {
	if ( ! acf_get_field( $key ) ) {
		fwrite( STDERR, "Missing runtime field: {$key}\n" );
		exit( 1 );
	}
}

$field  = acf_get_field( 'field_home_hero_boy_image_override' );
$result = HomepageImageOverrides::validateValue( true, 278, $field, 'acf[field_home_hero_boy_image_override]' );

if ( true === $result ) {
	fwrite( STDERR, "Attachment 278 was accepted for the hero override.\n" );
	exit( 1 );
}

echo "ACF loaded all 11 override fields; attachment 278 is rejected.\n";
