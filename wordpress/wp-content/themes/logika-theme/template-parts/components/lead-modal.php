<?php

declare(strict_types=1);
?>
<div class="lead-modal" data-logika-lead-modal hidden>
	<div class="lead-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="lead-modal-title">
		<button class="lead-modal__close" type="button" data-logika-lead-modal-close aria-label="Закрити форму">×</button>
		<div class="lead-modal__content">
			<h2 class="lead-modal__title" id="lead-modal-title">Перший урок — безкоштовно.</h2>
			<p class="lead-modal__text">Залиште заявку за 30 секунд — ми зателефонуємо і підберемо зручний час</p>
			<?php get_template_part( 'template-parts/forms/lead' ); ?>
		</div>
	</div>
</div>
