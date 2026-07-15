<?php

declare(strict_types=1);

$courses = new WP_Query(
	array(
		'post_type'      => 'course',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'no_found_rows'  => true,
		'orderby'        => 'menu_order title',
		'order'          => 'ASC',
	)
);

$assets = get_template_directory_uri() . '/assets/img/';
?>
<section class="services-section">
	<div class="container">
		<div class="services-section__wrapp">
			<h2>Курси програмування</h2>
			<?php if ( $courses->have_posts() ) : ?>
				<ul class="services-section__items">
					<?php while ( $courses->have_posts() ) : $courses->the_post(); ?>
						<?php
						$min_age     = get_field( 'course_age_min' );
						$max_age     = get_field( 'course_age_max' );
						$description = get_field( 'course_short_description' );
						$image       = get_the_post_thumbnail_url( get_the_ID(), 'large' ) ?: $assets . 'services/service1.png';
						$terms       = get_the_terms( get_the_ID(), 'course_direction' );
						$ages        = $min_age && $max_age ? sprintf( '%d-%d років', $min_age, $max_age ) : '';
						?>
						<li class="services-section__item">
							<div class="services-section__item-media">
								<div class="services-section__item-image"><img width="588" height="511" src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>"></div>
								<?php if ( $ages ) : ?><div class="services-section__item-ages"><?php echo esc_html( $ages ); ?></div><?php endif; ?>
								<div class="services-section__item-icon"><img src="<?php echo esc_url( $assets . 'services/service1.svg' ); ?>" alt=""></div>
							</div>
							<div class="services-section__item-content">
								<div class="services-section__item-title h3"><?php the_title(); ?></div>
								<?php if ( $terms && ! is_wp_error( $terms ) ) : ?><ul class="services-section__item-tags"><?php foreach ( $terms as $term ) : ?><li class="h5"><?php echo esc_html( $term->name ); ?></li><?php endforeach; ?></ul><?php endif; ?>
								<?php if ( $description ) : ?><p class="services-section__item-excerpt"><?php echo esc_html( $description ); ?></p><?php endif; ?>
								<div class="services-section__item-btns">
									<a href="<?php echo esc_url( home_url( '/#lead-form' ) ); ?>" class="services-section__item-lesson btn btn--yellow">Запис на безкоштовний урок <svg width="20" height="20"><use href="<?php echo esc_url( $assets . 'sprite/sprite.svg#arrow-right' ); ?>"></use></svg></a>
									<a href="<?php the_permalink(); ?>" class="services-section__item-about btn">Детальніше про курс <svg width="20" height="20"><use href="<?php echo esc_url( $assets . 'sprite/sprite.svg#arrow-right' ); ?>"></use></svg></a>
								</div>
							</div>
						</li>
					<?php endwhile; ?>
				</ul>
			<?php else : ?>
				<p class="services-section__item-excerpt"><?php echo esc_html( get_field( 'fallback_no_course_text', 'option' ) ?: 'Наразі курси готуються до публікації.' ); ?></p>
			<?php endif; wp_reset_postdata(); ?>
		</div>
	</div>
</section>
