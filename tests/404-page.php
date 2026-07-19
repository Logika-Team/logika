<?php

declare(strict_types=1);

$theme = dirname( __DIR__ ) . '/wordpress/wp-content/themes/logika-theme';
$template = file_get_contents( $theme . '/404.php' );
$styles   = file_get_contents( $theme . '/assets/css/404.css' );
$functions = file_get_contents( $theme . '/functions.php' );

if ( false === $template || false === $styles || false === $functions ) {
	throw new RuntimeException( '404 page files are incomplete.' );
}

$required_template = array(
	'get_header();',
	'get_footer();',
	'<h1',
	'Схоже, ця сторінка загубилася',
	"home_url( '/' )",
	"home_url( '/it-courses/' )",
);

foreach ( $required_template as $needle ) {
	if ( ! str_contains( $template, $needle ) ) {
		throw new RuntimeException( "404 template is missing {$needle}." );
	}
}

if ( 1 !== substr_count( $template, '<h1' ) || ! str_contains( $template, 'aria-labelledby=' ) ) {
	throw new RuntimeException( '404 template must contain one labelled h1.' );
}

if ( ! str_contains( $styles, '.error-page' ) || ! str_contains( $styles, 'var(--yellow)' ) || ! str_contains( $styles, 'prefers-reduced-motion' ) ) {
	throw new RuntimeException( '404 styles are missing brand or accessibility rules.' );
}

if ( ! str_contains( $functions, "if ( is_404() )" ) || ! str_contains( $functions, "'logika-404'" ) ) {
	throw new RuntimeException( '404 stylesheet is not conditionally enqueued.' );
}

echo "404 page contract is present.\n";
