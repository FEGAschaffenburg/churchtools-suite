<?php
/**
 * Debug Tab (Minimal)
 *
 * @package ChurchTools_Suite
 * @since   0.5.11.28
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;
$sync_history_table = $wpdb->prefix . 'cts_sync_history';
$sync_history = $wpdb->get_results( "SELECT * FROM {$sync_history_table} ORDER BY started_at DESC LIMIT 10" );
?>

<div class="cts-settings">

	<div class="cts-sub-tabs" style="margin-bottom:16px;">
		<a class="cts-sub-tab active"><?php esc_html_e( 'Debug', 'churchtools-suite' ); ?></a>
	</div>

	<div class="cts-tab-content cts-debug">

		<div class="cts-card">
			<div class="cts-card-header">
				<span class="cts-card-icon">⚙️</span>
				<h3><?php esc_html_e( 'System', 'churchtools-suite' ); ?></h3>
			</div>
			<div class="cts-card-body">
				<table class="cts-debug-table">
					<tr>
						<td><?php esc_html_e( 'Plugin-Version', 'churchtools-suite' ); ?></td>
						<td><?php echo esc_html( CHURCHTOOLS_SUITE_VERSION ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'PHP-Version', 'churchtools-suite' ); ?></td>
						<td><?php echo esc_html( phpversion() ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'WordPress-Version', 'churchtools-suite' ); ?></td>
						<td><?php echo esc_html( get_bloginfo( 'version' ) ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'DB-Version', 'churchtools-suite' ); ?></td>
						<td><?php echo esc_html( ChurchTools_Suite_Migrations::get_current_version() ); ?></td>
					</tr>
				</table>
			</div>
		</div>		
		<!-- Cron Jobs Übersicht (v0.10.1.9) -->
		<div class="cts-card" style="margin-top: 20px;">
			<div class="cts-card-header">
				<span class="cts-card-icon">⏰</span>
				<h3><?php esc_html_e( 'Geplante Aufgaben (Cron Jobs)', 'churchtools-suite' ); ?></h3>
			</div>
			<div class="cts-card-body">
				<?php
				// Load Cron Display Helper
				require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-cron-display.php';
				
				$our_hooks = [
					'churchtools_suite_auto_sync',
					'churchtools_suite_session_keepalive',
					'churchtools_suite_check_updates',
				];
				
				echo '<table class="cts-debug-table">';
				echo '<tr>';
				echo '<td><strong>' . esc_html__( 'Aufgabe', 'churchtools-suite' ) . '</strong></td>';
				echo '<td><strong>' . esc_html__( 'Nächste Ausführung', 'churchtools-suite' ) . '</strong></td>';
				echo '<td><strong>' . esc_html__( 'Intervall', 'churchtools-suite' ) . '</strong></td>';
				echo '</tr>';
				
				foreach ( $our_hooks as $hook ) {
					$next_run = wp_next_scheduled( $hook );
					$display_name = ChurchTools_Suite_Cron_Display::get_cron_display_name( $hook );
					$description = ChurchTools_Suite_Cron_Display::get_cron_description( $hook );
					
					// Get cron schedule
					$crons = get_option( 'cron', [] );
					$schedule = '';
					foreach ( $crons as $timestamp => $cron ) {
						if ( isset( $cron[ $hook ] ) ) {
							$event = reset( $cron[ $hook ] );
							$schedule = $event['schedule'] ?? 'einmalig';
							break;
						}
					}
					
					echo '<tr>';
					echo '<td>';
					echo '<strong>' . esc_html( $display_name ) . '</strong><br>';
					echo '<span style="color:#666; font-size:0.9em;">' . esc_html( $description ) . '</span><br>';
					echo '<code style="font-size:0.85em; color:#999;">' . esc_html( $hook ) . '</code>';
					echo '</td>';
					echo '<td>';
					if ( $next_run ) {
						// v0.10.2.2: WordPress-Zeitzone verwenden (nicht UTC!)
						$local_time = get_date_from_gmt( gmdate( 'Y-m-d H:i:s', $next_run ), 'Y-m-d H:i:s' );
						echo esc_html( date_i18n( 'D, d. M Y H:i', strtotime( $local_time ) ) );
						echo '<br><span style="color:#666; font-size:0.85em;">in ' . human_time_diff( $next_run ) . '</span>';
					} else {
						echo '<span style="color:#d63638;">❌ ' . esc_html__( 'Nicht geplant', 'churchtools-suite' ) . '</span>';
					}
					echo '</td>';
					echo '<td>' . esc_html( $schedule ) . '</td>';
					echo '</tr>';
				}
				
				echo '</table>';
				?>
			</div>
		</div>
		<div class="cts-card" style="margin-top: 20px;">
			<div class="cts-card-header">
				<span class="cts-card-icon">📊</span>
				<h3><?php esc_html_e( 'Sync-Historie (letzte 10)', 'churchtools-suite' ); ?></h3>
			</div>
			<div class="cts-card-body">
				<?php if ( empty( $sync_history ) ) : ?>
					<p><?php esc_html_e( 'Keine Sync-Historie vorhanden', 'churchtools-suite' ); ?></p>
				<?php else : ?>
					<table class="cts-debug-table">
						<tr>
							<td><strong><?php esc_html_e( 'Zeitpunkt', 'churchtools-suite' ); ?></strong></td>
							<td><strong><?php esc_html_e( 'Status', 'churchtools-suite' ); ?></strong></td>
							<td><strong><?php esc_html_e( 'Kalender', 'churchtools-suite' ); ?></strong></td>
							<td><strong><?php esc_html_e( 'Events', 'churchtools-suite' ); ?></strong></td>
							<td><strong><?php esc_html_e( 'Services', 'churchtools-suite' ); ?></strong></td>
							<td><strong><?php esc_html_e( 'Dauer', 'churchtools-suite' ); ?></strong></td>
						</tr>
						<?php foreach ( $sync_history as $entry ) : ?>
						<tr>
							<td><?php echo esc_html( $entry->started_at ); ?></td>
							<td><?php echo esc_html( $entry->status ); ?></td>
							<td><?php echo esc_html( $entry->calendars_processed ); ?></td>
							<td><?php echo esc_html( $entry->events_found ); ?></td>
							<td><?php echo esc_html( $entry->services_imported ); ?></td>
							<td><?php echo esc_html( $entry->duration_seconds ); ?>s</td>
						</tr>
						<?php endforeach; ?>
					</table>
				<?php endif; ?>
			</div>
		</div>

	</div>

</div>
	<!-- Rate Limiting (v0.7.0.2) -->

