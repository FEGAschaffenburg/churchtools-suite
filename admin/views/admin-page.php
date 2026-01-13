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
		<!-- Daten tab removed from main navigation; moved to separate submenu -->
		<a href="?page=churchtools-suite&tab=sync" class="cts-tab <?php echo $active_tab === 'sync' ? 'active' : ''; ?>">
			<span>📋</span>
			<?php esc_html_e( 'Events', 'churchtools-suite' ); ?>
		</a>
		   <!-- Dokumentation Tab entfernt -->
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
			   	echo '<div class="cts-settings">'; // Wrapper für konsistente Darstellung
			   	
			   	$subtab = isset( $_GET['subtab'] ) ? sanitize_key( wp_unslash( $_GET['subtab'] ) ) : '';

               	// Statically defined Debug subtabs to keep behavior consistent with Settings subtabs
               	$subtabs = array(
               		'uebersicht' => __( 'Übersicht', 'churchtools-suite' ),
               		'manuelle-trigger' => __( 'Manuelle Trigger', 'churchtools-suite' ),
               		'logs' => __( 'Logs', 'churchtools-suite' ),
               		'reset-cleanup' => __( 'Reset & Cleanup', 'churchtools-suite' ),
               	);

               	if ( empty( $subtab ) ) {
               		$subtab = 'uebersicht';
               	}

               	// expose variables for shared partial
               	$subtab_active = $subtab;
               	$subtab_parent_tab = 'debug';
               	include __DIR__ . '/partials/render-subtabs.php';

               	$subtab_file = __DIR__ . '/debug/subtab-' . $subtab . '.php';
               	if ( file_exists( $subtab_file ) ) {
               		include $subtab_file;
               	} else {
               		include __DIR__ . '/tab-debug-minimal.php';
               	}
               	
               	echo '</div>'; // Close wrapper
               }
			   break;
		   case 'dashboard':
		   default:
			   include __DIR__ . '/tab-dashboard.php';
			   break;
	   }
	?>

</div>
