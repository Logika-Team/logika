<?php

declare(strict_types=1);

$root = dirname( __DIR__ );
$script = file_get_contents( $root . '/source/js/camp-map.js' ) ?: '';
$functions = file_get_contents( $root . '/wordpress/wp-content/themes/logika-theme/functions.php' ) ?: '';
$scss = file_get_contents( $root . '/source/scss/blocks/sections/school-map.scss' ) ?: '';

if ( str_contains( $script, "fetch('img/maps/ukraine-regions.svg')" ) || ! str_contains( $script, 'logikaThemeAssets' ) || ! str_contains( $functions, 'mapUrl' ) ) {
	fwrite( STDERR, "School map must fetch the theme asset URL, not a page-relative path.\n" );
	exit( 1 );
}

foreach ( array( 'branchesEndpoint', 'fetchMap', 'logikaCityContext', 'cloneNode(true)', 'onlinePanel.append(onlineForm)' ) as $contract ) {
	if ( ! str_contains( $script, $contract ) && ! str_contains( $functions, $contract ) ) {
		fwrite( STDERR, "School map is missing {$contract}.\n" );
		exit( 1 );
	}
}

if ( ! str_contains( $script, ".cta-form[data-logika-lead-form]" ) ) {
	fwrite( STDERR, "School map must reuse the CTA form when a page has no hero form.\n" );
	exit( 1 );
}

if ( ! str_contains( $script, '[data-map-online-form]' ) ) {
	fwrite( STDERR, "School map must prefer a page-specific online form source.\n" );
	exit( 1 );
}

if ( str_contains( $script, 'onlinePanel.append(heroForm)' ) ) {
	fwrite( STDERR, "School map must keep the hero lead form in place.\n" );
	exit( 1 );
}

if ( str_contains( $script, 'config.citiesEndpoint' ) || str_contains( $functions, "'citiesEndpoint'" ) ) {
	fwrite( STDERR, "School map must load cities only through the shared city context.\n" );
	exit( 1 );
}

if ( ! str_contains( $script, "mapUrl: 'img/maps/ukraine-regions.svg'" ) || ! str_contains( $script, 'const cityContext = window.logikaCityContext ||' ) ) {
	fwrite( STDERR, "School map must render its SVG in static previews without WordPress configuration.\n" );
	exit( 1 );
}

if ( str_contains( $script, 'requestJson(config.mapUrl)' ) ) {
	fwrite( STDERR, "School map SVG must be read as text, not JSON.\n" );
	exit( 1 );
}

if ( str_contains( $script, 'const dnipro' ) || str_contains( $script, "selectRegion('dnipropetrovsk')" ) ) {
	fwrite( STDERR, "School map must not preselect or hardcode Dnipro.\n" );
	exit( 1 );
}

foreach ( array( 'show_on_map', 'unavailableRegions' ) as $contract ) {
	if ( ! str_contains( $script . $scss, $contract ) ) {
		fwrite( STDERR, "School map is missing {$contract}.\n" );
		exit( 1 );
	}
}

if ( ! str_contains( $script, 'city.region?.label === regionNames.zaporizhia' ) || str_contains( $script, "'kherson', 'zaporizhia'" ) || str_contains( $script . $scss, 'school-map__city-marker' ) ) {
	fwrite( STDERR, "Zaporizhzhia must be a violet region without auto-selection or a separate marker.\n" );
	exit( 1 );
}

if ( ! str_contains( $scss, '&__online' ) || ! str_contains( $scss, '[hidden]' ) ) {
	fwrite( STDERR, "School map must define the hidden online form panel.\n" );
	exit( 1 );
}

if ( 1 !== preg_match( '#&__online \{.*?width: 912px;.*?max-width: calc\(100vw - 40px\);.*?\.main-form \{\s*width: 100%;#s', $scss ) ) {
	fwrite( STDERR, "School map online form must match the mode switcher width.\n" );
	exit( 1 );
}

if ( ! str_contains( $functions, 'logika-school-map-style' ) ) {
	fwrite( STDERR, "School map stylesheet must be enqueued after the theme stylesheet.\n" );
	exit( 1 );
}

echo "School map uses theme assets and dynamic map contracts.\n";
