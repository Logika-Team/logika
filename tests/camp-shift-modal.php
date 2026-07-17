<?php

declare(strict_types=1);

$theme     = dirname(__DIR__) . '/wordpress/wp-content/themes/logika-theme';
$component = (string) file_get_contents( $theme . '/template-parts/components/camp-modal.php' );
$functions = (string) file_get_contents( $theme . '/functions.php' );
$script    = (string) file_get_contents( $theme . '/assets/js/main.js' );
$style     = (string) file_get_contents( $theme . '/assets/css/camp-modal.css' );
$page      = (string) file_get_contents( $theme . '/source-pages/camps.php' );
$errors    = array();

foreach (
	array(
		'class="modal"',
		'data-logika-camp-modal',
		'class="modal__container is-camps"',
		'data-target="camps"',
		'Оберіть зміну',
		'Фестиваль професій',
		'Дізнатись більше',
	) as $marker
) {
	if ( ! str_contains( $component, $marker ) ) {
		$errors[] = "Camp modal is missing {$marker}.";
	}
}

foreach (
	array(
		'logika_theme_render_camp_modal',
		'template-parts/components/camp-modal',
		'wp_footer',
	) as $marker
) {
	if ( ! str_contains( $functions, $marker ) ) {
		$errors[] = "Camp modal is not wired through {$marker}.";
	}
}

foreach ( array( '[data-logika-camp-modal]', 'data-path="camps"', 'Escape' ) as $marker ) {
	if ( ! str_contains( $script, $marker ) ) {
		$errors[] = "Camp modal interaction is missing {$marker}.";
	}
}

foreach ( array( '.modal__camps', '.modal__camps-items', '.modal__camps-link' ) as $marker ) {
	if ( ! str_contains( $style, $marker ) ) {
		$errors[] = "Camp modal styles are missing {$marker}.";
	}
}

if ( substr_count( $page, 'data-path="camps"' ) !== 4 ) {
	$errors[] = 'All four camp format cards must open the camp modal.';
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Camp shift modal contract is valid.\n";
