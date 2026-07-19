<?php

declare(strict_types=1);

final class Logika_Theme_Course_Page {
	public static function render( int $course_id ): string {
		$min_age = (int) get_field( 'course_age_min', $course_id );
		$max_age = (int) get_field( 'course_age_max', $course_id );
		$ages    = $min_age && $max_age ? "{$min_age}-{$max_age} років" : '';
		$terms   = get_the_terms( $course_id, 'course_direction' );
		$formats = get_the_terms( $course_id, 'learning_format' );
		$faq_ids = (array) get_field( 'course_related_faq', $course_id );
		if ( ! $faq_ids ) {
			$faq_ids = get_posts( array( 'post_type' => 'faq_item', 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => 'faq_related_course', 'meta_value' => $course_id ) );
		}

		ob_start();
		?>
		<main>
		<section class="course-banner-section"><div class="container"><div class="course-banner-section__blocks"><div class="course-banner-section__left"><?php if ( get_field( 'course_hero_label', $course_id ) ) : ?><span class="course-banner-section__label"><?php echo esc_html( get_field( 'course_hero_label', $course_id ) ); ?></span><?php endif; ?><div class="course-banner-section__info"><h1><?php echo esc_html( get_the_title( $course_id ) ); ?></h1><?php if ( $ages ) : ?><p class="h4"><?php echo esc_html( $ages ); ?></p><?php endif; ?><?php if ( $terms && ! is_wp_error( $terms ) ) : ?><p><?php echo esc_html( implode( ' · ', wp_list_pluck( $terms, 'name' ) ) ); ?></p><?php endif; ?><?php if ( $formats && ! is_wp_error( $formats ) ) : ?><p><?php echo esc_html( implode( ' · ', wp_list_pluck( $formats, 'name' ) ) ); ?></p><?php endif; ?><p><?php echo esc_html( get_field( 'course_hero_text', $course_id ) ?: get_field( 'course_short_description', $course_id ) ); ?></p></div><a class="btn btn--yellow" href="#lead-form"><?php echo esc_html( get_field( 'course_cta_label', $course_id ) ?: 'Записатися на безкоштовний урок' ); ?></a></div><?php if ( get_field( 'course_hero_image', $course_id ) ) : ?><div class="course-banner-section__right"><div class="course-banner-section__media"><?php echo wp_get_attachment_image( (int) get_field( 'course_hero_image', $course_id ), 'large' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div></div><?php endif; ?></div></div></section>
		<?php
		get_template_part( 'template-parts/sections/repeatable-cards', null, array( 'class' => 'learn-section', 'title' => get_field( 'course_learn_title', $course_id ), 'items' => get_field( 'course_learn_items', $course_id ) ) );
		self::program( $course_id );
		get_template_part( 'template-parts/sections/repeatable-cards', null, array( 'class' => 'portfolio-section', 'title' => get_field( 'course_portfolio_title', $course_id ), 'items' => self::projects( $course_id ) ) );
		get_template_part( 'template-parts/sections/local-faq', null, array( 'title' => get_field( 'course_faq_title', $course_id ), 'items' => get_field( 'course_faq_items', $course_id ), 'id_prefix' => 'course-local-' . $course_id ) );
		get_template_part( 'template-parts/sections/reviews', null, array( 'title' => get_field( 'course_reviews_title', $course_id ), 'items' => (array) get_field( 'course_related_reviews', $course_id ) ?: null ) );
		get_template_part( 'template-parts/sections/school-map', null, array( 'title' => get_field( 'course_map_title', $course_id ) ?: 'Знайдіть свою школу або<br>навчайтесь онлайн', 'text' => get_field( 'course_map_text', $course_id ) ?: 'Наші школи у 130 містах України — знайдіть зручний варіант поруч із вами або навчайтесь онлайн.', 'course_id' => $course_id ) );
		get_template_part( 'template-parts/sections/cta', null, array( 'section_id' => 'lead-form', 'title' => get_field( 'course_cta_title', $course_id ) ?: 'Підберемо зручний формат навчання', 'subtitle' => get_field( 'course_cta_subtitle', $course_id ) ?: get_field( 'course_cta_text', $course_id ), 'image' => get_field( 'course_cta_image', $course_id ), 'button_label' => get_field( 'course_cta_label', $course_id ), 'course_id' => $course_id ) );
		$faq_args = array( 'section_title' => get_field( 'course_general_faq_title', $course_id ) ?: 'Питання та відповіді' );
		if ( $faq_ids ) {
			$faq_args['items'] = $faq_ids;
		}
		get_template_part( 'template-parts/sections/faq', null, $faq_args );
		echo '</main>';

		return (string) ob_get_clean();
	}

	private static function program( int $course_id ): void {
		$program = (array) get_field( 'course_program', $course_id );
		if ( ! $program ) {
			return;
		}
		?><section class="process-section"><div class="container"><div class="process-section__wrapp"><h2><?php echo esc_html( get_field( 'course_process_title', $course_id ) ?: 'Програма курсу' ); ?></h2><ul class="process-section__items"><?php foreach ( $program as $module ) : ?><li class="process-section__item"><div class="process-section__item-content"><h3><?php echo esc_html( $module['title'] ?? '' ); ?></h3><div class="editor"><?php echo wp_kses_post( (string) ( $module['description'] ?? '' ) ); ?></div><?php if ( ! empty( $module['items'] ) ) : ?><ul><?php foreach ( $module['items'] as $item ) : ?><li><?php echo esc_html( $item['item_text'] ?? '' ); ?></li><?php endforeach; ?></ul><?php endif; ?></div></li><?php endforeach; ?></ul></div></div></section><?php
	}

	private static function projects( int $course_id ): array {
		return array_map(
			static fn( array $project ): array => array( 'title' => $project['project_title'] ?? '', 'text' => $project['project_description'] ?? '', 'image' => $project['project_image'] ?? 0 ),
			(array) get_field( 'course_projects', $course_id )
		);
	}
}
