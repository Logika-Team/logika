<?php

declare(strict_types=1);

$assets = get_template_directory_uri() . '/assets/img/camp';
$highlights = array(
	array( 'figma-age.png', 'Для дітей 10-16<br>років', 'one' ),
	array( 'figma-all-inclusive.png', 'Формат All-<br>inclusive', 'two' ),
	array( 'figma-entertainment.png', 'Власні<br>розважальні<br>програми', 'three' ),
	array( 'figma-team.png', 'Працює своя<br>команда вожатих', 'four' ),
	array( 'figma-transfer.png', 'Трансферти з<br>великих міст', 'five' ),
	array( 'figma-medical.png', 'Медичне<br>страхування', 'six' ),
);
$formats = array(
	array( 'summer.jpg', 'Літо', 'Літній морський пейзаж' ),
	array( 'autumn.jpg', 'Осінь', 'Осінні гори' ),
	array( 'winter.jpg', 'Зима', 'Засніжені гори взимку' ),
	array( 'spring.jpg', 'Весна', 'Весняний гірський луг' ),
);
?>
<main>
	<section class="camp-page-hero" aria-labelledby="camp-page-hero-title"><img class="camp-page-hero__art" src="<?php echo esc_url( $assets . '/camp-hero.svg' ); ?>" alt="" aria-hidden="true"><div class="container camp-page-hero__container"><div class="camp-page-hero__content"><h1 id="camp-page-hero-title">Табори з Logika: подаруйте<br>дитині незабутні емоції</h1><p>Відпочиваємо та розвиваємося разом!</p><a class="camp-page-hero__cta btn btn--violet" href="<?php echo esc_url( get_post_type_archive_link( 'camp' ) ); ?>">Переглянути табори <span aria-hidden="true">→</span></a></div></div></section>
	<section class="camp-highlights" aria-labelledby="camp-highlights-title"><div class="container"><h2 class="camp-highlights__title" id="camp-highlights-title">Табори з Logika – це:</h2><ul class="camp-highlights__list"><?php foreach ( $highlights as [ $image, $text, $modifier ] ) : ?><li class="camp-highlights__item"><img class="camp-highlights__character camp-highlights__character--<?php echo esc_attr( $modifier ); ?>" src="<?php echo esc_url( $assets . '/highlights/' . $image ); ?>" alt=""><p><?php echo wp_kses_post( $text ); ?></p></li><?php endforeach; ?></ul></div></section>
	<section class="camp-formats" aria-labelledby="camp-formats-title"><div class="container"><h2 class="camp-formats__title" id="camp-formats-title">Оберіть свій формат</h2><ul class="camp-formats__list"><?php foreach ( $formats as [ $image, $season, $alt ] ) : ?><li class="camp-formats__item"><img src="<?php echo esc_url( $assets . '/formats/' . $image ); ?>" alt="<?php echo esc_attr( $alt ); ?>"><span class="camp-formats__season"><?php echo esc_html( $season ); ?></span><a href="<?php echo esc_url( get_post_type_archive_link( 'camp' ) ); ?>">Переглянути табори</a></li><?php endforeach; ?></ul></div></section>
</main>
