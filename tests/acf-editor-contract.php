<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$errors     = array();
$group_keys = array();
$field_keys = array();
$locations  = array();
$json_dir   = dirname(__DIR__) . '/wordpress/wp-content/plugins/logika-core/acf-json';

$visit_fields = static function ( array $fields, string $group_key ) use ( &$visit_fields, &$field_keys, &$errors ): void {
	foreach ( $fields as $field ) {
		$key  = (string) ( $field['key'] ?? '' );
		$type = (string) ( $field['type'] ?? '' );
		$name = (string) ( $field['name'] ?? '' );
		if ( ! array_key_exists( 'name', $field ) ) {
			$errors[] = "{$group_key}.{$key} has no canonical name property.";
		}
		if ( '' === $key || isset( $field_keys[ $key ] ) ) {
			$errors[] = "Duplicate or empty field key {$key} in {$group_key}.";
		} else {
			$field_keys[ $key ] = true;
		}
		if ( ! in_array( $type, array( 'tab', 'message' ), true ) && '' === trim( (string) ( $field['instructions'] ?? '' ) ) ) {
			$errors[] = "{$group_key}.{$name} has no editor instructions.";
		}
		if ( in_array( $type, array( 'image', 'gallery' ), true ) && ( 'id' !== ( $field['return_format'] ?? '' ) || 'medium' !== ( $field['preview_size'] ?? '' ) ) ) {
			$errors[] = "{$group_key}.{$name} must return an ID and show a medium preview.";
		}
		$visit_fields( (array) ( $field['sub_fields'] ?? array() ), $group_key );
		foreach ( (array) ( $field['layouts'] ?? array() ) as $layout ) {
			$visit_fields( (array) ( $layout['sub_fields'] ?? array() ), $group_key );
		}
	}
};

foreach ( glob( $json_dir . '/group_logika_*.json' ) as $file ) {
	$group = json_decode( (string) file_get_contents( $file ), true, 512, JSON_THROW_ON_ERROR );
	$key   = (string) ( $group['key'] ?? '' );
	if ( '' === $key || isset( $group_keys[ $key ] ) ) {
		$errors[] = "Duplicate or empty field group key {$key}.";
	} else {
		$group_keys[ $key ] = true;
	}
	if ( empty( $group['show_in_rest'] ) || empty( $group['allow_ai_access'] ) || '' === trim( (string) ( $group['ai_description'] ?? '' ) ) ) {
		$errors[] = "{$key} is not available to the controlled ACF runtime.";
	}
	$location = wp_json_encode( $group['location'] ?? array() );
	if ( isset( $locations[ $location ] ) ) {
		$errors[] = "{$key} duplicates the location rules of {$locations[ $location ]}.";
	} else {
		$locations[ $location ] = $key;
	}
	$visit_fields( (array) ( $group['fields'] ?? array() ), $key );
}

$global_names = array_column( acf_get_fields( 'group_logika_global' ) ?: array(), 'name' );
foreach ( array( 'global_header_logo', 'global_footer_logo', 'global_footer_accreditation', 'global_footer_copyright', 'global_partners', 'global_certificates' ) as $name ) {
	if ( ! in_array( $name, $global_names, true ) ) {
		$errors[] = "Global Options is missing {$name}.";
	}
}

$menus = get_registered_nav_menus();
foreach ( array( 'primary', 'footer_navigation', 'footer_information' ) as $location ) {
	if ( ! isset( $menus[ $location ] ) ) {
		$errors[] = "Menu location {$location} is missing.";
	}
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "ACF fields follow the shared editor contract.\n";
