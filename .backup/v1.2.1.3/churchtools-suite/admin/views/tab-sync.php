<?php
/**
 * Sync Tab
 *
 * @package ChurchTools_Suite
 * @since   0.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load repositories
require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-repository-base.php';
require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-calendars-repository.php';

$calendars_repo = new ChurchTools_Suite_Calendars_Repository();
$calendars = $calendars_repo->get_all();
$selected_count = $calendars_repo->count_selected();
$calendars_last_sync = get_option('churchtools_suite_calendars_last_sync', null);
$events_last_sync = get_option('churchtools_suite_events_last_sync', null);
$days_past = get_option('churchtools_suite_sync_days_past', 7);
$days_future = get_option('churchtools_suite_sync_days_future', 90);
$auto_sync_enabled = get_option('churchtools_suite_auto_sync_enabled', 0);

$registered_modules = class_exists( 'ChurchTools_Suite_Sync_Modules' ) ? ChurchTools_Suite_Sync_Modules::get_registered_modules() : [];
$module_statuses = [];
if ( ! empty( $registered_modules ) && class_exists( 'ChurchTools_Suite_Sync_Modules' ) ) {
	foreach ( $registered_modules as $module_id => $module_config ) {
		$module_statuses[ $module_id ] = ChurchTools_Suite_Sync_Modules::get_module_status( (string) $module_id );
	}
}
?>

<div class="cts-tab-content-inner">

	<div class="cts-card" style="margin-top: 20px;">
		<div class="cts-card-header">
			<h2>🧩 <?php esc_html_e('Modulstatus', 'churchtools-suite'); ?></h2>
		</div>
		<div class="cts-card-body">
			<?php if ( empty( $registered_modules ) ) : ?>
				<div class="notice notice-info inline" style="margin: 0;">
					<p><?php esc_html_e('Aktuell sind keine Sync-Module registriert. Aktiviere ein Addon (z. B. Posts Sync), damit hier Modulstatus angezeigt wird.', 'churchtools-suite'); ?></p>
				</div>
			<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e('Modul', 'churchtools-suite'); ?></th>
						<th><?php esc_html_e('Status', 'churchtools-suite'); ?></th>
						<th><?php esc_html_e('Letzter Source-Sync', 'churchtools-suite'); ?></th>
						<th><?php esc_html_e('Letzter Daten-Sync', 'churchtools-suite'); ?></th>
						<th><?php esc_html_e('Letztes Ergebnis', 'churchtools-suite'); ?></th>
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
							$last_source_sync_at = get_date_from_gmt( $last_source_sync_at, get_option('date_format') . ' ' . get_option('time_format') );
						}
						if ( $last_data_sync_at !== '' ) {
							$last_data_sync_at = get_date_from_gmt( $last_data_sync_at, get_option('date_format') . ' ' . get_option('time_format') );
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
	
	<!-- Sync Events Card -->
	<div class="cts-card" style="margin-top: 20px;">
		<div class="cts-card-header">
			<h2>📅 <?php esc_html_e('Termine synchronisieren', 'churchtools-suite'); ?></h2>
		</div>
		<div class="cts-card-body">
			<p class="description">
				<?php 
				printf(
					esc_html__('Lädt Termine und Events aus den ausgewählten Kalendern. Zeitraum: %d Tage zurück bis %d Tage voraus. Termine ohne Event verwenden Appointmentdaten, Termine mit Events die Eventdaten (1:X).', 'churchtools-suite'),
					$days_past,
					$days_future
				);
				?>
			</p>
			
			<?php if ($events_last_sync): ?>
			<p class="cts-info">
				<strong><?php esc_html_e('Letzte Synchronisation:', 'churchtools-suite'); ?></strong>
				<?php echo esc_html(get_date_from_gmt($events_last_sync, get_option('date_format') . ' ' . get_option('time_format'))); ?>
			</p>
			<?php endif; ?>
			
			<?php if (empty($calendars)): ?>
				<div class="notice notice-warning inline">
					<p><?php esc_html_e('Bitte synchronisieren Sie zuerst die Kalender, bevor Sie Termine laden.', 'churchtools-suite'); ?></p>
				</div>
			<?php elseif ($selected_count === 0): ?>
				<div class="notice notice-warning inline">
					<p><?php esc_html_e('Bitte wählen Sie im Kalender-Tab mindestens einen Kalender aus.', 'churchtools-suite'); ?></p>
				</div>
			<?php elseif ($auto_sync_enabled): ?>
				<div class="notice notice-info inline">
					<p>
						<strong><?php esc_html_e('Automatischer Sync ist aktiviert', 'churchtools-suite'); ?></strong><br>
						<?php esc_html_e('Der manuelle Sync ist deaktiviert, da der automatische Sync in den Einstellungen aktiviert ist. Termine werden automatisch im konfigurierten Intervall synchronisiert.', 'churchtools-suite'); ?><br>
						<?php printf(
							esc_html__('Um einen Sync sofort auszuführen, nutzen Sie den %sManuellen Trigger im Debug-Tab%s.', 'churchtools-suite'),
							'<a href="?page=churchtools-suite&tab=debug">',
							'</a>'
						); ?>
					</p>
				</div>
				<div class="cts-button-group">
					<button type="button" id="cts-sync-events-btn" class="button button-primary" disabled title="<?php esc_attr_e('Automatischer Sync ist aktiviert', 'churchtools-suite'); ?>">
						<span class="dashicons dashicons-calendar"></span>
						<?php esc_html_e('Termine jetzt synchronisieren', 'churchtools-suite'); ?>
					</button>
					<button type="button" id="cts-force-full-sync-btn" class="button button-secondary" disabled title="<?php esc_attr_e('Automatischer Sync ist aktiviert', 'churchtools-suite'); ?>" style="margin-left: 10px;">
						<span class="dashicons dashicons-backup"></span>
						<?php esc_html_e('Vollständigen Sync erzwingen', 'churchtools-suite'); ?>
					</button>
				</div>
			<?php else: ?>
				<div class="cts-button-group">
					<button type="button" id="cts-sync-events-btn" class="button button-primary">
						<span class="dashicons dashicons-calendar"></span>
						<?php esc_html_e('Termine jetzt synchronisieren', 'churchtools-suite'); ?>
					</button>
					<button type="button" id="cts-force-full-sync-btn" class="button button-secondary" style="margin-left: 10px;">
						<span class="dashicons dashicons-backup"></span>
						<?php esc_html_e('Vollständigen Sync erzwingen', 'churchtools-suite'); ?>
					</button>
				</div>
			<?php endif; ?>
			
			<div id="cts-sync-events-result" style="margin-top: 15px;"></div>
		</div>
	</div>
	
	<!-- Sync Info -->
	<div class="cts-card" style="margin-top: 20px;">
		<div class="cts-card-header">
			<h2>ℹ️ <?php esc_html_e('Hinweise zur Synchronisation', 'churchtools-suite'); ?></h2>
		</div>
		<div class="cts-card-body">
			<ul style="margin-left: 20px;">
				<li><?php esc_html_e('Die Kalender-Synchronisation lädt die verfügbaren Kalender aus ChurchTools.', 'churchtools-suite'); ?></li>
				<li><?php esc_html_e('Nach der Kalender-Synchronisation können Sie im Kalender-Tab auswählen, welche Kalender synchronisiert werden sollen.', 'churchtools-suite'); ?></li>
				<li><?php esc_html_e('Die Termin-Synchronisation lädt nur Termine aus den im Kalender-Tab ausgewählten Kalendern.', 'churchtools-suite'); ?></li>
				<li><?php esc_html_e('Den Zeitraum für die Termin-Synchronisation können Sie im Einstellungen-Tab anpassen.', 'churchtools-suite'); ?></li>
				<li><strong><?php esc_html_e('Inkrementelle Synchronisation (v0.7.1.0):', 'churchtools-suite'); ?></strong> <?php esc_html_e('Nach dem ersten Sync werden nur noch geänderte Termine abgerufen (80-95% weniger API-Anfragen). Ein vollständiger Sync kann mit dem "Vollständigen Sync erzwingen"-Button ausgelöst werden.', 'churchtools-suite'); ?></li>
			</ul>
		</div>
	</div>

</div>
