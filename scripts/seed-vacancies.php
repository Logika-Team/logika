<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$page = get_page_by_path( 'vacancies', OBJECT, 'page' );
$page_id = $page instanceof WP_Post ? $page->ID : wp_insert_post( array( 'post_type' => 'page', 'post_name' => 'vacancies', 'post_title' => 'Вакансії', 'post_status' => 'publish' ) );

if ( is_wp_error( $page_id ) || ! $page_id ) {
	throw new RuntimeException( 'Не вдалося створити сторінку вакансій.' );
}

update_post_meta( $page_id, '_wp_page_template', 'templates/page-vacancies.php' );
require_once ABSPATH . 'wp-admin/includes/image.php';

$asset = static function ( string $relative ) use ( $page_id ): int {
	$marker = 'theme/assets/img/' . $relative;
	$existing = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_key' => '_logika_source_path', 'meta_value' => $marker ) );
	if ( $existing ) {
		return (int) $existing[0];
	}
	$source = get_theme_file_path( 'assets/img/' . $relative );
	if ( ! is_readable( $source ) ) {
		throw new RuntimeException( "Відсутній asset: {$relative}" );
	}
	$upload = wp_upload_dir();
	$target = trailingslashit( $upload['path'] ) . wp_unique_filename( $upload['path'], 'logika-' . basename( $relative ) );
	copy( $source, $target );
	$id = wp_insert_attachment( array( 'post_title' => sanitize_text_field( pathinfo( $relative, PATHINFO_FILENAME ) ), 'post_mime_type' => wp_check_filetype( $target )['type'], 'post_status' => 'inherit', 'post_parent' => $page_id ), $target, $page_id );
	if ( is_wp_error( $id ) ) {
		throw new RuntimeException( $id->get_error_message() );
	}
	update_post_meta( $id, '_logika_source_path', $marker );
	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $target ) );
	return (int) $id;
};
$images = static function ( array $paths ) use ( $asset ): array { return array_map( $asset, $paths ); };
$fill = static function ( string $field, mixed $value ) use ( $page_id ): void { if ( empty( get_field( $field, $page_id ) ) ) { update_field( $field, $value, $page_id ); } };

