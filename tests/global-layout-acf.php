<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$option_names = array( 'global_phone', 'global_email', 'global_header_logo', 'global_footer_logo', 'global_social_links', 'global_footer_accreditation', 'global_footer_copyright', 'global_privacy_policy_url' );
$options      = array();
foreach ( $option_names as $name ) {
	$options[ $name ] = array( get_option( "options_{$name}", null ), get_option( "_options_{$name}", null ) );
}
$locations_before = get_theme_mod( 'nav_menu_locations', array() );
$locations        = $locations_before;
$menus     = array();
$errors    = array();
$attachment = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'posts_per_page' => 1, 'fields' => 'ids' ) )[0] ?? 0;
$image_url  = $attachment ? wp_get_attachment_image_url( (int) $attachment, 'full' ) : '';

try {
	update_field( 'field_global_phone', '+380 44 123 45 67', 'option' );
	update_field( 'field_global_email', 'acf-layout@example.com', 'option' );
	update_field( 'field_global_header_logo', $attachment, 'option' );
	update_field( 'field_global_footer_logo', $attachment, 'option' );
	update_field( 'field_global_social_links', array( array( 'label' => 'ACF Social', 'url' => 'https://example.com/acf-social', 'icon' => $attachment ) ), 'option' );
	update_field( 'field_global_footer_accreditation', 'ACF-керований опис школи', 'option' );
	update_field( 'field_global_footer_copyright', '© 2026 ACF Logika', 'option' );
	update_field( 'field_global_privacy_policy_url', 'https://example.com/acf-policy', 'option' );

	foreach ( array( 'primary' => 'Головне тестове меню', 'footer_navigation' => 'Навігація тест', 'footer_information' => 'Інформація тест' ) as $location => $title ) {
		$menu_id = wp_create_nav_menu( $title );
		$menus[] = $menu_id;
		wp_update_nav_menu_item( $menu_id, 0, array( 'menu-item-title' => $title, 'menu-item-url' => home_url( '/' . $location . '/' ), 'menu-item-status' => 'publish' ) );
		$locations[ $location ] = $menu_id;
	}
	set_theme_mod( 'nav_menu_locations', $locations );

	ob_start();
	Logika_Theme_Source_Markup::renderFragment( 'header' );
	$header = (string) ob_get_clean();
	ob_start();
	Logika_Theme_Source_Markup::renderFragment( 'footer' );
	$footer = (string) ob_get_clean();

	foreach ( array( '+380 44 123 45 67', 'acf-layout@example.com', 'Головне тестове меню', 'https://example.com/acf-social', $image_url ) as $expected ) {
		if ( ! str_contains( $header, $expected ) ) {
			$errors[] = "Header is missing {$expected}.";
		}
	}
	foreach ( array( '+380 44 123 45 67', 'acf-layout@example.com', 'ACF-керований опис школи', '© 2026 ACF Logika', 'Навігація тест', 'Інформація тест', 'https://example.com/acf-social', 'https://example.com/acf-policy', $image_url ) as $expected ) {
		if ( ! str_contains( $footer, $expected ) ) {
			$errors[] = "Footer is missing {$expected}.";
		}
	}
} finally {
	foreach ( $menus as $menu_id ) {
		wp_delete_nav_menu( $menu_id );
	}
	set_theme_mod( 'nav_menu_locations', $locations_before );
	foreach ( $options as $name => $values ) {
		foreach ( array( "options_{$name}", "_options_{$name}" ) as $index => $option_name ) {
			if ( null === $values[ $index ] ) {
				delete_option( $option_name );
			} else {
				update_option( $option_name, $values[ $index ] );
			}
		}
	}
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Header and footer use Global Options and WordPress menus.\n";
