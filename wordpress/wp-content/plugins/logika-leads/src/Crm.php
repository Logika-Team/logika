<?php

declare(strict_types=1);

final class CrmResult {
	public function __construct( public bool $accepted, public string $status, public ?string $lead_id = null, public ?string $error_code = null, public ?string $message = null, public bool $retryable = false ) {}
}

interface CrmProviderInterface {
	public function createLead( array $lead ): CrmResult;
	public function isConfigured(): bool;
	public function name(): string;
}

final class NullCrmProvider implements CrmProviderInterface {
	public function createLead( array $lead ): CrmResult { return new CrmResult( false, 'not_configured', null, 'crm_not_configured', 'CRM не налаштовано.' ); }
	public function isConfigured(): bool { return false; }
	public function name(): string { return 'null'; }
}

abstract class Logika_Leads_Http_Provider implements CrmProviderInterface {
	abstract protected function endpoint(): string;
	public function isConfigured(): bool { return str_starts_with( $this->endpoint(), 'https://' ); }
	public function createLead( array $lead ): CrmResult {
		if ( ! $this->isConfigured() ) { return new CrmResult( false, 'not_configured', null, 'crm_not_configured', 'CRM не налаштовано.' ); }
		$response = wp_remote_post( $this->endpoint(), array( 'timeout' => 5, 'headers' => array_filter( array( 'Content-Type' => 'application/json', 'Idempotency-Key' => (string) $lead['idempotency_key'], 'Authorization' => getenv( 'LOGIKA_CRM_TOKEN' ) ? 'Bearer ' . getenv( 'LOGIKA_CRM_TOKEN' ) : null ) ), 'body' => wp_json_encode( Logika_Leads_Crm_Payload_Mapper::map( $lead ) ) ) );
		if ( is_wp_error( $response ) ) { return new CrmResult( false, 'timeout', null, 'timeout', $response->get_error_message(), true ); }
		$code = wp_remote_retrieve_response_code( $response );
		return $code >= 200 && $code < 300 ? new CrmResult( true, 'accepted' ) : new CrmResult( false, 'error', null, 'http_error', 'CRM повернула помилку.', $code >= 500 );
	}
}
final class Bitrix24Provider extends Logika_Leads_Http_Provider { protected function endpoint(): string { return (string) getenv( 'LOGIKA_BITRIX24_WEBHOOK_URL' ); } public function name(): string { return 'bitrix24'; } }
final class HubSpotProvider extends Logika_Leads_Http_Provider { protected function endpoint(): string { return (string) getenv( 'LOGIKA_HUBSPOT_WEBHOOK_URL' ); } public function name(): string { return 'hubspot'; } }
final class KeyCrmProvider extends Logika_Leads_Http_Provider { protected function endpoint(): string { return (string) getenv( 'LOGIKA_KEYCRM_WEBHOOK_URL' ); } public function name(): string { return 'keycrm'; } }
final class Logika_Leads_Crm_Payload_Mapper { public static function map( array $lead ): array { return array( 'external_id' => $lead['lead_id'], 'idempotency_key' => $lead['idempotency_key'], 'person' => array( 'name' => $lead['name'], 'phone' => $lead['phone'], 'child_age' => $lead['child_age'] ), 'message' => $lead['message'] ?? null, 'context' => array( 'form_id' => $lead['form_id'], 'city_id' => $lead['city_id'], 'course_id' => $lead['course_id'], 'camp_id' => $lead['camp_id'] ) ); } }
final class Logika_Leads_Crm_Factory { public static function provider(): CrmProviderInterface { $provider = match ( getenv( 'LOGIKA_CRM_PROVIDER' ) ?: 'null' ) { 'bitrix24' => new Bitrix24Provider(), 'hubspot' => new HubSpotProvider(), 'keycrm' => new KeyCrmProvider(), default => new NullCrmProvider() }; return apply_filters( 'logika_leads_crm_provider', $provider ); } }
