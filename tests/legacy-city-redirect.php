<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$kyiv = get_page_by_path( 'kyiv', OBJECT, 'city' );
$kyiv_id = $kyiv instanceof WP_Post ? (int) $kyiv->ID : (int) wp_insert_post( array( 'post_type' => 'city', 'post_name' => 'kyiv', 'post_title' => 'Київ', 'post_status' => 'publish' ) );
update_field( 'city_url_slug', 'kyiv', $kyiv_id );

if ( ! str_ends_with( (string) Logika_Theme_Routing::legacyCityUrl( 'map/kuiv' ), '/cities/kyiv/' ) ) {
	fwrite( STDERR, 'Legacy /map/kuiv must redirect to canonical Kyiv URL.' . PHP_EOL );
	exit( 1 );
}

if ( null !== Logika_Theme_Routing::legacyCityUrl( 'map/unknown-city' ) ) {
	fwrite( STDERR, 'Unknown legacy map URL must not redirect.' . PHP_EOL );
	exit( 1 );
}

if ( ! str_ends_with( (string) Logika_Theme_Routing::legacyCityUrl( 'map/lukyanivka' ), '/cities/kyiv/' ) ) {
	fwrite( STDERR, 'Kyiv branch page /map/lukyanivka must redirect to the Kyiv city page.' . PHP_EOL );
	exit( 1 );
}

$vinnytsia = get_page_by_path( 'vinnytsia', OBJECT, 'city' );
$vinnytsia_id = $vinnytsia instanceof WP_Post ? (int) $vinnytsia->ID : (int) wp_insert_post( array( 'post_type' => 'city', 'post_name' => 'vinnytsia', 'post_title' => 'Вінниця', 'post_status' => 'publish' ) );
update_field( 'city_url_slug', 'vinnytsia', $vinnytsia_id );

if ( ! str_ends_with( (string) Logika_Theme_Routing::legacyCityUrl( 'map/vinnytsya' ), '/cities/vinnytsia/' ) ) {
	fwrite( STDERR, 'Transliteration mismatch /map/vinnytsya must redirect to the canonical Vinnytsia URL.' . PHP_EOL );
	exit( 1 );
}

if ( null !== Logika_Theme_Routing::legacyCityUrl( 'map/pokrovsk' ) ) {
	fwrite( STDERR, 'Pokrovsk (Donetsk) must not be confused with Pokrov (Dnipro region).' . PHP_EOL );
	exit( 1 );
}

$chornomorsk = get_page_by_path( 'chornomorsk', OBJECT, 'city' );
$chornomorsk_id = $chornomorsk instanceof WP_Post ? (int) $chornomorsk->ID : (int) wp_insert_post( array( 'post_type' => 'city', 'post_name' => 'chornomorsk', 'post_title' => 'Чорноморськ', 'post_status' => 'publish' ) );
update_field( 'city_url_slug', 'chornomorsk', $chornomorsk_id );

if ( ! str_ends_with( (string) Logika_Theme_Routing::legacyCityUrl( 'mini/chornomorsk' ), '/cities/chornomorsk/' ) ) {
	fwrite( STDERR, 'Legacy /mini/chornomorsk must redirect to the canonical city URL.' . PHP_EOL );
	exit( 1 );
}

$game_design = get_page_by_path( 'game-design', OBJECT, 'course' );
$game_design_id = $game_design instanceof WP_Post ? (int) $game_design->ID : (int) wp_insert_post( array( 'post_type' => 'course', 'post_name' => 'game-design', 'post_title' => 'Геймдизайн', 'post_status' => 'publish' ) );

if ( ! str_ends_with( (string) Logika_Theme_Routing::legacyCourseUrl( 'aboutgamedesign' ), '/courses/game-design/' ) ) {
	fwrite( STDERR, 'Marketing-funnel alias /aboutgamedesign must redirect to the game-design course.' . PHP_EOL );
	exit( 1 );
}

echo "Legacy city redirects are resolved.\n";
