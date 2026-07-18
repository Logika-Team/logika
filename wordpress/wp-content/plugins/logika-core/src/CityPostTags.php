<?php

declare(strict_types=1);

namespace Logika\Core;

use WP_CLI;
use WP_Post;

final class CityPostTags {
	private const CITY_META = '_logika_city_id';
	private const COOKIE    = 'logika_city';

	public static function register(): void {
		add_action( 'save_post_city', array( self::class, 'syncCity' ), 20 );
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			WP_CLI::add_command( 'logika city-tags sync', static function (): void {
				$report = self::sync();
				WP_CLI::success( sprintf( 'Міські позначки: %d міст, %d публікацій.', $report['cities'], $report['posts'] ) );
			} );
		}
	}

	/** @return array{cities: int, posts: int} */
	public static function sync(): array {
		$report = array( 'cities' => 0, 'posts' => 0 );
		foreach ( get_posts( array( 'post_type' => 'city', 'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future' ), 'posts_per_page' => -1 ) ) as $city ) {
			if ( self::syncCity( $city->ID ) ) {
				++$report['cities'];
			}
		}
		foreach ( get_posts( array( 'post_type' => 'post', 'post_status' => 'any', 'posts_per_page' => -1, 'meta_key' => 'post_related_city' ) ) as $post ) {
			$tag_id = self::tagId( (int) get_post_meta( $post->ID, 'post_related_city', true ) );
			if ( $tag_id && ! has_tag( $tag_id, $post ) ) {
				wp_add_post_tags( $post->ID, array( $tag_id ) );
				++$report['posts'];
			}
		}

		return $report;
	}

	public static function syncCity( int $city_id ): int {
		$city = get_post( $city_id );
		if ( ! $city instanceof WP_Post || 'city' !== $city->post_type || 'trash' === $city->post_status ) {
			return 0;
		}
		$slug = CitySlug::for( $city );
		if ( '' === $slug ) {
			return 0;
		}
		$terms = get_terms( array( 'taxonomy' => 'post_tag', 'hide_empty' => false, 'meta_key' => self::CITY_META, 'meta_value' => $city_id, 'number' => 1 ) );
		$term  = $terms[0] ?? get_term_by( 'slug', $slug, 'post_tag' );
		if ( ! $term ) {
			$term = wp_insert_term( $city->post_title, 'post_tag', array( 'slug' => $slug ) );
		}
		if ( is_wp_error( $term ) ) {
			return 0;
		}
		$term_id = (int) ( is_array( $term ) ? $term['term_id'] : $term->term_id );
		wp_update_term( $term_id, 'post_tag', array( 'name' => $city->post_title, 'slug' => $slug ) );
		update_term_meta( $term_id, self::CITY_META, $city_id );

		return $term_id;
	}

	public static function tagId( int $city_id ): int {
		$city = get_post( $city_id );
		if ( ! $city instanceof WP_Post || 'city' !== $city->post_type || 'trash' === $city->post_status ) {
			return 0;
		}
		$terms = get_terms( array( 'taxonomy' => 'post_tag', 'hide_empty' => false, 'meta_key' => self::CITY_META, 'meta_value' => $city->ID, 'number' => 1 ) );
		if ( $terms ) {
			return (int) $terms[0]->term_id;
		}
		$term = get_term_by( 'slug', CitySlug::for( $city ), 'post_tag' );

		return $term ? (int) $term->term_id : self::syncCity( $city->ID );
	}

	public static function currentCityId(): int {
		$slug = (string) get_query_var( 'logika_city' );
		$city = $slug ? CitySlug::find( $slug ) : null;
		if ( $city instanceof WP_Post && 'publish' === $city->post_status ) {
			return $city->ID;
		}
		$city = get_post( absint( wp_unslash( $_COOKIE[ self::COOKIE ] ?? '' ) ) );

		return $city instanceof WP_Post && 'city' === $city->post_type && 'publish' === $city->post_status ? $city->ID : 0;
	}

	/** @return int[] */
	public static function tagIds(): array {
		return array_map( 'absint', get_terms( array( 'taxonomy' => 'post_tag', 'hide_empty' => false, 'fields' => 'ids', 'meta_key' => self::CITY_META ) ) );
	}

	/** @return array<int|string, mixed> */
	public static function commonTaxQuery(): array {
		$tags = self::tagIds();

		return $tags ? array( array( 'taxonomy' => 'post_tag', 'field' => 'term_id', 'terms' => $tags, 'operator' => 'NOT IN' ) ) : array();
	}

	/** @return array<int|string, mixed> */
	public static function cityTaxQuery( int $city_id ): array {
		$tag_id = self::tagId( $city_id );

		return $tag_id ? array( array( 'taxonomy' => 'post_tag', 'field' => 'term_id', 'terms' => array( $tag_id ) ) ) : array();
	}

	/** @return array<int|string, mixed> */
	public static function visibilityTaxQuery( int $city_id = 0 ): array {
		if ( ! $city_id ) {
			return self::commonTaxQuery();
		}
		$common = self::commonTaxQuery();
		$local  = self::cityTaxQuery( $city_id );

		return $common && $local ? array( 'relation' => 'OR', $common[0], $local[0] ) : array();
	}

	public static function visible( int $post_id, int $city_id = 0 ): bool {
		$city_tags = self::tagIds();
		$post_tags = wp_get_post_terms( $post_id, 'post_tag', array( 'fields' => 'ids' ) );
		$local     = array_intersect( $city_tags, array_map( 'absint', $post_tags ) );
		$selected  = $city_id ? self::tagId( $city_id ) : 0;

		return ! $local || ( $selected && in_array( $selected, $local, true ) );
	}
}
