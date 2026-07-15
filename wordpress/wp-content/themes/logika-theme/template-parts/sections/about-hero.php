<?php

declare(strict_types=1);

$assets = get_template_directory_uri() . '/assets/img';
?>
<section class="banner-section about-hero">
	<div class="container">
		<div class="banner-section__wrapp">
			<div class="banner-section__blocks">
				<div class="banner-section__left">
					<div class="banner-section__info"><h1>Найбільша в Україні школа програмування для дітей 7-17 років</h1><h4>Перші результати вже через 4 тижні</h4></div>
					<div class="banner-section__character-boy"><img width="432" height="232" src="<?php echo esc_url( $assets . '/about/hero-characters.png' ); ?>" alt="Персонажі Logika Ctrl та W"></div>
				</div>
				<div class="banner-section__right" id="about-lead-form">
					<div class="main-form__title h5"><span>Перший урок — безкоштовно.</span> Залиште заявку за 30 секунд — ми зателефонуємо і підберемо зручний час</div>
					<?php get_template_part( 'template-parts/forms/lead' ); ?>
					<div class="banner-section__character-logika"><img width="97" height="146" src="<?php echo esc_url( $assets . '/logika-character.svg' ); ?>" alt="Персонаж Logika"></div>
				</div>
			</div>
		</div>
	</div>
</section>
