<?php

declare(strict_types=1);

$header_styles = file_get_contents( __DIR__ . '/../source/scss/blocks/_header.scss' );
$footer_styles = file_get_contents( __DIR__ . '/../source/scss/blocks/_footer.scss' );
$functions     = file_get_contents( __DIR__ . '/../wordpress/wp-content/themes/logika-theme/functions.php' );

foreach ( array( $header_styles, $footer_styles ) as $styles ) {
	if ( ! preg_match( '/\.btn\s*\{\s*font-weight:\s*600;/', $styles ) ) {
		fwrite( STDERR, "Header/footer CTA must use Montserrat semibold weight 600.\n" );
		exit( 1 );
	}
}

if ( ! str_contains( $functions, '.header .btn,.footer .btn,.header__location-region-toggle{font-weight:600}' ) ) {
	fwrite( STDERR, "WordPress runtime is missing the header/footer typography override.\n" );
	exit( 1 );
}

echo "Header/footer typography contract is valid.\n";
