<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$theme      = dirname(__DIR__) . '/wordpress/wp-content/themes/logika-theme';
$plugin     = dirname(__DIR__) . '/wordpress/wp-content/plugins/logika-leads';
$index      = (string) file_get_contents( $theme . '/source-pages/index.php' );
$main       = (string) file_get_contents( $theme . '/assets/js/main.js' );
$form       = (string) file_get_contents( $theme . '/template-parts/forms/lead.php' );
$css        = (string) file_get_contents( $theme . '/assets/css/lead-modal.css' );
$rest       = (string) file_get_contents( $plugin . '/src/Rest.php' );
$crm        = (string) file_get_contents( $plugin . '/src/Crm.php' );
$errors     = array();

foreach ( array( 'href="#lead-form"', 'data-logika-form-id="gift_certificate"' ) as $marker ) {
	if ( ! str_contains( $index, $marker ) ) {
		$errors[] = "Gift certificate CTA is missing {$marker}.";
	}
}

foreach ( array( 'data-logika-age-field', 'dataset.logikaFormId', "'gift_certificate'" ) as $marker ) {
	if ( ! str_contains( $main . $form . $rest, $marker ) ) {
		$errors[] = "Gift certificate form contract is missing {$marker}.";
	}
}

if ( ! str_contains( $css, '[data-logika-age-field][hidden]' ) ) {
	$errors[] = 'Gift certificate age field is not hidden by the modal CSS.';
}

if ( ! str_contains( $crm, "'form_id' => \$lead['form_id']" ) ) {
	$errors[] = 'CRM payload does not include the form classification.';
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

$_SERVER['REMOTE_ADDR'] = '127.0.1.' . wp_rand( 1, 254 );
$request = new WP_REST_Request( 'POST', '/logika/v1/leads' );
$request->set_header( 'X-Logika-Form-Token', Logika_Leads_Form_Tokens::issue( 'gift_certificate' ) );
$request->set_body_params(
	array(
		'form_id'            => 'gift_certificate',
		'name'               => 'Тестовий Сертифікат',
		'phone'              => '+380931234569',
		'consent_accepted'   => true,
		'consent_text_version' => 'local-test',
		'idempotency_key'    => 'gift-certificate-test-' . wp_generate_uuid4(),
		'website'            => '',
	)
);
$response = rest_do_request( $request );
$lead_id  = (string) ( $response->get_data()['lead_id'] ?? '' );

if ( 201 !== $response->get_status() || '' === $lead_id ) {
	fwrite( STDERR, "Gift certificate REST request was not accepted.\n" );
	exit( 1 );
}

global $wpdb;
$lead = $wpdb->get_row( $wpdb->prepare( "SELECT form_id, child_age FROM {$wpdb->prefix}logika_leads WHERE lead_id = %s", $lead_id ), ARRAY_A );
if ( 'gift_certificate' !== ( $lead['form_id'] ?? '' ) || null !== $lead['child_age'] ) {
	fwrite( STDERR, "Gift certificate lead was not stored without child age.\n" );
	exit( 1 );
}

$payload = Logika_Leads_Crm_Payload_Mapper::map( array_merge( $lead, array( 'lead_id' => $lead_id, 'idempotency_key' => 'test', 'name' => 'Тестовий Сертифікат', 'phone' => '+380931234569', 'city_id' => null, 'course_id' => null, 'camp_id' => null ) ) );
if ( 'gift_certificate' !== ( $payload['context']['form_id'] ?? '' ) ) {
	fwrite( STDERR, "CRM payload does not classify the gift certificate lead.\n" );
	exit( 1 );
}

echo "Gift certificate lead contract is valid.\n";
