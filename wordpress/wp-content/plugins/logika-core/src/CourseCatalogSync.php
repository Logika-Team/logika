<?php

declare(strict_types=1);

namespace Logika\Core;

use WP_Post;

/**
 * Keeps the manually curated catalog relationships (`it_courses_age_categories` on the
 * /it-courses/ page, `english_courses_featured_courses` on /english-courses/) in sync with
 * published `course` posts, so a content manager only has to publish the course itself.
 *
 * Only ever adds/removes this course's own ID inside existing rows — never rewrites a whole
 * repeater — so hand-curated ordering and placeholder cards are left alone.
 */
final class CourseCatalogSync {
	private const IT_COURSES_SLUG      = 'it-courses';
	private const ENGLISH_COURSES_SLUG = 'english-courses';

	private const AGE_BUCKETS = array(
		'7-8'   => 'Курси для дітей 7–8 років',
		'9-11'  => 'Курси для дітей 9–11 років',
		'12-14' => 'Курси для дітей 12–14 років',
		'14-17' => 'Курси для дітей 14–17 років',
	);

	public static function register(): void {
		add_action( 'acf/save_post', array( self::class, 'onAcfSavePost' ), 25 );
		add_action( 'trashed_post', array( self::class, 'onRemoved' ) );
		add_action( 'before_delete_post', array( self::class, 'onRemoved' ) );
		add_action( 'untrashed_post', array( self::class, 'onAcfSavePost' ) );
	}

	public static function onAcfSavePost( mixed $post_id ): void {
		if ( ! is_numeric( $post_id ) || 'course' !== get_post_type( (int) $post_id ) ) {
			return;
		}
		self::sync( (int) $post_id );
	}

	public static function onRemoved( mixed $post_id ): void {
		if ( ! is_numeric( $post_id ) || 'course' !== get_post_type( (int) $post_id ) ) {
			return;
		}
		self::removeFromEverywhere( (int) $post_id );
	}

	public static function sync( int $course_id ): void {
		if ( 'publish' !== get_post_status( $course_id ) || false === get_field( 'course_show_in_catalog', $course_id ) ) {
			self::removeFromEverywhere( $course_id );
			return;
		}

		$directions = wp_get_post_terms( $course_id, 'course_direction', array( 'fields' => 'slugs' ) );
		$is_english = ! is_wp_error( $directions ) && in_array( 'english', $directions, true );

		if ( $is_english ) {
			self::removeFromItCourses( $course_id );
			self::addToEnglishCourses( $course_id );
			return;
		}

		self::removeFromEnglishCourses( $course_id );
		self::addToItCourses( $course_id );
	}

	private static function removeFromEverywhere( int $course_id ): void {
		self::removeFromItCourses( $course_id );
		self::removeFromEnglishCourses( $course_id );
	}

	private static function addToItCourses( int $course_id ): void {
		$page = get_page_by_path( self::IT_COURSES_SLUG );
		if ( ! $page instanceof WP_Post ) {
			return;
		}

		$bucket = self::ageBucket( (int) get_field( 'course_age_min', $course_id ), (int) get_field( 'course_age_max', $course_id ) );
		$rows   = array_values( array_filter( (array) get_field( 'it_courses_age_categories', $page->ID ), 'is_array' ) );

		$target_index = null;
		foreach ( $rows as $index => $row ) {
			if ( self::bucketForTitle( (string) ( $row['title'] ?? '' ) ) === $bucket ) {
				$target_index = $index;
			}
			$rows[ $index ]['courses'] = array_values(
				array_diff( array_map( 'intval', (array) ( $row['courses'] ?? array() ) ), array( $course_id ) )
			);
		}

		if ( null === $target_index ) {
			$rows[]        = array(
				'title'             => self::AGE_BUCKETS[ $bucket ],
				'courses'           => array(),
				'placeholder_cards' => array(),
			);
			$target_index = count( $rows ) - 1;
		}

		$rows[ $target_index ]['courses'][] = $course_id;
		$rows[ $target_index ]['courses']   = array_values( array_unique( $rows[ $target_index ]['courses'] ) );

		update_field( 'it_courses_age_categories', $rows, $page->ID );
	}

	private static function removeFromItCourses( int $course_id ): void {
		$page = get_page_by_path( self::IT_COURSES_SLUG );
		if ( ! $page instanceof WP_Post ) {
			return;
		}

		$rows = array_values( array_filter( (array) get_field( 'it_courses_age_categories', $page->ID ), 'is_array' ) );
		if ( ! $rows ) {
			return;
		}

		foreach ( $rows as $index => $row ) {
			$rows[ $index ]['courses'] = array_values(
				array_diff( array_map( 'intval', (array) ( $row['courses'] ?? array() ) ), array( $course_id ) )
			);
		}

		update_field( 'it_courses_age_categories', $rows, $page->ID );
	}

	private static function addToEnglishCourses( int $course_id ): void {
		$page = get_page_by_path( self::ENGLISH_COURSES_SLUG );
		if ( ! $page instanceof WP_Post ) {
			return;
		}

		$courses   = array_map( 'intval', (array) get_field( 'english_courses_featured_courses', $page->ID ) );
		$courses[] = $course_id;
		update_field( 'english_courses_featured_courses', array_values( array_unique( $courses ) ), $page->ID );
	}

	private static function removeFromEnglishCourses( int $course_id ): void {
		$page = get_page_by_path( self::ENGLISH_COURSES_SLUG );
		if ( ! $page instanceof WP_Post ) {
			return;
		}

		$courses = array_map( 'intval', (array) get_field( 'english_courses_featured_courses', $page->ID ) );
		update_field( 'english_courses_featured_courses', array_values( array_diff( $courses, array( $course_id ) ) ), $page->ID );
	}

	private static function ageBucket( int $min_age, int $max_age ): string {
		if ( $min_age >= 14 ) {
			return '14-17';
		}
		if ( $min_age <= 8 ) {
			return '7-8';
		}

		return $max_age <= 11 ? '9-11' : '12-14';
	}

	private static function bucketForTitle( string $title ): string {
		foreach ( array_keys( self::AGE_BUCKETS ) as $bucket ) {
			if ( str_contains( $title, str_replace( '-', '–', $bucket ) ) || str_contains( $title, $bucket ) ) {
				return $bucket;
			}
		}

		return '';
	}
}
