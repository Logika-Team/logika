<?php

declare(strict_types=1);

final class Logika_Leads_Schema {
	private const VERSION = '7';

	public static function maybe_migrate(): void {
		if ( self::VERSION !== get_option( 'logika_leads_db_version' ) ) {
			self::migrate();
		}
	}

	public static function migrate(): void {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$table = $wpdb->prefix . 'logika_leads';
		$sql = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			lead_id varchar(64) NOT NULL,
			idempotency_key varchar(191) NOT NULL,
			form_id varchar(80) NOT NULL,
			status varchar(32) NOT NULL DEFAULT 'pending',
			crm_provider varchar(32) NOT NULL DEFAULT 'null',
			crm_status varchar(32) NOT NULL DEFAULT 'not_configured',
			crm_lead_id varchar(191) NULL,
			crm_attempts int(10) unsigned NOT NULL DEFAULT 0,
			last_error_code varchar(80) NULL,
			last_error_message text NULL,
			next_retry_at datetime NULL,
			sync_lock_until datetime NULL,
			name varchar(160) NOT NULL,
			phone varchar(40) NOT NULL,
			message text NULL,
			phone_hash char(64) NOT NULL,
			child_age tinyint(3) unsigned NULL,
			source_url text NULL,
			city_id bigint(20) unsigned NULL,
			course_id bigint(20) unsigned NULL,
			camp_id bigint(20) unsigned NULL,
			city_slug varchar(200) NULL,
			course_slug varchar(200) NULL,
			referrer text NULL,
			utm_source varchar(191) NULL,
			utm_medium varchar(191) NULL,
			utm_campaign varchar(191) NULL,
			utm_term varchar(191) NULL,
			utm_content varchar(191) NULL,
			consent_accepted tinyint(1) NOT NULL DEFAULT 0,
			consent_text_version varchar(80) NULL,
			payload_json longtext NULL,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			sent_at datetime NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY lead_id (lead_id),
			UNIQUE KEY idempotency_key (idempotency_key),
			KEY status_created (status,created_at),
			KEY phone_hash_created (phone_hash,created_at),
			KEY retry_due (status,next_retry_at)
			,KEY city_created (city_id,created_at)
			,KEY course_created (course_id,created_at)
			,KEY camp_created (camp_id,created_at)
		) {$wpdb->get_charset_collate()};";
		dbDelta( $sql );
		$attempts = $wpdb->prefix . 'logika_lead_attempts';
		$events = $wpdb->prefix . 'logika_lead_events';
		dbDelta( "CREATE TABLE {$attempts} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			lead_id varchar(64) NOT NULL,
			attempt_no int(10) unsigned NOT NULL,
			status varchar(32) NOT NULL,
			http_status smallint(5) unsigned NULL,
			error_code varchar(80) NULL,
			error_message text NULL,
			request_id varchar(80) NULL,
			started_at datetime NOT NULL,
			finished_at datetime NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY lead_attempt (lead_id,attempt_no),
			KEY status_started (status,started_at)
		) {$wpdb->get_charset_collate()};" );
		dbDelta( "CREATE TABLE {$events} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			lead_id varchar(64) NOT NULL,
			event_type varchar(80) NOT NULL,
			actor_type varchar(32) NOT NULL,
			actor_id bigint(20) unsigned NULL,
			message text NULL,
			meta_json longtext NULL,
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY lead_created (lead_id,created_at),
			KEY event_created (event_type,created_at)
		) {$wpdb->get_charset_collate()};" );
		update_option( 'logika_leads_db_version', self::VERSION, false );
	}
}
