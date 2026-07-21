<?php

declare(strict_types=1);

namespace Logika\Core;

final class OptionsPage {
	public static function register(): void {
		if ( ! function_exists( 'acf_add_options_page' ) ) {
			return;
		}

		acf_add_options_page(
			array(
				'page_title' => 'Налаштування сайту',
				'menu_title' => 'Налаштування сайту',
				'menu_slug'  => 'logika-settings',
				'capability' => 'edit_pages',
				'redirect'   => false,
			)
		);
	}
}
