<?php

require_once ABSPATH . 'wp-admin/includes/image.php';

if ( ! function_exists( 'update_field' ) ) {
	WP_CLI::error( 'ACF Pro must be active.' );
}

$upload_dir = wp_upload_dir()['basedir'] . '/2026/07/tilda-summer-camps';

/** @return int */
$attachment = static function ( int $post_id, string $relative ) use ( $upload_dir ): int {
	$existing = get_posts( array( 'post_type' => 'attachment', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_key' => '_logika_tilda_source', 'meta_value' => $relative ) );
	if ( $existing ) {
		return (int) $existing[0];
	}
	$file = $upload_dir . '/' . $relative;
	if ( ! is_file( $file ) ) {
		return 0;
	}
	$attachment_id = wp_insert_attachment(
		array( 'post_mime_type' => (string) wp_check_filetype( $file )['type'], 'post_title' => pathinfo( $file, PATHINFO_FILENAME ), 'post_status' => 'inherit', 'post_parent' => $post_id ),
		$file,
		$post_id
	);
	if ( is_wp_error( $attachment_id ) ) {
		WP_CLI::warning( $attachment_id->get_error_message() );
		return 0;
	}
	wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $file ) );
	update_post_meta( $attachment_id, '_logika_tilda_source', $relative );
	return (int) $attachment_id;
};

/** @return int[] */
$images = static function ( int $post_id, string $folder ) use ( $attachment, $upload_dir ): array {
	$files = glob( $upload_dir . '/' . $folder . '/*.{jpg,jpeg,png,webp}', GLOB_BRACE ) ?: array();
	return array_values( array_filter( array_map( static fn( string $file ): int => $attachment( $post_id, $folder . '/' . basename( $file ) ), $files ) ) );
};

