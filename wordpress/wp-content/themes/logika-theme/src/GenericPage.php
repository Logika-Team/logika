<?php

declare(strict_types=1);

final class Logika_Theme_Generic_Page {
	public static function render( int $page_id ): void {
		$sections = array_filter( (array) get_field( 'generic_sections', $page_id ), 'is_array' );
		if ( ! $sections ) {
			echo '<main><article class="page-content editor">' . wp_kses_post( apply_filters( 'the_content', (string) get_post_field( 'post_content', $page_id ) ) ) . '</article></main>';
			return;
		}

		echo '<main>';
		foreach ( $sections as $section ) {
			self::renderSection( $section );
		}
		echo '</main>';
	}

	private static function renderSection( array $section ): void {
		switch ( $section['acf_fc_layout'] ?? '' ) {
			case 'hero':
				get_template_part( 'template-parts/sections/fixed-hero', null, $section );
				break;
			case 'rich_text':
				if ( ! empty( $section['title'] ) || ! empty( $section['content'] ) ) {
					echo '<section class="content-section"><div class="container"><div class="editor">' . ( empty( $section['title'] ) ? '' : '<h2>' . esc_html( $section['title'] ) . '</h2>' ) . wp_kses_post( (string) ( $section['content'] ?? '' ) ) . '</div></div></section>';
				}
				break;
			case 'gallery':
				get_template_part( 'template-parts/sections/gallery', null, $section );
				break;
			case 'course_selection':
				get_template_part( 'template-parts/sections/course-selection', null, $section );
				break;
			case 'reviews':
				get_template_part( 'template-parts/sections/reviews', null, $section );
				break;
			case 'faq':
				get_template_part( 'template-parts/sections/faq', null, array( 'section_title' => $section['title'] ?? '', 'items' => $section['items'] ?? array() ) );
				break;
			case 'partners':
				get_template_part( 'template-parts/sections/partners', null, array( 'title' => $section['title'] ?? '', 'items' => (array) get_field( 'global_partners', 'option' ) ) );
				break;
			case 'school_map':
				get_template_part( 'template-parts/sections/school-map', null, $section );
				break;
			case 'cta':
				get_template_part( 'template-parts/sections/cta', null, $section );
		}
	}
}
