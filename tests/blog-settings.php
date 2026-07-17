<?php

declare(strict_types=1);

require dirname( __DIR__ ) . '/wordpress/wp-load.php';

$_SERVER['SERVER_NAME'] = $_SERVER['SERVER_NAME'] ?? 'logika.ddev.site';

$settings_page = get_page_by_path( 'media-center' );
if ( ! $settings_page instanceof WP_Post ) {
	fwrite( STDERR, "Media Center settings page is missing.\n" );
	exit( 1 );
}
$settings_id = (int) $settings_page->ID;

$values = array(
	'media_center_blog_title'          => 'Blog title fixture',
	'media_center_blog_sort_new_label' => 'Newest fixture',
	'media_center_blog_sort_old_label' => 'Oldest fixture',
	'media_center_blog_years_label'    => 'Years fixture',
);
$filter = static fn( mixed $value, mixed $post_id, array $field ): mixed => (int) $post_id === $settings_id && isset( $values[ $field['name'] ?? '' ] ) ? $values[ $field['name'] ] : $value;
add_filter( 'acf/load_value', $filter, 20, 3 );
ob_start();
require get_template_directory() . '/templates/page-blog.php';
$html = (string) ob_get_clean();
remove_filter( 'acf/load_value', $filter, 20 );

foreach ( $values as $expected ) {
	if ( ! str_contains( $html, $expected ) ) {
		fwrite( STDERR, "Blog does not read shared Media Center setting: {$expected}.\n" );
		exit( 1 );
	}
}

echo "Blog reads its labels from the shared Media Center settings.\n";
