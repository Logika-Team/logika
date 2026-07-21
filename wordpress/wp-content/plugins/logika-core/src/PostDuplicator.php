<?php

declare(strict_types=1);

namespace Logika\Core;

use WP_Post;

/**
 * Lets a content manager clone an existing course/camp into a draft so they only have to
 * swap the title, images and copy instead of rebuilding every ACF tab from scratch.
 */
final class PostDuplicator {
	private const POST_TYPES = array( 'course', 'camp' );
	private const ACTION     = 'logika_duplicate_post';

	public static function register(): void {
		add_action( 'admin_action_' . self::ACTION, array( self::class, 'handleRequest' ) );
		foreach ( self::POST_TYPES as $post_type ) {
			add_filter( 'post_row_actions', array( self::class, 'addRowAction' ), 10, 2 );
			add_action( 'add_meta_boxes_' . $post_type, array( self::class, 'addMetaBox' ) );
		}
		add_action( 'admin_notices', array( self::class, 'renderNotice' ) );
	}

	/**
	 * @param array<string, string> $actions
	 */
	public static function addRowAction( array $actions, WP_Post $post ): array {
		if ( ! in_array( $post->post_type, self::POST_TYPES, true ) || ! current_user_can( 'edit_post', $post->ID ) ) {
			return $actions;
		}

		$actions['logika_duplicate'] = '<a href="' . esc_url( self::duplicateUrl( $post->ID ) ) . '">Дублювати</a>';

		return $actions;
	}

	public static function addMetaBox(): void {
		global $post;
		if ( ! $post instanceof WP_Post || 0 === $post->ID ) {
			return;
		}
		add_meta_box(
			'logika-duplicate-post',
			'Створити копію',
			static function () use ( $post ): void {
				printf(
					'<p><a class="button" href="%s">Дублювати цей запис</a></p><p class="description">Створить чернетку з усіма полями та зображеннями — залишиться замінити текст і картинки.</p>',
					esc_url( self::duplicateUrl( $post->ID ) )
				);
			},
			$post->post_type,
			'side',
			'high'
		);
	}

	public static function duplicateUrl( int $post_id ): string {
		return wp_nonce_url(
			admin_url( 'admin.php?action=' . self::ACTION . '&post=' . $post_id ),
			self::ACTION . '_' . $post_id
		);
	}

	public static function handleRequest(): void {
		$post_id = isset( $_GET['post'] ) ? absint( wp_unslash( $_GET['post'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		check_admin_referer( self::ACTION . '_' . $post_id );

		$source = get_post( $post_id );
		if ( ! $source instanceof WP_Post || ! in_array( $source->post_type, self::POST_TYPES, true ) || ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die( esc_html__( 'Немає доступу до цього запису.', 'logika-core' ) );
		}

		$new_id = self::duplicate( $source );

		wp_safe_redirect( admin_url( 'post.php?action=edit&post=' . $new_id . '&logika_duplicated=1' ) );
		exit;
	}

	public static function duplicate( WP_Post $source ): int {
		$new_id = wp_insert_post(
			array(
				'post_type'    => $source->post_type,
				'post_status'  => 'draft',
				'post_title'   => $source->post_title . ' (копія)',
				'post_content' => $source->post_content,
				'post_excerpt' => $source->post_excerpt,
				'menu_order'   => $source->menu_order,
			),
			true
		);

		if ( is_wp_error( $new_id ) ) {
			wp_die( esc_html( $new_id->get_error_message() ) );
		}

		self::copyMeta( $source->ID, $new_id );
		self::copyTaxonomies( $source->ID, $new_id, $source->post_type );

		$thumbnail_id = get_post_thumbnail_id( $source->ID );
		if ( $thumbnail_id ) {
			// set_post_thumbnail() refuses SVG/WebP attachments without a generated
			// "thumbnail" image size, so write the meta directly instead.
			update_post_meta( $new_id, '_thumbnail_id', $thumbnail_id );
		}

		update_post_meta( $new_id, '_logika_duplicated_from', $source->ID );

		return $new_id;
	}

	private const SKIP_META_KEYS = array(
		'_edit_lock',
		'_edit_last',
		'_wp_old_slug',
		'_wp_old_date',
		'_thumbnail_id',
		'course_external_id',
		'camp_external_id',
		'branch_address_hash',
		'_logika_duplicated_from',
	);

	private static function copyMeta( int $source_id, int $target_id ): void {
		foreach ( get_post_meta( $source_id ) as $key => $values ) {
			if ( in_array( $key, self::SKIP_META_KEYS, true ) ) {
				continue;
			}
			foreach ( $values as $value ) {
				add_post_meta( $target_id, $key, maybe_unserialize( $value ) );
			}
		}
	}

	private static function copyTaxonomies( int $source_id, int $target_id, string $post_type ): void {
		foreach ( get_object_taxonomies( $post_type ) as $taxonomy ) {
			$term_ids = wp_get_object_terms( $source_id, $taxonomy, array( 'fields' => 'ids' ) );
			if ( is_wp_error( $term_ids ) || ! $term_ids ) {
				continue;
			}
			wp_set_object_terms( $target_id, $term_ids, $taxonomy );
		}
	}

	public static function renderNotice(): void {
		if ( ! isset( $_GET['logika_duplicated'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}
		echo '<div class="notice notice-success is-dismissible"><p>Створено копію. Замініть назву, тексти й картинки — і публікуйте.</p></div>';
	}
}
