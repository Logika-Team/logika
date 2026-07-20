<?php

declare(strict_types=1);

namespace Logika\Core;

use WP_Post;
use WP_Query;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

final class MediaApi {
	private const LIMIT        = 7;
	private const SEARCH_LIMIT = 100;

	public static function register(): void {
		register_rest_route(
			'logika/v1',
			'/media',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( self::class, 'index' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'city' => array(
						'validate_callback' => static fn( $value ): bool => is_numeric( $value ),
					),
					'search' => array(
						'sanitize_callback' => static fn( $value ): string => sanitize_text_field( (string) $value ),
						'validate_callback' => static fn( $value ): bool => strlen( (string) $value ) <= 300,
					),
					'all' => array(
						'sanitize_callback' => 'rest_sanitize_boolean',
					),
					'category' => array(
						'sanitize_callback' => 'sanitize_key',
						'validate_callback' => static fn( $value ): bool => '' === $value || isset( MediaCategories::labels()[ $value ] ),
					),
					'featured' => array(
						'sanitize_callback' => 'absint',
					),
				),
			)
		);
	}

	public static function index( WP_REST_Request $request ): WP_REST_Response {
		$city_id = absint( $request->get_param( 'city' ) );
		$search  = trim( (string) $request->get_param( 'search' ) );
		$category = (string) $request->get_param( 'category' );
		$featured = absint( $request->get_param( 'featured' ) );
		$limit   = rest_sanitize_boolean( $request->get_param( 'all' ) ) ? -1 : ( $search ? self::SEARCH_LIMIT : self::LIMIT );

		if ( ! $city_id ) {
			return new WP_REST_Response( self::prioritize( self::cards( new WP_Query( self::latest_query( $search, $category, CityPostTags::commonTaxQuery(), $limit ) ) ), $featured, $search, $limit ) );
		}

		$city = get_post( $city_id );

		if ( ! $city instanceof WP_Post || 'city' !== $city->post_type || 'publish' !== $city->post_status ) {
			return new WP_REST_Response( array( 'message' => 'Місто не знайдено.' ), 404 );
		}

		$local = self::cards(
			new WP_Query(
				array_merge(
					array(
					'post_type'      => 'post',
					'post_status'    => 'publish',
					'posts_per_page' => $limit,
					'orderby'        => 'date',
					'order'          => 'DESC',
					'meta_query'     => self::visibility_query(),
					'tax_query'      => CityPostTags::cityTaxQuery( $city_id ),
					's'              => $search,
					),
					$category ? array( 'category_name' => $category ) : array()
				)
			)
		);
		$remaining = $limit - count( $local );
		$common = -1 === $limit || $remaining > 0 ? self::cards(
			new WP_Query(
				array_merge(
					array(
					'post_type'      => 'post',
					'post_status'    => 'publish',
					'posts_per_page' => -1 === $limit ? -1 : $remaining,
					'orderby'        => 'date',
					'order'          => 'DESC',
					'meta_query'     => self::visibility_query(),
					'tax_query'      => CityPostTags::commonTaxQuery(),
					's'              => $search,
					),
					$category ? array( 'category_name' => $category ) : array()
				)
			)
		) : array();

		return new WP_REST_Response( self::prioritize( array_merge( $local, $common ), 0, $search, $limit ) );
	}

	/**
	 * @return array<string, mixed>
	 */
	private static function latest_query( string $search = '', string $category = '', array $tax_query = array(), int $limit = self::LIMIT ): array {
		$query = array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'meta_query'     => self::visibility_query(),
		);

		if ( $search ) {
			$query['s'] = $search;
		}
		if ( $category ) { $query['category_name'] = $category; }
		if ( $tax_query ) { $query['tax_query'] = $tax_query; }

		return $query;
	}

	/**
	 * @return array<string, mixed>
	 */
	private static function visibility_query(): array {
		return array(
			'relation' => 'OR',
			array( 'key' => 'post_hide_from_blog', 'compare' => 'NOT EXISTS' ),
			array( 'key' => 'post_hide_from_blog', 'value' => '0', 'compare' => '=' ),
		);
	}

	/**
	 * @return list<array{id: int, title: string, url: string, date: string, excerpt: string, image: string}>
	 */
	private static function cards( WP_Query $query ): array {
		return array_map(
			static fn( WP_Post $post ): array => self::card( $post ),
			$query->posts
		);
	}

	/** @param list<array{id: int, title: string, url: string, date: string, excerpt: string, image: string}> $cards */
	private static function prioritize( array $cards, int $featured_id, string $search, int $limit ): array {
		$featured = get_post( $featured_id );
		if ( $search || ! $featured instanceof WP_Post || 'post' !== $featured->post_type || 'publish' !== $featured->post_status || '1' === get_post_meta( $featured_id, 'post_hide_from_blog', true ) ) {
			return $cards;
		}
		$cards = array_values( array_filter( $cards, static fn( array $card ): bool => $featured_id !== (int) $card['id'] ) );
		array_unshift( $cards, self::card( $featured ) );

		return -1 === $limit ? $cards : array_slice( $cards, 0, $limit );
	}

	/** @return array{id: int, title: string, url: string, date: string, excerpt: string, image: string} */
	private static function card( WP_Post $post ): array {
		return array( 'id' => $post->ID, 'title' => get_the_title( $post ), 'url' => get_permalink( $post ), 'date' => get_the_date( 'd.m.Y', $post ), 'excerpt' => wp_trim_words( get_the_excerpt( $post ), 30 ), 'image' => (string) get_the_post_thumbnail_url( $post, 'large' ) );
	}
}
