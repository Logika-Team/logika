<?php

declare(strict_types=1);

$data   = wp_parse_args( $args ?? array(), array( 'title' => '', 'images' => array() ) );
$images = array_values( array_filter( array_map( 'absint', (array) $data['images'] ) ) );
if ( ! $images ) {
	return;
}
?>
<section class="gallery-section"><div class="container"><div class="gallery-section__wrapp"><?php if ( $data['title'] ) : ?><h2><?php echo esc_html( $data['title'] ); ?></h2><?php endif; ?><div class="gallery-section__slider"><div class="swiper-container"><ul class="swiper-wrapper"><?php foreach ( $images as $image_id ) : ?><li class="swiper-slide"><div class="gallery-card"><?php echo wp_get_attachment_image( $image_id, 'large' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div></li><?php endforeach; ?></ul></div></div></div></div></section>
