<?php

declare(strict_types=1);

require dirname( __DIR__ ) . '/wordpress/wp-load.php';

use Logika\Core\AdminUi;

$errors = array();
$fields = acf_get_fields( 'group_logika_city' ) ?: array();
$by_key = array_column( $fields, null, 'key' );
$tabs   = array_values( array_filter( $fields, static fn( array $field ): bool => 'tab' === $field['type'] ) );

if ( 'message' !== ( $fields[0]['type'] ?? '' ) || ! str_contains( (string) ( $fields[0]['message'] ?? '' ), 'за 1 хвилину' ) ) {
	$errors[] = 'City editor must start with a one-minute Ukrainian guide.';
}

if ( array_column( $tabs, 'label' ) !== array( 'Основне', 'Карта', 'Контент міста', 'Добірки', 'SEO і технічне' ) ) {
	$errors[] = 'City fields must be split into five clear editor tabs.';
}

$region = $by_key['field_city_region'] ?? array();
if ( empty( $region['save_terms'] ) || empty( $region['load_terms'] ) ) {
	$errors[] = 'The ACF region selector must be the taxonomy source of truth.';
}

$map = $by_key['field_city_show_on_map'] ?? array();
if ( 'true_false' !== ( $map['type'] ?? '' ) || 1 !== (int) ( $map['default_value'] ?? 0 ) ) {
	$errors[] = 'New cities must expose an enabled map toggle.';
}

$branch_fields = acf_get_fields( 'group_logika_branch' ) ?: array();
$branch_by_key = array_column( $branch_fields, null, 'key' );
$branch_tabs   = array_values( array_filter( $branch_fields, static fn( array $field ): bool => 'tab' === $field['type'] ) );
if ( 'message' !== ( $branch_fields[0]['type'] ?? '' ) || ! str_contains( (string) ( $branch_fields[0]['message'] ?? '' ), 'адресу філії' ) ) {
	$errors[] = 'Branch editor must start with a short Ukrainian guide.';
}
if ( array_column( $branch_tabs, 'label' ) !== array( 'Основне', 'Карта і контакти', 'Технічне' ) ) {
	$errors[] = 'Branch fields must be split into three clear editor tabs.';
}
$address_hash = $branch_by_key['field_branch_address_hash'] ?? array();
if ( ! empty( $address_hash['required'] ) || empty( $address_hash['readonly'] ) ) {
	$errors[] = 'Branch address hash must be automatic and read-only.';
}
$branch_active = $branch_by_key['field_branch_is_active'] ?? array();
if ( 1 !== (int) ( $branch_active['default_value'] ?? 0 ) ) {
	$errors[] = 'New branches must be active by default.';
}
$branch_address = $branch_by_key['field_branch_address'] ?? array();
if ( ! str_contains( (string) ( $branch_address['instructions'] ?? '' ), 'автоматично' ) ) {
	$errors[] = 'Branch address must explain that the map marker appears automatically.';
}

$city = (object) array( 'post_type' => 'city' );
if ( 'Назва міста, наприклад Львів' !== apply_filters( 'enter_title_here', 'Add title', $city ) ) {
	$errors[] = 'City title must have a useful Ukrainian example.';
}

if ( false === has_action( 'add_meta_boxes_city', array( AdminUi::class, 'removeCityRegionMetaBox' ) ) ) {
	$errors[] = 'The duplicate native region metabox must be hidden on city screens.';
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "City editor has one guided region workflow.\n";
