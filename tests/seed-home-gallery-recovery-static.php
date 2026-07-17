<?php

declare(strict_types=1);

$script = (string) file_get_contents( dirname( __DIR__ ) . '/scripts/seed-home-gallery.php' );

foreach ( array( 'get_attached_file', 'is_readable( $target )', 'wp_update_attachment_metadata' ) as $needle ) {
	if ( ! str_contains( $script, $needle ) ) {
		fwrite( STDERR, "Homepage gallery seeding cannot restore missing attachment files: {$needle}\n" );
		exit( 1 );
	}
}

echo "Homepage gallery seeding restores missing attachment files.\n";
