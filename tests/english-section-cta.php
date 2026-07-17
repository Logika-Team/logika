<?php

declare(strict_types=1);

$scss = (string) file_get_contents( dirname(__DIR__) . '/source/scss/blocks/sections/english-section.scss' );

if ( ! preg_match( '/&__bg\s*\{[^}]*pointer-events:\s*none;/s', $scss ) ) {
	fwrite( STDERR, "English section background must not intercept CTA clicks.\n" );
	exit( 1 );
}

echo "English section CTA remains clickable.\n";
