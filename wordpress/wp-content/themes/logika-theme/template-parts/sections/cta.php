<?php

declare(strict_types=1);

$data = wp_parse_args( $args ?? array(), array( 'section_id' => '', 'title' => 'Підберемо курс саме для вашої дитини!', 'subtitle' => 'Ми зателефонуємо в зручний час', 'image' => 0, 'button_label' => 'Отримати консультацію', 'course_id' => 0, 'camp_id' => 0, 'city_id' => 0 ) );
?>
<section class="cta-section"<?php echo $data['section_id'] ? ' id="' . esc_attr( $data['section_id'] ) . '"' : ''; ?>><div class="container"><div class="cta-section__wrapp"><div class="cta-section__left"><div class="cta-form__top"><h2 class="cta-form__title h3"><?php echo esc_html( $data['title'] ); ?></h2><p class="cta-form__subtitle h4"><?php echo esc_html( $data['subtitle'] ); ?></p></div><?php get_template_part( 'template-parts/forms/lead', null, array( 'class' => 'cta-form', 'button_label' => $data['button_label'], 'course_id' => $data['course_id'], 'camp_id' => $data['camp_id'], 'city_id' => $data['city_id'] ) ); ?></div><?php if ( $data['image'] ) : ?><div class="cta-section__right"><div class="cta-section__image"><?php echo wp_get_attachment_image( (int) $data['image'], 'large' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div></div><?php endif; ?></div></div></section>
