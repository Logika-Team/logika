<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$page_id = (int) get_option( 'page_on_front' );
$original = get_field( 'home_form_consent', $page_id );
delete_field( 'home_form_consent', $page_id );

ob_start();
logika_theme_render_source_page( 'index' );
$homepage = (string) ob_get_clean();

if ( null === $original || false === $original || '' === $original ) {
	delete_field( 'home_form_consent', $page_id );
} else {
	update_field( 'home_form_consent', $original, $page_id );
}

if ( ! str_contains( $homepage, 'погоджуєтесь із<a href=' ) ) {
	fwrite( STDERR, "Homepage consent adds a gap before the privacy link.\n" );
	exit( 1 );
}

echo "Homepage consent fallback keeps the privacy link flush with the text.\n";
