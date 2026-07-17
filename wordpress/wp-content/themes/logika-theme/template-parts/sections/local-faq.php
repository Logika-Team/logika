<?php

declare(strict_types=1);

$data  = wp_parse_args( $args ?? array(), array( 'title' => 'Питання та відповіді', 'items' => array(), 'id_prefix' => 'local-faq' ) );
$items = array_values( array_filter( (array) $data['items'], static fn( array $item ): bool => ! empty( $item['question'] ) && ! empty( $item['answer'] ) ) );
if ( ! $items ) {
	return;
}
?>
<section class="faq-section"><div class="container"><div class="faq-section__wrapp"><h2><?php echo esc_html( $data['title'] ); ?></h2><ul class="accordion" data-single="true" data-accordion-init><?php foreach ( $items as $index => $item ) : $item_id = sanitize_html_class( $data['id_prefix'] . '-' . $index ); ?><li class="accordion__item"><button class="accordion__btn h5" data-id="<?php echo esc_attr( $item_id ); ?>"><?php echo esc_html( $item['question'] ); ?></button><div class="accordion__content" data-content="<?php echo esc_attr( $item_id ); ?>"><div class="editor"><?php echo wp_kses_post( (string) $item['answer'] ); ?></div></div></li><?php endforeach; ?></ul></div></div></section>
