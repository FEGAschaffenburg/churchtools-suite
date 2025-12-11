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

$last_sync = get_option( 'churchtools_suite_last_sync', 0 );
?>

<div class="cts-sync">
	
	<div class="cts-card">
		<h3><?php esc_html_e( 'Manuelle Synchronisation', 'churchtools-suite' ); ?></h3>
		
		<p><?php esc_html_e( 'Synchronisiere Events und Kalender aus ChurchTools.', 'churchtools-suite' ); ?></p>
		
		<?php if ( $last_sync ) : ?>
		<p class="description">
			<?php printf( esc_html__( 'Letzte Synchronisation: %s', 'churchtools-suite' ), esc_html( wp_date( 'd.m.Y H:i', $last_sync ) ) ); ?>
		</p>
		<?php endif; ?>
		
		<p>
			<button type="button" id="cts-sync-now" class="button button-primary">
				<span class="dashicons dashicons-update"></span>
				<?php esc_html_e( 'Jetzt synchronisieren', 'churchtools-suite' ); ?>
			</button>
		</p>
		
		<div id="cts-sync-progress" class="cts-sync-progress" style="display: none;">
			<div class="cts-progress-bar">
				<div class="cts-progress-fill"></div>
			</div>
			<p class="cts-progress-text"><?php esc_html_e( 'Synchronisierung läuft...', 'churchtools-suite' ); ?></p>
		</div>
		
		<div id="cts-sync-result" class="cts-sync-result"></div>
	</div>

</div>
