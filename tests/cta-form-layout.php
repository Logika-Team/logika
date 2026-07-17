<?php

declare(strict_types=1);

$scss = (string) file_get_contents( dirname(__DIR__) . '/source/scss/components/forms/_cta-form.scss' );

if ( ! str_contains( $scss, 'repeat(2, minmax(0, 1fr))' ) || ! str_contains( $scss, 'min-width: 0;' ) ) {
	fwrite( STDERR, "CTA form fields must use equal-width grid cells.\n" );
	exit( 1 );
}

echo "CTA form fields use equal-width cells.\n";
