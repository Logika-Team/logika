<?php

declare(strict_types=1);

$assets    = get_template_directory_uri() . '/assets';
$image     = $assets . '/img/camp/team.webp';
$camp_link = get_post_type_archive_link( 'camp' ) ?: home_url( '/camps/' );
?>
<div class="modal" data-logika-camp-modal hidden>
	<div class="modal__container is-camps" data-target="camps">
		<div class="modal__wrapper" role="dialog" aria-modal="true" aria-labelledby="camp-modal-title">
			<div class="modal__camps">
				<div class="modal__camps-top">
					<h2 class="modal__camps-title h4" id="camp-modal-title">Оберіть зміну</h2>
					<button class="modal__close modal-close" type="button" aria-label="Закрити вибір зміни">
						<img width="24" height="24" src="<?php echo esc_url( $assets . '/img/sprite/icon-close.svg' ); ?>" alt="">
					</button>
				</div>

				<ul class="modal__camps-items">
					<?php for ( $index = 0; $index < 4; $index++ ) : ?>
						<li class="modal__camps-item">
							<div class="modal__camps-image">
								<img width="100" height="100" src="<?php echo esc_url( $image ); ?>" alt="Учасники табору">
							</div>

							<div class="modal__camps-dates">27.06 - 06.07</div>

							<div class="modal__camps-info">
								<span>Фестиваль професій</span>
								<p>Це справжні пригоди, у які поринають всі мешканці табору на весь термін путівки.</p>
							</div>

							<a class="modal__camps-link btn btn--violet" href="<?php echo esc_url( $camp_link ); ?>">Дізнатись більше</a>
						</li>
					<?php endfor; ?>
				</ul>
			</div>
		</div>
	</div>
</div>
