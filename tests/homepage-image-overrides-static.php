<?php

declare(strict_types=1);

$root    = dirname(__DIR__);
$plugin  = $root . '/wordpress/wp-content/plugins/logika-core/logika-core.php';
$json    = $root . '/wordpress/wp-content/plugins/logika-core/acf-json/group_logika_home.json';
$markup  = $root . '/wordpress/wp-content/themes/logika-theme/src/SourceMarkup.php';
$errors  = array();
$expected = array(
	'field_home_hero_boy_image_override',
	'field_home_hero_character_image_override',
	'field_home_trust_item_icon_override',
	'field_home_english_level_image_override',
	'field_home_programming_courses_image_override',
	'field_home_programming_courses_icon_override',
	'field_home_transformation_before_image_override',
	'field_home_transformation_after_image_override',
	'field_home_onboarding_steps_image_override',
	'field_home_certificates_image_override',
	'field_home_partners_items_image_override',
);

$plugin_source = (string) file_get_contents( $plugin );
$markup_source = (string) file_get_contents( $markup );
$json_source   = (string) file_get_contents( $json );
$fields        = json_decode( (string) file_get_contents( $json ), true, 512, JSON_THROW_ON_ERROR );
$field_keys    = array();

$collect = static function ( array $items ) use ( &$collect, &$field_keys ): void {
	foreach ( $items as $item ) {
		if ( isset( $item['key'] ) ) {
			$field_keys[] = $item['key'];
		}

		if ( ! empty( $item['sub_fields'] ) && is_array( $item['sub_fields'] ) ) {
			$collect( $item['sub_fields'] );
		}
	}
};

$collect( $fields['fields'] ?? array() );

foreach ( $expected as $field_key ) {
	if ( ! in_array( $field_key, $field_keys, true ) ) {
		$errors[] = "Missing ACF override field: {$field_key}";
	}
}

if ( ! preg_match( '/"key": "field_home_trust_item_icon_override".*?"label": "".*?"instructions": "".*?"wrapper": \{ "width": "100"/s', $json_source ) ) {
	$errors[] = 'Trust icon override still renders an ACF label column instead of a full-width replacement panel.';
}

$trust_fields = array_values(
	array_filter(
		$fields['fields'] ?? array(),
		static fn( array $field ): bool => 'field_home_trust_items' === ( $field['key'] ?? '' )
	)
);

if ( ! isset( $trust_fields[0] ) || 'block' !== ( $trust_fields[0]['layout'] ?? '' ) ) {
	$errors[] = 'Trust item text is still constrained by the ACF table layout.';
}

if ( ! str_contains( $plugin_source, 'HomepageImageOverrides::register' ) ) {
	$errors[] = 'Homepage image overrides are not registered by Logika Core.';
}

$admin_script = (string) file_get_contents( $root . '/wordpress/wp-content/plugins/logika-core/assets/js/homepage-image-overrides.js' );
foreach ( array( 'logika-image-override-native', 'logika-image-override-panel', 'logika-image-override-selected' ) as $needle ) {
	if ( ! str_contains( $admin_script, $needle ) ) {
		$errors[] = "Homepage image override UI does not provide {$needle}.";
	}
}

foreach ( array( 'home_hero_boy_image_override', 'home_hero_character_image_override', 'image_override' ) as $needle ) {
	if ( ! str_contains( $markup_source, $needle ) ) {
		$errors[] = "Homepage markup does not read {$needle}.";
	}
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Homepage override fields, registration, and template reads are present.\n";
