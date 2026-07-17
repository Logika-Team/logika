<?php

declare(strict_types=1);

$page_id    = get_option( 'page_on_front' );
$title      = get_field( 'home_hero_title', $page_id ) ?: 'Найбільша в Україні школа програмування для дітей 7-17 років';
$subtitle   = get_field( 'home_hero_text', $page_id ) ?: 'Перші результати вже через 4 тижні';
$cta_label  = get_field( 'cta_primary_label', 'option' ) ?: 'Спробувати безкоштовно';
$privacy_url = get_field( 'global_privacy_policy_url', 'option' ) ?: home_url( '/privacy-policy/' );
$assets     = get_template_directory_uri() . '/assets/img';
$trust_items = (array) get_field( 'home_trust_items', $page_id );
?>
<section class="banner-section">
	<div class="container">
		<div class="banner-section__wrapp">
			<div class="banner-section__blocks">
				<div class="banner-section__left">
					<div class="banner-section__info"><h1><?php echo esc_html( $title ); ?></h1><h4><?php echo esc_html( $subtitle ); ?></h4></div>
					<div class="banner-section__character-boy"><img width="440" height="225" src="<?php echo esc_url( $assets . '/boy-character.svg' ); ?>" alt="Учень Logika"></div>
				</div>
				<div class="banner-section__right">
					<form class="banner-section__form main-form" data-logika-lead-form novalidate>
						<div class="main-form__title h5"><span>Перший урок — безкоштовно.</span> Залиште заявку за 30 секунд — ми зателефонуємо і підберемо зручний час</div>
					<div class="main-form__inputs"><input class="main-form__input" type="text" name="name" placeholder="Ім’я" required><div class="main-form__phone-wrap"><input class="main-form__input main-form__phone" type="tel" name="phone" placeholder="Номер телефону" data-logika-phone-input aria-describedby="logika-phone-error" required><span class="main-form__phone-error" id="logika-phone-error" data-logika-phone-error hidden>Введіть коректний номер телефону</span></div><?php echo Logika_Theme_Lead_Form::render_age_select( (int) $page_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
						<input type="hidden" name="form_id" value="trial_lesson"><input type="hidden" name="consent_text_version" value="<?php echo esc_attr( get_field( 'form_privacy_text_version', 'option' ) ?: 'v1' ); ?>"><input type="hidden" name="idempotency_key" value=""><input class="main-form__honeypot" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
						<label class="main-form__text"><input type="checkbox" name="consent_accepted" value="1" required> Натискаючи, ви погоджуєтесь із <a href="<?php echo esc_url( $privacy_url ); ?>">Політикою конфіденційності</a></label>
						<button class="main-form__btn btn btn--yellow" type="submit"><?php echo esc_html( $cta_label ); ?> <svg width="20" height="20"><use href="<?php echo esc_url( $assets . '/sprite/sprite.svg#arrow-right' ); ?>"></use></svg></button>
						<p class="main-form__status" aria-live="polite"></p>
					</form>
					<div class="banner-section__character-logika"><img width="97" height="146" src="<?php echo esc_url( $assets . '/logika-character.svg' ); ?>" alt="Персонаж Logika"></div>
				</div>
			</div>
			<ul class="banner-section__bar">
				<?php foreach ( $trust_items as $item ) : ?><?php $icon = ! empty( $item['icon'] ) ? wp_get_attachment_image_url( $item['icon'], 'thumbnail' ) : $assets . '/banner-bar/icon-rating-star.svg'; ?><li><span><img width="56" height="56" src="<?php echo esc_url( $icon ); ?>" alt=""></span><p><?php echo esc_html( $item['text'] ?? '' ); ?></p></li><?php endforeach; ?>
			</ul>
		</div>
	</div>
</section>
