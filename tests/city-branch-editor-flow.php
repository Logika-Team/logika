<?php

declare(strict_types=1);

require dirname( __DIR__ ) . '/wordpress/wp-load.php';

use Logika\Core\AdminUi;
use Logika\Core\CitySlug;

$errors = array();
$ids    = array();

register_shutdown_function(
	static function () use ( &$ids ): void {
		foreach ( $ids as $id ) {
			wp_delete_post( $id, true );
		}
	}
);

$city_id = (int) wp_insert_post( array( 'post_type' => 'city', 'post_status' => 'draft', 'post_title' => 'Місто редакторського потоку' ) );
$other_id = (int) wp_insert_post( array( 'post_type' => 'city', 'post_status' => 'draft', 'post_title' => 'Інше місто редакторського потоку' ) );
$not_city_id = (int) wp_insert_post( array( 'post_type' => 'post', 'post_status' => 'draft', 'post_title' => 'Не місто' ) );
$branch_id = (int) wp_insert_post( array( 'post_type' => 'branch', 'post_status' => 'draft', 'post_title' => 'Тестова філія редактора' ) );
$ids = array( $city_id, $other_id, $not_city_id, $branch_id );

if ( ! method_exists( CitySlug::class, 'fromTitle' ) || 'bila-tserkva' !== CitySlug::fromTitle( 'Біла Церква' ) ) {
	$errors[] = 'City title transliteration is not reusable.';
}

if ( ! method_exists( AdminUi::class, 'branchAddressHash' ) ) {
	$errors[] = 'Branch address hash helper is missing.';
} elseif ( AdminUi::branchAddressHash( $city_id, '  вул. Шкільна,  10 ' ) !== AdminUi::branchAddressHash( $city_id, 'вул. шкільна, 10' ) ) {
	$errors[] = 'Branch address hash is not stable after normalization.';
}

if ( ! method_exists( AdminUi::class, 'findDuplicateCity' ) ) {
	$errors[] = 'City duplicate lookup is missing.';
} else {
	$duplicate = AdminUi::findDuplicateCity( 'Місто редакторського потоку', '', $other_id );
	if ( ! $duplicate instanceof WP_Post || $city_id !== $duplicate->ID ) {
		$errors[] = 'City duplicate lookup does not use the canonical URL.';
	}
}

if ( method_exists( AdminUi::class, 'validateUniqueCity' ) ) {
	$original_post = $_POST;
	acf_reset_validation_errors();
	$_POST = array( 'post_ID' => $other_id, 'post_type' => 'city', 'post_title' => 'Місто редакторського потоку', 'acf' => array( 'field_city_url_slug' => '' ) );
	AdminUi::validateUniqueCity();
	$_POST = $original_post;
	if ( ! acf_get_validation_errors() ) {
		$errors[] = 'Duplicate city submission is not blocked.';
	}
	acf_reset_validation_errors();
} else {
	$errors[] = 'Duplicate city validation hook is missing.';
}

if ( ! method_exists( AdminUi::class, 'prepareBranchCityField' ) ) {
	$errors[] = 'Branch city prefill is missing.';
} else {
	$_GET['city_id'] = (string) $city_id;
	$field = AdminUi::prepareBranchCityField( array( 'key' => 'field_branch_city_id', 'instructions' => '' ) );
	if ( $city_id !== (int) ( $field['value'] ?? 0 ) ) {
		$errors[] = 'Valid city query parameter does not prefill the branch.';
	}
	$_GET['city_id'] = (string) $not_city_id;
	$field = AdminUi::prepareBranchCityField( array( 'key' => 'field_branch_city_id', 'instructions' => '' ) );
	if ( ! empty( $field['value'] ) ) {
		$errors[] = 'Invalid city query parameter prefills the branch.';
	}
	unset( $_GET['city_id'] );
}

if ( ! method_exists( AdminUi::class, 'prepareCityMapField' ) ) {
	$errors[] = 'City map field does not expose quick branch creation.';
} else {
	$GLOBALS['post'] = get_post( $city_id );
	$field = AdminUi::prepareCityMapField( array( 'key' => 'field_city_show_on_map', 'instructions' => '' ) );
	if ( ! str_contains( (string) ( $field['instructions'] ?? '' ), 'post-new.php?post_type=branch&#038;city_id=' . $city_id ) ) {
		$errors[] = 'City map field does not link to a prefilled branch form.';
	}
}

if ( method_exists( AdminUi::class, 'saveBranchHash' ) && method_exists( AdminUi::class, 'branchAddressHash' ) ) {
	update_field( 'branch_city_id', $city_id, $branch_id );
	update_field( 'branch_address', 'вул. Шкільна, 10', $branch_id );
	AdminUi::saveBranchHash( $branch_id );
	$first = (string) get_post_meta( $branch_id, 'branch_address_hash', true );
	AdminUi::saveBranchHash( $branch_id );
	if ( '' === $first || $first !== get_post_meta( $branch_id, 'branch_address_hash', true ) ) {
		$errors[] = 'Repeated branch save does not keep one stable address hash.';
	}
} else {
	$errors[] = 'Automatic branch hash save is missing.';
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "City and branch editors share one predictable map workflow.\n";
