<?php

declare(strict_types=1);

$scss = (string) file_get_contents( dirname(__DIR__) . '/source/scss/blocks/sections/cta-section.scss' );

if ( substr_count( $scss, 'pointer-events: none;' ) < 2 ) {
	fwrite( STDERR, "CTA decorative backgrounds must not intercept form clicks.\n" );
	exit( 1 );
}

echo "CTA decorations do not intercept form clicks.\n";
