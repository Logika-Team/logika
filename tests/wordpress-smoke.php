<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$errors = array();

foreach ( array( 'city', 'branch', 'course', 'camp', 'review', 'faq_item' ) as $post_type ) {
	if ( ! post_type_exists( $post_type ) ) {
		$errors[] = "CPT {$post_type} is not registered.";
	}
}

if ( ! function_exists( 'acf_get_field_groups' ) ) {
	$errors[] = 'ACF Pro is not active.';
} else {
	$titles = wp_list_pluck( acf_get_field_groups(), 'title' );

	foreach ( array( 'Місто', 'Філія', 'Курс', 'Табір', 'Відгук', 'FAQ', 'Глобальні налаштування' ) as $title ) {
		if ( ! in_array( $title, $titles, true ) ) {
			$errors[] = "ACF group {$title} is not available.";
		}
	}
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "WordPress content model is available.\n";
