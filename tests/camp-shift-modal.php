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
		'Дізнатись більше',
		"'post_type'      => 'camp'",
		"'key' => 'camp_is_active'",
		"get_field( 'camp_archive_formats', \$page_id )",
		"get_field( \$season_field, \$page_id )",
		'\'post__in\'       => $ids',
		'get_permalink( $camp_id )',
		'camp_card_description',
		'data-camp-season="<?php echo esc_attr( $season_key ); ?>"',
		'camp_archive_summer_camps',
		'camp_archive_autumn_camps',
		'camp_archive_winter_camps',
		'camp_archive_spring_camps',
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

foreach ( array( '[data-logika-camp-modal]', 'data-path="camps"', 'Escape', 'data-camp-season' ) as $marker ) {
	if ( ! str_contains( $script, $marker ) ) {
		$errors[] = "Camp modal interaction is missing {$marker}.";
	}
}

foreach ( array( '.modal__camps', '.modal__camps-items', '.modal__camps-link', '.modal__camps-items[hidden]', 'font-family: var(--font-primary)' ) as $marker ) {
	if ( ! str_contains( $style, $marker ) ) {
		$errors[] = "Camp modal styles are missing {$marker}.";
	}
}

foreach (
	array(
		'camp-formats__item-season',
		'Літо',
		'Осінь',
		'Зима',
		'Весна',
		'data-path="camps"',
		'data-camp-season="summer"',
		'data-camp-season="autumn"',
		'data-camp-season="winter"',
		'data-camp-season="spring"',
	) as $marker
) {
	if ( ! str_contains( $page, $marker ) ) {
		$errors[] = "Camp format season selector is missing {$marker}.";
	}
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Camp shift modal contract is valid.\n";
