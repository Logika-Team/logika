<?php

declare(strict_types=1);

$city_id = absint( $args['city_id'] ?? 0 );
$course_id = absint( $args['course_id'] ?? 0 );
$camp_id = absint( $args['camp_id'] ?? 0 );
$form_class = trim( 'main-form ' . (string) ( $args['class'] ?? '' ) );
$button_label = (string) ( $args['button_label'] ?? '' ) ?: 'Надіслати заявку';
$privacy_url = get_field( 'global_privacy_policy_url', 'option' ) ?: home_url( '/privacy-policy/' );
$is_modal     = 'modal' === (string) ( $args['presentation'] ?? '' );
?>
<?php if ( $is_modal ) : ?>
<form class="<?php echo esc_attr( $form_class ); ?>" data-logika-lead-form novalidate>
	<div class="modal-form__labels">
		<label class="modal-form__label"><span>Ім’я</span><input class="modal-form__input main-form__input" type="text" name="name" placeholder="Ім’я Прізвище" required></label>
		<label class="modal-form__label"><span>Номер телефону</span><div class="main-form__phone-wrap"><input class="modal-form__input main-form__input main-form__phone" type="tel" name="phone" placeholder="Номер телефону" data-logika-phone-input aria-describedby="logika-phone-error" required><span class="main-form__phone-error" id="logika-phone-error" data-logika-phone-error hidden>Введіть коректний номер телефону</span></div></label>
		<label class="modal-form__label"><span>Вік дитини</span><?php echo Logika_Theme_Lead_Form::render_age_select(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></label>
	</div>
	<input type="hidden" name="form_id" value="trial_lesson"><input type="hidden" name="consent_accepted" value="1"><input type="hidden" name="consent_text_version" value="<?php echo esc_attr( get_field( 'form_privacy_text_version', 'option' ) ?: 'v1' ); ?>"><input type="hidden" name="idempotency_key" value=""><input type="hidden" name="city_id" value="<?php echo esc_attr( $city_id ); ?>"><input type="hidden" name="course_id" value="<?php echo esc_attr( $course_id ); ?>"><?php if ( $camp_id ) : ?><input type="hidden" name="camp_id" value="<?php echo esc_attr( $camp_id ); ?>"><?php endif; ?><input class="main-form__honeypot" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
	<button class="modal-form__btn main-form__btn btn btn--yellow" type="submit">Надіслати</button>
	<p class="modal-form__text main-form__text">Натискаючи, ви погоджуєтесь із <a href="<?php echo esc_url( $privacy_url ); ?>">Політикою конфіденційності</a></p>
	<p class="main-form__status" aria-live="polite"></p>
</form>
<?php else : ?>
<form class="<?php echo esc_attr( $form_class ); ?>" data-logika-lead-form novalidate>
	<div class="main-form__inputs"><input class="main-form__input" type="text" name="name" placeholder="Ім’я" required><div class="main-form__phone-wrap"><input class="main-form__input main-form__phone" type="tel" name="phone" placeholder="Номер телефону" data-logika-phone-input aria-describedby="logika-phone-error" required><span class="main-form__phone-error" id="logika-phone-error" data-logika-phone-error hidden>Введіть коректний номер телефону</span></div><?php echo Logika_Theme_Lead_Form::render_age_select(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
	<input type="hidden" name="form_id" value="trial_lesson"><input type="hidden" name="consent_accepted" value="1"><input type="hidden" name="consent_text_version" value="<?php echo esc_attr( get_field( 'form_privacy_text_version', 'option' ) ?: 'v1' ); ?>"><input type="hidden" name="idempotency_key" value=""><input type="hidden" name="city_id" value="<?php echo esc_attr( $city_id ); ?>"><input type="hidden" name="course_id" value="<?php echo esc_attr( $course_id ); ?>"><?php if ( $camp_id ) : ?><input type="hidden" name="camp_id" value="<?php echo esc_attr( $camp_id ); ?>"><?php endif; ?><input class="main-form__honeypot" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
	<p class="main-form__text">Натискаючи, ви погоджуєтесь із <a href="<?php echo esc_url( $privacy_url ); ?>">Політикою конфіденційності</a></p>
	<button class="main-form__btn btn btn--yellow" type="submit"><?php echo esc_html( $button_label ); ?></button><p class="main-form__status" aria-live="polite"></p>
</form>
<?php endif; ?>
