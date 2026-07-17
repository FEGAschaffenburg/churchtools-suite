<?php
/**
 * Settings Subtab: Synchronization
 *
 * @package ChurchTools_Suite
 * @since   0.7.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Form processing
if ( isset( $_POST['cts_save_sync'] ) && check_admin_referer( 'cts_settings' ) ) {
	update_option( 'churchtools_suite_sync_days_past', absint( $_POST['sync_days_past'] ?? 7 ) );
	update_option( 'churchtools_suite_sync_days_future', absint( $_POST['sync_days_future'] ?? 90 ) );
	
	$auto_sync_enabled = isset( $_POST['auto_sync_enabled'] ) ? 1 : 0;
	update_option( 'churchtools_suite_auto_sync_enabled', $auto_sync_enabled );
	update_option( 'churchtools_suite_auto_sync_interval', sanitize_text_field( $_POST['auto_sync_interval'] ?? 'hourly' ) );

	// Allow addons to save their own sync-related settings in this existing tab.
	do_action( 'cts_sync_settings_save', $_POST );
	
	require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-cron.php';
	ChurchTools_Suite_Cron::update_sync_schedule();
	
	echo '<div class="cts-notice cts-notice-success"><p>' . esc_html__( 'Sync-Einstellungen gespeichert.', 'churchtools-suite' ) . '</p></div>';
}

$sync_days_past = get_option( 'churchtools_suite_sync_days_past', 7 );
$sync_days_future = get_option( 'churchtools_suite_sync_days_future', 90 );
$auto_sync_enabled = get_option( 'churchtools_suite_auto_sync_enabled', 0 );
$auto_sync_interval = get_option( 'churchtools_suite_auto_sync_interval', 'daily' ); // v0.10.2.0: Default 'daily'
$last_auto_sync = get_option( 'churchtools_suite_last_auto_sync', '' );

$registered_modules = class_exists( 'ChurchTools_Suite_Sync_Modules' ) ? ChurchTools_Suite_Sync_Modules::get_registered_modules() : [];
$module_statuses = [];
if ( ! empty( $registered_modules ) && class_exists( 'ChurchTools_Suite_Sync_Modules' ) ) {
	foreach ( $registered_modules as $module_id => $module_config ) {
		$module_statuses[ $module_id ] = ChurchTools_Suite_Sync_Modules::get_module_status( (string) $module_id );
	}
}
?>

<form method="post" action="" class="cts-form">
	<?php wp_nonce_field( 'cts_settings' ); ?>

	<div class="cts-card" style="margin-bottom: 20px;">
		<div class="cts-card-header">
			<span class="cts-card-icon">🧩</span>
			<h3><?php esc_html_e( 'Modulstatus', 'churchtools-suite' ); ?></h3>
		</div>
		<div class="cts-card-body">
			<?php if ( empty( $registered_modules ) ) : ?>
				<div class="notice notice-info inline" style="margin: 0;">
					<p><?php esc_html_e( 'Aktuell sind keine Sync-Module registriert. Aktiviere ein Addon (z. B. Posts Sync), damit hier Modulstatus angezeigt wird.', 'churchtools-suite' ); ?></p>
				</div>
			<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Modul', 'churchtools-suite' ); ?></th>
						<th><?php esc_html_e( 'Status', 'churchtools-suite' ); ?></th>
						<th><?php esc_html_e( 'Letzter Source-Sync', 'churchtools-suite' ); ?></th>
						<th><?php esc_html_e( 'Letzter Daten-Sync', 'churchtools-suite' ); ?></th>
						<th><?php esc_html_e( 'Letzte Meldung', 'churchtools-suite' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $registered_modules as $module_id => $module_config ) : ?>
						<?php
						$module_status = isset( $module_statuses[ $module_id ] ) && is_array( $module_statuses[ $module_id ] ) ? $module_statuses[ $module_id ] : [];
						$module_label = isset( $module_config['label'] ) ? (string) $module_config['label'] : (string) $module_id;
						$state = isset( $module_status['state'] ) ? (string) $module_status['state'] : 'idle';
						$state_label_map = [
							'idle' => __( 'Bereit', 'churchtools-suite' ),
							'running' => __( 'Läuft', 'churchtools-suite' ),
							'ok' => __( 'OK', 'churchtools-suite' ),
							'error' => __( 'Fehler', 'churchtools-suite' ),
							'disabled' => __( 'Deaktiviert', 'churchtools-suite' ),
						];
						$state_label = isset( $state_label_map[ $state ] ) ? (string) $state_label_map[ $state ] : $state;
						$last_source_sync_at = isset( $module_status['last_source_sync_at'] ) ? (string) $module_status['last_source_sync_at'] : '';
						$last_data_sync_at = isset( $module_status['last_data_sync_at'] ) ? (string) $module_status['last_data_sync_at'] : '';
						$last_result = isset( $module_status['last_result'] ) && is_array( $module_status['last_result'] ) ? $module_status['last_result'] : [];
						$last_message = isset( $last_result['message'] ) ? (string) $last_result['message'] : '';

						if ( $last_source_sync_at !== '' ) {
							$last_source_sync_at = get_date_from_gmt( $last_source_sync_at, get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );
						}
						if ( $last_data_sync_at !== '' ) {
							$last_data_sync_at = get_date_from_gmt( $last_data_sync_at, get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );
						}
						?>
						<tr>
							<td><strong><?php echo esc_html( $module_label ); ?></strong> <code><?php echo esc_html( (string) $module_id ); ?></code></td>
							<td><?php echo esc_html( $state_label ); ?></td>
							<td><?php echo esc_html( $last_source_sync_at !== '' ? $last_source_sync_at : '—' ); ?></td>
							<td><?php echo esc_html( $last_data_sync_at !== '' ? $last_data_sync_at : '—' ); ?></td>
							<td><?php echo esc_html( $last_message !== '' ? $last_message : '—' ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<?php endif; ?>
		</div>
	</div>
	
	<div class="cts-card">
		<div class="cts-card-header">
			<span class="cts-card-icon">⏱️</span>
			<h3><?php esc_html_e( 'Zeitraum', 'churchtools-suite' ); ?></h3>
		</div>
		<div class="cts-card-body">
		
		<table class="cts-form-table">
			<tr>
				<th scope="row">
					<label for="sync_days_past"><?php esc_html_e( 'Vergangene Tage', 'churchtools-suite' ); ?></label>
				</th>
				<td>
					<input type="number" 
						   id="sync_days_past" 
						   name="sync_days_past" 
						   value="<?php echo esc_attr( $sync_days_past ); ?>" 
						   class="cts-form-input"
						   min="0"
						   max="365"
						   style="max-width: 120px;">
					<span class="cts-form-description"><?php esc_html_e( 'Wie viele Tage in der Vergangenheit sollen synchronisiert werden? (Standard: 7)', 'churchtools-suite' ); ?></span>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="sync_days_future"><?php esc_html_e( 'Zukünftige Tage', 'churchtools-suite' ); ?></label>
				</th>
				<td>
					<input type="number" 
						   id="sync_days_future" 
						   name="sync_days_future" 
						   value="<?php echo esc_attr( $sync_days_future ); ?>" 
						   class="cts-form-input"
						   min="1"
						   max="730"
						   style="max-width: 120px;">
					<span class="cts-form-description"><?php esc_html_e( 'Wie viele Tage in der Zukunft sollen synchronisiert werden? (Standard: 90)', 'churchtools-suite' ); ?></span>
				</td>
			</tr>
		</table>
		
		<?php
		$from_date = date_i18n( get_option( 'date_format' ), current_time( 'timestamp' ) - absint( $sync_days_past ) * DAY_IN_SECONDS );
		$to_date = date_i18n( get_option( 'date_format' ), current_time( 'timestamp' ) + absint( $sync_days_future ) * DAY_IN_SECONDS );
		?>
		
		<div class="cts-info-box">
			<p>
				<strong>📅 Berechneter Zeitraum:</strong>
			</p>
			<p style="margin: 0; font-family: monospace; font-size: 14px;">
				<strong>Von:</strong> <?php echo esc_html( $from_date ); ?> 
				<span style="color: #646970; margin: 0 8px;">|</span>
				<strong>Bis:</strong> <?php echo esc_html( $to_date ); ?>
			</p>
			<p style="margin: 8px 0 0 0; font-size: 12px; color: #646970;">
				<?php esc_html_e( 'Dieser Zeitraum wird bei der nächsten Synchronisation verwendet.', 'churchtools-suite' ); ?>
			</p>
		</div>
	</div>
	
	<div class="cts-card cts-mt-20">
		<h3><?php esc_html_e( 'Automatische Synchronisation', 'churchtools-suite' ); ?></h3>
		
		<table class="cts-form-table">
			<tr>
				<th scope="row">
					<label for="auto_sync_enabled"><?php esc_html_e( 'Auto-Sync aktivieren', 'churchtools-suite' ); ?></label>
				</th>
				<td>
					<label class="cts-toggle">
						<input type="checkbox" 
							   id="auto_sync_enabled" 
							   name="auto_sync_enabled" 
							   value="1" 
							   <?php checked( $auto_sync_enabled, 1 ); ?>>
						<span class="cts-toggle-slider"></span>
					</label>
					<span class="cts-form-description"><?php esc_html_e( 'Termine automatisch im Hintergrund synchronisieren', 'churchtools-suite' ); ?></span>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="auto_sync_interval"><?php esc_html_e( 'Synchronisations-Intervall', 'churchtools-suite' ); ?></label>
				</th>
				<td>
					<select id="auto_sync_interval" 
							name="auto_sync_interval" 
							class="cts-form-input"
							style="max-width: 250px;"
							<?php disabled( ! $auto_sync_enabled ); ?>>
						<option value="daily" <?php selected( $auto_sync_interval, 'daily' ); ?>><?php esc_html_e( 'Täglich (empfohlen)', 'churchtools-suite' ); ?></option>
						<option value="cts_2days" <?php selected( $auto_sync_interval, 'cts_2days' ); ?>><?php esc_html_e( 'Alle 2 Tage', 'churchtools-suite' ); ?></option>
						<option value="cts_3days" <?php selected( $auto_sync_interval, 'cts_3days' ); ?>><?php esc_html_e( 'Alle 3 Tage', 'churchtools-suite' ); ?></option>
						<option value="cts_weekly" <?php selected( $auto_sync_interval, 'cts_weekly' ); ?>><?php esc_html_e( 'Wöchentlich', 'churchtools-suite' ); ?></option>
						<option value="cts_2weeks" <?php selected( $auto_sync_interval, 'cts_2weeks' ); ?>><?php esc_html_e( 'Alle 2 Wochen', 'churchtools-suite' ); ?></option>
						<option value="cts_monthly" <?php selected( $auto_sync_interval, 'cts_monthly' ); ?>><?php esc_html_e( 'Monatlich', 'churchtools-suite' ); ?></option>
					</select>
					<span class="cts-form-description"><?php esc_html_e( 'Empfohlen: Täglich oder alle 2-3 Tage für regelmäßige Termine', 'churchtools-suite' ); ?></span>
				</td>
			</tr>
			<?php if ( ! empty( $last_auto_sync ) ) : ?>
			<tr>
				<th scope="row">
					<?php esc_html_e( 'Letzte Auto-Sync', 'churchtools-suite' ); ?>
				</th>
				<td>
					<span style="color: #50575e; font-weight: 500;">
						<?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $last_auto_sync ) ) ); ?>
					</span>
					<span class="cts-form-description"><?php esc_html_e( 'Zeitpunkt der letzten automatischen Synchronisation', 'churchtools-suite' ); ?></span>
				</td>
			</tr>
			<?php endif; ?>
		</table>
		
		<?php if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) : ?>
		<div style="margin-top: 20px; padding: 16px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
			<h4 style="margin: 0 0 10px; color: #856404; font-size: 14px;">
				⚠️ <?php esc_html_e( 'WP-Cron ist deaktiviert', 'churchtools-suite' ); ?>
			</h4>
			<p style="margin: 0; color: #856404; font-size: 13px; line-height: 1.6;">
				<?php esc_html_e( 'Die automatische Synchronisation funktioniert nur mit einem System-Cron.', 'churchtools-suite' ); ?>
			</p>
		</div>
		<?php else : ?>
		<div class="cts-info" style="margin-top: 15px; padding: 12px; background: #f0f6fc; border-left: 4px solid #0073aa;">
			<p style="margin: 0;">
				<strong>✅ WP-Cron aktiv:</strong> 
				<?php esc_html_e( 'Die automatische Synchronisation ist einsatzbereit.', 'churchtools-suite' ); ?>
			</p>
		</div>
		<?php endif; ?>
	</div>

		<?php do_action( 'cts_sync_settings_render' ); ?>

	<div class="cts-submit">
		<button type="submit" name="cts_save_sync" class="cts-button cts-button-primary">
			<span>💾</span>
			<?php esc_html_e( 'Speichern', 'churchtools-suite' ); ?>
		</button>
	</div>
</form>
