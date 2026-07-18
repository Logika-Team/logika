<?php

declare(strict_types=1);

final class Logika_Leads_Rest {
	private const FORM_IDS = array( 'trial_lesson', 'consultation', 'gift_certificate', 'director_message' );

	public static function register(): void {
		register_rest_route( 'logika/v1', '/forms/token', array( 'methods' => WP_REST_Server::READABLE, 'callback' => array( self::class, 'token' ), 'permission_callback' => '__return_true' ) );
		register_rest_route(
			'logika/v1',
			'/leads',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( self::class, 'create' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	public static function token( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$form_id = sanitize_key( (string) $request->get_param( 'form_id' ) );
		if ( ! in_array( $form_id, self::FORM_IDS, true ) || ! self::rate_limit( 'token', 20 ) ) {
			return new WP_Error( 'rate_limited', 'Спробуйте пізніше.', array( 'status' => 429 ) );
		}

		return new WP_REST_Response( array( 'form_id' => $form_id, 'token' => Logika_Leads_Form_Tokens::issue( $form_id ), 'expires_in' => 900 ), 200 );
	}

	public static function create( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$form_id = sanitize_key( (string) $request->get_param( 'form_id' ) );
		if ( ! self::same_origin( $request ) || ! Logika_Leads_Form_Tokens::verify( $request->get_header( 'x-logika-form-token' ), $form_id ) ) {
			return new WP_Error( 'rest_forbidden', 'Неможливо підтвердити форму.', array( 'status' => 403 ) );
		}

		if ( '' !== (string) $request->get_param( 'website' ) ) {
			return new WP_REST_Response( array( 'status' => 'accepted' ), 202 );
		}

		$ip = sanitize_text_field( (string) ( $_SERVER['REMOTE_ADDR'] ?? 'unknown' ) );
		if ( ! self::rate_limit( 'lead', 5 ) ) {
			return new WP_Error( 'rate_limited', 'Спробуйте пізніше.', array( 'status' => 429 ) );
		}

		if ( ! in_array( $form_id, self::FORM_IDS, true ) ) {
			return new WP_Error( 'invalid_form', 'Некоректна форма.', array( 'status' => 422 ) );
		}

		$result = Logika_Leads_Service::create(
			array(
				'form_id'              => $form_id,
				'name'                 => $request->get_param( 'name' ),
				'phone'                => $request->get_param( 'phone' ),
				'message'              => $request->get_param( 'message' ),
				'child_age'            => $request->get_param( 'child_age' ),
				'consent_accepted'     => rest_sanitize_boolean( $request->get_param( 'consent_accepted' ) ),
				'consent_text_version' => $request->get_param( 'consent_text_version' ),
				'idempotency_key'      => $request->get_param( 'idempotency_key' ),
				'source_url'           => $request->get_param( 'source_url' ),
				'city_id'              => $request->get_param( 'city_id' ),
				'course_id'            => $request->get_param( 'course_id' ),
				'camp_id'              => $request->get_param( 'camp_id' ),
			)
		);

		if ( is_wp_error( $result ) ) {
			return new WP_Error( $result->get_error_code() ?: 'invalid_lead', 'Перевірте дані форми.', array( 'status' => 422 ) );
		}
		Logika_Leads_Service::deliver( $result['lead_id'] );
		Logika_Leads_Director_Email::send( $result['lead_id'] );

		return new WP_REST_Response( $result, 201 );
	}

	private static function rate_limit( string $scope, int $limit ): bool {
		$fingerprint = (string) ( $_SERVER['REMOTE_ADDR'] ?? '' ) . '|' . (string) ( $_SERVER['HTTP_USER_AGENT'] ?? '' );
		$key = 'logika_lead_rate_' . $scope . '_' . hash( 'sha256', $fingerprint );
		$count = (int) get_transient( $key );
		if ( $count >= $limit ) {
			return false;
		}
		set_transient( $key, $count + 1, HOUR_IN_SECONDS );
		return true;
	}

	private static function same_origin( WP_REST_Request $request ): bool {
		$origin = (string) $request->get_header( 'origin' );
		return '' === $origin || wp_parse_url( $origin, PHP_URL_HOST ) === wp_parse_url( home_url(), PHP_URL_HOST );
	}
}
