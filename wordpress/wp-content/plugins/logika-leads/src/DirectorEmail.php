<?php

declare(strict_types=1);

final class Logika_Leads_Director_Email {
	public static function send( string $lead_id ): bool {
		global $wpdb;
		$table = $wpdb->prefix . 'logika_leads';
		$lead  = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE lead_id = %s", $lead_id ), ARRAY_A );

		if ( ! $lead || 'director_message' !== $lead['form_id'] ) {
			return true;
		}

		$sent = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}logika_lead_events WHERE lead_id = %s AND event_type = 'director.email.sent' LIMIT 1", $lead_id ) );
		if ( $sent ) {
			return true;
		}

		$recipient = sanitize_email( (string) getenv( 'LOGIKA_DIRECTOR_EMAIL' ) );
		$recipient = is_email( $recipient ) ? $recipient : 'kiev@logikaschool.com';
		$body      = implode(
			"\n",
			array(
				'Нова заявка «Написати директору»',
				'Ім’я: ' . $lead['name'],
				'Телефон: ' . $lead['phone'],
				'Відгук:',
				(string) ( $lead['message'] ?? '' ),
				'URL: ' . (string) ( $lead['source_url'] ?? '' ),
				'ID заявки: ' . $lead['lead_id'],
			)
		);
		$success = wp_mail( $recipient, 'Новий лист директору з сайту Logika', $body );
		$wpdb->insert(
			$wpdb->prefix . 'logika_lead_events',
			array(
				'lead_id'    => $lead_id,
				'event_type' => $success ? 'director.email.sent' : 'director.email.failed',
				'actor_type' => 'system',
				'message'    => $success ? $recipient : 'wp_mail_failed',
				'created_at' => current_time( 'mysql', true ),
			),
			array( '%s', '%s', '%s', '%s', '%s' )
		);

		return (bool) $success;
	}
}
