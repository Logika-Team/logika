<?php

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/src/LeadForm.php';
require_once get_template_directory() . '/src/MenuWalker.php';
require_once get_template_directory() . '/src/Entities.php';
require_once get_template_directory() . '/src/PageContent.php';
require_once get_template_directory() . '/src/FixedPage.php';
require_once get_template_directory() . '/src/GenericPage.php';
require_once get_template_directory() . '/src/ArticlePage.php';
require_once get_template_directory() . '/src/SourceMarkup.php';
require_once get_template_directory() . '/src/Testimonials.php';
require_once get_template_directory() . '/src/CityPage.php';
require_once get_template_directory() . '/src/CoursePage.php';
require_once get_template_directory() . '/src/CampPage.php';
require_once get_template_directory() . '/src/CitySeo.php';
require_once get_template_directory() . '/src/CitySchema.php';
require_once get_template_directory() . '/src/CityFaqSchema.php';
require_once get_template_directory() . '/src/CourseSchema.php';
require_once get_template_directory() . '/src/PhoneCountry.php';
require_once get_template_directory() . '/src/Routing.php';

Logika_Theme_Routing::register();

function logika_theme_setup(): void {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'style', 'script', 'navigation-widgets' ) );
	add_editor_style( 'assets/css/article-editor.css' );
	register_nav_menus(
		array(
			'primary'            => 'Головне меню',
			'footer_navigation'  => 'Футер: навігація',
			'footer_information' => 'Футер: інформація',
		)
	);
}
add_action( 'after_setup_theme', 'logika_theme_setup' );

function logika_theme_favicon(): void {
	$path = get_theme_file_path( '/assets/img/favicon.svg' );

	if ( ! is_file( $path ) ) {
		return;
	}

	$uri = add_query_arg( 'ver', (string) filemtime( $path ), get_theme_file_uri( '/assets/img/favicon.svg' ) );
	printf( "<link rel=\"icon\" type=\"image/svg+xml\" href=\"%s\">\n", esc_url( $uri ) );
}
add_action( 'wp_head', 'logika_theme_favicon', 1 );

