<?php
/**
 * Dashboard Tab
 *
 * @package ChurchTools_Suite
 * @since   0.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Status prüfen
$ct_url = get_option( 'churchtools_suite_ct_url', '' );
$ct_username = get_option( 'churchtools_suite_ct_username', '' );
$ct_password = get_option( 'churchtools_suite_ct_password', '' );
$ct_cookies = get_option( 'churchtools_suite_ct_cookies', [] );
$ct_last_login = get_option( 'churchtools_suite_ct_last_login', '' );
$is_configured = ! empty( $ct_url ) && ! empty( $ct_username ) && ! empty( $ct_password );
$is_connected = ! empty( $ct_cookies );

// Statistiken
global $wpdb;
$prefix = $wpdb->prefix . CHURCHTOOLS_SUITE_DB_PREFIX;
$events_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$prefix}events" );
$calendars_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$prefix}calendars WHERE is_selected = 1" );
?>

<div class="cts-dashboard">
	
	<!-- Dashboard Header mit One-Click-Actions -->
	<div class="cts-section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
		<div>
			<h2><?php esc_html_e( 'Dashboard', 'churchtools-suite' ); ?></h2>
			<p class="cts-section-description"><?php esc_html_e( 'Übersicht über den aktuellen Status der ChurchTools-Integration.', 'churchtools-suite' ); ?></p>
		</div>
		<?php if ( $is_connected ) : ?>
		<div style="display: flex; gap: 10px; align-items: center;">
			<button id="cts-sync-now" class="cts-button cts-button-primary" style="font-size: 16px; padding: 12px 24px;">
				🔄 <?php esc_html_e( 'Jetzt synchronisieren', 'churchtools-suite' ); ?>
			</button>
			<a href="?page=churchtools-suite&tab=debug" class="cts-button cts-button-secondary" style="padding: 12px 20px;">
				📊 <?php esc_html_e( 'Sync-Logs', 'churchtools-suite' ); ?>
			</a>
			<div id="cts-sync-result" style="margin-left:12px; font-size:13px; color:#333; display:none;"></div>
		</div>
		<?php endif; ?>
	</div>
	</div>

	<?php
	// Show update notice if an update is available (cached by transient if desired)
	$update_info = null;
	if ( class_exists( 'ChurchTools_Suite_Auto_Updater' ) ) {
		$info = ChurchTools_Suite_Auto_Updater::get_latest_release_info();
		if ( ! is_wp_error( $info ) ) {
			$update_info = $info;
		}
	}

	if ( is_array( $update_info ) && ! empty( $update_info['is_update'] ) ) :
	?>
	<div class="cts-card" style="border-left:4px solid #2d7bf6; margin-top:16px;">
		<div class="cts-card-header">
			<span class="cts-card-icon">⬆️</span>
			<h3><?php esc_html_e( 'Update verfügbar', 'churchtools-suite' ); ?></h3>
		</div>
		<div class="cts-card-body">
			<p style="margin:0 0 8px;"><strong><?php echo esc_html( $update_info['tag_name'] ?? $update_info['latest_version'] ); ?></strong> — <?php esc_html_e( 'Neue Version verfügbar', 'churchtools-suite' ); ?></p>
			<?php if ( ! empty( $update_info['html_url'] ) ) : ?>
				<p style="margin:0 0 8px; font-size:13px;"><a href="<?php echo esc_url( $update_info['html_url'] ); ?>" target="_blank"><?php esc_html_e( 'Release Notes anzeigen', 'churchtools-suite' ); ?></a></p>
			<?php endif; ?>
			<p style="margin:0; font-size:13px; color:#444;">
				<?php esc_html_e( 'Sie können das Update manuell installieren oder die automatische Installation in den Einstellungen konfigurieren.', 'churchtools-suite' ); ?>
			</p>
		</div>
		<div class="cts-card-footer">
			<button id="cts_install_update_btn" class="cts-button cts-button-danger"><?php esc_html_e( 'Update installieren', 'churchtools-suite' ); ?></button>
			<a href="?page=churchtools-suite&tab=settings" class="cts-button" style="margin-left:8px;"><?php esc_html_e( 'Einstellungen', 'churchtools-suite' ); ?></a>
		</div>
	</div>
	<?php endif; ?>
	<!-- Status Cards -->
	<div class="cts-grid cts-grid-3">
		
		<!-- ChurchTools Verbindung -->
		<div class="cts-card">
			<div class="cts-card-header">
				<span class="cts-card-icon">☁️</span>
				<h3><?php esc_html_e( 'ChurchTools', 'churchtools-suite' ); ?></h3>
			</div>
			<div class="cts-card-body">
				<?php if ( $is_connected ) : ?>
					<p class="cts-status cts-status-success">
						<span class="cts-status-indicator"></span>
						<?php esc_html_e( 'Verbunden', 'churchtools-suite' ); ?>
					</p>
					<p class="cts-card-detail"><?php echo esc_html( parse_url( $ct_url, PHP_URL_HOST ) ?: $ct_url ); ?></p>
					<?php if ( $ct_last_login ) : ?>
						<p class="cts-card-meta"><?php echo esc_html( sprintf( __( 'Letzter Login: %s', 'churchtools-suite' ), date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $ct_last_login ) ) ) ); ?></p>
					<?php endif; ?>
				<?php elseif ( $is_configured ) : ?>
					<p class="cts-status cts-status-inactive">
						<span class="cts-status-indicator"></span>
						<?php esc_html_e( 'Konfiguriert', 'churchtools-suite' ); ?>
					</p>
					<p class="cts-card-detail"><?php echo esc_html( parse_url( $ct_url, PHP_URL_HOST ) ?: $ct_url ); ?></p>
					<p class="cts-card-meta"><?php esc_html_e( 'Verbindung noch nicht getestet', 'churchtools-suite' ); ?></p>
				<?php else : ?>
					<p class="cts-status cts-status-error">
						<span class="cts-status-indicator"></span>
						<?php esc_html_e( 'Nicht konfiguriert', 'churchtools-suite' ); ?>
					</p>
					<p class="cts-card-detail"><?php esc_html_e( 'Bitte ChurchTools-Zugangsdaten eingeben', 'churchtools-suite' ); ?></p>
				<?php endif; ?>
			</div>
			<div class="cts-card-footer">
				<a href="?page=churchtools-suite&tab=settings" class="cts-button cts-button-secondary">
					⚙️ <?php esc_html_e( 'Einstellungen', 'churchtools-suite' ); ?>
				</a>
			</div>
		</div>

		<!-- Automatischer Sync -->
		<?php
		$auto_sync_enabled = get_option( 'churchtools_suite_auto_sync_enabled', 0 );
		$last_sync_status = get_option( 'churchtools_suite_last_sync_status', '' );
		$last_sync_error = get_option( 'churchtools_suite_last_sync_error', '' );
		$last_sync_error_time = get_option( 'churchtools_suite_last_sync_error_time', '' );
		$last_sync_stats = get_option( 'churchtools_suite_last_sync_stats', [] );
		$auto_sync_interval = get_option( 'churchtools_suite_auto_sync_interval', 'daily' );
		
		// Intervall-Namen
		$interval_names = [
			'daily' => __( 'Täglich', 'churchtools-suite' ),
			'cts_2days' => __( 'Alle 2 Tage', 'churchtools-suite' ),
			'cts_3days' => __( 'Alle 3 Tage', 'churchtools-suite' ),
			'cts_weekly' => __( 'Wöchentlich', 'churchtools-suite' ),
			'cts_2weeks' => __( 'Alle 2 Wochen', 'churchtools-suite' ),
			'cts_monthly' => __( 'Monatlich', 'churchtools-suite' ),
		];
		?>
		<div class="cts-card">
			<div class="cts-card-header">
				<span class="cts-card-icon">🔄</span>
				<h3><?php esc_html_e( 'Automatischer Sync', 'churchtools-suite' ); ?></h3>
			</div>
			<div class="cts-card-body">
				<?php if ( $auto_sync_enabled ) : ?>
					<?php if ( $last_sync_status === 'error' && ! empty( $last_sync_error ) ) : ?>
						<p class="cts-status cts-status-error">
							<span class="cts-status-indicator"></span>
							<?php esc_html_e( 'Fehler beim letzten Sync', 'churchtools-suite' ); ?>
						</p>
						<p class="cts-card-detail" style="color: #d63638; font-size: 13px; margin-bottom: 8px;">
							<?php echo esc_html( $last_sync_error ); ?>
						</p>
						<?php if ( $last_sync_error_time ) : ?>
							<p class="cts-card-meta" style="color: #d63638;">
								<?php echo esc_html( sprintf( __( 'Fehler: %s', 'churchtools-suite' ), date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $last_sync_error_time ) ) ) ); ?>
							</p>
						<?php endif; ?>					<p class="cts-card-meta" style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #f0f0f1;">
						<small><?php printf(
							esc_html__('Manueller Trigger im %sDebug-Tab%s verfügbar', 'churchtools-suite'),
							'<a href="?page=churchtools-suite&tab=debug">',
							'</a>'
						); ?></small>
					</p>					<?php elseif ( $last_sync_status === 'success' && ! empty( $last_sync_stats ) ) : ?>
						<p class="cts-status cts-status-success">
							<span class="cts-status-indicator"></span>
							<?php echo esc_html( sprintf( __( 'Aktiv (%s)', 'churchtools-suite' ), $interval_names[ $auto_sync_interval ] ?? $auto_sync_interval ) ); ?>
						</p>
						<p class="cts-card-detail">
							<?php
							// Show sync type badge (v0.7.1.0)
							$sync_type = $last_sync_stats['sync_type'] ?? 'full';
							$sync_type_label = $sync_type === 'incremental' ? __( 'INKREMENTELL', 'churchtools-suite' ) : __( 'VOLL', 'churchtools-suite' );
							$sync_type_color = $sync_type === 'incremental' ? '#00a32a' : '#2271b1';
							?>
							<span style="display: inline-block; padding: 2px 8px; font-size: 11px; font-weight: 600; color: white; background: <?php echo esc_attr( $sync_type_color ); ?>; border-radius: 3px; margin-right: 6px;">
								<?php echo esc_html( $sync_type_label ); ?>
							</span>
							<?php
							echo esc_html( sprintf(
								__( '%d Events gefunden, %d neu, %d aktualisiert', 'churchtools-suite' ),
								$last_sync_stats['events_found'] ?? 0,
								$last_sync_stats['events_inserted'] ?? 0,
								$last_sync_stats['events_updated'] ?? 0
							) );
							
							// Show unchanged/deleted counts if incremental (v0.7.1.0)
							if ( $sync_type === 'incremental' ) {
								$unchanged = $last_sync_stats['events_unchanged'] ?? 0;
								$deleted = $last_sync_stats['events_deleted'] ?? 0;
								if ( $unchanged > 0 || $deleted > 0 ) {
									echo ' <span style="color: #646970; font-size: 12px;">';
									if ( $unchanged > 0 ) {
										echo esc_html( sprintf( __( '(%d unverändert)', 'churchtools-suite' ), $unchanged ) );
									}
									if ( $deleted > 0 ) {
										echo esc_html( sprintf( __( ' (%d gelöscht)', 'churchtools-suite' ), $deleted ) );
									}
									echo '</span>';
								}
							}
							?>
						</p>
						<?php if ( ! empty( $last_sync_stats['completed_at'] ) ) : ?>
							<p class="cts-card-meta">
								<?php echo esc_html( sprintf( __( 'Letzter Sync: %s', 'churchtools-suite' ), date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $last_sync_stats['completed_at'] ) ) ) ); ?>
							</p>
						<?php endif; ?>					<?php
					// Nächster geplanter Sync berechnen
					$next_scheduled = wp_next_scheduled('churchtools_suite_auto_sync');
					if ( $next_scheduled ) :
						if ( $next_scheduled < time() ) :
							$diff = human_time_diff( $next_scheduled, time() );
							?>
							<p class="cts-card-meta" style="margin-top: 8px; color:#d66;">
								<?php echo esc_html( sprintf( __( 'Nächster Sync (überfällig seit %s): %s', 'churchtools-suite' ), $diff, date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $next_scheduled ) ) ); ?>
							</p>
						<?php else : ?>
							<p class="cts-card-meta" style="margin-top: 8px;">
								<?php echo esc_html( sprintf( __( 'Nächster Sync: %s', 'churchtools-suite' ), date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $next_scheduled ) ) ); ?>
							</p>
						<?php endif; ?>
					<?php endif; ?>
					<p class="cts-card-meta" style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #f0f0f1;">
						<small><?php printf(
							esc_html__('Manueller Trigger im %sDebug-Tab%s verfügbar', 'churchtools-suite'),
							'<a href="?page=churchtools-suite&tab=debug">',
							'</a>'
						); ?></small>
					</p>					<?php else : ?>
						<p class="cts-status cts-status-success">
							<span class="cts-status-indicator"></span>
							<?php echo esc_html( sprintf( __( 'Aktiv (%s)', 'churchtools-suite' ), $interval_names[ $auto_sync_interval ] ?? $auto_sync_interval ) ); ?>
						</p>
						<p class="cts-card-detail"><?php esc_html_e( 'Wartet auf ersten automatischen Sync', 'churchtools-suite' ); ?></p>					<?php
					// Nächster geplanter Sync berechnen
					$next_scheduled = wp_next_scheduled('churchtools_suite_auto_sync');
					if ( $next_scheduled ) :
						if ( $next_scheduled < time() ) :
							$diff = human_time_diff( $next_scheduled, time() );
							?>
							<p class="cts-card-meta" style="color:#d66;">
								<?php echo esc_html( sprintf( __( 'Nächster Sync (überfällig seit %s): %s', 'churchtools-suite' ), $diff, date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $next_scheduled ) ) ); ?>
							</p>
						<?php else : ?>
							<p class="cts-card-meta">
								<?php echo esc_html( sprintf( __( 'Nächster Sync: %s', 'churchtools-suite' ), date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $next_scheduled ) ) ); ?>
							</p>
						<?php endif; ?>
					<?php endif; ?>
					<p class="cts-card-meta" style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #f0f0f1;">
						<small><?php printf(
							esc_html__('Manueller Trigger im %sDebug-Tab%s verfügbar', 'churchtools-suite'),
							'<a href="?page=churchtools-suite&tab=debug">',
							'</a>'
						); ?></small>
					</p>					<?php endif; ?>
				<?php else : ?>
					<p class="cts-status cts-status-inactive">
						<span class="cts-status-indicator"></span>
						<?php esc_html_e( 'Deaktiviert', 'churchtools-suite' ); ?>
					</p>
					<p class="cts-card-detail"><?php esc_html_e( 'Automatische Synchronisation ist ausgeschaltet', 'churchtools-suite' ); ?></p>
				<?php endif; ?>
			</div>
			<div class="cts-card-footer">
				<a href="?page=churchtools-suite&tab=settings#auto-sync" class="cts-button cts-button-secondary">
					⚙️ <?php esc_html_e( 'Konfigurieren', 'churchtools-suite' ); ?>
				</a>
			</div>
		</div>

		<!-- Synchronisation -->
		<div class="cts-card">
			<div class="cts-card-header">
				<span class="cts-card-icon">📅</span>
				<h3><?php esc_html_e( 'Synchronisation', 'churchtools-suite' ); ?></h3>
			</div>
			<div class="cts-card-body">
				<p class="cts-stat-number"><?php echo esc_html( $events_count ); ?></p>
				<p class="cts-card-detail">
					<?php
					printf(
						esc_html__( 'Termine gesamt, %s Kalender ausgewählt', 'churchtools-suite' ),
						esc_html( $calendars_count )
					);
					?>
				</p>
			</div>
			<div class="cts-card-footer">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=churchtools-suite-data&subtab=events' ) ); ?>" class="cts-button cts-button-secondary">
					📅 <?php esc_html_e( 'Termine anzeigen', 'churchtools-suite' ); ?>
				</a>
			</div>
		</div>

	</div>

	<!-- System Info -->
	<div class="cts-card cts-system-card">
		<div class="cts-card-header">
			<span class="cts-card-icon">ℹ️</span>
			<h3><?php esc_html_e( 'System', 'churchtools-suite' ); ?></h3>
		</div>
		<div class="cts-card-body">
			<table class="cts-system-table">
				<tr>
					<td><?php esc_html_e( 'Plugin-Version', 'churchtools-suite' ); ?></td>
					<td><strong><?php echo esc_html( CHURCHTOOLS_SUITE_VERSION ); ?></strong></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'WordPress-Version', 'churchtools-suite' ); ?></td>
					<td><strong><?php echo esc_html( get_bloginfo( 'version' ) ); ?></strong></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'PHP-Version', 'churchtools-suite' ); ?></td>
					<td><strong><?php echo esc_html( PHP_VERSION ); ?></strong></td>
				</tr>
			</table>
		</div>
		<div class="cts-card-footer">
			<a href="?page=churchtools-suite&tab=debug" class="cts-button cts-button-secondary">
				🔧 <?php esc_html_e( 'Debug-Info', 'churchtools-suite' ); ?>
			</a>
		</div>
	</div>

	<!-- WP-Cron Warnung -->
	<?php if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) : ?>
	<div style="margin-top: 20px; padding: 16px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 4px; max-width: 800px;">
		<h4 style="margin: 0 0 10px; color: #856404; font-size: 15px;">
			⚠️ <?php esc_html_e( 'WP-Cron ist deaktiviert', 'churchtools-suite' ); ?>
		</h4>
		<p style="margin: 0 0 10px; color: #856404; font-size: 13px; line-height: 1.6;">
			<?php esc_html_e( 'Die automatische Synchronisation ist nicht aktiv, da WP-Cron in Ihrer Konfiguration deaktiviert wurde. Bitte richten Sie einen System-Cron ein oder aktivieren Sie WP-Cron.', 'churchtools-suite' ); ?>
		</p>
		<a href="?page=churchtools-suite&tab=settings#auto-sync" class="cts-button cts-button-secondary" style="margin-top: 8px;">
			<?php esc_html_e( 'System-Cron Anleitung anzeigen', 'churchtools-suite' ); ?>
		</a>
	</div>
	<?php endif; ?>

	<!-- Quick Start -->
	<?php if ( ! $is_configured ) : ?>
	<div class="cts-card cts-quick-start">
		<h3><?php esc_html_e( 'Quick Start', 'churchtools-suite' ); ?></h3>
		<ol>
			<li><?php printf( esc_html__( 'ChurchTools-URL und API-Token in den %sEinstellungen%s hinterlegen', 'churchtools-suite' ), '<a href="?page=churchtools-suite&tab=settings">', '</a>' ); ?></li>
			<li><?php esc_html_e( 'Kalender auswählen und synchronisieren', 'churchtools-suite' ); ?></li>
			<li><?php esc_html_e( 'Events per Shortcode im Frontend anzeigen', 'churchtools-suite' ); ?></li>
		</ol>
	</div>
	<?php endif; ?>

</div>

<script>
(function(){
	'use strict';
	const btn = document.getElementById('cts-sync-now');
	const result = document.getElementById('cts-sync-result');
	if (!btn) return;

	btn.addEventListener('click', function(){
		if (!confirm('<?php echo esc_js( __( 'Einen manuellen Sync jetzt starten? Dies kann einige Zeit dauern.', 'churchtools-suite' ) ); ?>')) {
			return;
		}

		btn.disabled = true;
		const original = btn.innerHTML;
		btn.innerHTML = '⏳ ' + '<?php echo esc_js( __( 'Synchronisiere...', 'churchtools-suite' ) ); ?>';
		if (result) { result.style.display = 'inline-block'; result.innerHTML = ''; }

		fetch( churchtoolsSuite.ajaxUrl, {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: new URLSearchParams({ action: 'cts_trigger_manual_sync', nonce: churchtoolsSuite.nonce })
		}).then(r => r.json()).then(data => {
			if (data.success) {
				if (result) result.innerHTML = '<span style="color:#0a0">' + (data.data.message || '✅ Synchronisation abgeschlossen') + '</span>';
			} else {
				if (result) result.innerHTML = '<span style="color:#d63638">' + (data.data?.message || data.message || 'Fehler beim Sync') + '</span>';
			}
		}).catch(err => {
			if (result) result.innerHTML = '<span style="color:#d63638">Fehler: ' + err.message + '</span>';
		}).finally(() => {
			btn.disabled = false;
			btn.innerHTML = original;
		});
	});
})();
</script>

<script>
(function(){
	'use strict';
	var installBtn = document.getElementById('cts_install_update_btn');
	if (!installBtn) return;
	installBtn.addEventListener('click', function(){
		if (!confirm('<?php echo esc_js( __( 'Update jetzt installieren? Dies überschreibt Plugin-Dateien.', 'churchtools-suite' ) ); ?>')) return;
		installBtn.disabled = true;
		var orig = installBtn.innerHTML;
		installBtn.innerHTML = '⏳ <?php echo esc_js( __( 'Installiere...', 'churchtools-suite' ) ); ?>';

		fetch( churchtoolsSuite.ajaxUrl, {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: new URLSearchParams({ action: 'cts_run_update', nonce: churchtoolsSuite.nonce })
		}).then(r => r.json()).then(function(data){
			if (data.success) {
				alert( data.data && data.data.message ? data.data.message : '<?php echo esc_js( __( 'Update gestartet', 'churchtools-suite' ) ); ?>' );
			} else {
				alert( data.data && data.data.message ? data.data.message : (data.message || '<?php echo esc_js( __( 'Fehler beim Update', 'churchtools-suite' ) ); ?>') );
			}
		}).catch(function(err){
			alert('Netzwerkfehler: ' + err.message);
		}).finally(function(){
			installBtn.disabled = false;
			installBtn.innerHTML = orig;
		});
	});
})();
</script>
