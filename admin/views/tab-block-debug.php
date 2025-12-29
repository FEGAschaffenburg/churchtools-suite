<?php
/**
 * Block Debug Tab
 *
 * @package ChurchTools_Suite
 * @since   0.5.9.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get block debug logs from options
$block_logs = get_option( 'churchtools_suite_block_debug_logs', [] );
$block_status = get_option( 'churchtools_suite_block_status', [] );

// Reverse logs (newest first)
$block_logs = array_reverse( $block_logs );
?>

<div class="cts-tab-content">
	
	<div class="cts-section-header">
		<h2>🔍 Block Debug Informationen</h2>
		<p class="cts-section-description">
			Live-Debugging für Gutenberg Blocks. Zeigt Registration und Rendering-Status.
		</p>
	</div>
	
	<!-- Block Status -->
	<div class="cts-card" style="max-width: 1200px; margin-bottom: 20px;">
		<div class="cts-card-header">
			<span class="cts-card-icon">📊</span>
			<h3>Block Registration Status</h3>
		</div>
		<div class="cts-card-body">
			<?php if ( ! empty( $block_status ) ) : ?>
				<table class="cts-debug-table">
					<thead>
						<tr>
							<th style="width: 30%;">Block</th>
							<th style="width: 20%;">Status</th>
							<th style="width: 50%;">Details</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $block_status as $block_id => $status ) : ?>
							<tr>
								<td><code><?php echo esc_html( $block_id ); ?></code></td>
								<td>
									<?php if ( $status['registered'] ) : ?>
										<span style="color: #00a32a; font-weight: 600;">✅ Registriert</span>
									<?php else : ?>
										<span style="color: #d63638; font-weight: 600;">❌ Fehler</span>
									<?php endif; ?>
								</td>
								<td>
									<?php if ( isset( $status['render_callback'] ) ) : ?>
										<small>Callback: <code><?php echo esc_html( $status['render_callback'] ); ?></code></small><br>
									<?php endif; ?>
									<?php if ( isset( $status['last_render'] ) ) : ?>
										<small>Letztes Rendering: <?php echo esc_html( $status['last_render'] ); ?></small>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php else : ?>
				<p style="color: #999; font-style: italic;">Keine Block-Status-Daten verfügbar. Bitte Seite neu laden.</p>
			<?php endif; ?>
		</div>
	</div>
	
	<!-- Shortcode Parameter Test -->
	<div class="cts-card" style="max-width: 1200px; margin-bottom: 20px;">
		<div class="cts-card-header">
			<span class="cts-card-icon">🧪</span>
			<h3>Shortcode Parameter Test</h3>
		</div>
		<div class="cts-card-body">
			<p style="margin-bottom: 15px; color: #646970;">
				Teste die boolean-Parameter direkt:
			</p>
			
			<div style="background: #f9fafb; padding: 20px; border-radius: 4px; border: 1px solid #e5e7eb; margin-bottom: 20px;">
				<h4 style="margin: 0 0 15px 0;">Test mit allen Parametern TRUE:</h4>
				<code style="background: #1e1e1e; color: #10b981; padding: 10px; display: block; border-radius: 4px; margin-bottom: 10px;">
					[cts_list view="classic" limit="3" show_services="true" show_description="true" show_location="true"]
				</code>
				<div style="border: 2px solid #667eea; border-radius: 4px; padding: 15px; background: #fff;">
					<?php echo do_shortcode('[cts_list view="classic" limit="3" show_services="true" show_description="true" show_location="true"]'); ?>
				</div>
			</div>
			
			<div style="background: #fff5f5; padding: 20px; border-radius: 4px; border: 1px solid #fecaca; margin-bottom: 20px;">
				<h4 style="margin: 0 0 15px 0;">Test mit allen Parametern FALSE:</h4>
				<code style="background: #1e1e1e; color: #f87171; padding: 10px; display: block; border-radius: 4px; margin-bottom: 10px;">
					[cts_list view="classic" limit="3" show_services="false" show_description="false" show_location="false"]
				</code>
				<div style="border: 2px solid #dc2626; border-radius: 4px; padding: 15px; background: #fff;">
					<?php echo do_shortcode('[cts_list view="classic" limit="3" show_services="false" show_description="false" show_location="false"]'); ?>
				</div>
			</div>
			
			<div style="background: #fffbeb; padding: 20px; border-radius: 4px; border: 1px solid #fde68a;">
				<h4 style="margin: 0 0 15px 0;">Test OHNE Parameter (soll Defaults TRUE verwenden):</h4>
				<code style="background: #1e1e1e; color: #fbbf24; padding: 10px; display: block; border-radius: 4px; margin-bottom: 10px;">
					[cts_list view="classic" limit="3"]
				</code>
				<div style="border: 2px solid #f59e0b; border-radius: 4px; padding: 15px; background: #fff;">
					<?php echo do_shortcode('[cts_list view="classic" limit="3"]'); ?>
				</div>
			</div>
			
			<div style="background: #f0f9ff; padding: 15px; border-radius: 4px; border-left: 4px solid #2563eb; margin-top: 20px;">
				<strong>💡 Erwartetes Verhalten:</strong>
				<ul style="margin: 10px 0 0 20px; line-height: 1.8;">
					<li><strong>TRUE</strong>: Services, Beschreibung und Ort werden angezeigt</li>
					<li><strong>FALSE</strong>: Services, Beschreibung und Ort werden ausgeblendet</li>
					<li><strong>OHNE</strong>: Wie TRUE (Default-Verhalten)</li>
				</ul>
			</div>
		</div>
	</div>
	
	<!-- Live Debug Log -->
	<div class="cts-card" style="max-width: 1200px;">
		<div class="cts-card-header">
			<span class="cts-card-icon">📝</span>
			<h3>Live Debug Log</h3>
		</div>
		<div class="cts-card-body">
			<div style="margin-bottom: 15px;">
				<button type="button" class="cts-button cts-button-secondary" id="cts-reload-block-logs">
					<span>🔄</span> Log neu laden
				</button>
				<button type="button" class="cts-button cts-button-secondary" id="cts-clear-block-logs" style="margin-left: 10px;">
					<span>🗑️</span> Log löschen
				</button>
			</div>
			
			<div id="cts-block-log-content" style="
				background: #1e1e1e; 
				color: #d4d4d4; 
				padding: 20px; 
				border-radius: 4px; 
				font-family: 'Courier New', monospace; 
				font-size: 13px; 
				line-height: 1.6;
				max-height: 600px;
				overflow-y: auto;
			">
				<?php if ( ! empty( $block_logs ) ) : ?>
					<?php foreach ( $block_logs as $log ) : ?>
						<?php
						$timestamp = isset( $log['time'] ) ? date( 'H:i:s', $log['time'] ) : '';
						$level = $log['level'] ?? 'info';
						$message = $log['message'] ?? '';
						
						// Color coding
						$color = '#d4d4d4';
						$icon = '•';
						if ( strpos( $message, '🔴' ) !== false ) {
							$color = '#f48771';
							$icon = '🔴';
						} elseif ( strpos( $message, '🟢' ) !== false ) {
							$color = '#89d185';
							$icon = '🟢';
						} elseif ( strpos( $message, '🟡' ) !== false ) {
							$color = '#e5c07b';
							$icon = '🟡';
						} elseif ( strpos( $message, '⚠️' ) !== false ) {
							$color = '#e5c07b';
							$icon = '⚠️';
						}
						?>
						<div style="color: <?php echo esc_attr( $color ); ?>; margin-bottom: 8px;">
							<span style="color: #6a9fb5;">[<?php echo esc_html( $timestamp ); ?>]</span>
							<?php echo esc_html( $message ); ?>
						</div>
					<?php endforeach; ?>
				<?php else : ?>
					<div style="color: #6a9fb5; font-style: italic;">
						Keine Logs vorhanden. Füge einen Block in einer Seite ein, um Logs zu sehen.
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
	
	<!-- Instructions -->
	<div class="cts-card" style="max-width: 1200px; margin-top: 20px;">
		<div class="cts-card-header">
			<span class="cts-card-icon">ℹ️</span>
			<h3>Anleitung</h3>
		</div>
		<div class="cts-card-body">
			<ol style="line-height: 1.8;">
				<li>Öffne eine Seite oder Beitrag im <strong>Block-Editor</strong></li>
				<li>Füge einen <strong>"ChurchTools Calendar NEU"</strong> Block ein</li>
				<li>Speichere und <strong>lade die Seite im Frontend</strong></li>
				<li>Komme zurück zu diesem Tab und klicke <strong>"Log neu laden"</strong></li>
				<li>Du siehst dann die Debug-Ausgaben mit 🔴 (Registration) und 🟢 (Rendering)</li>
			</ol>
			
			<h4 style="margin-top: 20px; margin-bottom: 10px;">Log-Symbole:</h4>
			<ul style="list-style: none; padding: 0;">
				<li>🔴 <strong>Rot</strong> - Block Registration (beim Laden)</li>
				<li>🟢 <strong>Grün</strong> - Block Rendering (beim Anzeigen)</li>
				<li>🟡 <strong>Gelb</strong> - Warnungen</li>
				<li>⚠️ <strong>Warnung</strong> - Fehler oder Probleme</li>
			</ul>
		</div>
	</div>
	
</div>

<script>
(function() {
	'use strict';
	
	// Reload logs button
	const reloadBtn = document.getElementById('cts-reload-block-logs');
	if (reloadBtn) {
		reloadBtn.addEventListener('click', function() {
			location.reload();
		});
	}
	
	// Clear logs button
	const clearBtn = document.getElementById('cts-clear-block-logs');
	if (clearBtn) {
		clearBtn.addEventListener('click', function() {
			if (!confirm('Möchtest du wirklich alle Block-Logs löschen?')) {
				return;
			}
			
			clearBtn.disabled = true;
			const originalText = clearBtn.innerHTML;
			clearBtn.innerHTML = '<span>⏳</span> Lösche...';
			
			fetch(ajaxurl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: new URLSearchParams({
					action: 'cts_clear_block_logs',
					nonce: '<?php echo wp_create_nonce( 'churchtools_suite_admin' ); ?>'
				})
			})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					location.reload();
				} else {
					alert('Fehler beim Löschen: ' + (data.data.message || 'Unbekannter Fehler'));
				}
			})
			.catch(error => {
				alert('Fehler: ' + error.message);
			})
			.finally(() => {
				clearBtn.disabled = false;
				clearBtn.innerHTML = originalText;
			});
		});
	}
})();
</script>
