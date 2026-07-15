<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$errors = array();
$assert = static function ( bool $condition, string $message ) use ( &$errors ): void {
	if ( ! $condition ) {
		$errors[] = $message;
	}
};

foreach ( array( 'UA', 'PL', 'US' ) as $country ) {
	$assert( $country === Logika_Theme_Phone_Country::country_from_server( array( 'HTTP_CF_IPCOUNTRY' => $country ) ), "CF-IPCountry {$country} was not resolved." );
}

$assert(
	'PL' === Logika_Theme_Phone_Country::country_from_server( array( 'HTTP_CF_IPCOUNTRY' => 'PL', 'HTTP_X_VERCEL_IP_COUNTRY' => 'US' ) ),
	'CF-IPCountry does not take priority over compatible CDN headers.'
);

foreach ( array( 'XX', 'T1', 'EU', 'invalid' ) as $country ) {
	$assert( null === Logika_Theme_Phone_Country::country_from_server( array( 'HTTP_CF_IPCOUNTRY' => $country ) ), "Invalid country {$country} was accepted." );
}

$option_name = 'options_form_phone_country_default';
$had_option  = false !== get_option( $option_name, false );
$old_option  = get_option( $option_name );

try {
	update_option( $option_name, 'PL' );
	$assert( 'UA' === Logika_Theme_Phone_Country::resolve( array() ), 'Phone country fallback is configurable instead of fixed to UA.' );

	$_SERVER['HTTP_CF_IPCOUNTRY'] = 'US';
	$response = rest_do_request( new WP_REST_Request( 'GET', '/logika/v1/phone-country' ) );
	$headers  = array_change_key_case( $response->get_headers(), CASE_LOWER );
	$assert( 200 === $response->get_status(), 'Phone country endpoint did not return 200.' );
	$assert( array( 'country' => 'US' ) === $response->get_data(), 'Phone country endpoint did not return the Cloudflare country.' );
	$assert( 'no-store, no-cache, must-revalidate, max-age=0' === ( $headers['cache-control'] ?? '' ), 'Phone country endpoint is cacheable.' );
} finally {
	unset( $_SERVER['HTTP_CF_IPCOUNTRY'] );
	if ( $had_option ) {
		update_option( $option_name, $old_option );
	} else {
		delete_option( $option_name );
	}
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Phone country endpoint resolves safe countries and falls back to UA.\n";
