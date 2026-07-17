<?php

declare(strict_types=1);

final class Logika_Theme_Camp_Page {
	public static function render( int $camp_id ): string {
		$start      = (string) get_field( 'camp_start_date', $camp_id );
		$end        = (string) get_field( 'camp_end_date', $camp_id );
		$dates      = $start && $end ? wp_date( 'd.m.Y', strtotime( $start ) ) . ' — ' . wp_date( 'd.m.Y', strtotime( $end ) ) : '';
		$formats    = get_the_terms( $camp_id, 'learning_format' );
		$city_ids   = array_values( array_filter( array_map( 'absint', (array) get_field( 'camp_related_cities', $camp_id ) ) ) );
		$cities     = $city_ids ? get_posts( array( 'post_type' => 'city', 'post_status' => 'publish', 'post__in' => $city_ids, 'orderby' => 'post__in', 'posts_per_page' => -1 ) ) : array();
		$expired    = $end && strtotime( $end . ' 23:59:59' ) < current_time( 'timestamp' );
		$hero_image = (int) get_field( 'camp_hero_image', $camp_id ) ?: get_post_thumbnail_id( $camp_id );

		ob_start();
		?>
		<main>
		<section class="banner-section"><div class="container"><div class="banner-section__wrapp"><div class="banner-section__blocks"><div class="banner-section__left"><div class="banner-section__info"><h1><?php echo esc_html( get_the_title( $camp_id ) ); ?></h1><?php if ( get_field( 'camp_season', $camp_id ) ) : ?><h4><?php echo esc_html( get_field( 'camp_season', $camp_id ) ); ?></h4><?php endif; ?><?php if ( $dates ) : ?><p><?php echo esc_html( $dates ); ?></p><?php endif; ?><?php if ( $formats && ! is_wp_error( $formats ) ) : ?><p><?php echo esc_html( implode( ' · ', wp_list_pluck( $formats, 'name' ) ) ); ?></p><?php endif; ?><?php if ( $cities ) : ?><p><?php echo esc_html( implode( ' · ', wp_list_pluck( $cities, 'post_title' ) ) ); ?></p><?php endif; ?><p><?php echo esc_html( get_field( 'camp_hero_text', $camp_id ) ); ?></p><?php if ( $expired ) : ?><p><?php echo esc_html( get_field( 'camp_expired_state_text', $camp_id ) ?: 'Ця зміна вже завершилася.' ); ?></p><?php else : ?><a class="btn btn--yellow" href="#lead-form"><?php echo esc_html( get_field( 'camp_cta_label', $camp_id ) ?: 'Записатися до табору' ); ?></a><?php endif; ?></div></div><?php if ( $hero_image ) : ?><div class="banner-section__right"><?php echo wp_get_attachment_image( $hero_image, 'large' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><?php endif; ?></div></div></div></section>
		<?php
		get_template_part( 'template-parts/sections/repeatable-cards', null, array( 'class' => 'benefits-section', 'title' => get_field( 'camp_benefits_title', $camp_id ), 'items' => get_field( 'camp_benefits', $camp_id ) ) );
		get_template_part( 'template-parts/sections/repeatable-cards', null, array( 'class' => 'activities-section', 'title' => get_field( 'camp_activities_title', $camp_id ), 'items' => get_field( 'camp_activities', $camp_id ) ) );
		self::program( $camp_id );
		get_template_part( 'template-parts/sections/repeatable-cards', null, array( 'class' => 'trips-section', 'title' => get_field( 'camp_trips_title', $camp_id ), 'items' => get_field( 'camp_trips', $camp_id ) ) );
		get_template_part( 'template-parts/sections/repeatable-cards', null, array( 'class' => 'details-section', 'title' => get_field( 'camp_details_title', $camp_id ), 'items' => get_field( 'camp_details', $camp_id ) ) );
		if ( ! $expired ) {
			get_template_part( 'template-parts/sections/cta', null, array( 'section_id' => 'lead-form', 'title' => get_field( 'camp_booking_title', $camp_id ) ?: 'Забронювати місце у таборі', 'subtitle' => get_field( 'camp_booking_text', $camp_id ), 'image' => get_field( 'camp_booking_image', $camp_id ), 'button_label' => get_field( 'camp_cta_label', $camp_id ), 'camp_id' => $camp_id ) );
		}
		get_template_part( 'template-parts/sections/gallery', null, array( 'title' => get_field( 'camp_gallery_title', $camp_id ), 'images' => get_field( 'camp_gallery', $camp_id ) ) );
		get_template_part( 'template-parts/sections/reviews', null, array( 'title' => get_field( 'camp_reviews_title', $camp_id ), 'items' => (array) get_field( 'camp_related_reviews', $camp_id ) ?: null ) );
		$faq_args = array( 'section_title' => get_field( 'camp_faq_title', $camp_id ) ?: 'Питання та відповіді' );
		$faq_ids  = (array) get_field( 'camp_related_faq', $camp_id );
		if ( $faq_ids ) {
			$faq_args['items'] = $faq_ids;
		}
		get_template_part( 'template-parts/sections/faq', null, $faq_args );
		echo '</main>';

		return (string) ob_get_clean();
	}

	private static function program( int $camp_id ): void {
		$program = (array) get_field( 'camp_program', $camp_id );
		if ( ! $program ) {
			return;
		}
		?><section class="services-section"><div class="container"><div class="services-section__wrapp"><h2><?php echo esc_html( get_field( 'camp_program_title', $camp_id ) ?: 'Програма табору' ); ?></h2><ul class="services-section__items"><?php foreach ( $program as $day ) : ?><li class="services-section__item"><div class="services-section__item-content"><h3><?php echo esc_html( $day['title'] ?? '' ); ?></h3><div class="editor"><?php echo wp_kses_post( (string) ( $day['description'] ?? '' ) ); ?></div><?php if ( ! empty( $day['items'] ) ) : ?><ul><?php foreach ( $day['items'] as $item ) : ?><li><?php echo esc_html( $item['item_text'] ?? '' ); ?></li><?php endforeach; ?></ul><?php endif; ?></div></li><?php endforeach; ?></ul></div></div></section><?php
	}
}
