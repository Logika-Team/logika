<?php

declare(strict_types=1);

$root = dirname( __DIR__ );
$read = static fn( string $path ): string => is_readable( $path ) ? ( file_get_contents( $path ) ?: '' ) : '';
$context = $read( $root . '/source/js/city-context.js' );
$selector = $read( $root . '/wordpress/wp-content/themes/logika-theme/assets/js/city-selector.js' );
$map = $read( $root . '/source/js/camp-map.js' );
$media = $read( $root . '/source/js/media-center.js' );
$page = $read( $root . '/wordpress/wp-content/themes/logika-theme/source-pages/media-center.php' );
$functions = $read( $root . '/wordpress/wp-content/themes/logika-theme/functions.php' );
$acf = $read( $root . '/wordpress/wp-content/plugins/logika-core/acf-json/group_logika_post.json' );

if ( ! str_contains( $context, 'logika-city-id' ) || ! str_contains( $context, 'logika:city-change' ) ) {
	fwrite( STDERR, "City selection must use one persisted shared context.\n" );
	exit( 1 );
}

if ( str_contains( $selector, 'link.href = city.url' ) || ! str_contains( $selector, 'logikaCityContext.set' ) || ! str_contains( $map, 'logikaCityContext.set' ) ) {
	fwrite( STDERR, "Navbar and map must select a city without navigation.\n" );
	exit( 1 );
}

if ( ! str_contains( $media, 'logika:city-change' ) || ! str_contains( $media, '[data-media-featured]' ) || ! str_contains( $media, '[data-media-list]' ) || ! str_contains( $functions, 'logika-media-center' ) ) {
	fwrite( STDERR, "Media center must update its cards from shared city selection.\n" );
	exit( 1 );
}

if ( ! str_contains( $acf, 'Залиште порожнім для загальної статті.' ) ) {
	fwrite( STDERR, "Article city field must explain the global fallback.\n" );
	exit( 1 );
}

echo "City selection assets share one media context.\n";
