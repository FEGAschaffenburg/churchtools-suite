<?php
/**
 * Cron Jobs Tab - Admin Settings
 *
 * Displays scheduled cron jobs for the Demo Plugin, separate from main plugin.
 *
 * @package ChurchTools_Suite_Demo
 * @since   1.1.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load Cron classes
require_once CHURCHTOOLS_SUITE_DEMO_PATH . 'includes/class-demo-cron.php';
require_once CHURCHTOOLS_SUITE_DEMO_PATH . 'includes/class-demo-cron-display.php';

// Get scheduled jobs
$scheduled_jobs = ChurchTools_Suite_Demo_Cron::get_scheduled_jobs();

// Get settings
$demo_duration_days = (int) get_option( 'cts_demo_duration_days', 30 );
$auto_cleanup = get_option( 'cts_demo_auto_cleanup', true );
?>

<div class="cts-demo-tab-content">
	
	<!-- Overview Card -->
	<div class="cts-demo-card">
		<h2><?php _e( 'Cron-Jobs Übersicht', 'churchtools-suite-demo' ); ?></h2>
		<p class="description">
			<?php _e( 'Automatische Hintergrundaufgaben für das Demo-Plugin.', 'churchtools-suite-demo' ); ?>
		</p>
		
		<div class="cts-demo-stats-grid" style="margin-top: 20px;">
			<!-- Active Jobs -->
			<div class="cts-demo-stat-card">
				<div class="stat-icon" style="background-color: #00a32a;">
					<span class="dashicons dashicons-clock"></span>
				</div>
				<div class="stat-details">
					<div class="stat-label"><?php _e( 'Aktive Cron-Jobs', 'churchtools-suite-demo' ); ?></div>
					<div class="stat-value"><?php echo count( $scheduled_jobs ); ?></div>
				</div>
			</div>
			
			<!-- Demo Duration -->
			<div class="cts-demo-stat-card">
				<div class="stat-icon" style="background-color: #2271b1;">
					<span class="dashicons dashicons-calendar-alt"></span>
				</div>
				<div class="stat-details">
					<div class="stat-label"><?php _e( 'Demo-Dauer', 'churchtools-suite-demo' ); ?></div>
					<div class="stat-value"><?php echo $demo_duration_days; ?> <?php _e( 'Tage', 'churchtools-suite-demo' ); ?></div>
				</div>
			</div>
			
			<!-- Auto Cleanup -->
			<div class="cts-demo-stat-card">
				<div class="stat-icon" style="background-color: <?php echo $auto_cleanup ? '#00a32a' : '#999'; ?>;">
					<span class="dashicons dashicons-trash"></span>
				</div>
				<div class="stat-details">
					<div class="stat-label"><?php _e( 'Auto-Bereinigung', 'churchtools-suite-demo' ); ?></div>
					<div class="stat-value"><?php echo $auto_cleanup ? __( 'Aktiv', 'churchtools-suite-demo' ) : __( 'Inaktiv', 'churchtools-suite-demo' ); ?></div>
				</div>
			</div>
		</div>
	</div>
	
	<!-- Scheduled Jobs -->
	<div class="cts-demo-card">
		<h2><?php _e( 'Geplante Aufgaben', 'churchtools-suite-demo' ); ?></h2>
		
		<?php if ( empty( $scheduled_jobs ) ) : ?>
			<div class="notice notice-warning inline">
				<p>
					<span class="dashicons dashicons-warning" style="color: #dba617;"></span>
					<?php _e( 'Keine Cron-Jobs geplant. Dies kann auftreten, wenn Auto-Bereinigung deaktiviert ist.', 'churchtools-suite-demo' ); ?>
				</p>
			</div>
		<?php else : ?>
			
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th style="width: 50px;"><?php _e( 'Status', 'churchtools-suite-demo' ); ?></th>
						<th><?php _e( 'Name', 'churchtools-suite-demo' ); ?></th>
						<th><?php _e( 'Beschreibung', 'churchtools-suite-demo' ); ?></th>
						<th><?php _e( 'Intervall', 'churchtools-suite-demo' ); ?></th>
						<th><?php _e( 'Nächste Ausführung', 'churchtools-suite-demo' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $scheduled_jobs as $job ) : ?>
						<?php 
						$formatted = ChurchTools_Suite_Demo_Cron_Display::format_cron_event(
							$job['hook'],
							$job['next_run'],
							$job['schedule']
						); 
						?>
						<tr>
							<td style="text-align: center;">
								<?php echo ChurchTools_Suite_Demo_Cron_Display::get_status_icon( $job['next_run'] ); ?>
							</td>
							<td>
								<strong><?php echo esc_html( $formatted['name'] ); ?></strong>
								<br>
								<code style="font-size: 0.9em; color: #666;"><?php echo esc_html( $formatted['hook'] ); ?></code>
							</td>
							<td>
								<?php echo esc_html( $formatted['description'] ); ?>
							</td>
							<td>
								<?php echo esc_html( $formatted['schedule_display'] ); ?>
							</td>
							<td>
								<?php echo esc_html( $formatted['next_run_formatted'] ); ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			
		<?php endif; ?>
	</div>
	
	<!-- Cron System Info -->
	<div class="cts-demo-card">
		<h2><?php _e( 'System-Informationen', 'churchtools-suite-demo' ); ?></h2>
		
		<table class="form-table">
			<tr>
				<th scope="row"><?php _e( 'WordPress Cron', 'churchtools-suite-demo' ); ?></th>
				<td>
					<?php if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) : ?>
						<span class="dashicons dashicons-warning" style="color: #dba617;"></span>
						<?php _e( 'Deaktiviert (DISABLE_WP_CRON = true)', 'churchtools-suite-demo' ); ?>
						<p class="description">
							<?php _e( 'WordPress Cron ist deaktiviert. Stellen Sie sicher, dass ein echter Cron-Job konfiguriert ist.', 'churchtools-suite-demo' ); ?>
						</p>
					<?php else : ?>
						<span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span>
						<?php _e( 'Aktiv', 'churchtools-suite-demo' ); ?>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Zeitzone', 'churchtools-suite-demo' ); ?></th>
				<td>
					<code><?php echo esc_html( wp_timezone_string() ); ?></code>
					<p class="description">
						<?php _e( 'Aktuelle Zeit:', 'churchtools-suite-demo' ); ?> 
						<?php echo current_time( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Nächster Cron-Lauf', 'churchtools-suite-demo' ); ?></th>
				<td>
					<?php
					$crons = _get_cron_array();
					if ( ! empty( $crons ) ) {
						$next_cron = min( array_keys( $crons ) );
						$diff = $next_cron - time();
						$date = gmdate( 'Y-m-d H:i:s', $next_cron );
						$local_date = get_date_from_gmt( $date, get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );
						
						echo '<code>' . esc_html( $local_date ) . '</code> ';
						echo '<span style="color: #666;">(' . ChurchTools_Suite_Demo_Cron_Display::format_time_diff( $diff ) . ')</span>';
					} else {
						echo '<span class="dashicons dashicons-warning" style="color: #dba617;"></span> ';
						_e( 'Keine Cron-Jobs geplant', 'churchtools-suite-demo' );
					}
					?>
				</td>
			</tr>
		</table>
	</div>
	
	<!-- Actions -->
	<div class="cts-demo-card">
		<h2><?php _e( 'Aktionen', 'churchtools-suite-demo' ); ?></h2>
		
		<p class="description">
			<?php _e( 'Manuelle Ausführung von Cron-Aufgaben für Testzwecke.', 'churchtools-suite-demo' ); ?>
		</p>
		
		<p>
			<button type="button" class="button button-secondary" id="cts-demo-run-cleanup">
				<span class="dashicons dashicons-trash"></span>
				<?php _e( 'Bereinigung jetzt ausführen', 'churchtools-suite-demo' ); ?>
			</button>
			<span id="cts-demo-cleanup-result" style="margin-left: 10px;"></span>
		</p>
		
		<p class="description">
			<?php _e( 'Hinweis: Die Bereinigung läuft nur, wenn Auto-Cleanup aktiviert ist und die Demo-Dauer überschritten wurde.', 'churchtools-suite-demo' ); ?>
		</p>
	</div>
	
</div>

<style>
/* Additional styles for cron tab */
.cts-demo-tab-content table.wp-list-table th {
	padding: 12px;
}

