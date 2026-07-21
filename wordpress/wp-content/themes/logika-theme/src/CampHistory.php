<?php

declare(strict_types=1);

/**
 * Fills the "Як це було минулих років" slider with city video reviews.
 */
final class Logika_Theme_Camp_History {
	private const CAPTION_PREFIX = 'Школа програмування для дітей у місті';

	public static function apply( string $markup, int $page_id ): string {
		if ( ! function_exists( 'get_field' ) || ! $page_id ) {
			return $markup;
		}

		$slides = self::slides( $page_id );

		if ( '' === $slides ) {
			// Немає жодного відгуку з відео — лишаємо статичні слайди з верстки.
			return $markup;
		}

		return (string) preg_replace_callback(
			'#(<div class="camp-history__slider">.*?<ul class=.swiper-wrapper.>).*?(</ul>)#s',
			static fn( array $m ): string => $m[1] . $slides . $m[2],
			$markup,
			1
		);
	}

	private static function slides( int $page_id ): string {
		$prefix = trim( (string) get_field( 'camp_archive_history_caption_prefix', $page_id ) ) ?: self::CAPTION_PREFIX;
		$items  = array();

		foreach ( self::reviews( $page_id ) as $review ) {
			$video_id = self::videoId( (string) get_field( 'review_video_url', $review->ID ) );

			if ( '' === $video_id ) {
				continue;
			}

			$city    = self::city( $review );
			$caption = $city ? $prefix . ' ' . get_the_title( $city ) : ( trim( (string) get_field( 'review_card_label', $review->ID ) ) ?: get_the_title( $review ) );

			$items[] = '<li class="swiper-slide"><div class="nizhyn-school__video" data-video-id="' . esc_attr( $video_id ) . '"' . ( $city ? ' data-city-id="' . esc_attr( (string) $city->ID ) . '"' : '' ) . '>'
				. '<img src="' . esc_url( self::poster( $review, $city, $video_id ) ) . '" alt="' . esc_attr( $caption ) . '" loading="lazy">'
				. '<div class="nizhyn-school__video-overlay" aria-hidden="true"></div>'
				. '<div class="nizhyn-school__video-caption">' . esc_html( $caption ) . '</div>'
				. '<button class="nizhyn-school__play" type="button" aria-label="' . esc_attr( 'Відтворити відео: ' . $caption ) . '"><span aria-hidden="true"></span></button>'
				. '</div></li>';
		}

		return implode( '', $items );
	}

	/**
	 * @return array<int, WP_Post>
	 */
	private static function reviews( int $page_id ): array {
		$ids   = array_values( array_filter( array_map( 'absint', (array) get_field( 'camp_archive_history_reviews', $page_id ) ) ) );
		$limit = absint( get_field( 'camp_archive_history_limit', $page_id ) ) ?: 8;

		$args = array(
			'post_type'      => 'review',
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'meta_query'     => array( array( 'key' => 'review_video_url', 'value' => '', 'compare' => '!=' ) ),
		);

		if ( $ids ) {
			$args['post__in'] = $ids;
			$args['orderby']  = 'post__in';
		} else {
			$args['meta_query'][] = array( 'key' => 'review_is_approved', 'value' => '1' );
			$args['meta_key']     = 'review_display_order';
			$args['orderby']      = 'meta_value_num';
			$args['order']        = 'ASC';
		}

		return get_posts( $args );
	}

	private static function city( WP_Post $review ): ?WP_Post {
		$city = get_field( 'review_related_city', $review->ID );
		$city = is_array( $city ) ? reset( $city ) : $city;
		$city = is_numeric( $city ) ? get_post( (int) $city ) : $city;

		return $city instanceof WP_Post ? $city : null;
	}

	private static function poster( WP_Post $review, ?WP_Post $city, string $video_id ): string {
		foreach ( array( get_field( 'review_photo', $review->ID ), $city ? get_field( 'city_fallback_image', $city->ID ) : 0 ) as $image ) {
			$id  = is_array( $image ) && isset( $image['ID'] ) ? (int) $image['ID'] : (int) $image;
			$url = $id > 0 ? wp_get_attachment_image_url( $id, 'large' ) : '';

			if ( $url ) {
				return (string) $url;
			}
		}

		return 'https://i.ytimg.com/vi/' . $video_id . '/hqdefault.jpg';
	}

	private static function videoId( string $url ): string {
		return preg_match( '#(?:youtu\.be/|youtube(?:-nocookie)?\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/))([A-Za-z0-9_-]{11})#', trim( $url ), $matches ) ? $matches[1] : '';
	}
}
