<?php
/**
 * Debug/Erweitert Subtab: Reset & Cleanup
 *
 * @package ChurchTools_Suite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="cts-debug-subtab-content">
	<h2>🗑️ Reset & Cleanup</h2>
	<p class="description"><?php esc_html_e('Vorsicht: Diese Aktionen löschen Daten aus der Datenbank. Die Einstellungen (ChurchTools-Verbindung, Auswahlen) bleiben erhalten.', 'churchtools-suite'); ?></p>

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

		<!-- Full Reset (Daten) -->
		<div style="padding: 15px; background: #fff3cd; border: 2px solid #ffc107; border-radius: 5px;">
			<h4 style="margin: 0 0 10px 0;">⚠️ <?php esc_html_e( 'Kompletter Reset (Daten)', 'churchtools-suite' ); ?></h4>
			<p style="font-size: 13px; color: #856404; margin-bottom: 10px;">
				<?php esc_html_e( 'Löscht ALLE Daten (Events, Kalender, Services, Sync-Historie). Einstellungen bleiben erhalten.', 'churchtools-suite' ); ?>
			</p>
			<button type="button" class="button button-primary" id="cts-full-reset" style="width: 100%; background: #d63638; border-color: #d63638;">
				<?php esc_html_e( 'Daten zurücksetzen', 'churchtools-suite' ); ?>
			</button>
		</div>

		<!-- Complete Reset (Daten + Einstellungen) -->
		<div style="padding: 15px; background: #f8d7da; border: 2px solid #dc3545; border-radius: 5px;">
			<h4 style="margin: 0 0 10px 0;">🚨 <?php esc_html_e( 'WIRKLICH ALLES reseten', 'churchtools-suite' ); ?></h4>
			<p style="font-size: 13px; color: #721c24; margin-bottom: 10px;">
				<?php esc_html_e( 'Löscht ALLES: Daten, Einstellungen, Cookies, Logindaten. Plugin wird komplett zurückgesetzt.', 'churchtools-suite' ); ?>
			</p>
			<button type="button" class="button button-primary" id="cts-complete-reset" style="width: 100%; background: #721c24; border-color: #721c24;">
				<?php esc_html_e( 'WIRKLICH ALLES löschen', 'churchtools-suite' ); ?>
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
				'<?php esc_html_e('ACHTUNG: Wirklich ALLE Daten löschen (Events, Kalender, Services, Sync-Historie)?\n\nDiese Aktion kann nicht rückgängig gemacht werden!\n\nEinstellungen bleiben erhalten.', 'churchtools-suite'); ?>',
				'<?php esc_html_e('Daten erfolgreich zurückgesetzt!', 'churchtools-suite'); ?>'
			);
		});
		
		$('#cts-complete-reset').on('click', function() {
			performReset(
				'cts_complete_reset',
				'<?php esc_html_e('🚨 KRITISCHE WARNUNG 🚨\n\nWirklich ALLES KOMPLETT LÖSCHEN?\n\n- Alle Daten (Events, Kalender, Services, etc.)\n- Alle Einstellungen (ChurchTools URL, Login, Cookies)\n- Kompletter Plugin-Reset\n\nSie müssen danach alles neu konfigurieren!\n\nDiese Aktion kann NICHT rückgängig gemacht werden!', 'churchtools-suite'); ?>',
				'<?php esc_html_e('Plugin komplett zurückgesetzt! Bitte neu konfigurieren.', 'churchtools-suite'); ?>'
			);
		});
	});
	</script>
</div>
