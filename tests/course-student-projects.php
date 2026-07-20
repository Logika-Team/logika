<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$errors = array();
$source = (string) file_get_contents( dirname(__DIR__) . '/wordpress/wp-content/themes/logika-theme/source-pages/it-course.php' );
foreach ( array( 'href="#lead-form"', 'href="#course-program"', 'id="course-program"' ) as $value ) {
	if ( ! str_contains( $source, $value ) ) {
		$errors[] = "Course hero action {$value} is missing.";
	}
}
$fields = acf_get_fields( 'group_logika_course' );
$projects = $fields ? current( array_filter( $fields, static fn( array $item ): bool => 'course_projects' === $item['name'] ) ) : false;

if ( ! $projects || 'repeater' !== $projects['type'] ) {
	$errors[] = 'Course projects are not editable through an ACF repeater.';
}

if ( $projects && 'block' !== $projects['layout'] ) {
	$errors[] = 'Course project cards must use the compact ACF block layout.';
}

$project_fields = $projects['sub_fields'] ?? array();
foreach ( array( 'variant', 'student_name', 'student_age', 'course', 'topic', 'description', 'student_image', 'project_image', 'video_url', 'cta_label', 'cta_url' ) as $field_name ) {
	if ( ! current( array_filter( $project_fields, static fn( array $item ): bool => $field_name === $item['name'] ) ) ) {
		$errors[] = "Course project field {$field_name} is missing.";
	}
}

$course_id = wp_insert_post( array( 'post_type' => 'course', 'post_status' => 'publish', 'post_title' => 'Проєктний курс' ) );
$saved_items = get_field( 'course_projects', $course_id, false );
$programming_start = get_page_by_path( 'programming-start', OBJECT, 'course' );

if ( ! $programming_start || ! get_field( 'course_program', $programming_start->ID ) ) {
	$errors[] = 'Programming Start has no editable course program.';
}

try {
	update_field( 'course_projects', array(
		array( 'variant' => 'standard', 'student_name' => 'Тестова учениця', 'student_age' => '11 років', 'course' => 'Python Start', 'topic' => 'Python', 'description' => 'Стандартний проєкт.' ),
		array( 'variant' => 'featured', 'student_name' => 'Тестовий учень', 'student_age' => '12 років', 'course' => 'Python Start', 'description' => 'Виділений проєкт.', 'video_url' => 'https://example.test/video', 'cta_label' => 'Тестовий CTA', 'cta_url' => '#lead-form' ),
	), $course_id );

	ob_start();
	Logika_Theme_Source_Markup::renderPage( 'it-course', $course_id );
	$html = (string) ob_get_clean();

	foreach ( array( 'portfolio-section__card', 'portfolio-section__card--featured', 'Тестова учениця', 'Виділений проєкт.', 'https://example.test/video', 'Тестовий CTA' ) as $value ) {
		if ( ! str_contains( $html, $value ) ) {
			$errors[] = "Course does not render editable project value {$value}.";
		}
	}
	if ( ! str_contains( $html, 'portfolio-section--course' ) ) {
		$errors[] = 'Course portfolio has no centered layout modifier.';
	}
} finally {
	if ( false === $saved_items ) {
		delete_post_meta( $course_id, 'course_projects' );
		delete_post_meta( $course_id, '_course_projects' );
	} else {
		update_field( 'course_projects', $saved_items, $course_id );
	}
	wp_delete_post( $course_id, true );
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Course student projects are editable and render correctly.\n";
