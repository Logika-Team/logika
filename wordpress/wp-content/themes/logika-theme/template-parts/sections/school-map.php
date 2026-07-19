<?php

declare(strict_types=1);

$data = wp_parse_args( $args ?? array(), array( 'title' => 'Знайдіть свою школу або<br>навчайтесь онлайн', 'text' => 'Наші школи у 130 містах України — знайдіть зручний варіант поруч із вами або навчайтесь онлайн.' ) );
$theme_assets_uri = get_template_directory_uri() . '/assets';
$front_page_id     = (int) get_option( 'page_on_front' );
$privacy_url       = get_field( 'global_privacy_policy_url', 'option' ) ?: home_url( '/privacy-policy/' );
$course_id         = absint( $data['course_id'] ?? 0 );
$hero_button_label = get_field( 'cta_primary_label', 'option' ) ?: 'Спробувати безкоштовно';
?>
<div data-map-online-form hidden>
	<form class="banner-section__form main-form" data-logika-lead-form novalidate>
		<div class="main-form__title h5"><span>Перший урок — безкоштовно.</span> Залиште заявку за 30 секунд — ми зателефонуємо і підберемо зручний час</div>
		<div class="main-form__inputs"><input class="main-form__input" type="text" name="name" placeholder="Ім’я" required><div class="main-form__phone-wrap"><input class="main-form__input main-form__phone" type="tel" name="tel" placeholder="Номер телефону" data-logika-phone-input aria-describedby="map-logika-phone-error" required><span class="main-form__phone-error" id="map-logika-phone-error" data-logika-phone-error hidden>Введіть коректний номер телефону</span></div><?php echo Logika_Theme_Lead_Form::render_age_select( $front_page_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
		<input type="hidden" name="form_id" value="trial_lesson"><input type="hidden" name="consent_accepted" value="1"><input type="hidden" name="consent_text_version" value="<?php echo esc_attr( get_field( 'form_privacy_text_version', 'option' ) ?: 'v1' ); ?>"><input type="hidden" name="idempotency_key" value=""><input type="hidden" name="course_id" value="<?php echo esc_attr( (string) $course_id ); ?>"><input class="main-form__honeypot" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
		<p class="main-form__text">Натискаючи, ви погоджуєтесь із <a href="<?php echo esc_url( $privacy_url ); ?>">Політикою конфіденційності</a></p>
		<button class="main-form__btn btn btn--yellow" type="submit"><?php echo esc_html( $hero_button_label ); ?></button><p class="main-form__status" aria-live="polite"></p>
	</form>
</div>
<section class="school-map" data-school-map data-map-url="<?php echo esc_url( $theme_assets_uri . '/img/maps/ukraine-regions.svg' ); ?>" data-branch-icon-url="<?php echo esc_url( $theme_assets_uri . '/img/icons/solar_route-outline.svg' ); ?>" data-branches-endpoint="<?php echo esc_url( rest_url( 'logika/v1/cities/' ) ); ?>" aria-labelledby="school-map-title">
	<div class="container">
		<div class="school-map__heading">
			<h2 id="school-map-title"><?php echo wp_kses( (string) $data['title'], array( 'br' => array() ) ); ?></h2>
			<p><?php echo esc_html( $data['text'] ); ?></p>
		</div>
		<div class="school-map__mode" role="group" aria-label="Формат навчання">
			<button class="is-active" type="button" data-map-mode="offline">Навчатися у нашому місті</button>
			<button type="button" data-map-mode="online">Онлайн навчання</button>
		</div>
		<div class="school-map__layout">
			<div class="school-map__visual">
				<div class="school-map__canvas" data-map-canvas aria-live="polite"><p>Завантажуємо карту областей...</p></div>
			</div>
			<div class="school-map__selector">
				<h3>Оберіть місто навчання</h3>
				<p>Ми підкажемо зручний варіант у вибраній області.</p>
				<h4 data-map-region>Дніпропетровська область</h4>
				<div class="school-map__cities" data-map-cities></div>
			</div>
		</div>
		<div class="school-map__details" data-map-details>
			<h3 data-map-city-title>ДНІПРО</h3>
			<div class="school-map__details-content">
				<div class="school-map__locations">
					<p class="school-map__locations-count" data-map-locations-count>Усі локації (8)</p>
					<ul class="school-map__schools" data-map-schools></ul>
				</div>
				<iframe class="school-map__frame" data-map-frame title="Карта шкіл у Дніпрі" loading="lazy" src="https://www.google.com/maps?q=Dnipro,+Ukraine&amp;output=embed"></iframe>
			</div>
		</div>
	</div>
</section>
