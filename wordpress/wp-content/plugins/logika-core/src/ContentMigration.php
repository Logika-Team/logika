<?php

declare(strict_types=1);

namespace Logika\Core;

use DOMDocument;
use DOMElement;
use DOMXPath;
use WP_CLI;
use WP_Post;

final class ContentMigration {
	private const PLACEHOLDERS = array(
		'ACF test',
		'About ACF benefits title',
		'IT ACF reviews title',
		'English ACF test text',
		'FAQ ACF CTA title',
		'Media ACF discount title',
		'+380 00 000 00 00',
		'© 2026 Logika. Всі права захищені',
	);

	private static array $report = array();
	private static array $assets = array();

	public static function register(): void {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			WP_CLI::add_command( 'logika acf-migrate', array( self::class, 'cli' ) );
			WP_CLI::add_command( 'logika acf-migrate-reviews', array( self::class, 'cliReviews' ) );
			WP_CLI::add_command( 'logika article-faqs seed', array( self::class, 'cliArticleFaqs' ) );
		}
	}

	public static function cli( array $args, array $assoc_args ): void {
		WP_CLI::log( (string) wp_json_encode( self::run( isset( $assoc_args['dry-run'] ) ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
	}

	public static function cliReviews( array $args, array $assoc_args ): void {
		WP_CLI::log( (string) wp_json_encode( self::migrateReviewsPresentation( isset( $assoc_args['dry-run'] ) ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
	}

	public static function cliArticleFaqs( array $args, array $assoc_args ): void {
		WP_CLI::log( (string) wp_json_encode( self::seedArticleFaqs( isset( $assoc_args['dry-run'] ) ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
	}

	public static function run( bool $dry_run = false ): array {
		self::start( $dry_run );
		foreach ( array( 'about', 'it-courses', 'english-courses', 'faq', 'media-center' ) as $kind ) {
			$page = get_page_by_path( $kind );
			if ( $page instanceof WP_Post ) {
				self::applyPage( $kind, $page->ID );
			}
		}
		self::applyGlobal();
		self::applyLegalPages();
		self::applyCourses();
		self::applyCamps();
		self::applyCampArchive();
		MediaCategories::migrateUncategorized( $dry_run );
		self::cleanupPlaceholders();

		return self::$report;
	}

	public static function migratePage( string $kind, int $page_id, bool $dry_run = false ): array {
		self::start( $dry_run );
		self::applyPage( $kind, $page_id );

		return self::$report;
	}

	public static function seedArticleFaqs( bool $dry_run = false ): array {
		self::start( $dry_run );
		$items = array_map( static fn( array $item ): array => array( 'question' => (string) $item['question'], 'answer' => wpautop( (string) $item['answer'] ) ), (array) get_field( 'home_faq_items', (int) get_option( 'page_on_front' ) ) );
		if ( ! $items ) {
			return self::$report;
		}
		foreach ( get_posts( array( 'post_type' => 'post', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids' ) ) as $post_id ) {
			if ( $items === (array) get_field( 'article_faq_items', $post_id ) ) {
				++self::$report['preserved'];
				continue;
			}
			if ( ! $dry_run ) {
				update_field( 'article_faq_items', $items, $post_id );
			}
			++self::$report['changed'];
		}

		return self::$report;
	}

	public static function migrateHomepageTestimonials( bool $dry_run = false ): array {
		self::start( $dry_run );
		foreach ( range( 1, 4 ) as $index ) {
			self::fill( "home_testimonials_image_{$index}", '@asset:testimonials/testimonial.png', (int) get_option( 'page_on_front' ) );
		}

		return self::$report;
	}

	public static function migrateTestimonials( bool $dry_run = false ): array {
		self::start( $dry_run );
		$contexts = array_merge(
			array_map( static fn( string $path ): int => (int) ( get_page_by_path( $path )?->ID ?? 0 ), array( 'about', 'it-courses', 'english-courses', 'faq' ) ),
			array_map( 'intval', get_posts( array( 'post_type' => array( 'city', 'course', 'camp' ), 'post_status' => array( 'publish', 'draft', 'private' ), 'posts_per_page' => -1, 'fields' => 'ids' ) ) ),
			array( 'camp_archive' )
		);
		foreach ( array_filter( $contexts ) as $context ) {
			foreach ( range( 1, 4 ) as $index ) {
				self::fill( "testimonials_image_{$index}", '@asset:testimonials/testimonial.png', $context );
			}
		}

		return self::$report;
	}

	public static function migrateReviewsPresentation( bool $dry_run = false ): array {
		self::start( $dry_run );
		$home = (int) get_option( 'page_on_front' );
		$it   = (int) ( get_page_by_path( 'it-courses' )?->ID ?? 0 );
		$images = array();
		foreach ( range( 1, 4 ) as $index ) {
			$image = (int) get_field( "home_testimonials_image_{$index}", $home );
			$image = $image ?: (int) get_field( "testimonials_image_{$index}", $it );
			$images[] = $image ?: (int) get_field( "testimonials_image_{$index}", 'camp_archive' );
		}
		self::fill( 'global_reviews_title', 'Довіра, підтверджена результатами', 'option' );
		self::fill( 'global_reviews_gallery', array_values( array_filter( $images ) ), 'option' );
		$contexts = array(
			array( $home, 'home_testimonials_title', 'home_testimonials_image_', 'field_home_reviews_section' ),
			array( (int) ( get_page_by_path( 'about' )?->ID ?? 0 ), 'about_reviews_title', 'testimonials_image_', 'field_about_reviews_section' ),
			array( $it, 'it_courses_reviews_title', 'testimonials_image_', 'field_it_courses_reviews_section' ),
			array( (int) ( get_page_by_path( 'english-courses' )?->ID ?? 0 ), 'english_courses_reviews_title', 'testimonials_image_', 'field_english_courses_reviews_section' ),
			array( (int) ( get_page_by_path( 'faq' )?->ID ?? 0 ), 'faq_page_reviews_title', 'testimonials_image_', 'field_faq_page_reviews_section' ),
			array( 'camp_archive', 'camp_archive_reviews_title', 'testimonials_image_', 'field_camp_archive_reviews_section' ),
		);
		foreach ( $contexts as [ $context, $title_field, $image_prefix, $clone_key ] ) {
			if ( ! $context ) {
				continue;
			}
			self::fillReviewSection( 'reviews_section_title', $title_field ? trim( (string) get_field( $title_field, $context ) ) : '', $context, $clone_key );
			self::fillReviewSection( 'reviews_section_gallery', self::reviewGallery( $context, $image_prefix ), $context, $clone_key );
		}

		return self::$report;
	}

	public static function migrateCitySlug( string $slug, bool $dry_run = false ): array {
		self::start( $dry_run );
		$slug   = sanitize_title( $slug );
		$marker = 'logika_city_merge_' . md5( $slug ) . '_v1';
		if ( '' === $slug || get_option( $marker ) ) {
			return self::$report;
		}

		$cities = array_values(
			array_filter(
				get_posts( array( 'post_type' => 'city', 'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future' ), 'posts_per_page' => -1 ) ),
				static fn( WP_Post $city ): bool => $slug === CitySlug::for( $city )
			)
		);
		if ( count( $cities ) < 2 ) {
			return self::$report;
		}
		usort(
			$cities,
			static function ( WP_Post $left, WP_Post $right ): int {
				$left_imported  = '' !== trim( (string) get_post_meta( $left->ID, 'city_external_id', true ) );
				$right_imported = '' !== trim( (string) get_post_meta( $right->ID, 'city_external_id', true ) );

				return $left_imported === $right_imported ? $left->ID <=> $right->ID : ( $left_imported ? -1 : 1 );
			}
		);
		$canonical = array_shift( $cities );

		foreach ( $cities as $duplicate ) {
			foreach ( acf_get_fields( 'group_logika_city' ) ?: array() as $field ) {
				$name = (string) ( $field['name'] ?? '' );
				if ( '' === $name || 'city_show_on_map' === $name || ! self::emptyValue( get_field( $name, $canonical->ID ) ) ) {
					continue;
				}
				$value = get_field( $name, $duplicate->ID );
				if ( self::emptyValue( $value ) ) {
					continue;
				}
				if ( ! $dry_run ) {
					update_field( $field['key'], $value, $canonical->ID );
				}
				++self::$report['changed'];
			}

			$branches = get_posts( array( 'post_type' => 'branch', 'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future' ), 'posts_per_page' => -1, 'meta_key' => 'branch_city_id', 'meta_value' => (string) $duplicate->ID ) );
			foreach ( $branches as $branch ) {
				if ( ! $dry_run ) {
					update_field( 'field_branch_city_id', $canonical->ID, $branch->ID );
				}
				++self::$report['changed'];
			}
			if ( ! $dry_run ) {
				wp_trash_post( $duplicate->ID );
			}
			++self::$report['changed'];
		}

		if ( 'berestyn' === $slug && 'Берестин' === $canonical->post_title && '1' !== get_post_meta( $canonical->ID, 'city_show_on_map', true ) ) {
			if ( ! $dry_run ) {
				update_field( 'field_city_show_on_map', 1, $canonical->ID );
			}
			++self::$report['changed'];
		}
		if ( ! $dry_run ) {
			update_option( $marker, gmdate( 'c' ), false );
		}

		return self::$report;
	}

	private static function start( bool $dry_run ): void {
		self::$report = array( 'dry_run' => $dry_run, 'changed' => 0, 'preserved' => 0, 'attachments_created' => 0, 'warnings' => array() );
		self::$assets = array();
	}

	private static function applyPage( string $kind, int $id ): void {
		$reviews = self::entityIds( 'review', 'review_is_approved' );
		$faqs    = self::entityIds( 'faq_item', 'faq_is_active' );
		$posts   = self::entityIds( 'post' );
		$courses = self::entityIds( 'course' );
		$common  = array(
			'map_title'    => 'Знайдіть свою школу або навчайтесь онлайн',
			'map_text'     => 'Наші школи у 130 містах України — знайдіть зручний варіант поруч із вами або навчайтесь онлайн.',
			'cta_title'    => 'Підберемо курс саме для вашої дитини!',
			'cta_subtitle' => 'Ми зателефонуємо в зручний час',
			'cta_image'    => '@asset:cta/cta.png',
			'faq_title'    => 'Питання та відповіді',
		);

		$seeds = match ( $kind ) {
			'about' => array(
				'about_hero_title' => self::legacy( $id, 'about_page_texts', 'Найбільша в Україні школа програмування для дітей 7-17 років' ),
				'about_hero_text' => self::legacy( $id, 'about_page_texts', 'Перші результати вже через 4 тижні' ),
				'about_hero_image' => '@asset:about/hero-characters.png',
				'about_stats_title' => self::legacy( $id, 'about_page_texts', 'Навчайтеся в найбільшій школі в Україні' ),
				'about_stats_items' => self::cards( array( 'З 2018 року на ринку', 'Освітня ліцензія', '4.9 рейтинг від клієнтів', '178 шкіл в Україні', '130 міст по Україні', '100тис+ успішних випускників' ), array( 'banner-bar/icon-calendar-check.svg', 'banner-bar/icon-document-certificate.svg', 'banner-bar/icon-rating-star.svg', 'banner-bar/icon-outline_school.svg', 'banner-bar/icon-map-location.svg', 'banner-bar/icon-tabler-school.svg' ), 'text' ),
				'about_directions_title' => 'Напрями навчання',
				'about_directions_items' => array(
					array( 'title' => 'Курси програмування', 'image' => '@asset:about/direction-programming.png', 'link' => array( 'title' => 'Переглянути курси', 'url' => home_url( '/it-courses/' ) ) ),
					array( 'title' => 'Курси англійської мови', 'image' => '@asset:about/direction-english.png', 'link' => array( 'title' => 'Переглянути курси', 'url' => home_url( '/english-courses/' ) ) ),
					array( 'title' => 'Табори Logika', 'image' => '@asset:about/direction-camps.png', 'link' => array( 'title' => 'Переглянути табори', 'url' => home_url( '/camps/' ) ) ),
				),
				'about_outcomes_title' => self::legacy( $id, 'about_page_texts', 'Що отримає ваша дитина, навчаючись у школі “Logika”' ),
				'about_outcome_items' => array(
					array( 'title' => 'Розвине логічне й критичне мислення', 'text' => 'Побачить, як точні науки та шкільні предмети працюють у житті.', 'image' => '@asset:about/outcome-thinking.png' ),
					array( 'title' => 'Опанує сучасні IT-технології', 'text' => 'Познайомиться з інструментами, які використовують фахівці в реальних компаніях.', 'image' => '@asset:about/outcome-technology.png' ),
					array( 'title' => 'Створить власні проєкти', 'text' => 'Отримає готові роботи для перших професійних досягнень.', 'image' => '@asset:about/outcome-projects.png' ),
				),
				'about_history_title' => self::legacy( $id, 'about_page_texts', 'Logika – успішний освітній проєкт з 2018 року' ),
				'about_history_text' => '<p>Наша школа діє з 2018 року та має понад 50 000 випускників. Навчаємо дітей 7–17 років онлайн та офлайн за напрямами програмування й англійської мови.</p><p>Наша основна мета — навчити цікаво, легко та ефективно.</p>',
				'about_history_image' => '@asset:about/anniversary-full.png',
				'about_gallery_title' => 'Галерея',
				'about_gallery' => array( '@asset:gallery/gallery.png', '@asset:gallery/gallery2.png', '@asset:gallery/gallery3.png', '@asset:gallery/gallery4.png' ),
				'about_benefits_title' => self::legacy( $id, 'about_page_texts', 'Чому тисячі батьків обирають Logika' ),
				'about_media_title' => self::legacy( $id, 'about_page_texts', 'Чому тисячі батьків обирають Logika' ),
				'about_media_items' => self::benefits(),
				'about_onboarding_title' => 'Як розпочати навчання',
				'about_onboarding_items' => array(
					array( 'title' => 'Залишіть заявку', 'text' => 'Заповніть форму — менеджер зв’яжеться та підбере зручний час.', 'image' => '@asset:onbording/onbording1.svg' ),
					array( 'title' => 'Відвідайте безкоштовний пробний урок', 'text' => 'Дитина спробує навчання без жодних зобов’язань.', 'image' => '@asset:onbording/onbording2.svg' ),
					array( 'title' => 'Розпочніть навчання', 'text' => 'Регулярні заняття, власні проєкти та відкриті уроки для батьків.', 'image' => '@asset:onbording/onbording3.svg' ),
				),
				'about_map_title' => $common['map_title'], 'about_map_text' => $common['map_text'],
				'about_cta_title' => $common['cta_title'], 'about_cta_subtitle' => $common['cta_subtitle'], 'about_cta_image' => $common['cta_image'],
				'about_featured_reviews' => $reviews,
				'about_faq_title' => $common['faq_title'], 'about_featured_faq' => $faqs,
				'about_featured_posts' => $posts, 'about_use_global_certificates' => 1, 'about_use_global_partners' => 1,
			),
			'it-courses' => array(
				'it_courses_hero_title' => self::legacy( $id, 'it_courses_page_texts', 'Найбільша в Україні школа програмування для дітей 7-17 років' ),
				'it_courses_hero_text' => self::legacy( $id, 'it_courses_page_texts', 'Перші результати вже через 4 тижні' ),
				'it_courses_hero_image' => '@asset:boy-character.svg',
				'it_courses_catalog_title' => self::legacy( $id, 'it_courses_page_texts', 'Курси програмування для дітей 7-17 років' ),
				'it_courses_catalog_cards' => array(
					array( 'label' => '7-8 років', 'anchor' => '#years7', 'image' => '@asset:services/service1.png', 'background' => '@asset:services/services-bg.svg' ),
					array( 'label' => '9-11 років', 'anchor' => '#years9', 'image' => '@asset:services/service1.png', 'background' => '@asset:services/services-bg.svg' ),
					array( 'label' => '12-14 років', 'anchor' => '#years12', 'image' => '@asset:services/service1.png', 'background' => '@asset:services/services-bg.svg' ),
					array( 'label' => '14-17 років', 'anchor' => '#years14', 'image' => '@asset:services/service1.png', 'background' => '@asset:services/services-bg.svg' ),
				),
				'it_courses_marquee_items' => self::textRows( array( 'Перший урок — безкоштовно', 'Навчання з результатом', 'Уроки з живими викладачами', 'Інтерактивне навчання' ) ),
				'it_courses_age_categories' => self::courseCategories( (array) get_field( 'it_courses_featured_courses', $id ) ?: $courses ),
				'it_courses_featured_reviews' => $reviews,
				'it_courses_map_title' => $common['map_title'], 'it_courses_map_text' => $common['map_text'],
				'it_courses_cta_title' => $common['cta_title'], 'it_courses_cta_subtitle' => $common['cta_subtitle'], 'it_courses_cta_image' => $common['cta_image'],
				'it_courses_faq_title' => $common['faq_title'], 'it_courses_featured_faq' => $faqs,
			),
			'english-courses' => array(
				'english_courses_hero_title' => self::legacy( $id, 'english_courses_page_texts', 'Школа англійської мови для дітей 7-17 років' ),
				'english_courses_hero_text' => self::legacy( $id, 'english_courses_page_texts', 'Перші результати вже через 4 тижні' ),
				'english_courses_hero_image' => '@asset:en-courses/en-courses.svg',
				'english_courses_catalog_title' => self::legacy( $id, 'english_courses_page_texts', 'Наші курси' ),
				'english_courses_marquee_items' => self::textRows( array( 'Розмовна практика', 'Сучасна англійська', 'Навчання з результатом', 'Перший урок — безкоштовно' ) ),
				'english_courses_test_title' => self::legacy( $id, 'english_courses_page_texts', 'Пройти тестування і підібрати для вашої дитини найкращу групу' ),
				'english_courses_test_text' => 'Допоможемо визначити рівень підготовки, інтереси та формат навчання, щоб дитині було комфортно й цікаво навчатися з перших занять.',
				'english_courses_test_image' => '@asset:en-courses/test-image.svg', 'english_courses_test_cta_label' => 'Пройти тестування', 'english_courses_test_cta_url' => home_url( '/#lead-form' ),
				'english_courses_about_title' => self::legacy( $id, 'english_courses_page_texts', 'В школі «Logika»:' ),
				'english_courses_about_text' => '<p>Практикуємо живе спілкування, доступно пояснюємо граматику та підсилюємо результат щоденною практикою.</p>',
				'english_courses_about_image' => '@asset:en-courses/en-about-image.png',
				'english_courses_benefits' => array(
					array( 'title' => 'Багато спілкування', 'text' => 'Дитина постійно спілкується з учнями та вчителем для подолання мовного бар’єру.', 'image' => '@asset:en-courses/icon-check-one.svg' ),
					array( 'title' => 'Доступна граматика', 'text' => 'Пояснюємо граматику та одразу використовуємо її на практиці.', 'image' => '@asset:en-courses/icon-check-one.svg' ),
					array( 'title' => 'Читаємо, слухаємо і дивимося', 'text' => 'Працюємо з історіями, аудіо й відео та проводимо активні дискусії.', 'image' => '@asset:en-courses/icon-check-one.svg' ),
					array( 'title' => 'Граємо та конкуруємо', 'text' => 'Інтерактив та ігри підтримують увагу й мотивацію.', 'image' => '@asset:en-courses/icon-check-one.svg' ),
				),
				'english_courses_featured_reviews' => $reviews,
				'english_courses_map_title' => $common['map_title'], 'english_courses_map_text' => $common['map_text'],
				'english_courses_cta_title' => $common['cta_title'], 'english_courses_cta_subtitle' => $common['cta_subtitle'], 'english_courses_cta_image' => $common['cta_image'],
				'english_courses_faq_title' => $common['faq_title'], 'english_courses_featured_faq' => $faqs,
			),
			'faq' => array(
				'faq_page_hero_title' => self::legacy( $id, 'faq_page_texts', 'Часті запитання про навчання в Logika' ),
				'faq_page_hero_text' => 'Зібрали відповіді на найпоширеніші запитання про курси, формати навчання, розклад, вартість і викладачів.',
				'faq_page_hero_image' => '@asset:faq/faq-image.svg', 'faq_page_hero_icon' => '@asset:faq/faq-icon.svg', 'faq_page_list_title' => 'Найпоширеніші питання',
				'faq_page_featured_faq' => $faqs, 'faq_page_featured_reviews' => $reviews,
				'faq_page_map_title' => $common['map_title'], 'faq_page_map_text' => $common['map_text'],
				'faq_page_cta_title' => $common['cta_title'], 'faq_page_cta_subtitle' => $common['cta_subtitle'], 'faq_page_cta_image' => $common['cta_image'],
			),
			'media-center' => array(
				'media_center_hero_title' => self::legacy( $id, 'media_center_page_texts', 'Logika Медіа-центр' ), 'media_center_hero_image' => '@asset:media-promo.png', 'media_center_hero_background_image' => '@asset:media-promo.svg',
				'media_center_tags' => array_map( static fn( string $label ): array => array( 'label' => $label ), array( 'Новини', 'Корисні статті', 'Акції', 'Конкурси' ) ),
				'media_center_benefits_title' => 'Чому тисячі батьків обирають Logika',
				'media_center_benefits' => self::benefits(),
				'media_center_news_title' => 'Новини', 'media_center_news' => array_slice( $posts, 0, 3 ),
				'media_center_articles_title' => 'Корисні статті', 'media_center_featured_post' => $posts[0] ?? 0, 'media_center_articles' => array_slice( $posts, 0, 6 ),
				'media_center_discount_title' => 'Акції', 'media_center_offers' => array_slice( $posts, 0, 3 ),
				'media_center_cta_title' => $common['cta_title'], 'media_center_cta_subtitle' => $common['cta_subtitle'], 'media_center_cta_image' => $common['cta_image'],
				'media_center_faq_title' => $common['faq_title'], 'media_center_featured_faq' => $faqs,
				'media_center_blog_title' => 'Усі статті', 'media_center_blog_sort_new_label' => 'Спочатку новіші', 'media_center_blog_sort_old_label' => 'Спочатку старіші', 'media_center_blog_years_label' => 'Усі роки',
			),
			default => array(),
		};

		foreach ( $seeds as $field => $value ) {
			self::fill( $field, $value, $id );
		}
	}

	private static function applyGlobal(): void {
		$home_id  = (int) get_option( 'page_on_front' );
		$partners = (array) get_field( 'home_partners_items', $home_id );
		self::fill( 'global_phone', '+ 38 (093) 170-74-40', 'option' );
		self::fill( 'global_email', 'kiev@logikaschool.com', 'option' );
		self::fill( 'global_header_logo', '@asset:main-logo.svg', 'option' );
		self::fill( 'global_footer_logo', '@asset:logo.svg', 'option' );
		self::fill( 'global_social_links', array( array( 'label' => 'Instagram', 'url' => 'https://www.instagram.com/logika_it_school/' ), array( 'label' => 'Facebook', 'url' => 'https://www.facebook.com/logika.it.school/' ), array( 'label' => 'TikTok', 'url' => 'https://www.tiktok.com/@logika_fun?lang=uk-UA' ) ), 'option' );
		self::fill( 'global_footer_accreditation', 'Найбільша в Україні школа програмування та англійської мови для учнів 7–17 років', 'option' );
		self::fill( 'global_footer_copyright', '© 2026 Logika', 'option' );
		self::fill( 'global_partners', $partners, 'option' );
		self::fill( 'global_certificates', array( '@asset:certificates/certificate.png' ), 'option' );
		self::fill( 'fallback_reviews', self::entityIds( 'review', 'review_is_approved' ), 'option' );
		self::fill( 'fallback_faq', self::entityIds( 'faq_item', 'faq_is_active' ), 'option' );
		self::fill( 'fallback_no_branch_text', 'У цьому місті поки немає доступних філій.', 'option' );
		self::fill( 'fallback_no_camp_text', 'Табори для цього міста незабаром з’являться.', 'option' );
		self::fill( 'fallback_no_course_text', 'Курси для цього міста незабаром з’являться.', 'option' );
		self::fill( 'media_center_search_placeholder', 'Пошук статей', 'option' );
	}

	private static function applyCourses(): void {
		foreach ( get_posts( array( 'post_type' => 'course', 'post_status' => array( 'publish', 'draft', 'private' ), 'posts_per_page' => -1 ) ) as $course ) {
			$description = (string) get_field( 'course_short_description', $course->ID );
			$program     = (array) get_field( 'course_program', $course->ID );
			$learn       = array();
			foreach ( $program as $row ) {
				$learn[] = array( 'title' => $row['title'] ?? '', 'text' => wp_strip_all_tags( (string) ( $row['description'] ?? '' ) ) );
			}
			$learn = $learn ?: array( array( 'title' => 'Практичні навички', 'text' => 'Дитина закріплює знання на власних проєктах.' ), array( 'title' => 'Системне мислення', 'text' => 'Вчиться аналізувати завдання та знаходити рішення.' ), array( 'title' => 'Впевнена презентація', 'text' => 'Пояснює і демонструє результат своєї роботи.' ) );
			foreach ( array(
				'course_hero_label' => self::courseAge( $course->ID ), 'course_hero_text' => $description,
				'course_learn_title' => 'На курсі учні навчаються', 'course_learn_items' => $learn,
				'course_process_title' => 'Кожне заняття – теорія і практика',
				'course_process_items' => array(
					array( 'title' => 'Як проходять уроки?', 'text' => 'На кожному занятті вивчаємо новий інструмент і застосовуємо знання на практиці. Вчимося працювати індивідуально та в команді, проходимо всі етапи розробки.', 'image' => '@asset:course/process-img1.png' ),
					array( 'title' => 'Проєктний підхід', 'text' => 'Учні проходять повний цикл створення проєкту: від ідеї та дизайну до розробки, публікації й презентації готового результату.', 'image' => '@asset:course/process-img2.png' ),
				),
				'course_program' => array(
					array( 'title' => 'Модуль 1. Старт у фронтенді', 'items' => array( array( 'item_text' => 'Знайомство з фронтендом. HTML' ), array( 'item_text' => 'Знайомство з CSS: додаємо стилів' ), array( 'item_text' => 'Знайомство з JavaScript: змінні та DOM' ), array( 'item_text' => 'Бліц-презентація. HTML/CSS/JS: сайт-візитка' ) ) ),
					array( 'title' => 'Модуль 2. Стилі і вебдизайн' ),
					array( 'title' => 'Модуль 3. Програмування мовою JavaScript' ),
					array( 'title' => 'Модуль 4. Проєкт «Квіз»' ),
					array( 'title' => 'Модуль 5. Git та командна робота' ),
					array( 'title' => 'Модуль 6. Бібліотеки та аналітика' ),
					array( 'title' => 'Модуль 7. Реліз' ),
				),
				'course_portfolio_title' => 'Проєкти наших учнів',
				'course_projects' => array(
					array( 'variant' => 'standard', 'student_name' => 'Максим', 'student_age' => '12 років', 'course' => 'Python Start', 'topic' => 'Python', 'description' => 'Максим освоїв курс Python Start і вже створив свою першу комп’ютерну гру', 'student_image' => '@asset:portfolio/maxym.jpg' ),
					array( 'variant' => 'featured', 'student_name' => 'Максим', 'student_age' => '12 років', 'course' => 'Python Start', 'description' => 'Максим освоїв курс Python Start і вже створив свою першу комп’ютерну гру', 'project_image' => '@asset:portfolio/computer-game.png', 'cta_label' => 'Безкоштовний пробний урок', 'cta_url' => '#lead-form' ),
					array( 'variant' => 'standard', 'student_name' => 'Максим', 'student_age' => '12 років', 'course' => 'Python Start', 'topic' => 'Python', 'description' => 'Максим освоїв курс Python Start і вже створив свою першу комп’ютерну гру', 'student_image' => '@asset:portfolio/maxym.jpg' ),
				),
				'course_faq_title' => 'Програма курсу',
				'course_map_title' => 'Знайдіть свою школу або навчайтесь онлайн', 'course_map_text' => 'Оберіть зручний формат навчання у вашому місті або онлайн.',
				'course_cta_title' => 'Підберемо курс саме для вашої дитини!', 'course_cta_subtitle' => 'Ми зателефонуємо в зручний час', 'course_cta_image' => '@asset:cta/cta.png',
				'course_general_faq_title' => 'Питання та відповіді',
			) as $field => $value ) {
				self::fill( $field, $value, $course->ID );
			}
			if ( has_term( 'english', 'course_direction', $course->ID ) ) {
				self::fill(
					'course_faq_items',
					array(
						array( 'question' => 'Чи можна відвідати пробний урок?', 'answer' => '<p>Так, перед початком навчання дитина може відвідати безкоштовний пробний урок, познайомитися з викладачем і спробувати формат занять.</p>' ),
						array( 'question' => 'Для якого віку підходять курси?', 'answer' => '<p>Курси англійської мови підходять дітям від 7 до 17 років. Групу підбираємо за віком і рівнем підготовки, щоб навчання було комфортним та ефективним.</p>' ),
						array( 'question' => 'У якому форматі проходить навчання?', 'answer' => '<p>Навчання проходить офлайн у школах Logika у вашому місті або онлайн у невеликих групах. В обох форматах є живе спілкування, практика та підтримка викладача.</p>' ),
					),
					$course->ID
				);
			}
		}
	}

	private static function applyCamps(): void {
		foreach ( get_posts( array( 'post_type' => 'camp', 'post_status' => array( 'publish', 'draft', 'private' ), 'posts_per_page' => -1 ) ) as $camp ) {
			foreach ( array(
				'camp_hero_text' => get_the_excerpt( $camp ) ?: 'Незабутні емоції, розвиток і нові друзі разом із Logika.', 'camp_hero_dates_text' => '27.06 - 06.07 (перша зміна), 21.07 - 30.07 (друга зміна)', 'camp_hero_form_title' => "Встигніть забронювати.\nЗалиште заявку за 30 секунд — ми зателефонуємо і обговоримо усі деталі", 'camp_hero_image' => '@asset:camp/camp-hero.svg', 'camp_card_image' => '@asset:camp/team.webp', 'camp_card_description' => get_the_excerpt( $camp ) ?: 'Це справжні пригоди, у які поринають усі мешканці табору на весь термін путівки.',
				'camp_hero_images' => array( '@asset:camp/hands.png', '@asset:camp/pool.png', '@asset:camp/team.png', '@asset:camp/mountains.png' ),
				'camp_hero_facts' => array( array( 'label' => 'Де:', 'value' => 'с. Дудки, Закарпаття', 'icon' => '@asset:banner-bar/icon-map-location-figma.svg' ), array( 'label' => 'Коли:', 'value' => '27-01 липня 2026', 'icon' => '@asset:banner-bar/icon-calendar-check-figma.svg' ), array( 'label' => 'Тривалість:', 'value' => '10 днів/9 ночей', 'icon' => '@asset:banner-bar/icon-time-outline-figma.svg' ), array( 'label' => 'Для кого:', 'value' => 'діти 10-16 років', 'icon' => '@asset:banner-bar/icon-user-group-figma.svg' ), array( 'label' => 'Акційна ціна:', 'value' => '21000 грн (при оплаті до 10.06)', 'icon' => '@asset:banner-bar/icon-hot-price-figma.svg' ), array( 'label' => 'Ціна:', 'value' => '25000 грн', 'icon' => '@asset:banner-bar/icon-price-tag-figma.svg' ) ),
				'camp_benefits_title' => '10 днів цікавої програми та незабутніх вражень від літніх канікул', 'camp_benefits' => array( array( 'title' => 'Безлімітний басейн на свіжому повітрі', 'image' => '@asset:camp/benefits/pool.png' ), array( 'title' => 'Медична допомога 24/7 на території', 'image' => '@asset:camp/benefits/medical.png' ), array( 'title' => '4-х разове харчування (основне + перекус)', 'image' => '@asset:camp/benefits/food.png' ), array( 'title' => 'Незабутня професійна пінна вечірка', 'image' => '@asset:camp/benefits/foam.png' ) ),
				'camp_activities_title' => 'Активності у програмі:', 'camp_activities' => array( array( 'title' => 'Акваторій Emily Resort', 'text' => 'море веселощів, гірки та водні атракціони для яскравих емоцій!', 'image' => '@asset:camp/activities/aquatoriy.png' ), array( 'title' => 'Похід у гори', 'text' => 'свіже повітря, мальовничі краєвиди та справжня команда однодумців!', 'image' => '@asset:camp/activities/mountains.png' ), array( 'title' => 'Пісні біля вогнища', 'text' => 'атмосферні вечори з гітарою, історіями та смачними маршмелоу!', 'image' => '@asset:camp/activities/campfire.png' ), array( 'title' => 'Пригоди у Львові', 'text' => 'захоплива виїзна мандрівка до міста легенд, історії та яскравих вражень', 'image' => '@asset:camp/activities/lviv.png' ), array( 'title' => 'Розваги', 'text' => 'захопливі квести у стилі популярних ігор, інтелектуальні квізи та челенджі', 'image' => '@asset:camp/activities/quests.png' ), array( 'title' => 'Яскраве табірне життя', 'text' => 'спортивні ігри та командні змагання, вечірки, дискотеки та нові друзі', 'image' => '@asset:camp/activities/team.png' ) ),
				'camp_program_title' => 'Програма табору', 'camp_trips_title' => 'Виїзні екскурсії', 'camp_trips' => array( array( 'title' => 'озеро Синевір', 'image' => '@asset:trips/trip-img1.png' ), array( 'title' => 'Екопарк «Долина вовків»', 'image' => '@asset:trips/trip-img2.png' ), array( 'title' => 'Карпати', 'image' => '@asset:trips/trip-img3.png' ) ),
				'camp_details_title' => 'Деталі проживання', 'camp_details' => array( array( 'title' => 'Локація', 'text' => 'Комфортна та безпечна територія.', 'image' => '@asset:camp/details/location.png', 'gallery' => array( '@asset:camp/details/location.png', '@asset:camp/details/location-1.png', '@asset:camp/details/location-2.png', '@asset:camp/details/location-3.png' ) ), array( 'title' => 'Проживання', 'text' => 'Зручні кімнати й турбота команди.', 'image' => '@asset:camp/details/accommodation.png', 'gallery' => array( '@asset:camp/details/accommodation.png', '@asset:camp/details/gallery-1.png', '@asset:camp/details/gallery-2.png', '@asset:camp/details/gallery-3.png' ) ), array( 'title' => 'Меню', 'text' => 'Збалансоване харчування протягом дня.', 'image' => '@asset:camp/details/menu.png', 'gallery' => array( '@asset:camp/details/menu.png', '@asset:camp/details/gallery-1.png', '@asset:camp/details/gallery-2.png', '@asset:camp/details/gallery-4.png' ) ) ),
				'camp_includes_title' => 'У вартість входить:', 'camp_includes' => array( array( 'title' => 'Проживання', 'text' => 'Сучасний курортний комплекс з великою територією, природою та атмосферою справжнього відпочинку преміум класу', 'icon' => '@asset:details/details-icon1.svg' ), array( 'title' => 'Харчування', 'text' => '4-разове преміальне харчування, щоб енергії вистачило на всі пригоди', 'icon' => '@asset:details/details-icon2.svg' ), array( 'title' => 'Speaking clubs', 'text' => 'Спілкування з native-спікерами без нудних підручників', 'icon' => '@asset:details/details-icon3.svg' ), array( 'title' => 'Розваги', 'text' => 'Щодня новий ігровий всесвіт: екскурсії, вечірки та дискотеки', 'icon' => '@asset:details/details-icon4.svg' ), array( 'title' => 'Страхування', 'text' => 'Ми дбаємо про безпеку та комфорт дітей під час усіх активностей.', 'icon' => '@asset:details/details-icon5.svg' ), array( 'title' => 'Супровід', 'text' => 'Професійна команда школи Logika', 'icon' => '@asset:details/details-icon6.svg' ) ),
				'camp_booking_title' => 'Встигніть забронювати незабутні спогади', 'camp_booking_text' => 'Залиште заявку — ми зателефонуємо та обговоримо всі деталі.', 'camp_booking_image' => '@asset:camp/booking-characters.svg', 'camp_booking_benefits' => array( array( 'text' => 'Оновлена IT програма' ), array( 'text' => 'Активності, квести, турніри, ігри, дискотеки та екскурсії' ), array( 'text' => 'Безпека: вожаті поряд із дітьми 24/7' ) ), 'camp_booking_form_title' => "Встигніть забронювати.\nЗалиште заявку за 30 секунд — ми зателефонуємо і обговоримо усі деталі", 'camp_booking_submit_label' => 'Відправити',
				'camp_gallery_title' => 'Галерея', 'camp_related_reviews' => self::entityIds( 'review', 'review_is_approved' ),
				'camp_faq_title' => 'Питання та відповіді', 'camp_related_faq' => self::entityIds( 'faq_item', 'faq_is_active' ),
			) as $field => $value ) {
				self::fill( $field, $value, $camp->ID );
			}
			self::fillCampDetailGalleries( $camp->ID );
		}
	}

	private static function fillCampDetailGalleries( int $camp_id ): void {
		$details = array_values( array_filter( (array) get_field( 'camp_details', $camp_id ), 'is_array' ) );
		$galleries = array(
			array( '@asset:camp/details/location.png', '@asset:camp/details/location-1.png', '@asset:camp/details/location-2.png', '@asset:camp/details/location-3.png' ),
			array( '@asset:camp/details/accommodation.png', '@asset:camp/details/gallery-1.png', '@asset:camp/details/gallery-2.png', '@asset:camp/details/gallery-3.png' ),
			array( '@asset:camp/details/menu.png', '@asset:camp/details/gallery-1.png', '@asset:camp/details/gallery-2.png', '@asset:camp/details/gallery-4.png' ),
		);
		$changed = false;
		foreach ( $details as $index => $detail ) {
			if ( ! empty( $detail['gallery'] ) || ! isset( $galleries[ $index ] ) ) {
				continue;
			}
			$details[ $index ]['gallery'] = self::resolveAssets( $galleries[ $index ] );
			$changed = true;
		}
		if ( ! $changed ) {
			return;
		}
		if ( ! self::$report['dry_run'] ) {
			update_field( 'camp_details', $details, $camp_id );
		}
		++self::$report['changed'];
	}

	private static function applyCampArchive(): void {
		foreach ( array(
			'camp_archive_hero_title' => 'Табори з Logika: подаруйте дитині незабутні емоції', 'camp_archive_hero_text' => 'Відпочиваємо та розвиваємося разом!', 'camp_archive_hero_image' => '@asset:camp/camp-hero.svg',
			'camp_archive_benefits_title' => 'Табори з Logika - це:', 'camp_archive_benefits' => array( array( 'title' => 'Для дітей 10-16 років', 'image' => '@asset:camp/highlights/figma-age.png' ), array( 'title' => 'Формат All-inclusive', 'image' => '@asset:camp/highlights/figma-all-inclusive.png' ), array( 'title' => 'Власні розважальні програми', 'image' => '@asset:camp/highlights/figma-entertainment.png' ), array( 'title' => 'Працює своя команда вожатих', 'image' => '@asset:camp/highlights/figma-team.png' ), array( 'title' => 'Трансферти з великих міст', 'image' => '@asset:camp/highlights/figma-transfer.png' ), array( 'title' => 'Медичне страхування', 'image' => '@asset:camp/highlights/figma-medical.png' ) ),
			'camp_archive_formats_title' => 'Оберіть свій формат', 'camp_archive_booking_title' => 'Встигніть забронювати незабутні спогади', 'camp_archive_booking_text' => 'Залиште заявку — ми зателефонуємо і обговоримо всі деталі.', 'camp_archive_booking_image' => '@asset:camp/booking-characters.svg',
			'camp_archive_history_title' => 'Як це було минулих років', 'camp_archive_history_text' => '<p>Перегляньте моменти з попередніх змін та відчуйте атмосферу таборів Logika.</p>', 'camp_archive_history_image' => '@asset:camp/hands.png',
			'camp_archive_gallery_title' => 'Галерея', 'camp_archive_gallery' => array( '@asset:gallery/gallery.png', '@asset:gallery/gallery2.png', '@asset:gallery/gallery3.png', '@asset:gallery/gallery4.png' ),
			'camp_archive_reviews' => self::entityIds( 'review', 'review_is_approved' ), 'camp_archive_faq_title' => 'Питання та відповіді', 'camp_archive_faq' => self::entityIds( 'faq_item', 'faq_is_active' ),
		) as $field => $value ) {
			self::fill( $field, $value, 'camp_archive' );
		}
	}

	private static function applyLegalPages(): void {
		foreach ( array( 'privacy-policy' => 'privacy-policy', 'contractoffer' => 'contractoffer', 'contractoffer-overseas' => 'contractoffer', 'litsenziia' => 'litsenziia' ) as $slug => $source ) {
			$page = get_page_by_path( $slug );
			if ( ! $page instanceof WP_Post ) {
				continue;
			}
			foreach ( self::legalPayload( $source ) as $field => $value ) {
				self::fill( $field, $value, $page->ID );
			}
			if ( 'templates/page-legal.php' !== get_page_template_slug( $page ) ) {
				if ( self::$report['dry_run'] ) {
					++self::$report['changed'];
				} else {
					update_post_meta( $page->ID, '_wp_page_template', 'templates/page-legal.php' );
					++self::$report['changed'];
				}
			}
		}
	}

	private static function legalPayload( string $source ): array {
		$path = get_theme_file_path( "source-pages/{$source}.php" );
		if ( ! is_readable( $path ) || ! class_exists( DOMDocument::class ) ) {
			self::$report['warnings'][] = "Не вдалося прочитати legal source: {$source}.";
			return array();
		}
		$doc = new DOMDocument();
		libxml_use_internal_errors( true );
		$doc->loadHTML( '<?xml encoding="utf-8" ?>' . (string) file_get_contents( $path ) );
		libxml_clear_errors();
		$xpath  = new DOMXPath( $doc );
		$editor = $xpath->query( "//*[contains(concat(' ', normalize-space(@class), ' '), ' article-section__editor ')]" )->item( 0 );
		if ( ! $editor instanceof DOMElement ) {
			return array();
		}
		$title = '';
		$intro = '';
		$rows  = array();
		$row   = null;
		$images = array();
		foreach ( iterator_to_array( $editor->childNodes ) as $node ) {
			if ( $node instanceof DOMElement && 'img' === strtolower( $node->tagName ) ) {
				$images[] = '@asset:' . preg_replace( '#^img/#', '', (string) $node->getAttribute( 'src' ) );
				continue;
			}
			if ( $node instanceof DOMElement && 'div' === strtolower( $node->tagName ) && str_contains( $node->getAttribute( 'class' ), 'license-documents' ) ) {
				foreach ( $node->getElementsByTagName( 'img' ) as $image ) {
					$images[] = '@asset:' . preg_replace( '#^img/#', '', (string) $image->getAttribute( 'src' ) );
				}
				continue;
			}
			if ( $node instanceof DOMElement && 'h2' === strtolower( $node->tagName ) ) {
				if ( '' === $title ) {
					$title = trim( $node->textContent );
					continue;
				}
				if ( $row ) {
					$rows[] = $row;
				}
				$row = array( 'anchor' => sanitize_title( $node->textContent ), 'heading' => trim( $node->textContent ), 'content' => '' );
				continue;
			}
			$html = $node instanceof DOMElement ? (string) $doc->saveHTML( $node ) : '';
			if ( $row ) {
				$row['content'] .= $html;
			} else {
				$intro .= $html;
			}
		}
		if ( $row ) {
			$rows[] = $row;
		}
		if ( ! $rows && trim( wp_strip_all_tags( $intro ) ) ) {
			$rows[] = array( 'anchor' => 'polozhennia', 'heading' => 'Положення', 'content' => $intro );
			$intro  = '';
		}

		return array( 'legal_intro_title' => $title, 'legal_intro_text' => $intro, 'legal_sections' => $rows, 'legal_gallery' => $images );
	}

	private static function applyMenus(): void {
		$locations = (array) get_theme_mod( 'nav_menu_locations', array() );
		$config = array(
			'primary' => array( 'Головне меню Logika', array( 'about' => 'Про Logika', 'it-courses' => 'Курси програмування', 'english-courses' => 'Англійська мова', 'camps' => 'IT-табори', 'media-center' => 'Медіа-центр', 'faq' => 'FAQ' ) ),
			'footer_navigation' => array( 'Футер — навігація', array( 'about' => 'Про Logika', 'it-courses' => 'Курси', 'camps' => 'IT-табори', 'media-center' => 'Медіа-центр', 'faq' => 'FAQ' ) ),
			'footer_information' => array( 'Футер — інформація', array( 'contractoffer' => 'Договір оферти', 'contractoffer-overseas' => 'Договір оферти для клієнтів поза межами України', 'litsenziia' => 'Освітня ліцензія', 'privacy-policy' => 'Політика конфіденційності' ) ),
		);
		foreach ( $config as $location => [ $name, $items ] ) {
			if ( ! empty( $locations[ $location ] ) ) {
				$menu = wp_get_nav_menu_object( (int) $locations[ $location ] );
				if ( $menu && $name === $menu->name ) {
					self::syncMenu( (int) $menu->term_id, $items );
				} else {
					++self::$report['preserved'];
				}
				continue;
			}
			if ( self::$report['dry_run'] ) {
				++self::$report['changed'];
				continue;
			}
			$menu = wp_get_nav_menu_object( $name );
			$menu_id = $menu ? (int) $menu->term_id : (int) wp_create_nav_menu( $name );
			self::syncMenu( $menu_id, $items );
			$locations[ $location ] = $menu_id;
			set_theme_mod( 'nav_menu_locations', $locations );
			++self::$report['changed'];
		}
	}

	private static function syncMenu( int $menu_id, array $items ): void {
		$current = wp_get_nav_menu_items( $menu_id ) ?: array();
		$position = 0;
		foreach ( $items as $slug => $label ) {
			++$position;
			$page  = get_page_by_path( $slug );
			$route = home_url( '/' . trim( $slug, '/' ) . '/' );
			$match = current( array_filter( $current, static fn( WP_Post $item ): bool => $page instanceof WP_Post ? (int) $item->object_id === $page->ID : untrailingslashit( $item->url ) === untrailingslashit( $route ) ) );
			if ( $match instanceof WP_Post ) {
				$update = array( 'ID' => $match->ID );
				if ( $label !== $match->title && ( ! $page || $match->title === get_the_title( $page ) ) ) {
					$update['post_title'] = $label;
				}
				if ( (int) $match->menu_order !== $position ) {
					$update['menu_order'] = $position;
				}
				if ( 1 === count( $update ) ) {
					++self::$report['preserved'];
				} else {
					if ( ! self::$report['dry_run'] ) {
						wp_update_post( $update );
					}
					++self::$report['changed'];
				}
				continue;
			}
			if ( self::$report['dry_run'] ) {
				++self::$report['changed'];
				continue;
			}
			$args = array( 'menu-item-title' => $label, 'menu-item-status' => 'publish' );
			if ( $page instanceof WP_Post ) {
				$args += array( 'menu-item-object-id' => $page->ID, 'menu-item-object' => 'page', 'menu-item-type' => 'post_type' );
			} else {
				$args += array( 'menu-item-url' => $route, 'menu-item-type' => 'custom' );
			}
			wp_update_nav_menu_item( $menu_id, 0, $args );
			++self::$report['changed'];
		}
	}

	private static function cleanupPlaceholders(): void {
		global $wpdb;

		$values = implode( ',', array_fill( 0, count( self::PLACEHOLDERS ), '%s' ) );
		$rows   = $wpdb->get_results( $wpdb->prepare( "SELECT meta_id, post_id, meta_key FROM {$wpdb->postmeta} WHERE meta_value IN ({$values}) AND meta_key NOT LIKE %s", ...array_merge( self::PLACEHOLDERS, array( '%_page_texts%' ) ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		foreach ( $rows as $row ) {
			if ( self::$report['dry_run'] ) {
				++self::$report['changed'];
				continue;
			}
			delete_metadata_by_mid( 'post', (int) $row->meta_id );
			delete_post_meta( (int) $row->post_id, '_' . (string) $row->meta_key );
			++self::$report['changed'];
		}
	}

	private static function fill( string $field, mixed $desired, int|string $post_id ): void {
		if ( self::emptyValue( $desired ) ) {
			return;
		}
		$current = get_field( $field, $post_id );
		$known_placeholder = 'faq_page_hero_image' === $field && is_numeric( $current ) && 'theme/assets/img/faq/faq-left-bg.svg' === get_post_meta( (int) $current, '_logika_source_path', true );
		if ( ! self::emptyValue( $current ) && ! self::placeholder( $current ) && ! $known_placeholder ) {
			++self::$report['preserved'];
			return;
		}
		$desired = self::resolveAssets( $desired );
		if ( $current === $desired ) {
			return;
		}
		if ( ! self::$report['dry_run'] ) {
			update_field( $field, $desired, $post_id );
		}
		++self::$report['changed'];
	}

	private static function fillReviewSection( string $field, mixed $desired, int|string $context, string $clone_key ): void {
		if ( self::emptyValue( $desired ) ) {
			return;
		}
		$current = is_int( $context ) ? get_post_meta( $context, $field, true ) : get_option( $context . '_' . $field );
		if ( ! self::emptyValue( $current ) ) {
			++self::$report['preserved'];
			return;
		}
		if ( ! self::$report['dry_run'] ) {
			if ( is_int( $context ) ) {
				update_post_meta( $context, $field, $desired );
				update_post_meta( $context, '_' . $field, $clone_key . '_field_' . $field );
			} else {
				update_option( $context . '_' . $field, $desired, false );
				update_option( $context . '__' . $field, $clone_key . '_field_' . $field, false );
			}
		}
		++self::$report['changed'];
	}

	/** @return array<int, int> */
	private static function reviewGallery( int|string $context, string $prefix ): array {
		return array_values(
			array_filter(
				array_map(
					static fn( int $index ): int => (int) get_field( $prefix . $index, $context ),
					range( 1, 4 )
				)
			)
		);
	}

	private static function resolveAssets( mixed $value ): mixed {
		if ( is_string( $value ) && str_starts_with( $value, '@asset:' ) ) {
			return self::assetId( substr( $value, 7 ) );
		}
		if ( is_array( $value ) ) {
			foreach ( $value as $key => $item ) {
				$value[ $key ] = self::resolveAssets( $item );
			}
		}

		return $value;
	}

	private static function assetId( string $relative ): int {
		$relative = ltrim( strtok( $relative, '?' ) ?: $relative, '/' );
		$marker   = 'theme/assets/img/' . $relative;
		if ( isset( self::$assets[ $marker ] ) ) {
			return self::$assets[ $marker ];
		}
		$existing = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'fields' => 'ids', 'posts_per_page' => 1, 'meta_key' => '_logika_source_path', 'meta_value' => $marker ) );
		if ( $existing ) {
			return self::$assets[ $marker ] = (int) $existing[0];
		}
		$source = get_theme_file_path( 'assets/img/' . $relative );
		if ( ! is_readable( $source ) ) {
			self::$report['warnings'][] = "Відсутній source asset: {$relative}.";
			return self::$assets[ $marker ] = 0;
		}
		if ( self::$report['dry_run'] ) {
			++self::$report['attachments_created'];
			++self::$report['changed'];
			return self::$assets[ $marker ] = 1;
		}
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$upload = wp_upload_dir();
		$name   = wp_unique_filename( $upload['path'], 'logika-' . basename( $relative ) );
		$target = trailingslashit( $upload['path'] ) . $name;
		if ( ! copy( $source, $target ) ) {
			self::$report['warnings'][] = "Не вдалося імпортувати asset: {$relative}.";
			return self::$assets[ $marker ] = 0;
		}
		$mime = wp_check_filetype( $target )['type'] ?: ( 'svg' === pathinfo( $target, PATHINFO_EXTENSION ) ? 'image/svg+xml' : 'application/octet-stream' );
		$id   = wp_insert_attachment( array( 'post_title' => sanitize_text_field( pathinfo( $relative, PATHINFO_FILENAME ) ), 'post_mime_type' => $mime, 'post_status' => 'inherit' ), $target );
		if ( is_wp_error( $id ) ) {
			@unlink( $target );
			self::$report['warnings'][] = $id->get_error_message();
			return self::$assets[ $marker ] = 0;
		}
		update_post_meta( (int) $id, '_logika_source_path', $marker );
		$metadata = wp_generate_attachment_metadata( (int) $id, $target );
		if ( $metadata ) {
			wp_update_attachment_metadata( (int) $id, $metadata );
		}
		++self::$report['attachments_created'];
		++self::$report['changed'];

		return self::$assets[ $marker ] = (int) $id;
	}

	private static function legacy( int $post_id, string $field, string $source ): string {
		$count = (int) get_post_meta( $post_id, $field, true );
		for ( $index = 0; $index < $count; ++$index ) {
			if ( $source === html_entity_decode( trim( (string) get_post_meta( $post_id, "{$field}_{$index}_source", true ) ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ) {
				$value = trim( (string) get_post_meta( $post_id, "{$field}_{$index}_value", true ) );
				return self::placeholder( $value ) ? $source : $value;
			}
		}

		return $source;
	}

	private static function placeholder( mixed $value ): bool {
		if ( is_array( $value ) ) {
			$encoded = (string) wp_json_encode( $value );
			return str_contains( $encoded, 'ACF Social' ) || str_contains( $encoded, 'example.com/acf-social' );
		}
		if ( ! is_scalar( $value ) ) {
			return false;
		}
		$value = trim( (string) $value );

		return in_array( $value, self::PLACEHOLDERS, true ) || str_contains( $value, 'ACF test' );
	}

	private static function emptyValue( mixed $value ): bool {
		return is_array( $value ) ? ! $value : null === $value || false === $value || '' === trim( (string) $value );
	}

	private static function entityIds( string $type, string $active_meta = '' ): array {
		$args = array( 'post_type' => $type, 'post_status' => 'publish', 'fields' => 'ids', 'posts_per_page' => -1, 'orderby' => 'menu_order date', 'order' => 'ASC' );
		if ( $active_meta ) {
			$args['meta_query'] = array( array( 'key' => $active_meta, 'value' => '1' ) );
		}

		return array_map( 'intval', get_posts( $args ) );
	}

	private static function textRows( array $texts ): array {
		return array_map( static fn( string $text ): array => array( 'text' => $text ), $texts );
	}

	private static function cards( array $texts, array $images, string $text_key = 'title' ): array {
		$rows = array();
		foreach ( $texts as $index => $text ) {
			$rows[] = array( $text_key => $text, 'icon' => '@asset:' . $images[ $index ] );
		}

		return $rows;
	}

	private static function benefits(): array {
		$data = array(
			array( 'Єдина платформа — єдина якість', 'Інтерактивний підручник, практичні завдання й трекер результатів забезпечують високу якість навчання.', 'media-center/why-logika/platform.png' ),
			array( 'Мінімум 5 реальних проєктів за курс', 'Ігри, мультфільми, боти, сайти чи додатки — від ідеї до готового результату.', 'media-center/why-logika/projects.png' ),
			array( 'Відкриті уроки для батьків', 'Наприкінці модуля дитина презентує свій проєкт, а ви бачите реальний прогрес.', 'media-center/why-logika/open-lessons.png' ),
			array( 'Онлайн або у вашому місті', 'Займайтеся у школі Logika або онлайн у невеликих групах.', 'media-center/why-logika/online-city.png' ),
			array( 'Викладачі з профільною освітою', 'Багаторівневий відбір, навчання за методологією Logika та досвід роботи з дітьми.', 'media-center/why-logika/teachers.png' ),
			array( 'Власна ігрова методика', 'Навчаємо через сюжети, практику та залучення, а не через суху теорію.', 'media-center/why-logika/game-method.png' ),
		);

		return array_map( static fn( array $item ): array => array( 'title' => $item[0], 'text' => $item[1], 'image' => '@asset:' . $item[2] ), $data );
	}

	private static function courseCategories( array $ids ): array {
		$ids = array_values( array_filter( array_map( 'absint', $ids ) ) );
		$titles = array( 'Курси для дітей 7–8 років', 'Курси для дітей 9–11 років', 'Курси для дітей 12–14 років', 'Курси для дітей 14–17 років' );
		$chunks = array_chunk( $ids, max( 1, (int) ceil( count( $ids ) / 4 ) ) );

		return array_map( static fn( string $title, int $index ): array => array( 'title' => $title, 'courses' => $chunks[ $index ] ?? array() ), $titles, array_keys( $titles ) );
	}

	private static function courseAge( int $id ): string {
		$min = (int) get_field( 'course_age_min', $id );
		$max = (int) get_field( 'course_age_max', $id );

		return $min ? $min . ( $max ? '–' . $max : '+' ) . ' років' : '7–17 років';
	}
}
