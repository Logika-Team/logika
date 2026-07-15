<?php

declare(strict_types=1);

$header = file_get_contents( __DIR__ . '/../wordpress/wp-content/themes/logika-theme/source-pages/header.php' );
$styles = file_get_contents( __DIR__ . '/../source/scss/blocks/_header.scss' );
$theme_css = file_get_contents( __DIR__ . '/../wordpress/wp-content/themes/logika-theme/assets/css/style.css' );

foreach ( array( 'IT Курси', 'Курси англійської' ) as $label ) {
	if ( ! str_contains( $header, $label ) ) {
		fwrite( STDERR, "Course dropdown does not use Ukrainian label: {$label}.\n" );
		exit( 1 );
	}
}

foreach ( array( 'padding: 12px 16px', 'border-radius: 10px', 'box-shadow: none', 'font-size: 16px' ) as $style ) {
	if ( ! str_contains( $styles, $style ) ) {
		fwrite( STDERR, "Course dropdown is missing minimalist style: {$style}.\n" );
		exit( 1 );
	}
}

foreach ( array( 'padding:12px 16px', 'border-radius:10px', 'box-shadow:none', 'font-size:16px' ) as $style ) {
	if ( ! str_contains( $theme_css, $style ) ) {
		fwrite( STDERR, "Theme CSS is missing rebuilt dropdown style: {$style}.\n" );
		exit( 1 );
	}
}

echo "Header courses dropdown contract is valid.\n";
