<?php
/**
 * Settings Tab with Sub-Navigation
 *
 * @package ChurchTools_Suite
 * @since   0.7.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Determine active sub-tab
$active_subtab = isset( $_GET['subtab'] ) ? sanitize_key( $_GET['subtab'] ) : 'api';
?>

<div class="cts-settings">
	
	<!-- Sub-Navigation -->
	<div class="cts-sub-tabs">
		<a href="?page=churchtools-suite&tab=settings&subtab=api" class="cts-sub-tab <?php echo $active_subtab === 'api' ? 'active' : ''; ?>">
			<?php esc_html_e( 'API & Verbindung', 'churchtools-suite' ); ?>
		</a>
		<a href="?page=churchtools-suite&tab=settings&subtab=sync" class="cts-sub-tab <?php echo $active_subtab === 'sync' ? 'active' : ''; ?>">
			<?php esc_html_e( 'Synchronisation', 'churchtools-suite' ); ?>
		</a>
		<a href="?page=churchtools-suite&tab=settings&subtab=posts" class="cts-sub-tab <?php echo $active_subtab === 'posts' ? 'active' : ''; ?>">
			<?php esc_html_e( 'Berichte', 'churchtools-suite' ); ?>
		</a>
		<a href="?page=churchtools-suite&tab=settings&subtab=calendars" class="cts-sub-tab <?php echo $active_subtab === 'calendars' ? 'active' : ''; ?>">
			<?php esc_html_e( 'Kalender', 'churchtools-suite' ); ?>
		</a>
		<a href="?page=churchtools-suite&tab=settings&subtab=services" class="cts-sub-tab <?php echo $active_subtab === 'services' ? 'active' : ''; ?>">
			<?php esc_html_e( 'Dienste', 'churchtools-suite' ); ?>
		</a>
		<a href="?page=churchtools-suite&tab=settings&subtab=templates" class="cts-sub-tab <?php echo $active_subtab === 'templates' ? 'active' : ''; ?>">
			<?php esc_html_e( 'Templates', 'churchtools-suite' ); ?>
		</a>
		<a href="?page=churchtools-suite&tab=settings&subtab=advanced" class="cts-sub-tab <?php echo $active_subtab === 'advanced' ? 'active' : ''; ?>">
			<?php esc_html_e( 'Erweitert', 'churchtools-suite' ); ?>
		</a>
	</div>
	
	<!-- SubTab Content Wrapper -->
	<div class="cts-subtab-content">
		<?php
		switch ( $active_subtab ) {
			case 'posts':
				include __DIR__ . '/settings/subtab-posts.php';
				break;
			case 'sync':
				include __DIR__ . '/settings/subtab-sync.php';
				break;
			case 'calendars':
				include __DIR__ . '/tab-calendars.php';
				break;
			case 'services':
				include __DIR__ . '/tab-services.php';
				break;
			case 'templates':
				include __DIR__ . '/settings/subtab-templates.php';
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
	
</div>
