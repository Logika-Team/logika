<?php

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

final class Logika_Theme_Article_Page {
	public static function render( int $post_id = 0 ): string {
		$post_id = $post_id ?: get_queried_object_id();

		if ( $post_id <= 0 || 'post' !== get_post_type( $post_id ) ) {
			return '';
		}

		list( $content, $headings ) = self::content( $post_id );
		$cover   = self::image( self::field( 'article_cover_image', $post_id ) ?: self::field( 'global_fallback_image', 'option' ), 'article-section__cover-image' );
		$tags    = get_the_tags( $post_id );
		$author  = trim( (string) self::field( 'post_author_expert', $post_id ) );
		$role    = trim( (string) self::field( 'article_author_role', $post_id ) );
		$photo   = self::image( self::field( 'article_author_image', $post_id ), 'article-section__author-photo' );
		$minutes = (int) self::field( 'article_reading_minutes', $post_id );
		$minutes = $minutes > 0 ? $minutes : max( 1, (int) ceil( str_word_count( wp_strip_all_tags( $content ) ) / 180 ) );
		$views   = (int) self::field( 'article_view_count', $post_id );

		ob_start();
		?>
		<section class="article-section"><div class="top-block"><div class="container"><form class="search-form" action="<?php echo esc_url( home_url( '/media-center/' ) ); ?>" method="get"><label class="search-form__label"><input class="search-form__input" placeholder="<?php echo esc_attr( self::field( 'media_center_search_placeholder', 'option' ) ?: 'Пошук по статтях' ); ?>" name="s" type="search" autocomplete="off"><span class="search-form__icon"><svg width="20" height="20"><use href="<?php echo esc_url( self::asset( 'img/sprite/sprite.svg#icon-search' ) ); ?>"></use></svg></span></label><button class="search-form__btn" type="submit"><svg width="30" height="30"><use href="<?php echo esc_url( self::asset( 'img/sprite/sprite.svg#icon-search' ) ); ?>"></use></svg></button></form><?php self::topics(); ?></div></div><div class="container"><div class="article-section__wrapp"><div class="breadcrumbs"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Головна</a> / <a href="<?php echo esc_url( home_url( '/media-center/' ) ); ?>">Медіацентр</a> / <?php echo esc_html( get_the_title( $post_id ) ); ?></div><div class="article-section__box"><div class="article-section__hero"><div class="article-section__hero-left"><?php if ( $tags && ! is_wp_error( $tags ) ) : ?><ul class="article-section__tags"><?php foreach ( $tags as $tag ) : ?><li><?php echo esc_html( $tag->name ); ?></li><?php endforeach; ?></ul><?php endif; ?><h1 class="article-section__title"><?php echo esc_html( get_the_title( $post_id ) ); ?></h1><?php if ( $author || $role || $photo ) : ?><div class="article-section__about"><div class="article-section__author"><?php if ( $photo ) : ?><div class="article-section__author-avatar"><?php echo $photo; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><?php endif; ?><div class="article-section__author-info"><p>Автор статті</p><?php if ( $author ) : ?><span><?php echo esc_html( $author ); ?></span><?php endif; ?><?php if ( $role ) : ?><small><?php echo esc_html( $role ); ?></small><?php endif; ?></div></div></div><?php endif; ?></div><div class="article-section__hero-right"><?php if ( $cover ) : ?><div class="article-section__thumbnail"><?php echo $cover; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><?php endif; ?><ul class="article-section__details"><li><svg width="25" height="25"><use href="<?php echo esc_url( self::asset( 'img/sprite/sprite.svg#icon-calendar' ) ); ?>"></use></svg><p><?php echo esc_html( get_the_date( 'd.m.Y', $post_id ) ); ?></p></li><li><svg width="25" height="25"><use href="<?php echo esc_url( self::asset( 'img/sprite/sprite.svg#icon-readtime' ) ); ?>"></use></svg><p><?php echo esc_html( $minutes . ' хв' ); ?></p></li><?php if ( $views > 0 ) : ?><li><svg width="25" height="25"><use href="<?php echo esc_url( self::asset( 'img/sprite/sprite.svg#icon-eye' ) ); ?>"></use></svg><p><?php echo esc_html( (string) $views ); ?></p></li><?php endif; ?></ul></div></div><div class="article-section__content"><?php self::toc( $headings ); ?><div class="article-section__editor"><?php $summary = self::field( 'post_answer_first_summary', $post_id ); if ( $summary ) : ?><p class="article-section__summary"><?php echo esc_html( $summary ); ?></p><?php endif; ?><?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><?php self::sidebar( $post_id ); ?></div></div></div></div></section>
		<?php self::related( $post_id ); self::cta( $post_id ); self::faq( $post_id );

		return (string) ob_get_clean();
	}

