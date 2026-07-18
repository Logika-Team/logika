<?php

declare(strict_types=1);

final class Logika_Theme_Page_Content {
	private const TEXT_FIELDS = array(
		'about' => array(
			'about_hero_title' => 'Найбільша в Україні школа програмування для дітей 7-17 років',
			'about_hero_text' => 'Перші результати вже через 4 тижні',
			'about_stats_title' => 'Навчайтеся в найбільшій школі в Україні',
			'about_outcomes_title' => 'Що отримає ваша дитина,<br>навчаючись у школі “Logika”',
			'about_history_title' => 'Logika – успішний освітній<br>проєкт з 2018 року',
			'about_benefits_title' => 'Чому тисячі батьків обирають Logika',
			'about_gallery_title' => 'Gallery',
			'about_media_title' => 'Чому тисячі батьків обирають Logika',
			'about_onboarding_title' => 'Як розпочати навчання',
			'about_map_title' => 'Знайдіть свою школу або<br>навчайтесь онлайн',
			'about_map_text' => 'Наші школи у 130 містах України - знайдіть зручний варіант поруч із вами або навчайтесь онлайн.',
			'about_cta_title' => 'Підберемо курс саме для вашої дитини!',
			'about_cta_subtitle' => 'Ми зателефонуємо в зручний час',
			'about_reviews_title' => 'Довіра, підтверджена результатами',
			'about_faq_title' => 'Питання та відповіді',
		),
		'it-courses' => array(
			'it_courses_hero_title' => 'Найбільша в Україні школа програмування для дітей 7-17 років',
			'it_courses_hero_text' => 'Перші результати вже через 4 тижні',
			'it_courses_catalog_title' => 'Курси програмування для дітей 7-17 років',
			'it_courses_categories_title' => 'Курси для дітей 7-8 років',
			'it_courses_reviews_title' => 'Довіра, підтверджена результатами',
			'it_courses_map_title' => 'Знайдіть свою школу або<br>навчайтесь онлайн',
			'it_courses_map_text' => 'Наші школи у 130 містах України - знайдіть зручний варіант поруч із вами або навчайтесь онлайн.',
			'it_courses_cta_title' => 'Підберемо курс саме для вашої дитини!',
			'it_courses_cta_subtitle' => 'Ми зателефонуємо в зручний час',
			'it_courses_faq_title' => 'Питання та відповіді',
		),
		'en-courses' => array(
			'english_courses_hero_title' => 'Школа англійської мови для дітей 7-17 років',
			'english_courses_hero_text' => 'Перші результати вже через 4 тижні',
			'english_courses_catalog_title' => 'Наші курси',
			'english_courses_test_title' => 'Пройти тестування і підібрати для вашої дитини найкращу групу',
			'english_courses_test_text' => 'Допоможемо визначити рівень підготовки, інтереси та формат навчання, щоб дитині було комфортно й цікаво навчатися з перших занять.',
			'english_courses_test_cta_label' => 'Пройти тестування',
			'english_courses_about_title' => 'В школі «Logika»:',
			'english_courses_reviews_title' => 'Довіра, підтверджена результатами',
			'english_courses_map_title' => 'Знайдіть свою школу або<br>навчайтесь онлайн',
			'english_courses_map_text' => 'Наші школи у 130 містах України - знайдіть зручний варіант поруч із вами або навчайтесь онлайн.',
			'english_courses_cta_title' => 'Підберемо курс саме для вашої дитини!',
			'english_courses_cta_subtitle' => 'Ми зателефонуємо в зручний час',
			'english_courses_faq_title' => 'Питання та відповіді',
		),
		'faq' => array(
			'faq_page_hero_title' => 'Часті запитання про навчання в Logika',
			'faq_page_hero_text' => 'Зібрали відповіді на найпоширеніші запитання батьків і дітей про курси, формати навчання, розклад, вартість, викладачів, проєкти, табори та можливості приєднатися до команди Logika.',
			'faq_page_list_title' => 'Найпоширеніші питання',
			'faq_page_reviews_title' => 'Довіра, підтверджена результатами',
			'faq_page_cta_title' => 'Підберемо курс саме для вашої дитини!',
			'faq_page_map_title' => 'Знайдіть свою школу або<br>навчайтесь онлайн',
			'faq_page_map_text' => 'Наші школи у 130 містах України - знайдіть зручний варіант поруч із вами або навчайтесь онлайн.',
			'faq_page_cta_subtitle' => 'Ми зателефонуємо в зручний час',
		),
		'media-center' => array(
			'media_center_hero_title' => 'Logika Медіа-центр',
			'media_center_benefits_title' => 'Чому тисячі батьків обирають Logika',
			'media_center_news_title' => 'Новини',
			'media_center_articles_title' => 'Корисні статті',
			'media_center_discount_title' => '-10%',
			'media_center_cta_title' => 'Підберемо курс саме для вашої дитини!',
			'media_center_cta_subtitle' => 'Ми зателефонуємо в зручний час',
			'media_center_faq_title' => 'Питання та відповіді',
		),
		'camps' => array(
			'camp_archive_hero_title' => 'Табори з Logika: подаруйте<br>дитині незабутні емоції',
			'camp_archive_hero_text' => 'Відпочиваємо та розвиваємося разом!',
			'camp_archive_benefits_title' => 'Табори з Logika - це:',
			'camp_archive_formats_title' => 'Оберіть свій формат',
			'camp_archive_booking_title' => 'Встигніть забронювати<br>незабутні спогади',
			'camp_archive_booking_text' => 'Залиште заявку — ми зателефонуємо і обговоримо всі деталі.',
			'camp_archive_history_title' => 'Як це було минулих років',
			'camp_archive_reviews_title' => 'Довіра, підтверджена результатами',
			'camp_archive_faq_title' => 'Питання та відповіді',
		),
		'camp' => array(
			'camp_hero_text' => 'Навчаємо дітей створювати ігри, сайти, застосунки та власні digital-проєкти через практику й сучасні технології.',
			'camp_benefits_title' => '10 днів цікавої програми та незабутніх<br>вражень від літніх канікул',
			'camp_activities_title' => 'Активності у програмі:',
			'camp_trips_title' => 'Виїзні екскурсії:',
			'camp_booking_title' => 'Встигніть забронювати<br>незабутні спогади',
			'camp_booking_text' => 'Залиште заявку — ми зателефонуємо та обговоримо всі деталі.',
			'camp_gallery_title' => 'Галерея',
			'camp_reviews_title' => 'Довіра, підтверджена результатами',
			'camp_faq_title' => 'Питання та відповіді',
		),
		'it-course' => array(
			'course_hero_text' => 'Якщо ви маєте творчий підхід до роботи, бажаєте розробляти і створювати динамічні інтерфейси, вам однозначно дорога у фронтенд.',
			'course_hero_additional_text' => 'Frontend – це публічна частина web-додатків (веб-сайтів), з якою користувач може взаємодіяти і контактувати напряму. По суті, фронтенд – це все те, що бачить користувач при відкритті web-сторінки.',
			'course_learn_title' => 'На курсі учні навчаються',
			'course_process_title' => 'Кожне заняття – теорія і практика',
			'course_portfolio_title' => 'Проекти наших учнів',
			'course_faq_title' => 'Програма курсу',
			'course_reviews_title' => 'Довіра, підтверджена результатами',
			'course_map_title' => 'Знайдіть свою школу або<br>навчайтесь онлайн',
			'course_map_text' => 'Наші школи у 130 містах України - знайдіть зручний варіант поруч із вами або навчайтесь онлайн.',
			'course_cta_title' => 'Підберемо курс саме для вашої дитини!',
			'course_cta_subtitle' => 'Ми зателефонуємо в зручний час',
			'course_general_faq_title' => 'Питання та відповіді',
		),
	);

