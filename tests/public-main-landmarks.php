<?php

declare(strict_types=1);

require dirname( __DIR__ ) . '/wordpress/wp-load.php';

$routes = array( '/', '/about/', '/it-courses/', '/english-courses/', '/faq/', '/media-center/', '/courses/', '/courses/english-b2/', '/camps/', '/camps/test-routing-camp/', '/cities/kyiv/', '/blog/', '/media-center/videogames/', '/privacy-policy/' );
$errors = array();

foreach ( $routes as $route ) {
	$response = wp_remote_get( home_url( $route ), array( 'timeout' => 20 ) );
	if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
		$errors[] = "{$route} does not return 200.";
		continue;
	}
	$count = substr_count( (string) wp_remote_retrieve_body( $response ), '<main' );
	if ( 1 !== $count ) {
		$errors[] = "{$route} contains {$count} main landmarks.";
	}
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Public routes return 200 with one main landmark.\n";
