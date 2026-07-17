<?php

declare(strict_types=1);

$data   = wp_parse_args( $args ?? array(), array( 'title' => 'Сертифікати', 'images' => array() ) );
$images = array_values( array_filter( array_map( 'absint', (array) $data['images'] ) ) );
if ( ! $images ) {
	return;
}
?>
<section class="certificates-section"><div class="certificates-section__wrapp"><div class="container"><h2><?php echo esc_html( $data['title'] ); ?></h2><div class="certificates-section__box"><?php foreach ( $images as $image_id ) : ?><div class="certificates-section__preview"><?php echo wp_get_attachment_image( $image_id, 'large' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><?php endforeach; ?></div></div></div></section>
