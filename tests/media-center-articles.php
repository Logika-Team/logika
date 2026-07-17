<?php

declare(strict_types=1);

require dirname( __DIR__ ) . '/wordpress/wp-load.php';

$page   = file_get_contents( get_template_directory() . '/template-parts/pages/media-center.php' ) ?: '';
$source = file_get_contents( get_template_directory() . '/source-pages/media-center.php' ) ?: '';
$article = file_get_contents( get_template_directory() . '/src/ArticlePage.php' ) ?: '';
$script = file_get_contents( get_template_directory() . '/assets/js/media-center.js' ) ?: '';
$styles = file_get_contents( get_template_directory() . '/assets/css/media-search.css' ) ?: '';

foreach ( array( 'data-media-featured', 'data-media-list', 'news-card__thumbnail', 'news-card__tags', 'news-card__details' ) as $marker ) {
	if ( ! str_contains( $page, $marker ) ) {
		fwrite( STDERR, "Media center markup is missing {$marker}.\n" );
		exit( 1 );
	}
}

foreach ( array( 'data-media-featured', 'data-media-list' ) as $marker ) {
	if ( ! str_contains( $source, $marker ) ) {
		fwrite( STDERR, "Media center source markup is missing {$marker}.\n" );
		exit( 1 );
	}
}

if ( preg_match( '#data-media-featured>\s*<div class="news-card">|data-media-list>\s*<li class="news-section__item">#s', $source ) ) {
	fwrite( STDERR, "Media center source must not flash demo article cards before REST data loads.\n" );
	exit( 1 );
}

if ( ! str_contains( $source, 'data-media-search-suggestions' ) ) {
	fwrite( STDERR, "Media center search is missing a suggestions container.\n" );
	exit( 1 );
}

if ( str_contains( $source, 'search-form__btn' ) ) {
	fwrite( STDERR, "Media center search must not render a submit button.\n" );
	exit( 1 );
}

if ( str_contains( $article, 'search-form__btn' ) ) {
	fwrite( STDERR, "Article search must not render a submit button.\n" );
	exit( 1 );
}

foreach ( array( "document.createElement('picture')", 'news-card__thumbnail', 'news-card__tags', 'news-card__details', "searchForm?.addEventListener('submit'", "searchInput?.addEventListener('input'", 'renderSuggestions', "url.searchParams.set('search'" ) as $marker ) {
	if ( ! str_contains( $script, $marker ) ) {
		fwrite( STDERR, "Media center script does not render article cards: {$marker}.\n" );
		exit( 1 );
	}
}

if ( ! str_contains( $styles, '.search-form__suggestions' ) || ! str_contains( $styles, '[hidden]' ) ) {
	fwrite( STDERR, "Media center search suggestions need their dropdown styles.\n" );
	exit( 1 );
}

echo "Media center article sections use WordPress API cards.\n";
