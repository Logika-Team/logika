<?php

declare(strict_types=1);

$theme = dirname(__DIR__) . '/wordpress/wp-content/themes/logika-theme';
$courses = file_get_contents($theme . '/source-pages/it-courses.php') ?: '';
$header = file_get_contents($theme . '/source-pages/header.php') ?: '';
$styles = file_get_contents($theme . '/assets/css/courses-responsive.css') ?: '';
$theme_styles = file_get_contents($theme . '/assets/css/style.css') ?: '';
$adaptive_styles = file_get_contents($theme . '/assets/css/adaptive.css') ?: '';
$scripts = file_get_contents($theme . '/assets/js/main.js') ?: '';
$functions = file_get_contents($theme . '/functions.php') ?: '';

foreach (array('it-course', 'it-courses') as $page) {
	$template = file_get_contents("{$theme}/source-pages/{$page}.php") ?: '';
	foreach (array('testimonials-section__title', 'testimonials-section__slider', "<div class='swiper-container'>", "<ul class='swiper-wrapper'>", 'testimonial2.webp', 'testimonial3.webp', 'testimonial4.webp') as $markup) {
		if (!str_contains($template, $markup)) {
			fwrite(STDERR, "{$page} runtime template is missing {$markup}.\n");
			exit(1);
		}
	}
}

foreach (array('service1.webp', 'service2.webp', 'service3.webp', 'service4.webp') as $image) {
	if (!str_contains($courses, $image)) {
		fwrite(STDERR, "IT courses runtime markup is missing {$image}.\n");
		exit(1);
	}
}

foreach (array('grid-template-columns:100%', 'top:30px') as $rule) {
	if (!str_contains($styles, $rule)) {
		fwrite(STDERR, "IT courses runtime CSS is missing {$rule}.\n");
		exit(1);
	}
}

foreach (array('transform: translateX(110%);', 'transition: 0.3s ease;') as $rule) {
	if (!str_contains($theme_styles, $rule)) {
		fwrite(STDERR, "Mobile menu runtime CSS is missing {$rule}.\n");
		exit(1);
	}
}

if (!str_contains($adaptive_styles, '.mobile{display:contents}')) {
	fwrite(STDERR, "Desktop mobile-menu reset is missing.\n");
	exit(1);
}

if (strpos($header, '<div class="header__top">') > strpos($header, '<header class="header fixed-block">')) {
	fwrite(STDERR, "Header top bar must remain outside the sticky white header.\n");
	exit(1);
}

if (!str_contains($scripts, 'elementHeight(header, "header-height", headerTop);')) {
	fwrite(STDERR, "Mobile menu height must include the top bar.\n");
	exit(1);
}

foreach (array('.header__wrapp{display:contents}', '.header__location{width:100%;max-width:none;flex:0 0 auto;min-height:40px}', '.header__nav{width:100%;overflow-y:auto;flex-grow:1}') as $rule) {
	if (!str_contains($styles, $rule)) {
		fwrite(STDERR, "Mobile menu runtime CSS is missing {$rule}.\n");
		exit(1);
	}
}

foreach (array('.mobile{top:0;height:100svh}', '.mobile__box{padding-top:calc(') as $override) {
	if (str_contains($styles, $override)) {
		fwrite(STDERR, "Mobile menu runtime CSS overrides main: {$override}.\n");
		exit(1);
	}
}

if (!str_contains($functions, "'logika-courses-responsive'")) {
	fwrite(STDERR, "IT courses runtime CSS is not enqueued.\n");
	exit(1);
}

if (!str_contains($scripts, '360: {') || !str_contains($scripts, 'slidesPerView: 1.15')) {
	fwrite(STDERR, "IT courses runtime slider is missing mobile breakpoints.\n");
	exit(1);
}

echo "IT courses runtime artwork matches main.\n";
