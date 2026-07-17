<?php

declare(strict_types=1);

final class Logika_Theme_City_Page {
	private static int $active_city_id = 0;

	public static function renderHome( int $city_id ): void {
		self::$active_city_id = $city_id;
		add_filter( 'acf/format_value', array( self::class, 'inheritHomeValue' ), 20, 3 );

		try {
			Logika_Theme_Source_Markup::renderPage( 'index', $city_id );
		} finally {
			remove_filter( 'acf/format_value', array( self::class, 'inheritHomeValue' ), 20 );
			self::$active_city_id = 0;
		}
	}

	public static function inheritHomeValue( mixed $value, mixed $post_id, array $field ): mixed {
		if ( ! self::$active_city_id || (int) $post_id !== (int) get_option( 'page_on_front' ) ) {
			return $value;
		}

		$map = array(
			'home_hero_title'              => 'city_home_hero_title',
			'home_hero_text'               => 'city_home_hero_text',
			'home_hero_boy_image_override' => 'city_home_hero_image',
			'home_locations_title'         => 'city_home_locations_title',
			'home_locations_text'          => 'city_home_locations_text',
			'home_cta_title'                => 'city_home_cta_title',
			'home_media_title'              => 'city_home_media_title',
			'home_media_text'               => 'city_home_media_text',
			'home_faq_title'                => 'city_home_faq_title',
		);
		$name = (string) ( $field['name'] ?? '' );
		if ( isset( $map[ $name ] ) ) {
			$override = get_field( $map[ $name ], self::$active_city_id );
			return self::hasValue( $override ) ? $override : $value;
		}

		if ( 'home_faq_items' === $name ) {
			$ids = Logika_Theme_Entities::faqs( (array) get_field( 'city_related_faq', self::$active_city_id ) );
			if ( $ids ) {
				return array_map(
					static fn( int $id ): array => array(
						'question' => get_field( 'faq_question', $id ) ?: get_the_title( $id ),
						'answer'   => get_field( 'faq_answer', $id ),
					),
					$ids
				);
			}
		}

		return $value;
	}

	private static function hasValue( mixed $value ): bool {
		return is_array( $value ) ? (bool) $value : '' !== trim( (string) $value );
	}

	public static function render_reviews( int $city_id ): string {
		$reviews = self::related_posts( $city_id, 'city_related_reviews', 'fallback_reviews', 'review', 'review_is_approved' );

		ob_start();
		?>
		<section class="testimonials-section"><div class="container"><div class="testimonials-section__wrapp"><h2><?php esc_html_e( 'Відгуки батьків', 'logika-theme' ); ?></h2>
			<?php if ( $reviews ) : ?><ul class="services-section__items"><?php foreach ( $reviews as $review ) : ?><li class="services-section__item"><div class="services-section__item-content"><h3><?php echo esc_html( get_field( 'review_author_name', $review->ID ) ?: get_the_title( $review ) ); ?></h3><div class="editor"><?php echo wp_kses_post( get_field( 'review_text', $review->ID ) ); ?></div></div></li><?php endforeach; ?></ul><?php else : ?><p><?php esc_html_e( 'Відгуки для цього міста незабаром з’являться.', 'logika-theme' ); ?></p><?php endif; ?>
		</div></div></section>
		<?php

		return (string) ob_get_clean();
	}

	public static function render_faq( int $city_id ): string {
		$items = self::related_posts( $city_id, 'city_related_faq', 'fallback_faq', 'faq_item', 'faq_is_active' );

		ob_start();
		?>
		<section class="faq-section"><div class="container"><div class="faq-section__wrapp"><h2><?php esc_html_e( 'Питання та відповіді', 'logika-theme' ); ?></h2>
			<?php if ( $items ) : ?><ul class="accordion" data-single="true" data-accordion-init><?php foreach ( $items as $item ) : $id = 'city-faq-' . $item->ID; ?><li class="accordion__item"><button class="accordion__btn h5" data-id="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( get_field( 'faq_question', $item->ID ) ?: get_the_title( $item ) ); ?></button><div class="accordion__content" data-content="<?php echo esc_attr( $id ); ?>"><div class="editor"><?php echo wp_kses_post( get_field( 'faq_answer', $item->ID ) ); ?></div></div></li><?php endforeach; ?></ul><?php else : ?><p><?php esc_html_e( 'Відповіді на запитання незабаром з’являться.', 'logika-theme' ); ?></p><?php endif; ?>
		</div></div></section>
		<?php

		return (string) ob_get_clean();
	}

