<?php

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

final class Logika_Theme_Menu_Walker extends Walker_Nav_Menu {
	/** @var string[] */
	private array $submenu_ids = array();

	public function start_lvl( &$output, $depth = 0, $args = null ): void {
		$id      = end( $this->submenu_ids ) ?: 'submenu';
		$output .= '<ul class="sub-menu" data-content="' . esc_attr( $id ) . '">';
	}

	public function end_lvl( &$output, $depth = 0, $args = null ): void {
		array_pop( $this->submenu_ids );
		$output .= '</ul>';
	}

	public function start_el( &$output, $menu_item, $depth = 0, $args = null, $current_object_id = 0 ): void {
		$classes      = (array) $menu_item->classes;
		$has_children = in_array( 'menu-item-has-children', $classes, true );
		$submenu_id   = 'menu-' . $menu_item->ID;
		$output      .= '<li class="menu-item' . ( $has_children ? ' menu-has-child' : '' ) . '"><a class="menu-link" href="' . esc_url( $menu_item->url ) . '"' . ( $menu_item->target ? ' target="' . esc_attr( $menu_item->target ) . '"' : '' ) . '>' . esc_html( $menu_item->title ) . '</a>';
		if ( $has_children ) {
			$this->submenu_ids[] = $submenu_id;
			$output             .= '<button class="menu-button" type="button" data-id="' . esc_attr( $submenu_id ) . '" aria-label="Відкрити підменю"><svg width="18" height="18"><use href="' . esc_url( get_template_directory_uri() . '/assets/img/sprite/sprite.svg#icon-caret-down' ) . '"></use></svg></button>';
		}
	}

	public function end_el( &$output, $menu_item, $depth = 0, $args = null ): void {
		$output .= '</li>';
	}
}
