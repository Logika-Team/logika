<?php

declare(strict_types=1);

final class Logika_Theme_Course_Page {
	public static function render( int $course_id ): string {
		$min_age     = get_field( 'course_age_min', $course_id );
		$max_age     = get_field( 'course_age_max', $course_id );
		$description = get_field( 'course_short_description', $course_id );
		$terms       = get_the_terms( $course_id, 'course_direction' );
		$formats     = get_the_terms( $course_id, 'learning_format' );
		$ages        = $min_age && $max_age ? sprintf( '%d-%d років', $min_age, $max_age ) : '';
		$program     = (array) get_field( 'course_program', $course_id );
		$faq = get_posts( array( 'post_type' => 'faq_item', 'post_status' => 'publish', 'posts_per_page' => -1, 'meta_key' => 'faq_related_course', 'meta_value' => $course_id, 'meta_compare' => '=', 'meta_query' => array( array( 'key' => 'faq_is_active', 'value' => '1' ) ) ) );

		ob_start();
		?>
		<section class="banner-section"><div class="container"><div class="banner-section__wrapp"><div class="banner-section__info"><h1><?php echo esc_html( get_the_title( $course_id ) ); ?></h1><?php if ( $ages ) : ?><p class="h4"><?php echo esc_html( $ages ); ?></p><?php endif; ?><?php if ( $terms && ! is_wp_error( $terms ) ) : ?><p><?php echo esc_html( implode( ' · ', wp_list_pluck( $terms, 'name' ) ) ); ?></p><?php endif; ?><?php if ( $formats && ! is_wp_error( $formats ) ) : ?><p><?php echo esc_html( implode( ' · ', wp_list_pluck( $formats, 'name' ) ) ); ?></p><?php endif; ?><?php if ( $description ) : ?><p><?php echo esc_html( $description ); ?></p><?php endif; ?><a class="btn btn--yellow" href="<?php echo esc_url( home_url( '/#lead-form' ) ); ?>"><?php esc_html_e( 'Записатися на безкоштовний урок', 'logika-theme' ); ?></a></div></div></div></section>
		<?php if ( $program ) : ?><section class="services-section"><div class="container"><div class="services-section__wrapp"><h2><?php esc_html_e( 'Програма курсу', 'logika-theme' ); ?></h2><ul class="services-section__items"><?php foreach ( $program as $module ) : ?><li class="services-section__item"><div class="services-section__item-content"><h3><?php echo esc_html( $module['title'] ?? '' ); ?></h3><div class="editor"><?php echo wp_kses_post( $module['description'] ?? '' ); ?></div><?php if ( ! empty( $module['items'] ) ) : ?><ul><?php foreach ( $module['items'] as $item ) : ?><li><?php echo esc_html( $item['item_text'] ?? '' ); ?></li><?php endforeach; ?></ul><?php endif; ?></div></li><?php endforeach; ?></ul></div></div></section><?php endif; ?>
		<?php if ( $faq ) : ?><section class="faq-section"><div class="container"><div class="faq-section__wrapp"><h2><?php esc_html_e( 'Питання та відповіді', 'logika-theme' ); ?></h2><ul class="accordion" data-single="true" data-accordion-init><?php foreach ( $faq as $item ) : $id = 'course-faq-' . $item->ID; ?><li class="accordion__item"><button class="accordion__btn h5" data-id="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( get_field( 'faq_question', $item->ID ) ?: $item->post_title ); ?></button><div class="accordion__content" data-content="<?php echo esc_attr( $id ); ?>"><div class="editor"><?php echo wp_kses_post( get_field( 'faq_answer', $item->ID ) ); ?></div></div></li><?php endforeach; ?></ul></div></div></section><?php endif; ?>
		<?php

		return (string) ob_get_clean();
	}
}