	private const IMAGE_FIELDS = array(
		'about'        => array( 'about_hero_image' => 'img/about/hero-characters.png', 'about_history_image' => 'img/about/anniversary-full.png', 'about_cta_image' => 'img/cta/cta.png' ),
		'it-courses'   => array( 'it_courses_hero_image' => 'img/boy-character.svg', 'it_courses_cta_image' => 'img/cta/cta.png' ),
		'en-courses'   => array( 'english_courses_hero_image' => 'img/en-courses/en-courses.svg', 'english_courses_test_image' => 'img/en-courses/test-image.svg', 'english_courses_about_image' => 'img/en-courses/en-about-image.png', 'english_courses_cta_image' => 'img/cta/cta.png' ),
		'faq'          => array( 'faq_page_hero_image' => 'img/faq/faq-image.svg', 'faq_page_hero_icon' => 'img/faq/faq-icon.svg', 'faq_page_cta_image' => 'img/cta/cta.png' ),
		'media-center' => array( 'media_center_hero_image' => 'img/media-promo.png', 'media_center_hero_background_image' => 'img/media-promo.svg', 'media_center_cta_image' => 'img/cta/cta.png' ),
		'camps'        => array( 'camp_archive_hero_image' => 'img/camp/camp-hero.svg', 'camp_archive_booking_image' => 'img/camp/booking-characters.svg', 'camp_archive_history_image' => 'img/camp/hands.png' ),
		'camp'         => array( 'camp_booking_image' => 'img/camp/booking-characters.svg' ),
		'it-course'    => array(
			'course_hero_image' => 'img/course/it-course-image.svg', 'course_hero_background_image' => 'img/faq/faq-bg.svg', 'course_hero_character_image' => 'img/course/course-icon.svg',
			'course_learn_image' => 'img/learn/learn-image.png', 'course_learn_background_image' => 'img/learn/learn-bg.svg', 'course_learn_character_image' => 'img/learn/learn-icon.svg',
			'course_process_background_image' => 'img/course/process-left-bg.svg', 'course_faq_left_background_image' => 'img/faq/faq-left-bg.svg', 'course_faq_right_background_image' => 'img/faq/faq-right-bg.svg',
			'course_cta_image' => 'img/cta/cta.png', 'course_cta_character_image' => 'img/cta/cta-icon.svg', 'course_cta_top_background_image' => 'img/cta/cta-top-bg.svg', 'course_cta_bottom_background_image' => 'img/cta/cta-bottom-bg.svg',
		),
	);

	public static function apply( string $markup, string $source, int $context_id = 0 ): string {
		$page_id = self::pageId( $source, $context_id );

		if ( ! $page_id || ! function_exists( 'get_field' ) ) {
			return $markup;
		}

		foreach ( self::TEXT_FIELDS[ $source ] ?? array() as $field => $default ) {
			$value = trim( (string) get_field( $field, $page_id ) );

			if ( '' !== $value && self::plainText( $value ) !== self::plainText( $default ) ) {
				$markup = str_replace( $default, esc_html( $value ), $markup );
			}
		}

		foreach ( self::IMAGE_FIELDS[ $source ] ?? array() as $field => $default ) {
			$image = get_field( $field, $page_id );
			$url   = is_numeric( $image ) ? wp_get_attachment_image_url( (int) $image, 'full' ) : '';

			if ( $url ) {
				$markup = self::replaceImageAsset( $markup, $default, (string) $url );
			}
		}

		$markup = self::applySelectedContent( $markup, $source, $page_id );
		$markup = self::applySectionFields( $markup, $source, $page_id );
		$markup = self::applyGallery( $markup, $source, $page_id );
		$markup = self::applyRepeaters( $markup, $source, $page_id );
		$markup = 'it-courses' === $source ? self::applyItCategories( $markup, $page_id ) : $markup;
		$markup = 'it-courses' === $source ? self::applyItCatalogCards( $markup, $page_id ) : $markup;
		$markup = 'camp' === $source ? self::applyCampDetails( $markup, $page_id ) : $markup;
		$markup = 'camp' === $source ? self::applyCampHeroImages( $markup, $page_id ) : $markup;
		$markup = 'camp' === $source ? self::applyCampHeroDates( $markup, $page_id ) : $markup;
		$markup = 'camp' === $source ? self::applyCampHeroFacts( $markup, $page_id ) : $markup;
		$markup = 'camp' === $source ? self::applyCampTrips( $markup, $page_id ) : $markup;
		$markup = 'camp' === $source ? self::applyCampIncludes( $markup, $page_id ) : $markup;
		$markup = 'camp' === $source ? self::applyCampExtraSections( $markup, $page_id ) : $markup;
		$markup = 'camp' === $source ? self::applyCampBooking( $markup, $page_id ) : $markup;
		$markup = 'it-course' === $source ? self::applyCourseLearn( $markup, $page_id ) : $markup;
		$markup = 'it-course' === $source ? self::applyCoursePage( $markup, $page_id ) : $markup;
		$markup = 'about' === $source ? self::applyAboutImageRows( $markup, $page_id ) : $markup;
		$markup = in_array( $source, array( 'it-courses', 'en-courses' ), true ) ? self::applyMarquee( $markup, $source, $page_id ) : $markup;

		return $markup;
	}

	private static function replaceImageAsset( string $markup, string $default, string $url ): string {
		$markup = str_replace( $default, esc_url( $url ), $markup );
		return str_ends_with( $default, '.png' ) ? str_replace( substr( $default, 0, -4 ) . '.webp', esc_url( $url ), $markup ) : $markup;
	}

