<?php

declare(strict_types=1);

final class Logika_Leads_Admin {
	public static function register(): void {
		add_menu_page( 'Заявки Logika', 'Заявки', 'manage_logika_leads', 'logika-leads', array( self::class, 'render' ), 'dashicons-feedback', 26 );
	}

	public static function recent(): array {
		global $wpdb;
		$table = $wpdb->prefix . 'logika_leads';

		return $wpdb->get_results( "SELECT lead_id, form_id, status, crm_status, name, phone, child_age, message, created_at FROM {$table} ORDER BY created_at DESC, id DESC LIMIT 100", ARRAY_A );
	}

	public static function render(): void {
		if ( ! current_user_can( 'manage_logika_leads' ) ) {
			wp_die( esc_html__( 'Недостатньо прав.', 'logika-leads' ) );
		}
		?>
		<div class="wrap"><h1><?php esc_html_e( 'Заявки Logika', 'logika-leads' ); ?></h1><p><a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=logika_leads_export' ), 'logika_leads_export' ) ); ?>">Експорт CSV</a></p><table class="widefat striped"><thead><tr><th>ID</th><th>Ім’я</th><th>Телефон</th><th>Форма</th><th>Повідомлення</th><th>Статус</th><th>CRM</th><th>Створено</th></tr></thead><tbody>
		<?php foreach ( self::recent() as $lead ) : ?><tr><td><?php echo esc_html( $lead['lead_id'] ); ?></td><td><?php echo esc_html( $lead['name'] ); ?></td><td><?php echo esc_html( preg_replace( '/(?<=.{5}).(?=.{3})/', '*', $lead['phone'] ) ); ?></td><td><?php echo esc_html( $lead['form_id'] ); ?></td><td><?php echo nl2br( esc_html( (string) ( $lead['message'] ?? '' ) ) ); ?></td><td><mark><?php echo esc_html( $lead['status'] ); ?></mark></td><td><?php echo esc_html( $lead['crm_status'] ); ?></td><td><?php echo esc_html( $lead['created_at'] ); ?></td></tr><?php endforeach; ?>
		</tbody></table></div>
		<?php
	}

	public static function export(): void { if ( ! current_user_can( 'view_logika_lead_pii' ) || ! check_admin_referer( 'logika_leads_export' ) ) { wp_die( 'Недостатньо прав.' ); } header( 'Content-Type: text/csv; charset=utf-8' ); header( 'Content-Disposition: attachment; filename=logika-leads.csv' ); $out = fopen( 'php://output', 'w' ); fputcsv( $out, array( 'ID', 'Ім’я', 'Телефон', 'Форма', 'Статус', 'CRM', 'Створено' ) ); foreach ( self::recent() as $lead ) { fputcsv( $out, array_values( $lead ) ); } exit; }
}
