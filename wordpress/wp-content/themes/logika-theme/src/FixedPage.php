<?php

declare(strict_types=1);

final class Logika_Theme_Fixed_Page {
	private const CONFIG = array(
		'about'        => 'about',
		'it-courses'   => 'it-courses',
		'en-courses'   => 'en-courses',
		'faq'          => 'faq',
		'media-center' => 'media-center',
		'vacancies'    => 'vacancies',
		'camps'        => 'camps',
	);

	public static function render( string $kind, int $page_id ): void {
		$source = self::CONFIG[ $kind ] ?? '';
		if ( ! $source ) {
			return;
		}
		Logika_Theme_Source_Markup::renderPage( $source, $page_id );
	}

	public static function renderLegal( int $page_id ): void {
		$source = Logika_Theme_Source_Markup::sourceForCurrentPage();
		if ( $source ) {
			Logika_Theme_Source_Markup::renderPage( $source, $page_id );
			return;
		}
		$sections = (array) get_field( 'legal_sections', $page_id );
		$gallery  = (array) get_field( 'legal_gallery', $page_id );
		if ( ! get_field( 'legal_intro_title', $page_id ) && ! get_field( 'legal_intro_text', $page_id ) && ! $sections && ! $gallery ) {
			return;
		}
		get_template_part( 'template-parts/pages/legal', null, array( 'page_id' => $page_id, 'sections' => $sections, 'gallery' => $gallery ) );
	}
}
