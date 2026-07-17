<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$page_id   = (int) get_option( 'page_on_front' );
$original  = get_field( 'home_hero_title', $page_id );
$trust_original = get_field( 'home_trust_items', $page_id );
$english_title_original = get_field( 'home_english_title', $page_id );
$english_text_original = get_field( 'home_english_text', $page_id );
$programming_title_original = get_field( 'home_programming_title', $page_id );
$transformation_title_original = get_field( 'home_transformation_title', $page_id );
$transformation_cta_original = get_field( 'home_transformation_cta_label', $page_id );
$onboarding_title_original = get_field( 'home_onboarding_title', $page_id );
$locations_title_original = get_field( 'home_locations_title', $page_id );
$faq_items_original = get_field( 'home_faq_items', $page_id );
$certificates_title_original = get_field( 'home_certificates_title', $page_id );
$certificates_button_original = get_field( 'home_certificates_button', $page_id );
$partners_title_original = get_field( 'home_partners_title', $page_id );
$hero_boy_original = get_field( 'home_hero_boy_image', $page_id );
$hero_character_original = get_field( 'home_hero_character_image', $page_id );
$hero_boy_override_original = get_field( 'home_hero_boy_image_override', $page_id );
$hero_character_override_original = get_field( 'home_hero_character_image_override', $page_id );
$gallery_original = get_field( 'home_image_gallery', $page_id );
$age_placeholder_original = get_field( 'home_form_age_placeholder', $page_id );
$age_options_original = get_field( 'home_form_age_options', $page_id );
$test_text = 'Заголовок ACF у вихідній верстці';
$trust_text = 'Доказ із ACF без зміни trust-панелі';
$english_title = 'Англійська з ACF у вихідній секції';
$english_text = 'Редагований опис англійської секції без заміни слайдера.';
$programming_title = 'Курси програмування з ACF';
$transformation_title = 'Трансформація з ACF';
$transformation_cta = 'Кнопка трансформації з ACF';
$onboarding_title = 'Старт навчання з ACF';
$locations_title = 'Локації з ACF';
$faq_question = 'FAQ питання з ACF?';
$faq_answer = 'FAQ відповідь з ACF.';
$certificates_title = 'Сертифікати з ACF';
$certificates_button = 'Кнопка сертифікатів з ACF';
$partners_title = 'Партнери з ACF';
$age_placeholder = 'Оберіть вік дитини';
$age_options = array(
	array( 'value' => '7', 'label' => '7 років' ),
	array( 'value' => '12', 'label' => '12 років' ),
	array( 'value' => '17', 'label' => '17 років' ),
);
$errors    = array();

function logika_test_image_attachment( string $name ): int {
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$upload = wp_upload_dir();
	$file   = trailingslashit( $upload['path'] ) . $name;
	file_put_contents( $file, base64_decode( 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMB/ax3p1sAAAAASUVORK5CYII=' ) );
	$id = wp_insert_attachment( array( 'post_title' => $name, 'post_mime_type' => 'image/png', 'post_status' => 'inherit' ), $file );
	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $file ) );

	return (int) $id;
}

$hero_boy_id = logika_test_image_attachment( 'home-hero-boy-test.png' );
$hero_character_id = logika_test_image_attachment( 'home-hero-character-test.png' );
$trust_icon_id = logika_test_image_attachment( 'home-trust-icon-test.png' );
$hero_boy_url = wp_get_attachment_image_url( $hero_boy_id, 'full' );
$hero_character_url = wp_get_attachment_image_url( $hero_character_id, 'full' );
$trust_icon_url = wp_get_attachment_image_url( $trust_icon_id, 'full' );

