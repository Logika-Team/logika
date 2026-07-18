<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$errors = array();

ob_start();
logika_theme_render_source_page( 'index' );
$homepage = (string) ob_get_clean();

foreach (
	array(
		'img/transformation/4243.svg',
		'img/transformation/421.svg',
		'transformation-section__item-slogan-decoration',
	) as $marker
) {
	if ( ! str_contains( $homepage, $marker ) ) {
		$errors[] = "Homepage transformation badge marker {$marker} is missing.";
	}
}

if ( ! preg_match( '/status-before.*?4243\.svg.*?Просто грає в ігри/s', $homepage ) ) {
	$errors[] = 'The light badge is not attached to the before slogan.';
}

if ( ! preg_match( '/status-after.*?421\.svg.*?Створює власні ігри/s', $homepage ) ) {
	$errors[] = 'The violet badge is not attached to the after slogan.';
}

foreach (
	array(
		dirname( __DIR__ ) . '/source/img/transformation/4243.svg',
		dirname( __DIR__ ) . '/source/img/transformation/421.svg',
		dirname( __DIR__ ) . '/wordpress/wp-content/themes/logika-theme/assets/img/transformation/4243.svg',
		dirname( __DIR__ ) . '/wordpress/wp-content/themes/logika-theme/assets/img/transformation/421.svg',
	) as $asset
) {
	if ( ! is_file( $asset ) ) {
		$errors[] = "Transformation badge asset {$asset} is missing.";
	}
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Homepage transformation badges render correctly.\n";
