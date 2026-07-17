<?php

declare(strict_types=1);

final class Logika_Theme_Source_Markup {
	/**
	 * @var array<string, string>
	 */
	private const PAGE_SOURCES = array(
		'about'           => 'about',
		'faq'             => 'faq',
		'it-courses'      => 'it-courses',
		'english-courses' => 'en-courses',
		'media-center'    => 'media-center',
		'privacy-policy'  => 'privacy-policy',
		'contractoffer'   => 'contractoffer',
		'contractoffer-overseas' => 'contractoffer',
		'litsenziia'      => 'litsenziia',
	);

	/**
	 * @var array<string, string>
	 */
	private const ROUTES = array(
		'about.html'        => '/about/',
		'faq.html'          => '/faq/',
		'it-courses.html'   => '/it-courses/',
		'en-courses.html'   => '/english-courses/',
		'camps.html'        => '/camps/',
		'media-center.html' => '/media-center/',
		'article.html'      => '/media-center/',
		'it-course.html'    => '/courses/',
		'camp.html'         => '/camps/',
		'city.html'         => '/',
	);

	public static function renderPage( string $source ): void {
		$markup = self::read( $source );

		if ( '' === $markup ) {
			return;
		}

		if ( 'index' === $source ) {
			$markup = self::applyHomepageValues( $markup );
		}

		$markup = self::replacePrivacyPolicyLinks( Logika_Theme_Page_Content::apply( $markup, $source ) );
		if ( 'index' === $source || 'en-courses' === $source ) {
			$markup = self::applyEnglishCourseContext( $markup );
		}

		$markup = Logika_Theme_Testimonials::apply( self::routeNavigationLinks( self::applyLeadForms( $markup ), $source ) );

		if ( preg_match( '#<main(?:\s[^>]*)?>.*?</main>#is', $markup, $matches ) ) {
			echo self::rewriteAssets( $matches[0] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	public static function renderFragment( string $fragment ): void {
		echo self::rewriteAssets( self::replacePrivacyPolicyLinks( self::routeNavigationLinks( self::read( $fragment ), $fragment ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	public static function routeNavigationLinks( string $markup, string $source ): string {
		return (string) preg_replace_callback(
			'~<a(?P<attributes>[^>]*\bhref=(?P<quote>["\'])#(?P=quote)[^>]*)>(?P<body>.*?)</a>~s',
			static function ( array $matches ) use ( $source ): string {
				preg_match( '#\bclass=(["\'])(.*?)\1#', $matches['attributes'], $class );
				$route = self::navigationRoute( $class[2] ?? '', wp_strip_all_tags( $matches['body'] ), $source );

				return $route ? str_replace( 'href=' . $matches['quote'] . '#' . $matches['quote'], 'href=' . $matches['quote'] . esc_url( home_url( $route ) ) . $matches['quote'], $matches[0] ) : $matches[0];
			},
			$markup
		);
	}

	private static function navigationRoute( string $class, string $label, string $source ): string {
		if ( str_contains( $class, 'services-section__item-about' ) || ( str_contains( $class, 'course-card__btn' ) && str_contains( $class, 'btn--bordered' ) ) ) {
			return '/it-courses/';
		}

		if ( str_contains( $class, 'english-section__link' ) ) {
			return '/english-courses/';
		}

		if ( str_contains( $class, 'english-level__link' ) || str_contains( $class, 'test-section__link' ) ) {
			return '/#lead-form';
		}

		if ( str_contains( $class, 'archive-section__promo-link' ) || str_contains( $class, 'article-section__promo-btn' ) ) {
			return '/camps/';
		}

		if ( str_contains( $class, 'articles-section__btn' ) ) {
			return '/blog/';
		}

		if ( str_contains( $class, 'news-section__btn' ) ) {
			return '/media-center/';
		}

		if ( str_contains( $class, 'menu-link' ) && 'Курси' === trim( $label ) ) {
			return '/it-courses/';
		}

		if ( str_contains( $class, 'btn' ) && ( 'footer' === $source || str_contains( $class, 'header__lesson' ) || str_contains( $class, 'lesson' ) || str_contains( $class, 'transformation' ) || str_contains( $class, 'onboarding' ) || str_contains( $class, 'media-section__btn' ) || str_contains( $class, 'btn--violet' ) || ( str_contains( $class, 'course-card__btn' ) && 'it-courses' === $source ) ) ) {
			return '/#lead-form';
		}

		return '';
	}

	private static function applyEnglishCourseContext( string $markup ): string {
		$course_ids = array();
		foreach ( get_posts( array( 'post_type' => 'course', 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids', 'no_found_rows' => true ) ) as $id ) {
			$course_ids[ sanitize_title( get_the_title( $id ) ) ] = $id;
		}

		return (string) preg_replace_callback(
			'#(<div class="english-level">.*?<span class="h4">)(.*?)(</span>.*?<a)([^>]*\bclass=(?:["\'])[^"\']*english-level__link[^"\']*(?:["\'])[^>]*)>#s',
			static function ( array $matches ) use ( $course_ids ): string {
				if ( str_contains( $matches[4], 'data-logika-course-id=' ) ) {
					return $matches[0];
				}

				$id = $course_ids[ sanitize_title( wp_strip_all_tags( $matches[2] ) ) ] ?? 0;

				return $id ? $matches[1] . $matches[2] . $matches[3] . $matches[4] . ' data-logika-course-id="' . esc_attr( (string) $id ) . '">' : $matches[0];
			},
			$markup
		);
	}

	public static function sourceForCurrentPage(): ?string {
		if ( ! is_page() ) {
			return null;
		}

		$slug = get_post_field( 'post_name', get_queried_object_id() );

		return self::PAGE_SOURCES[ $slug ] ?? null;
	}

	private static function read( string $source ): string {
		$path = get_template_directory() . "/source-pages/{$source}.php";

		if ( ! is_readable( $path ) ) {
			return '';
		}

		ob_start();
		require $path;

		return (string) ob_get_clean();
	}

	private static function applyLeadForms( string $markup ): string {
		return (string) preg_replace_callback(
			'#<form\b(?=[^>]*(?:banner-section__form|cta-form|camp-booking__form))(?P<attributes>[^>]*)>(?P<body>.*?)</form>#s',
			static function ( array $matches ): string {
				if ( str_contains( $matches['attributes'], 'data-logika-lead-form' ) ) {
					return $matches[0];
				}

				$body = self::replaceLeadInputs( $matches['body'] );
				$body .= '<input type="hidden" name="form_id" value="consultation"><input type="hidden" name="consent_accepted" value="1"><input type="hidden" name="consent_text_version" value="v1"><input type="hidden" name="idempotency_key" value=""><input class="main-form__honeypot" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true"><p class="main-form__status" aria-live="polite" hidden></p>';

				return '<form' . self::addMainFormClass( $matches['attributes'] ) . ' data-logika-lead-form novalidate>' . $body . '</form>';
			},
			$markup
		);
	}

	private static function addMainFormClass( string $attributes ): string {
		return (string) preg_replace_callback(
			'#\bclass=([\'\"])(?P<classes>[^\'\"]*)\1#',
			static fn( array $matches ): string => str_contains( $matches['classes'], 'main-form' ) ? $matches[0] : 'class="' . esc_attr( trim( $matches['classes'] . ' main-form' ) ) . '"',
			$attributes,
			1
		);
	}

	private static function replaceLeadInputs( string $body ): string {
		$body = (string) preg_replace( '#<input\b(?=[^>]*\btype=[\'\"]tel[\'\"])[^>]*>#', '<div class="main-form__phone-wrap"><input class="main-form__input main-form__phone" type="tel" name="tel" placeholder="Номер телефону" data-logika-phone-input aria-describedby="logika-phone-error" required><span class="main-form__phone-error" id="logika-phone-error" data-logika-phone-error hidden>Введіть коректний номер телефону</span></div>', $body, 1 );
		$body = (string) preg_replace( '#<select\b(?=[^>]*\bname=[\'\"]city[\'\"])[^>]*>.*?</select>|<input\b(?=[^>]*\bname=[\'\"](?:city|town)[\'\"])[^>]*>#s', Logika_Theme_Lead_Form::render_city_select(), $body, 1 );

		return (string) preg_replace( '#<input\b(?=[^>]*\bname=[\'\"]age[\'\"])[^>]*>#', Logika_Theme_Lead_Form::render_age_select(), $body, 1 );
	}

	private static function replacePrivacyPolicyLinks( string $markup ): string {
		$url = esc_url( home_url( '/privacy-policy/' ) );

		return (string) preg_replace(
			'~href=["\']#["\'](?=[^>]*>\s*Політик(?:ою|а)\s+конфіденційності\s*</a>)~u',
			'href="' . $url . '"',
			$markup
		);
	}

	private static function applyHomepageValues( string $markup ): string {
		$page_id = (int) get_option( 'page_on_front' );
		$title   = get_field( 'home_hero_title', $page_id );
		$text    = get_field( 'home_hero_text', $page_id );

		if ( $title ) {
			$markup = str_replace( 'Найбільша в Україні школа програмування для дітей 7-17 років', esc_html( $title ), $markup );
		}

		if ( $text ) {
			$markup = str_replace( 'Перші результати вже через 4 тижні', esc_html( $text ), $markup );
		}

		$markup = self::applyHomepageSectionText( $markup, $page_id );
		$markup = self::applyHomepageMediaCenter( $markup, $page_id );
		$markup = self::applyHomepagePortfolioItems( $markup, $page_id );
		$markup = (string) preg_replace( '#<section class="banner-section">#', '<section id="lead-form" class="banner-section">', $markup, 1 );

		$hero_boy_image = self::attachmentUrl( get_field( 'home_hero_boy_image_override', $page_id ), true );
		if ( $hero_boy_image ) {
			$markup = str_replace( 'img/boy-character.svg', esc_url( $hero_boy_image ), $markup );
		}

		$hero_character_image = self::attachmentUrl( get_field( 'home_hero_character_image_override', $page_id ), true );
		if ( $hero_character_image ) {
			$markup = str_replace( 'img/logika-character.svg', esc_url( $hero_character_image ), $markup );
		}

		$english_title = get_field( 'home_english_title', $page_id );
		$english_text  = get_field( 'home_english_text', $page_id );

		if ( $english_title ) {
			$markup = str_replace( 'Англiйська мова в Logika', esc_html( $english_title ), $markup );
		}

		if ( $english_text ) {
			$markup = str_replace(
				'Навчаємо говорити англійською впевнено з перших занять — через практику, живе спілкування та сучасні методики. Програма підбирається за віком і рівнем, щоб дитині було цікаво та комфортно вчитися.',
				esc_html( $english_text ),
				$markup
			);
		}

		$trust_defaults = array(
			array( 'text' => 'З 2018 року на ринку', 'icon' => 'banner-bar/icon-calendar-check.svg' ),
			array( 'text' => 'Освітня ліцензія', 'icon' => 'banner-bar/icon-document-certificate.svg' ),
			array( 'text' => '4.9 рейтинг від клієнтів', 'icon' => 'banner-bar/icon-rating-star.svg' ),
			array( 'text' => '178 шкіл в Україні', 'icon' => 'banner-bar/icon-outline_school.svg' ),
			array( 'text' => '130 міст по Україні', 'icon' => 'banner-bar/icon-map-location.svg' ),
			array( 'text' => '100тис+ успішних випускників', 'icon' => 'banner-bar/icon-tabler-school.svg' ),
		);

		foreach ( (array) get_field( 'home_trust_items', $page_id ) as $index => $item ) {
			if ( ! isset( $trust_defaults[ $index ] ) || ! is_array( $item ) ) {
				continue;
			}

			if ( ! empty( $item['text'] ) ) {
				$markup = str_replace( $trust_defaults[ $index ]['text'], esc_html( $item['text'] ), $markup );
			}

			$icon = ! empty( $item['icon_override'] ) ? self::attachmentUrl( $item['icon_override'], true ) : '';

			if ( $icon ) {
				$markup = str_replace( 'img/' . $trust_defaults[ $index ]['icon'], esc_url( $icon ), $markup );
			}
		}

		$markup = str_replace(
			'<form class="banner-section__form main-form">',
			'<form class="banner-section__form main-form" data-logika-lead-form novalidate>',
			$markup
		);
		$markup = str_replace(
			'<input class="main-form__input" type="tel" name="tel" placeholder="Номер телефону">',
			'<div class="main-form__phone-wrap"><input class="main-form__input main-form__phone" type="tel" name="tel" placeholder="Номер телефону" data-logika-phone-input aria-describedby="logika-phone-error"><span class="main-form__phone-error" id="logika-phone-error" data-logika-phone-error hidden>Введіть коректний номер телефону</span></div>',
			$markup
		);
		$markup = str_replace(
			'<input class="main-form__input main-form__phone" type="tel" name="tel" placeholder="Номер телефону" data-logika-phone-input>',
			'<div class="main-form__phone-wrap"><input class="main-form__input main-form__phone" type="tel" name="tel" placeholder="Номер телефону" data-logika-phone-input aria-describedby="logika-phone-error"><span class="main-form__phone-error" id="logika-phone-error" data-logika-phone-error hidden>Введіть коректний номер телефону</span></div>',
			$markup
		);
		$markup = str_replace(
			'<p class="main-form__text">',
			'<input type="hidden" name="form_id" value="trial_lesson"><input type="hidden" name="consent_accepted" value="1"><input type="hidden" name="consent_text_version" value="v1"><input type="hidden" name="idempotency_key" value=""><input class="main-form__honeypot" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true"><p class="main-form__text">',
			$markup
		);
		$markup = (string) preg_replace( '#(<p class="main-form__text">.*?</p>)(\s*</form>)#s', '$1<p class="main-form__status" aria-live="polite" hidden></p>$2', $markup, 1 );
		$markup = self::replaceHomepageAgeSelect( $markup, $page_id );

		return str_replace( '<span>Наступні курси</span>', '<p>Наступні курси</p>', $markup );
	}

	private static function replaceHomepageAgeSelect( string $markup, int $page_id ): string {
		$select = Logika_Theme_Lead_Form::render_age_select( $page_id );
		$markup = (string) preg_replace( '#<input class="main-form__input" type="text" name="age" placeholder="Вік дитини \(від 7 до 17\)">#', $select, $markup, 1 );

		return (string) preg_replace( '#<(?P<tag>span|div) class="main-form__select-wrap"[^>]*>.*?</(?P=tag)>#s', $select, $markup, 1 );
	}

	private static function applyHomepagePortfolioItems( string $markup, int $page_id ): string {
		$rows = array_filter( (array) get_field( 'home_portfolio_items', $page_id ), 'is_array' );

		if ( ! $rows ) {
			return $markup;
		}

		$cards = array_filter( array_map( static fn( array $row ): string => self::portfolioCard( $row ), $rows ) );

		if ( ! $cards ) {
			return $markup;
		}

		return (string) preg_replace( '#(<ul class="portfolio-section__slider">).*?(</ul>)#s', '$1' . implode( '', $cards ) . '$2', $markup, 1 );
	}

	private static function portfolioCard( array $row ): string {
		$name        = trim( (string) ( $row['student_name'] ?? '' ) );
		$age         = trim( (string) ( $row['student_age'] ?? '' ) );
		$course      = trim( (string) ( $row['course'] ?? '' ) );
		$topic       = trim( (string) ( $row['topic'] ?? '' ) );
		$description = trim( (string) ( $row['description'] ?? '' ) );
		$title       = trim( $name . ( '' !== $age ? ', ' . $age : '' ) );

		if ( '' === $title || '' === $course || '' === $description ) {
			return '';
		}

		if ( 'featured' === ( $row['variant'] ?? 'standard' ) ) {
			$video_url = esc_url( (string) ( $row['video_url'] ?? '' ) );
			$cta_label = trim( (string) ( $row['cta_label'] ?? '' ) ) ?: 'Безкоштовний пробний урок';
			$cta_url   = esc_url( (string) ( $row['cta_url'] ?? '#lead-form' ) );
			$video     = '' !== $video_url ? '<a class="portfolio-section__video" href="' . $video_url . '"><span aria-hidden="true">▶</span>Дивитись відеовідгук</a>' : '';
			$image     = self::portfolioImage( $row['project_image'] ?? 0, 'Гра, створена учнем ' . $name, 'large', 'portfolio-section__game' );

			return '<li class="portfolio-section__card portfolio-section__card--featured"><div class="portfolio-section__content"><span class="portfolio-section__tag">' . esc_html( $course ) . '</span><h3>' . esc_html( $title ) . '</h3><p>' . esc_html( $description ) . '</p>' . $video . '<a class="portfolio-section__trial btn btn--yellow" href="' . $cta_url . '">' . esc_html( $cta_label ) . '<svg width="20" height="20" aria-hidden="true"><use href="img/sprite/sprite.svg#arrow-right"></use></svg></a></div>' . $image . '</li>';
		}

		$image = self::portfolioImage( $row['student_image'] ?? 0, $name . ', учень курсу ' . $course, 'medium', '' );
		$topic = '' !== $topic ? '<span class="portfolio-section__tag portfolio-section__tag--topic">' . esc_html( $topic ) . '</span>' : '';

		return '<li class="portfolio-section__card"><h3>' . esc_html( $title ) . '</h3><div class="portfolio-section__photo">' . $image . '<span class="portfolio-section__tag portfolio-section__tag--course">' . esc_html( $course ) . '</span>' . $topic . '</div><p>' . esc_html( $description ) . '</p></li>';
	}

	private static function portfolioImage( mixed $attachment, string $alt, string $size, string $class ): string {
		$id = is_array( $attachment ) && isset( $attachment['ID'] ) ? (int) $attachment['ID'] : (int) $attachment;

		return $id > 0 ? wp_get_attachment_image( $id, $size, false, array( 'alt' => $alt, 'class' => $class, 'loading' => 'lazy' ) ) : '';
	}

	private static function applyHomepageSectionText( string $markup, int $page_id ): string {
		foreach (
			array(
				'home_form_badge' => 'Перший урок — безкоштовно.',
				'home_form_text' => 'Залиште заявку за 30 секунд — ми зателефонуємо і підберемо зручний час',
				'home_programming_title' => 'Курси програмування',
				'home_english_subtitle' => 'Не просто уроки, а мовне середовище',
				'home_transformation_title' => 'Дайте дитині шанс не просто грати, а створювати',
				'home_transformation_before_label' => 'Було',
				'home_transformation_before_text' => 'Просто грає в ігри',
				'home_transformation_after_label' => 'Стало',
				'home_transformation_after_text' => 'Створює власні ігри',
				'home_onboarding_title' => 'Як розпочати навчання',
				'home_testimonials_title' => 'Довіра, підтверджена результатами',
				'home_portfolio_title' => 'Проєкти наших учнів',
				'home_locations_text' => 'Наші школи у 130 містах України - знайдіть зручний варіант поруч із вами або навчайтесь онлайн.',
				'home_media_title' => 'Медіа-центр',
				'home_media_text' => 'Новини, події та корисні матеріали про навчання, розвиток дітей і світ технологій.',
				'home_cta_title' => 'Підберемо курс саме для вашої дитини!',
				'home_faq_title' => 'Питання та відповіді',
				'home_certificates_title' => 'Ура, подарункові сертифікати',
				'home_certificates_subtitle' => 'Вартість сертифікату на 2 місяці навчання = вартість 1-го місяця + вартість 2-го місяця зі знижкою 10%',
				'home_certificates_text' => "Залиште заявку, і ми зв'яжемось з вами: відповімо на будь-які запитання і допоможемо обрати курс ",
				'home_partners_title' => 'Наші партнери',
		) as $field => $default
	) {
			$markup = self::replaceFieldText( $markup, $page_id, $field, $default );
		}
		$markup = self::replacePatternFieldText( $markup, $page_id, 'home_locations_title', '#(<h2 id="school-map-title">)(.*?)(</h2>)#s' );

		$markup = self::replacePatternFieldText( $markup, $page_id, 'home_form_button', '#(<button class="main-form__btn btn btn--yellow">)(.*?)(\s*<svg)#s' );
		$markup = self::replacePatternFieldText( $markup, $page_id, 'home_form_consent', '~(<p class="main-form__text">)(.*?)(<a href="#">Політикою конфіденційності</a></p>)~s' );
		$markup = self::replacePatternFieldText( $markup, $page_id, 'home_english_cta_label', '~(<a href="#" class="english-section__link btn btn--yellow">)(.*?)(\s*<svg)~s' );
		$markup = self::replacePatternFieldText( $markup, $page_id, 'home_transformation_cta_label', '~(<a href="#" class="transformation-section__link btn btn--yellow">)(.*?)(\s*<svg)~s' );
		$markup = self::replacePatternFieldText( $markup, $page_id, 'home_certificates_button', '~(<a class="btn btn--violet" href="#">)(.*?)(\s*<svg)~s' );
		$markup = self::replaceEnglishNextLabel( $markup, $page_id );

		$markup = self::applyRepeaterRows(
			$markup,
			(array) get_field( 'home_marquee_items', $page_id ),
			array(
				array( 'text' => 'Перший урок – безкоштовно' ),
				array( 'text' => 'Навчання з результатом' ),
				array( 'text' => 'Уроки з живими викладачами' ),
				array( 'text' => 'Інтерактивне навчання' ),
				array( 'text' => 'Програмування для дітей 7-17' ),
				array( 'text' => 'Англійська мова' ),
			),
			array( 'text' )
		);

		$markup = self::applyProgrammingRows(
			$markup,
			(array) get_field( 'home_programming_courses', $page_id ),
			array(
				array( 'age' => '7-8 рокiв', 'title' => 'Перший крок у свiт технологiй', 'text' => 'Допомагаємо дитині зробити перші впевнені кроки у світі технологій, розвиваючи логіку, увагу та базові комп’ютерні навички.', 'lesson_label' => 'Запис на безкоштовний урок', 'about_label' => 'Ознайомитись з курсами', 'image_webp' => 'img/services/service1.webp', 'image' => 'img/services/service1.png', 'icon' => 'img/services/service1.svg' ),
				array( 'age' => '9-11 рокiв', 'title' => 'Вiд iгор до власних проектiв', 'text' => 'Діти переходять від ігор до створення власних проєктів, розвивають креативне мислення та вчаться працювати з різними цифровими інструментами.', 'lesson_label' => 'Запис на безкоштовний урок', 'about_label' => 'Ознайомитись з курсами', 'image_webp' => 'img/services/service1.webp', 'image' => 'img/services/service1.png', 'icon' => 'img/services/service1.svg' ),
				array( 'age' => '12-14 рокiв', 'title' => 'Серйознi навички для серйозних цiлей', 'text' => 'Поглиблюємо знання в програмуванні, працюємо над реальними проєктами та формуємо навички, які стануть основою для подальшого розвитку в IT.', 'lesson_label' => 'Запис на безкоштовний урок', 'about_label' => 'Ознайомитись з курсами', 'image_webp' => 'img/services/service1.webp', 'image' => 'img/services/service1.png', 'icon' => 'img/services/service1.svg' ),
				array( 'age' => '14-17 рокiв', 'title' => "Перший крок у IT-кар'єру", 'text' => 'Готуємо до перших кроків у кар’єрі: даємо практичні знання, знайомимо з сучасними технологіями та вчимо мислити як розробник.', 'lesson_label' => 'Запис на безкоштовний урок', 'about_label' => 'Ознайомитись з курсами', 'image_webp' => 'img/services/service1.webp', 'image' => 'img/services/service1.png', 'icon' => 'img/services/service1.svg' ),
			),
		);

		$markup = self::applyEnglishRows(
			$markup,
			(array) get_field( 'home_english_levels', $page_id ),
			array(
				array( 'age' => '8-10 років', 'title' => 'Рівень А0', 'text' => 'Перші слова, фрази та знайомство з англійською', 'image' => 'img/english-courses/A0.svg' ),
				array( 'age' => '10-12 років', 'title' => 'Рівень А1', 'text' => 'Базове спілкування, прості речення та щоденні теми', 'image' => 'img/english-courses/A1.svg' ),
				array( 'age' => '10-12 років', 'title' => 'Рівень А2', 'text' => 'Більше практики та словникового запасу', 'image' => 'img/english-courses/A2.svg' ),
				array( 'age' => '13-15 років', 'title' => 'Рівень B1', 'text' => 'Вільніше спілкування та розуміння живої мови', 'image' => 'img/english-courses/B1.svg' ),
				array( 'age' => '15-17 років', 'title' => 'Рівень B2', 'text' => 'Впевнене володіння мовою та спілкування', 'image' => 'img/english-courses/B2.svg' ),
			)
		);

		$markup = self::replacePictureImage( $markup, get_field( 'home_transformation_before_image_override', $page_id ), 'img/transformation/before.webp', 'img/transformation/before.png', true );
		$markup = self::replacePictureImage( $markup, get_field( 'home_transformation_after_image_override', $page_id ), 'img/transformation/after.webp', 'img/transformation/after.png', true );

		$markup = self::applyOnboardingRows(
			$markup,
			(array) get_field( 'home_onboarding_steps', $page_id ),
			array(
				array( 'title' => 'Залишiть заявку', 'text' => "Заповнiть форму або зателефонуйте. Менеджер зв'яжеться за 30 хвилин", 'button' => 'Залишити заявку', 'image' => 'img/onbording/onbording1.svg' ),
				array( 'title' => 'Відвідайте безкоштовний пробний урок', 'text' => "Дитина спробує, ви подивитесь. Жодних зобов'язань", 'button' => 'Залишити заявку', 'image' => 'img/onbording/onbording2.svg' ),
				array( 'title' => 'Розпочніть навчання та отримайте результат', 'text' => 'Регулярнi заняття, власнi проекти, вiдкритi уроки для батькiв', 'button' => 'Залишити заявку', 'image' => 'img/onbording/onbording3.svg' ),
			),
		);

		$markup = self::replacePictureImage( $markup, get_field( 'home_certificates_image_override', $page_id ), 'img/certificates/certificate.webp', 'img/certificates/certificate.png', true );
		$markup = self::applyPartnerRows(
			$markup,
			(array) get_field( 'home_partners_items', $page_id ),
			array(
				array( 'webp' => 'img/Partners/think.webp', 'image' => 'img/Partners/think.png' ),
				array( 'webp' => 'img/Partners/1+1.webp', 'image' => 'img/Partners/1+1.png' ),
				array( 'webp' => 'img/Partners/Free.webp', 'image' => 'img/Partners/Free.png' ),
				array( 'webp' => 'img/Partners/club.webp', 'image' => 'img/Partners/club.png' ),
				array( 'webp' => 'img/Partners/ed.webp', 'image' => 'img/Partners/ed.png' ),
				array( 'webp' => 'img/Partners/basis.webp', 'image' => 'img/Partners/basis.png' ),
				array( 'webp' => 'img/Partners/fond.webp', 'image' => 'img/Partners/fond.png' ),
				array( 'webp' => 'img/Partners/mriya.webp', 'image' => 'img/Partners/mriya.png' ),
			)
		);

		return self::applyFaqRows(
			$markup,
			(array) get_field( 'home_faq_items', $page_id ),
			array(
				array( 'question' => 'Скільки коштує навчання в Logika?', 'answer' => 'Вартість залежить від обраного формату навчання, пакета занять, міста, можливих пільг і поточних знижок. Залиште заявку, і менеджер допоможе підібрати оптимальний варіант та розрахує фінальну вартість.' ),
				array( 'question' => 'З якого віку можна навчатися в Logika?', 'answer' => 'Вартість залежить від обраного формату навчання, пакета занять, міста, можливих пільг і поточних знижок. Залиште заявку, і менеджер допоможе підібрати оптимальний варіант та розрахує фінальну вартість.' ),
				array( 'question' => 'Чи потрібен досвід у програмуванні або англійській?', 'answer' => 'Вартість залежить від обраного формату навчання, пакета занять, міста, можливих пільг і поточних знижок. Залиште заявку, і менеджер допоможе підібрати оптимальний варіант та розрахує фінальну вартість.' ),
				array( 'question' => 'Як проходять заняття?', 'answer' => 'Вартість залежить від обраного формату навчання, пакета занять, міста, можливих пільг і поточних знижок. Залиште заявку, і менеджер допоможе підібрати оптимальний варіант та розрахує фінальну вартість.' ),
				array( 'question' => 'Що отримає дитина під час навчання?', 'answer' => 'Вартість залежить від обраного формату навчання, пакета занять, міста, можливих пільг і поточних знижок. Залиште заявку, і менеджер допоможе підібрати оптимальний варіант та розрахує фінальну вартість.' ),
				array( 'question' => 'Як записатися на безкоштовний пробний урок?', 'answer' => 'Вартість залежить від обраного формату навчання, пакета занять, міста, можливих пільг і поточних знижок. Залиште заявку, і менеджер допоможе підібрати оптимальний варіант та розрахує фінальну вартість.' ),
				array( 'question' => 'Чим Logika відрізняється від інших шкіл?', 'answer' => 'Вартість залежить від обраного формату навчання, пакета занять, міста, можливих пільг і поточних знижок. Залиште заявку, і менеджер допоможе підібрати оптимальний варіант та розрахує фінальну вартість.' ),
			)
		);
	}

	private static function replaceFieldText( string $markup, int $page_id, string $field, string $default ): string {
		$value = trim( (string) get_field( $field, $page_id ) );

		return '' === $value ? $markup : str_replace( $default, esc_html( $value ), $markup );
	}

	private static function applyHomepageMediaCenter( string $markup, int $page_id ): string {
		$markup  = self::replaceMediaTopLink( $markup, $page_id, 'home_media_lesson_link', 'media-section__btn-lesson btn btn--yellow' );
		$markup  = self::replaceMediaTopLink( $markup, $page_id, 'home_media_archive_link', 'media-section__btn-about btn' );
		$news    = (array) get_field( 'home_media_news', $page_id );
		$contest = (array) get_field( 'home_media_contest', $page_id );
		$offer   = (array) get_field( 'home_media_offer', $page_id );
		$discount = (array) get_field( 'home_media_discount', $page_id );
		$race    = (array) get_field( 'home_media_race', $page_id );
		$cards   = implode( '', array_map( static fn( int $id ): string => self::homeMediaPost( $id ), self::homeMediaPosts( $page_id ) ) );

		$layout = '<div class="media-section__news"><article class="media-section__feature"><div class="media-section__feature-tags"><span>' . esc_html( self::mediaValue( $news, 'tag_primary', 'Logika Новини' ) ) . '</span><span>' . esc_html( self::mediaValue( $news, 'tag_secondary', 'Корисне для батьків' ) ) . '</span></div><img class="media-section__figma-art" src="' . esc_url( self::mediaImage( $news, 'image', 'img/media-center/figma/news.svg' ) ) . '" alt=""></article><div class="media-section__feature-copy"><h3>' . esc_html( self::mediaValue( $news, 'title', 'Що нового у Logika' ) ) . '</h3><p>' . esc_html( self::mediaValue( $news, 'text', 'Актуальні новини, події та важливі оновлення школи.' ) ) . '</p>' . self::mediaLink( $news, 'link', '/media-center/', 'Перейти до розділу' ) . '</div><article class="media-section__contest"><img class="media-section__background" src="' . esc_url( self::mediaImage( $contest, 'image', 'img/media-center/figma/contest-art.svg' ) ) . '" alt="" aria-hidden="true"><span class="media-section__label">' . esc_html( self::mediaValue( $contest, 'label', 'Logika Конкурси' ) ) . '</span><div><h3>' . nl2br( esc_html( self::mediaValue( $contest, 'title', "Конкурси від\nLogika" ) ) ) . '</h3><p>' . esc_html( self::mediaValue( $contest, 'text', 'Беріть участь у хакатонах, змаганнях, творчих ініціативах і отримуйте нагороди за призові місця.' ) ) . '</p></div>' . self::mediaLink( $contest, 'link', '/media-center/', 'Переглянути усі конкурси' ) . '</article></div><div class="media-section__promos"><article class="media-section__promo media-section__promo--offer"><span class="media-section__label">' . esc_html( self::mediaValue( $offer, 'label', 'Акція' ) ) . '</span><img class="media-section__background" src="' . esc_url( self::mediaImage( $offer, 'background', 'img/media-center/figma/offer-background.svg' ) ) . '" alt=""><img class="media-section__figma-art" src="' . esc_url( self::mediaImage( $offer, 'image', 'img/media-center/figma/offer.svg' ) ) . '" alt=""><div><h3>' . esc_html( self::mediaValue( $offer, 'title', '1=2' ) ) . '</h3><h4>' . esc_html( self::mediaValue( $offer, 'subtitle', 'на усі курси 14-17 років' ) ) . '</h4><p>' . esc_html( self::mediaValue( $offer, 'text', 'Купуйте один курс та отримуйте другий у подарунок' ) ) . '</p></div>' . self::mediaLink( $offer, 'link', '#', 'Дізнатись більше' ) . '</article><article class="media-section__promo media-section__promo--discount"><span class="media-section__label">' . esc_html( self::mediaValue( $discount, 'label', 'Акція' ) ) . '</span><img class="media-section__figma-art" src="' . esc_url( self::mediaImage( $discount, 'image', 'img/media-center/figma/discount.svg' ) ) . '" alt=""><div><h3>' . esc_html( self::mediaValue( $discount, 'title', '-10%' ) ) . '</h3><h4>' . esc_html( self::mediaValue( $discount, 'subtitle', 'на обрані курси' ) ) . '</h4><p>' . esc_html( self::mediaValue( $discount, 'text', 'Спробуйте навчання за вигідною ціною' ) ) . '</p></div>' . self::mediaLink( $discount, 'link', '#', 'Дізнатись більше' ) . '</article><article class="media-section__race"><span class="media-section__label">' . esc_html( self::mediaValue( $race, 'label', 'Конкурс' ) ) . '</span><img class="media-section__figma-art" src="' . esc_url( self::mediaImage( $race, 'image', 'img/media-center/figma/race.svg' ) ) . '" alt=""><h3>' . nl2br( esc_html( self::mediaValue( $race, 'title', "LogiRace\n2026" ) ) ) . '</h3><p>' . esc_html( self::mediaValue( $race, 'text', 'Уявіть майбутнє і створіть свій світ на Червоній планеті: ландшафт, технології, роботів, транспорт, ресурси та екосистему.' ) ) . '</p>' . self::mediaLink( $race, 'link', '#', 'Дізнатись більше' ) . '</article></div><div class="media-section__blog-list">' . $cards . '</div>';

		return (string) preg_replace( '#(<section class="media-section">.*?<div class="media-section__cards-layout">).*?(</div>\s*</div>\s*</div>\s*</div>\s*</section>)#s', '$1' . $layout . '$2', $markup, 1 );
	}

	private static function replaceMediaTopLink( string $markup, int $page_id, string $field, string $class ): string {
		$link = get_field( $field, $page_id );
		if ( ! is_array( $link ) || empty( $link['url'] ) ) {
			return $markup;
		}

		return (string) preg_replace_callback(
			'#<a\s+href=["\'][^"\']*["\'](\s+class=["\']' . preg_quote( $class, '#' ) . '["\']>)(.*?)(\s*<svg\b.*?</svg>)#s',
			static function ( array $matches ) use ( $link ): string {
				$target = ! empty( $link['target'] ) ? ' target="' . esc_attr( (string) $link['target'] ) . '" rel="noopener noreferrer"' : '';

				return '<a href="' . esc_url( (string) $link['url'] ) . '"' . str_replace( '>', $target . '>', $matches[1] ) . esc_html( (string) ( $link['title'] ?: $matches[2] ) ) . $matches[3];
			},
			$markup,
			1
		);
	}

	private static function mediaValue( array $fields, string $field, string $default ): string {
		$value = trim( (string) ( $fields[ $field ] ?? '' ) );

		return '' === $value ? $default : $value;
	}

	private static function mediaImage( array $fields, string $field, string $default ): string {
		return self::attachmentUrl( $fields[ $field ] ?? 0, true ) ?: get_template_directory_uri() . '/assets/' . $default;
	}

	private static function mediaLink( array $fields, string $field, string $default_url, string $default_label ): string {
		$link   = $fields[ $field ] ?? array();
		$url    = is_array( $link ) && ! empty( $link['url'] ) ? (string) $link['url'] : $default_url;
		$label  = is_array( $link ) && ! empty( $link['title'] ) ? (string) $link['title'] : $default_label;
		$target = is_array( $link ) && ! empty( $link['target'] ) ? ' target="' . esc_attr( (string) $link['target'] ) . '" rel="noopener noreferrer"' : '';

		return '<a href="' . esc_url( $url ) . '" class="btn btn--yellow"' . $target . '>' . esc_html( $label ) . ' <svg width="20" height="20"><use href="img/sprite/sprite.svg#arrow-right"></use></svg></a>';
	}

	/** @return int[] */
	private static function homeMediaPosts( int $page_id ): array {
		$selected = array_values( array_unique( array_filter( array_map( 'absint', (array) get_field( 'home_media_posts', $page_id ) ) ) ) );

		if ( $selected ) {
			return array_values( array_filter( $selected, static fn( int $id ): bool => 'post' === get_post_type( $id ) && 'publish' === get_post_status( $id ) && ! get_post_meta( $id, 'post_hide_from_blog', true ) ) );
		}

		return array_map(
			'absint',
			get_posts(
				array(
					'post_type'      => 'post',
					'post_status'    => 'publish',
					'posts_per_page' => 3,
					'orderby'        => 'date',
					'order'          => 'DESC',
					'fields'         => 'ids',
					'meta_query'     => array(
						'relation' => 'OR',
						array( 'key' => 'post_hide_from_blog', 'compare' => 'NOT EXISTS' ),
						array( 'key' => 'post_hide_from_blog', 'value' => '0', 'compare' => '=' ),
					),
				)
			)
		);
	}

	private static function homeMediaPost( int $post_id ): string {
		$title   = get_the_title( $post_id );
		$cover   = self::attachmentUrl( get_field( 'article_cover_image', $post_id ), true ) ?: (string) get_the_post_thumbnail_url( $post_id, 'large' );
		$tags    = get_the_tags( $post_id );
		$label   = is_array( $tags ) && isset( $tags[0] ) ? $tags[0]->name : 'Logika Блог';
		$minutes = (int) get_field( 'article_reading_minutes', $post_id );
		$minutes = $minutes > 0 ? $minutes : max( 1, (int) ceil( str_word_count( wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) ) ) / 180 ) );
		$views   = (int) get_post_meta( $post_id, 'article_view_count', true );
		$image   = $cover ? '<img src="' . esc_url( $cover ) . '" alt="' . esc_attr( $title ) . '">' : '';

		return '<a class="media-section__post" href="' . esc_url( get_permalink( $post_id ) ) . '">' . $image . '<span class="media-section__label">' . esc_html( $label ) . '</span><h3>' . esc_html( $title ) . '</h3><p><span><img src="img/media-center/proicons_calendar.svg" alt="">' . esc_html( get_the_date( 'd.m.Y', $post_id ) ) . '</span><span><img src="img/media-center/formkit_time.svg" alt="">' . esc_html( $minutes . ' хв' ) . '</span><span><img src="img/media-center/proicons_eye.svg" alt="">' . esc_html( (string) $views ) . '</span></p></a>';
	}

	private static function replacePatternFieldText( string $markup, int $page_id, string $field, string $pattern ): string {
		$value = trim( (string) get_field( $field, $page_id ) );

		if ( '' === $value ) {
			return $markup;
		}

		return (string) preg_replace_callback(
			$pattern,
			static fn( array $matches ): string => $matches[1] . esc_html( $value ) . $matches[3],
			$markup,
			1
		);
	}

	private static function replaceEnglishNextLabel( string $markup, int $page_id ): string {
		$value = trim( (string) get_field( 'home_english_next_label', $page_id ) );

		if ( '' !== $value ) {
			return (string) preg_replace(
				'#(<button class="swiper-btn swiper-button-next">)<span>.*?</span>#s',
				'$1<p>' . esc_html( $value ) . '</p>',
				$markup,
				1
			);
		}

		return (string) preg_replace( '#(<button class="swiper-btn swiper-button-next">)<span>(.*?)</span>#s', '$1<p>$2</p>', $markup, 1 );
	}

	/**
	 * @param array<int, mixed> $rows
	 * @param array<int, array<string, string>> $defaults
	 */
	private static function applyProgrammingRows( string $markup, array $rows, array $defaults ): string {
		$index = 0;

		return (string) preg_replace_callback(
			'#<li class="services-section__item">.*?</li>#s',
			static function ( array $matches ) use ( $rows, $defaults, &$index ): string {
				$item = $matches[0];
				$row = $rows[ $index ] ?? null;
				$default = $defaults[ $index ] ?? null;
				$index++;

				if ( ! is_array( $row ) || ! is_array( $default ) ) {
					return $item;
				}

				foreach ( array( 'age', 'title', 'text', 'lesson_label', 'about_label' ) as $key ) {
					$value = trim( (string) ( $row[ $key ] ?? '' ) );
					if ( '' !== $value && isset( $default[ $key ] ) ) {
						$item = str_replace( $default[ $key ], esc_html( $value ), $item );
					}
				}

				$tags = self::lines( (string) ( $row['tags'] ?? '' ) );
				if ( $tags ) {
					$list = implode( '', array_map( static fn( string $tag ): string => '<li class="h5">' . esc_html( $tag ) . '</li>', $tags ) );
					$item = (string) preg_replace( '#<ul class="services-section__item-tags">.*?</ul>#s', '<ul class="services-section__item-tags">' . $list . '</ul>', $item, 1 );
				}

				$item = self::replacePictureImage( $item, $row['image_override'] ?? 0, $default['image_webp'] ?? '', $default['image'] ?? '', true );
				$item = self::replaceImagePath( $item, $row['icon_override'] ?? 0, $default['icon'] ?? '', true );

				return $item;
			},
			$markup
		);
	}

	/**
	 * @param array<int, mixed> $rows
	 * @param array<int, array<string, string>> $defaults
	 */
	private static function applyEnglishRows( string $markup, array $rows, array $defaults ): string {
		$index = 0;

		return (string) preg_replace_callback(
			'#<li class=[\'\"]swiper-slide[\'\"]>\s*<div class="english-level">.*?</div>\s*</li>#s',
			static function ( array $matches ) use ( $rows, $defaults, &$index ): string {
				$item = $matches[0];
				$row = $rows[ $index ] ?? null;
				$default = $defaults[ $index ] ?? null;
				$index++;

				if ( ! is_array( $row ) || ! is_array( $default ) ) {
					return $item;
				}

				foreach ( array( 'age', 'title', 'text' ) as $key ) {
					$value = trim( (string) ( $row[ $key ] ?? '' ) );
					if ( '' !== $value && isset( $default[ $key ] ) ) {
						$item = str_replace( $default[ $key ], esc_html( $value ), $item );
					}
				}

				return self::replaceImagePath( $item, $row['image_override'] ?? 0, $default['image'] ?? '', true );
			},
			$markup
		);
	}

	/**
	 * @param array<int, mixed> $rows
	 * @param array<int, array<string, string>> $defaults
	 */
	private static function applyOnboardingRows( string $markup, array $rows, array $defaults ): string {
		$index = 0;

		return (string) preg_replace_callback(
			'#<li class="onboarding-section__item">.*?</li>#s',
			static function ( array $matches ) use ( $rows, $defaults, &$index ): string {
				$item = $matches[0];
				$row = $rows[ $index ] ?? null;
				$default = $defaults[ $index ] ?? null;
				$index++;

				if ( ! is_array( $row ) || ! is_array( $default ) ) {
					return $item;
				}

				foreach ( array( 'title', 'text', 'button' ) as $key ) {
					$value = trim( (string) ( $row[ $key ] ?? '' ) );
					if ( '' !== $value && isset( $default[ $key ] ) ) {
						$item = str_replace( $default[ $key ], esc_html( $value ), $item );
					}
				}

				return self::replaceImagePath( $item, $row['image_override'] ?? 0, $default['image'] ?? '', true );
			},
			$markup
		);
	}

	/**
	 * @param array<int, mixed> $rows
	 * @param array<int, array<string, string>> $defaults
	 */
	private static function applyPartnerRows( string $markup, array $rows, array $defaults ): string {
		$index = 0;

		return (string) preg_replace_callback(
			'#<li>\s*<picture>.*?img/Partners/.*?</picture>\s*</li>#s',
			static function ( array $matches ) use ( $rows, $defaults, &$index ): string {
				$item = $matches[0];
				$row = $rows[ $index ] ?? null;
				$default = $defaults[ $index ] ?? null;
				$index++;

				if ( ! is_array( $row ) || ! is_array( $default ) ) {
					return $item;
				}

				return self::replacePictureImage( $item, $row['image_override'] ?? 0, $default['webp'] ?? '', $default['image'] ?? '', true );
			},
			$markup
		);
	}

	/**
	 * @param array<int, mixed> $rows
	 * @param array<int, array<string, string>> $defaults
	 */
	private static function applyFaqRows( string $markup, array $rows, array $defaults ): string {
		$index = 0;

		return (string) preg_replace_callback(
			'#<li class=[\'\"]accordion__item[\'\"]>.*?</li>#s',
			static function ( array $matches ) use ( $rows, $defaults, &$index ): string {
				$item = $matches[0];
				$row = $rows[ $index ] ?? null;
				$index++;

				if ( ! is_array( $row ) || ! isset( $defaults[ $index - 1 ] ) ) {
					return $item;
				}

				$question = trim( (string) ( $row['question'] ?? '' ) );
				$answer = trim( (string) ( $row['answer'] ?? '' ) );

				if ( '' !== $question ) {
					$item = (string) preg_replace( '#(<button class=[\'\"]accordion__btn h5[\'\"] data-id=[\'\"]\d+[\'\"]>).*?(</button>)#s', '$1' . esc_html( $question ) . '$2', $item, 1 );
				}

				if ( '' !== $answer ) {
					$item = (string) preg_replace( '#(<div class="editor">\s*<p>).*?(</p>\s*</div>)#s', '$1' . esc_html( $answer ) . '$2', $item, 1 );
				}

				return $item;
			},
			$markup
		);
	}

	/**
	 * @return string[]
	 */
	private static function lines( string $value ): array {
		return array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n|,/', $value ) ?: array() ) ) );
	}

	/**
	 * @param array<int, mixed> $rows
	 * @param array<int, array<string, string>> $defaults
	 * @param string[] $keys
	 */
	private static function applyRepeaterRows( string $markup, array $rows, array $defaults, array $keys ): string {
		foreach ( $rows as $index => $row ) {
			if ( ! is_array( $row ) || ! isset( $defaults[ $index ] ) ) {
				continue;
			}

			foreach ( $keys as $key ) {
				$value = trim( (string) ( $row[ $key ] ?? '' ) );
				if ( '' !== $value && isset( $defaults[ $index ][ $key ] ) ) {
					$markup = str_replace( $defaults[ $index ][ $key ], esc_html( $value ), $markup );
				}
			}
		}

		return $markup;
	}

	private static function attachmentUrl( mixed $attachment, bool $is_override = false ): string {
		$id = is_array( $attachment ) && isset( $attachment['ID'] ) ? (int) $attachment['ID'] : (int) $attachment;

		if ( ! $is_override && $id > 0 && str_starts_with( wp_specialchars_decode( get_the_title( $id ), ENT_QUOTES ), 'Головна ' ) ) {
			return '';
		}

		$url = $id > 0 ? wp_get_attachment_image_url( $id, 'full' ) : false;

		return $url ? (string) $url : '';
	}

	private static function replaceImagePath( string $markup, mixed $attachment, string $path, bool $is_override = false ): string {
		$url = self::attachmentUrl( $attachment, $is_override );

		return '' === $url || '' === $path ? $markup : str_replace( $path, esc_url( $url ), $markup );
	}

	private static function replacePictureImage( string $markup, mixed $attachment, string $webp_path, string $image_path, bool $is_override = false ): string {
		$url = self::attachmentUrl( $attachment, $is_override );

		if ( '' === $url || '' === $image_path ) {
			return $markup;
		}

		return str_replace( array_filter( array( $webp_path, $image_path ) ), esc_url( $url ), $markup );
	}

	private static function rewriteAssets( string $markup ): string {
		$assets = esc_url( get_template_directory_uri() . '/assets/img/' );

		foreach ( self::ROUTES as $source => $route ) {
			$markup = str_replace( "href=\"/{$source}\"", 'href="' . esc_url( home_url( $route ) ) . '"', $markup );
			$markup = str_replace( "href=\"{$source}\"", 'href="' . esc_url( home_url( $route ) ) . '"', $markup );
		}

		return (string) preg_replace( '#(["\'])img/#', '$1' . $assets, $markup );
	}
}