	private static function applyItCatalogCards( string $markup, int|string $context ): string {
		$rows = array_values( array_filter( (array) get_field( 'it_courses_catalog_cards', $context ), 'is_array' ) );
		if ( ! $rows || ! preg_match( '#(<ul class="courses-section__items">)(.*?)(</ul>)#s', $markup, $list ) ) {
			return $markup;
		}
		preg_match_all( '#<li class="courses-section__item">.*?</li>#s', $list[2], $cards );
		if ( ! $cards[0] ) {
			return $markup;
		}
		$output = '';
		foreach ( $rows as $index => $row ) {
			$card = $cards[0][ min( $index, count( $cards[0] ) - 1 ) ];
			$label = trim( (string) ( $row['label'] ?? '' ) );
			$anchor = trim( (string) ( $row['anchor'] ?? '' ) );
			$card = $label ? self::replaceLeaf( $card, '#(<div class="courses-section__item-ages">)(.*?)(</div>)#s', $label ) : $card;
			$card = $anchor ? (string) preg_replace( '#(<a\b[^>]*\bhref=)(["\']).*?\2#', '$1$2' . esc_url( $anchor ) . '$2', $card, 1 ) : $card;
			foreach ( array( 'background' => 'courses-section__item-bg', 'image' => 'courses-section__item-image' ) as $field => $class ) {
				$url = wp_get_attachment_image_url( (int) ( $row[ $field ] ?? 0 ), 'large' );
				if ( $url ) {
					$card = (string) preg_replace_callback( '#(<div class="' . $class . '">)(.*?)(</div>)#s', static fn( array $part ): string => $part[1] . preg_replace_callback( '#\b(src|srcset)=(["\']).*?\2#', static fn( array $attribute ): string => $attribute[1] . '="' . esc_url( $url ) . '"', $part[2] ) . $part[3], $card, 1 );
				}
			}
			$output .= $card;
		}

		return str_replace( $list[0], $list[1] . $output . $list[3], $markup );
	}

	private static function applyCampHeroImages( string $markup, int|string $context ): string {
		$images = array_values( array_filter( array_map( 'absint', (array) get_field( 'camp_hero_images', $context ) ) ) );
		if ( ! $images ) {
			return $markup;
		}
		$index = 0;
		return (string) preg_replace_callback(
			'#(<div class="camp-hero__gallery"[^>]*>)(.*?)(</div>)#s',
			static function ( array $gallery ) use ( $images, &$index ): string {
				$body = (string) preg_replace_callback(
					'#(<img\b[^>]*\bsrc=)(["\']).*?\2#s',
					static function ( array $image ) use ( $images, &$index ): string {
						$url = isset( $images[ $index ] ) ? wp_get_attachment_image_url( $images[ $index++ ], 'large' ) : false;
						return $url ? $image[1] . $image[2] . esc_url( $url ) . $image[2] : $image[0];
					},
					$gallery[2]
				);
				return $gallery[1] . $body . $gallery[3];
			},
			$markup,
			1
		);
	}

	private static function applyAboutImageRows( string $markup, int|string $context ): string {
		foreach ( array( 'about-directions' => array( 'about_directions_items', true ), 'about-outcomes' => array( 'about_outcome_items', false ) ) as $section_class => [ $field, $with_link ] ) {
			$rows = array_values( array_filter( (array) get_field( $field, $context ), 'is_array' ) );
			if ( ! $rows || ! preg_match( '#(<section class="' . $section_class . '".*?<ul>)(.*?)(</ul>)#s', $markup, $list ) || ! preg_match( '#<li>.*?</li>#s', $list[2], $template ) ) {
				continue;
			}
			$items = '';
			foreach ( $rows as $row ) {
				$item = $template[0];
				$image = (int) ( $row['image'] ?? 0 );
				$url = $image ? wp_get_attachment_image_url( $image, 'large' ) : false;
				if ( $url ) {
					$item = (string) preg_replace( '#(\bsrc=)(["\']).*?\2#', '$1$2' . esc_url( $url ) . '$2', $item, 1 );
				}
				$title = trim( (string) ( $row['title'] ?? '' ) );
				if ( $title ) {
					$item = (string) preg_replace( '#\balt=(["\']).*?\1#', 'alt="' . esc_attr( $title ) . '"', $item, 1 );
				}
				$link = is_array( $row['link'] ?? null ) ? (string) ( $row['link']['url'] ?? '' ) : '';
				if ( $with_link && $link ) {
					$item = (string) preg_replace( '#(<a\b[^>]*\bhref=)(["\']).*?\2#', '$1$2' . esc_url( $link ) . '$2', $item, 1 );
				}
				$items .= $item;
			}
			$markup = str_replace( $list[0], $list[1] . $items . $list[3], $markup );
		}

		return $markup;
	}

	private static function applyMarquee( string $markup, string $source, int|string $context ): string {
		$field = 'it-courses' === $source ? 'it_courses_marquee_items' : 'english_courses_marquee_items';
		$rows = array_values( array_filter( (array) get_field( $field, $context ), 'is_array' ) );
		if ( ! $rows ) {
			return $markup;
		}
		$index = 0;
		return (string) preg_replace_callback(
			'#(<p class="marquee-section__text">)(.*?)(</p>)#s',
			static function ( array $matches ) use ( $rows, &$index ): string {
				$text = trim( (string) ( $rows[ $index++ % count( $rows ) ]['text'] ?? '' ) );
				return $text ? $matches[1] . esc_html( $text ) . $matches[3] : $matches[0];
			},
			$markup
		);
	}

	private static function applyCourseLearn( string $markup, int|string $context ): string {
		$rows = array_values( array_filter( (array) get_field( 'course_learn_items', $context ), 'is_array' ) );
		if ( ! $rows || ! preg_match( '#(<ul class="learn-section__items">)(.*?)(</ul>)#s', $markup, $list ) || ! preg_match( '#<li class="learn-section__item">.*?</li>#s', $list[2], $template ) ) {
			return $markup;
		}
		$items = '';
		foreach ( $rows as $row ) {
			$item = $template[0];
			$title = trim( (string) ( $row['title'] ?? '' ) );
			$text = trim( (string) ( $row['text'] ?? '' ) );
			$label = $title . ( $title && $text ? ' — ' : '' ) . $text;
			$item = $label ? self::replaceLeaf( $item, '#(<p>)(.*?)(</p>)#s', $label ) : $item;
			$image = (int) ( $row['image'] ?? 0 );
			$url = $image ? wp_get_attachment_image_url( $image, 'thumbnail' ) : false;
			if ( $url ) {
				$item = (string) preg_replace( '#(\bsrc=)(["\']).*?\2#', '$1$2' . esc_url( $url ) . '$2', $item, 1 );
			}
			$items .= $item;
		}

		return str_replace( $list[0], $list[1] . $items . $list[3], $markup );
	}

