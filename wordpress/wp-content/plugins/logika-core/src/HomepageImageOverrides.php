<?php

declare(strict_types=1);

namespace Logika\Core;

final class HomepageImageOverrides {
	private const REVIEW_PHOTO_FIELD = 'field_review_photo';
	private const REVIEW_ORIGINAL_PHOTO_META = 'review_original_photo';
	private const MANAGED_FIELDS = array( 'field_it_courses_hero_image', 'field_it_courses_catalog_card_image', 'field_it_courses_catalog_card_background', 'field_it_courses_cta_image', 'field_course_card_image', 'field_course_hero_image', 'field_course_hero_background_image', 'field_course_hero_character_image', 'field_course_learn_image', 'field_course_learn_background_image', 'field_course_learn_character_image', 'field_course_learn_item_image', 'field_course_process_background_image', 'field_course_process_item_image', 'field_course_project_image', 'field_course_project_student_image', 'field_course_cta_image', 'field_course_cta_character_image', 'field_course_cta_top_background_image', 'field_course_cta_bottom_background_image', 'field_course_faq_left_background_image', 'field_course_faq_right_background_image' );
	private const MIME_TYPES = array( 'image/jpeg', 'image/png', 'image/webp' );
	private const RELAXED_FIELDS = array( 'field_home_programming_courses_image_override', 'field_home_programming_courses_icon_override' );
	private const RATIO_TOLERANCE = 0.02;
	private const LEGACY_FIELDS = array(
		'field_home_hero_boy_image_override' => 'field_home_hero_boy_image',
		'field_home_hero_character_image_override' => 'field_home_hero_character_image',
		'field_home_trust_item_icon_override' => 'field_home_trust_item_icon',
		'field_home_english_level_image_override' => 'field_home_english_level_image',
		'field_home_programming_courses_image_override' => 'field_home_programming_courses_image',
		'field_home_programming_courses_icon_override' => 'field_home_programming_courses_icon',
		'field_home_transformation_before_image_override' => 'field_home_transformation_before_image',
		'field_home_transformation_after_image_override' => 'field_home_transformation_after_image',
		'field_home_onboarding_steps_image_override' => 'field_home_onboarding_steps_image',
		'field_home_certificates_image_override' => 'field_home_certificates_image',
		'field_home_partners_items_image_override' => 'field_home_partners_items_image',
	);
	private const SOURCE_PATHS = array(
		'field_home_hero_boy_image_override' => array( 'img/boy-character.svg' ),
		'field_home_hero_character_image_override' => array( 'img/logika-character.svg' ),
		'field_home_trust_item_icon_override' => array( 'img/banner-bar/icon-calendar-check.svg', 'img/banner-bar/icon-document-certificate.svg', 'img/banner-bar/icon-rating-star.svg', 'img/banner-bar/icon-outline_school.svg', 'img/banner-bar/icon-map-location.svg', 'img/banner-bar/icon-tabler-school.svg' ),
		'field_home_english_level_image_override' => array( 'img/english-courses/A0.svg', 'img/english-courses/A1.svg', 'img/english-courses/A2.svg', 'img/english-courses/B1.svg', 'img/english-courses/B2.svg' ),
		'field_home_programming_courses_image_override' => array( 'img/services/service1.png' ),
		'field_home_programming_courses_icon_override' => array( 'img/services/service1.svg' ),
		'field_home_transformation_before_image_override' => array( 'img/transformation/before.png' ),
		'field_home_transformation_after_image_override' => array( 'img/transformation/after.png' ),
		'field_home_onboarding_steps_image_override' => array( 'img/onbording/onbording1.svg', 'img/onbording/onbording2.svg', 'img/onbording/onbording3.svg' ),
		'field_home_certificates_image_override' => array( 'img/certificates/certificate.png' ),
		'field_home_partners_items_image_override' => array( 'img/Partners/think.png', 'img/Partners/1+1.png', 'img/Partners/Free.png', 'img/Partners/club.png', 'img/Partners/ed.png', 'img/Partners/basis.png', 'img/Partners/fond.png', 'img/Partners/mriya.png' ),
	);

	/** @var array<string, array<int, array{width: int, height: int}>> */
	private const PROFILES = array(
		'field_home_hero_boy_image_override' => array( array( 'width' => 440, 'height' => 225 ) ),
		'field_home_hero_character_image_override' => array( array( 'width' => 97, 'height' => 146 ) ),
		'field_home_trust_item_icon_override' => array( array( 'width' => 56, 'height' => 56 ) ),
		'field_home_english_level_image_override' => array(
			array( 'width' => 202, 'height' => 258 ), array( 'width' => 241, 'height' => 258 ), array( 'width' => 283, 'height' => 258 ), array( 'width' => 179, 'height' => 258 ), array( 'width' => 247, 'height' => 258 ),
		),
		'field_home_programming_courses_image_override' => array( array( 'width' => 1176, 'height' => 1022 ) ),
		'field_home_programming_courses_icon_override' => array( array( 'width' => 246, 'height' => 209 ) ),
		'field_home_transformation_before_image_override' => array( array( 'width' => 1420, 'height' => 869 ) ),
		'field_home_transformation_after_image_override' => array( array( 'width' => 1420, 'height' => 870 ) ),
		'field_home_onboarding_steps_image_override' => array(
			array( 'width' => 447, 'height' => 282 ), array( 'width' => 398, 'height' => 321 ), array( 'width' => 327, 'height' => 324 ),
		),
		'field_home_certificates_image_override' => array( array( 'width' => 1065, 'height' => 752 ) ),
		'field_home_partners_items_image_override' => array( array( 'width' => 458, 'height' => 270 ) ),
	);

