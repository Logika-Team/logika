<?php

declare(strict_types=1);

$scss = (string) file_get_contents( dirname(__DIR__) . '/source/scss/components/forms/_main-form.scss' );

if ( ! str_contains( $scss, '&__city-dropdown[hidden]' ) ) {
	fwrite( STDERR, "Hidden city dropdown must not override the hidden attribute.\n" );
	exit( 1 );
}

echo "Hidden city dropdown remains hidden.\n";
