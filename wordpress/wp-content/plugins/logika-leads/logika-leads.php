<?php
/**
 * Plugin Name: Logika Leads
 * Description: Надійне локальне збереження заявок Logika.
 * Version: 0.1.0
 */

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/src/Schema.php';
require_once __DIR__ . '/src/Token.php';
require_once __DIR__ . '/src/Service.php';
require_once __DIR__ . '/src/Rest.php';
require_once __DIR__ . '/src/Crm.php';
require_once __DIR__ . '/src/DirectorEmail.php';
require_once __DIR__ . '/src/Admin.php';

register_activation_hook( __FILE__, 'logika_leads_activate' );
function logika_leads_activate(): void { Logika_Leads_Schema::migrate(); logika_leads_capabilities(); if ( ! wp_next_scheduled( 'logika_leads_retry' ) ) { wp_schedule_event( time() + 300, 'logika_five_minutes', 'logika_leads_retry' ); } }
function logika_leads_capabilities(): void { get_role( 'administrator' )?->add_cap( 'manage_logika_leads' ); get_role( 'administrator' )?->add_cap( 'view_logika_lead_pii' ); }
add_action( 'plugins_loaded', 'logika_leads_capabilities' );
add_filter( 'cron_schedules', static function( array $schedules ): array { $schedules['logika_five_minutes'] = array( 'interval' => 300, 'display' => 'Logika 5 minutes' ); return $schedules; } );
add_action( 'logika_leads_retry', static function(): void { global $wpdb; $rows = $wpdb->get_col( "SELECT lead_id FROM {$wpdb->prefix}logika_leads WHERE status = 'failed' AND next_retry_at <= UTC_TIMESTAMP() ORDER BY next_retry_at ASC LIMIT 20" ); foreach ( $rows as $lead_id ) { Logika_Leads_Service::deliver( (string) $lead_id ); } } );
add_action( 'plugins_loaded', array( 'Logika_Leads_Schema', 'maybe_migrate' ) );
add_action( 'rest_api_init', array( 'Logika_Leads_Rest', 'register' ) );
add_action( 'admin_menu', array( 'Logika_Leads_Admin', 'register' ) );
add_action( 'admin_post_logika_leads_export', array( 'Logika_Leads_Admin', 'export' ) );
