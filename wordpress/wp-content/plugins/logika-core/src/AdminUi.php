<?php

declare(strict_types=1);

namespace Logika\Core;

use WP_Post;

final class AdminUi {
	public static function register(): void {
		add_filter( 'acf/prepare_field/type=relationship', array( self::class, 'prepareCourseField' ) );
		add_filter( 'acf/prepare_field/type=post_object', array( self::class, 'prepareCourseField' ) );
		add_filter( 'acf/prepare_field/key=field_city_show_on_map', array( self::class, 'prepareCityMapField' ) );
		add_filter( 'acf/prepare_field/key=field_branch_city_id', array( self::class, 'prepareBranchCityField' ) );
		add_action( 'acf/save_post', array( self::class, 'saveBranchHash' ), 20 );
		add_action( 'acf/validate_save_post', array( self::class, 'validateUniqueCity' ) );
		add_filter( 'enter_title_here', array( self::class, 'titlePlaceholder' ), 10, 2 );
		add_action( 'add_meta_boxes_city', array( self::class, 'removeCityRegionMetaBox' ) );
	}

	public static function titlePlaceholder( string $title, object $post ): string {
		return match ( $post->post_type ?? '' ) {
			'city'   => 'Назва міста, наприклад Львів',
			'branch' => 'Назва філії або району, наприклад Центр',
			default  => $title,
		};
	}

	public static function removeCityRegionMetaBox(): void {
		remove_meta_box( 'regiondiv', 'city', 'side' );
	}

	public static function prepareCityMapField( array $field ): array {
		$post = $GLOBALS['post'] ?? null;
		if ( $post instanceof WP_Post && 'city' === $post->post_type ) {
			$url = admin_url( 'post-new.php?post_type=branch&city_id=' . $post->ID );
			$field['instructions'] = trim( (string) ( $field['instructions'] ?? '' ) . '<br><a href="' . esc_url( $url ) . '">+ Додати адресу філії</a>' );
		}

		return $field;
	}

	public static function prepareBranchCityField( array $field ): array {
		$city_id = isset( $_GET['city_id'] ) ? absint( wp_unslash( $_GET['city_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$city    = $city_id ? get_post( $city_id ) : null;
		if ( $city instanceof WP_Post && 'city' === $city->post_type ) {
			$field['value'] = $city_id;
		}

		return $field;
	}

	public static function branchAddressHash( int $city_id, string $address ): string {
		$address = mb_strtolower( trim( preg_replace( '/\s+/u', ' ', $address ) ?? $address ), 'UTF-8' );

		return hash( 'sha256', $city_id . '|' . $address );
	}

	public static function saveBranchHash( mixed $post_id ): void {
		if ( ! is_numeric( $post_id ) || 'branch' !== get_post_type( (int) $post_id ) ) {
			return;
		}
		$city_id = (int) get_field( 'branch_city_id', (int) $post_id );
		$address = trim( (string) get_field( 'branch_address', (int) $post_id ) );
		if ( ! $city_id || '' === $address ) {
			return;
		}
		$hash = self::branchAddressHash( $city_id, $address );
		if ( $hash !== get_post_meta( (int) $post_id, 'branch_address_hash', true ) ) {
			update_field( 'field_branch_address_hash', $hash, (int) $post_id );
		}
	}

	public static function findDuplicateCity( string $title, string $custom_slug = '', int $exclude_id = 0 ): ?WP_Post {
		$slug = $custom_slug ? sanitize_title( $custom_slug ) : CitySlug::fromTitle( $title );
		foreach ( get_posts( array( 'post_type' => 'city', 'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future' ), 'posts_per_page' => -1 ) ) as $city ) {
			if ( $city->ID !== $exclude_id && $slug === CitySlug::for( $city ) ) {
				return $city;
			}
		}

		return null;
	}

	public static function validateUniqueCity(): void {
		$post_id   = isset( $_POST['post_ID'] ) ? absint( wp_unslash( $_POST['post_ID'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$post_type = isset( $_POST['post_type'] ) ? sanitize_key( wp_unslash( $_POST['post_type'] ) ) : get_post_type( $post_id ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$title     = isset( $_POST['post_title'] ) ? sanitize_text_field( wp_unslash( $_POST['post_title'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$acf       = isset( $_POST['acf'] ) && is_array( $_POST['acf'] ) ? wp_unslash( $_POST['acf'] ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$slug      = sanitize_title( (string) ( $acf['field_city_url_slug'] ?? '' ) );
		if ( 'city' !== $post_type || '' === $title ) {
			return;
		}
		$duplicate = self::findDuplicateCity( $title, $slug, $post_id );
		if ( $duplicate ) {
			$message = sprintf( 'Місто з такою URL-адресою вже існує. <a href="%s">Редагувати «%s»</a>.', esc_url( (string) get_edit_post_link( $duplicate->ID, 'raw' ) ), esc_html( $duplicate->post_title ) );
			acf_add_validation_error( '', wp_kses_post( $message ) );
		}
	}

	public static function prepareCourseField( array $field ): array {
		if ( ! in_array( 'course', (array) ( $field['post_type'] ?? array() ), true ) ) {
			return $field;
		}
		$link = '<a href="' . esc_url( admin_url( 'post-new.php?post_type=course' ) ) . '">+ Додати новий курс із готовою структурою</a>';
		$field['instructions'] = trim( (string) ( $field['instructions'] ?? '' ) . '<br>' . $link );

		return $field;
	}
}
