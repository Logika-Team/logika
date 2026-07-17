<?php

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/src/LeadForm.php';
require_once get_template_directory() . '/src/PageContent.php';
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
	register_nav_menus( array( 'primary' => 'Головне меню' ) );
}
add_action( 'after_setup_theme', 'logika_theme_setup' );

function logika_theme_assets(): void {
	$uri     = get_template_directory_uri() . '/assets';
	$version = wp_get_theme()->get( 'Version' );
	$style_version = (string) filemtime( get_template_directory() . '/assets/css/style.css' );
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
	wp_enqueue_style( 'logika-intl-tel-input', $uri . '/css/vendor/intl-tel-input/intlTelInput.min.css', array(), '20.1.0' );
	wp_enqueue_style( 'logika-theme', $uri . '/css/style.css', array( 'logika-intl-tel-input' ), $style_version );
	wp_add_inline_style( 'logika-theme', '@media (hover:hover){.header__login:hover{transform:none;box-shadow:none}.header__login:hover svg{transform:none}}.search-form__input::-webkit-search-cancel-button{width:18px;height:18px;margin-right:2px;background:url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 18 18\'%3E%3Cpath d=\'m4 4 10 10M14 4 4 14\' fill=\'none\' stroke=\'%23602B7A\' stroke-linecap=\'round\' stroke-width=\'1.5\'/%3E%3C/svg%3E") center/contain no-repeat;-webkit-appearance:none;cursor:pointer}' );
	wp_enqueue_style( 'logika-school-map-style', $uri . '/css/blocks/sections/school-map.css', array( 'logika-theme' ), $map_style_version );
	wp_enqueue_style( 'logika-faq-banner-style', $uri . '/css/blocks/sections/faq-banner-section.css', array( 'logika-theme' ), $faq_banner_style_version );
	if ( is_page( 'faq' ) ) {
		wp_enqueue_style( 'logika-faq-accordion', $uri . '/css/faq-accordion.css', array( 'logika-theme' ), $faq_accordion_style_version );
	}
	if ( is_front_page() || get_query_var( 'logika_city' ) ) {
		wp_enqueue_style( 'logika-home-media-center', $uri . '/css/blocks/sections/media-section.css', array( 'logika-theme' ), $home_media_center_version );
	}
	if ( is_singular( 'camp' ) || is_post_type_archive( 'camp' ) ) {
		foreach ( array( 'trips-section', 'details-section', 'gallery-section' ) as $section ) {
			wp_enqueue_style( "logika-{$section}", "{$uri}/css/blocks/sections/{$section}.css", array( 'logika-theme' ), (string) filemtime( get_template_directory() . "/assets/css/blocks/sections/{$section}.css" ) );
		}
	}
	if ( is_singular( 'course' ) ) {
		foreach ( array( 'course-banner-section', 'learn-section', 'process-section' ) as $section ) {
			wp_enqueue_style( "logika-{$section}", "{$uri}/css/blocks/sections/{$section}.css", array( 'logika-theme' ), (string) filemtime( get_template_directory() . "/assets/css/blocks/sections/{$section}.css" ) );
		}
	}
	wp_enqueue_style( 'logika-phone-dropdown-dropup', $uri . '/css/phone-dropdown-dropup.css', array( 'logika-theme' ), $phone_dropup_version );
	wp_enqueue_script( 'logika-swiper', $uri . '/js/swiper.js', array(), $version, true );
	wp_enqueue_script( 'logika-theme', $uri . '/js/main.js', array( 'logika-swiper' ), $main_version, true );
	wp_enqueue_script( 'logika-city-context', $uri . '/js/city-context.js', array(), $city_context_version, true );
	wp_localize_script( 'logika-city-context', 'logikaCityContextConfig', array( 'endpoint' => esc_url_raw( rest_url( 'logika/v1/cities' ) ) ) );
	wp_enqueue_script( 'logika-school-map', $uri . '/js/camp-map.js', array( 'logika-city-context' ), $map_version, true );
	wp_localize_script( 'logika-school-map', 'logikaThemeAssets', array( 'mapUrl' => esc_url_raw( $uri . '/img/maps/ukraine-regions.svg' ), 'branchesEndpoint' => esc_url_raw( rest_url( 'logika/v1/cities/' ) ) ) );
	wp_enqueue_script( 'logika-intl-tel-input', $uri . '/js/vendor/intl-tel-input/intlTelInput.min.js', array(), '20.1.0', true );
	wp_enqueue_script( 'logika-intl-tel-input-i18n-uk', $uri . '/js/vendor/intl-tel-input/i18n-uk.js', array( 'logika-intl-tel-input' ), $version, true );
	wp_enqueue_script( 'logika-leads', $uri . '/js/leads.js', array( 'logika-intl-tel-input-i18n-uk', 'logika-city-context' ), $leads_version, true );
	wp_localize_script( 'logika-leads', 'logikaLead', array( 'endpoint' => esc_url_raw( rest_url( 'logika/v1/leads' ) ), 'tokenEndpoint' => esc_url_raw( rest_url( 'logika/v1/forms/token' ) ), 'cityEndpoint' => esc_url_raw( rest_url( 'logika/v1/cities' ) ), 'phoneCountryDefault' => 'UA', 'phoneCountryEndpoint' => esc_url_raw( rest_url( 'logika/v1/phone-country' ) ), 'phoneUtilsUrl' => esc_url_raw( $uri . '/js/vendor/intl-tel-input/utils.js' ) ) );
	wp_enqueue_script( 'logika-city-selector', $uri . '/js/city-selector.js', array( 'logika-city-context' ), $city_selector_version, true );
	if ( is_page( 'media-center' ) || get_query_var( 'logika_blog' ) ) {
		wp_enqueue_style( 'logika-media-search', $uri . '/css/media-search.css', array( 'logika-theme' ), $media_search_version );
		wp_enqueue_script( 'logika-media-center', $uri . '/js/media-center.js', array( 'logika-city-context' ), $media_center_version, true );
		wp_localize_script( 'logika-media-center', 'logikaMediaCenter', array( 'endpoint' => esc_url_raw( rest_url( 'logika/v1/media' ) ) ) );
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

	return (int) get_option( 'page_on_front' ) === $post_id || in_array( $slug, array( 'about', 'faq', 'it-courses', 'english-courses', 'media-center', 'privacy-policy', 'contractoffer', 'contractoffer-overseas', 'litsenziia' ), true );
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
