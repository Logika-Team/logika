<?php
/** Template Name: About */
get_header();
Logika_Theme_Fixed_Page::render( 'about', get_queried_object_id() );
get_footer();
