<?php

declare(strict_types=1);

namespace Logika\Core;

use WP_Post;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

final class ArticleViews {
	private const META_KEY = 'article_view_count';

	public static function register(): void {
		register_rest_route(
			'logika/v1',
			'/articles/(?P<id>\d+)/view',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( self::class, 'record' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	public static function record( WP_REST_Request $request ): WP_REST_Response {
		$post_id = absint( $request['id'] );
		$post = get_post( $post_id );
		if ( ! $post instanceof WP_Post || 'post' !== $post->post_type || 'publish' !== $post->post_status ) {
			return new WP_REST_Response( array( 'message' => 'Статтю не знайдено.' ), 404 );
		}

		$cookie = 'logika_article_view_' . $post_id;
		$counted = empty( $_COOKIE[ $cookie ] );
		if ( $counted ) {
			self::increment( $post_id );
			setcookie( $cookie, '1', array( 'expires' => time() + YEAR_IN_SECONDS, 'path' => COOKIEPATH ?: '/', 'domain' => COOKIE_DOMAIN, 'secure' => is_ssl(), 'httponly' => true, 'samesite' => 'Lax' ) );
		}

		$response = new WP_REST_Response( array( 'views' => (int) get_post_meta( $post_id, self::META_KEY, true ), 'counted' => $counted ) );
		$response->header( 'Cache-Control', 'no-store' );

		return $response;
	}

	private static function increment( int $post_id ): void {
		global $wpdb;

		if ( ! metadata_exists( 'post', $post_id, self::META_KEY ) ) {
			add_post_meta( $post_id, self::META_KEY, 0, true );
		}

		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->postmeta} SET meta_value = CAST(meta_value AS UNSIGNED) + 1 WHERE post_id = %d AND meta_key = %s", $post_id, self::META_KEY ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		wp_cache_delete( $post_id, 'post_meta' );
	}
}
