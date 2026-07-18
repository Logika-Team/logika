<?php
/** Template Name: Вакансії */

declare(strict_types=1);

get_header();
Logika_Theme_Fixed_Page::render( 'vacancies', get_queried_object_id() );
get_footer();
