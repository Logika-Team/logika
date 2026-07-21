<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$course = get_page_by_path( 'english-b2-1', OBJECT, 'course' );
$expected_text = 'Обговорюємо складні теми, розширюємо словниковий запас та вчимося впевнено спілкуватися англійською.';

if ( ! $course || get_field( 'course_hero_text', $course->ID ) !== $expected_text ) {
	fwrite( STDERR, "B2.1 hero text is not shortened.\n" );
	exit( 1 );
}

ob_start();
get_template_part( 'template-parts/courses/english', null, array( 'course_id' => $course->ID ) );
$markup = (string) ob_get_clean();
$hero = preg_match( '~<section class="english-course-hero">.*?</section>~s', $markup, $matches ) ? $matches[0] : '';

if ( ! str_contains( $hero, '/assets/img/english-levels/characters/b2-1-reader.png' ) || str_contains( $hero, 'logika-b2-1.png' ) ) {
	fwrite( STDERR, "B2.1 hero must use the transparent character asset.\n" );
	exit( 1 );
}

if ( ! str_contains( $markup, 'href="' . home_url( '/english-courses/' ) . '">Курси</a>' ) ) {
	fwrite( STDERR, "English course breadcrumbs must link to the English courses page.\n" );
	exit( 1 );
}

if ( str_contains( $markup, 'class="accordion__btn" data-id="english-program-' ) && str_contains( $markup, '<span aria-hidden="true">+</span>' ) ) {
	fwrite( STDERR, "English course program buttons must not duplicate the plus icon.\n" );
	exit( 1 );
}

if ( ! str_contains( $markup, 'english-course-catalog' ) ) {
	fwrite( STDERR, "English course catalog is missing.\n" );
	exit( 1 );
}

echo "B2.1 hero text and transparent character asset are correct.\n";
