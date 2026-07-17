<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$source_page = file_get_contents( get_template_directory() . '/source-pages/faq.php' ) ?: '';
$buttons = file_get_contents( dirname( __DIR__ ) . '/source/scss/general/_buttons.scss' ) ?: '';
$faq_style = file_get_contents( get_template_directory() . '/assets/css/blocks/sections/faq-banner-section.css' ) ?: '';
$accordion = file_get_contents( dirname( __DIR__ ) . '/source/scss/components/_accordion.scss' ) ?: '';
$faq_accordion_style = file_get_contents( get_template_directory() . '/assets/css/faq-accordion.css' ) ?: '';
$theme_functions = file_get_contents( get_template_directory() . '/functions.php' ) ?: '';
$main_js = file_get_contents( get_template_directory() . '/assets/js/main.js' ) ?: '';

ob_start();
Logika_Theme_Source_Markup::renderPage( 'faq' );
$rendered_page = (string) ob_get_clean();

foreach ( array( 'faq-banner-section', 'template-parts/sections/faq', 'testimonials-section', 'school-map', 'cta-section' ) as $section ) {
	if ( ! str_contains( $source_page, $section ) ) {
		fwrite( STDERR, "The full FAQ page is missing {$section}.\n" );
		exit( 1 );
	}
}

foreach ( array( 'faq-section', 'accordion accordion--mode', 'faq-left-bg.svg', 'faq-right-bg.svg' ) as $contract ) {
	if ( ! str_contains( $rendered_page, $contract ) ) {
		fwrite( STDERR, "Rendered FAQ page is missing {$contract}.\n" );
		exit( 1 );
	}
}

foreach ( array( 'faq-banner-section', 'accordion accordion--mode', 'З якого віку дитина може навчатися в Logika?' ) as $contract ) {
	if ( ! str_contains( $source_page, $contract ) ) {
		fwrite( STDERR, "FAQ page is missing the static {$contract} contract.\n" );
		exit( 1 );
	}
}

if ( ! str_contains( $source_page, 'data-map-online-form' ) ) {
	fwrite( STDERR, "FAQ map is missing the homepage-form source.\n" );
	exit( 1 );
}

foreach ( array( '&--bordered-violet', 'border: 1px solid var(--violet-100)' ) as $contract ) {
	if ( ! str_contains( $buttons, $contract ) ) {
		fwrite( STDERR, "FAQ source button styles are missing {$contract}.\n" );
		exit( 1 );
	}
}

if ( ! str_contains( $faq_style, '.btn--bordered-violet' ) ) {
	fwrite( STDERR, "FAQ button border style is not enqueued.\n" );
	exit( 1 );
}

foreach ( array( '&--mode', 'border: 1px solid var(--violet-100)' ) as $contract ) {
	if ( ! str_contains( $accordion, $contract ) ) {
		fwrite( STDERR, "FAQ accordion mode is missing {$contract}.\n" );
		exit( 1 );
	}
}

if ( ! str_contains( $faq_accordion_style, '.accordion--mode>li' ) || ! str_contains( $theme_functions, 'logika-faq-accordion' ) ) {
	fwrite( STDERR, "FAQ accordion mode is not served by the theme.\n" );
	exit( 1 );
}

if ( ! str_contains( $main_js, 'if (!defaultOpenContent || !defaultOpenButton)') ) {
	fwrite( STDERR, "FAQ accordion crashes when its default item is absent.\n" );
	exit( 1 );
}

echo "FAQ page keeps the full main HTML structure.\n";
