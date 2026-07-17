<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$camp = get_page_by_path( 'test-camp', OBJECT, 'camp' );
$camp_id = $camp ? (int) $camp->ID : (int) wp_insert_post( array( 'post_type' => 'camp', 'post_name' => 'test-camp', 'post_title' => 'Тестовий табір', 'post_status' => 'publish' ) );

update_field( 'camp_start_date', '2026-08-01', $camp_id );
update_field( 'camp_end_date', '2026-08-10', $camp_id );
update_field( 'camp_season', 'Літній сезон', $camp_id );
update_field( 'camp_program', array( array( 'title' => 'День знайомства', 'description' => '<p>Опис дня з ACF.</p>', 'items' => array( array( 'item_text' => 'Командний проєкт' ) ) ) ), $camp_id );
$format = term_exists( 'Онлайн', 'learning_format' );
$format_id = is_array( $format ) ? (int) $format['term_id'] : (int) $format;
wp_set_object_terms( $camp_id, array( $format_id ), 'learning_format' );
$city = get_page_by_path( 'test-city', OBJECT, 'city' );
update_field( 'camp_related_cities', array( $city->ID ), $camp_id );

$output = Logika_Theme_Camp_Page::render( $camp_id );

if ( ! str_contains( $output, 'Літній сезон' ) || ! str_contains( $output, '01.08.2026 — 10.08.2026' ) || ! str_contains( $output, 'Онлайн' ) || ! str_contains( $output, 'Тестове місто' ) || ! str_contains( $output, 'День знайомства' ) || ! str_contains( $output, 'Командний проєкт' ) ) {
	fwrite( STDERR, "Camp page does not render ACF dates and season.\n" );
	exit( 1 );
}

update_field( 'camp_end_date', '2025-01-01', $camp_id );
update_field( 'camp_expired_state_text', 'Ця зміна вже завершилася.', $camp_id );
if ( ! str_contains( Logika_Theme_Camp_Page::render( $camp_id ), 'Ця зміна вже завершилася.' ) ) {
	fwrite( STDERR, "Expired camp state is not rendered.\n" );
	exit( 1 );
}

echo "Camp page uses WordPress content.\n";
