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

