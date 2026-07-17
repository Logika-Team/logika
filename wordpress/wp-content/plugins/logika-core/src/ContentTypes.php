<?php

declare(strict_types=1);

namespace Logika\Core;

final class ContentTypes {
	public static function register(): void {
		self::registerTaxonomies();
		self::registerPostTypes();
	}

	private static function registerTaxonomies(): void {
		foreach (
			array(
				'region'           => array( array( 'city', 'branch' ), 'Регіон', 'Регіони' ),
				'course_direction' => array( array( 'course', 'camp' ), 'Напрям', 'Напрями' ),
				'age_group'        => array( array( 'course', 'camp' ), 'Вікова група', 'Вікові групи' ),
				'learning_format'  => array( array( 'course', 'camp', 'branch' ), 'Формат навчання', 'Формати навчання' ),
				'faq_context'      => array( array( 'faq_item' ), 'Контекст FAQ', 'Контексти FAQ' ),
			) as $taxonomy => $definition
		) {
			register_taxonomy(
				$taxonomy,
				$definition[0],
				array(
					'labels'       => self::labels( $definition[1], $definition[2] ),
					'hierarchical' => true,
					'show_in_rest' => true,
					'show_admin_column' => true,
				)
			);
		}
	}

	private static function registerPostTypes(): void {
		self::registerPostType( 'city', 'Місто', 'Міста', true, false, 'cities' );
		self::registerPostType( 'branch', 'Філія', 'Філії', false, false );
		self::registerPostType( 'course', 'Курс', 'Курси', true, true, 'courses' );
		self::registerPostType( 'camp', 'Табір', 'Табори', true, true, 'camps' );
		self::registerPostType( 'review', 'Відгук', 'Відгуки', false, false );
		self::registerPostType( 'faq_item', 'FAQ', 'FAQ', false, false );
		self::registerPostType( 'article_author', 'Автор статей', 'Автори статей', false, false, '', array( 'title' ) );
	}

	private static function registerPostType( string $postType, string $singular, string $plural, bool $public, bool $hasArchive, string $slug = '', array $supports = array( 'title', 'thumbnail' ) ): void {
		register_post_type(
			$postType,
			array(
				'labels'             => self::labels( $singular, $plural ),
				'public'             => $public,
				'show_ui'            => true,
				'show_in_rest'       => true,
				'has_archive'        => $hasArchive,
				'publicly_queryable' => $public,
				'rewrite'            => $public ? array( 'slug' => $slug ?: $postType ) : false,
				'menu_icon'          => 'dashicons-welcome-learn-more',
				'supports'           => $supports,
			)
		);
	}

	/**
	 * @return array<string, string>
	 */
	private static function labels( string $singular, string $plural ): array {
		return array(
			'name'          => $plural,
			'singular_name' => $singular,
			'add_new_item'  => "Додати {$singular}",
			'edit_item'     => "Редагувати {$singular}",
			'all_items'     => $plural,
			'search_items'  => "Шукати: {$plural}",
		);
	}
}
