<?php
$settings       = get_page_by_path( 'media-center' );
$settings_id    = $settings instanceof WP_Post ? $settings->ID : 0;
$title          = (string) ( $settings_id ? get_field( 'media_center_blog_title', $settings_id ) : '' ) ?: 'Усі статті';
$sort_new_label = (string) ( $settings_id ? get_field( 'media_center_blog_sort_new_label', $settings_id ) : '' ) ?: 'Спочатку новіші';
$sort_old_label = (string) ( $settings_id ? get_field( 'media_center_blog_sort_old_label', $settings_id ) : '' ) ?: 'Спочатку старіші';
$years_label    = (string) ( $settings_id ? get_field( 'media_center_blog_years_label', $settings_id ) : '' ) ?: 'Усі роки';
get_header();
?>
<main>
	<section class="blog-section" data-media-blog>
		<div class="container">
			<div class="blog-section__head">
				<h1 class="h2"><?php echo esc_html( $title ); ?></h1>
				<div class="blog-section__filters" aria-label="Фільтри статей">
					<div class="main-form__select-wrap blog-section__filter" data-media-filter><input type="hidden" value="new" data-media-sort><button class="main-form__input main-form__age-trigger blog-section__filter-trigger" type="button" data-media-filter-trigger aria-haspopup="listbox" aria-expanded="false"><span class="main-form__age-label"><?php echo esc_html( $sort_new_label ); ?></span></button><ul class="main-form__age-dropdown blog-section__filter-dropdown" role="listbox" hidden><li><button class="main-form__age-option" type="button" role="option" aria-selected="true" data-media-filter-option="new"><?php echo esc_html( $sort_new_label ); ?></button></li><li><button class="main-form__age-option" type="button" role="option" aria-selected="false" data-media-filter-option="old"><?php echo esc_html( $sort_old_label ); ?></button></li></ul></div>
					<div class="main-form__select-wrap blog-section__filter" data-media-filter><input type="hidden" value="" data-media-year><button class="main-form__input main-form__age-trigger blog-section__filter-trigger" type="button" data-media-filter-trigger aria-haspopup="listbox" aria-expanded="false"><span class="main-form__age-label"><?php echo esc_html( $years_label ); ?></span></button><ul class="main-form__age-dropdown blog-section__filter-dropdown" role="listbox" data-media-year-options hidden></ul></div>
				</div>
			</div>
			<ul class="articles-section__items" data-media-list></ul>
		</div>
	</section>
</main>
<?php get_footer();
