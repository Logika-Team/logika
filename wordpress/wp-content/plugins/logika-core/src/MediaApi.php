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
				),
			)
		);
	}

	public static function index( WP_REST_Request $request ): WP_REST_Response {
		$city_id = absint( $request->get_param( 'city' ) );
		$search  = trim( (string) $request->get_param( 'search' ) );
		$limit   = $search ? self::SEARCH_LIMIT : self::LIMIT;

		if ( ! $city_id ) {
			return new WP_REST_Response( self::cards( new WP_Query( self::latest_query( $search ) ) ) );
		}

		$city = get_post( $city_id );

		if ( ! $city instanceof WP_Post || 'city' !== $city->post_type || 'publish' !== $city->post_status ) {
			return new WP_REST_Response( array( 'message' => 'Місто не знайдено.' ), 404 );
		}

		$local = self::cards(
			new WP_Query(
				array(
					'post_type'      => 'post',
					'post_status'    => 'publish',
					'posts_per_page' => $limit,
					'orderby'        => 'date',
					'order'          => 'DESC',
					'meta_key'       => 'post_related_city',
					'meta_value'     => (string) $city_id,
					's'              => $search,
				)
			)
		);
		$remaining = $limit - count( $local );
		$common = $remaining > 0 ? self::cards(
			new WP_Query(
				array(
					'post_type'      => 'post',
					'post_status'    => 'publish',
					'posts_per_page' => $remaining,
					'orderby'        => 'date',
					'order'          => 'DESC',
						'meta_query'     => array(
						'relation' => 'OR',
						array( 'key' => 'post_related_city', 'compare' => 'NOT EXISTS' ),
						array( 'key' => 'post_related_city', 'value' => '', 'compare' => '=' ),
						array( 'key' => 'post_related_city', 'value' => '0' ),
						),
						's'              => $search,
				)
			)
		) : array();

		return new WP_REST_Response( array_merge( $local, $common ) );
	}

	/**
	 * @return array<string, mixed>
	 */
	private static function latest_query( string $search = '' ): array {
		$query = array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => $search ? self::SEARCH_LIMIT : self::LIMIT,
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		if ( $search ) {
			$query['s'] = $search;
		}

		return $query;
	}

	/**
	 * @return list<array{id: int, title: string, url: string, date: string, excerpt: string, image: string}>
	 */
	private static function cards( WP_Query $query ): array {
		return array_map(
			static fn( WP_Post $post ): array => array(
				'id'      => $post->ID,
				'title'   => get_the_title( $post ),
				'url'     => get_permalink( $post ),
				'date'    => get_the_date( 'd.m.Y', $post ),
				'excerpt' => wp_trim_words( get_the_excerpt( $post ), 30 ),
				'image'   => (string) get_the_post_thumbnail_url( $post, 'large' ),
			),
			$query->posts
		);
	}
}
