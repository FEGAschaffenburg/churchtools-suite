<?php
/**
 * Configuration Tab
 *
 * @package ChurchTools_Suite_Demo
 * @since   1.1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get current settings
$demo_duration = get_option( 'cts_demo_duration_days', 30 );
$auto_cleanup = get_option( 'cts_demo_auto_cleanup', true );
$admin_notifications = get_option( 'cts_demo_admin_notifications', true );
$demo_user_limit = get_option( 'cts_demo_user_limit', 0 ); // 0 = unlimited
?>

<div class="tab-configuration">
	<h2><?php _e( 'Plugin-Konfiguration', 'churchtools-suite-demo' ); ?></h2>
	
	<form method="post" id="cts-demo-config-form">
		<?php wp_nonce_field( 'cts_demo_save_config', 'cts_demo_config_nonce' ); ?>
		
		<table class="form-table" role="presentation">
			
			<!-- Demo Duration -->
			<tr>
				<th scope="row">
					<label for="demo_duration">
						<?php _e( 'Demo-Dauer', 'churchtools-suite-demo' ); ?>
					</label>
				</th>
				<td>
					<input 
						type="number" 
						id="demo_duration" 
						name="demo_duration" 
						value="<?php echo esc_attr( $demo_duration ); ?>" 
						min="1" 
						max="365"
						class="small-text"
					/> <?php _e( 'Tage', 'churchtools-suite-demo' ); ?>
					<p class="description">
						<?php _e( 'Wie lange sollen Demo-Accounts aktiv bleiben? (Standard: 30 Tage)', 'churchtools-suite-demo' ); ?>
					</p>
				</td>
			</tr>
			
			<!-- Auto Cleanup -->
			<tr>
				<th scope="row">
					<?php _e( 'Automatische Bereinigung', 'churchtools-suite-demo' ); ?>
				</th>
				<td>
					<fieldset>
						<label>
							<input 
								type="checkbox" 
								name="auto_cleanup" 
								id="auto_cleanup"
								value="1"
								<?php checked( $auto_cleanup, true ); ?>
							/>
							<?php _e( 'Abgelaufene Demo-Accounts automatisch löschen', 'churchtools-suite-demo' ); ?>
						</label>
						<p class="description">
							<?php _e( 'Wenn aktiviert, werden abgelaufene Demo-Accounts täglich automatisch entfernt.', 'churchtools-suite-demo' ); ?>
						</p>
					</fieldset>
				</td>
			</tr>
			
			<!-- Admin Notifications -->
			<tr>
				<th scope="row">
					<?php _e( 'Admin-Benachrichtigungen', 'churchtools-suite-demo' ); ?>
				</th>
				<td>
					<fieldset>
						<label>
							<input 
								type="checkbox" 
								name="admin_notifications" 
								id="admin_notifications"
								value="1"
								<?php checked( $admin_notifications, true ); ?>
							/>
							<?php _e( 'E-Mail-Benachrichtigung bei neuen Demo-Registrierungen', 'churchtools-suite-demo' ); ?>
						</label>
						<p class="description">
							<?php _e( 'Administrator erhält eine E-Mail, wenn sich ein neuer Demo-User registriert.', 'churchtools-suite-demo' ); ?>
						</p>
					</fieldset>
				</td>
			</tr>
			
			<!-- User Limit -->
			<tr>
				<th scope="row">
					<label for="demo_user_limit">
						<?php _e( 'Demo-User Limit', 'churchtools-suite-demo' ); ?>
					</label>
				</th>
				<td>
					<input 
						type="number" 
						id="demo_user_limit" 
						name="demo_user_limit" 
						value="<?php echo esc_attr( $demo_user_limit ); ?>" 
						min="0" 
						max="1000"
						class="small-text"
					/> <?php _e( 'User', 'churchtools-suite-demo' ); ?>
					<p class="description">
						<?php _e( 'Maximale Anzahl gleichzeitiger Demo-Accounts (0 = unbegrenzt)', 'churchtools-suite-demo' ); ?>
					</p>
				</td>
			</tr>
			
			<!-- BCC Email -->
			<tr>
				<th scope="row">
					<label for="bcc_email">
						<?php _e( 'BCC E-Mail-Adresse', 'churchtools-suite-demo' ); ?>
					</label>
				</th>
				<td>
					<input 
						type="email" 
						id="bcc_email" 
						name="bcc_email" 
						value="<?php echo esc_attr( get_option( 'cts_demo_bcc_email', '' ) ); ?>" 
						class="regular-text"
						placeholder="beispiel@domain.de"
					/>
					<p class="description">
						<?php _e( 'Optional: E-Mail-Adresse für BCC bei neuen Registrierungen (zusätzlich zur Haupt-Admin-E-Mail)', 'churchtools-suite-demo' ); ?>
					</p>
				</td>
			</tr>
			
		</table>
		
		<?php submit_button( __( 'Einstellungen speichern', 'churchtools-suite-demo' ) ); ?>
	</form>
	
	<!-- Current Statistics -->
	<hr>
	
	<h3><?php _e( 'Aktuelle Statistiken', 'churchtools-suite-demo' ); ?></h3>
	
	<?php
	// Get repository
	$repo = new ChurchTools_Suite_Demo_Users_Repository();
	$stats = $repo->get_statistics();
	
	// Get WP demo users
	$wp_demo_users = get_users( [ 'role' => 'demo_tester' ] );
	$active_wp_users = count( $wp_demo_users );
	?>
	
	<div class="cts-stats-grid">
		<div class="cts-stat-card">
			<span class="stat-value"><?php echo number_format_i18n( $stats['total'] ); ?></span>
			<span class="stat-label"><?php _e( 'Gesamt Registrierungen', 'churchtools-suite-demo' ); ?></span>
		</div>
		
		<div class="cts-stat-card">
			<span class="stat-value"><?php echo number_format_i18n( $stats['verified'] ); ?></span>
			<span class="stat-label"><?php _e( 'Verifizierte Accounts', 'churchtools-suite-demo' ); ?></span>
		</div>
		
		<div class="cts-stat-card">
			<span class="stat-value"><?php echo number_format_i18n( $active_wp_users ); ?></span>
			<span class="stat-label"><?php _e( 'Aktive WP-Users', 'churchtools-suite-demo' ); ?></span>
		</div>
		
		<div class="cts-stat-card">
			<span class="stat-value"><?php echo number_format_i18n( $stats['unverified'] ); ?></span>
			<span class="stat-label"><?php _e( 'Nicht verifiziert', 'churchtools-suite-demo' ); ?></span>
		</div>
	</div>
	
	<div class="cts-info-box">
		<p><strong><?php _e( 'Hinweis:', 'churchtools-suite-demo' ); ?></strong></p>
		<ul>
			<li><?php _e( 'Aktive WP-Users sind Benutzer mit der Rolle "Demo Tester"', 'churchtools-suite-demo' ); ?></li>
			<li><?php _e( 'Die automatische Bereinigung läuft täglich um 3 Uhr morgens', 'churchtools-suite-demo' ); ?></li>
			<li><?php _e( 'Demo-User können nur auf ihre eigenen Demo-Seiten und isolierte Daten zugreifen', 'churchtools-suite-demo' ); ?></li>
		</ul>
	</div>
</div>

<script>
jQuery(document).ready(function($) {
	$('#cts-demo-config-form').on('submit', function(e) {
		e.preventDefault();
		
		const $form = $(this);
		const $submitBtn = $form.find('[type="submit"]');
		const originalText = $submitBtn.val();
		
		$submitBtn.prop('disabled', true).val('<?php _e( 'Speichern...', 'churchtools-suite-demo' ); ?>');
		
		$.ajax({
			url: ajaxurl,
			method: 'POST',
			data: {
				action: 'cts_demo_save_config',
				nonce: $('#cts_demo_config_nonce').val(),
				demo_duration: $('#demo_duration').val(),
				auto_cleanup: $('#auto_cleanup').is(':checked') ? 1 : 0,
				admin_notifications: $('#admin_notifications').is(':checked') ? 1 : 0,
				demo_user_limit: $('#demo_user_limit').val(),
				bcc_email: $('#bcc_email').val()
			},
			success: function(response) {
				if (response.success) {
					// Show success notice
					$form.before('<div class="notice notice-success is-dismissible"><p>' + response.data.message + '</p></div>');
					setTimeout(function() {
						$('.notice-success').fadeOut();
					}, 3000);
				} else {
					alert('Fehler: ' + response.data.message);
				}
			},
			error: function() {
				alert('<?php _e( 'Ein Fehler ist aufgetreten. Bitte versuchen Sie es erneut.', 'churchtools-suite-demo' ); ?>');
			},
			complete: function() {
				$submitBtn.prop('disabled', false).val(originalText);
			}
		});
	});
});
</script>
