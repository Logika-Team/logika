<?php

declare(strict_types=1);

require dirname( __DIR__ ) . '/wordpress/wp-load.php';
require dirname( __DIR__ ) . '/scripts/data/tilda-courses.php';
require dirname( __DIR__ ) . '/scripts/seed-tilda-courses.php';

$expected = array(
	'visual-programming' => 'Створюємо анімації, ігри та перші проєкти у Scratch.',
	'game-design' => 'Створюємо власні ігри та вчимося мислити як геймдизайнери.',
	'websites' => 'Створюємо сучасні сайти та втілюємо власні ідеї у web.',
	'graphic-design' => 'Освоюємо дизайн-інструменти та створюємо яскраві проєкти.',
	'python-start' => 'Вчимося кодити на Python і створюємо корисні програми.',
	'python-mastery' => 'Поглиблюємо Python та створюємо ігри, інтерфейси й застосунки.',
	'python-advanced' => 'Вивчаємо професійні інструменти Python і готуємося до кар’єри.',
	'graphic-design-2-year' => 'Створюємо портфоліо та впевнено працюємо з графічним дизайном.',
	'python-expert' => 'Створюємо складні Python-проєкти: ігри, інтерфейси та мультимедіа.',
	'computer-literacy' => 'Використовуємо сучасні програми для навчання і щоденних справ.',
	'computer-literacy-14' => 'Використовуємо сучасні програми для навчання і щоденних справ.',
	'frontend' => 'Створюємо сучасні інтерфейси сайтів і web-застосунків.',
	'artificial-intelligence' => 'Вчимося працювати з нейромережами та створювати ШІ-агентів.',
);

logika_seed_tilda_courses( logika_tilda_courses() );
$errors = array();

foreach ( $expected as $slug => $description ) {
	$course = get_page_by_path( $slug, OBJECT, 'course' );
	$value  = $course ? (string) get_field( 'course_card_description', $course->ID ) : '';
	if ( $description !== $value || mb_strlen( $value ) > 100 ) {
		$errors[] = "Missing short card description for {$slug}.";
	}
}

$course = get_page_by_path( 'python-expert', OBJECT, 'course' );
if ( ! $course ) {
	$errors[] = 'Python Expert course is missing.';
} else {
	$original = (string) get_field( 'course_card_description', $course->ID );
	register_shutdown_function( static fn() => update_field( 'course_card_description', $original, $course->ID ) );
	delete_field( 'course_card_description', $course->ID );
	ob_start();
	Logika_Theme_Source_Markup::renderPage( 'it-courses' );
	$output = (string) ob_get_clean();
	if ( ! str_contains( $output, (string) get_field( 'course_short_description', $course->ID ) ) ) {
		$errors[] = 'Catalog does not fall back to the existing course description.';
	}
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Course card descriptions are editor-managed and concise.\n";
