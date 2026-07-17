<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$page_id = (int) get_option( 'page_on_front' );
$originals = array();
foreach (
	array(
		'home_programming_courses',
		'home_trust_items',
		'home_english_levels',
		'home_transformation_before_image',
		'home_transformation_after_image',
		'home_transformation_before_image_override',
		'home_transformation_after_image_override',
		'home_onboarding_steps',
		'home_certificates_image',
		'home_certificates_image_override',
		'home_partners_items',
		'home_image_gallery',
	) as $field_name
) {
	$originals[ $field_name ] = get_field( $field_name, $page_id );
}

$errors = array();

function logika_section_test_image( string $name ): int {
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$upload = wp_upload_dir();
	$file = trailingslashit( $upload['path'] ) . $name;
	file_put_contents( $file, base64_decode( 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMB/ax3p1sAAAAASUVORK5CYII=' ) );
	$id = wp_insert_attachment( array( 'post_title' => $name, 'post_mime_type' => 'image/png', 'post_status' => 'inherit' ), $file, $GLOBALS['page_id'] );
	wp_update_attachment_metadata( (int) $id, wp_generate_attachment_metadata( (int) $id, $file ) );

	return (int) $id;
}

$image_ids = array(
	'programming_image' => logika_section_test_image( 'home-section-programming-image-test.png' ),
	'programming_icon' => logika_section_test_image( 'home-section-programming-icon-test.png' ),
	'english_image' => logika_section_test_image( 'home-section-english-image-test.png' ),
	'transformation_before' => logika_section_test_image( 'home-section-transformation-before-test.png' ),
	'transformation_after' => logika_section_test_image( 'home-section-transformation-after-test.png' ),
	'onboarding_image' => logika_section_test_image( 'home-section-onboarding-image-test.png' ),
	'certificates_image' => logika_section_test_image( 'home-section-certificates-image-test.png' ),
	'partner_image' => logika_section_test_image( 'home-section-partner-image-test.png' ),
	'gallery_only' => logika_section_test_image( 'home-section-gallery-only-test.png' ),
	'default_preview' => logika_section_test_image( 'home-section-default-preview-test.png' ),
);
wp_update_post( array( 'ID' => $image_ids['default_preview'], 'post_title' => 'Головна 99 - Поточне превʼю' ) );

register_shutdown_function(
	static function () use ( $page_id, $originals, $image_ids ): void {
		foreach ( $originals as $field_name => $original_value ) {
			if ( null === $original_value || false === $original_value || '' === $original_value || array() === $original_value ) {
				delete_field( $field_name, $page_id );
			} else {
				update_field( $field_name, $original_value, $page_id );
			}
		}

		foreach ( $image_ids as $image_id ) {
			wp_delete_attachment( $image_id, true );
		}
	}
);

update_field(
	'home_programming_courses',
	array(
		array(
			'age' => '7-8 рокiв',
			'title' => 'Перший крок у свiт технологiй',
			'tags' => "Комп'ютерна грамотність",
			'text' => 'Допомагаємо дитині зробити перші впевнені кроки у світі технологій, розвиваючи логіку, увагу та базові комп’ютерні навички.',
			'lesson_label' => 'Запис на безкоштовний урок',
			'about_label' => 'Ознайомитись з курсами',
			'image_override' => $image_ids['programming_image'],
			'icon_override' => $image_ids['programming_icon'],
		),
	),
	$page_id
);
update_field( 'home_english_levels', array( array( 'age' => '8-10 років', 'title' => 'Рівень А0', 'text' => 'Перші слова, фрази та знайомство з англійською', 'image_override' => $image_ids['english_image'] ) ), $page_id );
update_field( 'home_transformation_before_image_override', $image_ids['transformation_before'], $page_id );
update_field( 'home_transformation_after_image_override', $image_ids['transformation_after'], $page_id );
update_field( 'home_onboarding_steps', array( array( 'title' => 'Залишiть заявку', 'text' => "Заповнiть форму або зателефонуйте. Менеджер зв'яжеться за 30 хвилин", 'button' => 'Залишити заявку', 'image_override' => $image_ids['onboarding_image'] ) ), $page_id );
update_field( 'home_certificates_image_override', $image_ids['certificates_image'], $page_id );
update_field( 'home_partners_items', array( array( 'name' => 'Тестовий партнер', 'image_override' => $image_ids['partner_image'] ) ), $page_id );
update_field( 'home_image_gallery', array( $image_ids['gallery_only'] ), $page_id );

ob_start();
logika_theme_render_source_page( 'index' );
$homepage = (string) ob_get_clean();

foreach ( $image_ids as $name => $image_id ) {
	$url = wp_get_attachment_image_url( $image_id, 'full' );
	if ( 'gallery_only' === $name ) {
		if ( $url && str_contains( $homepage, $url ) ) {
			$errors[] = 'Homepage renders the reference gallery as page content.';
		}
		continue;
	}

	if ( 'default_preview' === $name ) {
		update_field( 'home_certificates_image', $image_id, $page_id );
		update_field( 'home_trust_items', array( array( 'text' => 'З 2018 року на ринку', 'icon' => $image_id ) ), $page_id );
		ob_start();
		logika_theme_render_source_page( 'index' );
		$default_preview_homepage = (string) ob_get_clean();
		if ( $url && str_contains( $default_preview_homepage, $url ) ) {
			$errors[] = 'Homepage renders seeded default preview media instead of preserving source assets.';
		}
		update_field( 'home_certificates_image', $image_ids['certificates_image'], $page_id );
		update_field( 'home_trust_items', $originals['home_trust_items'], $page_id );
		continue;
	}

	if ( ! $url || ! str_contains( $homepage, $url ) ) {
		$errors[] = "Homepage does not render section image {$name}.";
	}
}

if ( ! str_contains( $homepage, 'services-section__item' ) || ! str_contains( $homepage, 'partners-section__gallery' ) ) {
	$errors[] = 'Homepage section image replacement broke original sections.';
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Homepage renders section-specific ACF images without using the reference gallery.\n";
