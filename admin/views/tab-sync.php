<?php
/**
 * Sync Tab
 *
 * @package ChurchTools_Suite
 * @since   0.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load repositories
require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-repository-base.php';
require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-calendars-repository.php';

$calendars_repo = new ChurchTools_Suite_Calendars_Repository();
$calendars = $calendars_repo->get_all();
$selected_count = $calendars_repo->count_selected();
$calendars_last_sync = get_option('churchtools_suite_calendars_last_sync', null);
$events_last_sync = get_option('churchtools_suite_events_last_sync', null);
$days_past = get_option('churchtools_suite_sync_days_past', 7);
$days_future = get_option('churchtools_suite_sync_days_future', 90);
$auto_sync_enabled = get_option('churchtools_suite_auto_sync_enabled', 0);
?>

<div class="cts-tab-content-inner">
	
	<!-- Sync Events Card -->
	<div class="cts-card" style="margin-top: 20px;">
		<div class="cts-card-header">
			<h2>📅 <?php esc_html_e('Termine synchronisieren', 'churchtools-suite'); ?></h2>
		</div>
		<div class="cts-card-body">
			<p class="description">
				<?php 
				printf(
					esc_html__('Lädt Termine und Events aus den ausgewählten Kalendern. Zeitraum: %d Tage zurück bis %d Tage voraus. Termine ohne Event verwenden Appointmentdaten, Termine mit Events die Eventdaten (1:X).', 'churchtools-suite'),
					$days_past,
					$days_future
				);
				?>
			</p>
			
			<?php if ($events_last_sync): ?>
			<p class="cts-info">
				<strong><?php esc_html_e('Letzte Synchronisation:', 'churchtools-suite'); ?></strong>
				<?php echo esc_html(get_date_from_gmt($events_last_sync, get_option('date_format') . ' ' . get_option('time_format'))); ?>
			</p>
			<?php endif; ?>
			
			<?php if (empty($calendars)): ?>
				<div class="notice notice-warning inline">
					<p><?php esc_html_e('Bitte synchronisieren Sie zuerst die Kalender, bevor Sie Termine laden.', 'churchtools-suite'); ?></p>
				</div>
			<?php elseif ($selected_count === 0): ?>
				<div class="notice notice-warning inline">
					<p><?php esc_html_e('Bitte wählen Sie im Kalender-Tab mindestens einen Kalender aus.', 'churchtools-suite'); ?></p>
				</div>
			<?php elseif ($auto_sync_enabled): ?>
				<div class="notice notice-info inline">
					<p>
						<strong><?php esc_html_e('Automatischer Sync ist aktiviert', 'churchtools-suite'); ?></strong><br>
						<?php esc_html_e('Der manuelle Sync ist deaktiviert, da der automatische Sync in den Einstellungen aktiviert ist. Termine werden automatisch im konfigurierten Intervall synchronisiert.', 'churchtools-suite'); ?><br>
						<?php printf(
							esc_html__('Um einen Sync sofort auszuführen, nutzen Sie den %sManuellen Trigger im Debug-Tab%s.', 'churchtools-suite'),
							'<a href="?page=churchtools-suite&tab=debug">',
							'</a>'
						); ?>
					</p>
				</div>
				<div class="cts-button-group">
					<button type="button" id="cts-sync-events-btn" class="button button-primary" disabled title="<?php esc_attr_e('Automatischer Sync ist aktiviert', 'churchtools-suite'); ?>">
						<span class="dashicons dashicons-calendar"></span>
						<?php esc_html_e('Termine jetzt synchronisieren', 'churchtools-suite'); ?>
					</button>
					<button type="button" id="cts-force-full-sync-btn" class="button button-secondary" disabled title="<?php esc_attr_e('Automatischer Sync ist aktiviert', 'churchtools-suite'); ?>" style="margin-left: 10px;">
						<span class="dashicons dashicons-backup"></span>
						<?php esc_html_e('Vollständigen Sync erzwingen', 'churchtools-suite'); ?>
					</button>
				</div>
			<?php else: ?>
				<div class="cts-button-group">
					<button type="button" id="cts-sync-events-btn" class="button button-primary">
						<span class="dashicons dashicons-calendar"></span>
						<?php esc_html_e('Termine jetzt synchronisieren', 'churchtools-suite'); ?>
					</button>
					<button type="button" id="cts-force-full-sync-btn" class="button button-secondary" style="margin-left: 10px;">
						<span class="dashicons dashicons-backup"></span>
						<?php esc_html_e('Vollständigen Sync erzwingen', 'churchtools-suite'); ?>
					</button>
				</div>
			<?php endif; ?>
			
			<div id="cts-sync-events-result" style="margin-top: 15px;"></div>
		</div>
	</div>
	
	<!-- Sync Info -->
	<div class="cts-card" style="margin-top: 20px;">
		<div class="cts-card-header">
			<h2>ℹ️ <?php esc_html_e('Hinweise zur Synchronisation', 'churchtools-suite'); ?></h2>
		</div>
		<div class="cts-card-body">
			<ul style="margin-left: 20px;">
				<li><?php esc_html_e('Die Kalender-Synchronisation lädt die verfügbaren Kalender aus ChurchTools.', 'churchtools-suite'); ?></li>
				<li><?php esc_html_e('Nach der Kalender-Synchronisation können Sie im Kalender-Tab auswählen, welche Kalender synchronisiert werden sollen.', 'churchtools-suite'); ?></li>
				<li><?php esc_html_e('Die Termin-Synchronisation lädt nur Termine aus den im Kalender-Tab ausgewählten Kalendern.', 'churchtools-suite'); ?></li>
				<li><?php esc_html_e('Den Zeitraum für die Termin-Synchronisation können Sie im Einstellungen-Tab anpassen.', 'churchtools-suite'); ?></li>
				<li><strong><?php esc_html_e('Inkrementelle Synchronisation (v0.7.1.0):', 'churchtools-suite'); ?></strong> <?php esc_html_e('Nach dem ersten Sync werden nur noch geänderte Termine abgerufen (80-95% weniger API-Anfragen). Ein vollständiger Sync kann mit dem "Vollständigen Sync erzwingen"-Button ausgelöst werden.', 'churchtools-suite'); ?></li>
			</ul>
		</div>
	</div>

</div>
