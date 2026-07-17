<?php
/** Template Name: English Courses */
get_header();
Logika_Theme_Fixed_Page::render( 'en-courses', get_queried_object_id() );
get_footer();
