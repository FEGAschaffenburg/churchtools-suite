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
			<span>📅</span>
			<?php esc_html_e( 'ChurchTools Suite', 'churchtools-suite' ); ?>
		</h1>
		<p class="cts-subtitle"><?php esc_html_e( 'WordPress Integration für ChurchTools', 'churchtools-suite' ); ?></p>
	</div>

	<div class="cts-tabs">
		<a href="?page=churchtools-suite&tab=dashboard" class="cts-tab <?php echo $active_tab === 'dashboard' ? 'active' : ''; ?>">
			<span>📊</span>
			<?php esc_html_e( 'Dashboard', 'churchtools-suite' ); ?>
		</a>
		<a href="?page=churchtools-suite&tab=settings" class="cts-tab <?php echo $active_tab === 'settings' ? 'active' : ''; ?>">
			<span>⚙️</span>
			<?php esc_html_e( 'Einstellungen', 'churchtools-suite' ); ?>
		</a>
		<a href="?page=churchtools-suite&tab=calendars" class="cts-tab <?php echo $active_tab === 'calendars' ? 'active' : ''; ?>">
			<span>🗓️</span>
			<?php esc_html_e( 'Kalender', 'churchtools-suite' ); ?>
		</a>
		<a href="?page=churchtools-suite&tab=sync" class="cts-tab <?php echo $active_tab === 'sync' ? 'active' : ''; ?>">
			<span>🔄</span>
			<?php esc_html_e( 'Sync', 'churchtools-suite' ); ?>
		</a>
		<a href="?page=churchtools-suite&tab=debug" class="cts-tab <?php echo $active_tab === 'debug' ? 'active' : ''; ?>">
			<span>🔧</span>
			<?php esc_html_e( 'Debug', 'churchtools-suite' ); ?>
		</a>
	</div>

	<?php
	switch ( $active_tab ) {
		case 'settings':
			include __DIR__ . '/tab-settings.php';
			break;
		case 'calendars':
			include __DIR__ . '/tab-calendars.php';
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
