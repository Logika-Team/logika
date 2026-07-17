<?php

declare(strict_types=1);

$theme = dirname(__DIR__) . '/wordpress/wp-content/themes/logika-theme';
$modal = (string) file_get_contents( $theme . '/template-parts/components/lead-modal.php' );
$functions = (string) file_get_contents( $theme . '/functions.php' );
$script = (string) file_get_contents( $theme . '/assets/js/main.js' );
$form = (string) file_get_contents( $theme . '/template-parts/forms/lead.php' );
$about = (string) file_get_contents( $theme . '/template-parts/pages/about.php' );
$about_source = (string) file_get_contents( $theme . '/source-pages/about.php' );
$scss = (string) file_get_contents( dirname(__DIR__) . '/source/scss/blocks/_lead-modal.scss' );
$errors = array();

foreach ( array( 'data-logika-lead-modal', 'role="dialog"', 'aria-modal="true"', 'template-parts/forms/lead' ) as $contract ) {
	if ( ! str_contains( $modal, $contract ) ) {
		$errors[] = "Lead modal is missing {$contract}.";
	}
}

foreach ( array( 'wp_footer', 'template-parts/components/lead-modal' ) as $contract ) {
	if ( ! str_contains( $functions, $contract ) ) {
		$errors[] = "Theme does not render the lead modal through {$contract}.";
	}
}

foreach ( array( 'data-logika-lead-modal', "#lead-form", 'Escape' ) as $contract ) {
	if ( ! str_contains( $script, $contract ) ) {
		$errors[] = "Lead modal interaction is missing {$contract}.";
	}
}

if ( ! str_contains( $scss, '.lead-modal' ) || ! str_contains( $scss, '[hidden]' ) ) {
	$errors[] = 'Lead modal needs hidden-state styling.';
}

if ( str_contains( $form, 'type="checkbox"' ) || ! str_contains( $form, 'name="consent_accepted" value="1"' ) ) {
	$errors[] = 'Shared lead form must keep consent without a checkbox.';
}

if ( ! str_contains( $about, 'class="about-history__cta" href="#lead-form"' ) ) {
	$errors[] = 'About trial lesson CTA must open the shared lead modal.';
}

if ( ! str_contains( $about_source, 'class="about-history__cta" href="#lead-form"' ) ) {
	$errors[] = 'Rendered About CTA must open the shared lead modal.';
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Lead modal contract is present.\n";
