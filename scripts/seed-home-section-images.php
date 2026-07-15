<?php

require_once ABSPATH . 'wp-admin/includes/image.php';

$page_id = (int) get_option( 'page_on_front' );
if ( $page_id <= 0 ) {
	fwrite( STDERR, "Homepage is not configured.\n" );
	exit( 1 );
}

$asset_dir = trailingslashit( get_template_directory() ) . 'assets/img';
$upload = wp_upload_dir();

function logika_home_asset_id( string $title, string $asset ): int {
	global $asset_dir, $page_id, $upload;

	$existing = get_posts(
		array(
			'fields' => 'ids',
			'no_found_rows' => true,
			'post_status' => 'inherit',
			'post_type' => 'attachment',
			'posts_per_page' => 1,
			'suppress_filters' => true,
			'title' => $title,
		)
	);

	if ( $existing ) {
		return (int) $existing[0];
	}

	$source = trailingslashit( $asset_dir ) . $asset;
	if ( ! is_readable( $source ) ) {
		fwrite( STDERR, "Missing asset: {$source}\n" );
		exit( 1 );
	}

	$target = trailingslashit( $upload['path'] ) . sanitize_file_name( 'logika-home-section-' . pathinfo( $asset, PATHINFO_FILENAME ) . '.png' );
	if ( 'svg' === strtolower( pathinfo( $source, PATHINFO_EXTENSION ) ) ) {
		$command = sprintf( 'convert -background none -resize 900x900 %s %s 2>&1', escapeshellarg( $source ), escapeshellarg( $target ) );
		exec( $command, $output, $code );
		if ( 0 !== $code || ! is_readable( $target ) ) {
			fwrite( STDERR, "Cannot create preview for {$source}: " . implode( ' ', $output ) . "\n" );
			exit( 1 );
		}
	} elseif ( ! copy( $source, $target ) ) {
		fwrite( STDERR, "Cannot copy asset: {$source}\n" );
		exit( 1 );
	}

	$id = wp_insert_attachment(
		array(
			'post_title' => $title,
			'post_mime_type' => 'image/png',
			'post_status' => 'inherit',
		),
		$target,
		$page_id
	);

	if ( is_wp_error( $id ) ) {
		fwrite( STDERR, $id->get_error_message() . "\n" );
		exit( 1 );
	}

	wp_update_attachment_metadata( (int) $id, wp_generate_attachment_metadata( (int) $id, $target ) );

	return (int) $id;
}

