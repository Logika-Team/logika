<?php

declare(strict_types=1);

$root   = dirname(__DIR__);
$source = (string) file_get_contents( $root . '/wordpress/wp-content/plugins/logika-core/src/ImageOverrides.php' );
$script = (string) file_get_contents( $root . '/wordpress/wp-content/plugins/logika-core/assets/js/image-overrides.js' );
$errors = array();

foreach ( array( 'field_review_photo', 'review_original_photo', 'captureReviewOriginalPhoto' ) as $needle ) {
	if ( ! str_contains( $source, $needle ) ) {
		$errors[] = "Review image source is missing {$needle}.";
	}
}

foreach ( array( 'settings.reviewField', 'resolveDefault(field)', 'acf.val(field.$input()', 'setAttachment(field, resolved.attachment)' ) as $needle ) {
	if ( ! str_contains( $script, $needle ) ) {
		$errors[] = "Review image editor is missing {$needle}.";
	}
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Review image replacement and reset contract is present.\n";
