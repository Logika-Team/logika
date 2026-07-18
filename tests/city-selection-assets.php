<?php

declare(strict_types=1);

$root = dirname( __DIR__ );
$read = static fn( string $path ): string => is_readable( $path ) ? ( file_get_contents( $path ) ?: '' ) : '';
$context = $read( $root . '/source/js/city-context.js' );
$runtime_context = $read( $root . '/wordpress/wp-content/themes/logika-theme/assets/js/city-context.js' );
$selector = $read( $root . '/wordpress/wp-content/themes/logika-theme/assets/js/city-selector.js' );
$map = $read( $root . '/source/js/camp-map.js' );
$runtime_map = $read( $root . '/wordpress/wp-content/themes/logika-theme/assets/js/camp-map.js' );
$map_styles = $read( $root . '/source/scss/blocks/sections/school-map.scss' );
$runtime_map_styles = $read( $root . '/wordpress/wp-content/themes/logika-theme/assets/css/blocks/sections/school-map.css' );
$media = $read( $root . '/source/js/media-center.js' );
$page = $read( $root . '/wordpress/wp-content/themes/logika-theme/source-pages/media-center.php' );
$functions = $read( $root . '/wordpress/wp-content/themes/logika-theme/functions.php' );
$map_template = $read( $root . '/wordpress/wp-content/themes/logika-theme/template-parts/sections/school-map.php' );
$routing = $read( $root . '/wordpress/wp-content/themes/logika-theme/src/Routing.php' );
$acf = $read( $root . '/wordpress/wp-content/plugins/logika-core/acf-json/group_logika_post.json' );

if ( ! str_contains( $context, 'logika-city-id' ) || ! str_contains( $context, 'logika:city-change' ) || ! str_contains( $context, 'if (!config.endpoint)' ) ) {
	fwrite( STDERR, "City selection must use one persisted shared context.\n" );
	exit( 1 );
}

if ( ! str_contains( $context, 'JSON.stringify(city)' ) || ! str_contains( $context, 'const cachedCity' ) || ! str_contains( $context, '|| cachedCity()' ) ) {
	fwrite( STDERR, "City context must restore the selected city before its REST list finishes loading.\n" );
	exit( 1 );
}

if ( ! str_contains( $selector, "region.label === 'Інші міста' ? 'other'" ) || ! str_contains( $selector, "a.region.label === 'Інші міста'" ) || ! str_contains( $selector, "a.region.label === 'Онлайн'" ) || ! str_contains( $selector, "a.label === 'Онлайн'" ) ) {
	fwrite( STDERR, "Header city selector must render Other cities and Online last.\n" );
	exit( 1 );
}

if ( ! str_contains( $functions, '$city_selector_version' ) || ! str_contains( $functions, "'logika-city-selector', \$uri . '/js/city-selector.js', array( 'logika-city-context' ), \$city_selector_version" ) ) {
	fwrite( STDERR, "City selector assets must receive a fresh version after a persistence fix.\n" );
	exit( 1 );
}

if ( ! str_contains( $context, 'window.location.assign( city.url )' ) || str_contains( $context, 'syncLinks' ) || str_contains( $context, 'window.history.pushState' ) || ! str_contains( $selector, 'logikaCityContext.set(city, true)' ) || ! str_contains( $map, 'cityContext.set(city, true)' ) ) {
	fwrite( STDERR, "Navbar and map selection must open only the selected city homepage.\n" );
	exit( 1 );
}

if ( ! str_contains( $context, 'const syncHomeLinks' ) || ! str_contains( $context, "url.pathname !== '/'" ) || ! str_contains( $context, 'anchor.href = city.url' ) ) {
	fwrite( STDERR, "Only homepage links must retain the selected city URL.\n" );
	exit( 1 );
}

if ( ! str_contains( $context, 'const isHomepage' ) || ! str_contains( $context, 'window.history?.replaceState' ) ) {
	fwrite( STDERR, "Changing city on the homepage must not reload it.\n" );
	exit( 1 );
}

if ( ! str_contains( $context, 'const applyHero' ) || substr_count( $context, 'applyHero(city);' ) < 3 || ! str_contains( $context, 'applyHero(initial);' ) || ! str_contains( $context, '.banner-section__title' ) || ! str_contains( $context, '.banner-section__subtitle' ) || ! str_contains( $context, 'textContent = city.hero.title' ) || ! str_contains( $context, 'textContent = city.hero.text' ) || $context !== $runtime_context ) {
	fwrite( STDERR, "City context must update the served homepage hero from the shared city payload.\n" );
	exit( 1 );
}

