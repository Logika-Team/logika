<?php

declare(strict_types=1);

get_header();

$source = Logika_Theme_Source_Markup::sourceForCurrentPage();

if ( $source ) {
		logika_theme_render_source_page( $source );
	} elseif ( have_posts() ) {
		while ( have_posts() ) {
			the_post();
			?>
			<main><article><?php the_content(); ?></article></main>
			<?php
		}
	}

get_footer();