	private static function content( int $post_id ): array {
		$content = apply_filters( 'the_content', (string) get_post_field( 'post_content', $post_id ) );
		$headings = array();
		$used = array();
		$content = (string) preg_replace_callback( '#<h([23])([^>]*)>(.*?)</h\\1>#is', static function ( array $matches ) use ( &$headings, &$used ): string {
			$label = trim( wp_strip_all_tags( $matches[3] ) );
			$base = sanitize_title( $label ) ?: 'section';
			$id = $base;
			$number = 2;
			while ( isset( $used[ $id ] ) ) {
				$id = $base . '-' . $number++;
			}
			$used[ $id ] = true;
			$headings[] = array( 'id' => $id, 'label' => $label, 'level' => (int) $matches[1] );
			$attributes = (string) preg_replace( '/\s+id=("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $matches[2] );

			return '<h' . $matches[1] . $attributes . ' id="' . esc_attr( $id ) . '">' . $matches[3] . '</h' . $matches[1] . '>';
		}, wp_kses_post( $content ) );

		return array( $content, $headings );
	}

	private static function topics(): void {
		$topics = (array) self::field( 'media_center_topics', 'option' );
		if ( ! $topics ) {
			return;
		}
		?> <ul class="tags"><?php foreach ( $topics as $topic ) : if ( empty( $topic['label'] ) || empty( $topic['url'] ) ) { continue; } ?><li><a href="<?php echo esc_url( $topic['url'] ); ?>"><?php echo esc_html( $topic['label'] ); ?></a></li><?php endforeach; ?></ul><?php
	}

	private static function toc( array $headings ): void {
		if ( ! $headings ) { return; }
		?><nav class="article-section__headings" aria-label="Зміст статті"><span class="h4"><svg width="36" height="36"><use href="<?php echo esc_url( self::asset( 'img/sprite/sprite.svg#icon-readme' ) ); ?>"></use></svg>Що в статті</span><ul><?php foreach ( $headings as $heading ) : ?><li class="article-section__heading--<?php echo esc_attr( (string) $heading['level'] ); ?>"><a href="#<?php echo esc_attr( $heading['id'] ); ?>"><?php echo esc_html( $heading['label'] ); ?></a></li><?php endforeach; ?></ul></nav><?php
	}

	private static function sidebar( int $post_id ): void {
		$enabled = (bool) self::field( 'article_sidebar_enabled', $post_id );
		$courses = self::published( (array) self::field( 'article_popular_courses', $post_id ), 'course', 4 );
		if ( ! $enabled && ! $courses ) { return; }
		?> <aside class="article-section__aside"><?php if ( $enabled && ( self::field( 'article_promo_title', $post_id ) || self::field( 'article_promo_description', $post_id ) ) ) : ?><div class="article-section__promo"><?php $background = self::image( self::field( 'article_promo_background', $post_id ), 'article-section__promo-background' ); if ( $background ) : ?><div class="article-section__promo-bg"><?php echo $background; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><?php endif; ?><div class="article-section__promo-title h4"><?php echo esc_html( self::field( 'article_promo_title', $post_id ) ); ?></div><p class="article-section__promo-description"><?php echo esc_html( self::field( 'article_promo_description', $post_id ) ); ?></p><?php $image = self::image( self::field( 'article_promo_image', $post_id ), 'article-section__promo-image' ); if ( $image ) { echo $image; } $link = self::field( 'article_promo_link', $post_id ); if ( is_array( $link ) && ! empty( $link['url'] ) ) : ?><a class="article-section__promo-btn btn btn--yellow" href="<?php echo esc_url( $link['url'] ); ?>"<?php echo ! empty( $link['target'] ) ? ' target="' . esc_attr( $link['target'] ) . '"' : ''; ?>><?php echo esc_html( $link['title'] ?: 'Дізнатися більше' ); ?></a><?php endif; ?></div><?php endif; ?><?php if ( $courses ) : ?><div class="article-section__populars"><div class="article-section__populars-title h4"><?php echo esc_html( self::field( 'article_popular_courses_title', $post_id ) ?: 'Популярні курси' ); ?></div><ul class="article-section__populars-courses"><?php foreach ( $courses as $course ) : ?><li><a href="<?php echo esc_url( get_permalink( $course ) ); ?>"><?php echo esc_html( get_the_title( $course ) ); ?></a></li><?php endforeach; ?></ul></div><?php endif; ?></aside><?php
	}

	private static function related( int $post_id ): void {
		$posts = self::published( (array) self::field( 'article_related_posts', $post_id ), 'post', 3 );
		if ( ! $posts ) { return; }
		?><section class="related-section"><div class="container"><div class="related-section__wrapp"><h2 class="related-section__title h2"><?php echo esc_html( self::field( 'article_related_heading', $post_id ) ?: 'Читайте також' ); ?></h2><ul class="related-section__items"><?php foreach ( $posts as $related ) : ?><li class="related-section__item"><article class="article-card"><?php $image = self::image( self::field( 'article_cover_image', $related->ID ), 'article-card__image' ); if ( $image ) : ?><div class="article-card__thumbnail"><?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><?php endif; ?><div class="article-card__info"><a class="article-card__title" href="<?php echo esc_url( get_permalink( $related ) ); ?>"><?php echo esc_html( get_the_title( $related ) ); ?></a><?php if ( has_excerpt( $related ) ) : ?><p class="article-card__excerpt"><?php echo esc_html( get_the_excerpt( $related ) ); ?></p><?php endif; ?></div></article></li><?php endforeach; ?></ul></div></div></section><?php
	}

	private static function cta( int $post_id ): void {
		if ( ! self::field( 'article_cta_enabled', $post_id ) || ! self::field( 'article_cta_title', $post_id ) ) { return; }
		?><section class="cta-section"><div class="container"><div class="cta-section__wrapp"><div class="cta-section__left"><form class="cta-form" data-logika-lead-form novalidate><div class="cta-form__top"><h2 class="cta-form__title h3"><?php echo esc_html( self::field( 'article_cta_title', $post_id ) ); ?></h2><?php if ( self::field( 'article_cta_subtitle', $post_id ) ) : ?><p class="cta-form__subtitle h4"><?php echo esc_html( self::field( 'article_cta_subtitle', $post_id ) ); ?></p><?php endif; ?></div><div class="cta-form__inputs"><input class="main-form__input" type="text" name="name" placeholder="Ім’я" required><div class="main-form__phone-wrap"><input class="main-form__input main-form__phone" type="tel" name="tel" placeholder="Номер телефону" data-logika-phone-input required></div><?php echo Logika_Theme_Lead_Form::render_city_select(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo Logika_Theme_Lead_Form::render_age_select(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><div class="cta-form__bottom"><button class="cta-form__btn btn btn--yellow" type="submit"><?php echo esc_html( self::field( 'article_cta_button_label', $post_id ) ?: 'Отримати консультацію' ); ?></button><p class="cta-form__text"><?php echo esc_html( self::field( 'article_cta_consent', $post_id ) ?: 'Натискаючи, ви погоджуєтесь із' ); ?> <a href="<?php echo esc_url( self::field( 'global_privacy_policy_url', 'option' ) ); ?>">Політикою конфіденційності</a></p><input type="hidden" name="form_id" value="consultation"><input type="hidden" name="consent_accepted" value="1"><input type="hidden" name="consent_text_version" value="v1"><input type="hidden" name="idempotency_key" value=""><input class="main-form__honeypot" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true"><p class="main-form__status" aria-live="polite" hidden></p></div></form></div><div class="cta-section__right"><div class="cta-section__image"><?php echo self::image( self::field( 'article_cta_image', $post_id ), 'article-cta-image' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><div class="cta-section__character-logika"><?php echo self::image( self::field( 'article_cta_character_image', $post_id ), 'article-cta-character' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div></div><div class="cta-section__top-bg"><img src="<?php echo esc_url( self::asset( 'img/cta/cta-top-bg.svg' ) ); ?>" alt=""></div><div class="cta-section__bottom-bg"><img src="<?php echo esc_url( self::asset( 'img/cta/cta-bottom-bg.svg' ) ); ?>" alt=""></div></div></div></section><?php
	}

	private static function faq( int $post_id ): void {
		$items = (array) self::field( 'article_faq_items', $post_id );
		if ( ! self::field( 'article_faq_enabled', $post_id ) || ! $items ) { return; }
		?><section class="faq-section"><div class="faq-section__left-bg"><img src="<?php echo esc_url( self::asset( 'img/faq/faq-left-bg.svg' ) ); ?>" alt=""></div><div class="faq-section__right-bg"><img src="<?php echo esc_url( self::asset( 'img/faq/faq-right-bg.svg' ) ); ?>" alt=""></div><div class="container"><div class="faq-section__wrapp"><h2><?php echo esc_html( self::field( 'article_faq_title', $post_id ) ?: 'Питання та відповіді' ); ?></h2><ul class="accordion" data-single="true" data-accordion-init><?php foreach ( $items as $index => $item ) : if ( empty( $item['question'] ) || empty( $item['answer'] ) ) { continue; } $id = 'article-faq-' . $post_id . '-' . $index; ?><li class="accordion__item"><button class="accordion__btn h5" data-id="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $item['question'] ); ?></button><div class="accordion__content" data-content="<?php echo esc_attr( $id ); ?>"><div class="editor"><?php echo wp_kses_post( $item['answer'] ); ?></div></div></li><?php endforeach; ?></ul></div></div></section><?php
	}

	private static function published( array $ids, string $type, int $limit ): array {
		$posts = array();
		foreach ( $ids as $id ) {
			$post = get_post( (int) $id );
			if ( $post instanceof WP_Post && $type === $post->post_type && 'publish' === $post->post_status ) { $posts[] = $post; }
			if ( count( $posts ) >= $limit ) { break; }
		}

		return $posts;
	}

	private static function field( string $name, int|string $post_id = 0 ): mixed { return function_exists( 'get_field' ) ? get_field( $name, $post_id ) : null; }
	private static function asset( string $path ): string { return get_template_directory_uri() . '/assets/' . ltrim( $path, '/' ); }
	private static function image( mixed $id, string $class ): string { return $id ? (string) wp_get_attachment_image( (int) $id, 'full', false, array( 'class' => $class, 'loading' => 'lazy' ) ) : ''; }
}