$records = array(
	array(
		'slug' => 'greece-2026', 'title' => 'Літній табір в Греції', 'dates' => '5.07-14.07 (10 днів, 9 ночей)', 'folder' => 'greece',
		'hero' => 'Місце щасливих дітей Logika: яскравий відпочинок і море незабутніх вражень.',
		'facts' => array( array( 'label' => 'Де', 'value' => 'Греція' ), array( 'label' => 'Тривалість', 'value' => '10 днів, 9 ночей' ), array( 'label' => 'Для кого', 'value' => 'Діти 10-16 років' ) ),
		'benefits' => array( 'Знайомство з Егейським морем та магією Греції', 'Активний відпочинок на воді та спортивні турніри', 'Майстер-класи, інтелектуальні ігри та квести', 'Тематичні дні та дискотеки', 'Кожен день море та водні розваги', 'Нові друзі та враження на весь рік' ),
		'activities' => array( array( 'Локація', 'Грецьке узбережжя, море та щоденні водні розваги.' ), array( 'Харчування', 'Шведський стіл: триразове харчування та полуденок.' ), array( 'Проживання', 'Комфортні 2-, 3- та 4-місні номери з усіма зручностями.' ) ),
		'includes' => array( array( 'Проживання', 'Комфортні 2-, 3- та 4-місні номери.' ), array( 'Харчування', '3-4-разове смачне та збалансоване харчування.' ), array( 'Розваги', 'Квести, майстер-класи, дискотеки, тематичні дні та водні активності.' ), array( 'Супровід', 'Професійні викладачі та вожаті школи Logika.' ), array( 'Курортний збір', 'Обов’язковий місцевий платіж за проживання у готелях.' ) ),
		'extra' => array( array( 'У вартість не входить', '<ul><li>Трансфер з Чернівців до Греції і назад — 160 євро.</li><li>Обід у дорозі — 15 євро.</li><li>Обов’язкове медичне страхування — 15 євро.</li><li>Екскурсії за бажанням.</li></ul>' ), array( 'Екскурсії', '<ul><li>Казкові Метеори — орієнтовно 50 євро.</li><li>Круїз на острів Скіатос — орієнтовно 55 євро.</li><li>Аквапарк WATERLAND — орієнтовно 45 євро.</li></ul>' ), array( 'Вартість', '<p>1150 євро. До 30.03 зі знижкою — 1075 євро. Для клієнтів Logika діють знижки; кількість місць обмежена.</p>' ) ),
	),
	array(
		'slug' => 'emily-resort-2026', 'title' => 'VIP табір в Emily Resort', 'dates' => '20.06-27.06 (8 днів, 7 ночей)', 'folder' => 'emily',
		'hero' => 'Портал у світ ігор: пригоди, які запам’ятаються назавжди.',
		'facts' => array( array( 'label' => 'Де', 'value' => 'Emily Resort' ), array( 'label' => 'Коли', 'value' => '20-27 червня' ), array( 'label' => 'Тривалість', 'value' => '8 днів' ) ),
		'benefits' => array( 'Великий акваторій на території комплексу', '4-разове збалансоване харчування', 'Медична допомога на території', 'Speaking Club — прокачуємо навички спілкування!' ),
		'activities' => array( array( 'Акваторій Emily Resort', 'Море веселощів, гірки та водні атракціони для яскравих емоцій!' ), array( 'Похід у гори', 'Свіже повітря, мальовничі краєвиди та справжня команда однодумців!' ), array( 'Пісні біля вогнища', 'Атмосферні вечори з гітарою, історіями та маршмелоу.' ), array( 'Пригоди у Львові', 'Захоплива виїзна мандрівка до міста легенд.' ), array( 'Розваги', 'Квести, інтелектуальні квізи та челенджі.' ), array( 'Яскраве табірне життя', 'Спортивні ігри, змагання, вечірки, дискотеки та нові друзі.' ) ),
		'includes' => array( array( 'Проживання', 'Комфортні 4/6-місні номери з усіма зручностями.' ), array( 'Харчування', '4-разове преміальне харчування.' ), array( 'Speaking clubs', 'Спілкування з native-спікерами без нудних підручників.' ), array( 'Розваги', 'Виїзні екскурсії, вечірки та дискотеки.' ), array( 'Страхування', 'Турбота про безпеку й комфорт дітей.' ), array( 'Супровід', 'Професійна команда школи Logika.' ) ),
		'extra' => array( array( 'Вартість', '<p>Найнижча ціна до 30 квітня — 22 900 грн. Повна ціна — 25 000 грн. Для клієнтів Logika доступна спеціальна знижка.</p>' ), array( 'Відгуки батьків', '<p>«Ваша робота безцінна, тому щира подяка за якість у всьому!»</p><p>«Діти у захваті та вже чекають наступного табору.»</p>' ) ),
	),
	array(
		'slug' => 'carpathians-2026', 'title' => 'Літній табір на Закарпатті', 'dates' => "27.06 – 06.07 (1 зміна)\n21.07 – 30.07 (2 зміна)", 'folder' => 'carpathians',
		'hero' => '«Фестиваль професій»: створіть свою фантастичну історію разом із нами.',
		'facts' => array( array( 'label' => 'Де', 'value' => 'с. Білки, Закарпаття' ), array( 'label' => 'Тривалість', 'value' => '10 днів' ), array( 'label' => 'Для кого', 'value' => 'Діти 10-16 років' ) ),
		'benefits' => array( 'Безлімітний басейн на свіжому повітрі', '4-х разове харчування (основне + перекус)', 'Медична допомога 24/7 на території', 'Незабутня професійна пінна вечірка' ),
		'activities' => array( array( 'Занурення у світ професій', 'Від блогера до детектива.' ), array( 'Командні батли', 'Професійні челенджі та практичні завдання.' ), array( 'Екскурсії', 'Виїзди на Синевир та в Екопарк «Долина вовків».' ), array( 'Розваги', 'Дискотеки, пінна вечірка, спортивне орієнтування та басейн.' ) ),
		'includes' => array( array( 'Проживання', 'Комфортні 6-місні номери.' ), array( 'Харчування', '5-разове харчування.' ), array( 'Страхування', 'Страхування на час зміни.' ), array( 'Екскурсії', 'Поїздки на Синевир та в Екопарк.' ), array( 'Розваги', 'Квести, конкурси та активності.' ) ),
		'extra' => array( array( 'Локація', '<p>Адреса готелю: с. Білки, Хустський район, Закарпатська область.</p>' ), array( 'Вартість', '<p>До 15 травня зі знижкою — 26 290 грн. Повна ціна — 28 919 грн. Трансфер з вашого міста не входить у вартість.</p>' ), array( 'Виїзні екскурсії', '<ul><li>Озеро Синевір.</li><li>Екопарк «Долина вовків».</li></ul>' ) ),
	),
	array(
		'slug' => 'city-camps-2026', 'title' => 'Міські табори', 'dates' => 'Дати уточнюйте у свого менеджера', 'folder' => 'city',
		'hero' => 'IT-табір Logika міського формату для дітей 9-15 років: програмуй та відпочивай разом з Logika.',
		'facts' => array( array( 'label' => 'Графік', 'value' => '2 тижні по буднях, 9:30-18:30' ), array( 'label' => 'Група', 'value' => 'До 12 дітей, 9-15 років' ), array( 'label' => 'Локація', 'value' => 'Школи Logika у вашому місті' ) ),
		'benefits' => array( 'Створення власних IT-проєктів', 'Прогулянки та ігри на свіжому повітрі', 'Публічні виступи та робота в команді', 'Екскурсії, змагання та квести', 'Нові друзі та враження на весь рік' ),
		'activities' => array( array( 'IT-проєкти', 'Практичні заняття з ноутбуками й технічним обладнанням.' ), array( 'Активності', 'Творчі заняття, прогулянки, квести, зарядка та командні ігри.' ), array( 'Презентація проєктів', 'Фінальний виступ перед батьками.' ) ),
		'includes' => array( array( 'IT-дисципліни', 'Повноцінні заняття, ноутбук та технічне обладнання.' ), array( 'Харчування', '2-разове смачне та збалансоване харчування.' ), array( 'Активності', 'Творчі заняття, прогулянки та квести.' ), array( 'Презентація проєктів', 'Перед батьками наприкінці тижня.' ), array( 'Супровід', 'Професійні викладачі та вожаті школи Logika.' ) ),
		'extra' => array( array( 'Розпорядок дня', '<p>10:00 — збір і обговорення планів.</p><p>10:30 — заняття 1: робота над проєктом.</p><p>12:00 — відпочинок: квести, змагання, вікторини.</p><p>13:00 — обід.</p><p>14:30 — заняття 2: робота над проєктом.</p><p>16:00 — активності й підготовка презентацій.</p><p>18:00 — презентація проєктів та підсумки дня.</p>' ), array( 'Програма для молодших (9-11 років)', '<p>Google Earth, аватари та логотипи команд, ігри й анімації, квести «Блокігейм», Tinkercad, нейромережі, Canva, QR-візитівки та власне портфоліо.</p>' ), array( 'Програма для старших (12-15 років)', '<p>Аналіз міст, брендбук команди, великі дані, бізнес-модель стартапу, 3D-прототипування, ШІ для контенту, криптографія, HTML/CSS та захист стартапу.</p>' ) ),
	),
);

