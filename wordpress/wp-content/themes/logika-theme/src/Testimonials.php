<?php

declare(strict_types=1);

final class Logika_Theme_Testimonials {
	public static function apply( string $markup, ?array $ids = null, int|string $image_context = 0 ): string {
		$reviews = array_filter( array_map( 'get_post', array_slice( Logika_Theme_Entities::reviews( $ids ), 0, 12 ) ) );

		if ( ! $reviews ) {
			return $markup;
		}

		$index = 0;
		$markup = (string) preg_replace_callback(
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

		return self::replaceDecorations( self::replaceAvatars( $markup, $reviews ), $image_context );
	}

	/** @param array<int, \WP_Post> $reviews */
	private static function replaceAvatars( string $markup, array $reviews ): string {
		$index = 0;

		return (string) preg_replace_callback(
			'#(<div class="testimonials-card__avatar">).*?(</div>)#s',
			static function ( array $matches ) use ( $reviews, &$index ): string {
				$photo = isset( $reviews[ $index ] ) ? (int) get_field( 'review_photo', $reviews[ $index++ ]->ID ) : 0;

				return $photo ? $matches[1] . wp_get_attachment_image( $photo, 'thumbnail', false, array( 'width' => 56, 'height' => 56, 'alt' => '' ) ) . $matches[2] : $matches[0];
			},
			$markup
		);
	}

	private static function replaceDecorations( string $markup, int|string $image_context ): string {
		$field  = $image_context ? 'testimonials_image_' : 'home_testimonials_image_';
		$images = array_map( static fn( int $index ): int => (int) get_field( "{$field}{$index}", $image_context ?: (int) get_option( 'page_on_front' ) ), range( 1, 4 ) );
		$index  = 0;

		return (string) preg_replace_callback(
			'#(<div class="testimonials-card is-image">.*?<picture>).*?(</picture>)#s',
			static function ( array $matches ) use ( $images, &$index ): string {
				$image = $images[ $index++ ] ?? 0;

				return $image ? $matches[1] . wp_get_attachment_image( $image, 'medium', false, array( 'width' => 220, 'height' => 220, 'alt' => '' ) ) . $matches[2] : $matches[0];
			},
			$markup
		);
	}
}
