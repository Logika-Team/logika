<?php

declare(strict_types=1);

namespace Logika\Core;

/**
 * Теги запису редагуються двома полями однієї таксономії: «Теги міст» (керує видимістю
 * матеріалу в місті) і «Власні теги» (тематичні позначки та створення нових через «+»).
 * Тому обидва поля не зберігають терміни самі (save_terms = 0), а значення читаються з
 * реальних тегів запису й записуються назад одним об’єднаним набором.
 */
final class PostTagsFields {
	private const CITY   = 'field_post_city_tags';
	private const CUSTOM = 'field_post_custom_tags';

	public static function register(): void {
		add_filter( 'acf/load_value/key=' . self::CITY, array( self::class, 'loadCityValue' ), 10, 3 );
		add_filter( 'acf/load_value/key=' . self::CUSTOM, array( self::class, 'loadCustomValue' ), 10, 3 );
		add_filter( 'acf/fields/taxonomy/query/key=' . self::CITY, array( self::class, 'cityQuery' ), 10, 3 );
		add_filter( 'acf/fields/taxonomy/query/key=' . self::CUSTOM, array( self::class, 'customQuery' ), 10, 3 );
		add_action( 'acf/save_post', array( self::class, 'save' ), 20 );
		add_action( 'admin_head', array( self::class, 'renderAdminStyles' ) );
	}

	/**
	 * ACF показує кнопку «+» (створити тег) лише при наведенні на поле, тому редактор її не
	 * помічає. Для поля власних тегів робимо кнопку постійно видимою з підписом.
	 */
	public static function renderAdminStyles(): void {
		if ( 'post' !== get_current_screen()?->post_type ) {
			return;
		}
		?>
<style>
.acf-field-post-custom-tags .acf-taxonomy-field .acf-actions { display: block; opacity: 1; }
</style>
		<?php
	}

	/**
	 * @param mixed $value
	 * @param mixed $post_id
	 * @param mixed $field
	 * @return mixed
	 */
	public static function loadCityValue( $value, $post_id, $field ) {
		$tags = self::postTags( $post_id );

		return null === $tags ? $value : array_values( array_intersect( $tags, CityPostTags::tagIds() ) );
	}

	/**
	 * @param mixed $value
	 * @param mixed $post_id
	 * @param mixed $field
	 * @return mixed
	 */
	public static function loadCustomValue( $value, $post_id, $field ) {
		$tags = self::postTags( $post_id );

		return null === $tags ? $value : array_values( array_diff( $tags, CityPostTags::tagIds() ) );
	}

	/**
	 * @param array<string, mixed> $args
	 * @param mixed                $field
	 * @param mixed                $post_id
	 * @return array<string, mixed>
	 */
	public static function cityQuery( array $args, $field, $post_id ): array {
		$args['include'] = CityPostTags::tagIds() ?: array( 0 );

		return $args;
	}

	/**
	 * @param array<string, mixed> $args
	 * @param mixed                $field
	 * @param mixed                $post_id
	 * @return array<string, mixed>
	 */
	public static function customQuery( array $args, $field, $post_id ): array {
		$args['exclude'] = CityPostTags::tagIds();

		return $args;
	}

	/**
	 * @param mixed $post_id
	 */
	public static function save( $post_id ): void {
		if ( null === self::postTags( $post_id ) ) {
			return;
		}
		$post_id = (int) $post_id;
		if ( ! metadata_exists( 'post', $post_id, 'post_city_tags' ) && ! metadata_exists( 'post', $post_id, 'post_custom_tags' ) ) {
			return;
		}
		$tags = array_merge( self::submitted( 'post_city_tags', $post_id ), self::submitted( 'post_custom_tags', $post_id ) );
		wp_set_object_terms( $post_id, array_values( array_unique( $tags ) ), 'post_tag', false );
		// Значення живуть у тегах запису; посилання `_ім’я → ключ поля` лишаємо, бо без нього
		// get_field() за іменем поля не знаходить поле.
		delete_post_meta( $post_id, 'post_city_tags' );
		delete_post_meta( $post_id, 'post_custom_tags' );
	}

	/**
	 * Значення читається з метаполя, куди ACF щойно записав надіслану форму: get_field() тут
	 * поверне вже перевизначене load_value, тобто наявні теги запису, а не нові дані.
	 *
	 * @return int[]
	 */
	private static function submitted( string $name, int $post_id ): array {
		return array_values( array_filter( array_map( 'absint', (array) get_post_meta( $post_id, $name, true ) ) ) );
	}

	/**
	 * @param mixed $post_id
	 * @return int[]|null Теги запису або null, якщо це не запис блогу.
	 */
	private static function postTags( $post_id ): ?array {
		if ( ! is_numeric( $post_id ) || 'post' !== get_post_type( (int) $post_id ) ) {
			return null;
		}

		return array_map( 'absint', wp_get_post_terms( (int) $post_id, 'post_tag', array( 'fields' => 'ids' ) ) );
	}
}
