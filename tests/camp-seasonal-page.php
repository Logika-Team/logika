<?php

declare(strict_types=1);

$root      = dirname(__DIR__);
$group      = (string) file_get_contents( $root . '/wordpress/wp-content/plugins/logika-core/acf-json/group_logika_camp.json' );
$content    = (string) file_get_contents( $root . '/wordpress/wp-content/themes/logika-theme/src/PageContent.php' );
$modal      = (string) file_get_contents( $root . '/wordpress/wp-content/themes/logika-theme/template-parts/components/camp-modal.php' );
$errors     = array();

foreach ( array( 'camp_card_image', 'camp_card_description', 'camp_hero_dates_text', 'camp_hero_form_title', 'camp_hero_facts', 'camp_includes', 'camp_booking_benefits', 'camp_booking_form_title' ) as $field ) {
	if ( ! str_contains( $group, '"name": "' . $field . '"' ) ) {
		$errors[] = "Camp ACF field {$field} is missing.";
	}
}

foreach ( array( 'applyCampHeroDates', 'applyCampHeroFacts', 'applyCampTrips', 'applyCampIncludes', 'applyCampBooking' ) as $method ) {
	if ( ! str_contains( $content, 'function ' . $method ) ) {
		$errors[] = "Camp page renderer is missing {$method}.";
	}
}

if ( ! str_contains( $content, 'href="\\#form"' ) ) {
	$errors[] = 'Camp CTA route must be escaped inside the renderer pattern.';
}

foreach ( array( '$camps  = get_posts( array(', 'get_permalink( $camp_id )', 'camp_card_description' ) as $marker ) {
	if ( ! str_contains( $modal, $marker ) ) {
		$errors[] = "Camp modal is missing {$marker}.";
	}
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Seasonal camp page contract is valid.\n";
