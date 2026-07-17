<?php
/** Template Name: IT Courses */
get_header();
Logika_Theme_Fixed_Page::render( 'it-courses', get_queried_object_id() );
get_footer();