$ids = array(
	'hero_boy' => logika_home_asset_id( 'Головна 01 - Дитина в hero', 'boy-character.svg' ),
	'hero_character' => logika_home_asset_id( 'Головна 02 - Персонаж біля форми', 'logika-character.svg' ),
	'calendar' => logika_home_asset_id( 'Головна 03 - Іконка: календар', 'banner-bar/icon-calendar-check.svg' ),
	'certificate_icon' => logika_home_asset_id( 'Головна 04 - Іконка: сертифікат', 'banner-bar/icon-document-certificate.svg' ),
	'rating' => logika_home_asset_id( 'Головна 05 - Іконка: рейтинг', 'banner-bar/icon-rating-star.svg' ),
	'schools' => logika_home_asset_id( 'Головна 06 - Іконка: школи', 'banner-bar/icon-outline_school.svg' ),
	'cities' => logika_home_asset_id( 'Головна 07 - Іконка: міста', 'banner-bar/icon-map-location.svg' ),
	'graduates' => logika_home_asset_id( 'Головна 08 - Іконка: випускники', 'banner-bar/icon-tabler-school.svg' ),
	'service_image' => logika_home_asset_id( 'Головна 09 - Картка послуг', 'services/service1.png' ),
	'service_icon' => logika_home_asset_id( 'Головна 10 - Ілюстрація послуг', 'services/service1.svg' ),
	'a0' => logika_home_asset_id( 'Головна 11 - Рівень англійської A0', 'english-courses/A0.svg' ),
	'a1' => logika_home_asset_id( 'Головна 12 - Рівень англійської A1', 'english-courses/A1.svg' ),
	'a2' => logika_home_asset_id( 'Головна 13 - Рівень англійської A2', 'english-courses/A2.svg' ),
	'b1' => logika_home_asset_id( 'Головна 14 - Рівень англійської B1', 'english-courses/B1.svg' ),
	'b2' => logika_home_asset_id( 'Головна 15 - Рівень англійської B2', 'english-courses/B2.svg' ),
	'before' => logika_home_asset_id( 'Головна 16 - Трансформація до', 'transformation/before.png' ),
	'after' => logika_home_asset_id( 'Головна 17 - Трансформація після', 'transformation/after.png' ),
	'onboarding_1' => logika_home_asset_id( 'Головна 18 - Онбординг 1', 'onbording/onbording1.svg' ),
	'onboarding_2' => logika_home_asset_id( 'Головна 19 - Онбординг 2', 'onbording/onbording2.svg' ),
	'onboarding_3' => logika_home_asset_id( 'Головна 20 - Онбординг 3', 'onbording/onbording3.svg' ),
	'certificates' => logika_home_asset_id( 'Головна 21 - Сертифікат', 'certificates/certificate.png' ),
	'think' => logika_home_asset_id( 'Головна 22 - Партнер Think', 'Partners/think.png' ),
	'one_plus_one' => logika_home_asset_id( 'Головна 23 - Партнер 1+1', 'Partners/1+1.png' ),
	'free' => logika_home_asset_id( 'Головна 24 - Партнер Free', 'Partners/Free.png' ),
	'club' => logika_home_asset_id( 'Головна 25 - Партнер Club', 'Partners/club.png' ),
	'ed' => logika_home_asset_id( 'Головна 26 - Партнер Ed', 'Partners/ed.png' ),
	'basis' => logika_home_asset_id( 'Головна 27 - Партнер Basis', 'Partners/basis.png' ),
	'fond' => logika_home_asset_id( 'Головна 28 - Партнер Fond', 'Partners/fond.png' ),
	'mriya' => logika_home_asset_id( 'Головна 29 - Партнер Mriya', 'Partners/mriya.png' ),
);

update_field( 'home_hero_boy_image', $ids['hero_boy'], $page_id );
update_field( 'home_hero_character_image', $ids['hero_character'], $page_id );
update_field( 'home_transformation_before_image', $ids['before'], $page_id );
update_field( 'home_transformation_after_image', $ids['after'], $page_id );
update_field( 'home_certificates_image', $ids['certificates'], $page_id );

update_field(
	'home_trust_items',
	array(
		array( 'text' => 'З 2018 року на ринку', 'icon' => $ids['calendar'] ),
		array( 'text' => 'Освітня ліцензія', 'icon' => $ids['certificate_icon'] ),
		array( 'text' => '4.9 рейтинг від клієнтів', 'icon' => $ids['rating'] ),
		array( 'text' => '178 шкіл в Україні', 'icon' => $ids['schools'] ),
		array( 'text' => '130 міст по Україні', 'icon' => $ids['cities'] ),
		array( 'text' => '100тис+ успішних випускників', 'icon' => $ids['graduates'] ),
	),
	$page_id
);

