<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

if ( ! class_exists( 'Logika_Leads_Service' ) ) {
	fwrite( STDERR, "Logika leads service is not available.\n" );
	exit( 1 );
}

$payload = array(
	'form_id'            => 'trial_lesson',
	'name'               => 'Тестова Мама',
	'phone'              => '+380931234567',
	'child_age'          => 10,
	'consent_accepted'   => true,
	'consent_text_version' => 'local-test',
	'source_url'         => home_url( '/test-source/' ),
	'city_id'            => (int) get_page_by_path( 'test-city', OBJECT, 'city' )->ID,
	'course_id'          => (int) get_page_by_path( 'test-course', OBJECT, 'course' )->ID,
	'camp_id'            => (int) get_page_by_path( 'test-camp', OBJECT, 'camp' )->ID,
	'idempotency_key'    => 'local-lead-test-' . wp_generate_uuid4(),
);

$first  = Logika_Leads_Service::create( $payload );
$second = Logika_Leads_Service::create( $payload );

if ( is_wp_error( $first ) || is_wp_error( $second ) || $first['lead_id'] !== $second['lead_id'] || 'pending' !== $first['status'] ) {
	fwrite( STDERR, "Lead storage or idempotency failed.\n" );
	exit( 1 );
}

global $wpdb;
$events = $wpdb->prefix . 'logika_lead_events';
$event = $wpdb->get_var( $wpdb->prepare( "SELECT event_type FROM {$events} WHERE lead_id = %s ORDER BY id DESC LIMIT 1", $first['lead_id'] ) );
if ( 'lead.created' !== $event ) {
	fwrite( STDERR, "Lead creation event was not stored.\n" );
	exit( 1 );
}

$source_url = $wpdb->get_var( $wpdb->prepare( "SELECT source_url FROM {$wpdb->prefix}logika_leads WHERE lead_id = %s", $first['lead_id'] ) );
if ( home_url( '/test-source/' ) !== $source_url ) {
	fwrite( STDERR, "Lead source URL was not stored safely.\n" );
	exit( 1 );
}

$context = $wpdb->get_row( $wpdb->prepare( "SELECT city_id, course_id, camp_id FROM {$wpdb->prefix}logika_leads WHERE lead_id = %s", $first['lead_id'] ), ARRAY_A );
if ( (int) $payload['city_id'] !== (int) $context['city_id'] || (int) $payload['course_id'] !== (int) $context['course_id'] || (int) $payload['camp_id'] !== (int) $context['camp_id'] ) {
	fwrite( STDERR, "Lead city or course context was not stored safely.\n" );
	exit( 1 );
}

$stored_phone = $wpdb->get_var( $wpdb->prepare( "SELECT phone FROM {$wpdb->prefix}logika_leads WHERE lead_id = %s", $first['lead_id'] ) );
if ( '+380931234567' !== $stored_phone ) {
	fwrite( STDERR, "Lead phone is not stored as normalized E.164.\n" );
	exit( 1 );
}

$us_payload = $payload;
$us_payload['phone'] = '+14155552671';
$us_payload['idempotency_key'] = 'local-lead-us-test-' . wp_generate_uuid4();
$us_lead = Logika_Leads_Service::create( $us_payload );
if ( is_wp_error( $us_lead ) ) {
	fwrite( STDERR, "Lead service rejected a valid international phone.\n" );
	exit( 1 );
}

$invalid_payload = $payload;
$invalid_payload['phone'] = '12345';
$invalid_payload['idempotency_key'] = 'local-invalid-phone-test-' . wp_generate_uuid4();
$invalid_result = Logika_Leads_Service::create( $invalid_payload );
if ( ! is_wp_error( $invalid_result ) || 'invalid_phone' !== $invalid_result->get_error_code() ) {
	fwrite( STDERR, "Lead service did not reject an invalid phone.\n" );
	exit( 1 );
}

if ( ! ( Logika_Leads_Crm_Factory::provider() instanceof NullCrmProvider ) ) {
	fwrite( STDERR, "CRM adapter does not fail safely without configuration.\n" );
	exit( 1 );
}

if ( ! class_exists( 'Logika_Leads_Admin' ) || ! in_array( $first['lead_id'], wp_list_pluck( Logika_Leads_Admin::recent(), 'lead_id' ), true ) ) {
	fwrite( STDERR, "Lead admin list is not available.\n" );
	exit( 1 );
}

echo "Lead is stored once with an idempotency key.\n";

$_SERVER['REMOTE_ADDR'] = '127.0.0.' . wp_rand( 1, 254 );
$request = new WP_REST_Request( 'POST', '/logika/v1/leads' );
$request->set_header( 'X-Logika-Form-Token', Logika_Leads_Form_Tokens::issue( 'trial_lesson' ) );
$request->set_body_params(
	array(
		'form_id'              => 'trial_lesson',
		'name'                 => 'REST Тест',
		'phone'                => '+380931234568',
		'child_age'            => 11,
		'consent_accepted'     => true,
		'consent_text_version' => 'local-test',
		'idempotency_key'      => 'local-rest-lead-test-key',
		'website'              => '',
	)
);
$response = rest_do_request( $request );

if ( 201 !== $response->get_status() || empty( $response->get_data()['lead_id'] ) ) {
	fwrite( STDERR, "Lead REST endpoint did not accept a valid request.\n" );
	exit( 1 );
}

$invalid_request = new WP_REST_Request( 'POST', '/logika/v1/leads' );
$invalid_request->set_header( 'X-Logika-Form-Token', Logika_Leads_Form_Tokens::issue( 'trial_lesson' ) );
$invalid_request->set_body_params(
	array(
		'form_id'              => 'trial_lesson',
		'name'                 => 'REST Тест',
		'phone'                => 'not-a-phone',
		'child_age'            => 11,
		'consent_accepted'     => true,
		'consent_text_version' => 'local-test',
		'idempotency_key'      => 'local-rest-invalid-phone-test-key-' . wp_generate_uuid4(),
		'website'              => '',
	)
);
$invalid_response = rest_do_request( $invalid_request );
if ( 422 !== $invalid_response->get_status() || 'invalid_phone' !== ( $invalid_response->get_data()['code'] ?? '' ) ) {
	fwrite( STDERR, "Lead REST endpoint did not reject an invalid phone.\n" );
	exit( 1 );
}
