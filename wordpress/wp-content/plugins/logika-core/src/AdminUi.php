<?php

declare(strict_types=1);

namespace Logika\Core;

use WP_Post;

final class AdminUi {
	private const POST_TYPES = array( 'city', 'branch', 'course', 'camp', 'review', 'faq_item', 'article_author' );

	public static function register(): void {
		add_filter( 'acf/prepare_field/type=relationship', array( self::class, 'prepareCourseField' ) );
		add_filter( 'acf/prepare_field/type=post_object', array( self::class, 'prepareCourseField' ) );
		add_filter( 'acf/prepare_field/key=field_city_show_on_map', array( self::class, 'prepareCityMapField' ) );
		add_filter( 'acf/prepare_field/key=field_branch_city_id', array( self::class, 'prepareBranchCityField' ) );
		add_action( 'acf/save_post', array( self::class, 'saveBranchHash' ), 20 );
		add_action( 'acf/validate_save_post', array( self::class, 'validateUniqueCity' ) );
		add_filter( 'enter_title_here', array( self::class, 'titlePlaceholder' ), 10, 2 );
		add_action( 'add_meta_boxes_city', array( self::class, 'removeCityRegionMetaBox' ) );
		add_action( 'add_meta_boxes_post', array( self::class, 'removePostTagsMetaBox' ) );
		add_filter( 'rest_prepare_taxonomy', array( self::class, 'hidePostTagsPanel' ), 10, 3 );
		add_filter( 'acf/validate_value/type=url', array( self::class, 'allowInternalUrl' ), 20, 2 );
		add_filter( 'acf/prepare_field/type=url', array( self::class, 'renderUrlAsText' ) );
		foreach ( self::POST_TYPES as $post_type ) {
			add_filter( 'get_user_option_meta-box-order_' . $post_type, array( self::class, 'publishBoxFirst' ) );
		}
	}

	/**
	 * ACF's url field only accepts absolute URLs, so the in-page anchors and root-relative paths
	 * editors legitimately enter for buttons (`#lead-form`, `/camps/`) are rejected on save.
	 *
	 * @param mixed $valid
	 * @param mixed $value
	 * @return mixed
	 */
	public static function allowInternalUrl( $valid, $value ) {
		if ( true === $valid || ! is_string( $value ) ) {
			return $valid;
		}

		$value = trim( $value );
		if ( str_starts_with( $value, '#' ) || preg_match( '#^(mailto:|tel:)#i', $value ) ) {
			return true;
		}
		if ( str_starts_with( $value, '/' ) && ! str_starts_with( $value, '//' ) ) {
			return true;
		}

		return $valid;
	}

	/**
	 * ACF renders url fields as `<input type="url">`, so the browser itself refuses to submit
	 * the form ("Please enter a URL.") before any of our server-side validation runs. Render the
	 * plain text input instead; the value is still validated on save by allowInternalUrl().
	 *
	 * @param mixed $field
	 * @return mixed
	 */
	public static function renderUrlAsText( $field ) {
		if ( ! is_array( $field ) ) {
			return $field;
		}

		// The text renderer reads settings the url field type does not define.
		$field = array_merge( array( 'prepend' => '', 'append' => '', 'maxlength' => '' ), $field );
		$field['type'] = 'text';

		return $field;
	}

	/**
	 * The side column is rendered before the field groups, but a stored box order (or a group
	 * dragged into the side column) can push the Publish box below the whole ACF form. Pin it
	 * back to the top of its column.
	 *
	 * @param mixed $order
	 * @return mixed
	 */
	public static function publishBoxFirst( $order ) {
		if ( ! is_array( $order ) || ! isset( $order['side'] ) || ! is_string( $order['side'] ) ) {
			return $order;
		}

		$boxes = array_filter( array_map( 'trim', explode( ',', $order['side'] ) ) );
		if ( ! in_array( 'submitdiv', $boxes, true ) ) {
			return $order;
		}

		$order['side'] = implode( ',', array_merge( array( 'submitdiv' ), array_diff( $boxes, array( 'submitdiv' ) ) ) );

		return $order;
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

	/**
	 * Теги запису редагуються полем «Теги» (field_post_tags). Стандартний блок тегів редактора
	 * зберігає ту саму таксономію й перетирає значення поля, тому його прибрано в обох редакторах.
	 */
	public static function removePostTagsMetaBox(): void {
		remove_meta_box( 'tagsdiv-post_tag', 'post', 'side' );
	}

	/**
	 * @param mixed $response
	 * @param mixed $taxonomy
	 * @param mixed $request
	 * @return mixed
	 */
	public static function hidePostTagsPanel( $response, $taxonomy, $request ) {
		if ( ! $response instanceof \WP_REST_Response || ! is_object( $taxonomy ) || 'post_tag' !== ( $taxonomy->name ?? '' ) ) {
			return $response;
		}
		if ( ! is_object( $request ) || 'edit' !== $request['context'] ) {
			return $response;
		}
		$data = $response->get_data();
		if ( isset( $data['visibility']['show_ui'] ) ) {
			$data['visibility']['show_ui'] = false;
			$response->set_data( $data );
		}

		return $response;
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
		$link = '<a href="' . esc_url( admin_url( 'edit.php?post_type=course' ) ) . '">+ Додати новий курс (дублюйте наявний у списку курсів)</a>';
		$field['instructions'] = trim( (string) ( $field['instructions'] ?? '' ) . '<br>' . $link );

		return $field;
	}
}
