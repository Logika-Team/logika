<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$modal = (string) file_get_contents( dirname(__DIR__) . '/wordpress/wp-content/themes/logika-theme/template-parts/components/lead-modal.php' );
$main  = (string) file_get_contents( dirname(__DIR__) . '/wordpress/wp-content/themes/logika-theme/assets/js/main.js' );
$css   = (string) file_get_contents( dirname(__DIR__) . '/wordpress/wp-content/themes/logika-theme/assets/css/lead-modal.css' );

foreach ( array( 'data-logika-modal-title', 'modal.is-director-message .modal__lesson-image' ) as $marker ) {
	if ( ! str_contains( $modal . $main . $css, $marker ) ) {
		fwrite( STDERR, "Director modal text mode is missing {$marker}.\n" );
		exit( 1 );
	}
}

$captured_mail = null;
$mail_should_fail = false;
add_filter(
	'pre_wp_mail',
	static function ( $pre, array $atts ) use ( &$captured_mail, &$mail_should_fail ) {
		$captured_mail = $atts;

		return ! $mail_should_fail;
	},
	10,
	2
);

$_SERVER['REMOTE_ADDR'] = '127.0.2.' . wp_rand( 1, 254 );
$request = new WP_REST_Request( 'POST', '/logika/v1/leads' );
$request->set_header( 'X-Logika-Form-Token', Logika_Leads_Form_Tokens::issue( 'director_message' ) );
$request->set_body_params(
	array(
		'form_id'              => 'director_message',
		'name'                 => 'Тестовий <b>Відгук</b>',
		'phone'                => '+380931234569',
		'message'              => "Перший рядок\n<script>alert('xss')</script>",
		'consent_accepted'    => true,
		'consent_text_version' => 'local-test',
		'idempotency_key'      => 'director-message-test-' . wp_generate_uuid4(),
		'source_url'           => home_url( '/test-director/' ),
		'website'              => '',
	)
);

$response = rest_do_request( $request );
$data     = $response->get_data();
$lead_id  = (string) ( $data['lead_id'] ?? '' );

if ( 201 !== $response->get_status() || '' === $lead_id ) {
	fwrite( STDERR, "Director feedback REST request was not accepted.\n" );
	exit( 1 );
}

global $wpdb;
$lead = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}logika_leads WHERE lead_id = %s", $lead_id ), ARRAY_A );

if ( 'director_message' !== ( $lead['form_id'] ?? '' ) || 'Перший рядок' !== ( $lead['message'] ?? '' ) ) {
	fwrite( STDERR, "Director feedback message was not sanitized and stored.\n" );
	exit( 1 );
}

$payload = Logika_Leads_Crm_Payload_Mapper::map( $lead );
if ( 'director_message' !== ( $payload['context']['form_id'] ?? '' ) || $lead['message'] !== ( $payload['message'] ?? '' ) ) {
	fwrite( STDERR, "CRM payload does not include the director feedback.\n" );
	exit( 1 );
}

if ( ! is_array( $captured_mail ) || 'kiev@logikaschool.com' !== ( $captured_mail['to'] ?? '' ) || ! str_contains( (string) $captured_mail['message'], $lead['message'] ) ) {
	fwrite( STDERR, "Director email was not prepared with the stored lead.\n" );
	exit( 1 );
}

$mail_should_fail = true;
$failed_mail_request = new WP_REST_Request( 'POST', '/logika/v1/leads' );
$failed_mail_request->set_header( 'X-Logika-Form-Token', Logika_Leads_Form_Tokens::issue( 'director_message' ) );
$failed_mail_request->set_body_params(
	array(
		'form_id'              => 'director_message',
		'name'                 => 'Заявка без email',
		'phone'                => '+380931234569',
		'message'              => 'Збережіть мене навіть якщо пошта недоступна.',
		'consent_accepted'     => true,
		'consent_text_version' => 'local-test',
		'idempotency_key'      => 'director-message-mail-failure-' . wp_generate_uuid4(),
		'website'              => '',
	)
);

$failed_mail_response = rest_do_request( $failed_mail_request );
if ( 201 !== $failed_mail_response->get_status() || 'director.email.failed' !== $wpdb->get_var( $wpdb->prepare( "SELECT event_type FROM {$wpdb->prefix}logika_lead_events WHERE lead_id = %s ORDER BY id DESC LIMIT 1", (string) ( $failed_mail_response->get_data()['lead_id'] ?? '' ) ) ) ) {
	fwrite( STDERR, "Email failure incorrectly rejected the stored lead.\n" );
	exit( 1 );
}

$invalid = new WP_REST_Request( 'POST', '/logika/v1/leads' );
$invalid->set_header( 'X-Logika-Form-Token', Logika_Leads_Form_Tokens::issue( 'director_message' ) );
$invalid->set_body_params(
	array(
		'form_id'            => 'director_message',
		'name'               => 'Без повідомлення',
		'phone'              => '+380931234569',
		'consent_accepted'   => true,
		'idempotency_key'    => 'director-message-invalid-' . wp_generate_uuid4(),
		'website'            => '',
	)
);

if ( 422 !== rest_do_request( $invalid )->get_status() ) {
	fwrite( STDERR, "Empty director feedback was accepted.\n" );
	exit( 1 );
}

echo "Director feedback lead contract is valid.\n";
