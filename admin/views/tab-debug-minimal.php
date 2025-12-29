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
			<?php
			// Load Rate Limiter
			require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-rate-limiter.php';
			
			$user_id = get_current_user_id();
			$identifier = 'user_' . $user_id;
			
			$api_status = ChurchTools_Suite_Rate_Limiter::get_status( $identifier, 'api' );
			$ajax_status = ChurchTools_Suite_Rate_Limiter::get_status( $identifier, 'ajax' );
			?>
			
			<table class="cts-debug-table">
				<tr>
					<td colspan="2"><strong><?php esc_html_e( 'API Requests', 'churchtools-suite' ); ?></strong></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Pro Minute', 'churchtools-suite' ); ?></td>
					<td>
						<?php echo esc_html( $api_status['minute_count'] ); ?> / <?php echo esc_html( $api_status['minute_limit'] ); ?>
						<span style="color: <?php echo $api_status['minute_remaining'] < 10 ? '#d63638' : '#50C878'; ?>">
							(<?php echo esc_html( $api_status['minute_remaining'] ); ?> <?php esc_html_e( 'verfügbar', 'churchtools-suite' ); ?>)
						</span>
					</td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Pro Stunde', 'churchtools-suite' ); ?></td>
					<td>
						<?php echo esc_html( $api_status['hour_count'] ); ?> / <?php echo esc_html( $api_status['hour_limit'] ); ?>
						<span style="color: <?php echo $api_status['hour_remaining'] < 100 ? '#d63638' : '#50C878'; ?>">
							(<?php echo esc_html( $api_status['hour_remaining'] ); ?> <?php esc_html_e( 'verfügbar', 'churchtools-suite' ); ?>)
						</span>
					</td>
				</tr>
				<tr>
					<td colspan="2"><strong><?php esc_html_e( 'AJAX Requests', 'churchtools-suite' ); ?></strong></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Pro Minute', 'churchtools-suite' ); ?></td>
					<td>
						<?php echo esc_html( $ajax_status['minute_count'] ); ?> / <?php echo esc_html( $ajax_status['minute_limit'] ); ?>
						<span style="color: <?php echo $ajax_status['minute_remaining'] < 10 ? '#d63638' : '#50C878'; ?>">
							(<?php echo esc_html( $ajax_status['minute_remaining'] ); ?> <?php esc_html_e( 'verfügbar', 'churchtools-suite' ); ?>)
						</span>
					</td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Pro Stunde', 'churchtools-suite' ); ?></td>
					<td>
						<?php echo esc_html( $ajax_status['hour_count'] ); ?> / <?php echo esc_html( $ajax_status['hour_limit'] ); ?>
						<span style="color: <?php echo $ajax_status['hour_remaining'] < 100 ? '#d63638' : '#50C878'; ?>">
							(<?php echo esc_html( $ajax_status['hour_remaining'] ); ?> <?php esc_html_e( 'verfügbar', 'churchtools-suite' ); ?>)
						</span>
					</td>
				</tr>
			</table>
			
			<p class="description" style="margin-top: 15px;">
				<?php esc_html_e( 'Rate Limiting schützt vor übermäßigen API-Anfragen. Limits: 60/Minute, 1000/Stunde.', 'churchtools-suite' ); ?>
			</p>
		</div>
	</div>
	
	<!-- System Logs (v0.7.0.3) -->
	<div class="cts-card" style="margin-top: 20px;">
		<div class="cts-card-header">
			<span class="cts-card-icon">📝</span>
			<h3><?php esc_html_e( 'System Logs', 'churchtools-suite' ); ?></h3>
		</div>
		<div class="cts-card-body">
			<?php
			// Load Logger
			require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-logger.php';
			
			// Get log content (last 100 lines) - returns array of parsed entries
			$log_entries = ChurchTools_Suite_Logger::get_log_content(100);
			?>
			
			<?php if (empty($log_entries)) : ?>
				<p><?php esc_html_e('Keine Log-Einträge vorhanden', 'churchtools-suite'); ?></p>
			<?php else : ?>
				<div style="margin-bottom: 15px;">
					<label>
						<?php esc_html_e('Filter nach Level:', 'churchtools-suite'); ?>
						<select id="cts-log-level-filter" style="margin-left: 10px;">
							<option value=""><?php esc_html_e('Alle', 'churchtools-suite'); ?></option>
							<option value="DEBUG"><?php esc_html_e('Debug', 'churchtools-suite'); ?></option>
							<option value="INFO"><?php esc_html_e('Info', 'churchtools-suite'); ?></option>
							<option value="WARNING"><?php esc_html_e('Warning', 'churchtools-suite'); ?></option>
							<option value="ERROR"><?php esc_html_e('Error', 'churchtools-suite'); ?></option>
							<option value="CRITICAL"><?php esc_html_e('Critical', 'churchtools-suite'); ?></option>
						</select>
					</label>
					
					<button type="button" class="button" style="margin-left: 15px;" onclick="location.reload()">
						🔄 <?php esc_html_e('Aktualisieren', 'churchtools-suite'); ?>
					</button>
				</div>
				
				<div style="max-height: 500px; overflow-y: auto; background: #1e1e1e; padding: 15px; border-radius: 5px; font-family: 'Courier New', monospace; font-size: 12px;">
					<?php foreach ($log_entries as $entry) : ?>
						<?php
						// Entry is already parsed as array from get_log_content()
						if (is_array($entry) && isset($entry['level'])) {
							// JSON format (new)
							$level = strtoupper($entry['level']); // v0.7.2.6: Convert to uppercase
							$message = $entry['message'];
							$timestamp = $entry['timestamp'] ?? '';
							$context_data = $entry['data'] ?? $entry['context'] ?? []; // v0.7.2.6: Use 'data' field
							$context_name = $entry['context'] ?? 'general'; // v0.7.2.6: Context name
							
							// Level colors
							$level_colors = [
								'DEBUG' => '#888',
								'INFO' => '#50C878',
								'WARNING' => '#FFD700',
								'ERROR' => '#FF6B6B',
								'CRITICAL' => '#FF0000'
							];
							$color = $level_colors[$level] ?? '#FFF';
						?>
							<div class="cts-log-entry" data-level="<?php echo esc_attr($level); ?>" style="margin-bottom: 10px; padding: 8px; background: #2a2a2a; border-left: 3px solid <?php echo esc_attr($color); ?>; border-radius: 3px;">
								<div style="color: #888; font-size: 11px;">
									<?php echo esc_html($timestamp); ?>
									<?php if ($context_name && $context_name !== 'general') : ?>
										<span style="margin-left: 10px; color: #6c9bcf;">[<?php echo esc_html($context_name); ?>]</span>
									<?php endif; ?>
								</div>
								<div>
									<span style="color: <?php echo esc_attr($color); ?>; font-weight: bold;">[<?php echo esc_html($level); ?>]</span>
									<span style="color: #FFF; margin-left: 10px;"><?php echo esc_html($message); ?></span>
								</div>
								<?php if (!empty($context_data)) : ?>
									<details style="margin-top: 5px;">
										<summary style="cursor: pointer; color: #888; font-size: 11px;"><?php esc_html_e('Details anzeigen', 'churchtools-suite'); ?></summary>
										<pre style="color: #888; margin: 5px 0 0 0; font-size: 11px; white-space: pre-wrap;"><?php echo esc_html(print_r($context_data, true)); ?></pre>
									</details>
								<?php endif; ?>
							</div>
						<?php } elseif (isset($entry['context']) && $entry['context'] === 'legacy') { ?>
							<!-- Legacy format from old logs -->
							<div class="cts-log-entry" data-level="INFO" style="color: #FFF; margin-bottom: 5px; padding: 5px; background: #2a2a2a; border-radius: 3px;">
								[<?php echo esc_html($entry['timestamp']); ?>] [<?php echo esc_html(strtoupper($entry['level'])); ?>] <?php echo esc_html($entry['message']); ?>
							</div>
						<?php } ?>
					<?php endforeach; ?>
				</div>
				
				<script>
				jQuery(function($) {
					$('#cts-log-level-filter').on('change', function() {
						var level = $(this).val();
						$('.cts-log-entry').each(function() {
							if (level === '' || $(this).data('level') === level) {
								$(this).show();
							} else {
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
	