$fill( 'vacancies_application_url', 'https://forms.gle/ikGeworjH6wnAdSt6' );
$fill( 'vacancies_hero_title', 'Долучайтесь до нашої команди освітян!' );
$fill( 'vacancies_hero_text', 'Запрошуємо натхненних викладачів і менеджерів, які готові ростити покоління нових ІТ-спеціалістів.' );
$hero_image = (int) get_field( 'vacancies_hero_image', $page_id );
if ( ! $hero_image || 'theme/assets/img/vacancies/hero-team.jpg' === get_post_meta( $hero_image, '_logika_source_path', true ) ) {
	update_field( 'vacancies_hero_image', $asset( 'vacancies/team-10.jpg' ), $page_id );
}
$fill( 'vacancies_about_title', 'Навчаємо цікаво, легко та ефективно' );
$fill( 'vacancies_about_text', '<p>Logika — успішний освітній проєкт з 2018 року і понад 50 000 випускників. Ми навчаємо дітей 7–17 років програмуванню та англійської онлайн і офлайн.</p><p>Проводимо квести, їздимо в IT-табори та створюємо навчання, до якого хочеться повертатися.</p>' );
$fill( 'vacancies_about_gallery', $images( array( 'vacancies/about-1.jpg', 'vacancies/about-2.jpg', 'vacancies/about-3.jpg' ) ) );
$fill( 'vacancies_benefits_title', 'Робота роботою, а відпочинок за графіком :)' );
$fill( 'vacancies_benefits_text', 'Працівники нашої компанії — наша команда і сім’я. Ми робимо атмосферу сприятливою для ефективної та приємної роботи.' );
$fill( 'vacancies_benefits', array( array( 'title' => 'Щорічні корпоративи', 'text' => 'Кофі-брейки, спільні поїздки в Карпати, квесткімнати та ігроклуби.', 'icon' => $asset( 'vacancies/benefit-events-3d.webp' ) ), array( 'title' => 'Постійна підтримка', 'text' => 'Регулярне навчання та щотижневі дзвінки, де команда вирішує робочі питання разом.', 'icon' => $asset( 'vacancies/benefit-support-3d.webp' ) ), array( 'title' => 'Достойна зарплата', 'text' => 'Зрозуміла система бонусів і навантаження, з якою ви впливаєте на свій дохід.', 'icon' => $asset( 'vacancies/benefit-pay-3d.webp' ) ) ) );
$benefit_icons = array( 'vacancies/benefit-events-3d.webp', 'vacancies/benefit-support-3d.webp', 'vacancies/benefit-pay-3d.webp' );
$benefits = get_field( 'vacancies_benefits', $page_id );
if ( is_array( $benefits ) && count( $benefits ) === count( $benefit_icons ) ) {
	foreach ( $benefit_icons as $index => $icon ) {
		$benefits[ $index ]['icon'] = $asset( $icon );
	}
	update_field( 'vacancies_benefits', $benefits, $page_id );
}
$fill( 'vacancies_team_gallery_title', 'Наші корпоративні теплі зустрічі' );
$fill( 'vacancies_team_gallery', $images( array_map( static fn( int $number ): string => sprintf( 'vacancies/team-%02d.jpg', $number ), range( 1, 13 ) ) ) );
$fill( 'vacancies_list_title', 'Наші вакансії' );
$vacancy_details = array(
	'<h3>Хто нам потрібен?</h3><ul><li>Підприємні, енергійні та вмотивовані викладачі, які ладнають із комп’ютером на «ти» та мають активний підхід до своєї роботи.</li><li>Необхідні знання у Python.</li><li>Великою перевагою буде освіта в області педагогіки або психології.</li><li>Вміти проводити заняття з використанням уже готових методичних матеріалів.</li><li>Якщо у тебе ще немає досвіду, але є хист і бажання, школа пропонує навчання і підтримку.</li></ul><h3>Обов’язки:</h3><ul><li>Проводити заняття з використанням уже готових методичних матеріалів школи Logika;</li><li>Створювати та підтримувати дружню робочу атмосферу;</li><li>Слідкувати за результатами учнів;</li><li>Давати звіти за шаблонами;</li><li>Отримувати задоволення від роботи з дітками — майбутнім нашої країни.</li></ul><h3>Ми пропонуємо:</h3><ul><li>Роботу у вихідні та будні.</li><li>Заробітна плата: стабільне підвищення залежно від навантаження.</li><li>Постійне навчання за рахунок компанії від наших професіоналів.</li><li>Зручні та сучасні кабінети.</li></ul>',
	'<h3>Основні обов’язки на посаді:</h3><ul><li>Проводити групові заняття з дітьми 2 рази на тиждень: тривалість заняття — 1,5 години, групи — 8 учнів;</li><li>Моніторити виконання домашніх завдань, проводити роботу над помилками;</li><li>Мотивувати та допомагати учням у навчальному процесі, контролювати індивідуальний прогрес навчання кожної дитини;</li><li>Працювати у команді з методистом та менеджером, вести звітність;</li><li>Вміти зацікавити учнів дійти до бажаного результату.</li></ul><h3>Для нас важливо:</h3><ul><li>Наявність вищої освіти: педагогічної чи філологічної;</li><li>Досвід викладання: групові, індивідуальні заняття з дітьми;</li><li>Рівень володіння англійською від B2–C1;</li><li>Досвід проведення дистанційних групових занять для дітей за допомогою сучасних технічних засобів;</li><li>Високий рівень володіння технічними засобами та відповідним програмним забезпеченням;</li><li>Великою перевагою буде наявність сертифікатів: CELTA, CELT-S, CELT-P, ICELT, CELTYL, TESOL, TEFL або успішно складений TKT Modules 1, 2, 3 та YL;</li><li>Бажання рости та розвиватися разом з міжнародною компанією.</li></ul><h3>Чому саме ми:</h3><ul><li>Компанія надає готові методичні матеріали: презентації, аудіоскрипти, плани-конспекти, розроблені інтерактивні тести для роботи з учнями;</li><li>Учні працюють на корпоративній платформі з автоматичною перевіркою завдань і готовими скриптами;</li><li>Можливість впливати на рівень свого доходу: навантаження обговорюється індивідуально;</li><li>Можна обрати зручний графік роботи: понеділок–п’ятниця після школи;</li><li>Корпоративне навчання, методологічна підтримка від компанії;</li><li>Можливості для кар’єрного та особистісного зростання.</li></ul>',
	'<h3>Ваші обов’язки будуть включати в себе:</h3><ul><li>Проведення презентацій про діяльність школи для батьків;</li><li>Ефективно організовувати навчальний процес і взаємодіяти з усіма учасниками — учнями, батьками та викладачами;</li><li>Контролювати своєчасність платежів, вести базову фінансову звітність;</li><li>Працювати над зворотним зв’язком з боку учасників навчального процесу, аналізувати показники для подальшого їх поліпшення;</li><li>Працювати над підвищенням ефективності показників.</li></ul><h3>Сильні сторони, які ми шукаємо в кандидата на посаду:</h3><ul><li>Бажання вчитися, мотивація досягати поставлених цілей;</li><li><strong>Бажаний досвід роботи в продажах як прямих, так і по телефону;</strong></li><li>Стресостійкість;</li><li><strong>Хороша дикція</strong>, оскільки потрібно консультувати батьків про послуги нашої школи;</li><li>Вільне спілкування українською мовою;</li><li>Самостійність у прийнятті управлінських рішень та готовність нести відповідальність за результат.</li></ul><h3>Ми пропонуємо:</h3><ul><li>Стабільну роботу у великій компанії;</li><li>Навчання і розвиток менеджерських та лідерських навичок у команді, підтримку і допомогу з боку колег;</li><li><strong>Робочий день: 10:00–19:00, вихідні — вівторок і середа;</strong></li><li><strong>Субота і неділя — робочі дні!</strong></li><li>Заробітна плата складається з фіксованої частини та місячних бонусів;</li><li>Реальна можливість кар’єрного росту в міжнародній компанії;</li><li>Робота в молодій команді амбітних професіоналів;</li><li><strong>Надаємо гарне навчання від компанії і постійний супровід. Головне — відповідальність, самоорганізованість, бажання працювати з дітками і вміння гарно продавати. Клієнтську базу ми надаємо з нашої реклами.</strong></li></ul>',
);
$legacy_vacancy_details = array(
	'<p>Проводьте заняття за готовими методичними матеріалами, підтримуйте дружню атмосферу та допомагайте учням досягати результатів.</p>',
	'<p>Проводьте групові заняття, перевіряйте домашні завдання, мотивуйте учнів і працюйте у команді з методистом та менеджером.</p>',
	'<p>Презентуйте школу батькам, організовуйте навчальний процес та працюйте зі зворотним зв’язком.</p>',
);
$vacancy_items = array( array( 'title' => 'Викладач програмування', 'image' => $asset( 'vacancies/programming-teacher.jpeg' ), 'summary' => 'Для енергійних викладачів, які впевнено працюють з комп’ютером і хочуть надихати дітей.', 'details' => $vacancy_details[0] ), array( 'title' => 'Викладач англійської мови', 'image' => $asset( 'vacancies/english-teacher.jpg' ), 'summary' => 'Для викладачів, які допомагають дітям говорити англійською впевнено та із задоволенням.', 'details' => $vacancy_details[1] ), array( 'title' => 'Клієнтський менеджер', 'image' => $asset( 'vacancies/client-manager.jpg' ), 'summary' => 'Для уважних менеджерів, яким подобається допомагати батькам обрати навчання для дитини.', 'details' => $vacancy_details[2] ) );
$fill( 'vacancies_items', $vacancy_items );
foreach ( $vacancy_details as $index => $details ) {
	$key = "vacancies_items_{$index}_details";
	$current = (string) get_post_meta( $page_id, $key, true );
	if ( '' === $current || $legacy_vacancy_details[ $index ] === $current ) {
		update_post_meta( $page_id, $key, $details );
	}
}
$fill( 'vacancies_cta_title', 'Ми чекаємо на тебе!' );
$fill( 'vacancies_cta_text', 'Наші школи розташовані по всій Україні, а також діє великий онлайн-відділ.' );
$fill( 'vacancies_cta_image', $asset( 'vacancies/cta-character.svg' ) );

echo "Vacancies page seeded: {$page_id}.\n";
