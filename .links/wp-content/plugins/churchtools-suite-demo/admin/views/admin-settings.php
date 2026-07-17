<?php
/**
 * Admin Settings Page (Demo Plugin)
 * 
 * Tab-based settings interface for plugin configuration, updates, and migrations
 * Only accessible by administrators
 *
 * @package ChurchTools_Suite_Demo
 * @since   1.1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Check admin permissions
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( __( 'Sie haben keine Berechtigung auf diese Seite zuzugreifen.', 'churchtools-suite-demo' ) );
}

// Get current tab
$current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'configuration';

// Available tabs
$tabs = [
	'configuration' => [
		'label' => 'Konfiguration',
		'icon' => 'admin-settings',
	],
	'cron' => [
		'label' => 'Cron-Jobs',
		'icon' => 'clock',
	],
	'updates' => [
		'label' => 'Updates',
		'icon' => 'update',
	],
	'migrations' => [
		'label' => 'Migrationen',
		'icon' => 'database-import',
	],
];
?>

<div class="wrap cts-demo-settings">
	<h1>
		<span class="dashicons dashicons-admin-tools"></span>
		<?php _e( 'ChurchTools Suite Demo - Einstellungen', 'churchtools-suite-demo' ); ?>
	</h1>
	
	<p class="description">
		<?php _e( 'Verwalten Sie Plugin-Konfigurationen, Updates und Datenbank-Migrationen.', 'churchtools-suite-demo' ); ?>
	</p>
	
	<!-- Tab Navigation -->
	<nav class="nav-tab-wrapper wp-clearfix" aria-label="Sekundäres Menü">
		<?php foreach ( $tabs as $tab_id => $tab_data ) : ?>
			<a 
				href="<?php echo esc_url( add_query_arg( [ 'page' => 'churchtools-suite-demo-settings', 'tab' => $tab_id ], admin_url( 'edit.php?post_type=cts_demo_page' ) ) ); ?>" 
				class="nav-tab <?php echo $current_tab === $tab_id ? 'nav-tab-active' : ''; ?>"
			>
				<span class="dashicons dashicons-<?php echo esc_attr( $tab_data['icon'] ); ?>"></span>
				<?php echo esc_html( $tab_data['label'] ); ?>
			</a>
		<?php endforeach; ?>
	</nav>
	
	<!-- Tab Content -->
	<div class="tab-content">
		<?php
		switch ( $current_tab ) {
			case 'configuration':
				include CHURCHTOOLS_SUITE_DEMO_PATH . 'admin/views/tab-configuration.php';
				break;
			case 'cron':
				include CHURCHTOOLS_SUITE_DEMO_PATH . 'admin/views/tab-cron.php';
				break;
			case 'updates':
				include CHURCHTOOLS_SUITE_DEMO_PATH . 'admin/views/tab-updates.php';
				break;
			case 'migrations':
				include CHURCHTOOLS_SUITE_DEMO_PATH . 'admin/views/tab-migrations.php';
				break;
			default:
				include CHURCHTOOLS_SUITE_DEMO_PATH . 'admin/views/tab-configuration.php';
		}
		?>
	</div>
</div>

<style>
.cts-demo-settings h1 {
	display: flex;
	align-items: center;
	gap: 10px;
}

.cts-demo-settings h1 .dashicons {
	font-size: 32px;
	width: 32px;
	height: 32px;
}

.nav-tab .dashicons {
	margin-right: 5px;
	font-size: 16px;
	width: 16px;
	height: 16px;
	vertical-align: middle;
}

.tab-content {
	background: #fff;
	border: 1px solid #ccd0d4;
	border-top: none;
	padding: 20px;
	margin-top: -1px;
}

.cts-info-box {
	background: #f0f6fc;
	border-left: 4px solid #2271b1;
	padding: 15px;
	margin: 20px 0;
}

.cts-warning-box {
	background: #fcf9e8;
	border-left: 4px solid #dba617;
	padding: 15px;
	margin: 20px 0;
}

.cts-success-box {
	background: #f0f6fc;
	border-left: 4px solid #00a32a;
	padding: 15px;
	margin: 20px 0;
}

.cts-stats-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
	gap: 15px;
	margin: 20px 0;
}

.cts-stat-card {
	background: #fff;
	border: 1px solid #ccd0d4;
	border-radius: 4px;
	padding: 15px;
	text-align: center;
}

.cts-stat-card .stat-value {
	font-size: 32px;
	font-weight: 600;
	color: #2271b1;
	display: block;
	margin-bottom: 5px;
}

.cts-stat-card .stat-label {
	font-size: 14px;
	color: #646970;
}

.cts-action-buttons {
	display: flex;
	gap: 10px;
	margin: 20px 0;
	flex-wrap: wrap;
}

.cts-action-buttons .button {
	display: inline-flex;
	align-items: center;
	gap: 5px;
}

.cts-action-buttons .button .dashicons {
	font-size: 16px;
	width: 16px;
	height: 16px;
}

/* Loading Spinner */
.cts-loading {
	display: inline-block;
	width: 16px;
	height: 16px;
	border: 3px solid rgba(0,0,0,0.1);
	border-radius: 50%;
	border-top-color: #2271b1;
	animation: spin 0.8s linear infinite;
}

@keyframes spin {
	to { transform: rotate(360deg); }
}

.button .cts-loading {
	margin-right: 5px;
}
</style>
