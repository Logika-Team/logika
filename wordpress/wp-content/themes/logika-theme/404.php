<?php

declare(strict_types=1);

get_header();
?>
<main class="error-page">
	<section class="error-page__section" aria-labelledby="error-page-title">
		<div class="container">
			<div class="error-page__layout">
				<div class="error-page__code-card" aria-hidden="true">
					<p class="error-page__code">404</p>
					<span class="error-page__code-label">ПОМИЛКА НАВІГАЦІЇ</span>
				</div>

				<div class="error-page__content">
					<p class="error-page__eyebrow">Сторінку не знайдено</p>
					<h1 id="error-page-title">Схоже, ця сторінка загубилася</h1>
					<p class="error-page__text">Повернімося до навчання: оберіть напрям або завітайте на головну сторінку Logika.</p>
					<div class="error-page__actions">
						<a class="btn btn--yellow" href="<?php echo esc_url( home_url( '/' ) ); ?>">На головну</a>
						<a class="btn btn--bordered error-page__secondary" href="<?php echo esc_url( home_url( '/it-courses/' ) ); ?>">Обрати курс</a>
					</div>
				</div>
			</div>
		</div>
	</section>
</main>
<?php
get_footer();
