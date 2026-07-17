<?php
/** Template Name: Юридична сторінка */
get_header();
Logika_Theme_Fixed_Page::renderLegal( get_queried_object_id() );
get_footer();