function logika_theme_assets(): void {
	$uri     = get_template_directory_uri() . '/assets';
	$version = wp_get_theme()->get( 'Version' );
	$style_version = (string) filemtime( get_template_directory() . '/assets/css/style.css' );
	$adaptive_style_version = (string) filemtime( get_template_directory() . '/assets/css/adaptive.css' );
	$courses_responsive_version = (string) filemtime( get_template_directory() . '/assets/css/courses-responsive.css' );
	$main_version = (string) filemtime( get_template_directory() . '/assets/js/main.js' );
	$map_version = (string) filemtime( get_template_directory() . '/assets/js/camp-map.js' );
	$city_context_version = (string) filemtime( get_template_directory() . '/assets/js/city-context.js' );
	$city_selector_version = (string) filemtime( get_template_directory() . '/assets/js/city-selector.js' );
	$media_center_version = (string) filemtime( get_template_directory() . '/assets/js/media-center.js' );
	$article_views_version = (string) filemtime( get_template_directory() . '/assets/js/article-views.js' );
	$media_search_version = (string) filemtime( get_template_directory() . '/assets/css/media-search.css' );
	$map_style_version = (string) filemtime( get_template_directory() . '/assets/css/blocks/sections/school-map.css' );
	$faq_banner_style_version = (string) filemtime( get_template_directory() . '/assets/css/blocks/sections/faq-banner-section.css' );
	$faq_accordion_style_version = (string) filemtime( get_template_directory() . '/assets/css/faq-accordion.css' );
	$leads_version = (string) filemtime( get_template_directory() . '/assets/js/leads.js' );
	$phone_dropup_version = (string) filemtime( get_template_directory() . '/assets/css/phone-dropdown-dropup.css' );
	$home_media_center_version = (string) filemtime( get_template_directory() . '/assets/css/blocks/sections/media-section.css' );
	$home_media_center_mobile_version = (string) filemtime( get_template_directory() . '/assets/css/blocks/sections/media-section-mobile.css' );
	$home_banner_main_version = (string) filemtime( get_template_directory() . '/assets/css/blocks/sections/home-banner-main.css' );
	$home_nizhyn_school_version = (string) filemtime( get_template_directory() . '/assets/css/blocks/sections/nizhyn-school.css' );
	$home_city_seo_version = (string) filemtime( get_template_directory() . '/assets/js/homepage-city-seo.js' );
	$lead_modal_style_version = (string) filemtime( get_template_directory() . '/assets/css/lead-modal.css' );
	$director_feedback_style_version = (string) filemtime( get_template_directory() . '/assets/css/blocks/sections/director-feedback.css' );
	$vacancies_style_version = (string) filemtime( get_template_directory() . '/assets/css/blocks/sections/vacancies.css' );
	$vacancies_lightbox_version = (string) filemtime( get_template_directory() . '/assets/js/vacancies-lightbox.js' );
	$vacancies_details_dialog_version = (string) filemtime( get_template_directory() . '/assets/js/vacancies-details-dialog.js' );
	$cta_style_version = (string) filemtime( get_template_directory() . '/assets/css/blocks/sections/cta-section.css' );
	$about_style_version = (string) filemtime( get_template_directory() . '/assets/css/blocks/sections/about.css' );
	wp_enqueue_style( 'logika-intl-tel-input', $uri . '/css/vendor/intl-tel-input/intlTelInput.min.css', array(), '20.1.0' );
	wp_enqueue_style( 'logika-theme', $uri . '/css/style.css', array( 'logika-intl-tel-input' ), $style_version );
	wp_enqueue_style( 'logika-cta-section', $uri . '/css/blocks/sections/cta-section.css', array( 'logika-theme' ), $cta_style_version );
	wp_enqueue_style( 'logika-breadcrumbs', $uri . '/css/breadcrumbs.css', array( 'logika-theme' ), (string) filemtime( get_template_directory() . '/assets/css/breadcrumbs.css' ) );
	wp_enqueue_style( 'logika-course-card', $uri . '/css/course-card.css', array( 'logika-theme' ), (string) filemtime( get_template_directory() . '/assets/css/course-card.css' ) );
	wp_enqueue_style( 'logika-theme-adaptive', $uri . '/css/adaptive.css', array( 'logika-theme' ), $adaptive_style_version );
	wp_enqueue_style( 'logika-courses-responsive', $uri . '/css/courses-responsive.css', array( 'logika-theme-adaptive' ), $courses_responsive_version );
	if ( is_404() ) {
		wp_enqueue_style( 'logika-404', $uri . '/css/404.css', array( 'logika-theme-adaptive' ), (string) filemtime( get_template_directory() . '/assets/css/404.css' ) );
	}
	wp_add_inline_style( 'logika-theme', '@media (hover:hover){.header__login:hover{transform:none;box-shadow:none}.header__login:hover svg{transform:none}}.search-form__input::-webkit-search-cancel-button{width:18px;height:18px;margin-right:2px;background:url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 18 18\'%3E%3Cpath d=\'m4 4 10 10M14 4 4 14\' fill=\'none\' stroke=\'%23602B7A\' stroke-linecap=\'round\' stroke-width=\'1.5\'/%3E%3C/svg%3E") center/contain no-repeat;-webkit-appearance:none;cursor:pointer}' );
	wp_add_inline_style( 'logika-theme', '.header{position:sticky;top:0}' );
	wp_add_inline_style( 'logika-theme', '.banner-section__subtitle{color:var(--violet-100)}.banner-section .main-form__title>span{color:var(--light-blue)}.banner-section__bar{box-shadow:0 0 25px 0 rgba(37,37,37,.12)}' );
	wp_add_inline_style( 'logika-theme', '.header .btn,.footer .btn,.header__location-region-toggle{font-weight:600}' );
	wp_add_inline_style( 'logika-theme', '.categories-section__controls-btn{background-color:var(--violet-100)}.categories-section__controls-btn.swiper-button-disabled{background-color:var(--grey-100)}' );
	wp_add_inline_style( 'logika-theme', '.faq-section{overflow:hidden}.faq-section__left-bg,.faq-section__right-bg{pointer-events:none}' );
	wp_add_inline_style( 'logika-theme', '.testimonials-section__box{width:100%}.testimonials-section__items{grid-template-columns:repeat(6,minmax(0,1fr))}.testimonials-card__decor{width:min(100%,220px);aspect-ratio:1/1;align-self:flex-start;border-radius:50%;overflow:hidden}.testimonials-card__decor picture,.testimonials-card__decor img{width:100%;height:100%;object-fit:cover}' );
	wp_enqueue_style( 'logika-lead-modal', $uri . '/css/lead-modal.css', array( 'logika-theme' ), $lead_modal_style_version );
	if ( is_post_type_archive( 'camp' ) || is_page( 'camps' ) ) {
		wp_enqueue_style( 'logika-camp-modal', $uri . '/css/camp-modal.css', array( 'logika-lead-modal' ), (string) filemtime( get_template_directory() . '/assets/css/camp-modal.css' ) );
		foreach ( array( 'camp-booking', 'camp-extra', 'camp-formats', 'camp-highlights', 'camp-history', 'camp-page-hero' ) as $section ) {
			wp_enqueue_style( "logika-{$section}", "{$uri}/css/blocks/sections/{$section}.css", array( 'logika-theme' ), (string) filemtime( get_template_directory() . "/assets/css/blocks/sections/{$section}.css" ) );
		}
	}
	wp_enqueue_style( 'logika-school-map-style', $uri . '/css/blocks/sections/school-map.css', array( 'logika-theme' ), $map_style_version );
	wp_enqueue_style( 'logika-faq-banner-style', $uri . '/css/blocks/sections/faq-banner-section.css', array( 'logika-theme' ), $faq_banner_style_version );
	if ( is_page( 'faq' ) || is_singular( 'course' ) ) {
		wp_enqueue_style( 'logika-faq-accordion', $uri . '/css/faq-accordion.css', array( 'logika-theme' ), $faq_accordion_style_version );
	}
	if ( is_page( 'vacancies' ) ) {
		wp_enqueue_style( 'logika-vacancies', $uri . '/css/blocks/sections/vacancies.css', array( 'logika-theme' ), $vacancies_style_version );
		wp_enqueue_script( 'logika-vacancies-lightbox', $uri . '/js/vacancies-lightbox.js', array( 'logika-theme' ), $vacancies_lightbox_version, true );
		wp_enqueue_script( 'logika-vacancies-details-dialog', $uri . '/js/vacancies-details-dialog.js', array( 'logika-theme' ), $vacancies_details_dialog_version, true );
	}
	if ( is_front_page() || get_query_var( 'logika_city' ) || is_page( array( 'about', 'media-center' ) ) ) {
		wp_enqueue_style( 'logika-home-media-center', $uri . '/css/blocks/sections/media-section.css', array( 'logika-theme' ), $home_media_center_version );
	}
	if ( is_front_page() || get_query_var( 'logika_city' ) ) {
		wp_enqueue_style( 'logika-home-banner-main', $uri . '/css/blocks/sections/home-banner-main.css', array( 'logika-theme-adaptive' ), $home_banner_main_version );
		foreach ( array( 'marquee', 'services', 'english', 'transformation', 'onboarding', 'testimonials', 'portfolio', 'faq', 'certificates', 'partners' ) as $section ) {
			wp_enqueue_style( "logika-home-{$section}-main", "{$uri}/css/blocks/sections/home-{$section}-main.css", array( 'logika-home-banner-main' ), (string) filemtime( get_template_directory() . "/assets/css/blocks/sections/home-{$section}-main.css" ) );
		}
		wp_enqueue_style( 'logika-director-feedback', $uri . '/css/blocks/sections/director-feedback.css', array( 'logika-theme' ), $director_feedback_style_version );
		wp_enqueue_style( 'logika-home-media-center-mobile', $uri . '/css/blocks/sections/media-section-mobile.css', array( 'logika-home-media-center' ), $home_media_center_mobile_version );
	}
	if ( is_front_page() || get_query_var( 'logika_city' ) ) {
		wp_enqueue_style( 'logika-home-nizhyn-school', $uri . '/css/blocks/sections/nizhyn-school.css', array( 'logika-theme' ), $home_nizhyn_school_version );
		wp_enqueue_script( 'logika-home-city-seo', $uri . '/js/homepage-city-seo.js', array( 'logika-city-context' ), $home_city_seo_version, true );
		wp_localize_script( 'logika-home-city-seo', 'logikaHomepageCitySeo', array( 'endpoint' => esc_url_raw( rest_url( 'logika/v1/cities/' ) ) ) );
	}
	wp_enqueue_style( 'logika-gallery-section', "{$uri}/css/blocks/sections/gallery-section.css", array( 'logika-theme' ), (string) filemtime( get_template_directory() . '/assets/css/blocks/sections/gallery-section.css' ) );
	if ( is_page( 'about' ) ) {
		wp_enqueue_style( 'logika-about', $uri . '/css/blocks/sections/about.css', array( 'logika-theme-adaptive' ), $about_style_version );
	}
	if ( is_singular( 'camp' ) || is_post_type_archive( 'camp' ) ) {
		foreach ( array( 'trips-section', 'details-section', 'camp-extra' ) as $section ) {
			wp_enqueue_style( "logika-{$section}", "{$uri}/css/blocks/sections/{$section}.css", array( 'logika-theme' ), (string) filemtime( get_template_directory() . "/assets/css/blocks/sections/{$section}.css" ) );
		}
	}
	if ( is_singular( 'course' ) ) {
		wp_add_inline_style( 'logika-theme', '.accordion--mode .course-program-icon{flex:0 0 64px;width:64px;height:64px;padding:14px;border-radius:50%;background:var(--light-blue)}.accordion--mode .course-program-icon img{width:100%;height:100%;object-fit:contain}.accordion--mode .course-program-title{margin-right:auto}' );
		if ( has_term( 'english', 'course_direction', get_queried_object_id() ) ) {
			wp_enqueue_style( 'logika-english-course', $uri . '/css/english-course.css', array( 'logika-theme' ), (string) filemtime( get_template_directory() . '/assets/css/english-course.css' ) );
			wp_add_inline_style( 'logika-english-course', '.english-course-program__items{align-items:start}' );
		}
		foreach ( array( 'course-banner-section', 'learn-section', 'process-section', 'portfolio-section' ) as $section ) {
			wp_enqueue_style( "logika-{$section}", "{$uri}/css/blocks/sections/{$section}.css", array( 'logika-theme' ), (string) filemtime( get_template_directory() . "/assets/css/blocks/sections/{$section}.css" ) );
		}
	}
	wp_enqueue_style( 'logika-phone-dropdown-dropup', $uri . '/css/phone-dropdown-dropup.css', array( 'logika-theme' ), $phone_dropup_version );
	wp_enqueue_script( 'logika-swiper', $uri . '/js/swiper.js', array(), $version, true );
	wp_enqueue_script( 'logika-theme', $uri . '/js/main.js', array( 'logika-swiper' ), $main_version, true );
	wp_enqueue_script( 'logika-city-context', $uri . '/js/city-context.js', array(), $city_context_version, true );
	wp_localize_script( 'logika-city-context', 'logikaCityContextConfig', array( 'endpoint' => esc_url_raw( rest_url( 'logika/v1/cities' ) ) ) );
	wp_enqueue_script( 'logika-school-map', $uri . '/js/camp-map.js', array( 'logika-city-context' ), $map_version, true );
	wp_localize_script( 'logika-school-map', 'logikaThemeAssets', array( 'mapUrl' => esc_url_raw( $uri . '/img/maps/ukraine-regions.svg?ver=' . $map_version ), 'branchIconUrl' => esc_url_raw( $uri . '/img/icons/solar_route-outline.svg' ), 'branchesEndpoint' => esc_url_raw( rest_url( 'logika/v1/cities/' ) ) ) );
	wp_enqueue_script( 'logika-intl-tel-input', $uri . '/js/vendor/intl-tel-input/intlTelInput.min.js', array(), '20.1.0', true );
	wp_enqueue_script( 'logika-intl-tel-input-i18n-uk', $uri . '/js/vendor/intl-tel-input/i18n-uk.js', array( 'logika-intl-tel-input' ), $version, true );
	wp_enqueue_script( 'logika-leads', $uri . '/js/leads.js', array( 'logika-intl-tel-input-i18n-uk', 'logika-city-context' ), $leads_version, true );
	wp_localize_script( 'logika-leads', 'logikaLead', array( 'endpoint' => esc_url_raw( rest_url( 'logika/v1/leads' ) ), 'tokenEndpoint' => esc_url_raw( rest_url( 'logika/v1/forms/token' ) ), 'cityEndpoint' => esc_url_raw( rest_url( 'logika/v1/cities' ) ), 'phoneCountryDefault' => 'UA', 'phoneCountryEndpoint' => esc_url_raw( rest_url( 'logika/v1/phone-country' ) ), 'phoneUtilsUrl' => esc_url_raw( $uri . '/js/vendor/intl-tel-input/utils.js' ) ) );
	wp_enqueue_script( 'logika-city-selector', $uri . '/js/city-selector.js', array( 'logika-city-context' ), $city_selector_version, true );
	if ( is_page( 'media-center' ) || get_query_var( 'logika_blog' ) || get_query_var( 'logika_media_category' ) ) {
		$media_tag = isset( $_GET['tag'] ) ? sanitize_title( wp_unslash( $_GET['tag'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		wp_enqueue_style( 'logika-media-search', $uri . '/css/media-search.css', array( 'logika-theme' ), $media_search_version );
		wp_enqueue_script( 'logika-media-center', $uri . '/js/media-center.js', array( 'logika-city-context' ), $media_center_version, true );
		wp_localize_script( 'logika-media-center', 'logikaMediaCenter', array( 'endpoint' => esc_url_raw( rest_url( 'logika/v1/media' ) ), 'category' => sanitize_key( (string) get_query_var( 'logika_media_category' ) ), 'tag' => $media_tag, 'featuredPost' => is_page( 'media-center' ) && function_exists( 'get_field' ) ? absint( get_field( 'media_center_featured_post', get_queried_object_id() ) ) : 0 ) );
	}
	if ( is_singular( 'post' ) ) {
		wp_enqueue_script( 'logika-article-views', $uri . '/js/article-views.js', array(), $article_views_version, true );
		wp_localize_script( 'logika-article-views', 'logikaArticleViews', array( 'endpoint' => esc_url_raw( rest_url( 'logika/v1/articles/' . get_queried_object_id() . '/view' ) ) ) );
	}
}
add_action( 'wp_enqueue_scripts', 'logika_theme_assets' );

function logika_theme_render_lead_modal(): void {
	get_template_part( 'template-parts/components/lead-modal' );
}
add_action( 'wp_footer', 'logika_theme_render_lead_modal' );

function logika_theme_render_camp_modal(): void {
	if ( ! is_post_type_archive( 'camp' ) && ! is_page( 'camps' ) ) {
		return;
	}

	get_template_part( 'template-parts/components/camp-modal' );
}
add_action( 'wp_footer', 'logika_theme_render_camp_modal' );

add_action( 'rest_api_init', array( 'Logika_Theme_Phone_Country', 'register' ) );
add_filter( 'wp_robots', array( 'Logika_Theme_City_Seo', 'robots' ) );
add_filter( 'get_canonical_url', array( 'Logika_Theme_City_Seo', 'canonical' ) );
add_action( 'wp_head', array( 'Logika_Theme_City_Schema', 'render' ) );
add_action( 'wp_head', array( 'Logika_Theme_City_Faq_Schema', 'render' ) );
add_action( 'wp_head', array( 'Logika_Theme_Course_Schema', 'render' ) );

function logika_theme_is_managed_page( int $post_id ): bool {
	if ( 'page' !== get_post_type( $post_id ) ) {
		return false;
	}

	$slug = (string) get_post_field( 'post_name', $post_id );

	return (int) get_option( 'page_on_front' ) === $post_id || in_array( $slug, array( 'about', 'faq', 'it-courses', 'english-courses', 'media-center', 'privacy-policy', 'contractoffer', 'contractoffer-overseas', 'litsenziia', 'vacancies', 'camps' ), true );
}

add_filter(
	'use_block_editor_for_post',
	static function ( bool $use_block_editor, WP_Post $post ): bool {
		return logika_theme_is_managed_page( (int) $post->ID ) ? false : $use_block_editor;
	},
	10,
	2
);

add_action(
	'admin_init',
	static function (): void {
		$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( $post_id && logika_theme_is_managed_page( $post_id ) ) {
			remove_post_type_support( 'page', 'editor' );
		}
	}
);

function logika_theme_render_source_page( string $source ): void {
	Logika_Theme_Source_Markup::renderPage( $source );
}
