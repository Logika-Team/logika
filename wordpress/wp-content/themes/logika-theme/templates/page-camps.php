<?php
/** Template Name: Табори */
get_header();
Logika_Theme_Fixed_Page::render( 'camps', get_queried_object_id() );
get_footer();