register_shutdown_function(
	static function () use (
		$page_id,
		$original,
		$trust_original,
		$english_title_original,
		$english_text_original,
		$programming_title_original,
		$transformation_title_original,
		$transformation_cta_original,
		$onboarding_title_original,
		$locations_title_original,
		$faq_items_original,
		$certificates_title_original,
		$certificates_button_original,
		$partners_title_original,
			$hero_boy_original,
			$hero_character_original,
			$hero_boy_override_original,
			$hero_character_override_original,
			$gallery_original,
			$age_placeholder_original,
			$age_options_original,
			$hero_boy_id,
		$hero_character_id,
		$trust_icon_id
	): void {
		foreach (
			array(
				'home_hero_title' => $original,
				'home_trust_items' => $trust_original,
				'home_english_title' => $english_title_original,
				'home_english_text' => $english_text_original,
				'home_programming_title' => $programming_title_original,
				'home_transformation_title' => $transformation_title_original,
				'home_transformation_cta_label' => $transformation_cta_original,
				'home_onboarding_title' => $onboarding_title_original,
				'home_locations_title' => $locations_title_original,
				'home_faq_items' => $faq_items_original,
				'home_certificates_title' => $certificates_title_original,
				'home_certificates_button' => $certificates_button_original,
				'home_partners_title' => $partners_title_original,
					'home_hero_boy_image' => $hero_boy_original,
					'home_hero_character_image' => $hero_character_original,
					'home_hero_boy_image_override' => $hero_boy_override_original,
					'home_hero_character_image_override' => $hero_character_override_original,
					'home_image_gallery' => $gallery_original,
					'home_form_age_placeholder' => $age_placeholder_original,
					'home_form_age_options' => $age_options_original,
				) as $field_name => $original_value
		) {
			if ( null === $original_value || false === $original_value || '' === $original_value || array() === $original_value ) {
				delete_field( $field_name, $page_id );
			} else {
				update_field( $field_name, $original_value, $page_id );
			}
		}

		wp_delete_attachment( $hero_boy_id, true );
		wp_delete_attachment( $hero_character_id, true );
		wp_delete_attachment( $trust_icon_id, true );
	}
);

update_field( 'home_hero_title', $test_text, $page_id );
update_field( 'home_trust_items', array( array( 'text' => $trust_text, 'icon' => 0 ) ), $page_id );
update_field( 'home_english_title', $english_title, $page_id );
update_field( 'home_english_text', $english_text, $page_id );
update_field( 'home_programming_title', $programming_title, $page_id );
update_field( 'home_transformation_title', $transformation_title, $page_id );
update_field( 'home_transformation_cta_label', $transformation_cta, $page_id );
update_field( 'home_onboarding_title', $onboarding_title, $page_id );
update_field( 'home_locations_title', $locations_title, $page_id );
update_field( 'home_faq_items', array( array( 'question' => $faq_question, 'answer' => $faq_answer ) ), $page_id );
update_field( 'home_certificates_title', $certificates_title, $page_id );
update_field( 'home_certificates_button', $certificates_button, $page_id );
update_field( 'home_partners_title', $partners_title, $page_id );
update_field( 'home_hero_boy_image_override', $hero_boy_id, $page_id );
update_field( 'home_hero_character_image_override', $hero_character_id, $page_id );
update_field( 'home_image_gallery', array( $hero_boy_id, $hero_character_id, $trust_icon_id ), $page_id );
update_field( 'home_form_age_placeholder', $age_placeholder, $page_id );
update_field( 'home_form_age_options', $age_options, $page_id );

ob_start();
logika_theme_render_source_page( 'index' );
$homepage = (string) ob_get_clean();

if ( ! str_contains( $homepage, $test_text ) ) {
	$errors[] = 'Homepage does not render the ACF hero title inside source markup.';
}

if ( ! str_contains( $homepage, $trust_text ) ) {
	$errors[] = 'Homepage does not render ACF trust text inside source markup.';
}

if ( ! str_contains( $homepage, $english_title ) || ! str_contains( $homepage, $english_text ) ) {
	$errors[] = 'Homepage does not render ACF English copy inside source markup.';
}

foreach ( array( $programming_title, $transformation_title, $onboarding_title, $locations_title, $faq_question, $faq_answer, $certificates_title, $partners_title ) as $expected_text ) {
	if ( ! str_contains( $homepage, $expected_text ) ) {
		$errors[] = "Homepage does not render ACF section text: {$expected_text}";
	}
}

if ( ! str_contains( $homepage, $transformation_cta ) || ! str_contains( $homepage, $certificates_button ) || ! str_contains( $homepage, 'Запис на безкоштовний урок' ) ) {
	$errors[] = 'Homepage section CTA fields are not scoped to their own sections.';
}

if ( ! $hero_boy_url || ! $hero_character_url || ! str_contains( $homepage, $hero_boy_url ) || ! str_contains( $homepage, $hero_character_url ) ) {
	$errors[] = 'Homepage does not render editable ACF hero images inside source markup.';
}

