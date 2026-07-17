<?php

declare(strict_types=1);

$data = wp_parse_args( $args ?? array(), array( 'title' => 'Наші партнери', 'items' => array() ) );
if ( ! $data['items'] ) {
	return;
}
?>
<section class="partners-section"><div class="container"><div class="partners-section__wrapp"><h2><?php echo esc_html( $data['title'] ); ?></h2><ul class="partners-section__gallery"><?php foreach ( (array) $data['items'] as $item ) : $image = wp_get_attachment_image( (int) ( $item['image'] ?? 0 ), 'medium', false, array( 'alt' => (string) ( $item['name'] ?? '' ) ) ); if ( ! $image ) { continue; } ?><li><?php if ( $item['url'] ?? '' ) : ?><a href="<?php echo esc_url( $item['url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a><?php else : echo $image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php endif; ?><span class="screen-reader-text"><?php echo esc_html( $item['name'] ?? '' ); ?></span></li><?php endforeach; ?></ul></div></div></section>