if ( ! str_contains( $routing, "'index.php?logika_city=\$matches[1]'" ) || str_contains( $routing, '^cities/([^/]+)/(.+)/?$' ) || ! str_contains( $routing, 'resolveCityHomepage' ) ) {
	fwrite( STDERR, "Only the city homepage may use a city-prefixed URL.\n" );
	exit( 1 );
}

if ( ! str_contains( $map, 'Object.entries(regionNames).find' ) || ! str_contains( $map, 'label === city.region?.label' ) ) {
	fwrite( STDERR, "Map must resolve the selected city region by its public label.\n" );
	exit( 1 );
}

if ( ! str_contains( $map, "new Set(['crimea', 'donetsk', 'luhansk', 'kherson', 'sumy'])" ) || ! str_contains( $map, "new Set(['zaporizhia'])" ) || ! str_contains( $runtime_map, "new Set(['crimea', 'donetsk', 'luhansk', 'kherson', 'sumy'])" ) || ! str_contains( $runtime_map, "new Set(['zaporizhia'])" ) || ! str_contains( $map_styles, 'is-city-only' ) || ! str_contains( $runtime_map_styles, 'is-city-only' ) ) {
	fwrite( STDERR, "Map must keep Sumy unavailable and Zaporizhzhia grey but selectable.\n" );
	exit( 1 );
}

if ( ! str_contains( $map, "'zaporizhzhia-city': 'zaporizhia'" ) || ! str_contains( $runtime_map, "'zaporizhzhia-city': 'zaporizhia'" ) || ! str_contains( $map_styles, 'is-city-boundary:hover' ) || ! str_contains( $runtime_map_styles, 'is-city-boundary:hover' ) ) {
	fwrite( STDERR, "Zaporizhzhia must have a selectable city boundary on the grey region.\n" );
	exit( 1 );
}

if ( ! str_contains( $map_template, 'OpenStreetMap contributors' ) || ! str_contains( $functions, "ukraine-regions.svg?ver=' . \$map_version" ) ) {
	fwrite( STDERR, "The Zaporizhzhia boundary needs attribution and an uncached map asset URL.\n" );
	exit( 1 );
}

$leads = $read( $root . '/wordpress/wp-content/themes/logika-theme/assets/js/leads.js' );
if ( ! str_contains( $leads, "region.label === 'Інші міста' ? 'other'" ) || ! str_contains( $leads, "a.region.label === 'Інші міста'" ) || ! str_contains( $leads, "a.region.label === 'Онлайн'" ) || ! str_contains( $leads, "a.label === 'Онлайн'" ) ) {
	fwrite( STDERR, "Lead form city selectors must render Other cities and Online last.\n" );
	exit( 1 );
}

if ( ! str_contains( $leads, "window.addEventListener('logika:city-change'" ) || ! str_contains( $leads, 'window.logikaCityContext?.get()' ) ) {
	fwrite( STDERR, "Lead forms must inherit the shared selected city.\n" );
	exit( 1 );
}

if ( str_contains( $leads, 'window.logikaCityContext?.set(city, true)' ) ) {
	fwrite( STDERR, "Form city selection must not redirect away from the form.\n" );
	exit( 1 );
}

if ( ! str_contains( $media, 'logika:city-change' ) || ! str_contains( $media, '[data-media-featured]' ) || ! str_contains( $media, '[data-media-list]' ) || ! str_contains( $functions, 'logika-media-center' ) ) {
	fwrite( STDERR, "Media center must update its cards from shared city selection.\n" );
	exit( 1 );
}

if ( str_contains( $acf, 'post_related_city' ) ) {
	fwrite( STDERR, "Article city selection must use WordPress tags instead of a duplicate ACF field.\n" );
	exit( 1 );
}

if ( ! str_contains( $context, "const cookieName = 'logika_city'" ) || ! str_contains( $context, 'SameSite=Lax' ) || ! str_contains( $context, 'Max-Age=0' ) ) {
	fwrite( STDERR, "City context must persist and clear the server-readable city cookie.\n" );
	exit( 1 );
}

$city_acf = $read( $root . '/wordpress/wp-content/plugins/logika-core/acf-json/group_logika_city.json' );
if ( ! str_contains( $city_acf, 'city_url_slug' ) ) {
	fwrite( STDERR, "Cities need an editor-managed Latin URL key.\n" );
	exit( 1 );
}

echo "City selection assets share one media context.\n";
