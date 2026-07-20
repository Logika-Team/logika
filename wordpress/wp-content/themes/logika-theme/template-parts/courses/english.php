<?php

declare(strict_types=1);

$course_id = absint( $args['course_id'] ?? 0 );
if ( ! $course_id ) {
	return;
}

$field = static fn( string $name, mixed $fallback = '' ): mixed => get_field( $name, $course_id ) ?: $fallback;
$image_id = static fn( mixed $value ): int => is_array( $value ) ? (int) ( $value['ID'] ?? 0 ) : (int) $value;
$attachment_image = static function ( int $attachment_id, string $loading = 'lazy' ): string {
	$image = wp_get_attachment_image( $attachment_id, 'large', false, array( 'loading' => $loading ) );
	if ( $image ) {
		return $image;
	}

	$url = wp_get_attachment_url( $attachment_id );
	return $url ? sprintf( '<img src="%s" alt="%s" loading="%s">', esc_url( $url ), esc_attr( (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ), esc_attr( $loading ) ) : '';
};
$variant = sanitize_html_class( (string) $field( 'course_visual_variant', 'a0' ) );
$variant = in_array( $variant, array( 'a0', 'a1', 'a2', 'b1', 'b2', 'b2-1' ), true ) ? $variant : 'a0';
$character_assets = array( 'a2' => 'a2-eye.png', 'b1' => 'b1-reader.png' );
$character_url    = $character_assets[ $variant ] ?? '';
$character_image  = $character_url ? sprintf( '<img src="%s" alt="%s" loading="eager">', esc_url( get_template_directory_uri() . '/assets/img/english-levels/characters/' . $character_url ), esc_attr( get_the_title( $course_id ) ) ) : '';
$character_outcomes_image = $character_url ? sprintf( '<img src="%s" alt="%s" loading="lazy">', esc_url( get_template_directory_uri() . '/assets/img/english-levels/characters/' . $character_url ), esc_attr( get_the_title( $course_id ) ) ) : '';
$image = $image_id( $field( 'course_hero_image', 0 ) );
$hero_image = 'b2-1' === $variant
	? sprintf( '<img src="%s" alt="%s" loading="eager">', esc_url( get_template_directory_uri() . '/assets/img/english-levels/characters/b2-1-reader.png' ), esc_attr( get_the_title( $course_id ) ) )
	: ( $character_image ?: ( $image ? $attachment_image( $image, 'eager' ) : '' ) );
$approach_image = $image_id( $field( 'course_english_approach_image', 0 ) );
$outcomes_image = $image_id( $field( 'course_english_outcomes_image', 0 ) );
$outcomes_markup = $character_outcomes_image ?: ( $outcomes_image ? $attachment_image( $outcomes_image ) : '' );
$benefits = array_filter( array_filter( (array) $field( 'course_hero_benefits', array() ), 'is_array' ), static fn( array $item ): bool => ! empty( $item['text'] ) );
$advantages = array_filter( array_filter( (array) $field( 'course_english_advantages', array() ), 'is_array' ), static fn( array $item ): bool => ! empty( $item['title'] ) || ! empty( $item['text'] ) );
$outcomes = array_filter( array_filter( (array) $field( 'course_english_outcomes', array() ), 'is_array' ), static fn( array $item ): bool => ! empty( $item['text'] ) );
$format = array_filter( array_filter( (array) $field( 'course_english_lesson_format', array() ), 'is_array' ), static fn( array $item ): bool => ! empty( $item['title'] ) || ! empty( $item['text'] ) );
$skills = array_filter( array_filter( (array) $field( 'course_english_skills', array() ), 'is_array' ), static fn( array $item ): bool => ! empty( $item['text'] ) );
$program = array_filter( array_filter( (array) $field( 'course_program', array() ), 'is_array' ), static fn( array $item ): bool => ! empty( $item['title'] ) );
$faq = array_filter( array_filter( (array) $field( 'course_faq_items', array() ), 'is_array' ), static fn( array $item ): bool => ! empty( $item['question'] ) && ! empty( $item['answer'] ) );
$label = (string) $field( 'course_hero_label', 'Курс англійської мови' );
$description = (string) $field( 'course_hero_text', get_the_excerpt( $course_id ) );
$cta_label = (string) $field( 'course_hero_cta_label', 'Записатися на безкоштовний урок' );
$catalog_courses = get_posts(
	array(
		'post_type'      => 'course',
		'post_status'    => 'publish',
		'post_name__in'  => array( 'english-a0', 'english-a1', 'english-a2', 'english-b1', 'english-b2', 'english-b2-1' ),
		'post__not_in'   => array( $course_id ),
		'orderby'        => 'post_name__in',
		'posts_per_page' => 6,
	)
);
?>
<?php echo Logika_Theme_Source_Markup::breadcrumbs( array( array( 'label' => 'Головна', 'url' => '/' ), array( 'label' => 'Курси', 'url' => '/english-courses/' ), array( 'label' => get_the_title( $course_id ) ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
<main class="english-course-page english-course-page--<?php echo esc_attr( $variant ); ?>">
	<section class="english-course-hero"><div class="container"><div class="english-course-hero__grid"><div class="english-course-hero__content"><p class="english-course-hero__eyebrow"><?php echo esc_html( $label ); ?></p><h1><?php echo esc_html( get_the_title( $course_id ) ); ?></h1><?php if ( $description ) : ?><p class="english-course-hero__text"><?php echo esc_html( $description ); ?></p><?php endif; ?><?php if ( $benefits ) : ?><ul class="english-course-hero__benefits"><?php foreach ( $benefits as $benefit ) : ?><li><?php echo esc_html( (string) $benefit['text'] ); ?></li><?php endforeach; ?></ul><?php endif; ?><a class="btn btn--yellow english-course-hero__cta" href="#lead-form" data-logika-course-id="<?php echo esc_attr( (string) $course_id ); ?>"><?php echo esc_html( $cta_label ); ?></a></div><?php if ( $hero_image ) : ?><div class="english-course-hero__media"><?php echo $hero_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><?php endif; ?></div></div></section>

	<?php if ( $advantages ) : ?><section class="english-course-section english-course-advantages"><div class="container english-course-story"><div class="english-course-story__content"><div class="english-course-heading"><p>Logika English</p><h2>Англійська, якою хочеться говорити</h2></div><div class="english-course-cards"><?php foreach ( $advantages as $item ) : ?><article class="english-course-card"><h3><?php echo esc_html( (string) $item['title'] ); ?></h3><p><?php echo esc_html( (string) $item['text'] ); ?></p></article><?php endforeach; ?></div></div><?php if ( $approach_image ) : ?><figure class="english-course-story__media"><?php echo $attachment_image( $approach_image ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></figure><?php endif; ?></div></section><?php endif; ?>

	<?php if ( $outcomes ) : ?><section class="english-course-section english-course-outcomes"><div class="container english-course-journey"><?php if ( $outcomes_markup ) : ?><figure class="english-course-journey__media"><?php echo $outcomes_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></figure><?php endif; ?><div class="english-course-journey__content"><div class="english-course-heading"><p>Результат курсу</p><h2><?php echo esc_html( (string) $field( 'course_learn_title', 'Ваша дитина зможе' ) ); ?></h2></div><ol class="english-course-outcomes__list"><?php foreach ( $outcomes as $index => $item ) : ?><li><span><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span><?php echo esc_html( (string) $item['text'] ); ?></li><?php endforeach; ?></ol></div></div></section><?php endif; ?>

	<?php if ( $format ) : ?><section class="english-course-section english-course-format"><div class="container"><div class="english-course-heading"><p>Формат навчання</p><h2><?php echo esc_html( (string) $field( 'course_process_title', 'На заняттях багато практики' ) ); ?></h2></div><div class="english-course-format__grid"><?php foreach ( $format as $index => $item ) : ?><article><span class="english-course-format__number"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span><h3><?php echo esc_html( (string) $item['title'] ); ?></h3><p><?php echo esc_html( (string) $item['text'] ); ?></p></article><?php endforeach; ?></div></div></section><?php endif; ?>

	<?php if ( $skills ) : ?><section class="english-course-section english-course-skills"><div class="container"><div class="english-course-heading"><p>Більше, ніж мова</p><h2><?php echo esc_html( (string) $field( 'course_portfolio_title', 'Розвиваємо важливі навички' ) ); ?></h2></div><ul><?php foreach ( $skills as $index => $item ) : ?><li><span><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span><?php echo esc_html( (string) $item['text'] ); ?></li><?php endforeach; ?></ul></div></section><?php endif; ?>

	<?php if ( $program ) : ?><section class="english-course-section english-course-program" id="course-program"><div class="container"><div class="english-course-heading"><p>Програма</p><h2>Що вивчатиме дитина</h2></div><ul class="accordion accordion--mode english-course-program__items" data-single="true" data-accordion-init><?php foreach ( $program as $index => $module ) : $module_id = 'english-program-' . $course_id . '-' . $index; ?><li class="accordion__item"><button class="accordion__btn" data-id="<?php echo esc_attr( $module_id ); ?>"><?php echo esc_html( (string) $module['title'] ); ?></button><?php if ( ! empty( $module['items'] ) ) : ?><div class="accordion__content" data-content="<?php echo esc_attr( $module_id ); ?>"><ul><?php foreach ( (array) $module['items'] as $item ) : ?><li><?php echo esc_html( (string) ( $item['item_text'] ?? '' ) ); ?></li><?php endforeach; ?></ul></div><?php endif; ?></li><?php endforeach; ?></ul></div></section><?php endif; ?>

	<?php get_template_part( 'template-parts/sections/reviews', null, array( 'items' => (array) get_field( 'course_related_reviews', $course_id ) ?: null, 'context' => $course_id ) ); ?>
	<?php get_template_part( 'template-parts/sections/school-map', null, array( 'course_id' => $course_id ) ); ?>
	<?php get_template_part( 'template-parts/sections/cta', null, array( 'title' => 'Підберемо курс саме для вашої дитини!', 'subtitle' => 'Ми зателефонуємо в зручний час', 'image' => (int) $field( 'course_cta_image', $image ), 'button_label' => 'Отримати консультацію', 'course_id' => $course_id ) ); ?>
	<?php $catalog_character_assets = array( 'english-a0' => 'english-courses/A0.svg', 'english-a1' => 'english-courses/A1.svg', 'english-a2' => 'english-courses/A2.svg', 'english-b1' => 'english-courses/B1.svg', 'english-b2' => 'english-levels/characters/b2-megaphone.svg', 'english-b2-1' => 'english-levels/characters/b2-1-reader.png' ); ?>
<?php $show_catalog = false; // Temporarily hidden. ?>
<?php if ( $show_catalog && $catalog_courses ) : ?>
<section class="english-course-catalog">
	<div class="container">
		<div class="english-course-catalog__heading"><h2>Доступні курси</h2><img class="english-course-catalog__mascot" src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/english-course/catalog-mascot.png' ); ?>" alt="" aria-hidden="true"><p>Для дітей від 8 до 17 років</p></div>
		<ul class="english-course-catalog__grid">
			<?php foreach ( $catalog_courses as $catalog_course ) : ?>
				<?php
				$catalog_id        = (int) $catalog_course->ID;
				$catalog_image     = $image_id( get_field( 'course_card_image', $catalog_id ) ) ?: get_post_thumbnail_id( $catalog_id );
				$catalog_character = $catalog_character_assets[ $catalog_course->post_name ] ?? '';
				$age_min           = absint( get_field( 'course_age_min', $catalog_id ) );
				$age_max           = absint( get_field( 'course_age_max', $catalog_id ) );
				$ages              = $age_min ? $age_min . ( $age_max ? '-' . $age_max : '+' ) . ' років' : '';
				?>
				<li class="english-course-catalog__slide">
					<div class="english-level<?php echo $catalog_id === $course_id ? ' is-current' : ''; ?>">
						<div class="english-level__ages"><?php echo esc_html( $ages ); ?></div>
						<div class="english-level__image">
							<?php if ( $catalog_character ) : ?><img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/' . $catalog_character ); ?>" alt="<?php echo esc_attr( get_the_title( $catalog_id ) ); ?>"><?php elseif ( $catalog_image ) : ?><?php echo $attachment_image( $catalog_image ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php endif; ?>
						</div>
						<div class="english-level__info"><span class="h4"><?php echo esc_html( get_the_title( $catalog_id ) ); ?></span><?php if ( $age_min ) : ?><p><?php echo esc_html( $ages ); ?></p><?php endif; ?></div>
						<div class="english-level__actions"><a class="english-level__link btn btn--violet" href="#lead-form" data-logika-course-id="<?php echo esc_attr( (string) $catalog_id ); ?>">Записатись</a><a class="english-level__link english-level__link--secondary btn" href="<?php echo esc_url( get_permalink( $catalog_id ) ); ?>"<?php echo $catalog_id === $course_id ? ' aria-current="page"' : ''; ?>>Детальніше</a></div>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
<?php endif; ?>
	<?php if ( $faq ) : ?><?php get_template_part( 'template-parts/sections/local-faq', null, array( 'title' => (string) $field( 'course_general_faq_title', 'Питання та відповіді' ), 'items' => $faq, 'id_prefix' => 'english-faq-' . $course_id ) ); ?><?php endif; ?>
</main>
