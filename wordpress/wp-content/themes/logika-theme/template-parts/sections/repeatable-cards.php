<?php

declare(strict_types=1);

$data  = wp_parse_args( $args ?? array(), array( 'class' => 'content-section', 'title' => '', 'items' => array() ) );
$items = (array) $data['items'];
if ( ! $items ) {
	return;
}
$class = sanitize_html_class( (string) $data['class'] );
?>
<section class="<?php echo esc_attr( $class ); ?>"><div class="container"><div class="<?php echo esc_attr( $class ); ?>__wrapp"><?php if ( $data['title'] ) : ?><h2><?php echo esc_html( $data['title'] ); ?></h2><?php endif; ?><ul class="<?php echo esc_attr( $class ); ?>__items"><?php foreach ( $items as $item ) : ?><li class="<?php echo esc_attr( $class ); ?>__item"><?php if ( $item['image'] ?? 0 ) : ?><div class="<?php echo esc_attr( $class ); ?>__item-image"><?php echo wp_get_attachment_image( (int) $item['image'], 'large', false, array( 'alt' => (string) ( $item['title'] ?? '' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><?php endif; ?><?php if ( $item['title'] ?? '' ) : ?><h3><?php echo esc_html( $item['title'] ); ?></h3><?php endif; ?><?php if ( $item['text'] ?? '' ) : ?><p><?php echo esc_html( $item['text'] ); ?></p><?php endif; ?><?php if ( ! empty( $item['link']['url'] ) ) : ?><a class="btn btn--yellow" href="<?php echo esc_url( $item['link']['url'] ); ?>"><?php echo esc_html( $item['link']['title'] ?: 'Дізнатись більше' ); ?></a><?php endif; ?></li><?php endforeach; ?></ul></div></div></section>
