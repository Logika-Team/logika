<?php

declare(strict_types=1);

final class Logika_Theme_Page_Content {
	private const TEXT_FIELDS = array(
		'about' => array(
			'about_hero_title' => 'Найбільша в Україні школа програмування для дітей 7-17 років',
			'about_stats_title' => 'Навчайтеся в найбільшій школі в Україні',
			'about_outcomes_title' => 'Що отримає ваша дитина,<br>навчаючись у школі “Logika”',
			'about_history_title' => 'Logika – успішний освітній<br>проєкт з 2018 року',
		),
		'it-courses' => array(
			'it_courses_hero_title' => 'Найбільша в Україні школа програмування для дітей 7-17 років',
			'it_courses_catalog_title' => 'Курси програмування для дітей 7-17 років',
			'it_courses_categories_title' => 'Курси для дітей 7-8 років',
		),
		'en-courses' => array(
			'english_courses_hero_title' => 'Школа англійської мови для дітей 7-17 років',
			'english_courses_catalog_title' => 'Наші курси',
			'english_courses_test_title' => 'Пройти тестування і підібрати для вашої дитини найкращу групу',
		),
		'faq' => array(
			'faq_page_hero_title' => 'Часті запитання про навчання в Logika',
			'faq_page_list_title' => 'Найпоширеніші питання',
			'faq_page_reviews_title' => 'Довіра, підтверджена результатами',
		),
		'media-center' => array(
			'media_center_hero_title' => 'Logika Медіа-центр',
			'media_center_benefits_title' => 'Чому тисячі батьків обирають Logika',
			'media_center_news_title' => 'Новини',
			'media_center_articles_title' => 'Корисні статті',
		),
	);

	private const IMAGE_FIELDS = array(
		'about'        => array( 'about_hero_image' => 'img/about/hero-characters.png' ),
		'it-courses'   => array( 'it_courses_hero_image' => 'img/boy-character.svg' ),
		'en-courses'   => array( 'english_courses_hero_image' => 'img/en-courses/en-courses.svg' ),
		'faq'          => array( 'faq_page_hero_image' => 'img/faq/faq-left-bg.svg' ),
		'media-center' => array( 'media_center_hero_image' => 'img/media-promo.svg' ),
	);

	public static function apply( string $markup, string $source ): string {
		$page_id = self::pageId( $source );

		if ( ! $page_id || ! function_exists( 'get_field' ) ) {
			return $markup;
		}

		foreach ( self::TEXT_FIELDS[ $source ] ?? array() as $field => $default ) {
			$value = trim( (string) get_field( $field, $page_id ) );

			if ( '' !== $value ) {
				$markup = str_replace( $default, esc_html( $value ), $markup );
			}
		}

		foreach ( self::IMAGE_FIELDS[ $source ] ?? array() as $field => $default ) {
			$image = get_field( $field, $page_id );
			$url   = is_numeric( $image ) ? wp_get_attachment_image_url( (int) $image, 'full' ) : '';

			if ( $url ) {
				$markup = str_replace( $default, esc_url( $url ), $markup );
			}
		}

		$markup = self::applySelectedContent( $markup, $source, $page_id );

		return $markup;
	}

	private static function applySelectedContent( string $markup, string $source, int $page_id ): string {
		$course_field = array( 'it-courses' => 'it_courses_featured_courses', 'en-courses' => 'english_courses_featured_courses' )[ $source ] ?? '';
		if ( $course_field ) {
			$courses = self::published( (array) get_field( $course_field, $page_id ), 'course' );
			if ( $courses ) {
				$items  = array_map( static fn( int $id ): string => self::courseCard( $id, $source ), $courses );
				$class  = 'it-courses' === $source ? 'courses-section__items' : 'en-courses-section__items';
				$markup = (string) preg_replace( '#(<ul class=[\'\"]' . $class . '[\'\"][^>]*>).*?(</ul>)#s', '$1' . implode( '', $items ) . '$2', $markup, 1 );
			}
		}

		$faq_field = array( 'faq' => 'faq_page_featured_faq', 'it-courses' => 'it_courses_featured_faq', 'en-courses' => 'english_courses_featured_faq', 'about' => 'about_featured_faq' )[ $source ] ?? '';
		if ( $faq_field ) {
			$faqs = self::published( (array) get_field( $faq_field, $page_id ), 'faq_item' );
			if ( $faqs ) {
				$items  = array_map( static fn( int $id ): string => self::faqItem( $id ), $faqs );
				$markup = (string) preg_replace( '#(<ul class=[\'\"]accordion[\'\"][^>]*>).*?(</ul>)#s', '$1' . implode( '', $items ) . '$2', $markup, 1 );
			}
		}

		return $markup;
	}

	/** @return int[] */
	private static function published( array $ids, string $post_type ): array {
		return array_values( array_filter( array_map( 'absint', $ids ), static fn( int $id ): bool => $id && $post_type === get_post_type( $id ) && 'publish' === get_post_status( $id ) ) );
	}

	private static function courseCard( int $id, string $source ): string {
		$title = get_the_title( $id );
		$text  = (string) get_field( 'course_short_description', $id );
		$image = wp_get_attachment_image_url( (int) get_field( 'course_card_image', $id ), 'large' );
		$url   = get_permalink( $id );

		if ( 'it-courses' === $source ) {
			return '<li class="courses-section__item"><div class="courses-section__item-media"><div class="courses-section__item-image">' . ( $image ? '<img src="' . esc_url( $image ) . '" alt="' . esc_attr( $title ) . '">' : '' ) . '</div></div><div class="courses-section__item-ages">' . esc_html( $title ) . '</div><a href="' . esc_url( $url ) . '" class="courses-section__item-link btn btn--white">Переглянути курс</a></li>';
		}

		return '<li class="en-courses-section__item"><div class="english-level"><div class="english-level__image">' . ( $image ? '<img src="' . esc_url( $image ) . '" alt="' . esc_attr( $title ) . '">' : '' ) . '</div><div class="english-level__info"><span class="h4">' . esc_html( $title ) . '</span><p>' . esc_html( $text ) . '</p></div><a href="' . esc_url( $url ) . '" class="english-level__link btn btn--green">Обрати курс</a></div></li>';
	}

	private static function faqItem( int $id ): string {
		$question = (string) get_field( 'faq_question', $id );
		$answer   = (string) get_field( 'faq_answer', $id );

		return '<li class="accordion__item"><button class="accordion__btn h5" data-id="' . esc_attr( (string) $id ) . '">' . esc_html( $question ) . '</button><div class="accordion__content" data-content="' . esc_attr( (string) $id ) . '"><div class="editor">' . wp_kses_post( $answer ) . '</div></div></li>';
	}

	private static function pageId( string $source ): int {
		$queried_id = get_queried_object_id();

		if ( $queried_id ) {
			return (int) $queried_id;
		}

		$page = get_page_by_path( 'en-courses' === $source ? 'english-courses' : $source );

		return $page ? (int) $page->ID : 0;
	}
}
