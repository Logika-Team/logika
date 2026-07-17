<?php

declare(strict_types=1);

final class Logika_Theme_Entities {
	/** @return int[] */
	public static function courses( ?array $ids = null ): array {
		return self::query( 'course', $ids );
	}

	/** @return int[] */
	public static function faqs( ?array $ids = null ): array {
		return self::query( 'faq_item', $ids, array( 'key' => 'faq_is_active', 'value' => '1' ) );
	}

	/** @return int[] */
	public static function reviews( ?array $ids = null ): array {
		return self::query( 'review', $ids, array( 'key' => 'review_is_approved', 'value' => '1' ), array( 'meta_value_num' => 'ASC', 'date' => 'DESC' ), 'review_display_order' );
	}

	/** @return int[] */
	private static function query( string $post_type, ?array $values, array $meta = array(), string|array $orderby = 'menu_order title', string $meta_key = '' ): array {
		$args = array( 'post_type' => $post_type, 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids', 'no_found_rows' => true );
		if ( null !== $values ) {
			$ids = array_values( array_filter( array_map( static fn( mixed $value ): int => absint( $value instanceof WP_Post ? $value->ID : $value ), $values ) ) );
			if ( ! $ids ) {
				return array();
			}
			$args['post__in'] = $ids;
			$args['orderby']  = 'post__in';
		} else {
			$args['orderby'] = $orderby;
			$args['order']   = 'ASC';
		}
		if ( $meta_key && null === $values ) {
			$args['meta_key'] = $meta_key;
		}
		if ( $meta ) {
			$args['meta_query'] = array( $meta );
		}

		return array_map( 'intval', get_posts( $args ) );
	}
}
