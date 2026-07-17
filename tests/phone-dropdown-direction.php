<?php

declare(strict_types=1);

$scss = (string) file_get_contents( dirname(__DIR__) . '/source/scss/components/forms/_main-form.scss' );
$script = (string) file_get_contents( dirname(__DIR__) . '/wordpress/wp-content/themes/logika-theme/assets/js/leads.js' );

if ( ! str_contains( $scss, '&__dropdown-content--dropup' ) || ! str_contains( $scss, 'bottom: auto;' ) || str_contains( $script, "classList.toggle('iti--phone-dropdown-up'" ) ) {
	\fwrite( STDERR, "Phone country dropdown must open below the field.\n" );
	exit( 1 );
}

echo "Phone country dropdown opens below the field.\n";
