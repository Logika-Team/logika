<?php

declare(strict_types=1);

get_header();

while ( have_posts() ) :
	the_post();
	Logika_Theme_City_Page::renderHome( get_the_ID() );
endwhile;

get_footer();
