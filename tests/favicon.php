<?php

declare(strict_types=1);

$functions = file_get_contents( __DIR__ . '/../wordpress/wp-content/themes/logika-theme/functions.php' );
$favicon = __DIR__ . '/../wordpress/wp-content/themes/logika-theme/assets/img/favicon.svg';

if ( ! is_file( $favicon ) ) {
	fwrite( STDERR, "Theme favicon asset is missing.\n" );
	exit( 1 );
}

foreach ( array( "add_action( 'wp_head', 'logika_theme_favicon'", 'image/svg+xml', 'get_theme_file_uri' ) as $contract ) {
	if ( ! str_contains( $functions, $contract ) ) {
		fwrite( STDERR, "WordPress favicon integration is missing: {$contract}\n" );
		exit( 1 );
	}
}

echo "Favicon contract is valid.\n";
