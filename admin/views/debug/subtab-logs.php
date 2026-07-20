<?php
/**
 * Debug/Erweitert Subtab: Logs
 *
 * @package ChurchTools_Suite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="cts-debug-subtab-content">
	<h2>📝 Logs</h2>
	<p>Hier können Sie die letzten Log-Einträge einsehen und das Log löschen.</p>
	<div class="cts-card">
		<h3>Service Import Logs</h3>
		<div style="display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 16px;">
			<button type="button" id="cts-reload-logs" class="cts-button cts-button-primary">
				<span>🔄</span> Logs neu laden
			</button>
			<button type="button" id="cts_export_logs_btn" class="cts-button cts-button-secondary">
				<span>📥</span> Logs exportieren (CSV)
			</button>
			<button type="button" id="cts_clear_logs_btn" class="cts-button cts-button-danger">
				<span>🗑️</span> Logs löschen
			</button>
		</div>
		<div id="cts-log-content" style="background: #1e1e1e; color: #d4d4d4; padding: 16px; border-radius: 4px; max-height: 400px; overflow-y: auto; font-family: 'Courier New', monospace; font-size: 12px; line-height: 1.6;">
			<?php
			$log_entries = ChurchTools_Suite_Logger::get_log_content(200); // Letzte 200 Einträge als Array
			if ( empty( $log_entries ) ) {
				echo '<span style="color: #8c8f94;">Keine Logs verfügbar. Führen Sie einen Sync aus, um Logs zu generieren.</span>';
			} else {
				if ( ! function_exists( 'esc_html' ) ) {
					function esc_html( $text ) { return htmlspecialchars( $text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' ); }
				}
				foreach ( $log_entries as $entry ) {
					$color = '#d4d4d4';
					switch ( $entry['level'] ?? '' ) {
						case 'error': $color = '#f48771'; break;
						case 'warning': $color = '#dcdcaa'; break;
						case 'info': $color = '#4ec9b0'; break;
						case 'debug': $color = '#9cdcfe'; break;
						case 'critical': $color = '#ff1744'; break;
					}
					$time = esc_html( $entry['timestamp'] ?? '' );
					$level = strtoupper( esc_html( $entry['level'] ?? '' ) );
					$context = esc_html( $entry['context'] ?? '' );
					$msg = esc_html( $entry['message'] ?? '' );
					echo '<div style="color:'.$color.';margin-bottom:2px;">';
					echo '<span style="color:#6a9fb5;">['.$time.']</span> ';
					echo '<span style="font-weight:600;">['.$level.']</span> ';
					echo '<span style="color:#b388ff;">['.$context.']</span> ';
					echo $msg;
					echo '</div>';
				}
			}
			?>
		</div>
	</div>
</div>

<script>
jQuery(function($){
	var exportNonce = '<?php echo esc_js( wp_create_nonce( 'churchtools_suite_admin' ) ); ?>';

	// Logs neu laden Button (v0.10.4.8)
	$('#cts-reload-logs').on('click', function(e){
		e.preventDefault();
		location.reload();
	});

	// Logs exportieren Button
	$('#cts_export_logs_btn').on('click', function(e){
		e.preventDefault();
		var $btn = $(this);
		$btn.prop('disabled', true).text('⏳ <?php esc_html_e( 'Exportiere...', 'churchtools-suite' ); ?>');

		var url = ajaxurl + '?action=cts_export_logs&nonce=' + encodeURIComponent(exportNonce) + '&lines=5000';
		window.location.href = url;

		setTimeout(function(){
			$btn.prop('disabled', false).text('📥 <?php esc_html_e( 'Logs exportieren (CSV)', 'churchtools-suite' ); ?>');
		}, 1200);
	});
	
	// Logs löschen Button
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
			$btn.prop('disabled', false).text('🗑️ <?php esc_html_e( 'Logs löschen', 'churchtools-suite' ); ?>');
		});
	});
});
</script>