	private static function applyCoursePage( string $markup, int|string $context ): string {
		$hero_cta = trim( (string) get_field( 'course_hero_cta_label', $context ) ) ?: trim( (string) get_field( 'course_cta_label', $context ) );
		$markup = $hero_cta ? self::replaceButtonText( $markup, 'course-banner-section__btn', $hero_cta ) : $markup;
		$markup = self::replaceButtonText( $markup, 'btn--bordered-violet', (string) get_field( 'course_program_anchor_label', $context ) );
		$markup = self::replaceButtonText( $markup, 'cta-form__btn', (string) get_field( 'course_cta_submit_label', $context ) );

		if ( ! array_filter( (array) get_field( 'course_learn_items', $context ), 'is_array' ) ) {
			$markup = self::removeSection( $markup, 'learn-section' );
		}
		if ( ! array_filter( (array) get_field( 'course_process_items', $context ), 'is_array' ) ) {
			$markup = self::removeSection( $markup, 'process-section' );
		}
		$markup = self::applyCoursePortfolio( $markup, $context );

		if ( ! array_filter( (array) get_field( 'course_program', $context ), 'is_array' ) ) {
			$markup = self::removeSection( $markup, 'faq-section' );
		}

		$related_faq = self::published( (array) get_field( 'course_related_faq', $context ), 'faq_item' );
		$local_faq   = array_filter( (array) get_field( 'course_faq_items', $context ), 'is_array' );
		if ( ! $related_faq && ! $local_faq ) {
			$markup = self::removeSection( $markup, 'faq-section', true );
		} elseif ( ! $related_faq ) {
			$items  = array_map( static fn( array $row ): string => self::localFaqItem( $row ), $local_faq );
			$markup = self::replaceAccordion( $markup, implode( '', $items ), true );
		}

		if ( ! self::published( (array) get_field( 'course_related_reviews', $context ), 'review' ) ) {
			$markup = self::removeSection( $markup, 'testimonials-section' );
		}

		return self::applyCourseContext( $markup, absint( $context ) );
	}

	private static function applyCoursePortfolio( string $markup, int|string $context ): string {
		$cards = Logika_Theme_Source_Markup::portfolioCards( array_filter( (array) get_field( 'course_projects', $context ), 'is_array' ) );
		if ( ! $cards ) {
			return self::removeSection( $markup, 'portfolio-section' );
		}

		return (string) preg_replace( '#<div class="portfolio-section__slider">\s*</div>#s', '<div class="portfolio-section__viewport"><ul class="portfolio-section__slider">' . implode( '', $cards ) . '</ul></div>', $markup, 1 );
	}

	private static function applyCourseContext( string $markup, int $course_id ): string {
		if ( ! $course_id ) {
			return $markup;
		}
		$markup = (string) preg_replace_callback(
			'#(<(?:a|button)\b[^>]*\bclass=(?:["\'])[^"\']*(?:course-banner-section__btn|process-section__item-lesson)[^"\']*(?:["\'])[^>]*)(>)#s',
			static fn( array $matches ): string => str_contains( $matches[1], 'data-logika-course-id=' ) ? $matches[0] : $matches[1] . ' data-logika-course-id="' . esc_attr( (string) $course_id ) . '"' . $matches[2],
			$markup
		);

		return (string) preg_replace( '#(<form\b[^>]*\bclass=(?:["\'])[^"\']*\bcta-form\b[^"\']*(?:["\'])[^>]*>)#s', '$1<input type="hidden" name="course_id" value="' . esc_attr( (string) $course_id ) . '">', $markup, 1 );
	}

	private static function replaceButtonText( string $markup, string $class, string $text ): string {
		if ( '' === trim( $text ) ) {
			return $markup;
		}
		return (string) preg_replace_callback(
			'#(<(?:a|button)\b[^>]*\bclass=(?:["\'])[^"\']*\b' . preg_quote( $class, '#' ) . '\b[^"\']*(?:["\'])[^>]*>).*?(<svg\b[^>]*>)#s',
			static fn( array $matches ): string => $matches[1] . esc_html( $text ) . ' ' . $matches[2],
			$markup,
			1
		);
	}

	private static function removeSection( string $markup, string $class, bool $last = false ): string {
		if ( ! preg_match_all( '#<section\b[^>]*\bclass=(?:["\'])[^"\']*\b' . preg_quote( $class, '#' ) . '\b[^"\']*(?:["\'])[^>]*>.*?</section>#s', $markup, $sections, PREG_OFFSET_CAPTURE ) ) {
			return $markup;
		}
		$section = $sections[0][ $last ? count( $sections[0] ) - 1 : 0 ];
		return substr_replace( $markup, '', (int) $section[1], strlen( $section[0] ) );
	}

	private static function applyItCategories( string $markup, int|string $context ): string {
		$rows = array_values( array_filter( (array) get_field( 'it_courses_age_categories', $context ), 'is_array' ) );
		if ( ! $rows ) {
			return $markup;
		}
		$index = 0;
		return (string) preg_replace_callback(
			'#<section class="categories-section"[^>]*>.*?</section>#s',
			static function ( array $matches ) use ( $rows, &$index ): string {
				$section = $matches[0];
				$row = $rows[ $index++ ] ?? null;
				if ( ! is_array( $row ) ) {
					return $section;
				}
				$title = trim( (string) ( $row['title'] ?? '' ) );
				$section = $title ? self::replaceLeaf( $section, '#(<h2 class="categories-section__title">)(.*?)(</h2>)#s', $title ) : $section;
				$courses = self::published( (array) ( $row['courses'] ?? array() ), 'course' );
				$placeholders = array_values( array_filter( (array) ( $row['placeholder_cards'] ?? array() ), 'is_array' ) );
				if ( ( ! $courses && ! $placeholders ) || ! preg_match( '#(<ul class=["\']swiper-wrapper["\']>)(.*)(</ul>\s*</div>)#s', $section, $list ) || ! preg_match( '#<li class=["\']swiper-slide["\']>.*?</div>\s*</li>#s', $list[2], $template ) ) {
					return $section;
				}
				$cards = '';
				foreach ( $courses as $id ) {
					$card = $template[0];
					$min = (int) get_field( 'course_age_min', $id );
					$max = (int) get_field( 'course_age_max', $id );
					$age = $min ? $min . ( $max ? '-' . $max : '+' ) . ' років' : '';
					$card = $age ? self::replaceLeaf( $card, '#(<span class="course-card__label">)(.*?)(</span>)#s', $age ) : $card;
					$card = self::replaceLeaf( $card, '#(<span class="course-card__title h4">)(.*?)(</span>)#s', get_the_title( $id ) );
					$text = trim( (string) get_field( 'course_card_description', $id ) ) ?: trim( (string) get_field( 'course_short_description', $id ) );
					$card = $text ? self::replaceLeaf( $card, '#(<p class="course-card__descr">)(.*?)(</p>)#s', $text ) : $card;
					$image = (int) get_field( 'course_card_image', $id );
					$url = $image ? wp_get_attachment_image_url( $image, 'large' ) : false;
					if ( $url ) {
						$card = (string) preg_replace( '#(<div class="course-card__image">\s*<img\b[^>]*\bsrc=)(["\']).*?\2#s', '$1$2' . esc_url( $url ) . '$2', $card, 1 );
					}
					$card = (string) preg_replace( '#(<a\b[^>]*class="[^"]*course-card__btn btn btn--green[^"]*")#', '$1 data-logika-course-id="' . esc_attr( (string) $id ) . '"', $card, 1 );
					$card = (string) preg_replace( '#(<a\b[^>]*href=)(["\']).*?\2(?=[^>]*class="[^"]*btn--bordered)#', '$1$2' . esc_url( get_permalink( $id ) ) . '$2', $card, 1 );
					$cards .= $card;
				}
				foreach ( $placeholders as $placeholder ) {
					$card = self::replaceLeaf( $template[0], '#(<span class="course-card__title h4">)(.*?)(</span>)#s', (string) ( $placeholder['title'] ?? '' ) );
					$cards .= $card;
				}

				return str_replace( $list[0], $list[1] . $cards . $list[3], $section );
			},
			$markup
		);
	}

