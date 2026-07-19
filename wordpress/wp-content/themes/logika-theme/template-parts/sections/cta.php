<?php

declare(strict_types=1);

$data = wp_parse_args( $args ?? array(), array( 'section_id' => '', 'title' => 'Підберемо курс саме для вашої дитини!', 'subtitle' => 'Ми зателефонуємо в зручний час', 'image' => 0, 'button_label' => 'Отримати консультацію', 'course_id' => 0, 'camp_id' => 0, 'city_id' => 0 ) );
$assets = get_template_directory_uri() . '/assets/img/cta/';
?>
<section class="cta-section"<?php echo $data['section_id'] ? ' id="' . esc_attr( $data['section_id'] ) . '"' : ''; ?>><div class="container"><div class="cta-section__wrapp"><div class="cta-section__left"><?php get_template_part( 'template-parts/forms/lead', null, array( 'class' => 'cta-form', 'title' => $data['title'], 'subtitle' => $data['subtitle'], 'button_label' => $data['button_label'], 'course_id' => $data['course_id'], 'camp_id' => $data['camp_id'], 'city_id' => $data['city_id'] ) ); ?></div><div class="cta-section__right" aria-hidden="true"><div class="cta-section__image"><picture><source type="image/webp" srcset="<?php echo esc_url( $assets . 'cta.webp' ); ?>"><img width="744" height="1068" src="<?php echo esc_url( $assets . 'cta.png' ); ?>" alt=""></picture></div><div class="cta-section__character-logika"><img width="97" height="146" src="<?php echo esc_url( $assets . 'cta-icon.svg' ); ?>" alt=""></div></div><div class="cta-section__top-bg" aria-hidden="true"><img src="<?php echo esc_url( $assets . 'cta-top-bg.svg' ); ?>" alt=""></div><div class="cta-section__bottom-bg" aria-hidden="true"><img src="<?php echo esc_url( $assets . 'cta-bottom-bg.svg' ); ?>" alt=""></div></div></div></section>
