<?php

declare(strict_types=1);

require dirname( __DIR__ ) . '/wordpress/wp-load.php';

use Logika\Core\ContentMigration;

$slug   = 'duplicate-migration-fixture';
$marker = 'logika_city_merge_' . md5( $slug ) . '_v1';
$ids    = array();
$errors = array();

register_shutdown_function(
	static function () use ( &$ids, $marker ): void {
		delete_option( $marker );
		foreach ( $ids as $id ) {
			wp_delete_post( $id, true );
		}
	}
);

delete_option( $marker );
$duplicate_id = (int) wp_insert_post( array( 'post_type' => 'city', 'post_status' => 'draft', 'post_title' => 'Місто-дублікат міграції' ) );
$canonical_id = (int) wp_insert_post( array( 'post_type' => 'city', 'post_status' => 'draft', 'post_title' => 'Канонічне місто міграції' ) );
$branch_id    = (int) wp_insert_post( array( 'post_type' => 'branch', 'post_status' => 'draft', 'post_title' => 'Філія дубліката міграції' ) );
$ids = array( $duplicate_id, $canonical_id, $branch_id );

foreach ( array( $duplicate_id, $canonical_id ) as $city_id ) {
	update_field( 'city_url_slug', $slug, $city_id );
}
update_field( 'city_external_id', 'fixture-canonical', $canonical_id );
update_field( 'city_intro', 'Не перезаписувати', $canonical_id );
update_field( 'city_intro', 'Нове значення', $duplicate_id );
update_field( 'city_seo_title', 'Скопійоване SEO', $duplicate_id );
update_field( 'branch_city_id', $duplicate_id, $branch_id );

if ( ! method_exists( ContentMigration::class, 'migrateCitySlug' ) ) {
	$errors[] = 'City duplicate migration entry point is missing.';
} else {
	$report = ContentMigration::migrateCitySlug( $slug );
	if ( empty( $report['changed'] ) ) {
		$errors[] = 'First city duplicate migration reports no changes.';
	}
	if ( 'Не перезаписувати' !== get_field( 'city_intro', $canonical_id ) || 'Скопійоване SEO' !== get_field( 'city_seo_title', $canonical_id ) ) {
		$errors[] = 'City migration does not preserve and fill canonical fields correctly.';
	}
	if ( $canonical_id !== (int) get_field( 'branch_city_id', $branch_id ) ) {
		$errors[] = 'City migration does not reassign branches.';
	}
	if ( 'trash' !== get_post_status( $duplicate_id ) ) {
		$errors[] = 'City migration does not trash the duplicate.';
	}
	$second = ContentMigration::migrateCitySlug( $slug );
	if ( 0 !== (int) ( $second['changed'] ?? -1 ) ) {
		$errors[] = 'Repeated city duplicate migration is not idempotent.';
	}
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "City duplicate migration preserves data and is idempotent.\n";
