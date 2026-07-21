<?php

declare(strict_types=1);

require dirname( __DIR__ ) . '/wordpress/wp-load.php';

$camps_page = get_page_by_path( 'camps' );
if ( ! $camps_page instanceof WP_Post ) {
	fwrite( STDERR, "The /camps/ page fixture does not exist; cannot test the format cards.\n" );
	exit( 1 );
}

$errors  = array();
$posts   = array();
$backup_summer = (array) get_field( 'camp_archive_summer_camps', $camps_page->ID );
$backup_autumn = (array) get_field( 'camp_archive_autumn_camps', $camps_page->ID );

/**
 * @return array{0: string, 1: string} [before <ul>, after closing </ul>] not used; helper below extracts by season.
 */
function logika_test_camp_modal_season_markup( string $markup, string $season ): string {
	if ( ! preg_match( '#<ul class="modal__camps-items" data-camp-season="' . preg_quote( $season, '#' ) . '"[^>]*>(.*?)</ul>#s', $markup, $matches ) ) {
		return '';
	}
	return $matches[1];
}

function logika_test_render_camp_modal( WP_Post $camps_page ): string {
	global $post, $wp_query;
	$post                         = $camps_page;
	$wp_query->queried_object     = $camps_page;
	$wp_query->queried_object_id  = $camps_page->ID;
	ob_start();
	get_template_part( 'template-parts/components/camp-modal' );
	return (string) ob_get_clean();
}

try {
	$camp_a = wp_insert_post( array( 'post_type' => 'camp', 'post_status' => 'publish', 'post_title' => 'Літо А fixture' ), true );
	$camp_b = wp_insert_post( array( 'post_type' => 'camp', 'post_status' => 'publish', 'post_title' => 'Літо Б fixture' ), true );
	$camp_c = wp_insert_post( array( 'post_type' => 'camp', 'post_status' => 'publish', 'post_title' => 'Осінь В fixture' ), true );
	$posts  = array( $camp_a, $camp_b, $camp_c );

	foreach ( array( $camp_a => 'Літо', $camp_b => 'Літо', $camp_c => 'Осінь' ) as $camp_id => $season ) {
		update_field( 'camp_season', $season, $camp_id );
		update_field( 'camp_is_active', 1, $camp_id );
		Logika\Core\CampArchiveSync::sync( $camp_id );
	}

	update_field( 'camp_archive_summer_camps', array(), $camps_page->ID );
	update_field( 'camp_archive_autumn_camps', array(), $camps_page->ID );

	// Without an override, the summer card falls back to every active camp whose season matches "Літо".
	$markup  = logika_test_render_camp_modal( $camps_page );
	$summer  = logika_test_camp_modal_season_markup( $markup, 'summer' );
	if ( ! str_contains( $summer, 'Літо А fixture' ) || ! str_contains( $summer, 'Літо Б fixture' ) ) {
		$errors[] = 'Summer card without an override must fall back to all active summer camps.';
	}

	// Hiding one camp: curate the override list to exclude camp_a.
	update_field( 'camp_archive_summer_camps', array( $camp_b ), $camps_page->ID );
	$markup = logika_test_render_camp_modal( $camps_page );
	$summer = logika_test_camp_modal_season_markup( $markup, 'summer' );
	if ( str_contains( $summer, 'Літо А fixture' ) ) {
		$errors[] = 'Curating the summer override must be able to hide a camp from the modal.';
	}
	if ( ! str_contains( $summer, 'Літо Б fixture' ) ) {
		$errors[] = 'Curating the summer override must keep the remaining camp visible.';
	}

	// Adding a camp to a card separately from its own season.
	update_field( 'camp_archive_autumn_camps', array( $camp_c, $camp_a ), $camps_page->ID );
	$markup = logika_test_render_camp_modal( $camps_page );
	$autumn = logika_test_camp_modal_season_markup( $markup, 'autumn' );
	if ( ! str_contains( $autumn, 'Осінь В fixture' ) || ! str_contains( $autumn, 'Літо А fixture' ) ) {
		$errors[] = 'The autumn override must be able to add camps to that card independently of their own season.';
	}
} finally {
	foreach ( $posts as $post_id ) {
		if ( is_numeric( $post_id ) ) {
			wp_delete_post( (int) $post_id, true );
		}
	}
	update_field( 'camp_archive_summer_camps', $backup_summer, $camps_page->ID );
	update_field( 'camp_archive_autumn_camps', $backup_autumn, $camps_page->ID );
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Camp format cards can be curated per card independently.\n";
