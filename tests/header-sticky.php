<?php

declare(strict_types=1);

$header = file_get_contents( __DIR__ . '/../wordpress/wp-content/themes/logika-theme/source-pages/header.php' );
$styles = file_get_contents( __DIR__ . '/../source/scss/blocks/_header.scss' );
$functions = file_get_contents( __DIR__ . '/../wordpress/wp-content/themes/logika-theme/functions.php' );

if ( strpos( $header, '<div class="header__top">' ) > strpos( $header, '<header class="header fixed-block">' ) ) {
	fwrite( STDERR, "The purple top bar must remain outside the sticky white header.\n" );
	exit( 1 );
}

foreach ( array( 'position: sticky;', 'top: 0;' ) as $style ) {
	if ( ! str_contains( $styles, $style ) ) {
		fwrite( STDERR, "Source header is missing sticky style: {$style}\n" );
		exit( 1 );
	}
}

if ( ! str_contains( $functions, ".header{position:sticky;top:0}" ) ) {
	fwrite( STDERR, "WordPress runtime is missing the sticky white header.\n" );
	exit( 1 );
}

echo "Header sticky contract is valid.\n";