	public static function register(): void {
		add_filter( 'acf/validate_value', array( self::class, 'validateValue' ), 10, 4 );
		add_action( 'admin_enqueue_scripts', array( self::class, 'enqueueAssets' ) );
		add_action( 'acf/save_post', array( self::class, 'captureReviewOriginalPhoto' ), 1 );
		add_action( 'acf/save_post', array( self::class, 'captureReviewOriginalPhoto' ), 20 );
	}

	public static function enqueueAssets(): void {
		$screen = get_current_screen();

		if ( ! $screen || ! function_exists( 'acf_get_field' ) ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_script(
			'logika-homepage-image-overrides',
			LOGIKA_CORE_URL . 'assets/js/homepage-image-overrides.js',
			array( 'acf-input' ),
			(string) filemtime( LOGIKA_CORE_PATH . 'assets/js/homepage-image-overrides.js' ),
			true
		);
		wp_localize_script(
			'logika-homepage-image-overrides',
			'logikaHomepageImageOverrides',
			array(
				'profiles' => self::PROFILES,
				'relaxedFields' => self::RELAXED_FIELDS,
				'managedFields' => self::MANAGED_FIELDS,
				'legacyFields' => self::LEGACY_FIELDS,
				'sources' => self::sources(),
				'originals' => self::reviewOriginals(),
			)
		);
	}

	public static function captureReviewOriginalPhoto( mixed $post_id ): void {
		$post_id = (int) $post_id;
		if ( $post_id <= 0 || 'review' !== get_post_type( $post_id ) || (int) get_post_meta( $post_id, self::REVIEW_ORIGINAL_PHOTO_META, true ) > 0 ) {
			return;
		}

		$photo_id = (int) get_post_meta( $post_id, 'review_photo', true );
		if ( $photo_id > 0 ) {
			update_post_meta( $post_id, self::REVIEW_ORIGINAL_PHOTO_META, $photo_id );
		}
	}

	/**
	 * @param true|string $valid
	 * @param array<string, mixed> $field
	 * @return true|string
	 */
	public static function validateValue( true|string $valid, mixed $value, array $field, string $input ): true|string {
		if ( true !== $valid || ! isset( self::PROFILES[ $field['key'] ?? '' ] ) || ! $value ) {
			return $valid;
		}

		$id = (int) $value;
		if ( $id <= 0 || ! in_array( get_post_mime_type( $id ), self::MIME_TYPES, true ) ) {
			return 'Оберіть зображення у форматі PNG, WebP або JPEG.';
		}
		if ( in_array( $field['key'], self::RELAXED_FIELDS, true ) ) {
			return true;
		}

		$metadata = wp_get_attachment_metadata( $id );
		$width    = (int) ( $metadata['width'] ?? 0 );
		$height   = (int) ( $metadata['height'] ?? 0 );
		$profile  = self::profile( (string) $field['key'], $input );

		if ( $width < $profile['width'] || $height < $profile['height'] ) {
			return self::error( $profile );
		}

		$expected_ratio = $profile['width'] / $profile['height'];
		$actual_ratio   = $width / $height;

		return abs( $actual_ratio / $expected_ratio - 1 ) <= self::RATIO_TOLERANCE ? true : self::error( $profile );
	}

	/** @return array<string, array<int, array{width: int, height: int}>> */
	public static function profiles(): array {
		return self::PROFILES;
	}

	/** @return array<string, array<int, string>> */
	public static function sources(): array {
		$base = trailingslashit( get_template_directory_uri() ) . 'assets/';

		return array_map(
			static fn( array $paths ): array => array_map( static fn( string $path ): string => $base . $path, $paths ),
			self::SOURCE_PATHS
		);
	}

	/** @return array<string, array<string, mixed>> */
	private static function reviewOriginals(): array {
		$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
		if ( ! $post_id || 'review' !== get_post_type( $post_id ) ) {
			return array();
		}

		self::captureReviewOriginalPhoto( $post_id );
		$photo = wp_prepare_attachment_for_js( (int) get_post_meta( $post_id, self::REVIEW_ORIGINAL_PHOTO_META, true ) );

		return is_array( $photo ) ? array( self::REVIEW_PHOTO_FIELD => $photo ) : array();
	}

	/** @return array{width: int, height: int} */
	private static function profile( string $field_key, string $input ): array {
		$profiles = self::PROFILES[ $field_key ];
		$index    = preg_match( '/\[row-(\d+)\]/', $input, $matches ) ? (int) $matches[1] : 0;

		return $profiles[ $index ] ?? $profiles[0];
	}

	/** @param array{width: int, height: int} $profile */
	private static function error( array $profile ): string {
		return sprintf(
			'Оберіть зображення щонайменше %1$d × %2$d px з такими самими пропорціями.',
			$profile['width'],
			$profile['height']
		);
	}
}
