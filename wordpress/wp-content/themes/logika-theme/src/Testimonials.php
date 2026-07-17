<?php

declare(strict_types=1);

final class Logika_Theme_Testimonials {
	public static function apply( string $markup, ?array $ids = null ): string {
		$reviews = array_filter( array_map( 'get_post', array_slice( Logika_Theme_Entities::reviews( $ids ), 0, 12 ) ) );

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
