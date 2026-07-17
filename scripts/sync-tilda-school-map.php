<?php

$cities_by_region = array(
	'місто Київ'             => array( 'Київ' ),
	'Київська область'       => array( 'Бориспіль', 'Бровари', 'Ірпінь / Буча', 'Біла Церква', 'Вишневе', 'Фастів', 'Крюківщина', 'Васильків', 'Вишгород', 'Обухів / Гатне', 'Переяслав', 'Боярка', 'Українка', 'Миронівка' ),
	'Черкаська область'      => array( 'Черкаси', 'Сміла', 'Умань', 'Канів', 'Корсунь' ),
	'Кіровоградська область' => array( 'Кропивницький', 'Світловодськ', 'Олександрія', "Знам'янка" ),
	'Одеська область'        => array( 'Одеса', 'Чорноморськ', 'Ізмаїл', 'Південне', 'Авангард', 'Білгород-Дністровський', 'Роздільна' ),
	'Миколаївська область'   => array( 'Миколаїв', 'Південноукраїнськ', 'Первомайськ' ),
	'Дніпропетровська область' => array( 'Дніпро', 'Павлоград', 'Кривий Ріг', 'Новомосковськ', "Кам'янське", 'Жовті води', 'Синельникове', 'Покров', 'Тернівка', 'Солоне', "П'ятихатки", 'Підгородне', 'Перещепине' ),
	'Запорізька область'     => array( 'Запоріжжя' ),
	'Харківська область'     => array( 'Харків', 'Берестин' ),
	'Полтавська область'     => array( 'Полтава', 'Миргород', 'Кременчук', 'Горішні Плавні', 'Лубни' ),
	'Чернігівська область'   => array( 'Чернігів', 'Ніжин', 'Прилуки' ),
	'Вінницька область'      => array( 'Вінниця', 'Жмеринка', 'Могилів-Подільський', 'Козятин' ),
	'Житомирська область'    => array( 'Житомир', 'Бердичів', 'Коростень', 'Малин', 'Звягель' ),
	'Львівська область'      => array( 'Львів', 'Стрий', 'Шептицький', 'Дрогобич', 'Броди', 'Винники', 'Новояворівськ', 'Жовква', 'Борислав', 'Пустомити', 'Самбір', 'Новий Розділ', 'Городок', 'Трускавець', 'Сокаль', 'Зимна Вода', 'Золочів', 'Яворів', 'Рудне', 'Дубляни', 'Брюховичі', 'Мостиська' ),
	'Тернопільська область'  => array( 'Тернопіль', 'Кременець' ),
	'Волинська область'      => array( 'Луцьк', 'Володимир' ),
	'Івано-Франківська область' => array( 'Івано-Франківськ', 'Коломия', 'Калуш', 'Снятин', 'Долина' ),
	'Рівненська область'     => array( 'Рівне', 'Дубно', 'Костопіль', 'Вараш', 'Здолбунів', 'Острог', 'Сарни', 'Дубровиця' ),
	'Хмельницька область'    => array( "Кам'янець-Подільський", 'Шепетівка', 'Полонне', 'Хмельницький', 'Славута', 'Старокостянтинів', 'Нетішин', 'Волочиськ', 'Дунаївці' ),
	'Закарпатська область'   => array( 'Мукачево', 'Хуст', 'Ужгород', 'Тячів', 'Виноградів' ),
	'Чернівецька область'    => array( 'Чернівці', 'Сторожинець' ),
);
$aliases = array(
	'Корсунь' => 'Корсунь-Шевченковский',
	'Звягель' => 'Новоград-Волинський',
);
$cities = get_posts(
	array(
		'post_type'      => 'city',
		'post_status'    => 'any',
		'posts_per_page' => -1,
	)
);
$by_label = array();

foreach ( $cities as $city ) {
	foreach ( array( $city->post_title, (string) get_field( 'city_selected_label', $city->ID ) ) as $label ) {
		$by_label[ mb_strtolower( $label, 'UTF-8' ) ][ $city->ID ] = $city;
	}
}

$targets = array();
foreach ( $cities_by_region as $region => $labels ) {
	foreach ( $labels as $label ) {
		$matches = array_values( $by_label[ mb_strtolower( $label, 'UTF-8' ) ] ?? $by_label[ mb_strtolower( $aliases[ $label ] ?? '', 'UTF-8' ) ] ?? array() );
		if ( 1 !== count( $matches ) ) {
			fwrite( STDERR, "Cannot resolve Tilda map city: {$label}\n" );
			exit( 1 );
		}
		$targets[] = array( 'city' => $matches[0], 'label' => $label, 'region' => $region );
	}
}

if ( 122 !== count( $targets ) || 122 !== count( array_unique( array_map( static fn( array $target ): int => $target['city']->ID, $targets ) ) ) ) {
	fwrite( STDERR, "Tilda map must resolve exactly 122 unique cities.\n" );
	exit( 1 );
}

foreach ( $cities as $city ) {
	if ( 'tilda' === get_post_meta( $city->ID, 'city_map_source', true ) ) {
		update_post_meta( $city->ID, 'city_show_on_map', '0' );
	}
}

foreach ( $targets as $target ) {
	$term = term_exists( $target['region'], 'region' );
	if ( ! $term ) {
		$term = wp_insert_term( $target['region'], 'region' );
	}
	if ( is_wp_error( $term ) ) {
		fwrite( STDERR, $term->get_error_message() . "\n" );
		exit( 1 );
	}
	wp_set_object_terms( $target['city']->ID, array( (int) ( is_array( $term ) ? $term['term_id'] : $term ) ), 'region' );
	update_field( 'city_selected_label', $target['label'], $target['city']->ID );
	update_post_meta( $target['city']->ID, 'city_map_source', 'tilda' );
	update_post_meta( $target['city']->ID, 'city_show_on_map', '1' );
}

echo "Synced 122 Tilda map cities.\n";
