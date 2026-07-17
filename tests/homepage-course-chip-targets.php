<?php

declare(strict_types=1);

require dirname( __DIR__ ) . '/wordpress/wp-load.php';

$page_id = (int) get_option( 'page_on_front' );
$rows    = (array) get_field( 'home_programming_courses', $page_id );
$chips   = (array) ( $rows[3]['chips'] ?? array() );
$links   = array_column( $chips, 'url', 'label' );
$errors  = array();

foreach ( array( 'Python Expert' => '/courses/python-expert/', 'Python Advanced' => '/courses/python-advanced/', 'Основи фронтенд розробки' => '/courses/frontend/', 'Комп\'ютерна грамотність для дорослих' => '/courses/computer-literacy-14/' ) as $label => $path ) {
	$url = home_url( $path );
	if ( $url !== ( $links[ $label ] ?? '' ) ) {
		$errors[] = "ACF chip URL is missing for {$label}.";
	}
}

ob_start();
logika_theme_render_source_page( 'index' );
$homepage = (string) ob_get_clean();

foreach ( array_values( $links ) as $url ) {
	if ( ! str_contains( $homepage, 'href="' . esc_url( $url ) . '"' ) ) {
		$errors[] = "Homepage does not render {$url}.";
	}
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Homepage course chips link to their courses.\n";
