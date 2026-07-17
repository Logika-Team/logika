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
		acf_add_options_sub_page(
			array(
				'page_title'  => 'Архів таборів',
				'menu_title'  => 'Архів таборів',
				'menu_slug'   => 'logika-camp-archive',
				'parent_slug' => 'logika-settings',
				'post_id'     => 'camp_archive',
				'capability'  => 'edit_pages',
			)
		);
	}
}