	private static function applyRepeaters( string $markup, string $source, int|string $context ): string {
		$configs = array(
			'about' => array(
				array( 'about_stats_items', 'about-stats__list', '', 'tail' ),
				array( 'about_media_items', 'media-section__cards', 'media-section__card', 'card' ),
				array( 'about_onboarding_items', 'onboarding-section__items', 'onboarding-section__item', 'card' ),
			),
			'en-courses' => array( array( 'english_courses_benefits', 'en-about-section__items', 'en-about-section__item', 'card' ) ),
			'media-center' => array( array( 'media_center_benefits', 'media-section__cards', 'media-section__card', 'card' ) ),
			'camps' => array( array( 'camp_archive_benefits', 'camp-highlights__list', 'camp-highlights__item', 'card' ) ),
			'camp' => array(
				array( 'camp_benefits', 'camp-benefits__list', 'camp-benefits__item', 'card' ),
				array( 'camp_activities', 'camp-activities__list', 'camp-activities__item', 'card' ),
			),
			'it-course' => array( array( 'course_process_items', 'process-section__items', 'process-section__item', 'card' ) ),
		);
		foreach ( $configs[ $source ] ?? array() as [ $field, $list_class, $item_class, $mode ] ) {
			$rows = array_values( array_filter( (array) get_field( $field, $context ), 'is_array' ) );
			if ( $rows ) {
				$markup = self::replaceListRows( $markup, $list_class, $item_class, $rows, $mode );
			}
		}

		return $markup;
	}

	private static function applyCampDetails( string $markup, int|string $context ): string {
		$rows = array_values( array_filter( (array) get_field( 'camp_details', $context ), 'is_array' ) );
		if ( ! $rows ) {
			return $markup;
		}
		$index = 0;
		return (string) preg_replace_callback(
			'#<section class="[^"]*\\bcamp-details\\b[^"]*"[^>]*>.*?</section>#s',
			static function ( array $matches ) use ( $rows, &$index ): string {
				$row = $rows[ $index++ ] ?? null;
				if ( ! is_array( $row ) ) {
					return $matches[0];
				}
				$section = $matches[0];
				$title = trim( (string) ( $row['title'] ?? '' ) );
				$text = trim( (string) ( $row['text'] ?? '' ) );
				$section = $title ? self::replaceLeaf( $section, '#(<h2\b[^>]*>)(.*?)(</h2>)#s', $title ) : $section;
				$section = $text ? self::replaceLeaf( $section, '#(<div class="camp-details__copy">.*?<p>)(.*?)(</p>)#s', $text ) : $section;
				$images = array_values( array_filter( array_map( 'absint', (array) ( $row['gallery'] ?? array() ) ) ) );
				if ( ! $images && ! empty( $row['image'] ) ) {
					$images[] = absint( $row['image'] );
				}
				$urls = array_values( array_filter( array_map( static fn( int $image ): string|false => wp_get_attachment_image_url( $image, 'large' ), $images ) ) );
				if ( ! $urls ) {
					return $section;
				}
				$section = (string) preg_replace( '#(<div class="camp-details__main-image">.*?<img\b[^>]*\bsrc=)(["\']).*?\2#s', '$1$2' . esc_url( $urls[0] ) . '$2', $section, 1 );
				$thumbs = '';
				foreach ( $urls as $image_index => $url ) {
					$thumbs .= '<li><button class="camp-details__thumb' . ( 0 === $image_index ? ' is-active' : '' ) . '" type="button" data-gallery-thumb="' . esc_url( $url ) . '" aria-label="Показати фото ' . esc_attr( (string) ( $image_index + 1 ) ) . '" aria-pressed="' . ( 0 === $image_index ? 'true' : 'false' ) . '"><img src="' . esc_url( $url ) . '" alt=""></button></li>';
				}
				return (string) preg_replace( '#(<ul class="camp-details__thumbs">).*?(</ul>)#s', '$1' . $thumbs . '$2', $section, 1 );
			},
			$markup
		);
	}

	private static function applyCampHeroFacts( string $markup, int|string $context ): string {
		$rows = array_values( array_filter( (array) get_field( 'camp_hero_facts', $context ), 'is_array' ) );
		if ( ! $rows || ! preg_match( '#(<ul class="banner-section__bar">)(.*?)(</ul>)#s', $markup, $list ) ) {
			return $markup;
		}
		preg_match_all( '#<li>.*?</li>#s', $list[2], $templates );
		if ( ! $templates[0] ) {
			return $markup;
		}
		$items = '';
		foreach ( $rows as $index => $row ) {
			$item  = $templates[0][ min( $index, count( $templates[0] ) - 1 ) ];
			$label = trim( (string) ( $row['label'] ?? '' ) );
			$value = trim( (string) ( $row['value'] ?? '' ) );
			$item  = $label ? self::replaceLeaf( $item, '#(<p>\s*<span>)(.*?)(</span>)#s', $label ) : $item;
			$item  = $value ? self::replaceLeaf( $item, '#(<strong>)(.*?)(</strong>)#s', $value ) : $item;
			$url   = wp_get_attachment_image_url( absint( $row['icon'] ?? 0 ), 'large' );
			$items .= $url ? (string) preg_replace( '#(<img\b[^>]*\bsrc=)(["\']).*?\2#', '$1$2' . esc_url( $url ) . '$2', $item, 1 ) : $item;
		}

		return str_replace( $list[0], $list[1] . $items . $list[3], $markup );
	}

	private static function applyCampHeroDates( string $markup, int|string $context ): string {
		$dates = trim( (string) get_field( 'camp_hero_dates_text', $context ) );
		if ( ! $dates ) {
			$start = (string) get_field( 'camp_start_date', $context );
			$end   = (string) get_field( 'camp_end_date', $context );
			$dates = $start ? wp_date( 'd.m.Y', strtotime( $start ) ) . ( $end ? ' - ' . wp_date( 'd.m.Y', strtotime( $end ) ) : '' ) : '';
		}
		if ( $dates ) {
			$markup = self::replaceLeaf( $markup, '#(<div class="banner-section__info">.*?<h4>)(.*?)(</h4>)#s', $dates );
		}
		$form_title = trim( (string) get_field( 'camp_hero_form_title', $context ) );
		if ( $form_title ) {
			$markup = (string) preg_replace( '#(<div class="main-form__title h5">).*?(</div>)#s', '$1' . nl2br( esc_html( $form_title ) ) . '$2', $markup, 1 );
		}
		$cta_label = trim( (string) get_field( 'camp_cta_label', $context ) );
		if ( $cta_label ) {
			$markup = (string) preg_replace( '#(<button class="main-form__btn btn btn--yellow" type="submit">).*?(\s*<svg)#s', '$1' . esc_html( $cta_label ) . '$2', $markup, 1 );
			$markup = (string) preg_replace( '#(<a class="camp-details__cta btn btn--violet" href="\#form">).*?(\s*<svg)#s', '$1' . esc_html( $cta_label ) . '$2', $markup );
		}

		return $markup;
	}

