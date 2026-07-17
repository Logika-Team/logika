<?php
/** Template Name: Media Center */
get_header();
Logika_Theme_Fixed_Page::render( 'media-center', get_queried_object_id() );
get_footer();
