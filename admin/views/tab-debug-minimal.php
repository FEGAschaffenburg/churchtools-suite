<?php
/**
 * Debug Tab (Minimal)
 *
 * @package ChurchTools_Suite
 * @since   0.5.11.28
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get sync history
global $wpdb;
$sync_history_table = $wpdb->prefix . 'cts_sync_history';
$sync_history = $wpdb->get_results( "SELECT * FROM {$sync_history_table} ORDER BY started_at DESC LIMIT 10" );
?>

<div class="cts-tab-content cts-debug">
	
	<div class="cts-section-header">
		<h2><?php esc_html_e( 'Debug Informationen', 'churchtools-suite' ); ?></h2>
		<p class="cts-section-description">
			<?php esc_html_e( 'Grundlegende Systeminformationen und Sync-Historie', 'churchtools-suite' ); ?>
		</p>
	</div>
	
	<!-- System Info -->
	<div class="cts-card">
		<div class="cts-card-header">
			<span class="cts-card-icon">⚙️</span>
			<h3><?php esc_html_e( 'System', 'churchtools-suite' ); ?></h3>
		</div>
		<div class="cts-card-body">
			<table class="cts-debug-table">
				<tr>
					<td><?php esc_html_e( 'Plugin-Version', 'churchtools-suite' ); ?></td>
					<td><?php echo esc_html( CHURCHTOOLS_SUITE_VERSION ); ?></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'PHP-Version', 'churchtools-suite' ); ?></td>
					<td><?php echo esc_html( phpversion() ); ?></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'WordPress-Version', 'churchtools-suite' ); ?></td>
					<td><?php echo esc_html( get_bloginfo( 'version' ) ); ?></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'DB-Version', 'churchtools-suite' ); ?></td>
					<td><?php echo esc_html( ChurchTools_Suite_Migrations::get_current_version() ); ?></td>
				</tr>
			</table>
		</div>
	</div>
	
	<!-- Sync History -->
	<div class="cts-card" style="margin-top: 20px;">
		<div class="cts-card-header">
			<span class="cts-card-icon">📊</span>
			<h3><?php esc_html_e( 'Sync-Historie (letzte 10)', 'churchtools-suite' ); ?></h3>
		</div>
		<div class="cts-card-body">
			<?php if ( empty( $sync_history ) ) : ?>
				<p><?php esc_html_e( 'Keine Sync-Historie vorhanden', 'churchtools-suite' ); ?></p>
			<?php else : ?>
				<table class="cts-debug-table">
					<tr>
						<td><strong><?php esc_html_e( 'Zeitpunkt', 'churchtools-suite' ); ?></strong></td>
						<td><strong><?php esc_html_e( 'Status', 'churchtools-suite' ); ?></strong></td>
						<td><strong><?php esc_html_e( 'Kalender', 'churchtools-suite' ); ?></strong></td>
						<td><strong><?php esc_html_e( 'Events', 'churchtools-suite' ); ?></strong></td>
						<td><strong><?php esc_html_e( 'Services', 'churchtools-suite' ); ?></strong></td>
						<td><strong><?php esc_html_e( 'Dauer', 'churchtools-suite' ); ?></strong></td>
					</tr>
					<?php foreach ( $sync_history as $entry ) : ?>
					<tr>
						<td><?php echo esc_html( $entry->started_at ); ?></td>
						<td><?php echo esc_html( $entry->status ); ?></td>
						<td><?php echo esc_html( $entry->calendars_processed ); ?></td>
						<td><?php echo esc_html( $entry->events_found ); ?></td>
						<td><?php echo esc_html( $entry->services_imported ); ?></td>
						<td><?php echo esc_html( $entry->duration_seconds ); ?>s</td>
					</tr>
					<?php endforeach; ?>
				</table>
			<?php endif; ?>
		</div>
	</div>
	
	<!-- Rate Limiting (v0.7.0.2) -->
	<div class="cts-card" style="margin-top: 20px;">
		<div class="cts-card-header">
			<span class="cts-card-icon">🚦</span>
			<h3><?php esc_html_e( 'Rate Limiting', 'churchtools-suite' ); ?></h3>
		</div>
		<div class="cts-card-body">

								$(this).hide();
							}
						});
					});
				});
				</script>
			<?php endif; ?>
			
			<p class="description" style="margin-top: 15px;">
				<?php esc_html_e('Zeigt die letzten 100 Log-Einträge. Logs werden automatisch rotiert (max. 10 MB) und nach 30 Tagen gelöscht.', 'churchtools-suite'); ?>
			</p>
		</div>
	</div>
	
	<!-- Reset & Cleanup (v0.7.2.4) -->
	<div class="cts-card" style="margin-top: 20px;">
		<div class="cts-card-header">
			<span class="cts-card-icon">🗑️</span>
			<h3><?php esc_html_e('Reset & Cleanup', 'churchtools-suite'); ?></h3>
		</div>
		<div class="cts-card-body">
			<p class="description" style="margin-bottom: 20px;">
				<?php esc_html_e('Vorsicht: Diese Aktionen löschen Daten aus der Datenbank. Die Einstellungen (ChurchTools-Verbindung, Auswahlen) bleiben erhalten.', 'churchtools-suite'); ?>
			</p>
			
			<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
				
				<!-- Clear Events -->
				<div style="padding: 15px; background: #f9f9f9; border-radius: 5px;">
					<h4 style="margin: 0 0 10px 0;">📅 <?php esc_html_e('Events löschen', 'churchtools-suite'); ?></h4>
					<p style="font-size: 13px; color: #666; margin-bottom: 10px;">
						<?php esc_html_e('Löscht alle Events aus der Datenbank.', 'churchtools-suite'); ?>
					</p>
					<button type="button" class="button" id="cts-clear-events" style="width: 100%;">
						<?php esc_html_e('Events löschen', 'churchtools-suite'); ?>
					</button>
				</div>
				
				<!-- Clear Calendars -->
				<div style="padding: 15px; background: #f9f9f9; border-radius: 5px;">
					<h4 style="margin: 0 0 10px 0;">🗓️ <?php esc_html_e('Kalender löschen', 'churchtools-suite'); ?></h4>
					<p style="font-size: 13px; color: #666; margin-bottom: 10px;">
						<?php esc_html_e('Löscht alle Kalender aus der Datenbank.', 'churchtools-suite'); ?>
					</p>
					<button type="button" class="button" id="cts-clear-calendars" style="width: 100%;">
						<?php esc_html_e('Kalender löschen', 'churchtools-suite'); ?>
					</button>
				</div>
				
				<!-- Clear Services -->
				<div style="padding: 15px; background: #f9f9f9; border-radius: 5px;">
					<h4 style="margin: 0 0 10px 0;">👥 <?php esc_html_e('Services löschen', 'churchtools-suite'); ?></h4>
					<p style="font-size: 13px; color: #666; margin-bottom: 10px;">
						<?php esc_html_e('Löscht alle Services und Service-Gruppen.', 'churchtools-suite'); ?>
					</p>
					<button type="button" class="button" id="cts-clear-services" style="width: 100%;">
						<?php esc_html_e('Services löschen', 'churchtools-suite'); ?>
					</button>
				</div>
				
				<!-- Clear Sync History -->
				<div style="padding: 15px; background: #f9f9f9; border-radius: 5px;">
					<h4 style="margin: 0 0 10px 0;">📊 <?php esc_html_e('Sync-Historie löschen', 'churchtools-suite'); ?></h4>
					<p style="font-size: 13px; color: #666; margin-bottom: 10px;">
						<?php esc_html_e('Löscht die gesamte Sync-Historie.', 'churchtools-suite'); ?>
					</p>
					<button type="button" class="button" id="cts-clear-sync-history" style="width: 100%;">
						<?php esc_html_e('Historie löschen', 'churchtools-suite'); ?>
					</button>
				</div>
				
				<!-- Full Reset -->
				<div style="padding: 15px; background: #fff3cd; border: 2px solid #ffc107; border-radius: 5px;">
					<h4 style="margin: 0 0 10px 0;">⚠️ <?php esc_html_e('Kompletter Reset', 'churchtools-suite'); ?></h4>
					<p style="font-size: 13px; color: #856404; margin-bottom: 10px;">
						<?php esc_html_e('Löscht ALLE Daten (Events, Kalender, Services, Sync-Historie). Einstellungen bleiben erhalten.', 'churchtools-suite'); ?>
					</p>
					<button type="button" class="button button-primary" id="cts-full-reset" style="width: 100%; background: #d63638; border-color: #d63638;">
						<?php esc_html_e('Komplett zurücksetzen', 'churchtools-suite'); ?>
					</button>
				</div>
				
			</div>
			
			<script>
			jQuery(function($) {
				// Helper function for AJAX reset calls
				function performReset(action, confirmMessage, successMessage) {
					if (!confirm(confirmMessage)) {
						return;
					}
					
					$.ajax({
						url: ajaxurl,
						type: 'POST',
						data: {
							action: action,
							nonce: '<?php echo wp_create_nonce('churchtools_suite_admin'); ?>'
						},
						beforeSend: function() {
							$('#' + action.replace('cts_', 'cts-')).prop('disabled', true).text('⏳ <?php esc_html_e('Wird gelöscht...', 'churchtools-suite'); ?>');
						},
						success: function(response) {
							if (response.success) {
								alert(successMessage + '\n\n' + response.data.message);
								location.reload();
							} else {
								alert('<?php esc_html_e('Fehler:', 'churchtools-suite'); ?> ' + response.data.message);
							}
						},
						error: function() {
							alert('<?php esc_html_e('Fehler beim Löschen', 'churchtools-suite'); ?>');
						},
						complete: function() {
							$('#' + action.replace('cts_', 'cts-')).prop('disabled', false).text('<?php esc_html_e('Erneut löschen', 'churchtools-suite'); ?>');
						}
					});
				}
				
				$('#cts-clear-events').on('click', function() {
					performReset(
						'cts_clear_events',
						'<?php esc_html_e('Wirklich alle Events löschen? Diese Aktion kann nicht rückgängig gemacht werden!', 'churchtools-suite'); ?>',
						'<?php esc_html_e('Events erfolgreich gelöscht!', 'churchtools-suite'); ?>'
					);
				});
				
				$('#cts-clear-calendars').on('click', function() {
					performReset(
						'cts_clear_calendars',
						'<?php esc_html_e('Wirklich alle Kalender löschen? Diese Aktion kann nicht rückgängig gemacht werden!', 'churchtools-suite'); ?>',
						'<?php esc_html_e('Kalender erfolgreich gelöscht!', 'churchtools-suite'); ?>'
					);
				});
				
				$('#cts-clear-services').on('click', function() {
					performReset(
						'cts_clear_services',
						'<?php esc_html_e('Wirklich alle Services löschen? Diese Aktion kann nicht rückgängig gemacht werden!', 'churchtools-suite'); ?>',
						'<?php esc_html_e('Services erfolgreich gelöscht!', 'churchtools-suite'); ?>'
					);
				});
				
				$('#cts-clear-sync-history').on('click', function() {
					performReset(
						'cts_clear_sync_history',
						'<?php esc_html_e('Wirklich die gesamte Sync-Historie löschen?', 'churchtools-suite'); ?>',
						'<?php esc_html_e('Sync-Historie erfolgreich gelöscht!', 'churchtools-suite'); ?>'
					);
				});
				
				$('#cts-full-reset').on('click', function() {
					performReset(
						'cts_full_reset',
						'<?php esc_html_e('ACHTUNG: Wirklich ALLE Daten löschen (Events, Kalender, Services, Sync-Historie)?\n\nDiese Aktion kann nicht rückgängig gemacht werden!\n\nEinstellungen bleiben erhalten und müssen separat unter Einstellungen gelöscht werden.', 'churchtools-suite'); ?>',
						'<?php esc_html_e('Plugin erfolgreich zurückgesetzt!', 'churchtools-suite'); ?>'
					);
				});
			});
			</script>
		</div>
	</div>
	
