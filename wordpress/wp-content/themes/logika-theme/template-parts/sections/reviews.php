<?php

declare(strict_types=1);

$data    = wp_parse_args( $args ?? array(), array( 'title' => 'Довіра, підтверджена результатами', 'items' => null ) );
$reviews = Logika_Theme_Entities::reviews( is_array( $data['items'] ) ? $data['items'] : null );
if ( ! $reviews ) {
	return;
}
?>
<section class="testimonials-section"><div class="container"><div class="testimonials-section__wrapp"><h2><?php echo esc_html( $data['title'] ); ?></h2><div class="testimonials-section__box"><ul class="testimonials-section__items">
	<?php foreach ( $reviews as $review_id ) : $name = (string) ( get_field( 'review_author_name', $review_id ) ?: get_the_title( $review_id ) ); ?>
		<li class="testimonials-section__item"><div class="testimonials-card"><div class="testimonials-card__box"><div class="testimonials-card__top"><div class="testimonials-card__info"><div class="testimonials-card__name"><?php echo esc_html( $name ); ?></div></div></div><p class="testimonials-card__excerpt"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( (string) get_field( 'review_text', $review_id ) ), 40, '…' ) ); ?></p></div></div></li>
	<?php endforeach; ?>
</ul></div></div></div></section>
