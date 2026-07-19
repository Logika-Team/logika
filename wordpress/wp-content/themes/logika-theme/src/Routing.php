<?php

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

final class Logika_Theme_Routing {
	private const REWRITE_VERSION = '8';

	private const LEGACY_ROUTES = array(
		'about.html' => '/about/', 'faq.html' => '/faq/', 'it-courses.html' => '/it-courses/', 'en-courses.html' => '/english-courses/', 'camps.html' => '/camps/', 'media-center.html' => '/media-center/', 'article.html' => '/media-center/', 'it-course.html' => '/courses/', 'camp.html' => '/camps/', 'city.html' => '/', 'litsenziia.html' => '/litsenziia/',
	);

	private const LEGACY_CITY_SLUGS = array( 'map/kuiv' => 'kyiv' );

	private const LEGACY_COURSE_SLUGS = array(
		'english_a0' => 'english-a0', 'english_a1' => 'english-a1', 'english_a2' => 'english-a2',
		'english_b1' => 'english-b1', 'english_b2' => 'english-b2', 'english_b2_1' => 'english-b2-1',
	);

	public static function register(): void {
		add_action( 'init', array( self::class, 'rewriteRules' ), 20 );
		add_action( 'init', array( self::class, 'flushRules' ), 99 );
		add_action( 'parse_request', array( self::class, 'resolveCityHomepage' ) );
		add_filter( 'query_vars', array( self::class, 'queryVars' ) );
		add_filter( 'post_type_link', array( self::class, 'postTypeLink' ), 10, 2 );
		add_filter( 'post_link', array( self::class, 'postLink' ), 10, 2 );
		add_filter( 'redirect_canonical', array( self::class, 'redirectCanonical' ), 10, 2 );
		add_action( 'template_redirect', array( self::class, 'validateContextCity' ), 1 );
		add_action( 'template_redirect', array( self::class, 'redirectLegacy' ) );
		add_action( 'template_redirect', array( self::class, 'redirectMediaCanonical' ), 2 );
		add_filter( 'template_include', array( self::class, 'blogTemplate' ) );
	}

	public static function rewriteRules(): void {
		add_rewrite_rule( '^cities/([^/]+)/?$', 'index.php?logika_city=$matches[1]', 'top' );
		add_rewrite_rule( '^media-center/(news|articles|offers)/([^/]+)/?$', 'index.php?post_type=post&name=$matches[2]&logika_media_category=$matches[1]', 'top' );
		add_rewrite_rule( '^media-center/(news|articles|offers)/?$', 'index.php?logika_media_category=$matches[1]', 'top' );
		add_rewrite_rule( '^media-center/([^/]+)/?$', 'index.php?logika_legacy_article=$matches[1]', 'top' );
		add_rewrite_rule( '^blog/?$', 'index.php?logika_legacy_blog=1', 'top' );
	}

	public static function queryVars( array $vars ): array {
		$vars[] = 'logika_city';
		$vars[] = 'logika_blog';
		$vars[] = 'logika_media_category';
		$vars[] = 'logika_legacy_article';
		$vars[] = 'logika_legacy_blog';

		return $vars;
	}

	public static function blogTemplate( string $template ): string {
		return get_query_var( 'logika_blog' ) || ( get_query_var( 'logika_media_category' ) && ! is_singular( 'post' ) ) ? get_template_directory() . '/templates/page-blog.php' : $template;
	}

	public static function resolveCityHomepage( WP $wp ): void {
		if ( empty( $wp->query_vars['logika_city'] ) || ! get_option( 'page_on_front' ) ) {
			return;
		}

		$wp->query_vars['page_id'] = (int) get_option( 'page_on_front' );
	}

	public static function flushRules(): void {
		if ( self::REWRITE_VERSION === get_option( 'logika_routing_version' ) ) {
			return;
		}

		flush_rewrite_rules( false );
		update_option( 'logika_routing_version', self::REWRITE_VERSION );
	}

