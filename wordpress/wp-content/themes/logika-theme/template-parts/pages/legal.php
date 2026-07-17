<?php

declare(strict_types=1);

$id = absint( $args['page_id'] ?? 0 );
?>
<main><article class="legal-page"><div class="container"><header class="legal-page__intro"><h1><?php echo esc_html( get_field( 'legal_intro_title', $id ) ?: get_the_title( $id ) ); ?></h1><?php echo wp_kses_post( (string) get_field( 'legal_intro_text', $id ) ); ?></header><?php foreach ( (array) ( $args['sections'] ?? array() ) as $section ) : $anchor = sanitize_title( (string) ( $section['anchor'] ?? '' ) ); ?><section class="legal-page__section"<?php echo $anchor ? ' id="' . esc_attr( $anchor ) . '"' : ''; ?>><?php if ( $section['heading'] ?? '' ) : ?><h2><?php echo esc_html( $section['heading'] ); ?></h2><?php endif; ?><?php echo wp_kses_post( (string) ( $section['content'] ?? '' ) ); ?></section><?php endforeach; ?></div><?php get_template_part( 'template-parts/sections/gallery', null, array( 'images' => $args['gallery'] ?? array() ) ); ?></article></main>