update_field(
	'home_programming_courses',
	array(
		array( 'age' => '7-8 рокiв', 'title' => 'Перший крок у свiт технологiй', 'tags' => "Комп'ютерна грамотність", 'text' => 'Допомагаємо дитині зробити перші впевнені кроки у світі технологій, розвиваючи логіку, увагу та базові комп’ютерні навички.', 'lesson_label' => 'Запис на безкоштовний урок', 'about_label' => 'Ознайомитись з курсами', 'image' => $ids['service_image'], 'icon' => $ids['service_icon'] ),
		array( 'age' => '9-11 рокiв', 'title' => 'Вiд iгор до власних проектiв', 'tags' => "Візуальне програмування\nГеймдизайн\nОснови штучного інтелекту", 'text' => 'Діти переходять від ігор до створення власних проєктів, розвивають креативне мислення та вчаться працювати з різними цифровими інструментами.', 'lesson_label' => 'Запис на безкоштовний урок', 'about_label' => 'Ознайомитись з курсами', 'image' => $ids['service_image'], 'icon' => $ids['service_icon'] ),
		array( 'age' => '12-14 рокiв', 'title' => 'Серйознi навички для серйозних цiлей', 'tags' => "Python Start\nPython Mastery\nГрафічний дизайн\nГрафічний дизайн 2.0\nСтворення веб-сайтів", 'text' => 'Поглиблюємо знання в програмуванні, працюємо над реальними проєктами та формуємо навички, які стануть основою для подальшого розвитку в IT.', 'lesson_label' => 'Запис на безкоштовний урок', 'about_label' => 'Ознайомитись з курсами', 'image' => $ids['service_image'], 'icon' => $ids['service_icon'] ),
		array( 'age' => '14-17 рокiв', 'title' => "Перший крок у IT-кар'єру", 'tags' => "Python Expert\nPython Advanced\nОснови фронтенд розробки\nКомп'ютерна грамотність для дорослих", 'text' => 'Готуємо до перших кроків у кар’єрі: даємо практичні знання, знайомимо з сучасними технологіями та вчимо мислити як розробник.', 'lesson_label' => 'Запис на безкоштовний урок', 'about_label' => 'Ознайомитись з курсами', 'image' => $ids['service_image'], 'icon' => $ids['service_icon'] ),
	),
	$page_id
);

update_field(
	'home_english_levels',
	array(
		array( 'age' => '8-10 років', 'title' => 'Рівень А0', 'text' => 'Перші слова, фрази та знайомство з англійською', 'image' => $ids['a0'] ),
		array( 'age' => '10-12 років', 'title' => 'Рівень А1', 'text' => 'Базове спілкування, прості речення та щоденні теми', 'image' => $ids['a1'] ),
		array( 'age' => '10-12 років', 'title' => 'Рівень А2', 'text' => 'Більше практики та словникового запасу', 'image' => $ids['a2'] ),
		array( 'age' => '13-15 років', 'title' => 'Рівень B1', 'text' => 'Вільніше спілкування та розуміння живої мови', 'image' => $ids['b1'] ),
		array( 'age' => '15-17 років', 'title' => 'Рівень B2', 'text' => 'Впевнене володіння мовою та спілкування', 'image' => $ids['b2'] ),
	),
	$page_id
);

update_field(
	'home_onboarding_steps',
	array(
		array( 'title' => 'Залишiть заявку', 'text' => "Заповнiть форму або зателефонуйте. Менеджер зв'яжеться за 30 хвилин", 'button' => 'Залишити заявку', 'image' => $ids['onboarding_1'] ),
		array( 'title' => 'Відвідайте безкоштовний пробний урок', 'text' => "Дитина спробує, ви подивитесь. Жодних зобов'язань", 'button' => 'Залишити заявку', 'image' => $ids['onboarding_2'] ),
		array( 'title' => 'Розпочніть навчання та отримайте результат', 'text' => 'Регулярнi заняття, власнi проекти, вiдкритi уроки для батькiв', 'button' => 'Залишити заявку', 'image' => $ids['onboarding_3'] ),
	),
	$page_id
);

update_field(
	'home_partners_items',
	array(
		array( 'name' => 'Think Global', 'image' => $ids['think'] ),
		array( 'name' => '1+1 Media', 'image' => $ids['one_plus_one'] ),
		array( 'name' => 'Free School', 'image' => $ids['free'] ),
		array( 'name' => 'Клуб Добродіїв', 'image' => $ids['club'] ),
		array( 'name' => 'Ed Kids', 'image' => $ids['ed'] ),
		array( 'name' => 'Basis', 'image' => $ids['basis'] ),
		array( 'name' => 'Діти Героїв', 'image' => $ids['fond'] ),
		array( 'name' => 'Мрія дітей України', 'image' => $ids['mriya'] ),
	),
	$page_id
);

echo 'Homepage section images seeded: ' . count( $ids ) . PHP_EOL;
