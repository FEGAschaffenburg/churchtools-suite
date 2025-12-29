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
$advanced_mode = get_option( 'churchtools_suite_advanced_mode', 0 );
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
		<a href="?page=churchtools-suite&tab=data" class="cts-tab <?php echo $active_tab === 'data' ? 'active' : ''; ?>">
			<span>📋</span>
			<?php esc_html_e( 'Daten', 'churchtools-suite' ); ?>
		</a>
		<a href="?page=churchtools-suite&tab=sync" class="cts-tab <?php echo $active_tab === 'sync' ? 'active' : ''; ?>">
			<span>🔄</span>
			<?php esc_html_e( 'Synchronisation', 'churchtools-suite' ); ?>
		</a>
		<a href="?page=churchtools-suite&tab=documentation" class="cts-tab <?php echo $active_tab === 'documentation' ? 'active' : ''; ?>">
			<span>📚</span>
			<?php esc_html_e( 'Dokumentation', 'churchtools-suite' ); ?>
		</a>
		<?php if ( $advanced_mode ) : ?>
		<a href="?page=churchtools-suite&tab=debug" class="cts-tab <?php echo $active_tab === 'debug' ? 'active' : ''; ?>">
			<span>🔧</span>
			<?php esc_html_e( 'Erweitert', 'churchtools-suite' ); ?>
		</a>
		<?php endif; ?>
	</div>

	<?php
	switch ( $active_tab ) {
		case 'settings':
			include __DIR__ . '/tab-settings.php';
			break;
		case 'data':
			include __DIR__ . '/tab-data.php';
			break;
		case 'sync':
			include __DIR__ . '/tab-sync.php';
			break;
		case 'debug':
			if ( $advanced_mode ) {
				include __DIR__ . '/tab-debug-minimal.php';
			}
			break;
		case 'dashboard':
		default:
			include __DIR__ . '/tab-dashboard.php';
			break;
	}
	?>

</div>
