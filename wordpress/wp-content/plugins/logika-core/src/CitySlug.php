<?php

declare(strict_types=1);

namespace Logika\Core;

use WP_Post;

final class CitySlug {
	private const TRANSLITERATION = array(
		'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'h', 'ґ' => 'g', 'д' => 'd', 'е' => 'e', 'є' => 'ye', 'ж' => 'zh', 'з' => 'z', 'и' => 'y', 'і' => 'i', 'ї' => 'i', 'й' => 'i', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'kh', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch', 'ь' => '', 'ю' => 'iu', 'я' => 'ia', 'ё' => 'yo', 'ы' => 'y', 'э' => 'e', 'ъ' => '',
	);

	public static function for( int|WP_Post $city ): string {
		$city = get_post( $city );
		if ( ! $city instanceof WP_Post ) {
			return '';
		}

		$slug = (string) get_post_meta( $city->ID, 'city_url_slug', true );

		return sanitize_title( $slug ?: strtr( mb_strtolower( $city->post_title, 'UTF-8' ), self::TRANSLITERATION ) );
	}

	public static function url( int|WP_Post $city ): string {
		return home_url( '/cities/' . self::for( $city ) . '/' );
	}

	public static function find( string $slug ): ?WP_Post {
		$slug = sanitize_title_for_query( $slug );
		foreach ( get_posts( array( 'post_type' => 'city', 'post_status' => 'publish', 'posts_per_page' => -1 ) ) as $city ) {
			if ( $slug === self::for( $city ) ) {
				return $city;
			}
		}

		return null;
	}
}
