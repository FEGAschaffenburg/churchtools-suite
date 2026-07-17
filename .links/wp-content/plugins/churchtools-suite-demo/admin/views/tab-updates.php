<?php
/**
 * Updates Tab
 *
 * @package ChurchTools_Suite_Demo
 * @since   1.1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get current version
$current_version = CHURCHTOOLS_SUITE_DEMO_VERSION;

// Check for updates (use transient to cache results)
$update_info = get_transient( 'cts_demo_update_check' );
if ( false === $update_info ) {
	$update_info = null; // Will be fetched via AJAX
}
?>

<div class="tab-updates">
	<h2><?php _e( 'Plugin-Updates', 'churchtools-suite-demo' ); ?></h2>
	
	<div class="cts-info-box">
		<p><strong><?php _e( 'Aktuelle Version:', 'churchtools-suite-demo' ); ?></strong> <?php echo esc_html( $current_version ); ?></p>
		<p><?php _e( 'Dieses Plugin verwendet automatische Updates über GitHub Releases.', 'churchtools-suite-demo' ); ?></p>
	</div>
	
	<!-- Check for Updates -->
	<div class="cts-action-buttons">
		<button type="button" id="cts-check-updates" class="button button-primary">
			<span class="dashicons dashicons-update"></span>
			<?php _e( 'Jetzt nach Updates suchen', 'churchtools-suite-demo' ); ?>
		</button>
		
		<button type="button" id="cts-clear-cache" class="button">
			<span class="dashicons dashicons-trash"></span>
			<?php _e( 'Update-Cache leeren', 'churchtools-suite-demo' ); ?>
		</button>
	</div>
	
	<!-- Update Results -->
	<div id="cts-update-results" style="margin-top: 20px;"></div>
	
	<hr>
	
	<!-- GitHub Release Info -->
	<h3><?php _e( 'GitHub-Repository', 'churchtools-suite-demo' ); ?></h3>
	
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row"><?php _e( 'Repository', 'churchtools-suite-demo' ); ?></th>
			<td>
				<a href="https://github.com/FEGAschaffenburg/churchtools-suite-demos" target="_blank">
					FEGAschaffenburg/churchtools-suite-demos
					<span class="dashicons dashicons-external" style="text-decoration: none;"></span>
				</a>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php _e( 'Aktueller Branch', 'churchtools-suite-demo' ); ?></th>
			<td><code>master</code></td>
		</tr>
		<tr>
			<th scope="row"><?php _e( 'Update-Mechanismus', 'churchtools-suite-demo' ); ?></th>
			<td>
				<?php _e( 'ChurchTools_Suite_Demo_Auto_Updater', 'churchtools-suite-demo' ); ?>
				<p class="description">
					<?php _e( 'Prüft GitHub API alle 12 Stunden auf neue Releases', 'churchtools-suite-demo' ); ?>
				</p>
			</td>
		</tr>
	</table>
	
	<hr>
	
	<!-- Manual Update Instructions -->
	<h3><?php _e( 'Manuelles Update', 'churchtools-suite-demo' ); ?></h3>
	
	<div class="cts-info-box">
		<p><strong><?php _e( 'Falls automatische Updates nicht funktionieren:', 'churchtools-suite-demo' ); ?></strong></p>
		<ol>
			<li><?php _e( 'Laden Sie die neueste Version von GitHub herunter:', 'churchtools-suite-demo' ); ?> 
				<a href="https://github.com/FEGAschaffenburg/churchtools-suite-demos/releases/latest" target="_blank">
					<?php _e( 'Latest Release', 'churchtools-suite-demo' ); ?>
					<span class="dashicons dashicons-external" style="text-decoration: none;"></span>
				</a>
			</li>
			<li><?php _e( 'Entpacken Sie die ZIP-Datei', 'churchtools-suite-demo' ); ?></li>
			<li><?php _e( 'Laden Sie die Dateien per FTP/SFTP in das Plugin-Verzeichnis hoch:', 'churchtools-suite-demo' ); ?>
				<code><?php echo esc_html( WP_PLUGIN_DIR . '/churchtools-suite-demo/' ); ?></code>
			</li>
			<li><?php _e( 'Stellen Sie sicher, dass alle Dateien korrekte Berechtigungen haben (755 für Ordner, 644 für Dateien)', 'churchtools-suite-demo' ); ?></li>
		</ol>
	</div>
	
	<!-- Version History -->
	<hr>
	
	<h3><?php _e( 'Version History', 'churchtools-suite-demo' ); ?></h3>
	
	<div id="cts-version-history">
		<button type="button" id="cts-load-history" class="button">
			<span class="dashicons dashicons-clock"></span>
			<?php _e( 'Release History von GitHub laden', 'churchtools-suite-demo' ); ?>
		</button>
		<div id="cts-history-results" style="margin-top: 15px;"></div>
	</div>
</div>

<script>
jQuery(document).ready(function($) {
	// Check for updates
	$('#cts-check-updates').on('click', function() {
		const $btn = $(this);
		const originalHtml = $btn.html();
		
		$btn.prop('disabled', true).html('<span class="cts-loading"></span> <?php _e( 'Prüfe...', 'churchtools-suite-demo' ); ?>');
		$('#cts-update-results').html('');
		
		$.ajax({
			url: ajaxurl,
			method: 'POST',
			data: {
				action: 'cts_demo_check_updates',
				nonce: '<?php echo wp_create_nonce( 'cts_demo_admin' ); ?>'
			},
			success: function(response) {
				if (response.success) {
					const data = response.data;
					let html = '';
					
					if (data.has_update) {
						html = '<div class="cts-warning-box">';
						html += '<p><strong>⚡ ' + data.message + '</strong></p>';
						html += '<p><?php _e( 'Verfügbare Version:', 'churchtools-suite-demo' ); ?> <strong>' + data.latest_version + '</strong></p>';
						html += '<p><?php _e( 'Installierte Version:', 'churchtools-suite-demo' ); ?> ' + data.current_version + '</p>';
						html += '<p><a href="<?php echo admin_url( 'plugins.php' ); ?>" class="button button-primary"><?php _e( 'Zu WordPress Updates', 'churchtools-suite-demo' ); ?></a></p>';
						html += '</div>';
					} else {
						html = '<div class="cts-success-box">';
						html += '<p><strong>✓ ' + data.message + '</strong></p>';
						html += '<p><?php _e( 'Installierte Version:', 'churchtools-suite-demo' ); ?> ' + data.current_version + '</p>';
						html += '</div>';
					}
					
					$('#cts-update-results').html(html);
				} else {
					$('#cts-update-results').html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
				}
			},
			error: function() {
				$('#cts-update-results').html('<div class="notice notice-error"><p><?php _e( 'Fehler beim Abrufen der Update-Informationen', 'churchtools-suite-demo' ); ?></p></div>');
			},
			complete: function() {
				$btn.prop('disabled', false).html(originalHtml);
			}
		});
	});
	
	// Clear cache
	$('#cts-clear-cache').on('click', function() {
		const $btn = $(this);
		const originalHtml = $btn.html();
		
		$btn.prop('disabled', true).html('<span class="cts-loading"></span> <?php _e( 'Lösche...', 'churchtools-suite-demo' ); ?>');
		
		$.ajax({
			url: ajaxurl,
			method: 'POST',
			data: {
				action: 'cts_demo_clear_update_cache',
				nonce: '<?php echo wp_create_nonce( 'cts_demo_admin' ); ?>'
			},
			success: function(response) {
				if (response.success) {
					$('#cts-update-results').html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
					setTimeout(function() {
						$('#cts-update-results').html('');
					}, 3000);
				}
			},
			complete: function() {
				$btn.prop('disabled', false).html(originalHtml);
			}
		});
	});
	
	// Load version history
	$('#cts-load-history').on('click', function() {
		const $btn = $(this);
		const originalHtml = $btn.html();
		
		$btn.prop('disabled', true).html('<span class="cts-loading"></span> <?php _e( 'Lade...', 'churchtools-suite-demo' ); ?>');
		
		$.ajax({
			url: ajaxurl,
			method: 'POST',
			data: {
				action: 'cts_demo_get_version_history',
				nonce: '<?php echo wp_create_nonce( 'cts_demo_admin' ); ?>'
			},
			success: function(response) {
				if (response.success) {
					let html = '<table class="wp-list-table widefat fixed striped">';
					html += '<thead><tr><th><?php _e( 'Version', 'churchtools-suite-demo' ); ?></th><th><?php _e( 'Veröffentlicht', 'churchtools-suite-demo' ); ?></th><th><?php _e( 'Beschreibung', 'churchtools-suite-demo' ); ?></th></tr></thead>';
					html += '<tbody>';
					
					response.data.releases.forEach(function(release) {
						html += '<tr>';
						html += '<td><strong><a href="' + release.url + '" target="_blank">' + release.tag + '</a></strong></td>';
						html += '<td>' + release.date + '</td>';
						html += '<td>' + (release.description || '-') + '</td>';
						html += '</tr>';
					});
					
					html += '</tbody></table>';
					
					$('#cts-history-results').html(html);
					$btn.hide();
				}
			},
			error: function() {
				$('#cts-history-results').html('<p class="description"><?php _e( 'Fehler beim Laden der Version History', 'churchtools-suite-demo' ); ?></p>');
			},
			complete: function() {
				$btn.prop('disabled', false).html(originalHtml);
			}
		});
	});
});
</script>
