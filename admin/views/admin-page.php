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
			<span>🔄</span>
			<?php esc_html_e( 'Synchronisation', 'churchtools-suite' ); ?>
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
				// Dynamic Subtab navigation for Debug/Erweitert
				$subtab = isset( $_GET['subtab'] ) ? sanitize_key( wp_unslash( $_GET['subtab'] ) ) : '';
				$subtabs = [];
				$debug_dir = __DIR__ . '/debug';
				if ( is_dir( $debug_dir ) ) {
					$files = scandir( $debug_dir );
					foreach ( $files as $file ) {
						if ( strpos( $file, 'subtab-' ) === 0 && substr( $file, -4 ) === '.php' ) {
							$slug = str_replace( [ 'subtab-', '.php' ], '', $file );
							// Generate label from slug: replace hyphens with spaces and ucfirst words
							$label = str_replace( '-', ' ', $slug );
							$label = mb_convert_case( $label, MB_CASE_TITLE, 'UTF-8' );
							// Small localization fixes
							if ( $slug === 'uebersicht' ) {
								$label = __( 'Übersicht', 'churchtools-suite' );
							} elseif ( $slug === 'manuelle-trigger' ) {
								$label = __( 'Manuelle Trigger', 'churchtools-suite' );
							} elseif ( $slug === 'logs' ) {
								$label = __( 'Logs', 'churchtools-suite' );
							}
							$subtabs[ $slug ] = $label;
						}
					}
				}
				if ( empty( $subtab ) ) {
					// choose first available subtab or default to 'uebersicht'
					$subtab = key( $subtabs ) ?: 'uebersicht';
				}
				?>
				<div class="cts-subtab-nav" role="tablist" aria-label="CTS Debug Subtabs">
					<?php foreach ( $subtabs as $key => $label ) : ?>
						<a href="?page=churchtools-suite&tab=debug&subtab=<?php echo esc_attr( $key ); ?>" class="cts-subtab <?php echo $subtab === $key ? 'active' : ''; ?>"><?php echo esc_html( $label ); ?></a>
					<?php endforeach; ?>
				</div>
				<?php
				$subtab_file = __DIR__ . '/debug/subtab-' . $subtab . '.php';
				if ( file_exists( $subtab_file ) ) {
					include $subtab_file;
				} else {
					include __DIR__ . '/tab-debug-minimal.php';
				}
			   }
			   break;
		   case 'dashboard':
		   default:
			   include __DIR__ . '/tab-dashboard.php';
			   break;
	   }
	?>

</div>
