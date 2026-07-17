<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$errors = array();

foreach ( array( 'index', 'about', 'faq', 'it-courses', 'en-courses', 'media-center', 'camps', 'camp', 'it-course', 'article' ) as $source ) {
	ob_start();
	logika_theme_render_source_page( $source );
	$markup = (string) ob_get_clean();

	foreach ( array( 'data-logika-lead-form', 'data-logika-phone-input', 'name="form_id" value="consultation"', 'name="consent_accepted" value="1"' ) as $contract ) {
		if ( ! str_contains( $markup, $contract ) ) {
			$errors[] = "{$source} is missing {$contract}.";
		}
	}

	if ( str_contains( $markup, 'name="town"' ) || str_contains( $markup, 'name="city"' ) ) {
		$errors[] = "{$source} retains an unconnected city field.";
	}
}

ob_start();
logika_theme_render_source_page( 'faq' );
$faq_markup = (string) ob_get_clean();

if ( ! str_contains( $faq_markup, '<form class="cta-form main-form"' ) ) {
	$errors[] = 'CTA form does not inherit the shared main-form styles.';
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Source lead forms use the shared lead contract.\n";
