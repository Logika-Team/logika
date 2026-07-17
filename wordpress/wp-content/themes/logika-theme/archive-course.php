<?php

declare(strict_types=1);

get_header();
$settings_page = get_page_by_path( 'it-courses' );
Logika_Theme_Fixed_Page::render( 'it-courses', $settings_page ? (int) $settings_page->ID : 0 );
get_footer();
