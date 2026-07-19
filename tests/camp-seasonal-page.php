<?php

declare(strict_types=1);

$root      = dirname(__DIR__);
$group      = (string) file_get_contents( $root . '/wordpress/wp-content/plugins/logika-core/acf-json/group_logika_camp.json' );
$content    = (string) file_get_contents( $root . '/wordpress/wp-content/themes/logika-theme/src/PageContent.php' );
$modal      = (string) file_get_contents( $root . '/wordpress/wp-content/themes/logika-theme/template-parts/components/camp-modal.php' );
$errors     = array();

foreach ( array( 'camp_card_image', 'camp_card_description', 'camp_hero_dates_text', 'camp_hero_form_title', 'camp_hero_facts', 'camp_hero_images', 'camp_details', 'camp_gallery' ) as $field ) {
	if ( ! str_contains( $group, '"name": "' . $field . '"' ) ) {
		$errors[] = "Camp ACF field {$field} is missing.";
	}
}

foreach ( array( 'applyCampHeroDates', 'applyCampHeroFacts', 'applyCampHeroImages', 'applyCampDetailGalleries' ) as $method ) {
	if ( ! str_contains( $content, 'function ' . $method ) ) {
		$errors[] = "Camp page renderer is missing {$method}.";
	}
}

if ( ! str_contains( $content, 'href="\\#lead-form"' ) ) {
	$errors[] = 'Camp CTA route must target the shared lead modal.';
}

foreach ( array( 'get_posts( array(', 'get_permalink( $camp_id )', 'camp_card_description' ) as $marker ) {
	if ( ! str_contains( $modal, $marker ) ) {
		$errors[] = "Camp modal is missing {$marker}.";
	}
}

$camp_source = (string) file_get_contents( $root . '/wordpress/wp-content/themes/logika-theme/source-pages/camp.php' );
foreach ( array( 'camp-booking__form banner-section__form main-form', 'name="city" placeholder="Ваше місто"' ) as $marker ) {
	if ( ! str_contains( $camp_source, $marker ) ) {
		$errors[] = "Camp booking must reuse the hero form {$marker}.";
	}
}
if ( str_contains( $camp_source, 'camp-booking__camp-select' ) ) {
	$errors[] = 'Camp booking must not keep its legacy camp selector.';
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Seasonal camp page contract is valid.\n";