	public static function postTypeLink( string $url, WP_Post $post ): string {
		$base = array( 'course' => 'courses', 'camp' => 'camps', 'city' => 'cities' );

		if ( 'city' === $post->post_type ) {
			return \Logika\Core\CitySlug::url( $post );
		}

		return isset( $base[ $post->post_type ] ) ? home_url( '/' . $base[ $post->post_type ] . '/' . $post->post_name . '/' ) : $url;
	}

	public static function postLink( string $url, WP_Post $post ): string {
		return 'post' === $post->post_type ? home_url( '/media-center/' . \Logika\Core\MediaCategories::for( $post ) . '/' . $post->post_name . '/' ) : $url;
	}

	public static function mediaCategory( WP_Post|int $post ): string { return \Logika\Core\MediaCategories::for( $post ); }
	public static function mediaCategoryLabel( string $slug ): string { return \Logika\Core\MediaCategories::label( $slug ); }

	public static function redirectMediaCanonical(): void {
		if ( ! is_singular( 'post' ) || ! get_query_var( 'logika_media_category' ) ) { return; }
		$post = get_post( get_queried_object_id() );
		if ( $post instanceof WP_Post && get_query_var( 'logika_media_category' ) !== self::mediaCategory( $post ) ) { wp_safe_redirect( get_permalink( $post ), 301 ); exit; }
	}

	public static function redirectCanonical( $redirect, string $requested_url ) {
		return get_query_var( 'logika_city' ) ? false : $redirect;
	}

	public static function validateContextCity(): void {
		$slug = (string) get_query_var( 'logika_city' );
		$city = $slug ? \Logika\Core\CitySlug::find( $slug ) : null;

		if ( ! $slug || $city instanceof WP_Post && 'publish' === $city->post_status ) {
			return;
		}

		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
	}

	public static function legacyCityUrl( string $path ): ?string {
		$slug = self::LEGACY_CITY_SLUGS[ $path ] ?? ( str_starts_with( $path, 'map/' ) ? substr( $path, 4 ) : '' );
		$city = $slug ? \Logika\Core\CitySlug::find( $slug ) : null;

		return $city instanceof WP_Post ? \Logika\Core\CitySlug::url( $city ) : null;
	}

	public static function legacyCourseUrl( string $path ): ?string {
		$slug = self::LEGACY_COURSE_SLUGS[ $path ] ?? '';
		$course = $slug ? get_page_by_path( $slug, OBJECT, 'course' ) : null;

		return $course instanceof WP_Post ? get_permalink( $course ) : null;
	}

	public static function redirectLegacy(): void {
		if ( get_query_var( 'logika_legacy_blog' ) ) { wp_safe_redirect( home_url( '/media-center/articles/' ), 301 ); exit; }
		$legacy_article = (string) get_query_var( 'logika_legacy_article' );
		if ( $legacy_article ) {
			$post = get_page_by_path( $legacy_article, OBJECT, 'post' );
			if ( $post instanceof WP_Post ) { wp_safe_redirect( get_permalink( $post ), 301 ); exit; }
		}
		$path = trim( (string) wp_parse_url( (string) ( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH ), '/' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$city_url = self::legacyCityUrl( $path );
		if ( $city_url ) {
			wp_safe_redirect( $city_url, 301 );
			exit;
		}
		$course_url = self::legacyCourseUrl( $path );
		if ( $course_url ) {
			wp_safe_redirect( $course_url, 301 );
			exit;
		}

		if ( isset( self::LEGACY_ROUTES[ $path ] ) ) {
			wp_safe_redirect( home_url( self::LEGACY_ROUTES[ $path ] ), 301 );
			exit;
		}

		if ( str_ends_with( $path, '.html' ) ) {
			$post = get_page_by_path( substr( $path, 0, -5 ), OBJECT, 'post' );
			if ( $post instanceof WP_Post ) {
				wp_safe_redirect( get_permalink( $post ), 301 );
				exit;
			}
		}
	}
}
