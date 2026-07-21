<?php

declare(strict_types=1);

/**
 * Fills the "Як це було минулих років" slider with per-city videos.
 */
final class Logika_Theme_Camp_History {
	private const CAPTION_PREFIX = 'Школа програмування для дітей у місті';

	public static function apply( string $markup, int $page_id ): string {
		if ( ! function_exists( 'get_field' ) || ! $page_id ) {
			return $markup;
		}

		$slides = self::slides( $page_id );

		if ( '' === $slides ) {
			// Немає жодного відео — лишаємо статичні слайди з верстки.
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

		foreach ( self::entries( $page_id ) as $entry ) {
			$video_id = self::videoId( $entry['video_url'] );

			if ( '' === $video_id ) {
				continue;
			}

			$city    = $entry['city'];
			$caption = $city ? $prefix . ' ' . get_the_title( $city ) : $entry['label'];
			$region  = $city ? self::regionId( $city ) : 0;

			$items[] = '<li class="swiper-slide"><div class="nizhyn-school__video" data-video-id="' . esc_attr( $video_id ) . '"' . ( $city ? ' data-city-id="' . esc_attr( (string) $city->ID ) . '"' : '' ) . ( $region ? ' data-region-id="' . esc_attr( (string) $region ) . '"' : '' ) . '>'
				. '<img src="' . esc_url( self::posterUrl( $entry['poster_ids'], $video_id ) ) . '" alt="' . esc_attr( $caption ) . '" loading="lazy">'
				. '<div class="nizhyn-school__video-overlay" aria-hidden="true"></div>'
				. '<div class="nizhyn-school__video-caption">' . esc_html( $caption ) . '</div>'
				. '<button class="nizhyn-school__play" type="button" aria-label="' . esc_attr( 'Відтворити відео: ' . $caption ) . '"><span aria-hidden="true"></span></button>'
				. '</div></li>';
		}

		return implode( '', $items );
	}

	/**
	 * Кожен слайд, незалежно від джерела, зводиться до однакової форми:
	 * посилання на відео, місто (якщо є) та кандидати на обкладинку.
	 *
	 * @return array<int, array{video_url: string, city: ?WP_Post, poster_ids: array<int, int>, label: string}>
	 */
	private static function entries( int $page_id ): array {
		$cityEntries = self::cityEntries( $page_id );

		return $cityEntries ?: self::reviewEntries( $page_id );
	}

	/**
	 * Основне джерело: відео, задане прямо в картці міста.
	 *
	 * @return array<int, array{video_url: string, city: ?WP_Post, poster_ids: array<int, int>, label: string}>
	 */
	private static function cityEntries( int $page_id ): array {
		$limit = absint( get_field( 'camp_archive_history_limit', $page_id ) ) ?: 8;

		$cities = get_posts(
			array(
				'post_type'      => 'city',
				'post_status'    => 'publish',
				'posts_per_page' => $limit,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'meta_query'     => array( array( 'key' => 'city_camp_history_video_url', 'value' => '', 'compare' => '!=' ) ),
			)
		);

		return array_map(
			static fn( WP_Post $city ): array => array(
				'video_url'  => (string) get_field( 'city_camp_history_video_url', $city->ID ),
				'city'       => $city,
				'poster_ids' => array( absint( get_field( 'city_camp_history_video_poster', $city->ID ) ), absint( get_field( 'city_fallback_image', $city->ID ) ) ),
				'label'      => get_the_title( $city ),
			),
			$cities
		);
	}

	/**
	 * Резервне джерело (для сумісності зі старим контентом): відео-відгуки.
	 *
	 * @return array<int, array{video_url: string, city: ?WP_Post, poster_ids: array<int, int>, label: string}>
	 */
	private static function reviewEntries( int $page_id ): array {
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

		return array_map(
			static function ( WP_Post $review ): array {
				$city = self::city( $review );

				return array(
					'video_url'  => (string) get_field( 'review_video_url', $review->ID ),
					'city'       => $city,
					'poster_ids' => array( absint( get_field( 'review_photo', $review->ID ) ), $city ? absint( get_field( 'city_fallback_image', $city->ID ) ) : 0 ),
					'label'      => trim( (string) get_field( 'review_card_label', $review->ID ) ) ?: get_the_title( $review ),
				);
			},
			get_posts( $args )
		);
	}

	private static function city( WP_Post $review ): ?WP_Post {
		$city = get_field( 'review_related_city', $review->ID );
		$city = is_array( $city ) ? reset( $city ) : $city;
		$city = is_numeric( $city ) ? get_post( (int) $city ) : $city;

		return $city instanceof WP_Post ? $city : null;
	}

	private static function regionId( WP_Post $city ): int {
		$terms = get_the_terms( $city->ID, 'region' );
		$term  = is_array( $terms ) ? current( $terms ) : false;

		return $term instanceof WP_Term ? (int) $term->term_id : 0;
	}

	/**
	 * @param array<int, int> $imageIds
	 */
	private static function posterUrl( array $imageIds, string $video_id ): string {
		foreach ( $imageIds as $id ) {
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
