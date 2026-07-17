<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$plugin = dirname(__DIR__) . '/wordpress/wp-content/plugins/logika-leads/logika-leads.php';
if ( ! class_exists( 'Logika_Leads_Service' ) && is_readable( $plugin ) ) {
	require_once $plugin;
}

function logika_leads_expect( bool $condition, string $message ): void {
	if ( ! $condition ) {
		fwrite( STDERR, $message . "\n" );
		exit( 1 );
	}
}

logika_leads_expect( class_exists( 'Logika_Leads_Form_Tokens' ), 'Short-lived form tokens are not implemented.' );

$token = Logika_Leads_Form_Tokens::issue( 'trial_lesson' );
logika_leads_expect( Logika_Leads_Form_Tokens::verify( $token, 'trial_lesson' ), 'A valid form token was rejected.' );
logika_leads_expect( ! Logika_Leads_Form_Tokens::verify( $token, 'consultation' ), 'A token was accepted for another form.' );

Logika_Leads_Schema::migrate();
$lead = Logika_Leads_Service::create(
	array(
		'form_id'              => 'trial_lesson',
		'name'                 => 'Тестова мама',
		'phone'                => '050 123 45 67',
		'consent_accepted'     => true,
		'consent_text_version' => 'test',
		'idempotency_key'      => wp_generate_uuid4(),
	)
);

logika_leads_expect( ! is_wp_error( $lead ) && 'pending' === $lead['status'], 'A locally saved lead must stay pending without CRM.' );

global $wpdb;
$stored = $wpdb->get_row( $wpdb->prepare( "SELECT phone, crm_status FROM {$wpdb->prefix}logika_leads WHERE lead_id = %s", $lead['lead_id'] ), ARRAY_A );
logika_leads_expect( '+380501234567' === $stored['phone'], 'Ukrainian phone number was not normalized to E.164.' );
logika_leads_expect( 'not_configured' === $stored['crm_status'], 'Null CRM must not pretend that the lead was sent.' );

$token_request = new WP_REST_Request( 'GET', '/logika/v1/forms/token' );
$token_request->set_param( 'form_id', 'trial_lesson' );
$token_response = rest_do_request( $token_request );
logika_leads_expect( 200 === $token_response->get_status() && ! empty( $token_response->get_data()['token'] ), 'The public form-token endpoint is not available.' );

echo "Reliable lead contract is available.\n";
