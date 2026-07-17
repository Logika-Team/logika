<?php

declare(strict_types=1);

require dirname( __DIR__ ) . '/wordpress/wp-load.php';

$page     = get_page_by_path( 'it-courses' );
$page_id  = $page instanceof WP_Post ? $page->ID : 0;
$original = $page_id ? get_field( 'it_courses_age_categories', $page_id ) : null;
$titles   = array( 'Python Start', 'Python Mastery', 'Графічний дизайн', 'Графічний дизайн 2.0', 'Створення веб-сайтів' );
$errors   = array();

if ( ! $page_id ) {
	$errors[] = 'IT Courses page is missing.';
} else {
	register_shutdown_function(
		static function () use ( $page_id, $original ): void {
			if ( $original ) {
				update_field( 'it_courses_age_categories', $original, $page_id );
			} else {
				delete_field( 'it_courses_age_categories', $page_id );
			}
		}
	);

	update_field(
		'it_courses_age_categories',
		array(
			array(
				'title'             => 'Курси для дітей 12-14 років',
				'courses'           => array(),
				'placeholder_cards' => array_map( static fn( string $title ): array => array( 'title' => $title ), $titles ),
			)
		),
		$page_id
	);
	ob_start();
	Logika_Theme_Source_Markup::renderPage( 'it-courses', $page_id );
	$markup = (string) ob_get_clean();
	foreach ( $titles as $title ) {
		if ( ! str_contains( $markup, '<span class="course-card__title h4">' . $title . '</span>' ) ) {
			$errors[] = "IT Courses does not render placeholder {$title}.";
		}
	}
}

$schema = json_decode( (string) file_get_contents( dirname( __DIR__ ) . '/wordpress/wp-content/plugins/logika-core/acf-json/group_logika_page_it_courses.json' ), true );
$category = current( array_filter( $schema['fields'] ?? array(), static fn( array $field ): bool => 'it_courses_age_categories' === ( $field['name'] ?? '' ) ) );
$fields = is_array( $category ) ? array_column( $category['sub_fields'] ?? array(), 'name' ) : array();
if ( ! in_array( 'placeholder_cards', $fields, true ) ) {
	$errors[] = 'IT Courses ACF does not expose placeholder cards.';
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "IT Courses renders editable placeholder cards.\n";
