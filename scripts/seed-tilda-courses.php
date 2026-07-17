<?php

declare(strict_types=1);

/**
 * Import the standalone course pages exported from Tilda into the course CPT.
 *
 * @param array<int, array<string, mixed>> $courses
 * @return array{created: int, updated: int, ids: array<string, int>}
 */
function logika_seed_tilda_courses( array $courses, bool $sync_catalog = true ): array {
	if ( ! function_exists( 'update_field' ) ) {
		throw new RuntimeException( 'ACF Pro is required to import Tilda courses.' );
	}

	$result = array(
		'created' => 0,
		'updated' => 0,
		'ids'     => array(),
	);

	foreach ( $courses as $course ) {
		$existing = get_posts(
			array(
				'post_type'      => 'course',
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_key'       => 'course_external_id',
				'meta_value'     => (string) $course['external_id'],
			)
		);
		$course_id = (int) ( $existing[0] ?? 0 );

		if ( ! $course_id ) {
			$course_post = get_page_by_path( (string) $course['slug'], OBJECT, 'course' );
			$course_id   = $course_post ? (int) $course_post->ID : 0;
		}

		$post_data = array(
			'post_type'   => 'course',
			'post_status' => 'publish',
			'post_title'  => (string) $course['title'],
			'post_name'   => (string) $course['slug'],
		);

		if ( $course_id ) {
			$post_data['ID'] = $course_id;
			$updated_id      = wp_update_post( $post_data, true );
			if ( is_wp_error( $updated_id ) ) {
				throw new RuntimeException( $updated_id->get_error_message() );
			}
			$result['updated']++;
		} else {
			$course_id = wp_insert_post( $post_data, true );
			if ( is_wp_error( $course_id ) ) {
				throw new RuntimeException( $course_id->get_error_message() );
			}
			$course_id = (int) $course_id;
			$result['created']++;
		}

		foreach (
			array(
				'course_external_id'       => $course['external_id'],
				'course_age_min'           => $course['age_min'],
				'course_age_max'           => $course['age_max'],
				'course_short_description' => $course['short_description'],
				'course_card_description'  => $course['card_description'],
				'course_hero_label'        => $course['hero_label'],
				'course_hero_text'         => $course['hero_text'],
				'course_hero_additional_text' => $course['hero_additional_text'],
				'course_hero_cta_label'    => $course['hero_cta_label'],
				'course_program_anchor_label' => $course['program_anchor_label'],
				'course_program'            => $course['program'],
				'course_learn_items'        => $course['learn_items'],
				'course_process_title'      => 'Як проходять уроки?',
				'course_process_items'      => $course['process_items'],
				'course_portfolio_title'    => 'Приклади проєктів наших учнів',
				'course_cta_label'          => $course['cta_label'],
				'course_cta_submit_label'   => $course['hero_cta_label'],
			) as $field_name => $value
		) {
			update_field( $field_name, $value, $course_id );
		}

		$direction_id = logika_tilda_term_id( (string) $course['direction'], 'course_direction' );
		$format_id    = logika_tilda_term_id( (string) $course['format'], 'learning_format' );
		wp_set_object_terms( $course_id, array( $direction_id ), 'course_direction' );
		wp_set_object_terms( $course_id, array( $format_id ), 'learning_format' );

		$result['ids'][ (string) $course['external_id'] ] = $course_id;
	}

	if ( $sync_catalog ) {
		logika_sync_tilda_course_catalog( $courses, $result['ids'] );
	}

	return $result;
}

function logika_tilda_term_id( string $name, string $taxonomy ): int {
	$term = term_exists( $name, $taxonomy );
	if ( ! $term ) {
		$term = wp_insert_term( $name, $taxonomy );
	}
	if ( is_wp_error( $term ) ) {
		throw new RuntimeException( $term->get_error_message() );
	}

	return (int) ( is_array( $term ) ? $term['term_id'] : $term );
}

/**
 * @param array<int, array<string, mixed>> $courses
 * @param array<string, int>               $course_ids
 */
