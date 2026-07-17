<?php

declare(strict_types=1);

$data = wp_parse_args( $args ?? array(), array( 'title' => '', 'text' => '', 'image' => 0, 'page_id' => 0 ) );
if ( ! $data['title'] && ! $data['text'] && ! $data['image'] ) {
	return;
}
?>
<section class="banner-section"><div class="container"><div class="banner-section__wrapp"><div class="banner-section__blocks"><div class="banner-section__left"><div class="banner-section__info"><?php if ( $data['title'] ) : ?><h1><?php echo esc_html( $data['title'] ); ?></h1><?php endif; ?><?php if ( $data['text'] ) : ?><h4><?php echo esc_html( $data['text'] ); ?></h4><?php endif; ?></div><?php if ( $data['image'] ) : ?><div class="banner-section__character-boy"><?php echo wp_get_attachment_image( (int) $data['image'], 'large' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><?php endif; ?></div><div class="banner-section__right" id="lead-form"><?php get_template_part( 'template-parts/forms/lead', null, array() ); ?></div></div></div></div></section>
