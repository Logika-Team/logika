<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$theme        = dirname(__DIR__) . '/wordpress/wp-content/themes/logika-theme';
$modal        = (string) file_get_contents( $theme . '/template-parts/components/lead-modal.php' );
$functions    = (string) file_get_contents( $theme . '/functions.php' );
$script       = (string) file_get_contents( $theme . '/assets/js/main.js' );
$leads_script = (string) file_get_contents( $theme . '/assets/js/leads.js' );
$form         = (string) file_get_contents( $theme . '/template-parts/forms/lead.php' );
$about        = (string) file_get_contents( $theme . '/template-parts/pages/about.php' );
$about_source = (string) file_get_contents( $theme . '/source-pages/about.php' );
$css          = (string) file_get_contents( $theme . '/assets/css/lead-modal.css' );
$errors       = array();

foreach (
	array(
		'class="modal"',
		'class="modal__container is-lesson"',
		'data-target="lesson"',
		'role="dialog"',
		'aria-modal="true"',
		'template-parts/forms/lead',
		'img/modal-image.webp',
	) as $contract
) {
	if ( ! str_contains( $modal, $contract ) ) {
		$errors[] = "Lead modal is missing {$contract}.";
	}
}

foreach ( array( 'wp_footer', 'template-parts/components/lead-modal' ) as $contract ) {
	if ( ! str_contains( $functions, $contract ) ) {
		$errors[] = "Theme does not render the lead modal through {$contract}.";
	}
}

foreach ( array( '[data-logika-modal]', '#lead-form', 'Escape', '.modal-close' ) as $contract ) {
	if ( ! str_contains( $script, $contract ) ) {
		$errors[] = "Lead modal interaction is missing {$contract}.";
	}
}

foreach ( array( '.modal', '.modal__container', '[hidden]' ) as $contract ) {
	if ( ! str_contains( $css, $contract ) ) {
		$errors[] = "Lead modal styles are missing {$contract}.";
	}
}

if ( str_contains( $form, 'type="checkbox"' ) || ! str_contains( $form, 'name="consent_accepted" value="1"' ) ) {
	$errors[] = 'Shared lead form must keep consent without a checkbox.';
}

if ( ! str_contains( $about, 'class="about-history__cta" href="#lead-form"' ) ) {
	$errors[] = 'About trial lesson CTA must open the shared lead modal.';
}

if ( ! str_contains( $about_source, 'class="about-history__cta btn" href="#lead-form"' ) ) {
	$errors[] = 'Rendered About CTA must open the shared lead modal.';
}

ob_start();
logika_theme_render_lead_modal();
$markup = (string) ob_get_clean();

foreach (
	array(
		'class="modal"',
		'class="modal__container is-lesson"',
		'data-target="lesson"',
		'name="name"',
		'data-logika-phone-input',
		'data-logika-age-select',
		'name="child_age"',
		'data-logika-lead-form',
		'name="form_id" value="trial_lesson"',
		'name="idempotency_key"',
		'img/modal-image.webp',
	) as $marker
) {
	if ( ! str_contains( $markup, $marker ) ) {
		$errors[] = "Rendered modal is missing {$marker}.";
	}
}

if ( str_contains( $markup, 'class="lead-modal"' ) ) {
	$errors[] = 'The legacy purple lead modal is still rendered.';
}

ob_start();
logika_theme_render_source_page( 'index' );
$home_markup = (string) ob_get_clean();
if ( ! str_contains( $home_markup, '/#lead-form' ) && ! str_contains( $home_markup, 'href="#lead-form"' ) ) {
	$errors[] = 'Homepage does not retain a lesson lead CTA target.';
}

if ( ! str_contains( $leads_script, 'data.idempotency_key = key.value;' ) ) {
	$errors[] = 'Lead submit does not send the generated idempotency key.';
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Lesson lead modal contract is valid.\n";
