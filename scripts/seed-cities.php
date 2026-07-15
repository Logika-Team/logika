<?php

$cities = array(
	array( 'Київ', 'kyiv', 'Київська область', 50.4501, 30.5234 ),
	array( 'Львів', 'lviv', 'Львівська область', 49.8397, 24.0297 ),
	array( 'Одеса', 'odesa', 'Одеська область', 46.4825, 30.7233 ),
	array( 'Харків', 'kharkiv', 'Харківська область', 49.9935, 36.2304, array( 'Харьков' ) ),
	array( 'Дніпро', 'dnipro', 'Дніпропетровська область', 48.4647, 35.0462 ),
	array( 'Запоріжжя', 'zaporizhzhia', 'Запорізька область', 47.8388, 35.1396 ),
	array( 'Вінниця', 'vinnytsia', 'Вінницька область', 49.2331, 28.4682 ),
	array( 'Івано-Франківськ', 'ivano-frankivsk', 'Івано-Франківська область', 48.9226, 24.7111 ),
	array( 'Тернопіль', 'ternopil', 'Тернопільська область', 49.5535, 25.5948 ),
	array( 'Чернівці', 'chernivtsi', 'Чернівецька область', 48.2915, 25.9403 ),
	array( 'Ужгород', 'uzhhorod', 'Закарпатська область', 48.6208, 22.2879 ),
	array( 'Луцьк', 'lutsk', 'Волинська область', 50.7472, 25.3254 ),
	array( 'Рівне', 'rivne', 'Рівненська область', 50.6199, 26.2516 ),
	array( 'Житомир', 'zhytomyr', 'Житомирська область', 50.2547, 28.6587 ),
	array( 'Черкаси', 'cherkasy', 'Черкаська область', 49.4444, 32.0598 ),
	array( 'Полтава', 'poltava', 'Полтавська область', 49.5883, 34.5514 ),
	array( 'Суми', 'sumy', 'Сумська область', 50.9077, 34.7981 ),
	array( 'Чернігів', 'chernihiv', 'Чернігівська область', 51.4982, 31.2893 ),
	array( 'Кропивницький', 'kropyvnytskyi', 'Кіровоградська область', 48.5079, 32.2623 ),
	array( 'Миколаїв', 'mykolaiv', 'Миколаївська область', 46.9750, 31.9946 ),
	array( 'Херсон', 'kherson', 'Херсонська область', 46.6354, 32.6169 ),
	array( 'Хмельницький', 'khmelnytskyi', 'Хмельницька область', 49.4229, 26.9871 ),
	array( 'Біла Церква', 'bila-tserkva', 'Київська область', 49.7956, 30.1167 ),
	array( 'Кременчук', 'kremenchuk', 'Полтавська область', 49.0670, 33.4204 ),
	array( 'Кривий Ріг', 'kryvyi-rih', 'Дніпропетровська область', 47.9105, 33.3918 ),
	array( 'Маріуполь', 'mariupol', 'Донецька область', 47.0971, 37.5434 ),
	array( 'Чорноморськ', 'chornomorsk', 'Одеська область', 46.3017, 30.6569 ),
	array( 'Онлайн', 'online', 'Онлайн', 50.4501, 30.5234 ),
);

foreach ( $cities as $city ) {
	list( $title, $slug, $region, $lat, $lng ) = $city;
	$aliases = $city[5] ?? array();
	$term = term_exists( $region, 'region' );
	$term_id = is_array( $term ) ? (int) $term['term_id'] : (int) $term;

	if ( ! $term_id ) {
		$term = wp_insert_term( $region, 'region', array( 'slug' => sanitize_title( $region ) ) );
		if ( is_wp_error( $term ) ) {
			fwrite( STDERR, $term->get_error_message() . "\n" );
			exit( 1 );
		}
		$term_id = (int) $term['term_id'];
	}

	$post = get_page_by_path( $slug, OBJECT, 'city' );
	if ( ! $post ) {
		foreach ( array_merge( array( $title ), $aliases ) as $alias ) {
			$matches = get_posts( array( 'post_type' => 'city', 'post_status' => 'any', 'title' => $alias, 'posts_per_page' => 1, 'fields' => 'ids' ) );
			if ( $matches ) {
				$post = get_post( (int) $matches[0] );
				break;
			}
		}
	}

	$post_id = $post instanceof WP_Post ? (int) $post->ID : 0;
	$data = array(
		'post_type'   => 'city',
		'post_title'  => $title,
		'post_name'   => $slug,
		'post_status' => 'publish',
	);

	if ( $post_id ) {
		$data['ID'] = $post_id;
		wp_update_post( $data );
	} else {
		$post_id = (int) wp_insert_post( $data );
	}

	if ( ! $post_id ) {
		fwrite( STDERR, "Cannot seed city: {$title}\n" );
		exit( 1 );
	}

	wp_set_object_terms( $post_id, array( $term_id ), 'region' );
	update_field( 'city_external_id', 'ua-' . $slug, $post_id );
	update_field( 'city_region', $term_id, $post_id );
	update_field( 'city_lat', $lat, $post_id );
	update_field( 'city_lng', $lng, $post_id );
	update_field( 'city_index_status', 'index', $post_id );
	update_field( 'city_selected_label', $title, $post_id );
	update_field( 'city_intro', "Logika у місті {$title}: курси програмування та англійської для дітей і підлітків.", $post_id );
}

$test_city = get_page_by_path( 'test-city', OBJECT, 'city' );
if ( $test_city instanceof WP_Post ) {
	update_field( 'city_index_status', 'noindex', (int) $test_city->ID );
	update_field( 'city_selected_label', 'Тестове місто', (int) $test_city->ID );
}

echo 'Seeded public cities: ' . count( $cities ) . PHP_EOL;
