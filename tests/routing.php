<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$routing = file_get_contents( get_template_directory() . '/src/Routing.php' ) ?: '';
$errors = array();

foreach ( array( 'about.html', 'faq.html', 'it-courses.html', 'en-courses.html', 'camps.html', 'media-center.html', 'article.html', 'it-course.html', 'camp.html', 'city.html', '^media-center/([^/]+)/?$', 'litsenziia', 'privacy_policy', 'contractoffer', 'pythonstart', 'map/nezhen' ) as $marker ) {
	if ( ! str_contains( $routing, $marker ) ) {
		$errors[] = "Routing is missing {$marker}.";
	}
}

$created_posts = array();
$default_category = (int) get_option( 'default_category' );
foreach (
	array(
		'post'   => array( 'test-routing-post', '/media-center/articles/test-routing-post/' ),
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

$media_terms = array();
foreach ( array( 'news' => 'Новини', 'articles' => 'Статті', 'offers' => 'Акції' ) as $slug => $name ) {
	$term = get_term_by( 'slug', $slug, 'category' );
	if ( ! $term ) {
		$term = wp_insert_term( $name, 'category', array( 'slug' => $slug ) );
		if ( ! is_wp_error( $term ) ) {
			$media_terms[] = (int) $term['term_id'];
			$term = get_term( $term['term_id'], 'category' );
		}
	}
	if ( ! $term || is_wp_error( $term ) ) {
		$errors[] = "Media category {$slug} is unavailable.";
	}
}

$article = get_page_by_path( 'test-routing-post', OBJECT, 'post' );
if ( $article instanceof WP_Post ) {
	foreach ( array( 'news', 'articles', 'offers' ) as $category ) {
		wp_set_object_terms( $article->ID, $category, 'category', false );
		if ( ! str_ends_with( (string) get_permalink( $article ), "/media-center/{$category}/test-routing-post/" ) ) {
			$errors[] = "Article permalink does not include {$category}.";
		}
	}
	wp_set_object_terms( $article->ID, 'articles', 'category', false );
	wp_set_object_terms( $article->ID, array( 'news', 'offers' ), 'category', false );
	if ( array( 'news' ) !== wp_get_post_terms( $article->ID, 'category', array( 'fields' => 'slugs' ) ) ) {
		$errors[] = 'A media article must have one canonical category.';
	}
	wp_set_object_terms( $article->ID, 'articles', 'category', false );
	$breadcrumbs = Logika_Theme_Article_Page::render( $article->ID );
	foreach ( array( '/media-center/', '/media-center/articles/', 'Медіа-центр', 'Статті' ) as $expected ) {
		if ( ! str_contains( $breadcrumbs, $expected ) ) {
			$errors[] = "Article breadcrumbs are missing {$expected}.";
		}
	}
}

$dry_run_post = wp_insert_post( array( 'post_type' => 'post', 'post_status' => 'draft', 'post_title' => 'Dry-run media fixture' ) );
$created_posts[] = $dry_run_post;
wp_set_object_terms( $dry_run_post, 1, 'category', false );
update_option( 'default_category', 1 );
Logika\Core\ContentMigration::run( true );
if ( 1 !== (int) get_option( 'default_category' ) || array( 1 ) !== array_map( 'intval', wp_get_post_terms( $dry_run_post, 'category', array( 'fields' => 'ids' ) ) ) ) {
	$errors[] = 'Media migration dry-run must not change categories or defaults.';
}
update_option( 'default_category', $default_category );

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

foreach ( $media_terms as $term_id ) {
	wp_delete_term( $term_id, 'category' );
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Canonical routes and legacy redirects are registered.\n";