function logika_sync_tilda_course_catalog( array $courses, array $course_ids ): void {
	$catalog = get_page_by_path( 'it-courses', OBJECT, 'page' );
	if ( ! $catalog ) {
		return;
	}

	$bucket_titles = array(
		'7-8'   => 'Курси для дітей 7–8 років',
		'9-11'  => 'Курси для дітей 9–11 років',
		'12-14' => 'Курси для дітей 12–14 років',
		'14-17' => 'Курси для дітей 14–17 років',
	);
	$bucket_ids = array_fill_keys( array_keys( $bucket_titles ), array() );
	$source_titles = array();
	$imported_ids  = array_map( 'intval', $course_ids );

	foreach ( $courses as $course ) {
		$min_age = (int) $course['age_min'];
		$max_age = (int) $course['age_max'];
		$bucket  = $min_age <= 8 ? '7-8' : ( $min_age >= 14 ? '14-17' : ( $max_age <= 11 ? '9-11' : '12-14' ) );
		$bucket_ids[ $bucket ][] = (int) $course_ids[ (string) $course['external_id'] ];
		$source_titles[]        = (string) $course['title'];
	}

	$rows = (array) get_field( 'it_courses_age_categories', $catalog->ID );
	if ( ! $rows ) {
		$rows = array_map(
			static fn ( string $title ): array => array(
				'title'           => $title,
				'courses'         => array(),
				'placeholder_cards' => array(),
			),
			array_values( $bucket_titles )
		);
	}

	$seen_buckets = array();
	$normalized_rows = array();
	foreach ( $rows as $row ) {
		$bucket = logika_tilda_catalog_bucket( (string) ( $row['title'] ?? '' ) );
		if ( ! $bucket ) {
			$normalized_rows[] = $row;
			continue;
		}

		if ( isset( $seen_buckets[ $bucket ] ) ) {
			$index = $seen_buckets[ $bucket ];
			$normalized_rows[ $index ]['courses'] = array_values(
				array_unique(
					array_merge(
						$normalized_rows[ $index ]['courses'],
						array_map( 'intval', (array) ( $row['courses'] ?? array() ) )
					)
				)
			);
			continue;
		}

		$existing_ids = array_filter(
			array_map( 'intval', (array) ( $row['courses'] ?? array() ) ),
			static fn ( int $id ): bool => ! in_array( $id, $imported_ids, true ) && 'programming-start' !== get_post_field( 'post_name', $id )
		);
		$row['courses']         = array_values( array_unique( array_merge( $bucket_ids[ $bucket ], $existing_ids ) ) );
		$placeholders = $row['placeholder_cards'] ?? array();
		$placeholders = is_array( $placeholders ) ? $placeholders : array();
		$row['placeholder_cards'] = array_values(
			array_filter(
				$placeholders,
				static function ( $placeholder ) use ( $source_titles ): bool {
					if ( ! is_array( $placeholder ) ) {
						return false;
					}
					$title = (string) ( $placeholder['title'] ?? '' );
					if ( in_array( $title, $source_titles, true ) ) {
						return false;
					}
					return 'Графічний дизайн 2.0' !== $title;
				}
			)
		);
		$seen_buckets[ $bucket ] = count( $normalized_rows );
		$normalized_rows[] = $row;
	}
	$rows = $normalized_rows;

	foreach ( $bucket_titles as $bucket => $title ) {
		if ( isset( $seen_buckets[ $bucket ] ) ) {
			continue;
		}
		$rows[] = array(
			'title'            => $title,
			'courses'          => $bucket_ids[ $bucket ],
			'placeholder_cards' => array(),
		);
	}

	update_field( 'it_courses_age_categories', $rows, $catalog->ID );
}

function logika_tilda_catalog_bucket( string $title ): string {
	foreach ( array( '7-8', '9-11', '12-14', '14-17' ) as $bucket ) {
		if ( str_contains( $title, str_replace( '-', '–', $bucket ) ) || str_contains( $title, $bucket ) ) {
			return $bucket;
		}
	}

	return '';
}