	private static function applyCampTrips( string $markup, int|string $context ): string {
		$rows = array_values( array_filter( (array) get_field( 'camp_trips', $context ), 'is_array' ) );
		if ( ! $rows || ! preg_match( '#<section class="trips-section">.*?</section>#s', $markup, $match ) ) {
			return $markup;
		}
		$section = $match[0];
		$title   = trim( (string) get_field( 'camp_trips_title', $context ) );
		$section = $title ? self::replaceLeaf( $section, '#(<h2 class="trips-section__title">)(.*?)(</h2>)#s', $title ) : $section;
		$tags    = array_filter( array_map( static fn( array $row ): string => trim( (string) ( $row['title'] ?? '' ) ), $rows ) );
		if ( $tags ) {
			$section = (string) preg_replace( '#(<ul class="trips-section__tags">).*?(</ul>)#s', '$1' . implode( '', array_map( static fn( string $tag ): string => '<li class="h5">' . esc_html( $tag ) . '</li>', $tags ) ) . '$2', $section, 1 );
		}
		if ( preg_match( '#(<ul class=["\']swiper-wrapper["\']>)(.*?)(</ul>)#s', $section, $list ) && preg_match( '#<li class=["\']swiper-slide["\']>.*?</li>#s', $list[2], $template ) ) {
			$slides = '';
			foreach ( $rows as $row ) {
				$url = wp_get_attachment_image_url( absint( $row['image'] ?? 0 ), 'large' );
				if ( ! $url ) {
					continue;
				}
				$slide = (string) preg_replace( '#\b(src|srcset)=(["\']).*?\2#', '$1=$2' . esc_url( $url ) . '$2', $template[0] );
				$slides .= (string) preg_replace( '#(<img\b[^>]*\balt=)(["\']).*?\2#', '$1$2' . esc_attr( (string) ( $row['title'] ?? '' ) ) . '$2', $slide, 1 );
			}
			$section = $slides ? str_replace( $list[0], $list[1] . $slides . $list[3], $section ) : $section;
		}

		return str_replace( $match[0], $section, $markup );
	}

	private static function applyCampIncludes( string $markup, int|string $context ): string {
		$rows = array_values( array_filter( (array) get_field( 'camp_includes', $context ), 'is_array' ) );
		if ( ! $rows ) {
			return $markup;
		}
		$title = trim( (string) get_field( 'camp_includes_title', $context ) );
		if ( $title ) {
			$markup = self::replaceLeaf( $markup, '#(<h2 class="details-section__title">)(.*?)(</h2>)#s', $title );
		}

		return self::replaceListRows( $markup, 'details-section__items', 'details-section__item', $rows, 'card' );
	}

	private static function applyCampBooking( string $markup, int|string $context ): string {
		$rows = array_values( array_filter( (array) get_field( 'camp_booking_benefits', $context ), 'is_array' ) );
		if ( $rows && preg_match( '#(<div class="camp-booking__benefits">\s*<ul>)(.*?)(</ul>)#s', $markup, $list ) ) {
			$items = implode( '', array_map( static fn( array $row ): string => '<li>' . esc_html( (string) ( $row['text'] ?? '' ) ) . '</li>', $rows ) );
			$markup = str_replace( $list[0], $list[1] . $items . $list[3], $markup );
		}
		$form_title = trim( (string) get_field( 'camp_booking_form_title', $context ) );
		if ( $form_title ) {
			$markup = (string) preg_replace( '#(<div class="camp-booking__form-title">).*?(</div>)#s', '$1' . nl2br( esc_html( $form_title ) ) . '$2', $markup, 1 );
		}
		$submit_label = trim( (string) get_field( 'camp_booking_submit_label', $context ) );
		if ( $submit_label ) {
			$markup = (string) preg_replace( '#(<button class="camp-booking__submit btn btn--yellow" type="submit">).*?(\s*<span)#s', '$1' . esc_html( $submit_label ) . '$2', $markup, 1 );
		}

		return $markup;
	}

	private static function applyCampExtraSections( string $markup, int|string $context ): string {
		$rows = array_values( array_filter( (array) get_field( 'camp_extra_sections', $context ), 'is_array' ) );
		if ( ! $rows ) {
			return self::removeSection( $markup, 'camp-extra' );
		}
		$items = '';
		foreach ( $rows as $row ) {
			$title  = trim( (string) ( $row['title'] ?? '' ) );
			$text   = wp_kses_post( (string) ( $row['text'] ?? '' ) );
			$images = array_values( array_filter( array_map( 'absint', (array) ( $row['images'] ?? array() ) ) ) );
			$gallery = '';
			foreach ( $images as $image ) {
				$url = wp_get_attachment_image_url( $image, 'large' );
				$gallery .= $url ? '<li><img src="' . esc_url( $url ) . '" alt=""></li>' : '';
			}
			$items .= '<article class="camp-extra__item">' . ( $title ? '<h2>' . esc_html( $title ) . '</h2>' : '' ) . ( $text ? '<div class="camp-extra__text">' . $text . '</div>' : '' ) . ( $gallery ? '<ul class="camp-extra__gallery">' . $gallery . '</ul>' : '' ) . '</article>';
		}

		return (string) preg_replace( '#(<div class="camp-extra__list">).*?(</div>\s*</div>\s*</section>)#s', '$1' . $items . '$2', $markup, 1 );
	}

	private static function replaceListRows( string $markup, string $list_class, string $item_class, array $rows, string $mode ): string {
		$pattern = '#(<ul\b[^>]*class=(["\'])[^"\']*\\b' . preg_quote( $list_class, '#' ) . '\\b[^"\']*\2[^>]*>)(.*?)(</ul>)#s';
		if ( ! preg_match( $pattern, $markup, $list ) ) {
			return $markup;
		}
		$item_pattern = $item_class ? '#<li\b(?=[^>]*class=(["\'])[^"\']*\\b' . preg_quote( $item_class, '#' ) . '\\b[^"\']*\1)[^>]*>.*?</li>#s' : '#<li\b[^>]*>.*?</li>#s';
		preg_match_all( $item_pattern, $list[3], $matches );
		if ( ! $matches[0] ) {
			return $markup;
		}
		$items = '';
		foreach ( $rows as $index => $row ) {
			$template = $matches[0][ min( $index, count( $matches[0] ) - 1 ) ];
			$items .= self::hydrateListItem( $template, $row, $mode );
		}

		return str_replace( $list[0], $list[1] . $items . $list[4], $markup );
	}

