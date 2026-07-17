<?php
/**
 * Template Name: Конструктор сторінки
 */

declare(strict_types=1);

get_header();
while ( have_posts() ) {
	the_post();
	Logika_Theme_Generic_Page::render( get_the_ID() );
}
get_footer();
