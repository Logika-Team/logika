<?php

declare(strict_types=1);

namespace Logika\Core;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

final class CityApi {
	private const DEFAULT_HOMEPAGE_SEO_VIDEO_URL = 'https://www.youtube.com/watch?v=7QN3QcMHMQ4';

	public static function register(): void {
		register_rest_route(
			'logika/v1',
			'/cities',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( self::class, 'index' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			'logika/v1',
			'/cities/(?P<id>\d+)/branches',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( self::class, 'branches' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'id' => array(
						'validate_callback' => static fn( $value ): bool => is_numeric( $value ),
					),
				),
			)
		);

		register_rest_route(
			'logika/v1',
			'/cities/(?P<id>\d+)/homepage-seo',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( self::class, 'homepageSeo' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'id' => array(
						'validate_callback' => static fn( $value ): bool => is_numeric( $value ),
					),
				),
			)
		);
	}

	public static function index( WP_REST_Request $request ): WP_REST_Response {
		$cities = get_posts(
			array(
				'post_type'      => 'city',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'meta_query'     => array(
					'relation' => 'OR',
					array(
						'key'     => 'city_index_status',
						'compare' => 'NOT EXISTS',
					),
					array(
						'key'     => 'city_index_status',
						'value'   => 'noindex',
						'compare' => '!=',
					),
				),
			)
		);

		return new WP_REST_Response(
				array_map(
				static fn( \WP_Post $city ): array => array(
					'id'     => $city->ID,
					'label'  => get_field( 'city_selected_label', $city->ID ) ?: $city->post_title,
					'hero'   => CityHero::resolve( $city ),
					'show_on_map' => '1' === get_post_meta( $city->ID, 'city_show_on_map', true ),
					'slug'   => CitySlug::for( $city ),
						'url'    => CitySlug::url( $city ),
						'lat'    => (float) get_field( 'city_lat', $city->ID ),
						'lng'    => (float) get_field( 'city_lng', $city->ID ),
						'region' => self::region( $city->ID ),
					),
					$cities
				)
			);
	}

	public static function branches( WP_REST_Request $request ): WP_REST_Response {
		$city_id = absint( $request['id'] );
		$city    = get_post( $city_id );

		if ( ! $city instanceof \WP_Post || 'city' !== $city->post_type || 'publish' !== $city->post_status ) {
			return new WP_REST_Response( array( 'message' => 'Місто не знайдено.' ), 404 );
		}

		$branches = get_posts(
			array(
				'post_type'      => 'branch',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'meta_query'     => array(
					'relation' => 'AND',
					array( 'key' => 'branch_city_id', 'value' => (string) $city_id ),
					array( 'key' => 'branch_is_active', 'value' => '1' ),
				),
			)
		);

		return new WP_REST_Response(
			array_map(
				static fn( \WP_Post $branch ): array => array(
					'id'      => $branch->ID,
					'label'   => $branch->post_title,
					'address' => (string) get_field( 'branch_address', $branch->ID ),
					'lat'     => (float) get_field( 'branch_lat', $branch->ID ),
					'lng'     => (float) get_field( 'branch_lng', $branch->ID ),
					'map_url' => (string) get_field( 'branch_google_maps_url', $branch->ID ),
				),
				$branches
			)
		);
	}

	public static function homepageSeo( WP_REST_Request $request ): WP_REST_Response {
		$city = get_post( absint( $request['id'] ) );

		if ( ! $city instanceof \WP_Post || 'city' !== $city->post_type || 'publish' !== $city->post_status ) {
			return new WP_REST_Response( array( 'message' => 'Місто не знайдено.' ), 404 );
		}

		$title       = trim( sanitize_text_field( (string) get_post_meta( $city->ID, 'city_home_seo_title', true ) ) );
		$description = trim( sanitize_textarea_field( (string) get_post_meta( $city->ID, 'city_home_seo_description', true ) ) );
		$cta_label   = trim( sanitize_text_field( (string) get_post_meta( $city->ID, 'city_home_seo_cta_label', true ) ) );
		$caption     = trim( sanitize_textarea_field( (string) get_post_meta( $city->ID, 'city_home_seo_video_caption', true ) ) );
		$video_url   = esc_url_raw( (string) get_post_meta( $city->ID, 'city_home_seo_video_url', true ) ) ?: self::DEFAULT_HOMEPAGE_SEO_VIDEO_URL;
		$illustration = self::image( absint( get_post_meta( $city->ID, 'city_home_seo_illustration', true ) ) );
		$poster       = self::image( absint( get_post_meta( $city->ID, 'city_home_seo_video_poster', true ) ) );

		if ( ! $title || ! $description || ! $cta_label || ! $caption || ! $video_url || ! $illustration || ! $poster ) {
			return new WP_REST_Response( null );
		}

		return new WP_REST_Response(
			array(
				'title'        => $title,
				'description'  => $description,
				'cta_label'    => $cta_label,
				'illustration' => $illustration,
				'video'        => array(
					'url'     => $video_url,
					'poster'  => $poster,
					'caption' => $caption,
				),
			)
		);
	}

	private static function image( int $attachment_id ): ?array {
		$url = $attachment_id ? wp_get_attachment_image_url( $attachment_id, 'full' ) : false;

		return $url ? array(
			'url' => esc_url_raw( $url ),
			'alt' => sanitize_text_field( (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ),
		) : null;
	}

	private static function region( int $city_id ): array {
		$terms = get_the_terms( $city_id, 'region' );
		$term  = is_array( $terms ) ? current( $terms ) : false;

		return $term instanceof \WP_Term ? array(
			'id'    => $term->term_id,
			'label' => $term->name,
			'slug'  => $term->slug,
		) : array(
			'id'    => 0,
			'label' => 'Інші міста',
			'slug'  => 'other',
		);
	}
	}