$format_ids = array();
foreach ( $records as $record ) {
	$post = get_page_by_path( $record['slug'], OBJECT, 'camp' );
	$post_id = $post ? (int) $post->ID : wp_insert_post( array( 'post_type' => 'camp', 'post_status' => 'publish', 'post_name' => $record['slug'], 'post_title' => $record['title'] ) );
	if ( ! $post_id || is_wp_error( $post_id ) ) {
		WP_CLI::warning( 'Could not create ' . $record['slug'] );
		continue;
	}
	wp_update_post( array( 'ID' => $post_id, 'post_title' => $record['title'], 'post_excerpt' => $record['hero'], 'post_status' => 'publish' ) );
	$media = $images( $post_id, $record['folder'] );
	$card = $attachment( $post_id, $record['folder'] . '/card.' . ( 'emily' === $record['folder'] ? 'png' : 'jpg' ) );
	$card = $card ?: ( $media[0] ?? 0 );
	$detail_galleries = array_chunk( $media, 4 );
	$details = array();
	foreach ( array( 'Локація', 'Проживання', 'Харчування' ) as $index => $title ) {
		$details[] = array( 'title' => $title, 'text' => $record['activities'][ $index ][1] ?? $record['hero'], 'image' => $detail_galleries[ $index ][0] ?? 0, 'gallery' => $detail_galleries[ $index ] ?? array() );
	}
	$benefits = array_map( static fn( string $title, int $index ): array => array( 'title' => $title, 'image' => $media[ $index ] ?? 0 ), $record['benefits'], array_keys( $record['benefits'] ) );
	$activities = array_map( static fn( array $row, int $index ): array => array( 'title' => $row[0], 'text' => $row[1], 'image' => $media[ $index ] ?? 0 ), $record['activities'], array_keys( $record['activities'] ) );
	$extra = array_map( static fn( array $row, int $index ): array => array( 'title' => $row[0], 'text' => $row[1], 'images' => $index ? array() : array_slice( $media, 0, 4 ) ), $record['extra'], array_keys( $record['extra'] ) );
	update_field( 'camp_is_active', 1, $post_id );
	update_field( 'camp_season', 'Літо 2026', $post_id );
	update_field( 'camp_card_dates', $record['dates'], $post_id );
	update_field( 'camp_card_description', $record['hero'], $post_id );
	update_field( 'camp_hero_dates_text', $record['dates'], $post_id );
	update_field( 'camp_hero_text', $record['hero'], $post_id );
	update_field( 'camp_hero_image', $card, $post_id );
	update_field( 'camp_card_image', $card, $post_id );
	update_field( 'camp_hero_images', array_slice( $media, 0, 4 ), $post_id );
	update_field( 'camp_hero_facts', $record['facts'], $post_id );
	update_field( 'camp_benefits_title', 'У програмі табору', $post_id );
	update_field( 'camp_benefits', $benefits, $post_id );
	update_field( 'camp_activities_title', 'Активності у програмі', $post_id );
	update_field( 'camp_activities', $activities, $post_id );
	update_field( 'camp_details', $details, $post_id );
	update_field( 'camp_includes_title', 'У вартість входить', $post_id );
	update_field( 'camp_includes', $record['includes'], $post_id );
	update_field( 'camp_extra_sections', $extra, $post_id );
	update_field( 'camp_booking_title', 'Встигніть забронювати', $post_id );
	update_field( 'camp_booking_text', 'Залиште заявку — ми зателефонуємо і обговоримо всі деталі.', $post_id );
	update_field( 'camp_booking_benefits', array( array( 'text' => 'Активності, квести, турніри та нові друзі' ), array( 'text' => 'Оновлена IT програма' ), array( 'text' => 'Безпека: вожаті поряд із дітьми 24/7' ) ), $post_id );
	update_field( 'camp_gallery', $media, $post_id );
	$format_ids[] = $post_id;
}

foreach ( array( 'summer-camp', 'spring-camp-2026', 'autumn-camp', 'winter-camp' ) as $slug ) {
	$old = get_page_by_path( $slug, OBJECT, 'camp' );
	if ( $old ) {
		update_field( 'camp_is_active', 0, $old->ID );
	}
}
update_field( 'camp_archive_formats', $format_ids, 'camp_archive' );

WP_CLI::success( sprintf( 'Seeded %d Tilda summer camp formats.', count( $format_ids ) ) );
