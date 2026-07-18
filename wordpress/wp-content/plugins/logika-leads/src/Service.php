<?php

declare(strict_types=1);

final class Logika_Leads_Service {
	public static function create( array $input ): array|WP_Error {
		global $wpdb;
		$name = sanitize_text_field( (string) ( $input['name'] ?? '' ) );
		$phone = self::phone( (string) ( $input['phone'] ?? '' ) );
		$message = sanitize_textarea_field( (string) ( $input['message'] ?? '' ) );
		$key = sanitize_text_field( (string) ( $input['idempotency_key'] ?? '' ) );
		$form_id = sanitize_key( (string) ( $input['form_id'] ?? '' ) );
		$source_url = self::source_url( (string) ( $input['source_url'] ?? '' ) );
		$city_id = self::context_id( $input['city_id'] ?? 0, 'city' );
		$course_id = self::context_id( $input['course_id'] ?? 0, 'course' );
		$camp_id = self::context_id( $input['camp_id'] ?? 0, 'camp' );

		if ( ! $phone ) {
			return new WP_Error( 'invalid_phone', 'Некоректний номер телефону.' );
		}

		if ( ! $name || ! $key || ! $form_id || empty( $input['consent_accepted'] ) || ( 'director_message' === $form_id && ! $message ) ) {
			return new WP_Error( 'invalid_lead', 'Некоректні дані заявки.' );
		}

		$table = $wpdb->prefix . 'logika_leads';
		$existing = $wpdb->get_row( $wpdb->prepare( "SELECT lead_id, status FROM {$table} WHERE idempotency_key = %s", $key ), ARRAY_A );
		if ( $existing ) {
			return $existing;
		}

		$lead_id = wp_generate_uuid4();
		$now = current_time( 'mysql', true );
		$inserted = $wpdb->insert( $table, array( 'lead_id' => $lead_id, 'idempotency_key' => $key, 'form_id' => $form_id, 'crm_provider' => 'null', 'crm_status' => 'not_configured', 'name' => $name, 'phone' => $phone, 'message' => $message ?: null, 'phone_hash' => hash( 'sha256', $phone ), 'child_age' => absint( $input['child_age'] ?? 0 ) ?: null, 'source_url' => $source_url, 'city_id' => $city_id, 'course_id' => $course_id, 'camp_id' => $camp_id, 'consent_accepted' => 1, 'consent_text_version' => sanitize_text_field( (string) ( $input['consent_text_version'] ?? '' ) ), 'payload_json' => wp_json_encode( array( 'form_id' => $form_id, 'message' => $message ) ), 'created_at' => $now, 'updated_at' => $now ), array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%s' ) );

		if ( false === $inserted ) {
			return new WP_Error( 'lead_storage_failed', 'Не вдалося зберегти заявку.' );
		}
		$wpdb->insert( $wpdb->prefix . 'logika_lead_events', array( 'lead_id' => $lead_id, 'event_type' => 'lead.created', 'actor_type' => 'public', 'created_at' => $now ), array( '%s', '%s', '%s', '%s' ) );

		return array( 'lead_id' => $lead_id, 'status' => 'pending' );
	}

	public static function deliver( string $lead_id ): CrmResult|WP_Error {
		global $wpdb;
		$table = $wpdb->prefix . 'logika_leads';
		$lead = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE lead_id = %s", $lead_id ), ARRAY_A );
		if ( ! $lead ) { return new WP_Error( 'lead_not_found' ); }
		$provider = Logika_Leads_Crm_Factory::provider();
		if ( ! $provider->isConfigured() ) { return $provider->createLead( $lead ); }
		$result = $provider->createLead( $lead );
		$now = current_time( 'mysql', true );
		$attempts = (int) $lead['crm_attempts'] + 1;
		$update = array( 'crm_provider' => $provider->name(), 'crm_status' => $result->status, 'crm_attempts' => $attempts, 'updated_at' => $now, 'last_error_code' => $result->error_code, 'last_error_message' => $result->accepted ? null : $result->message );
		if ( $result->accepted ) { $update += array( 'status' => 'sent', 'sent_at' => $now, 'next_retry_at' => null ); }
		else { $update += array( 'status' => 'failed', 'next_retry_at' => $result->retryable && $attempts <= 5 ? gmdate( 'Y-m-d H:i:s', time() + array( 300, 1800, 7200, 43200, 86400 )[ $attempts - 1 ] ) : null ); }
		$wpdb->update( $table, $update, array( 'lead_id' => $lead_id ) );
		$wpdb->insert( $wpdb->prefix . 'logika_lead_events', array( 'lead_id' => $lead_id, 'event_type' => $result->accepted ? 'crm.send.accepted' : 'crm.send.failed', 'actor_type' => 'system', 'message' => $result->error_code, 'created_at' => $now ) );
		return $result;
	}

	private static function source_url( string $url ): ?string {
		$url = esc_url_raw( $url );
		if ( ! $url || wp_parse_url( $url, PHP_URL_HOST ) !== wp_parse_url( home_url(), PHP_URL_HOST ) ) {
			return null;
		}

		return strtok( $url, '?' ) ?: null;
	}

	private static function phone( string $phone ): ?string {
		$phone = preg_replace( '/[\s().-]+/', '', trim( $phone ) );
		if ( is_string( $phone ) && preg_match( '/^0[0-9]{9}$/', $phone ) ) {
			$phone = '+38' . $phone;
		}
		if ( is_string( $phone ) && preg_match( '/^380[0-9]{9}$/', $phone ) ) {
			$phone = '+' . $phone;
		}

		return is_string( $phone ) && preg_match( '/^\+[1-9][0-9]{7,14}$/', $phone ) ? $phone : null;
	}

	private static function context_id( mixed $id, string $type ): ?int {
		$id = absint( $id );

		return $id && 'publish' === get_post_status( $id ) && $type === get_post_type( $id ) ? $id : null;
	}
}