	private static function related_posts( int $city_id, string $field, string $fallback, string $post_type, string $published_meta ): array {
		$ids = array_map( 'absint', (array) get_field( $field, $city_id ) );
		$ids = $ids ?: array_map( 'absint', (array) get_field( $fallback, 'option' ) );

		return $ids ? get_posts( array( 'post_type' => $post_type, 'post_status' => 'publish', 'post__in' => $ids, 'orderby' => 'post__in', 'posts_per_page' => -1, 'meta_key' => $published_meta, 'meta_value' => '1' ) ) : array();
	}

	public static function render_related( int $city_id, string $field, string $heading ): string {
		$ids = array_map( 'absint', (array) get_field( $field, $city_id ) );

		$posts = $ids ? get_posts(
			array(
				'post_type'      => 'city_related_courses' === $field ? 'course' : 'camp',
				'post_status'    => 'publish',
				'post__in'       => $ids,
				'orderby'        => 'post__in',
				'posts_per_page' => -1,
			)
		) : array();

		ob_start();
		?>
		<section class="services-section"><div class="container"><div class="services-section__wrapp"><h2><?php echo esc_html( $heading ); ?></h2>
			<?php if ( $posts ) : ?><ul class="services-section__items"><?php foreach ( $posts as $post ) : ?><li class="services-section__item"><div class="services-section__item-content"><h3><?php echo esc_html( get_the_title( $post ) ); ?></h3><a class="btn" href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php esc_html_e( 'Детальніше', 'logika-theme' ); ?></a></div></li><?php endforeach; ?></ul>
			<?php else : ?><p><?php echo esc_html( 'city_related_courses' === $field ? ( get_field( 'fallback_no_course_text', 'option' ) ?: 'Курси для цього міста незабаром з’являться.' ) : ( get_field( 'fallback_no_camp_text', 'option' ) ?: 'Табори для цього міста незабаром з’являться.' ) ); ?></p><?php endif; ?>
		</div></div></section>
		<?php

		return (string) ob_get_clean();
	}

	public static function render_branches( int $city_id ): string {
		$branches = new WP_Query(
			array(
				'post_type'      => 'branch',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'no_found_rows'  => true,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'meta_query'     => array(
					'relation' => 'AND',
					array( 'key' => 'branch_city_id', 'value' => (string) $city_id ),
					array( 'key' => 'branch_is_active', 'value' => '1' ),
				),
			)
		);

		ob_start();
		?>
		<section class="locations-section">
			<div class="container"><div class="locations-section__wrapp">
				<h2><?php esc_html_e( 'Наші філії', 'logika-theme' ); ?></h2>
				<?php if ( $branches->have_posts() ) : ?><ul class="services-section__items">
					<?php while ( $branches->have_posts() ) : $branches->the_post(); ?>
						<li class="services-section__item"><div class="services-section__item-content"><h3><?php the_title(); ?></h3><p class="services-section__item-excerpt"><?php echo esc_html( get_field( 'branch_address' ) ); ?></p><?php if ( get_field( 'branch_phone' ) ) : ?><a class="btn" href="tel:<?php echo esc_attr( preg_replace( '/[^+0-9]/', '', (string) get_field( 'branch_phone' ) ) ); ?>"><?php echo esc_html( get_field( 'branch_phone' ) ); ?></a><?php endif; ?></div></li>
					<?php endwhile; ?>
				</ul><?php else : ?><p><?php echo esc_html( get_field( 'fallback_no_branch_text', 'option' ) ?: 'У цьому місті поки немає доступних філій.' ); ?></p><?php endif; wp_reset_postdata(); ?>
			</div></div>
		</section>
		<?php

		return (string) ob_get_clean();
	}
}
