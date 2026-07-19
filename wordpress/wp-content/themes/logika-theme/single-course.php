<?php

declare(strict_types=1);

get_header();
$course_id = get_queried_object_id();
if ( has_term( 'english', 'course_direction', $course_id ) ) {
	get_template_part( 'template-parts/courses/english', null, array( 'course_id' => $course_id ) );
} else {
	logika_theme_render_source_page( 'it-course' );
}
get_footer();