.cts-demo-tab-content table.wp-list-table td {
	padding: 12px;
	vertical-align: middle;
}

.cts-demo-tab-content .dashicons {
	vertical-align: middle;
	width: 20px;
	height: 20px;
	font-size: 20px;
}
</style>

<script>
jQuery(document).ready(function($) {
	// Manual cleanup
	$('#cts-demo-run-cleanup').on('click', function() {
		const btn = $(this);
		const result = $('#cts-demo-cleanup-result');
		
		btn.prop('disabled', true).html('<span class="dashicons dashicons-update dashicons-spin"></span> <?php _e( 'Läuft...', 'churchtools-suite-demo' ); ?>');
		result.html('');
		
		$.ajax({
			url: ajaxurl,
			method: 'POST',
			data: {
				action: 'cts_demo_run_cleanup',
				nonce: '<?php echo wp_create_nonce( 'cts_demo_admin' ); ?>'
			},
			success: function(response) {
				if (response.success) {
					result.html('<span style="color: #00a32a;">' + response.data.message + '</span>');
				} else {
					result.html('<span style="color: #d63638;">' + response.data.message + '</span>');
				}
				btn.prop('disabled', false).html('<span class="dashicons dashicons-trash"></span> <?php _e( 'Bereinigung jetzt ausführen', 'churchtools-suite-demo' ); ?>');
			},
			error: function() {
				result.html('<span style="color: #d63638;"><?php _e( 'Fehler beim Ausführen', 'churchtools-suite-demo' ); ?></span>');
				btn.prop('disabled', false).html('<span class="dashicons dashicons-trash"></span> <?php _e( 'Bereinigung jetzt ausführen', 'churchtools-suite-demo' ); ?>');
			}
		});
	});
});
</script>
