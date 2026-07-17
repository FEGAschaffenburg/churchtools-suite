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
$feedback_state = get_option( 'churchtools_suite_feedback_status', [] );
$feedback_status = '';
if ( is_array( $feedback_state ) ) {
	$feedback_status = (string) ( $feedback_state['status'] ?? '' );
} elseif ( is_string( $feedback_state ) ) {
	// Backward compatibility for older plain-string option values.
	$feedback_status = $feedback_state;
}
$feedback_result = get_transient( 'churchtools_suite_feedback_flash' );
if ( ! empty( $feedback_result ) ) {
	delete_transient( 'churchtools_suite_feedback_flash' );
}
$feedback_redirect = add_query_arg( [
	'page' => 'churchtools-suite',
	'tab' => $active_tab,
], admin_url( 'admin.php' ) );
?>

<div class="wrap cts-wrap">
	
	<div class="cts-header">
		<h1>
			<span>📅</span>
			<?php esc_html_e( 'ChurchTools Suite', 'churchtools-suite' ); ?>
		</h1>
		<p class="cts-subtitle"><?php esc_html_e( 'WordPress Integration für ChurchTools', 'churchtools-suite' ); ?></p>
	</div>

	<?php if ( $feedback_result === 'sent' ) : ?>
		<div class="notice notice-success" style="margin: 12px 0 16px;"><p><?php esc_html_e( 'Danke! Die Rückmeldung wurde automatisch per Mail gesendet.', 'churchtools-suite' ); ?></p></div>
	<?php elseif ( $feedback_result === 'declined' ) : ?>
		<div class="notice notice-info" style="margin: 12px 0 16px;"><p><?php esc_html_e( 'Alles klar – es wurde keine Rückmeldung gesendet.', 'churchtools-suite' ); ?></p></div>
	<?php elseif ( $feedback_result === 'error' ) : ?>
		<div class="notice notice-error" style="margin: 12px 0 16px;"><p><?php esc_html_e( 'Die Mail konnte nicht gesendet werden. Bitte Mail-Konfiguration prüfen.', 'churchtools-suite' ); ?></p></div>
	<?php endif; ?>

	<?php if ( ! in_array( $feedback_status, [ 'sent', 'declined', 'answered', 'error' ], true ) ) : ?>
	<div class="notice notice-info" style="margin: 12px 0 16px;">
		<p><strong><?php esc_html_e( 'Kurze Rückfrage:', 'churchtools-suite' ); ?></strong> <?php esc_html_e( 'Dürfen wir eine automatische Mail senden, dass das Plugin bei dir installiert/getestet/genutzt wird?', 'churchtools-suite' ); ?></p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:8px;">
			<input type="hidden" name="action" value="cts_feedback_submit">
			<input type="hidden" name="cts_feedback_redirect" value="<?php echo esc_url( $feedback_redirect ); ?>">
			<?php wp_nonce_field( 'cts_feedback_submit', 'cts_feedback_nonce' ); ?>

			<label style="margin-right:16px;"><input type="checkbox" name="cts_feedback_stage[]" value="installiert"> <?php esc_html_e( 'Installiert', 'churchtools-suite' ); ?></label>
			<label style="margin-right:16px;"><input type="checkbox" name="cts_feedback_stage[]" value="getestet"> <?php esc_html_e( 'Getestet', 'churchtools-suite' ); ?></label>
			<label style="margin-right:16px;"><input type="checkbox" name="cts_feedback_stage[]" value="genutzt"> <?php esc_html_e( 'Genutzt', 'churchtools-suite' ); ?></label>

			<div style="margin-top:10px; display:flex; gap:8px;">
				<button type="submit" name="cts_feedback_choice" value="send" class="button button-primary"><?php esc_html_e( 'Ja, Mail automatisch senden', 'churchtools-suite' ); ?></button>
				<button type="submit" name="cts_feedback_choice" value="skip" class="button"><?php esc_html_e( 'Nein, nicht senden', 'churchtools-suite' ); ?></button>
			</div>
		</form>
	</div>
	<?php endif; ?>

	<div class="cts-tabs">
		<a href="?page=churchtools-suite&tab=dashboard" class="cts-tab <?php echo $active_tab === 'dashboard' ? 'active' : ''; ?>">
			<span>📊</span>
			<?php esc_html_e( 'Dashboard', 'churchtools-suite' ); ?>
		</a>
		<?php if ( current_user_can( 'cts_demo_user' ) ) : ?>
		<a href="?page=churchtools-suite&tab=demo-config" class="cts-tab <?php echo $active_tab === 'demo-config' ? 'active' : ''; ?>" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
			<span>🎛️</span>
			<?php esc_html_e( 'Demo Konfig', 'churchtools-suite' ); ?>
		</a>
		<?php endif; ?>
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
		   case 'demo-config':
			   // Only for demo users
			   if ( current_user_can( 'cts_demo_user' ) ) {
				   $demo_config_file = WP_PLUGIN_DIR . '/churchtools-suite-demo/admin/views/tab-demo-config.php';
				   if ( file_exists( $demo_config_file ) ) {
					   include $demo_config_file;
				   } else {
					   echo '<div class="notice notice-error"><p>Demo Config Tab nicht gefunden. Ist das Demo-Plugin aktiviert?</p></div>';
				   }
			   } else {
				   wp_die( 'Keine Berechtigung.' );
			   }
			   break;
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
