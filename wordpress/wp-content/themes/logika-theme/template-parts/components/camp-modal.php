<?php

declare(strict_types=1);

$assets = get_template_directory_uri() . '/assets';

$selected_ids = array_values( array_filter( array_map( 'absint', (array) get_field( 'camp_archive_formats', 'camp_archive' ) ) ) );
$camps        = get_posts( array(
	'post_type'      => 'camp',
	'post_status'    => 'publish',
	'post__in'       => $selected_ids,
	'orderby'        => 'post__in',
	'posts_per_page' => count( $selected_ids ),
	'meta_query'     => array( array( 'key' => 'camp_is_active', 'value' => '1' ) ),
) );

	if ( ! $selected_ids || ! $camps ) {
	return;
}
?>
<div class="modal" data-logika-camp-modal hidden>
	<div class="modal__container is-camps" data-target="camps">
		<div class="modal__wrapper" role="dialog" aria-modal="true" aria-labelledby="camp-modal-title">
			<div class="modal__camps">
				<div class="modal__camps-top">
					<h2 class="modal__camps-title h4" id="camp-modal-title">Оберіть зміну</h2>
					<button class="modal__close modal-close" type="button" aria-label="Закрити вибір зміни">
						<img width="24" height="24" src="<?php echo esc_url( $assets . '/img/sprite/icon-close.svg' ); ?>" alt="">
					</button>
				</div>

				<ul class="modal__camps-items">
					<?php foreach ( $camps as $camp ) : ?>
						<?php
						$camp_id     = (int) $camp->ID;
						$hero_images = array_values( array_filter( array_map( 'absint', (array) get_field( 'camp_hero_images', $camp_id ) ) ) );
						$image_id    = absint( get_field( 'camp_card_image', $camp_id ) ) ?: absint( get_field( 'camp_hero_image', $camp_id ) ) ?: ( $hero_images[0] ?? 0 );
						$image_url   = $image_id ? wp_get_attachment_image_url( $image_id, 'medium_large' ) : false;
						$start       = (string) get_field( 'camp_start_date', $camp_id );
						$end         = (string) get_field( 'camp_end_date', $camp_id );
						$dates       = trim( (string) get_field( 'camp_card_dates', $camp_id ) ) ?: ( $start ? wp_date( 'd.m', strtotime( $start ) ) . ( $end ? ' - ' . wp_date( 'd.m', strtotime( $end ) ) : '' ) : trim( (string) get_field( 'camp_season', $camp_id ) ) );
						$description = trim( (string) get_field( 'camp_card_description', $camp_id ) ) ?: trim( (string) get_the_excerpt( $camp_id ) ) ?: trim( (string) get_field( 'camp_hero_text', $camp_id ) );
						?>
						<li class="modal__camps-item">
							<div class="modal__camps-image">
								<img width="100" height="100" src="<?php echo esc_url( $image_url ?: $assets . '/img/camp/team.webp' ); ?>" alt="<?php echo esc_attr( get_the_title( $camp_id ) ); ?>">
							</div>
							<?php if ( $dates ) : ?><div class="modal__camps-dates"><?php echo esc_html( $dates ); ?></div><?php endif; ?>
							<div class="modal__camps-info">
								<span><?php echo esc_html( get_the_title( $camp_id ) ); ?></span>
								<?php if ( $description ) : ?><p><?php echo esc_html( $description ); ?></p><?php endif; ?>
							</div>
							<a class="modal__camps-link btn btn--violet" href="<?php echo esc_url( get_permalink( $camp_id ) ); ?>">Дізнатись більше</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
	</div>
</div>
