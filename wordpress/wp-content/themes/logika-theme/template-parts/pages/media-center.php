<?php

declare(strict_types=1);

$posts = new WP_Query( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 7, 'no_found_rows' => true ) );
?>
<main data-media-center>
	<section class="archive-section"><div class="top-block"><div class="container"><ul class="tags"><li>Акції</li><li>Logika Новини</li><li>Logika Блог</li></ul></div></div><div class="container"><div class="archive-section__wrapp"><h1 class="archive-section__title">Logika Медіа-центр</h1><?php if ( $posts->have_posts() ) : ?><div class="archive-section__box"><div class="archive-section__main" data-media-featured><?php $posts->the_post(); ?><div class="news-card"><a class="news-card__thumbnail" href="<?php the_permalink(); ?>"><picture><?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'large' ); } ?></picture></a><div class="news-card__info"><div class="news-card__top"><ul class="news-card__tags"><li class="news-card__tag">Logika Блог</li></ul><ul class="news-card__details"><li><p><?php echo esc_html( get_the_date( 'd.m.Y' ) ); ?></p></li></ul></div><a class="news-card__title" href="<?php the_permalink(); ?>"><?php the_title(); ?></a><p class="news-card__descr"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 30 ) ); ?></p></div></div></div></div><?php endif; ?></div></div></section>
	<section class="news-section"><div class="container"><div class="news-section__wrapp"><div class="news-section__top"><h2 class="news-section__title">Новини</h2></div><ul class="news-section__items" data-media-list><?php while ( $posts->have_posts() ) : $posts->the_post(); ?><li class="news-section__item"><div class="news-card"><a class="news-card__thumbnail" href="<?php the_permalink(); ?>"><picture><?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'medium_large' ); } ?></picture></a><div class="news-card__info"><ul class="news-card__tags"><li class="news-card__tag">Logika Блог</li></ul><a class="news-card__title" href="<?php the_permalink(); ?>"><?php the_title(); ?></a><ul class="news-card__details"><li><p><?php echo esc_html( get_the_date( 'd.m.Y' ) ); ?></p></li></ul></div></div></li><?php endwhile; ?></ul></div></div></section>
</main>
<?php wp_reset_postdata(); ?>
