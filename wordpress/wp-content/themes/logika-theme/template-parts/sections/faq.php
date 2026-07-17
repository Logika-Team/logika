<?php

declare(strict_types=1);

$faq_section_args = wp_parse_args(
	$args ?? array(),
	array(
		'section_id'          => '',
		'section_title'       => __( 'Питання та відповіді', 'logika-theme' ),
		'with_backgrounds'    => false,
		'accordion_class'     => 'accordion',
		'accordion_breakpoint' => '576',
		'accordion_default'   => '1',
		'accordion_single'    => 'true',
		'fallback_faq_items'  => array(),
	)
);

$section_id           = (string) $faq_section_args['section_id'];
$section_title        = (string) $faq_section_args['section_title'];
$with_backgrounds     = (bool) $faq_section_args['with_backgrounds'];
$accordion_class      = (string) $faq_section_args['accordion_class'];
$accordion_default    = (string) $faq_section_args['accordion_default'];
$accordion_breakpoint = (string) $faq_section_args['accordion_breakpoint'];
$accordion_single     = (string) ( 'false' === (string) $faq_section_args['accordion_single'] ? 'false' : 'true' );

$faq = new WP_Query(
	array(
		'post_type'      => 'faq_item',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'no_found_rows'  => true,
		'orderby'        => 'menu_order title',
		'order'          => 'ASC',
		'meta_query'     => array(
			array(
				'key'     => 'faq_is_active',
				'value'   => '1',
				'compare' => '=',
			),
		),
	)
);
?>
<section class="faq-section"<?php echo '' === $section_id ? '' : ' id="' . esc_attr( $section_id ) . '"'; ?>>
	<?php if ( $with_backgrounds ) : ?>
		<div class="faq-section__left-bg">
			<img src="img/faq/faq-left-bg.svg" alt="">
		</div>

		<div class="faq-section__right-bg">
			<img src="img/faq/faq-right-bg.svg" alt="">
		</div>
	<?php endif; ?>

	<div class="container">
		<div class="faq-section__wrapp">
			<h2><?php echo esc_html( $section_title ); ?></h2>
			<?php if ( $faq->have_posts() ) : ?>
				<ul class="<?php echo esc_attr( $accordion_class ); ?>" data-default="<?php echo esc_attr( $accordion_default ); ?>" data-single="<?php echo esc_attr( $accordion_single ); ?>" data-breakpoint="<?php echo esc_attr( $accordion_breakpoint ); ?>" data-accordion-init>
					<?php while ( $faq->have_posts() ) : $faq->the_post(); ?>
						<?php $id = 'faq-' . get_the_ID(); ?>
						<li class="accordion__item">
							<button class="accordion__btn h5" data-id="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( get_field( 'faq_question' ) ?: get_the_title() ); ?></button>
							<div class="accordion__content" data-content="<?php echo esc_attr( $id ); ?>"><div class="editor"><?php echo wp_kses_post( get_field( 'faq_answer' ) ); ?></div></div>
						</li>
					<?php endwhile; ?>
				</ul>
			<?php else : ?>
				<?php if ( ! empty( $faq_section_args['fallback_faq_items'] ) && is_array( $faq_section_args['fallback_faq_items'] ) ) : ?>
					<ul class="<?php echo esc_attr( $accordion_class ); ?>" data-default="<?php echo esc_attr( $accordion_default ); ?>" data-single="<?php echo esc_attr( $accordion_single ); ?>" data-breakpoint="<?php echo esc_attr( $accordion_breakpoint ); ?>" data-accordion-init>
						<?php foreach ( $faq_section_args['fallback_faq_items'] as $index => $item ) : ?>
							<?php
							$fallback_question = isset( $item['question'] ) ? (string) $item['question'] : '';
							$fallback_answer   = isset( $item['answer'] ) ? (string) $item['answer'] : '';
							$index_id         = 'faq-fallback-' . ( $index + 1 );
							?>
							<li class="accordion__item">
								<button class="accordion__btn h5" data-id="<?php echo esc_attr( $index_id ); ?>"><?php echo esc_html( $fallback_question ); ?></button>
								<div class="accordion__content" data-content="<?php echo esc_attr( $index_id ); ?>"><div class="editor"><p><?php echo esc_html( $fallback_answer ); ?></p></div></div>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php else : ?>
				<p><?php esc_html_e( 'Відповіді на поширені запитання незабаром з’являться тут.', 'logika-theme' ); ?></p>
				<?php endif; ?>
			<?php endif; wp_reset_postdata(); ?>
		</div>
	</div>
</section>
