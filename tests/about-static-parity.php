<?php

declare(strict_types=1);

$root  = dirname( __DIR__ );
$theme = $root . '/wordpress/wp-content/themes/logika-theme';
$html  = file_get_contents( $root . '/source/about.html' ) ?: '';
$page  = file_get_contents( $theme . '/source-pages/about.php' ) ?: '';
$css   = file_get_contents( $theme . '/assets/css/blocks/sections/about.css' ) ?: '';
$built = file_get_contents( $root . '/build/css/blocks/sections/about.css' ) ?: '';
$errors = array();

preg_match( '#<main(?:\s[^>]*)?>.*?</main>#s', $html, $main );
preg_match( '#<main(?:\s[^>]*)?>.*?</main>#s', $page, $runtime_main );

if ( trim( $main[0] ?? '' ) !== trim( $runtime_main[0] ?? '' ) ) {
	$errors[] = 'About runtime markup differs from the static source.';
}

if ( $css !== $built ) {
	$errors[] = 'About runtime CSS differs from the static build.';
}

foreach ( array_unique( preg_match_all( '#src="img/([^"?]+)#', $page, $matches ) ? $matches[1] : array() ) as $asset ) {
	if ( ! is_file( $theme . '/assets/img/' . $asset ) ) {
		$errors[] = "About asset is missing: {$asset}.";
	}
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "About runtime matches its static source.\n";