if ( $trust_icon_url && str_contains( $homepage, $trust_icon_url ) ) {
	$errors[] = 'Homepage renders positional gallery images into source markup and can break the original layout.';
}

if ( ! str_contains( $homepage, '<picture>' ) || ! str_contains( $homepage, 'english-section__controls' ) ) {
	$errors[] = 'Homepage ACF replacement changed the supplied markup.';
}

if ( ! str_contains( $homepage, '<select name="child_age"' ) || ! str_contains( $homepage, 'main-form__select' ) || ! str_contains( $homepage, 'data-logika-age-select' ) || ! str_contains( $homepage, 'main-form__age-dropdown' ) || ! str_contains( $homepage, $age_placeholder ) ) {
	$errors[] = 'Homepage does not render the ACF child age dropdown.';
}

foreach ( range( 7, 17 ) as $age ) {
	if ( ! str_contains( $homepage, 'value="' . esc_attr( (string) $age ) . '"' ) || ! str_contains( $homepage, esc_html( "{$age} років" ) ) ) {
		$errors[] = 'Homepage does not render ACF child age dropdown options.';
		break;
	}
}

if ( null === $original || false === $original || '' === $original ) {
	delete_field( 'home_hero_title', $page_id );
} else {
	update_field( 'home_hero_title', $original, $page_id );
}

if ( null === $trust_original || false === $trust_original || array() === $trust_original ) {
	delete_field( 'home_trust_items', $page_id );
} else {
	update_field( 'home_trust_items', $trust_original, $page_id );
}

if ( null === $english_title_original || false === $english_title_original || '' === $english_title_original ) {
	delete_field( 'home_english_title', $page_id );
} else {
	update_field( 'home_english_title', $english_title_original, $page_id );
}

if ( null === $english_text_original || false === $english_text_original || '' === $english_text_original ) {
	delete_field( 'home_english_text', $page_id );
} else {
	update_field( 'home_english_text', $english_text_original, $page_id );
}

foreach (
	array(
		'home_programming_title' => $programming_title_original,
		'home_transformation_title' => $transformation_title_original,
		'home_transformation_cta_label' => $transformation_cta_original,
		'home_onboarding_title' => $onboarding_title_original,
		'home_locations_title' => $locations_title_original,
		'home_faq_items' => $faq_items_original,
		'home_certificates_title' => $certificates_title_original,
		'home_certificates_button' => $certificates_button_original,
		'home_partners_title' => $partners_title_original,
	) as $field_name => $original_value
) {
	if ( null === $original_value || false === $original_value || '' === $original_value || array() === $original_value ) {
		delete_field( $field_name, $page_id );
	} else {
		update_field( $field_name, $original_value, $page_id );
	}
}

if ( null === $hero_boy_override_original || false === $hero_boy_override_original || '' === $hero_boy_override_original ) {
	delete_field( 'home_hero_boy_image_override', $page_id );
} else {
	update_field( 'home_hero_boy_image_override', $hero_boy_override_original, $page_id );
}

if ( null === $hero_character_override_original || false === $hero_character_override_original || '' === $hero_character_override_original ) {
	delete_field( 'home_hero_character_image_override', $page_id );
} else {
	update_field( 'home_hero_character_image_override', $hero_character_override_original, $page_id );
}

if ( null === $gallery_original || false === $gallery_original || array() === $gallery_original ) {
	delete_field( 'home_image_gallery', $page_id );
} else {
	update_field( 'home_image_gallery', $gallery_original, $page_id );
}

if ( null === $age_placeholder_original || false === $age_placeholder_original || '' === $age_placeholder_original ) {
	delete_field( 'home_form_age_placeholder', $page_id );
} else {
	update_field( 'home_form_age_placeholder', $age_placeholder_original, $page_id );
}

if ( null === $age_options_original || false === $age_options_original || array() === $age_options_original ) {
	delete_field( 'home_form_age_options', $page_id );
} else {
	update_field( 'home_form_age_options', $age_options_original, $page_id );
}

wp_delete_attachment( $hero_boy_id, true );
wp_delete_attachment( $hero_character_id, true );
wp_delete_attachment( $trust_icon_id, true );

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Homepage ACF title preserves the supplied markup.\n";
