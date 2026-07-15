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

foreach ( array( 'citiesEndpoint', 'branchesEndpoint', 'fetchCities', 'moveHeroForm', 'restoreHeroForm' ) as $contract ) {
	if ( ! str_contains( $script, $contract ) && ! str_contains( $functions, $contract ) ) {
		fwrite( STDERR, "School map is missing {$contract}.\n" );
		exit( 1 );
	}
}

if ( str_contains( $script, 'const dnipro' ) || str_contains( $script, "selectRegion('dnipropetrovsk')" ) ) {
	fwrite( STDERR, "School map must not preselect or hardcode Dnipro.\n" );
	exit( 1 );
}

if ( ! str_contains( $scss, '&__online' ) || ! str_contains( $scss, '[hidden]' ) ) {
	fwrite( STDERR, "School map must define the hidden online form panel.\n" );
	exit( 1 );
}

echo "School map uses theme assets and dynamic map contracts.\n";
