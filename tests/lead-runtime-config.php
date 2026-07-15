<?php

declare(strict_types=1);

$script = (string) file_get_contents( dirname(__DIR__) . '/wordpress/wp-content/themes/logika-theme/assets/js/leads.js' );

if ( ! str_contains( $script, 'const leadConfig = window.logikaLead || {};' ) || str_contains( $script, 'logikaLead.' ) || ! str_contains( $script, 'Logika phone setup failed' ) ) {
	fwrite( STDERR, "Lead controls must tolerate a missing localized config.\n" );
	exit( 1 );
}

echo "Lead controls tolerate a missing localized config.\n";
