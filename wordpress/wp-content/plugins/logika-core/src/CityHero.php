<?php

declare(strict_types=1);

namespace Logika\Core;

use WP_Post;

final class CityHero {
	public static function resolve( int|WP_Post $city ): array {
		$city = is_int( $city ) ? get_post( $city ) : $city;
		$name = $city instanceof WP_Post ? sanitize_text_field( $city->post_title ) : '';
		$id   = $city instanceof WP_Post ? $city->ID : 0;

		return array(
			'title' => trim( sanitize_text_field( (string) get_field( 'city_home_hero_title', $id ) ) ) ?: "Програмування та англійська мова для дітей в м. {$name}",
			'text'  => trim( sanitize_textarea_field( (string) get_field( 'city_home_hero_text', $id ) ) ) ?: "Найбільша офлайн школа в місті {$name}. Доступний онлайн формат",
		);
	}
}
