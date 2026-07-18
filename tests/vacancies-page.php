<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$source = (string) file_get_contents( get_template_directory() . '/source-pages/vacancies.php' );
$lightbox_script = (string) file_get_contents( get_template_directory() . '/assets/js/vacancies-lightbox.js' );
$details_script = (string) file_get_contents( get_template_directory() . '/assets/js/vacancies-details-dialog.js' );
$functions = (string) file_get_contents( get_template_directory() . '/functions.php' );
$seed = (string) file_get_contents( __DIR__ . '/../scripts/seed-vacancies.php' );
if ( str_contains( $source, 'class="vacancies-nav' ) ) {
	fwrite( STDERR, "Vacancies page must not render its anchor navigation.\n" );
	exit( 1 );
}

$required = array( 'vacancies-hero', 'vacancies-about', 'vacancies-benefits', 'vacancies-gallery', 'vacancies-list', 'vacancies-cta', 'forms.gle/ikGeworjH6wnAdSt6' );

foreach ( $required as $marker ) {
	if ( ! str_contains( $source, $marker ) ) {
		fwrite( STDERR, "Vacancies page is missing {$marker}.\n" );
		exit( 1 );
	}
}

foreach ( array( 'benefit-events-3d.webp', 'benefit-support-3d.webp', 'benefit-pay-3d.webp' ) as $asset ) {
	if ( ! str_contains( $source, $asset ) || ! is_readable( get_template_directory() . '/assets/img/vacancies/' . $asset ) ) {
		fwrite( STDERR, "Vacancies page is missing generated benefit illustration {$asset}.\n" );
		exit( 1 );
	}
}

ob_start();
$page = get_page_by_path( 'vacancies' );
if ( ! $page instanceof WP_Post ) {
	fwrite( STDERR, "Vacancies page was not seeded.\n" );
	exit( 1 );
}
global $post, $wp_query;
$post = $page;
$wp_query->queried_object = $page;
$wp_query->queried_object_id = $page->ID;
logika_theme_render_source_page( 'vacancies' );
$markup = (string) ob_get_clean();

if ( 3 !== substr_count( $markup, 'class="vacancies-card"' ) ) {
	fwrite( STDERR, "Vacancies page must render three vacancy cards.\n" );
	exit( 1 );
}

if ( 3 !== substr_count( $markup, 'data-vacancies-details-open' ) || ! str_contains( $markup, 'data-vacancies-details-dialog' ) ) {
	fwrite( STDERR, "Vacancies cards must open their full descriptions in a dialog.\n" );
	exit( 1 );
}

if ( ! str_contains( $details_script, 'showModal' ) || ! str_contains( $details_script, "event.key === 'Escape'" ) || ! str_contains( $functions, 'logika-vacancies-details-dialog' ) ) {
	fwrite( STDERR, "Vacancies detail dialog assets are not enqueued.\n" );
	exit( 1 );
}

foreach ( array( 'data-vacancies-gallery-src', 'data-vacancies-lightbox', '<dialog' ) as $marker ) {
	if ( ! str_contains( $markup, $marker ) ) {
		fwrite( STDERR, "Vacancies team gallery is missing lightbox markup {$marker}.\n" );
		exit( 1 );
	}
}

if ( ! str_contains( $lightbox_script, 'showModal' ) || ! str_contains( $lightbox_script, "event.key === 'Escape'" ) || ! str_contains( $functions, 'logika-vacancies-lightbox' ) ) {
	fwrite( STDERR, "Vacancies team gallery lightbox assets are not enqueued.\n" );
	exit( 1 );
}

if ( str_contains( $markup, 'src="img/vacancies/benefit-events.svg"' ) || str_contains( $markup, 'src="img/vacancies/cta-character.svg"' ) ) {
	fwrite( STDERR, "Vacancies SVG attachments must use WordPress media URLs.\n" );
	exit( 1 );
}

if ( ! str_contains( $markup, 'team-10.jpg' ) || ! str_contains( $source, "'img/vacancies/team-10.jpg'" ) || ! str_contains( $seed, "'vacancies/team-10.jpg'" ) ) {
	fwrite( STDERR, "Vacancies hero must use the selected team photo.\n" );
	exit( 1 );
}

$response = wp_remote_get( home_url( '/vacancies/' ) );
$live_markup = is_wp_error( $response ) ? '' : (string) wp_remote_retrieve_body( $response );
if ( str_contains( $live_markup, 'http://img/vacancies/' ) ) {
	fwrite( STDERR, "Vacancies page must not emit invalid image hosts.\n" );
	exit( 1 );
}

$stylesheet = (string) file_get_contents( get_template_directory() . '/assets/css/blocks/sections/vacancies.css' );
if ( str_contains( $stylesheet, '.vacancies-page{overflow:hidden}' ) ) {
	fwrite( STDERR, "Vacancies page must not clip its sections inside the site flex layout.\n" );
	exit( 1 );
}

foreach ( array( 'background-image: radial-gradient', 'background-size: 24px 24px', '.vacancies-hero h1 { margin-bottom: 24px; }', 'margin: 0 0 30px;', 'line-height: 1.45;' ) as $style ) {
	if ( ! str_contains( $stylesheet, $style ) ) {
		fwrite( STDERR, "Vacancies hero is missing its visual rhythm: {$style}.\n" );
		exit( 1 );
	}
}

foreach ( array( 'width: 120px', 'height: 120px' ) as $style ) {
	if ( ! str_contains( $stylesheet, $style ) ) {
		fwrite( STDERR, "Vacancies benefit illustrations are not displayed at the intended size: {$style}.\n" );
		exit( 1 );
	}
}

if ( ! str_contains( $stylesheet, 'aspect-ratio: 16 / 10' ) ) {
	fwrite( STDERR, "Vacancies hero must preserve the selected team photo composition.\n" );
	exit( 1 );
}

if ( ! str_contains( $stylesheet, '.vacancies-cta p:not(.vacancies-eyebrow) { color: var(--white); }' ) ) {
	fwrite( STDERR, "Vacancies CTA description must remain white on its violet background.\n" );
	exit( 1 );
}

if ( ! str_contains( $stylesheet, '.vacancies-cta .btn { margin-top: 44px; }' ) ) {
	fwrite( STDERR, "Vacancies CTA button needs a clear gap after its text.\n" );
	exit( 1 );
}

if ( ! str_contains( $stylesheet, '.vacancies-benefits article p { font-weight: 600; letter-spacing: normal; }' ) ) {
	fwrite( STDERR, "Vacancies benefit card descriptions need readable typography.\n" );
	exit( 1 );
}

foreach ( array( '.vacancies-card p { font-weight: 600; letter-spacing: normal; }', '.vacancies-details-trigger {', 'background: var(--violet-100);' ) as $style ) {
	if ( ! str_contains( $stylesheet, $style ) ) {
		fwrite( STDERR, "Vacancies detail controls need readable purple styling: {$style}.\n" );
		exit( 1 );
	}
}

echo "Vacancies page markup is present.\n";
