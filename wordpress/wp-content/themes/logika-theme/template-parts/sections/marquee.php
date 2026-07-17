<?php

declare(strict_types=1);

$items = (array) ( $args['items'] ?? array() );
if ( ! $items ) {
	return;
}
?>
<section class="marquee-section" aria-label="Ключові переваги"><div class="marquee-section__inner"><ul class="marquee-section__items"><?php foreach ( $items as $item ) : if ( empty( $item['text'] ) ) { continue; } ?><li><?php echo esc_html( $item['text'] ); ?></li><?php endforeach; ?></ul></div></section>