	private static function hydrateListItem( string $item, array $row, string $mode ): string {
		$title = trim( (string) ( $row['title'] ?? $row['text'] ?? '' ) );
		$text  = trim( (string) ( $row['text'] ?? '' ) );
		$title_uses_paragraph = false;
		if ( 'tail' === $mode && $title ) {
			$item = (string) preg_replace_callback(
				'#(<li\b[^>]*>\s*<img\b[^>]*>).*?(</li>)#s',
				static fn( array $matches ): string => $matches[1] . esc_html( $title ) . $matches[2],
				$item,
				1
			);
		} elseif ( $title ) {
			$title_pattern = '#(<(?:h3\b[^>]*|(?:div|span)\b[^>]*class=["\'][^"\']*(?:__item-title|__item-heading|__name|__heading)[^"\']*["\'][^>]*)>)(.*?)(</(?:h3|div|span)>)#s';
			if ( preg_match( $title_pattern, $item ) ) {
				$item = self::replaceLeaf( $item, $title_pattern, $title );
			} else {
				$item = self::replaceLeaf( $item, '#(<p\b[^>]*>)(.*?)(</p>)#s', $title );
				$title_uses_paragraph = true;
			}
		}
		if ( $text && $text !== $title && ! $title_uses_paragraph ) {
			$item = self::replaceLeaf( $item, '#(<p\b[^>]*>)(.*?)(</p>)#s', $text );
		}
		$image = (int) ( $row['image'] ?? $row['icon'] ?? 0 );
		$url = $image ? wp_get_attachment_image_url( $image, 'large' ) : false;
		if ( $url ) {
			$item = (string) preg_replace( '#(\bsrc=)(["\']).*?\2#', '$1$2' . esc_url( $url ) . '$2', $item, 1 );
		}
		$cta_label = trim( (string) ( $row['cta_label'] ?? '' ) );
		if ( $cta_label ) {
			$item = (string) preg_replace_callback( '#(<a\b[^>]*\bclass=(?:["\'])[^"\']*\bbtn\b[^"\']*(?:["\'])[^>]*>).*?(<svg\b[^>]*>)#s', static fn( array $matches ): string => $matches[1] . esc_html( $cta_label ) . ' ' . $matches[2], $item, 1 );
		}

		return $item;
	}

	private static function replaceLeaf( string $markup, string $pattern, string $value ): string {
		return (string) preg_replace_callback(
			$pattern,
			static fn( array $matches ): string => self::plainText( $matches[2] ) === self::plainText( $value ) ? $matches[0] : $matches[1] . esc_html( $value ) . $matches[3],
			$markup,
			1
		);
	}

	private static function applyGallery( string $markup, string $source, int|string $context ): string {
		$field = array( 'camps' => 'camp_archive_gallery', 'camp' => 'camp_gallery' )[ $source ] ?? '';
		$images = $field ? array_values( array_filter( array_map( 'absint', (array) get_field( $field, $context ) ) ) ) : array();
		if ( ! $images ) {
			return $markup;
		}
		$target = $markup;
		if ( 'camp' === $source ) {
			if ( ! preg_match( '#<section class="gallery-section">.*?</section>#s', $markup, $section ) ) {
				return $markup;
			}
			$target = $section[0];
		}
		if ( ! preg_match( '#(<ul class=["\']swiper-wrapper["\']>)(.*?)(</ul>)#s', $target, $list ) || ! preg_match( '#(?:<div class="swiper-slide">.*?</div>\s*</div>|<li class="swiper-slide">.*?</div>\s*</li>)#s', $list[2], $slide ) ) {
			return $markup;
		}
		$slides = '';
		foreach ( $images as $image ) {
			$url = wp_get_attachment_image_url( $image, 'large' );
			if ( ! $url ) {
				continue;
			}
			$item = (string) preg_replace( '#\bsrcset=(["\']).*?\1#', 'srcset="' . esc_url( $url ) . '"', $slide[0] );
			$item = (string) preg_replace( '#\bsrc=(["\']).*?\1#', 'src="' . esc_url( $url ) . '"', $item );
			$slides .= $item;
		}

		if ( ! $slides ) {
			return $markup;
		}
		$target = str_replace( $list[0], $list[1] . $slides . $list[3], $target );

		return 'camp' === $source ? str_replace( $section[0], $target, $markup ) : $target;
	}

	private static function applySectionFields( string $markup, string $source, int|string $context ): string {
		if ( in_array( $source, array( 'privacy-policy', 'contractoffer', 'litsenziia' ), true ) ) {
			return self::applyLegalFields( $markup, $context );
		}
		if ( 'camp' === $source || 'it-course' === $source ) {
			$title = get_the_title( (int) $context );
			if ( $title ) {
				$default = 'camp' === $source ? 'Літній табір на Закарпатті – “Фестиваль професій”' : 'Основи фронтенд розробки';
				$markup = str_replace( $default, esc_html( $title ), $markup );
			}
		}

		$html_fields = array(
			'about' => array( 'about_history_text' => '#(<div class="about-history__copy">).*?(</div>)#s' ),
			'camps' => array( 'camp_archive_history_text' => '#(<div class="camp-history__copy">).*?(</div>)#s' ),
		);
		foreach ( $html_fields[ $source ] ?? array() as $field => $pattern ) {
			$value = trim( (string) get_field( $field, $context ) );
			if ( $value ) {
				$markup = (string) preg_replace( $pattern, '$1' . wp_kses_post( $value ) . '$2', $markup, 1 );
			}
		}

		return $markup;
	}

	private static function applyLegalFields( string $markup, int|string $context ): string {
		$title = trim( (string) get_field( 'legal_intro_title', $context ) );
		$intro = trim( (string) get_field( 'legal_intro_text', $context ) );
		$rows = array_filter( (array) get_field( 'legal_sections', $context ), 'is_array' );
		$gallery = array_values( array_filter( array_map( 'absint', (array) get_field( 'legal_gallery', $context ) ) ) );
		if ( ! $title && ! $intro && ! $rows && ! $gallery ) {
			return $markup;
		}
		$content = $title ? '<h2>' . esc_html( $title ) . '</h2>' : '';
		$content .= wp_kses_post( $intro );
		foreach ( $rows as $row ) {
			$anchor = sanitize_title( (string) ( $row['anchor'] ?? '' ) );
			$heading = trim( (string) ( $row['heading'] ?? '' ) );
			$content .= $heading ? '<h2' . ( $anchor ? ' id="' . esc_attr( $anchor ) . '"' : '' ) . '>' . esc_html( $heading ) . '</h2>' : '';
			$content .= wp_kses_post( (string) ( $row['content'] ?? '' ) );
		}
		if ( $gallery ) {
			$content .= '<div class="license-documents">';
			foreach ( $gallery as $image ) {
				$content .= wp_get_attachment_image( $image, 'large' );
			}
			$content .= '</div>';
		}

		return (string) preg_replace_callback(
			'#(<div class="article-section__editor[^>]*">).*?(</div>\s*</div>\s*</section>)#s',
			static fn( array $matches ): string => $matches[1] . $content . $matches[2],
			$markup,
			1
		);
	}

