<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$errors = array();
$fields = acf_get_fields( 'group_logika_home' );
$projects = $fields ? current( array_filter( $fields, static fn( array $item ): bool => 'home_portfolio_items' === $item['name'] ) ) : false;

if ( ! $projects || 'repeater' !== $projects['type'] ) {
	$errors[] = 'Homepage projects are not editable through an ACF repeater.';
}

$project_fields = $projects['sub_fields'] ?? array();
foreach ( array( 'variant', 'student_name', 'student_age', 'course', 'topic', 'description', 'student_image', 'project_image', 'video_url', 'cta_label', 'cta_url' ) as $field_name ) {
	if ( ! current( array_filter( $project_fields, static fn( array $item ): bool => $field_name === $item['name'] ) ) ) {
		$errors[] = "Homepage project field {$field_name} is missing.";
	}
}

ob_start();
logika_theme_render_source_page( 'index' );
$homepage = (string) ob_get_clean();

foreach ( array( 'portfolio-section', 'portfolio-section__slider', 'portfolio-section__card', 'portfolio-section__card--featured' ) as $marker ) {
	if ( ! str_contains( $homepage, $marker ) ) {
		$errors[] = "Homepage does not render {$marker}.";
	}
}

$front_page_id = (int) get_option( 'page_on_front' );
$saved_items   = get_field( 'home_portfolio_items', $front_page_id, false );

try {
	update_field(
		'home_portfolio_items',
		array(
			array(
				'variant'     => 'featured',
				'student_name' => 'Тестовий учень',
				'student_age'  => '12 років',
				'course'       => 'Python Start',
				'topic'        => 'Python',
				'description'  => 'Тестовий опис проєкту.',
				'video_url'    => 'https://example.test/video',
				'cta_label'    => 'Тестовий CTA',
				'cta_url'      => '#lead-form',
			),
		),
		$front_page_id
	);

	ob_start();
	logika_theme_render_source_page( 'index' );
	$dynamic_homepage = (string) ob_get_clean();

	foreach ( array( 'Тестовий учень', 'Тестовий опис проєкту.', 'https://example.test/video', 'Тестовий CTA' ) as $value ) {
		if ( ! str_contains( $dynamic_homepage, $value ) ) {
			$errors[] = "Homepage does not render editable project value {$value}.";
		}
	}
} finally {
	if ( false === $saved_items ) {
		delete_post_meta( $front_page_id, 'home_portfolio_items' );
		delete_post_meta( $front_page_id, '_home_portfolio_items' );
	} else {
		update_field( 'home_portfolio_items', $saved_items, $front_page_id );
	}
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Homepage student projects are editable and render correctly.\n";
