<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$city = get_page_by_path( 'test-city', OBJECT, 'city' );
$course = get_page_by_path( 'test-course', OBJECT, 'course' );
$camp = get_page_by_path( 'test-camp', OBJECT, 'camp' );
ob_start();
get_template_part( 'template-parts/forms/lead', null, array( 'city_id' => $city->ID, 'course_id' => $course->ID, 'camp_id' => $camp->ID ) );
$form = (string) ob_get_clean();

if ( ! str_contains( $form, 'name="city_id" value="' . $city->ID . '"' ) || ! str_contains( $form, 'name="course_id" value="' . $course->ID . '"' ) || ! str_contains( $form, 'name="camp_id" value="' . $camp->ID . '"' ) || ! str_contains( $form, 'data-logika-lead-form' ) ) {
	fwrite( STDERR, "Context lead form is not rendered.\n" );
	exit( 1 );
}

if ( ! str_contains( $form, 'name="child_age"' ) || ! str_contains( $form, 'main-form__select' ) || ! str_contains( $form, 'data-logika-age-select' ) || ! str_contains( $form, 'main-form__age-option' ) ) {
	fwrite( STDERR, "Context lead form does not render the child age dropdown.\n" );
	exit( 1 );
}

if ( ! str_contains( $form, 'data-logika-phone-input' ) || ! str_contains( $form, 'name="phone"' ) ) {
	fwrite( STDERR, "Context lead form does not render the intl phone input contract.\n" );
	exit( 1 );
}

echo "Context lead form renders verified entity IDs.\n";