	private static function plainText( string $value ): string {
		$value = html_entity_decode( wp_strip_all_tags( str_replace( '<br>', ' ', $value ) ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		return trim( (string) preg_replace( '/\s+/u', ' ', $value ) );
	}

	private static function applySelectedContent( string $markup, string $source, int|string $page_id ): string {
		$course_field = array( 'en-courses' => 'english_courses_featured_courses' )[ $source ] ?? '';
		if ( $course_field ) {
			$courses = self::published( (array) get_field( $course_field, $page_id ), 'course' );
			if ( $courses ) {
				$markup = self::applyEnglishCourses( $markup, $courses );
			}
		}

		$faq_field = array(
			'faq' => 'faq_page_featured_faq',
			'it-courses' => 'it_courses_featured_faq',
			'en-courses' => 'english_courses_featured_faq',
			'about' => 'about_featured_faq',
			'media-center' => 'media_center_featured_faq',
			'camps' => 'camp_archive_faq',
			'camp' => 'camp_related_faq',
			'it-course' => 'course_related_faq',
		)[ $source ] ?? '';
		if ( $faq_field ) {
			$faqs = self::published( (array) get_field( $faq_field, $page_id ), 'faq_item' );
			if ( $faqs ) {
				$items  = array_map( static fn( int $id ): string => self::faqItem( $id ), $faqs );
				$markup = self::replaceAccordion( $markup, implode( '', $items ), 'it-course' === $source );
			}
		}
		if ( 'it-course' === $source ) {
			$program = array_filter( (array) get_field( 'course_program', $page_id ), 'is_array' );
			if ( $program ) {
				$items = array_map( static fn( array $row ): string => self::programItem( $row ), $program );
				$markup = self::replaceAccordion( $markup, implode( '', $items ) );
			}
		}

		return $markup;
	}

	private static function applyEnglishCourses( string $markup, array $courses ): string {
		if ( ! preg_match( '#(<ul class="en-courses-section__items">)(.*?)(</ul>)#s', $markup, $list ) ) {
			return $markup;
		}
		preg_match_all( '#<li class=["\']en-courses-section__item["\']>.*?</li>#s', $list[2], $templates );
		if ( ! $templates[0] ) {
			return $markup;
		}
		$items = '';
		foreach ( array_values( $courses ) as $index => $id ) {
			$item = $templates[0][ min( $index, count( $templates[0] ) - 1 ) ];
			$min = (int) get_field( 'course_age_min', $id );
			$max = (int) get_field( 'course_age_max', $id );
			$age = $min ? $min . ( $max ? '-' . $max : '+' ) . ' років' : '';
			$item = $age ? self::replaceLeaf( $item, '#(<div class="english-level__ages">)(.*?)(</div>)#s', $age ) : $item;
			$item = self::replaceLeaf( $item, '#(<span class="h4">)(.*?)(</span>)#s', get_the_title( $id ) );
			$text = trim( (string) get_field( 'course_short_description', $id ) );
			$item = $text ? self::replaceLeaf( $item, '#(<div class="english-level__info">.*?<p>)(.*?)(</p>)#s', $text ) : $item;
			$image = (int) get_field( 'course_card_image', $id );
			$url = $image ? wp_get_attachment_image_url( $image, 'large' ) : false;
			if ( $url ) {
				$item = (string) preg_replace( '#(<div class="english-level__image">\s*<img\b[^>]*\bsrc=)(["\']).*?\2#s', '$1$2' . esc_url( $url ) . '$2', $item, 1 );
			}
			$item = (string) preg_replace( '#(<a\b[^>]*class="[^"]*english-level__link[^"]*")#', '$1 data-logika-course-id="' . esc_attr( (string) $id ) . '"', $item, 1 );
			$items .= $item;
		}

		return str_replace( $list[0], $list[1] . $items . $list[3], $markup );
	}

	private static function replaceAccordion( string $markup, string $items, bool $last = false ): string {
		preg_match_all( '#<section class="faq-section"[^>]*>.*?</section>#s', $markup, $sections, PREG_OFFSET_CAPTURE );
		if ( ! $sections[0] ) {
			return $markup;
		}
		$target = $sections[0][ $last ? count( $sections[0] ) - 1 : 0 ];
		$section = (string) preg_replace_callback(
			'#(<ul class=[\'\"][^\'\"]*\\baccordion\\b[^\'\"]*[\'\"][^>]*>)(.*)(</ul>)#s',
			static fn( array $matches ): string => $matches[1] . $items . $matches[3],
			$target[0],
			1
		);

		return substr_replace( $markup, $section, (int) $target[1], strlen( $target[0] ) );
	}

	/** @return int[] */
	private static function published( array $ids, string $post_type ): array {
		return match ( $post_type ) {
			'course'   => Logika_Theme_Entities::courses( $ids ),
			'faq_item' => Logika_Theme_Entities::faqs( $ids ),
			'review'   => Logika_Theme_Entities::reviews( $ids ),
			default    => array_values( array_filter( array_map( 'absint', $ids ), static fn( int $id ): bool => $id && $post_type === get_post_type( $id ) && 'publish' === get_post_status( $id ) ) ),
		};
	}

	private static function faqItem( int $id ): string {
		$question = (string) get_field( 'faq_question', $id );
		$answer   = (string) get_field( 'faq_answer', $id );

		return '<li class="accordion__item"><button class="accordion__btn h5" data-id="' . esc_attr( (string) $id ) . '">' . esc_html( $question ) . '</button><div class="accordion__content" data-content="' . esc_attr( (string) $id ) . '"><div class="editor">' . wp_kses_post( $answer ) . '</div></div></li>';
	}

	private static function localFaqItem( array $row ): string {
		$id = wp_unique_id( 'course-faq-' );
		return '<li class="accordion__item"><button class="accordion__btn h5" data-id="' . esc_attr( $id ) . '">' . esc_html( (string) ( $row['question'] ?? '' ) ) . '</button><div class="accordion__content" data-content="' . esc_attr( $id ) . '"><div class="editor">' . wp_kses_post( (string) ( $row['answer'] ?? '' ) ) . '</div></div></li>';
	}

	private static function programItem( array $row ): string {
		$id = wp_unique_id( 'course-program-' );
		$content = wp_kses_post( (string) ( $row['description'] ?? '' ) );
		$points = array_filter( (array) ( $row['items'] ?? array() ), 'is_array' );
		if ( $points ) {
			$content .= '<ul>' . implode( '', array_map( static fn( array $point ): string => '<li>' . esc_html( (string) ( $point['item_text'] ?? '' ) ) . '</li>', $points ) ) . '</ul>';
		}

		return '<li class="accordion__item"><button class="accordion__btn h5" data-id="' . esc_attr( $id ) . '">' . esc_html( (string) ( $row['title'] ?? '' ) ) . '</button><div class="accordion__content" data-content="' . esc_attr( $id ) . '"><div class="editor">' . $content . '</div></div></li>';
	}

	private static function pageId( string $source, int $context_id = 0 ): int|string {
		if ( 'camps' === $source ) {
			return 'camp_archive';
		}
		if ( $context_id && 'index' !== $source ) {
			return $context_id;
		}
		$queried_id = get_queried_object_id();

		if ( $queried_id ) {
			return (int) $queried_id;
		}

		$page = get_page_by_path( 'en-courses' === $source ? 'english-courses' : $source );

		return $page ? (int) $page->ID : 0;
	}
}
