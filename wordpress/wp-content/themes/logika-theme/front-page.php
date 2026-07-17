<?php

declare(strict_types=1);

get_header();
$city_slug = (string) get_query_var( 'logika_city' );
$city      = $city_slug ? \Logika\Core\CitySlug::find( $city_slug ) : null;
if ( $city instanceof WP_Post ) {
	Logika_Theme_City_Page::renderHome( $city->ID );
} else {
	logika_theme_render_source_page( 'index' );
}
get_footer();
