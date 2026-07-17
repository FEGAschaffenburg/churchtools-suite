<?php
/**
 * Debug/Erweitert Subtab: Übersicht
 *
 * @package ChurchTools_Suite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$registered_modules = class_exists( 'ChurchTools_Suite_Sync_Modules' )
	? ChurchTools_Suite_Sync_Modules::get_registered_modules()
	: [];

$state_labels = [
	'idle' => __( 'Bereit', 'churchtools-suite' ),
	'running' => __( 'Läuft', 'churchtools-suite' ),
	'ok' => __( 'OK', 'churchtools-suite' ),
	'error' => __( 'Fehler', 'churchtools-suite' ),
	'disabled' => __( 'Deaktiviert', 'churchtools-suite' ),
];
?>
<div class="cts-debug-subtab-content">
	<h2>🔎 Übersicht</h2>
	<p>Hier finden Sie eine Übersicht der wichtigsten System- und Debug-Informationen.</p>

	<div class="cts-card" style="margin-top: 16px;">
		<div class="cts-card-header">
			<span class="cts-card-icon">🩺</span>
			<h3><?php esc_html_e( 'Health-Übersicht', 'churchtools-suite' ); ?></h3>
		</div>
		<div class="cts-card-body">
			<?php if ( empty( $registered_modules ) ) : ?>
				<p><?php esc_html_e( 'Keine Sync-Module registriert.', 'churchtools-suite' ); ?></p>
			<?php else : ?>
				<table class="cts-debug-table">
					<tr>
						<td><strong><?php esc_html_e( 'Modul', 'churchtools-suite' ); ?></strong></td>
						<td><strong><?php esc_html_e( 'Status', 'churchtools-suite' ); ?></strong></td>
						<td><strong><?php esc_html_e( 'Letzter Daten-Sync', 'churchtools-suite' ); ?></strong></td>
						<td><strong><?php esc_html_e( 'Letztes Ergebnis', 'churchtools-suite' ); ?></strong></td>
					</tr>
					<?php foreach ( $registered_modules as $module ) : ?>
						<?php
						$module_id = isset( $module['id'] ) ? (string) $module['id'] : '';
						$module_label = isset( $module['label'] ) ? (string) $module['label'] : $module_id;
						$status = $module_id !== '' ? ChurchTools_Suite_Sync_Modules::get_module_status( $module_id ) : [];
						$state = isset( $status['state'] ) ? sanitize_key( (string) $status['state'] ) : 'idle';
						$state_label = $state_labels[ $state ] ?? ucfirst( $state );
						$last_data_sync_at = isset( $status['last_data_sync_at'] ) ? (string) $status['last_data_sync_at'] : '';
						$last_result = isset( $status['last_result'] ) && is_array( $status['last_result'] ) ? $status['last_result'] : [];
						$last_message = isset( $last_result['message'] ) ? (string) $last_result['message'] : '';
						?>
						<tr>
							<td><?php echo esc_html( $module_label ); ?></td>
							<td><?php echo esc_html( $state_label ); ?></td>
							<td><?php echo esc_html( $last_data_sync_at !== '' ? $last_data_sync_at : '—' ); ?></td>
							<td><?php echo esc_html( $last_message !== '' ? $last_message : '—' ); ?></td>
						</tr>
					<?php endforeach; ?>
				</table>
			<?php endif; ?>
		</div>
	</div>

	<?php
	include __DIR__ . '/../tab-debug-minimal.php';
	?>
</div>
