<?php
/**
 * Debug Tab
 *
 * @package ChurchTools_Suite
 * @since   0.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load Logger class
require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-logger.php';

global $wpdb;
$prefix = $wpdb->prefix . CHURCHTOOLS_SUITE_DB_PREFIX;
?>

<div class="cts-debug">
	
	<div class="cts-card">
		<h3><?php esc_html_e( 'System', 'churchtools-suite' ); ?></h3>
		<table class="cts-debug-table">
			<tr>
				<td><?php esc_html_e( 'Plugin Version', 'churchtools-suite' ); ?></td>
				<td><code><?php echo esc_html( CHURCHTOOLS_SUITE_VERSION ); ?></code></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'PHP Version', 'churchtools-suite' ); ?></td>
				<td><code><?php echo esc_html( phpversion() ); ?></code></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'WordPress', 'churchtools-suite' ); ?></td>
				<td><code><?php echo esc_html( get_bloginfo( 'version' ) ); ?></code></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'DB Prefix', 'churchtools-suite' ); ?></td>
				<td><code><?php echo esc_html( $prefix ); ?></code></td>
			</tr>
		</table>
	</div>

	<!-- Block Rendering Debug -->
	<?php
	$block_status = get_option( 'churchtools_suite_block_status', [] );
	$block_logs = get_option( 'churchtools_suite_block_debug_logs', [] );
	$block_logs = array_reverse( array_slice( $block_logs, -20 ) ); // Last 20 logs
	
	// Check if blocks are registered
	$has_blocks = function_exists( 'has_blocks' );
	$registered_blocks = $has_blocks ? WP_Block_Type_Registry::get_instance()->get_all_registered() : [];
	$cts_blocks = array_filter( $registered_blocks, function( $name ) {
		return strpos( $name, 'churchtools-suite/' ) === 0;
	}, ARRAY_FILTER_USE_KEY );
	?>
	
	<div class="cts-card" style="border-left: 4px solid <?php echo ! empty( $cts_blocks ) ? '#00a32a' : '#d63638'; ?>;">
		<h3 style="color: <?php echo ! empty( $cts_blocks ) ? '#00a32a' : '#d63638'; ?>;">
			<?php echo ! empty( $cts_blocks ) ? '✅' : '❌'; ?> <?php esc_html_e( 'Gutenberg Blocks', 'churchtools-suite' ); ?>
		</h3>
		
		<table class="cts-debug-table">
			<tr>
				<td><?php esc_html_e( 'Block-System verfügbar', 'churchtools-suite' ); ?></td>
				<td>
					<?php if ( $has_blocks ) : ?>
						<strong style="color: #00a32a;">✅ Ja</strong>
					<?php else : ?>
						<strong style="color: #d63638;">❌ Nein - Gutenberg nicht aktiv</strong>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Registrierte CTS-Blocks', 'churchtools-suite' ); ?></td>
				<td>
					<strong><?php echo count( $cts_blocks ); ?> Blocks</strong>
					<?php if ( ! empty( $cts_blocks ) ) : ?>
						<div style="margin-top: 8px; font-size: 12px;">
							<?php foreach ( array_keys( $cts_blocks ) as $block_name ) : ?>
								<code style="display: inline-block; margin: 2px 4px 2px 0; padding: 2px 6px; background: #f0f0f1; border-radius: 2px;">
									<?php echo esc_html( $block_name ); ?>
								</code>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</td>
			</tr>
			
			<?php if ( ! empty( $block_status ) ) : ?>
				<?php foreach ( $block_status as $block_id => $status ) : ?>
				<tr>
					<td>
						<code><?php echo esc_html( str_replace( 'churchtools-suite/', '', $block_id ) ); ?></code>
					</td>
					<td>
						<?php if ( $status['registered'] ) : ?>
							<span style="color: #00a32a; font-weight: 600;">✅ Registriert</span>
						<?php else : ?>
							<span style="color: #d63638; font-weight: 600;">❌ Registrierung fehlgeschlagen</span>
						<?php endif; ?>
						
						<?php if ( isset( $status['last_render'] ) ) : ?>
							<br>
							<small style="color: #667eea;">
								🟢 Letztes Rendering: <?php echo esc_html( date_i18n( get_option( 'time_format' ), strtotime( $status['last_render'] ) ) ); ?>
							</small>
						<?php else : ?>
							<br>
							<small style="color: #d63638;">
								⚠️ Noch nie gerendert
							</small>
						<?php endif; ?>
					</td>
				</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr>
					<td colspan="2" style="color: #8c8f94; font-style: italic;">
						<?php esc_html_e( 'Keine Block-Status-Daten verfügbar. Fügen Sie einen Block in einer Seite ein.', 'churchtools-suite' ); ?>
					</td>
				</tr>
			<?php endif; ?>
		</table>
		
		<?php if ( ! empty( $block_logs ) ) : ?>
		<div style="margin-top: 16px; padding: 16px; background: #1e1e1e; border-radius: 4px; max-height: 300px; overflow-y: auto;">
			<div style="margin-bottom: 10px; color: #d4d4d4; font-weight: 600; font-size: 13px;">
				📝 Block Debug Log (letzte 20 Einträge):
			</div>
			<?php foreach ( $block_logs as $log ) : ?>
				<?php
				$timestamp = isset( $log['time'] ) ? date( 'H:i:s', $log['time'] ) : '';
				$message = $log['message'] ?? '';
				
				// Color coding
				$color = '#d4d4d4';
				if ( strpos( $message, '🔴' ) !== false ) {
					$color = '#f48771';
				} elseif ( strpos( $message, '🟢' ) !== false ) {
					$color = '#89d185';
				} elseif ( strpos( $message, '🟡' ) !== false || strpos( $message, '⚠️' ) !== false ) {
					$color = '#e5c07b';
				}
				?>
				<div style="color: <?php echo esc_attr( $color ); ?>; font-family: 'Courier New', monospace; font-size: 12px; line-height: 1.6; margin-bottom: 4px;">
					<span style="color: #6a9fb5;">[<?php echo esc_html( $timestamp ); ?>]</span>
					<?php echo esc_html( $message ); ?>
				</div>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>
		
		<div style="margin-top: 16px; padding: 12px; background: #f0f6fc; border-radius: 3px; border-left: 3px solid #2271b1;">
			<p style="margin: 0; font-size: 13px; color: #2c3e50; line-height: 1.6;">
				<strong>🔍 Debugging-Tipp:</strong>
				Wenn Blocks registriert sind, aber <strong>"Letztes Rendering"</strong> fehlt oder alt ist:
			</p>
			<ol style="margin: 8px 0 0 20px; font-size: 13px; color: #2c3e50; line-height: 1.6;">
				<li>Erstellen Sie eine <strong>neue Seite</strong> im Block-Editor</li>
				<li>Fügen Sie einen <strong>"ChurchTools Calendar NEU"</strong> Block ein</li>
				<li>Speichern und <strong>Seite im Frontend aufrufen</strong></li>
				<li>Diese Seite neu laden und prüfen ob <strong>🟢 Grüne Logs</strong> erscheinen</li>
				<li>Wenn nur 🔴 Rot (Registration) aber keine 🟢 Grün (Rendering) Logs → <strong>render_callback wird nicht aufgerufen</strong></li>
			</ol>
		</div>
	</div>

	<div class="cts-card">
		<h3><?php esc_html_e( 'Datenbank', 'churchtools-suite' ); ?></h3>
		<table class="cts-debug-table">
			<tr>
				<td><?php esc_html_e( 'Events', 'churchtools-suite' ); ?></td>
				<td><?php echo esc_html( $wpdb->get_var( "SELECT COUNT(*) FROM {$prefix}events" ) ); ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Kalender', 'churchtools-suite' ); ?></td>
				<td><?php echo esc_html( $wpdb->get_var( "SELECT COUNT(*) FROM {$prefix}calendars" ) ); ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Services', 'churchtools-suite' ); ?></td>
				<td><?php echo esc_html( $wpdb->get_var( "SELECT COUNT(*) FROM {$prefix}event_services" ) ); ?></td>
			</tr>
		</table>
	</div>

	<?php
	// Sync Status & Errors
	$last_sync_status = get_option( 'churchtools_suite_last_sync_status', '' );
	$last_sync_error = get_option( 'churchtools_suite_last_sync_error', '' );
	$last_sync_error_time = get_option( 'churchtools_suite_last_sync_error_time', '' );
	$last_sync_stats = get_option( 'churchtools_suite_last_sync_stats', [] );
	?>

	<?php if ( $last_sync_status === 'error' && ! empty( $last_sync_error ) ) : ?>
	<div class="cts-card" style="border-left: 4px solid #d63638;">
		<h3 style="color: #d63638;">🚨 <?php esc_html_e( 'Letzter Sync-Fehler', 'churchtools-suite' ); ?></h3>
		<table class="cts-debug-table">
			<tr>
				<td><?php esc_html_e( 'Fehler', 'churchtools-suite' ); ?></td>
				<td style="color: #d63638; font-weight: 600;"><?php echo esc_html( $last_sync_error ); ?></td>
			</tr>
			<?php if ( $last_sync_error_time ) : ?>
			<tr>
				<td><?php esc_html_e( 'Zeitpunkt', 'churchtools-suite' ); ?></td>
				<td><code><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $last_sync_error_time ) ) ); ?></code></td>
			</tr>
			<?php endif; ?>
		</table>
		<div style="margin-top: 12px; padding: 12px; background: #fcf0f1; border-radius: 3px;">
			<p style="margin: 0; font-size: 13px; color: #5a2020; line-height: 1.6;">
				<strong><?php esc_html_e( 'Hinweis:', 'churchtools-suite' ); ?></strong>
				<?php esc_html_e( 'Prüfen Sie die ChurchTools-Verbindung in den Einstellungen und starten Sie einen manuellen Sync im Sync-Tab.', 'churchtools-suite' ); ?>
			</p>
		</div>
	</div>
	<?php endif; ?>

	<?php if ( $last_sync_status === 'success' && ! empty( $last_sync_stats ) ) : ?>
	<div class="cts-card" style="border-left: 4px solid #00a32a;">
		<h3 style="color: #00a32a;">✅ <?php esc_html_e( 'Letzter erfolgreicher Sync', 'churchtools-suite' ); ?></h3>
		<table class="cts-debug-table">
			<tr>
				<td><?php esc_html_e( 'Kalender', 'churchtools-suite' ); ?></td>
				<td><strong><?php echo esc_html( $last_sync_stats['calendars_processed'] ?? 0 ); ?></strong></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Events gefunden', 'churchtools-suite' ); ?></td>
				<td><strong><?php echo esc_html( $last_sync_stats['events_found'] ?? 0 ); ?></strong></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Neu hinzugefügt', 'churchtools-suite' ); ?></td>
				<td style="color: #00a32a; font-weight: 600;"><?php echo esc_html( $last_sync_stats['events_inserted'] ?? 0 ); ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Aktualisiert', 'churchtools-suite' ); ?></td>
				<td style="color: #0073aa; font-weight: 600;"><?php echo esc_html( $last_sync_stats['events_updated'] ?? 0 ); ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Übersprungen', 'churchtools-suite' ); ?></td>
				<td><?php echo esc_html( $last_sync_stats['events_skipped'] ?? 0 ); ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Services importiert', 'churchtools-suite' ); ?></td>
				<td style="color: #667eea; font-weight: 600;"><?php echo esc_html( $last_sync_stats['services_imported'] ?? 0 ); ?></td>
			</tr>
			<?php if ( ! empty( $last_sync_stats['started_at'] ) && ! empty( $last_sync_stats['completed_at'] ) ) : ?>
			<tr>
				<td><?php esc_html_e( 'Dauer', 'churchtools-suite' ); ?></td>
				<td>
					<code>
						<?php
						$start = strtotime( $last_sync_stats['started_at'] );
						$end = strtotime( $last_sync_stats['completed_at'] );
						$duration = $end - $start;
						echo esc_html( sprintf( '%d Sekunden', $duration ) );
						?>
					</code>
				</td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Abgeschlossen', 'churchtools-suite' ); ?></td>
				<td><code><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $last_sync_stats['completed_at'] ) ) ); ?></code></td>
			</tr>
			<?php endif; ?>
		</table>
	</div>
	<?php endif; ?>

	<?php
	// Sync-Historie anzeigen
	require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-sync-history-repository.php';
	$history_repo = new ChurchTools_Suite_Sync_History_Repository();
	$recent_syncs = $history_repo->get_recent( 10 );
	?>

	<div class="cts-card">
		<h3>🔧 <?php esc_html_e( 'Manuelle Cron-Trigger', 'churchtools-suite' ); ?></h3>
		<div class="cts-card-body">
			<p style="margin: 0 0 16px; color: #646970; font-size: 13px;">
				<?php esc_html_e( 'Führen Sie Cron-Jobs manuell aus, um Sync oder Session-Keepalive zu testen.', 'churchtools-suite' ); ?>
			</p>
			<div style="display: flex; gap: 12px; flex-wrap: wrap;">
				<button type="button" id="cts-trigger-manual-sync" class="cts-button cts-button-primary">
					<span>🔄</span>
					<?php esc_html_e( 'Event-Sync jetzt ausführen', 'churchtools-suite' ); ?>
				</button>
				<button type="button" id="cts-trigger-keepalive" class="cts-button cts-button-secondary">
					<span>💓</span>
					<?php esc_html_e( 'Session Keepalive', 'churchtools-suite' ); ?>
				</button>
			</div>
			<div id="cts-manual-trigger-result" style="margin-top: 16px;"></div>
		</div>
	</div>

	<div class="cts-card">
		<h3>📝 <?php esc_html_e( 'Service Import Logs', 'churchtools-suite' ); ?></h3>
		<div class="cts-card-body">
			<p style="margin: 0 0 16px; color: #646970; font-size: 13px;">
				<?php esc_html_e( 'Detaillierte Logs über den Service-Import-Prozess.', 'churchtools-suite' ); ?>
			</p>
			<div style="display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 16px;">
				<button type="button" id="cts-reload-logs" class="cts-button cts-button-primary">
					<span>🔄</span>
					<?php esc_html_e( 'Logs neu laden', 'churchtools-suite' ); ?>
				</button>
				<button type="button" id="cts-clear-logs" class="cts-button cts-button-secondary">
					<span>🗑️</span>
					<?php esc_html_e( 'Logs löschen', 'churchtools-suite' ); ?>
				</button>
			</div>
			<div id="cts-log-content" style="background: #1e1e1e; color: #d4d4d4; padding: 16px; border-radius: 4px; max-height: 400px; overflow-y: auto; font-family: 'Courier New', monospace; font-size: 12px; line-height: 1.6;">
				<?php
				$log_content = ChurchTools_Suite_Logger::get_log_content(200); // Last 200 lines
				if ( empty( $log_content ) ) {
					echo '<span style="color: #8c8f94;">Keine Logs verfügbar. Führen Sie einen Sync aus, um Logs zu generieren.</span>';
				} else {
					// Colorize log levels
					$log_content = htmlspecialchars( $log_content );
					$log_content = preg_replace( '/\[ERROR\]/', '<span style="color: #f48771; font-weight: 600;">[ERROR]</span>', $log_content );
					$log_content = preg_replace( '/\[WARNING\]/', '<span style="color: #dcdcaa; font-weight: 600;">[WARNING]</span>', $log_content );
					$log_content = preg_replace( '/\[INFO\]/', '<span style="color: #4ec9b0; font-weight: 600;">[INFO]</span>', $log_content );
					$log_content = preg_replace( '/\[DEBUG\]/', '<span style="color: #9cdcfe; font-weight: 600;">[DEBUG]</span>', $log_content );
					// Highlight block markers
					$log_content = preg_replace( '/(=== ChurchTools .* ===)/', '<span style="color: #ce9178; font-weight: 700;">$1</span>', $log_content );
					echo $log_content;
				}
				?>
			</div>
		</div>
	</div>

	<?php if ( ! empty( $recent_syncs ) ) : ?>
	<div class="cts-card">
		<h3>📊 <?php esc_html_e( 'Sync-Historie (letzte 10)', 'churchtools-suite' ); ?></h3>
		<table class="cts-events-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Datum', 'churchtools-suite' ); ?></th>
					<th><?php esc_html_e( 'Typ', 'churchtools-suite' ); ?></th>
					<th><?php esc_html_e( 'Status', 'churchtools-suite' ); ?></th>
					<th><?php esc_html_e( 'Kalender', 'churchtools-suite' ); ?></th>
					<th><?php esc_html_e( 'Events', 'churchtools-suite' ); ?></th>
					<th><?php esc_html_e( 'Neu/Akt./Über.', 'churchtools-suite' ); ?></th>
					<th><?php esc_html_e( 'Services', 'churchtools-suite' ); ?></th>
					<th><?php esc_html_e( 'Dauer', 'churchtools-suite' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $recent_syncs as $sync ) : ?>
				<tr>
					<td>
						<div style="font-size: 13px; color: #1f2937; font-weight: 500;">
							<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $sync->started_at ) ) ); ?>
						</div>
						<div style="font-size: 12px; color: #6b7280;">
							<?php echo esc_html( date_i18n( get_option( 'time_format' ), strtotime( $sync->started_at ) ) ); ?>
						</div>
					</td>
					<td>
						<span class="cts-type-badge" style="<?php echo $sync->sync_type === 'auto' ? 'background: #dbeafe; color: #1e40af;' : 'background: #fef3c7; color: #92400e;'; ?>">
							<?php echo $sync->sync_type === 'auto' ? '🤖 Auto' : '👤 Manuell'; ?>
						</span>
					</td>
					<td>
						<?php if ( $sync->status === 'success' ) : ?>
							<span style="color: #00a32a; font-weight: 600;">✅ Erfolg</span>
						<?php elseif ( $sync->status === 'error' ) : ?>
							<span style="color: #d63638; font-weight: 600;">❌ Fehler</span>
						<?php elseif ( $sync->status === 'running' ) : ?>
							<span style="color: #0073aa; font-weight: 600;">⏳ Läuft</span>
						<?php else : ?>
							<span style="color: #8c8f94;">⌛ Ausstehend</span>
						<?php endif; ?>
					</td>
					<td><strong><?php echo esc_html( $sync->calendars_processed ); ?></strong></td>
					<td><strong><?php echo esc_html( $sync->events_found ); ?></strong></td>
					<td>
						<span style="color: #00a32a;"><?php echo esc_html( $sync->events_inserted ); ?></span> /
						<span style="color: #0073aa;"><?php echo esc_html( $sync->events_updated ); ?></span> /
						<span style="color: #8c8f94;"><?php echo esc_html( $sync->events_skipped ); ?></span>
					</td>
					<td>
						<?php if ( isset( $sync->services_imported ) && $sync->services_imported > 0 ) : ?>
							<strong style="color: #667eea;"><?php echo esc_html( $sync->services_imported ); ?></strong>
						<?php else : ?>
							<span style="color: #8c8f94;">—</span>
						<?php endif; ?>
					</td>
					<td>
						<?php if ( $sync->duration_seconds ) : ?>
							<code><?php echo esc_html( $sync->duration_seconds ); ?>s</code>
						<?php else : ?>
							<span style="color: #8c8f94;">—</span>
						<?php endif; ?>
					</td>
				</tr>
				<?php if ( $sync->status === 'error' && ! empty( $sync->error_message ) ) : ?>
				<tr>
					<td colspan="7" style="padding: 8px 16px; background: #fcf0f1; border-left: 4px solid #d63638;">
						<strong style="color: #d63638;">Fehler:</strong>
						<span style="color: #5a2020; font-size: 12px;"><?php echo esc_html( $sync->error_message ); ?></span>
					</td>
				</tr>
				<?php endif; ?>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php endif; ?>

</div>
