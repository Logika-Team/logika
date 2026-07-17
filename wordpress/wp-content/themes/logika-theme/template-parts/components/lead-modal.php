<?php

declare(strict_types=1);

$assets = get_template_directory_uri() . '/assets';
?>
<div class="modal" data-logika-modal hidden>
	<div class="modal__container is-lesson" data-target="lesson">
		<div class="modal__wrapper" role="dialog" aria-modal="true" aria-labelledby="lead-modal-title">
			<button class="modal__close modal-close" type="button" aria-label="Закрити форму">
				<img width="24" height="24" src="<?php echo esc_url( $assets . '/img/sprite/icon-close.svg' ); ?>" alt="">
			</button>
			<div class="modal__lesson">
				<div class="modal__lesson-image"><img width="560" height="300" src="<?php echo esc_url( $assets . '/img/modal-image.webp' ); ?>" alt=""></div>
				<h2 class="visually-hidden" id="lead-modal-title">Перший урок — безкоштовно.</h2>
				<?php get_template_part( 'template-parts/forms/lead', null, array( 'class' => 'modal-form', 'presentation' => 'modal' ) ); ?>
			</div>
		</div>
	</div>
</div>
