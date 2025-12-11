<?php
/**
 * Main Admin Page
 *
 * @package ChurchTools_Suite
 * @since   0.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'dashboard';
?>

<div class="wrap cts-wrap">
	
	<div class="cts-header">
		<h1>
			<span class="dashicons dashicons-calendar-alt"></span>
			<?php esc_html_e( 'ChurchTools Suite', 'churchtools-suite' ); ?>
		</h1>
		<p class="cts-subtitle"><?php esc_html_e( 'WordPress Integration für ChurchTools', 'churchtools-suite' ); ?></p>
	</div>

	<nav class="nav-tab-wrapper">
		<a href="?page=churchtools-suite&tab=dashboard" class="nav-tab <?php echo $active_tab === 'dashboard' ? 'nav-tab-active' : ''; ?>">
			<span class="dashicons dashicons-dashboard"></span>
			<?php esc_html_e( 'Dashboard', 'churchtools-suite' ); ?>
		</a>
		<a href="?page=churchtools-suite&tab=settings" class="nav-tab <?php echo $active_tab === 'settings' ? 'nav-tab-active' : ''; ?>">
			<span class="dashicons dashicons-admin-settings"></span>
			<?php esc_html_e( 'Einstellungen', 'churchtools-suite' ); ?>
		</a>
		<a href="?page=churchtools-suite&tab=sync" class="nav-tab <?php echo $active_tab === 'sync' ? 'nav-tab-active' : ''; ?>">
			<span class="dashicons dashicons-update"></span>
			<?php esc_html_e( 'Sync', 'churchtools-suite' ); ?>
		</a>
		<a href="?page=churchtools-suite&tab=debug" class="nav-tab <?php echo $active_tab === 'debug' ? 'nav-tab-active' : ''; ?>">
			<span class="dashicons dashicons-admin-tools"></span>
			<?php esc_html_e( 'Debug', 'churchtools-suite' ); ?>
		</a>
	</nav>

	<?php
	switch ( $active_tab ) {
		case 'settings':
			include __DIR__ . '/tab-settings.php';
			break;
		case 'sync':
			include __DIR__ . '/tab-sync.php';
			break;
		case 'debug':
			include __DIR__ . '/tab-debug.php';
			break;
		case 'dashboard':
		default:
			include __DIR__ . '/tab-dashboard.php';
			break;
	}
	?>

</div>
