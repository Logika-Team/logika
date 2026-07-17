<?php

declare(strict_types=1);

$data    = wp_parse_args( $args ?? array(), array( 'title' => 'Курси', 'items' => null ) );
$courses = Logika_Theme_Entities::courses( is_array( $data['items'] ) ? $data['items'] : null );
if ( ! $courses ) {
	return;
}
?>
<section class="courses-section"><div class="container"><div class="courses-section__wrapp"><h2 class="courses-section__title"><?php echo esc_html( $data['title'] ); ?></h2><ul class="courses-section__items">
	<?php foreach ( $courses as $course_id ) : $title = get_the_title( $course_id ); $image = (int) get_field( 'course_card_image', $course_id ) ?: get_post_thumbnail_id( $course_id ); ?>
		<li class="courses-section__item"><div class="courses-section__item-media"><div class="courses-section__item-image"><?php echo $image ? wp_get_attachment_image( $image, 'large', false, array( 'alt' => $title ) ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div></div><div class="courses-section__item-ages"><?php echo esc_html( $title ); ?></div><?php if ( get_field( 'course_short_description', $course_id ) ) : ?><p><?php echo esc_html( get_field( 'course_short_description', $course_id ) ); ?></p><?php endif; ?><a class="courses-section__item-link btn btn--white" href="<?php echo esc_url( get_permalink( $course_id ) ); ?>">Переглянути курс</a></li>
	<?php endforeach; ?>
</ul></div></div></section>
