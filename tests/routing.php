<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$routing = file_get_contents( get_template_directory() . '/src/Routing.php' ) ?: '';
$errors = array();

foreach ( array( 'about.html', 'faq.html', 'it-courses.html', 'en-courses.html', 'camps.html', 'media-center.html', 'article.html', 'it-course.html', 'camp.html', 'city.html', '^media-center/([^/]+)/?$' ) as $marker ) {
	if ( ! str_contains( $routing, $marker ) ) {
		$errors[] = "Routing is missing {$marker}.";
	}
}

$created_posts = array();
foreach (
	array(
		'post'   => array( 'test-routing-post', '/media-center/test-routing-post/' ),
		'course' => array( 'test-routing-course', '/courses/test-routing-course/' ),
		'camp'   => array( 'test-routing-camp', '/camps/test-routing-camp/' ),
	) as $post_type => $route
) {
	$post = get_page_by_path( $route[0], OBJECT, $post_type );
	if ( ! $post ) {
		$post_id         = wp_insert_post( array( 'post_type' => $post_type, 'post_status' => 'publish', 'post_title' => 'Routing fixture', 'post_name' => $route[0] ) );
		$created_posts[] = $post_id;
	} else {
		$post_id = $post->ID;
	}
	$permalink = get_permalink( $post_id );
	if ( ! is_string( $permalink ) || ! str_ends_with( $permalink, $route[1] ) ) {
		$errors[] = "Post {$post_id} does not use {$route[1]}.";
	}
}

foreach (
	array(
		array( '<a href="#" class="services-section__item-about btn">Ознайомитись з курсами</a>', 'index', '/it-courses/' ),
		array( '<a href="#" class="english-section__link btn">Дізнатись більше</a>', 'index', '/english-courses/' ),
		array( '<a href="#" class="english-level__link btn">Обрати курс</a>', 'index', '/#lead-form' ),
		array( '<a href="#" class="transformation-section__link btn">Запис на безкоштовний урок</a>', 'index', '/#lead-form' ),
		array( '<a href="#" class="archive-section__promo-link btn">Дізнатись більше</a>', 'media-center', '/camps/' ),
		array( '<a href="#" class="news-section__btn btn">Переглянути усі</a>', 'media-center', '/blog/' ),
	) as $case
) {
	list( $markup, $source, $path ) = $case;
	$linked = Logika_Theme_Source_Markup::routeNavigationLinks( $markup, $source );
	preg_match( '#href="([^"]+)"#', $linked, $matches );
	if ( empty( $matches[1] ) || $path !== wp_parse_url( html_entity_decode( $matches[1] ), PHP_URL_PATH ) . ( str_contains( $path, '#' ) ? '#' . wp_parse_url( html_entity_decode( $matches[1] ), PHP_URL_FRAGMENT ) : '' ) ) {
		$errors[] = "CTA did not route to {$path}.";
	}
}

ob_start();
Logika_Theme_Source_Markup::renderPage( 'index' );
$home_markup = (string) ob_get_clean();
if ( ! str_contains( $home_markup, '<section id="lead-form" class="banner-section">' ) ) {
	$errors[] = 'The CTA anchor is not at the top of the hero section.';
}

foreach ( array( 'Course' => Logika_Theme_Course_Page::render( 1019 ), 'Camp' => Logika_Theme_Camp_Page::render( 1020 ) ) as $kind => $markup ) {
	if ( ! str_contains( $markup, 'href="#lead-form"' ) || ! str_contains( $markup, 'id="lead-form"' ) ) {
		$errors[] = "{$kind} CTA does not have a local lead-form target.";
	}
}

foreach ( glob( get_template_directory() . '/source-pages/*.php' ) as $file ) {
	$markup = file_get_contents( $file ) ?: '';
	$markup = Logika_Theme_Source_Markup::routeNavigationLinks( $markup, basename( $file, '.php' ) );
	preg_match_all( '~<a\b[^>]*\bhref=(["\'])#\1[^>]*>~', $markup, $anchors );
	foreach ( $anchors[0] as $anchor ) {
		if ( str_contains( $anchor, 'btn' ) && ! str_contains( $anchor, 'header__login' ) ) {
			$errors[] = "Navigation CTA remains unlinked in {$file}.";
		}
	}
}

foreach ( $created_posts as $post_id ) {
	wp_delete_post( $post_id, true );
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Canonical routes and legacy redirects are registered.\n";
