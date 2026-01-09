<?php
/**
 * Debug/Erweitert Subtab: Manuelle Trigger
 *
 * @package ChurchTools_Suite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="cts-debug-subtab-content">
	<h2>⚡ Manuelle Trigger</h2>
	<p>Führen Sie manuelle Aktionen wie Sync, Keepalive oder Update-Checks aus.</p>
	<div class="cts-card">
		<h3>🔄 Event-Sync & Session</h3>
		<div style="display: flex; gap: 12px; flex-wrap: wrap;">
			<button type="button" id="cts-trigger-manual-sync" class="cts-button cts-button-primary">
				<span>🔄</span> Event-Sync jetzt ausführen
			</button>
			<button type="button" id="cts-trigger-keepalive" class="cts-button cts-button-secondary">
				<span>💓</span> Session Keepalive
			</button>
		</div>
		<div id="cts-manual-trigger-result" style="margin-top: 16px;"></div>
	</div>
	<div class="cts-card" style="margin-top:24px;">

		<h3>�🛠️ Update & Log</h3>
		<div style="display: flex; gap: 12px; flex-wrap: wrap;">
			<button type="button" id="cts-manual-update" class="cts-button">
				<span>🔄</span> Manuelles Update prüfen
			</button>
			<button type="button" id="cts-clear-logs" class="cts-button cts-button-danger">
				<span>🗑️</span> Log löschen
			</button>
		</div>
	</div>
</div>

<script>
jQuery(function($) {
	// Manual Sync Trigger
	$('#cts-trigger-manual-sync').on('click', function() {
		var $btn = $(this);
		var $result = $('#cts-manual-trigger-result');
		
		$btn.prop('disabled', true).html('<span>⏳</span> Sync läuft...');
		$result.html('<div style="padding: 12px; background: #f0f9ff; border-radius: 4px;">🔄 Event-Sync gestartet...</div>');
		
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'cts_sync_events',
				nonce: '<?php echo wp_create_nonce('churchtools_suite_admin'); ?>'
			},
			success: function(response) {
				if (response && response.success) {
					// response.data.stats enthält die Sync-Statistiken
					var stats = response.data.stats || response.data || {};
					$result.html(
						'<div style="padding: 12px; background: #d1fae5; border-radius: 4px; border: 1px solid #10b981;">' +
						'✅ <strong>Sync erfolgreich!</strong><br>' +
						'Kalender: ' + (stats.calendars_processed || 0) + '<br>' +
						'Events: ' + (stats.events_inserted || 0) + ' neu, ' + 
						(stats.events_updated || 0) + ' aktualisiert<br>' +
						'Services: ' + (stats.services_imported || 0) +
						'</div>'
					);
					setTimeout(function() { location.reload(); }, 2000);
				} else {
					var errorMsg = 'Unbekannter Fehler';
					if (response && response.data && response.data.message) {
						errorMsg = response.data.message;
					} else if (response && response.data && response.data.error) {
						errorMsg = response.data.error;
					}
					$result.html(
						'<div style="padding: 12px; background: #fee2e2; border-radius: 4px; border: 1px solid #dc2626;">' +
						'❌ <strong>Fehler:</strong> ' + errorMsg +
						'</div>'
					);
				}
			},
			error: function() {
				$result.html(
					'<div style="padding: 12px; background: #fee2e2; border-radius: 4px; border: 1px solid #dc2626;">' +
					'❌ <strong>Netzwerkfehler</strong> - Bitte erneut versuchen' +
					'</div>'
				);
			},
			complete: function() {
				$btn.prop('disabled', false).html('<span>🔄</span> Event-Sync jetzt ausführen');
			}
		});
	});
	
	// Keepalive Trigger
	$('#cts-trigger-keepalive').on('click', function() {
		var $btn = $(this);
		var $result = $('#cts-manual-trigger-result');
		
		$btn.prop('disabled', true).html('<span>⏳</span> Teste...');
		$result.html('<div style="padding: 12px; background: #f0f9ff; border-radius: 4px;">💓 Session Keepalive läuft...</div>');
		
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'cts_keepalive_ping',
				nonce: '<?php echo wp_create_nonce('churchtools_suite_admin'); ?>'
			},
			success: function(response) {
				if (response.success) {
					$result.html(
						'<div style="padding: 12px; background: #d1fae5; border-radius: 4px; border: 1px solid #10b981;">' +
						'✅ <strong>Keepalive erfolgreich!</strong> Session ist aktiv.' +
						'</div>'
					);
				} else {
					$result.html(
						'<div style="padding: 12px; background: #fee2e2; border-radius: 4px; border: 1px solid #dc2626;">' +
						'❌ ' + (response.data.message || 'Session-Fehler') +
						'</div>'
					);
				}
			},
			error: function() {
				$result.html(
					'<div style="padding: 12px; background: #fee2e2; border-radius: 4px; border: 1px solid #dc2626;">' +
					'❌ <strong>Netzwerkfehler</strong>' +
					'</div>'
				);
			},
			complete: function() {
				$btn.prop('disabled', false).html('<span>💓</span> Session Keepalive');
			}
		});
	});
	
	// Manual Update Check
	$('#cts-manual-update').on('click', function() {
		var $btn = $(this);
		
		$btn.prop('disabled', true).html('<span>⏳</span> Update wird geprüft...');
		
		// Use WordPress native update mechanism
		// Trigger the update check by navigating to update-core.php with force-check parameter
		window.location.href = '<?php echo admin_url('update-core.php?force-check=1'); ?>';
		
		// Note: Page will reload, so no completion callback needed
	});
	
	// Clear Logs
	$('#cts-clear-logs').on('click', function() {
		if (!confirm('<?php esc_html_e('Alle Plugin-Logs unwiderruflich löschen?', 'churchtools-suite'); ?>')) {
			return;
		}
		
		var $btn = $(this);
		
		$btn.prop('disabled', true).html('<span>⏳</span> Lösche...');
		
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'cts_clear_logs',
				nonce: '<?php echo wp_create_nonce('churchtools_suite_admin'); ?>'
			},
			success: function(response) {
				if (response.success) {
					alert('✅ Logs wurden gelöscht.');
					location.reload();
				} else {
					alert('❌ ' + (response.data.message || 'Fehler beim Löschen der Logs'));
				}
			},
			error: function() {
				alert('❌ Netzwerkfehler beim Löschen der Logs');
			},
			complete: function() {
				$btn.prop('disabled', false).html('<span>🗑️</span> Log löschen');
			}
		});
	});
});
</script>
