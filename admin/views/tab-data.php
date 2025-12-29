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
		<a href="?page=churchtools-suite&tab=data&subtab=events" class="cts-sub-tab <?php echo $active_subtab === 'events' ? 'active' : ''; ?>">
			<?php esc_html_e( 'Termine', 'churchtools-suite' ); ?>
		</a>
		<a href="?page=churchtools-suite&tab=data&subtab=imported-services" class="cts-sub-tab <?php echo $active_subtab === 'imported-services' ? 'active' : ''; ?>">
			<?php esc_html_e( 'Importierte Services', 'churchtools-suite' ); ?>
		</a>
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
