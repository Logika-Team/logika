<?php
/** Template Name: FAQ */
get_header();
Logika_Theme_Fixed_Page::render( 'faq', get_queried_object_id() );
get_footer();
