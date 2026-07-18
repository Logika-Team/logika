<?php

declare(strict_types=1);

namespace Logika\Core;

use WP_Post;

final class MediaCategories {
	private const LABELS = array( 'news' => 'Новини', 'articles' => 'Статті', 'offers' => 'Акції' );

	public static function register(): void { add_action( 'set_object_terms', array( self::class, 'enforceSingle' ), 10, 6 ); }
	public static function labels(): array { return self::LABELS; }
	public static function label( string $slug ): string { return self::LABELS[ $slug ] ?? self::LABELS['articles']; }

	public static function for( WP_Post|int $post ): string {
		$slugs = wp_get_post_terms( $post instanceof WP_Post ? $post->ID : $post, 'category', array( 'fields' => 'slugs' ) );
		foreach ( array_keys( self::LABELS ) as $slug ) {
			if ( in_array( $slug, $slugs, true ) ) { return $slug; }
		}

		return 'articles';
	}

	public static function enforceSingle( int $post_id, array $terms, array $term_taxonomy_ids, string $taxonomy, bool $append, array $old_term_taxonomy_ids ): void {
		$post = get_post( $post_id );
		if ( 'category' !== $taxonomy || ! $post instanceof WP_Post || 'post' !== $post->post_type ) { return; }
		$managed = array_filter( wp_get_post_terms( $post_id, 'category' ), static fn( $term ): bool => isset( self::LABELS[ $term->slug ] ) );
		if ( count( $managed ) < 2 ) { return; }
		$canonical = self::for( $post );
		foreach ( $managed as $term ) {
			if ( $canonical !== $term->slug ) { wp_remove_object_terms( $post_id, $term->term_id, 'category' ); }
		}
	}

	public static function migrateUncategorized( bool $dry_run = false ): void {
		$uncategorized = (int) get_option( 'default_category' );
		if ( $dry_run ) { return; }
		foreach ( self::LABELS as $slug => $label ) {
			if ( ! term_exists( $slug, 'category' ) ) { wp_insert_term( $label, 'category', array( 'slug' => $slug ) ); }
		}
		$articles = get_term_by( 'slug', 'articles', 'category' );
		if ( ! $articles ) { return; }
		foreach ( get_posts( array( 'post_type' => 'post', 'post_status' => 'any', 'posts_per_page' => -1, 'category' => $uncategorized ) ) as $post ) {
			$terms = array_map( 'intval', wp_get_post_terms( $post->ID, 'category', array( 'fields' => 'ids' ) ) );
			if ( array( $uncategorized ) === $terms ) { wp_set_object_terms( $post->ID, (int) $articles->term_id, 'category', false ); }
		}
		update_option( 'default_category', (int) $articles->term_id );
	}
}
