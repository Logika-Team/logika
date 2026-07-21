<?php

declare(strict_types=1);

namespace Logika\Core;

use WP_Post;

/**
 * Keeps `camp_archive_formats` on the /camps/ page in sync with published, active `camp`
 * posts so a content manager only has to publish the camp itself to get it into the
 * "Оберіть зміну" modal — only ever adds/removes this camp's own ID, never rewrites the row.
 */
final class CampArchiveSync {
	private const CAMPS_SLUG = 'camps';

	public static function register(): void {
		add_action( 'acf/save_post', array( self::class, 'onAcfSavePost' ), 25 );
		add_action( 'trashed_post', array( self::class, 'onRemoved' ) );
		add_action( 'before_delete_post', array( self::class, 'onRemoved' ) );
		add_action( 'untrashed_post', array( self::class, 'onAcfSavePost' ) );
	}

	public static function onAcfSavePost( mixed $post_id ): void {
		if ( ! is_numeric( $post_id ) || 'camp' !== get_post_type( (int) $post_id ) ) {
			return;
		}
		self::sync( (int) $post_id );
	}

	public static function onRemoved( mixed $post_id ): void {
		if ( ! is_numeric( $post_id ) || 'camp' !== get_post_type( (int) $post_id ) ) {
			return;
		}
		self::remove( (int) $post_id );
	}

	public static function sync( int $camp_id ): void {
		$is_active = (bool) get_field( 'camp_is_active', $camp_id );
		if ( 'publish' !== get_post_status( $camp_id ) || ! $is_active ) {
			self::remove( $camp_id );
			return;
		}

		$page = get_page_by_path( self::CAMPS_SLUG );
		if ( ! $page instanceof WP_Post ) {
			return;
		}

		$camps   = array_map( 'intval', (array) get_field( 'camp_archive_formats', $page->ID ) );
		$camps[] = $camp_id;
		update_field( 'camp_archive_formats', array_values( array_unique( $camps ) ), $page->ID );
	}

	private static function remove( int $camp_id ): void {
		$page = get_page_by_path( self::CAMPS_SLUG );
		if ( ! $page instanceof WP_Post ) {
			return;
		}

		$camps = array_map( 'intval', (array) get_field( 'camp_archive_formats', $page->ID ) );
		update_field( 'camp_archive_formats', array_values( array_diff( $camps, array( $camp_id ) ) ), $page->ID );
	}
}
