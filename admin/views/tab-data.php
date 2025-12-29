<?php
/**
 * Data Tab with sub-navigation
 *
 * @package ChurchTools_Suite
 * @since   0.7.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$active_subtab = isset( $_GET['subtab'] ) ? sanitize_key( $_GET['subtab'] ) : 'events';
?>

<div class="cts-data">
	
	<!-- Sub-Navigation -->
	<div class="cts-sub-tabs" style="margin-bottom: 20px; border-bottom: 1px solid #ddd;">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=churchtools-suite-data&subtab=events' ) ); ?>" class="cts-sub-tab <?php echo $active_subtab === 'events' ? 'active' : ''; ?>">
			<?php esc_html_e( 'Termine', 'churchtools-suite' ); ?>
		</a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=churchtools-suite-data&subtab=imported-services' ) ); ?>" class="cts-sub-tab <?php echo $active_subtab === 'imported-services' ? 'active' : ''; ?>">
			<?php esc_html_e( 'Importierte Services', 'churchtools-suite' ); ?>
		</a>
	</div>

	<?php
	// Data page header with quick actions (similar to Dashboard)
	$ct_cookies = get_option( 'churchtools_suite_ct_cookies', [] );
	$is_connected = ! empty( $ct_cookies );
	?>
	<div class="cts-section-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px;">
		<div>
			<h2><?php esc_html_e( 'Daten', 'churchtools-suite' ); ?></h2>
			<p class="cts-section-description"><?php esc_html_e( 'Verwalte importierte Termine und Services.', 'churchtools-suite' ); ?></p>
		</div>
		<?php if ( $is_connected ) : ?>
		<div style="display:flex; gap:10px; align-items:center;">
			<button id="cts-data-sync-now" class="cts-button cts-button-primary" style="font-size:14px; padding:10px 18px;">
				🔄 <?php esc_html_e( 'Jetzt synchronisieren', 'churchtools-suite' ); ?>
			</button>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=churchtools-suite&tab=debug' ) ); ?>" class="cts-button cts-button-secondary">
				📊 <?php esc_html_e( 'Sync-Logs', 'churchtools-suite' ); ?>
			</a>
			<div id="cts-data-sync-result" style="margin-left:12px; font-size:13px; color:#333; display:none;"></div>
		</div>
		<?php endif; ?>
	</div>
	
	<?php
	switch ( $active_subtab ) {
		case 'imported-services':
			include __DIR__ . '/tab-imported-services.php';
			break;
		case 'events':
		default:
			include __DIR__ . '/tab-events.php';
			break;
	}
	?>
	
</div>
