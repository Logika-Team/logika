<?php

declare(strict_types=1);

$data = wp_parse_args( $args ?? array(), array( 'title' => '', 'items' => array(), 'class' => 'articles-section' ) );
$ids  = array_values( array_filter( array_map( 'absint', (array) $data['items'] ), static fn( int $id ): bool => $id && 'post' === get_post_type( $id ) && 'publish' === get_post_status( $id ) ) );
if ( ! $ids ) {
	return;
}
$class = sanitize_html_class( (string) $data['class'] );
?>
<section class="<?php echo esc_attr( $class ); ?>"><div class="container"><div class="<?php echo esc_attr( $class ); ?>__wrapp"><?php if ( $data['title'] ) : ?><h2><?php echo esc_html( $data['title'] ); ?></h2><?php endif; ?><ul class="<?php echo esc_attr( $class ); ?>__items"><?php foreach ( $ids as $post_id ) : ?><li class="<?php echo esc_attr( $class ); ?>__item"><a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"><?php echo get_the_post_thumbnail( $post_id, 'medium' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><h3><?php echo esc_html( get_the_title( $post_id ) ); ?></h3></a></li><?php endforeach; ?></ul></div></div></section>
