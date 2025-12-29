<?php
/**
 * Settings Subtab: Advanced
 *
 * @package ChurchTools_Suite
 * @since   0.7.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Form processing
if ( isset( $_POST['cts_save_advanced'] ) && check_admin_referer( 'cts_settings' ) ) {
	$advanced_mode = isset( $_POST['advanced_mode'] ) ? 1 : 0;
	update_option( 'churchtools_suite_advanced_mode', $advanced_mode );

	// Update auto-updater interval and token
	$interval = in_array( $_POST['cts_update_interval'] ?? '', [ 'hourly', 'daily', 'weekly' ], true ) ? $_POST['cts_update_interval'] : 'daily';
	update_option( 'churchtools_suite_update_interval', $interval );

	// Reschedule updater if class exists
	if ( class_exists( 'ChurchTools_Suite_Auto_Updater' ) ) {
		ChurchTools_Suite_Auto_Updater::reschedule( $interval );
	}
	
	echo '<div class="cts-notice cts-notice-success"><p>' . esc_html__( 'Erweiterte Einstellungen gespeichert.', 'churchtools-suite' ) . '</p></div>';
}

$advanced_mode = get_option( 'churchtools_suite_advanced_mode', 0 );
$update_interval = get_option( 'churchtools_suite_update_interval', 'daily' );
?>

<form method="post" action="" class="cts-form">
	<?php wp_nonce_field( 'cts_settings' ); ?>
	
	<div class="cts-card">
		<h3><?php esc_html_e( 'Erweiterte Optionen', 'churchtools-suite' ); ?></h3>
		
		<table class="cts-form-table">
			<tr>
				<th scope="row">
					<label for="advanced_mode"><?php esc_html_e( 'Erweiteter Modus', 'churchtools-suite' ); ?></label>
				</th>
				<td>
					<label class="cts-toggle">
						<input type="checkbox" 
							   id="advanced_mode" 
							   name="advanced_mode" 
							   value="1" 
							   <?php checked( $advanced_mode, 1 ); ?>>
						<span class="cts-toggle-slider"></span>
					</label>
					<span class="cts-form-description">
						<?php esc_html_e( 'Zeigt zusätzliche Funktionen wie Debug-Logs und erweiterte Statistiken in der Navigation an.', 'churchtools-suite' ); ?>
					</span>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="cts_update_interval"><?php esc_html_e( 'Auto‑Update Intervall', 'churchtools-suite' ); ?></label>
				</th>
				<td>
					<select id="cts_update_interval" name="cts_update_interval">
						<option value="hourly" <?php selected( $update_interval, 'hourly' ); ?>><?php esc_html_e( 'Stündlich', 'churchtools-suite' ); ?></option>
						<option value="daily" <?php selected( $update_interval, 'daily' ); ?>><?php esc_html_e( 'Täglich', 'churchtools-suite' ); ?></option>
						<option value="weekly" <?php selected( $update_interval, 'weekly' ); ?>><?php esc_html_e( 'Wöchentlich', 'churchtools-suite' ); ?></option>
					</select>
					<p class="cts-form-description"><?php esc_html_e( 'Legt fest, wie oft das Plugin automatisch nach Releases sucht.', 'churchtools-suite' ); ?></p>
				</td>
			</tr>


		</table>
		
		<?php if ( $advanced_mode ) : ?>
		<div class="cts-info" style="margin-top: 15px; padding: 12px; background: #fff3cd; border-left: 4px solid #ffc107;">
			<p style="margin: 0;">
				<strong>🔧 Erweiterter Modus aktiv:</strong> 
				<?php esc_html_e( 'Sie sehen jetzt zusätzliche Tabs und Optionen in der Administration.', 'churchtools-suite' ); ?>
			</p>
		</div>
		<?php endif; ?>
	</div>

	<div class="cts-submit">
		<button type="submit" name="cts_save_advanced" class="cts-button cts-button-primary">
			<span>💾</span>
			<?php esc_html_e( 'Speichern', 'churchtools-suite' ); ?>
		</button>

		<button type="button" id="cts_manual_update_btn" class="cts-button" style="margin-left:10px;">
			<span>🔄</span>
			<?php esc_html_e( 'Manuelles Update prüfen', 'churchtools-suite' ); ?>
		</button>
	</div>
</form>

<!-- Update Modal -->
<div id="cts_update_modal" style="display:none;">
	<div class="cts-modal-overlay"></div>
	<div class="cts-modal">
		<h3 id="cts_update_title"><?php esc_html_e( 'Update Verfügbar', 'churchtools-suite' ); ?></h3>
		<div id="cts_update_body">
			<p><?php esc_html_e( 'Prüfe...', 'churchtools-suite' ); ?></p>
		</div>
		<p style="margin-top:12px;">
			<button type="button" id="cts_start_update_btn" class="cts-button cts-button-danger"><?php esc_html_e( 'Update installieren', 'churchtools-suite' ); ?></button>
			<button type="button" id="cts_close_update_btn" class="cts-button" style="margin-left:8px;"><?php esc_html_e( 'Abbrechen', 'churchtools-suite' ); ?></button>
		</p>
	</div>
</div>

<style>
.cts-modal-overlay{position:fixed;left:0;top:0;right:0;bottom:0;background:rgba(0,0,0,0.5);}
.cts-modal{position:fixed;left:50%;top:50%;transform:translate(-50%,-50%);background:#fff;padding:20px;border-radius:6px;max-width:640px;width:90%;box-shadow:0 8px 24px rgba(0,0,0,0.2);}
</style>

<script type="text/javascript">
/* <![CDATA[ */
(function($){
	$('#cts_manual_update_btn').on('click', function(e){
		e.preventDefault();
		var $btn = $(this);
		$btn.prop('disabled', true).text('⏳ Prüfe...');
		$.post( churchtoolsSuite.ajaxUrl, {
			action: 'cts_manual_update',
			nonce: churchtoolsSuite.nonce
		}).done(function(resp){
			if ( resp.success && resp.data ) {
				var data = resp.data.data || resp.data; // older/newer shapes
				var html = '<p><strong><?php esc_html_e( 'Version', 'churchtools-suite' ); ?>:</strong> ' + (data.latest_version || data.latest_version) + '</p>' +
						   '<p><strong><?php esc_html_e( 'Release', 'churchtools-suite' ); ?>:</strong> <a href="' + (data.html_url || '#') + '" target="_blank">' + (data.tag_name || '') + '</a></p>' +
						   '<p><strong><?php esc_html_e( 'Paket', 'churchtools-suite' ); ?>:</strong> ' + (data.zip_url ? ('<a href="' + data.zip_url + '" target="_blank">Download</a>') : '<?php esc_html_e( 'Kein Paket verfügbar', 'churchtools-suite' ); ?>') + '</p>';
				$('#cts_update_body').html( html );
				$('#cts_update_modal').show();
			} else if ( resp.success ) {
				alert( resp.data.message || 'Update-Prüfung abgeschlossen.' );
			} else {
				alert( resp.data && resp.data.message ? resp.data.message : 'Fehler bei Update-Prüfung.' );
			}
		}).fail(function(){
			alert('Netzwerkfehler beim Auslösen der Update-Prüfung.');
		}).always(function(){
			$btn.prop('disabled', false).text('🔄 <?php esc_html_e( 'Manuelles Update prüfen', 'churchtools-suite' ); ?>');
		});
	});

	$('#cts_close_update_btn').on('click', function(){
		$('#cts_update_modal').hide();
	});

	$('#cts_start_update_btn').on('click', function(){
		if ( ! confirm('<?php esc_html_e( "Update jetzt installieren? Dies überschreibt Plugin-Dateien.", "churchtools-suite" ); ?>') ) {
			return;
		}
		var $btn = $(this);
		$btn.prop('disabled', true).text('⏳ Installiere...');
		$.post( churchtoolsSuite.ajaxUrl, {
			action: 'cts_run_update',
			nonce: churchtoolsSuite.nonce
		}).done(function(resp){
			if ( resp.success ) {
				alert( resp.data.message || 'Update gestartet.' );
			} else {
				alert( resp.data && resp.data.message ? resp.data.message : 'Fehler beim Starten des Updates.' );
			}
			$('#cts_update_modal').hide();
		}).fail(function(){
			alert('Netzwerkfehler beim Starten des Updates.');
		}).always(function(){
			$btn.prop('disabled', false).text('<?php esc_html_e( 'Update installieren', 'churchtools-suite' ); ?>');
		});
	});
})(jQuery);
/* ]]> */
</script>
