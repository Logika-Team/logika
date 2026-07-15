<?php

declare(strict_types=1);

final class Logika_Theme_Camp_Page {
	public static function render( int $camp_id ): string {
		$start  = get_field( 'camp_start_date', $camp_id );
		$end    = get_field( 'camp_end_date', $camp_id );
		$season = get_field( 'camp_season', $camp_id );
		$dates  = $start && $end ? wp_date( 'd.m.Y', strtotime( $start ) ) . ' — ' . wp_date( 'd.m.Y', strtotime( $end ) ) : '';
		$program = (array) get_field( 'camp_program', $camp_id );
		$formats = get_the_terms( $camp_id, 'learning_format' );
		$cities = get_posts( array( 'post_type' => 'city', 'post_status' => 'publish', 'post__in' => array_map( 'absint', (array) get_field( 'camp_related_cities', $camp_id ) ), 'orderby' => 'post__in', 'posts_per_page' => -1 ) );
		$expired = $end && strtotime( $end . ' 23:59:59' ) < current_time( 'timestamp' );
		$expired_text = get_field( 'camp_expired_state_text', $camp_id ) ?: 'Ця зміна вже завершилася.';

		ob_start();
		?>
		<section class="banner-section"><div class="container"><div class="banner-section__wrapp"><div class="banner-section__info"><h1><?php echo esc_html( get_the_title( $camp_id ) ); ?></h1><?php if ( $season ) : ?><p class="h4"><?php echo esc_html( $season ); ?></p><?php endif; ?><?php if ( $dates ) : ?><p><?php echo esc_html( $dates ); ?></p><?php endif; ?><?php if ( $formats && ! is_wp_error( $formats ) ) : ?><p><?php echo esc_html( implode( ' · ', wp_list_pluck( $formats, 'name' ) ) ); ?></p><?php endif; ?><?php if ( $cities ) : ?><p><?php echo esc_html( implode( ' · ', wp_list_pluck( $cities, 'post_title' ) ) ); ?></p><?php endif; ?><?php if ( $expired ) : ?><p><?php echo esc_html( $expired_text ); ?></p><?php else : ?><a class="btn btn--yellow" href="<?php echo esc_url( home_url( '/#lead-form' ) ); ?>"><?php esc_html_e( 'Записатися до табору', 'logika-theme' ); ?></a><?php endif; ?></div></div></div></section>
		<?php if ( $program ) : ?><section class="services-section"><div class="container"><div class="services-section__wrapp"><h2><?php esc_html_e( 'Програма табору', 'logika-theme' ); ?></h2><ul class="services-section__items"><?php foreach ( $program as $day ) : ?><li class="services-section__item"><div class="services-section__item-content"><h3><?php echo esc_html( $day['title'] ?? '' ); ?></h3><div class="editor"><?php echo wp_kses_post( $day['description'] ?? '' ); ?></div><?php if ( ! empty( $day['items'] ) ) : ?><ul><?php foreach ( $day['items'] as $item ) : ?><li><?php echo esc_html( $item['item_text'] ?? '' ); ?></li><?php endforeach; ?></ul><?php endif; ?></div></li><?php endforeach; ?></ul></div></div></section><?php endif; ?>
		<?php

		return (string) ob_get_clean();
	}
}
