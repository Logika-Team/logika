<?php

declare(strict_types=1);

require dirname( __DIR__ ) . '/wordpress/wp-load.php';

$suffix = (string) wp_generate_uuid4();
$city   = 0;
$post   = 0;

try {
	foreach ( array( 'field_post_city_tags' => 0, 'field_post_custom_tags' => 1 ) as $key => $add_term ) {
		$field = acf_get_field( $key );
		if ( ! $field || 'taxonomy' !== $field['type'] || 'post_tag' !== $field['taxonomy'] || 'multi_select' !== $field['field_type'] || $field['save_terms'] || (int) $field['add_term'] !== $add_term ) {
			throw new RuntimeException( "Поле {$key} має бути мультивибором тегів без власного збереження термінів." );
		}
	}

	wp_set_current_user( 1 );
	$request = new WP_REST_Request( 'GET', '/wp/v2/taxonomies/post_tag' );
	$request->set_param( 'context', 'edit' );
	$visibility = (array) ( rest_do_request( $request )->get_data()['visibility'] ?? array() );
	if ( false !== ( $visibility['show_ui'] ?? null ) ) {
		throw new RuntimeException( 'Стандартна панель тегів редактора має бути прихована, щоб не перетирати поля тегів.' );
	}

	$city = wp_insert_post( array( 'post_type' => 'city', 'post_status' => 'publish', 'post_title' => 'Тегове місто ' . $suffix, 'post_name' => "post-tags-city-{$suffix}" ) );
	$post = wp_insert_post( array( 'post_type' => 'post', 'post_status' => 'publish', 'post_title' => 'Стаття міста ' . $suffix, 'post_name' => "post-tags-post-{$suffix}" ) );

	$city_tag = \Logika\Core\CityPostTags::tagId( $city );
	$own_tag  = (int) ( (array) wp_insert_term( 'Власний тег ' . $suffix, 'post_tag' ) )['term_id'];

	update_field( 'field_post_city_tags', array( $city_tag ), $post );
	update_field( 'field_post_custom_tags', array( $own_tag ), $post );
	do_action( 'acf/save_post', $post );

	$tags = array_map( 'intval', wp_get_post_terms( $post, 'post_tag', array( 'fields' => 'ids' ) ) );
	sort( $tags );
	$expected = array( $city_tag, $own_tag );
	sort( $expected );
	if ( $expected !== $tags ) {
		throw new RuntimeException( 'Обидва поля мають зберігатися в теги запису без втрат.' );
	}
	if ( get_post_meta( $post, 'post_city_tags', true ) || get_post_meta( $post, 'post_custom_tags', true ) ) {
		throw new RuntimeException( 'Значення полів мають жити в тегах запису, а не дублюватися в метаполях.' );
	}

	acf_flush_value_cache( $post );
	if ( array( $city_tag ) !== array_map( 'intval', (array) get_field( 'post_city_tags', $post ) ) ) {
		throw new RuntimeException( 'Поле «Теги міст» має показувати лише міські теги запису.' );
	}
	if ( array( $own_tag ) !== array_map( 'intval', (array) get_field( 'post_custom_tags', $post ) ) ) {
		throw new RuntimeException( 'Поле «Власні теги» має показувати лише неміські теги запису.' );
	}

	$city_choices   = apply_filters( 'acf/fields/taxonomy/query/key=field_post_city_tags', array(), acf_get_field( 'field_post_city_tags' ), $post );
	$custom_choices = apply_filters( 'acf/fields/taxonomy/query/key=field_post_custom_tags', array(), acf_get_field( 'field_post_custom_tags' ), $post );
	if ( ! in_array( $city_tag, array_map( 'intval', (array) $city_choices['include'] ), true ) || ! in_array( $city_tag, array_map( 'intval', (array) $custom_choices['exclude'] ), true ) ) {
		throw new RuntimeException( 'Списки полів мають бути розділені: міські теги лише в полі міст.' );
	}

	if ( ! \Logika\Core\CityPostTags::visible( $post, $city ) || \Logika\Core\CityPostTags::visible( $post, 0 ) ) {
		throw new RuntimeException( 'Запис із міським тегом має бути видимим лише у вибраному місті.' );
	}
} finally {
	if ( isset( $own_tag ) ) {
		wp_delete_term( $own_tag, 'post_tag' );
	}
	if ( $post ) {
		wp_delete_post( $post, true );
	}
	if ( $city ) {
		$city_term = \Logika\Core\CityPostTags::tagId( $city );
		if ( $city_term ) {
			wp_delete_term( $city_term, 'post_tag' );
		}
		wp_delete_post( $city, true );
	}
}

echo "Post tag fields split city tags, custom tags and term creation.\n";
