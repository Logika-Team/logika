<?php

declare(strict_types=1);

require dirname( __DIR__ ) . '/wordpress/wp-load.php';

$page_id  = (int) get_option( 'page_on_front' );
$original = get_field( 'home_programming_courses', $page_id );
$rows     = array(
	array(
		'chips' => array(
			array( 'label' => 'Чип із власним посиланням', 'url' => 'https://example.test/course/' ),
			array( 'label' => 'Чип із заглушкою', 'url' => '' ),
		),
	),
);

register_shutdown_function(
	static function () use ( $page_id, $original ): void {
		if ( $original ) {
			update_field( 'home_programming_courses', $original, $page_id );
		} else {
			delete_field( 'home_programming_courses', $page_id );
		}
	}
);

update_field( 'home_programming_courses', $rows, $page_id );
ob_start();
logika_theme_render_source_page( 'index' );
$homepage = (string) ob_get_clean();
$errors   = array();

foreach (
	array(
		'<a href="https://example.test/course/" class="h5">Чип із власним посиланням</a>',
		'<a href="' . esc_url( home_url( '/courses/programming-projects/' ) ) . '" class="h5">Чип із заглушкою</a>',
	) as $expected
) {
	if ( ! str_contains( $homepage, $expected ) ) {
		$errors[] = "Homepage does not render editable course chip: {$expected}";
	}
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Homepage course chips use editable links with a fallback.\n";
