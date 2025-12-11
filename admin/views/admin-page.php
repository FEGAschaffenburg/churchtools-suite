<?php
/**
 * Main Admin Page Template
 *
 * @package ChurchTools_Suite
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap churchtools-suite-admin-wrapper">
	
	<!-- Header -->
	<div class="churchtools-suite-header">
		<h1>
			<span class="dashicons dashicons-calendar-alt"></span>
			<?php echo esc_html( get_admin_page_title() ); ?>
		</h1>
		<p><?php esc_html_e( 'Professionelle ChurchTools-Integration für WordPress', 'churchtools-suite' ); ?></p>
	</div>
	
	<!-- Tab Navigation -->
	<nav class="nav-tab-wrapper">
		<a href="?page=churchtools-suite&tab=dashboard" 
		   class="nav-tab <?php echo $active_tab === 'dashboard' ? 'nav-tab-active' : ''; ?>">
			<span class="dashicons dashicons-dashboard"></span>
			<?php esc_html_e( 'Dashboard', 'churchtools-suite' ); ?>
		</a>
		<a href="?page=churchtools-suite&tab=settings" 
		   class="nav-tab <?php echo $active_tab === 'settings' ? 'nav-tab-active' : ''; ?>">
			<span class="dashicons dashicons-admin-settings"></span>
			<?php esc_html_e( 'Einstellungen', 'churchtools-suite' ); ?>
		</a>
		<a href="?page=churchtools-suite&tab=sync" 
		   class="nav-tab <?php echo $active_tab === 'sync' ? 'nav-tab-active' : ''; ?>">
			<span class="dashicons dashicons-update"></span>
			<?php esc_html_e( 'Synchronisation', 'churchtools-suite' ); ?>
		</a>
		<a href="?page=churchtools-suite&tab=debug" 
		   class="nav-tab <?php echo $active_tab === 'debug' ? 'nav-tab-active' : ''; ?>">
			<span class="dashicons dashicons-admin-tools"></span>
			<?php esc_html_e( 'Debug', 'churchtools-suite' ); ?>
		</a>
	</nav>
	
	<!-- Tab Content -->
	<div class="churchtools-suite-tab-content">
		<?php
		switch ( $active_tab ) {
			case 'settings':
				include_once CHURCHTOOLS_SUITE_PATH . 'admin/views/tab-settings.php';
				break;
			case 'sync':
				include_once CHURCHTOOLS_SUITE_PATH . 'admin/views/tab-sync.php';
				break;
			case 'debug':
				include_once CHURCHTOOLS_SUITE_PATH . 'admin/views/tab-debug.php';
				break;
			case 'dashboard':
			default:
				include_once CHURCHTOOLS_SUITE_PATH . 'admin/views/tab-dashboard.php';
				break;
		}
		?>
	</div>
	
</div>
