<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$group = json_decode((string) file_get_contents(WP_CONTENT_DIR . '/plugins/logika-core/acf-json/group_logika_page_about.json'), true);
$names = array_filter(array_column((array) ($group['fields'] ?? array()), 'name'));
$required = array(
	'about_hero_title', 'about_hero_text', 'about_hero_image', 'about_hero_form_title', 'about_hero_form_text', 'about_hero_cta_label', 'about_hero_consent_text', 'about_hero_background_image', 'about_hero_pattern_image', 'about_hero_character_image',
	'about_stats_title', 'about_stats_items', 'about_directions_items', 'about_outcomes_title', 'about_outcome_items', 'about_history_title', 'about_history_text', 'about_history_image', 'about_history_cta_label',
	'about_gallery', 'about_media_title', 'about_media_items', 'about_onboarding_title', 'about_onboarding_items', 'about_map_title', 'about_map_text', 'about_map_offline_label', 'about_map_online_label', 'about_map_city_label',
	'about_cta_title', 'about_cta_subtitle', 'about_cta_image', 'about_cta_submit_label', 'about_cta_consent_text', 'about_cta_character_image', 'about_cta_top_background_image', 'about_cta_bottom_background_image',
	'about_featured_reviews', 'about_featured_faq', 'about_featured_posts', 'about_faq_title', 'about_certificates_title', 'about_certificates_subtitle', 'about_certificates_text', 'about_certificates_button_label', 'about_certificates_image', 'about_certificates_background_image', 'about_partners_title', 'about_partners_items',
);
$errors = array();

foreach ($required as $name) {
	if (!in_array($name, $names, true)) {
		$errors[] = "Missing About ACF field {$name}.";
	}
}

if ($errors) {
	fwrite(STDERR, implode(PHP_EOL, $errors) . PHP_EOL);
	exit(1);
}

$page = get_page_by_path('about');
if (!$page) {
	fwrite(STDERR, "About page is missing.\n");
	exit(1);
}

$fields = array('about_history_cta_label' => 'Редагований пробний урок', 'about_cta_submit_label' => 'Редагована консультація');
$original = array();

try {
	foreach ($fields as $name => $value) {
		$original[$name] = get_field($name, $page->ID);
		update_field($name, $value, $page->ID);
	}

	$source = (string) file_get_contents(get_template_directory() . '/source-pages/about.php');
	$rendered = Logika_Theme_Page_Content::apply($source, 'about', (int) $page->ID);

	foreach ($fields as $value) {
		if (!str_contains($rendered, $value)) {
			$errors[] = "About page does not render {$value}.";
		}
	}
} finally {
	foreach ($original as $name => $value) {
		update_field($name, $value, $page->ID);
	}
}

if ($errors) {
	fwrite(STDERR, implode(PHP_EOL, $errors) . PHP_EOL);
	exit(1);
}

echo "About page ACF fields render through the source-markup adapter.\n";
