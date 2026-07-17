<?php
get_header();
?>
<main>
	<section class="blog-section" data-media-blog>
		<div class="container">
			<div class="blog-section__head">
				<h1 class="h2">Усі статті</h1>
				<div class="blog-section__filters" aria-label="Фільтри статей">
					<div class="main-form__select-wrap blog-section__filter" data-media-filter><input type="hidden" value="new" data-media-sort><button class="main-form__input main-form__age-trigger blog-section__filter-trigger" type="button" data-media-filter-trigger aria-haspopup="listbox" aria-expanded="false"><span class="main-form__age-label">Спочатку новіші</span></button><ul class="main-form__age-dropdown blog-section__filter-dropdown" role="listbox" hidden><li><button class="main-form__age-option" type="button" role="option" aria-selected="true" data-media-filter-option="new">Спочатку новіші</button></li><li><button class="main-form__age-option" type="button" role="option" aria-selected="false" data-media-filter-option="old">Спочатку старіші</button></li></ul></div>
					<div class="main-form__select-wrap blog-section__filter" data-media-filter><input type="hidden" value="" data-media-year><button class="main-form__input main-form__age-trigger blog-section__filter-trigger" type="button" data-media-filter-trigger aria-haspopup="listbox" aria-expanded="false"><span class="main-form__age-label">Усі роки</span></button><ul class="main-form__age-dropdown blog-section__filter-dropdown" role="listbox" data-media-year-options hidden></ul></div>
				</div>
			</div>
			<ul class="articles-section__items" data-media-list></ul>
		</div>
	</section>
</main>
<?php get_footer();
