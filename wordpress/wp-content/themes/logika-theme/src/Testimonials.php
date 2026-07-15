<?php

declare(strict_types=1);

final class Logika_Theme_Testimonials {
	public static function apply( string $markup ): string {
		$reviews = get_posts(
			array(
				'post_type'      => 'review',
				'post_status'    => 'publish',
				'posts_per_page' => 12,
				'meta_key'       => 'review_display_order',
				'orderby'        => array( 'meta_value_num' => 'ASC', 'date' => 'DESC' ),
				'meta_query'     => array( array( 'key' => 'review_is_approved', 'value' => '1' ) ),
			)
		);

		if ( ! $reviews ) {
			return $markup;
		}

		$index = 0;
		return (string) preg_replace_callback(
			'#(<div class="testimonials-card__name">).*?(</div>.*?<p class="testimonials-card__excerpt">).*?(</p>)#s',
			static function ( array $matches ) use ( $reviews, &$index ): string {
				if ( ! isset( $reviews[ $index ] ) ) {
					return $matches[0];
				}

				$review = $reviews[ $index++ ];
				$name   = (string) ( get_field( 'review_author_name', $review->ID ) ?: get_the_title( $review ) );
				$text   = wp_trim_words( wp_strip_all_tags( (string) get_field( 'review_text', $review->ID ) ), 30, '…' );

				return $matches[1] . esc_html( $name ) . $matches[2] . esc_html( $text ) . $matches[3];
			},
			$markup
		);
	}
}
