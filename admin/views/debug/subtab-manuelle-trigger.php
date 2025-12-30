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
		<h3>🛠️ Update & Log</h3>
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
<!-- <script>
jQuery(function($){
	$('#cts_clear_logs_btn').on('click', function(e){
		e.preventDefault();
		if (!confirm('<?php esc_html_e( 'Alle Plugin-Logs unwiderruflich löschen?', 'churchtools-suite' ); ?>')) return;
		var $btn = $(this);
		$btn.prop('disabled', true).text('⏳ <?php esc_html_e( 'Lösche...', 'churchtools-suite' ); ?>');
		$.post(ajaxurl, {
			action: 'cts_clear_logs',
			nonce: '<?php echo wp_create_nonce('churchtools_suite_admin'); ?>'
		}).done(function(resp){
			if (resp.success) {
				alert('<?php esc_html_e( 'Logs wurden gelöscht.', 'churchtools-suite' ); ?>');
				location.reload();
			} else {
				alert((resp.data && resp.data.message) ? resp.data.message : 'Fehler beim Löschen der Logs.');
			}
		}).fail(function(){
			alert('Netzwerkfehler beim Löschen der Logs.');
		}).always(function(){
			$btn.prop('disabled', false).text('🗑️ <?php esc_html_e( 'Log löschen', 'churchtools-suite' ); ?>');
		});
	});
});
</script> -->
