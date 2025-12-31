<?php
/**
 * Settings Tab
 *
 * @package ChurchTools_Suite
 * @since   0.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Formular verarbeiten
if ( isset( $_POST['cts_save_settings'] ) && check_admin_referer( 'cts_settings' ) ) {
	$tenant = sanitize_text_field( $_POST['ct_tenant'] ?? '' );
	// Vollständige URL aus Tenant-Namen erstellen
	$full_url = ! empty( $tenant ) ? 'https://' . $tenant . '.church.tools' : '';
	
	update_option( 'churchtools_suite_ct_url', $full_url );
	update_option( 'churchtools_suite_ct_username', sanitize_email( $_POST['ct_username'] ?? '' ) );
	update_option( 'churchtools_suite_ct_password', sanitize_text_field( $_POST['ct_password'] ?? '' ) );
	
	// Sync-Einstellungen
	update_option( 'churchtools_suite_sync_days_past', absint( $_POST['sync_days_past'] ?? 7 ) );
	update_option( 'churchtools_suite_sync_days_future', absint( $_POST['sync_days_future'] ?? 90 ) );
	
	// Auto-Sync Einstellungen
	$auto_sync_enabled = isset( $_POST['auto_sync_enabled'] ) ? 1 : 0;
	update_option( 'churchtools_suite_auto_sync_enabled', $auto_sync_enabled );
	update_option( 'churchtools_suite_auto_sync_interval', sanitize_text_field( $_POST['auto_sync_interval'] ?? 'hourly' ) );
	
	// Advanced Mode
	$advanced_mode = isset( $_POST['advanced_mode'] ) ? 1 : 0;
	update_option( 'churchtools_suite_advanced_mode', $advanced_mode );
	
	// Cron-Job aktualisieren
	require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-cron.php';
	ChurchTools_Suite_Cron::update_sync_schedule();
	
	echo '<div class="cts-notice cts-notice-success"><p>' . esc_html__( 'Einstellungen gespeichert.', 'churchtools-suite' ) . '</p></div>';
}

$ct_url = get_option( 'churchtools_suite_ct_url', '' );
// Tenant-Namen aus URL extrahieren
$ct_tenant = '';
if ( ! empty( $ct_url ) ) {
	$parsed = parse_url( $ct_url );
	if ( isset( $parsed['host'] ) ) {
		$ct_tenant = str_replace( '.church.tools', '', $parsed['host'] );
	}
}
$ct_username = get_option( 'churchtools_suite_ct_username', '' );
$ct_password = get_option( 'churchtools_suite_ct_password', '' );
$sync_days_past = get_option( 'churchtools_suite_sync_days_past', 7 );
$sync_days_future = get_option( 'churchtools_suite_sync_days_future', 90 );
$auto_sync_enabled = get_option( 'churchtools_suite_auto_sync_enabled', 0 );
$auto_sync_interval = get_option( 'churchtools_suite_auto_sync_interval', 'hourly' );
$last_auto_sync = get_option( 'churchtools_suite_last_auto_sync', '' );
$advanced_mode = get_option( 'churchtools_suite_advanced_mode', 0 );

// Determine active sub-tab
$active_subtab = isset( $_GET['subtab'] ) ? sanitize_key( $_GET['subtab'] ) : 'api';
?>

<div class="cts-settings">
	
	<!-- Sub-Navigation -->
	<?php
	$subtabs = array(
		'api' => __( 'API & Verbindung', 'churchtools-suite' ),
		'sync' => __( 'Synchronisation', 'churchtools-suite' ),
		'calendars' => __( 'Kalender', 'churchtools-suite' ),
		'services' => __( 'Services', 'churchtools-suite' ),
		'advanced' => __( 'Erweitert', 'churchtools-suite' ),
	);
	$subtab_active = $active_subtab;
	$subtab_parent_tab = 'settings';
	include __DIR__ . '/partials/render-subtabs.php';
	?>
	
	<?php
	switch ( $active_subtab ) {
		case 'sync':
			include __DIR__ . '/settings/subtab-sync.php';
			break;
		case 'calendars':
			include __DIR__ . '/../tab-calendars.php';
			break;
		case 'services':
			include __DIR__ . '/../tab-services.php';
			break;
		case 'advanced':
			include __DIR__ . '/settings/subtab-advanced.php';
			break;
		case 'api':
		default:
			include __DIR__ . '/settings/subtab-api.php';
			break;
	}
	?>
	
</div>
		
		<div class="cts-card">
			<h3><?php esc_html_e( 'ChurchTools API', 'churchtools-suite' ); ?></h3>
			
			<table class="cts-form-table">
				<tr>
					<th scope="row">
						<label for="ct_tenant"><?php esc_html_e( 'ChurchTools Tenant', 'churchtools-suite' ); ?></label>
					</th>
					<td>
						<div style="display: flex; align-items: center; gap: 8px; max-width: 450px;">
							<span style="color: #646970; font-size: 13px; white-space: nowrap;">https://</span>
							<input type="text" 
								   id="ct_tenant" 
								   name="ct_tenant" 
								   value="<?php echo esc_attr( $ct_tenant ); ?>" 
								   class="cts-form-input"
								   style="max-width: none; flex: 1;"
								   placeholder="ihre-gemeinde">
							<span style="color: #646970; font-size: 13px; white-space: nowrap;">.church.tools</span>
						</div>
						<span class="cts-form-description"><?php esc_html_e( 'Nur der Name Ihrer ChurchTools-Instanz (ohne https:// und .church.tools)', 'churchtools-suite' ); ?></span>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="ct_username"><?php esc_html_e( 'Benutzername', 'churchtools-suite' ); ?></label>
					</th>
					<td>
						<input type="email" 
							   id="ct_username" 
							   name="ct_username" 
							   value="<?php echo esc_attr( $ct_username ); ?>" 
							   class="cts-form-input"
							   placeholder="<?php esc_attr_e( 'ihre.email@gemeinde.de', 'churchtools-suite' ); ?>">
						<span class="cts-form-description"><?php esc_html_e( 'Ihre E-Mail-Adresse für ChurchTools', 'churchtools-suite' ); ?></span>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="ct_password"><?php esc_html_e( 'Passwort', 'churchtools-suite' ); ?></label>
					</th>
					<td>
						<input type="password" 
							   id="ct_password" 
							   name="ct_password" 
							   value="<?php echo esc_attr( $ct_password ); ?>" 
							   class="cts-form-input"
							   placeholder="<?php esc_attr_e( 'Ihr ChurchTools Passwort', 'churchtools-suite' ); ?>">
						<span class="cts-form-description"><?php esc_html_e( 'Ihr Passwort für ChurchTools', 'churchtools-suite' ); ?></span>
					</td>
				</tr>
			</table>
		</div>
		
		<div class="cts-card" style="margin-top: 20px;">
			<h3><?php esc_html_e( 'Synchronisations-Einstellungen', 'churchtools-suite' ); ?></h3>
			
			<table class="cts-form-table">
				<tr>
					<th scope="row">
						<label for="sync_days_past"><?php esc_html_e( 'Vergangene Tage', 'churchtools-suite' ); ?></label>
					</th>
					<td>
						<input type="number" 
							   id="sync_days_past" 
							   name="sync_days_past" 
						   value="<?php echo esc_attr( $sync_days_past ); ?>" 
							   class="cts-form-input"
							   min="0"
							   max="365"
							   style="max-width: 120px;">
						<span class="cts-form-description"><?php esc_html_e( 'Wie viele Tage in der Vergangenheit sollen synchronisiert werden? (Standard: 7)', 'churchtools-suite' ); ?></span>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="sync_days_future"><?php esc_html_e( 'Zukünftige Tage', 'churchtools-suite' ); ?></label>
					</th>
					<td>
						<input type="number" 
							   id="sync_days_future" 
							   name="sync_days_future" 
						   value="<?php echo esc_attr( $sync_days_future ); ?>" 
							   class="cts-form-input"
							   min="1"
							   max="730"
							   style="max-width: 120px;">
						<span class="cts-form-description"><?php esc_html_e( 'Wie viele Tage in der Zukunft sollen synchronisiert werden? (Standard: 90)', 'churchtools-suite' ); ?></span>
					</td>
				</tr>
			</table>
			
			<?php
			// Berechne und zeige die konkreten Daten an (v0.7.3.1)
			$from_date = date_i18n( get_option( 'date_format' ), current_time( 'timestamp' ) - absint( $sync_days_past ) * DAY_IN_SECONDS );
			$to_date = date_i18n( get_option( 'date_format' ), current_time( 'timestamp' ) + absint( $sync_days_future ) * DAY_IN_SECONDS );
			?>
			
			<div class="cts-info" style="margin-top: 15px; padding: 12px; background: #f0f6fc; border-left: 4px solid #0073aa;">
				<p style="margin: 0 0 8px 0;">
					<strong>📅 Berechneter Zeitraum:</strong>
				</p>
				<p style="margin: 0; font-family: monospace; font-size: 14px;">
					<strong>Von:</strong> <?php echo esc_html( $from_date ); ?> 
					<span style="color: #646970; margin: 0 8px;">|</span>
					<strong>Bis:</strong> <?php echo esc_html( $to_date ); ?>
				</p>
				<p style="margin: 8px 0 0 0; font-size: 12px; color: #646970;">
					<?php esc_html_e( 'Dieser Zeitraum wird bei der nächsten Synchronisation verwendet.', 'churchtools-suite' ); ?>
				</p>
			</div>
			
			<div class="cts-info" style="margin-top: 15px; padding: 12px; background: #f0f6fc; border-left: 4px solid #0073aa;">
				<p style="margin: 0;">
					<strong>ℹ️ Hinweis:</strong> 
					<?php esc_html_e( 'Diese Einstellungen bestimmen, welcher Zeitraum bei der Synchronisation von Terminen berücksichtigt wird. Ein größerer Zeitraum bedeutet mehr Termine, aber auch längere Sync-Zeiten.', 'churchtools-suite' ); ?>
				</p>
			</div>
		</div>
		
		<div class="cts-card" style="margin-top: 20px;">
			<h3><?php esc_html_e( 'Automatische Synchronisation', 'churchtools-suite' ); ?></h3>
			
			<table class="cts-form-table">
				<tr>
					<th scope="row">
						<label for="auto_sync_enabled"><?php esc_html_e( 'Auto-Sync aktivieren', 'churchtools-suite' ); ?></label>
					</th>
					<td>
						<label class="cts-toggle">
							<input type="checkbox" 
								   id="auto_sync_enabled" 
								   name="auto_sync_enabled" 
								   value="1" 
								   <?php checked( $auto_sync_enabled, 1 ); ?>>
							<span class="cts-toggle-slider"></span>
						</label>
						<span class="cts-form-description"><?php esc_html_e( 'Termine automatisch im Hintergrund synchronisieren', 'churchtools-suite' ); ?></span>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="auto_sync_interval"><?php esc_html_e( 'Synchronisations-Intervall', 'churchtools-suite' ); ?></label>
					</th>
					<td>
						<select id="auto_sync_interval" 
								name="auto_sync_interval" 
								class="cts-form-input"
								style="max-width: 250px;"
								<?php disabled( ! $auto_sync_enabled ); ?>>
							<option value="daily" <?php selected( $auto_sync_interval, 'daily' ); ?>><?php esc_html_e( 'Täglich (empfohlen)', 'churchtools-suite' ); ?></option>
							<option value="cts_2days" <?php selected( $auto_sync_interval, 'cts_2days' ); ?>><?php esc_html_e( 'Alle 2 Tage', 'churchtools-suite' ); ?></option>
							<option value="cts_3days" <?php selected( $auto_sync_interval, 'cts_3days' ); ?>><?php esc_html_e( 'Alle 3 Tage', 'churchtools-suite' ); ?></option>
							<option value="cts_weekly" <?php selected( $auto_sync_interval, 'cts_weekly' ); ?>><?php esc_html_e( 'Wöchentlich', 'churchtools-suite' ); ?></option>
							<option value="cts_2weeks" <?php selected( $auto_sync_interval, 'cts_2weeks' ); ?>><?php esc_html_e( 'Alle 2 Wochen', 'churchtools-suite' ); ?></option>
							<option value="cts_monthly" <?php selected( $auto_sync_interval, 'cts_monthly' ); ?>><?php esc_html_e( 'Monatlich', 'churchtools-suite' ); ?></option>
						</select>
						<span class="cts-form-description"><?php esc_html_e( 'Empfohlen: Täglich oder alle 2-3 Tage für regelmäßige Termine', 'churchtools-suite' ); ?></span>
					</td>
				</tr>
				<?php if ( ! empty( $last_auto_sync ) ) : ?>
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Letzte Auto-Sync', 'churchtools-suite' ); ?>
					</th>
					<td>
						<span style="color: #50575e; font-weight: 500;">
							<?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $last_auto_sync ) ) ); ?>
						</span>
						<span class="cts-form-description"><?php esc_html_e( 'Zeitpunkt der letzten automatischen Synchronisation', 'churchtools-suite' ); ?></span>
					</td>
				</tr>
				<?php endif; ?>
			</table>
			
			<?php if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) : ?>
			<div style="margin-top: 20px; padding: 16px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
				<h4 style="margin: 0 0 10px; color: #856404; font-size: 14px;">
					⚠️ <?php esc_html_e( 'WP-Cron ist deaktiviert', 'churchtools-suite' ); ?>
				</h4>
				<p style="margin: 0 0 10px; color: #856404; font-size: 13px; line-height: 1.6;">
					<?php esc_html_e( 'In Ihrer wp-config.php ist DISABLE_WP_CRON auf true gesetzt. Die automatische Synchronisation funktioniert nur, wenn Sie einen System-Cron einrichten.', 'churchtools-suite' ); ?>
				</p>
				<details style="margin-top: 12px;">
					<summary style="cursor: pointer; color: #856404; font-weight: 600; font-size: 13px;">
						<?php esc_html_e( 'System-Cron Anleitung anzeigen', 'churchtools-suite' ); ?>
					</summary>
					<div style="margin-top: 10px; padding: 12px; background: #fff; border: 1px solid #ffc107; border-radius: 3px;">
						<p style="margin: 0 0 8px; font-size: 13px; color: #333;">
							<?php esc_html_e( 'Fügen Sie diese Zeile zu Ihrem System-Cron (crontab) hinzu:', 'churchtools-suite' ); ?>
						</p>
						<code style="display: block; padding: 8px; background: #f5f5f5; border: 1px solid #ddd; border-radius: 3px; font-size: 12px; font-family: monospace; overflow-x: auto;">
							*/15 * * * * wget -q -O - <?php echo esc_url( site_url( 'wp-cron.php' ) ); ?> >/dev/null 2>&1
						</code>
						<p style="margin: 8px 0 0; font-size: 12px; color: #666;">
							<?php esc_html_e( 'Oder mit curl:', 'churchtools-suite' ); ?>
						</p>
						<code style="display: block; padding: 8px; background: #f5f5f5; border: 1px solid #ddd; border-radius: 3px; font-size: 12px; font-family: monospace; overflow-x: auto;">
							*/15 * * * * curl -s <?php echo esc_url( site_url( 'wp-cron.php' ) ); ?> >/dev/null 2>&1
						</code>
					</div>
				</details>
			</div>
			<?php else : ?>
			<div class="cts-info" style="margin-top: 15px; padding: 12px; background: #f0f6fc; border-left: 4px solid #0073aa;">
				<p style="margin: 0;">
					<strong>✅ WP-Cron aktiv:</strong> 
					<?php esc_html_e( 'Die automatische Synchronisation ist einsatzbereit. Termine werden automatisch im konfigurierten Intervall synchronisiert.', 'churchtools-suite' ); ?>
				</p>
			</div>
			<?php endif; ?>
		</div>

		<div class="cts-submit">
			<button type="submit" name="cts_save_settings" class="cts-button cts-button-primary">
				<span>💾</span>
				<?php esc_html_e( 'Einstellungen speichern', 'churchtools-suite' ); ?>
			</button>
			<button type="button" id="cts-test-connection" class="cts-button cts-button-secondary">
				<span>🔌</span>
				<?php esc_html_e( 'Verbindung testen', 'churchtools-suite' ); ?>
			</button>
		</div>
	</form>
	
	<div id="cts-connection-result" style="display: none; margin-top: 20px;"></div>

</div>
