<?php
/**
 * Demo Configuration Tab
 * 
 * Allows demo users to toggle between demo data and own ChurchTools instance
 *
 * @package ChurchTools_Suite_Demo
 * @since   1.0.7.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Only for demo users
if ( ! current_user_can( 'cts_demo_user' ) ) {
	wp_die( __( 'Sie haben keine Berechtigung, auf diese Seite zuzugreifen.', 'churchtools-suite' ) );
}

// Load user settings
require_once CHURCHTOOLS_SUITE_DEMO_PATH . 'includes/class-user-settings.php';
$demo_mode = ChurchTools_Suite_User_Settings::is_demo_mode();
?>

<div class="cts-demo-config">
	
	<div class="cts-card">
		<div class="cts-card-header">
			<span class="cts-card-icon">🎛️</span>
			<h3><?php esc_html_e( 'Demo-Modus Konfiguration', 'churchtools-suite' ); ?></h3>
		</div>
		<div class="cts-card-body">
			
			<div class="cts-demo-status-box" style="background: <?php echo $demo_mode ? '#e3f2fd' : '#f3e5f5'; ?>; padding: 24px; border-radius: 8px; margin-bottom: 24px;">
				<div style="display: flex; align-items: center; gap: 16px; margin-bottom: 16px;">
					<span style="font-size: 48px;"><?php echo $demo_mode ? '📘' : '📗'; ?></span>
					<div style="flex: 1;">
						<h2 style="margin: 0 0 8px 0; font-size: 24px;">
							<?php if ( $demo_mode ) : ?>
								<?php esc_html_e( 'Demo-Modus ist AKTIV', 'churchtools-suite' ); ?>
							<?php else : ?>
								<?php esc_html_e( 'Eigene ChurchTools-Instanz', 'churchtools-suite' ); ?>
							<?php endif; ?>
						</h2>
						<p style="margin: 0; font-size: 16px; color: #555;">
							<?php if ( $demo_mode ) : ?>
								<?php esc_html_e( 'Sie nutzen vorkonfigurierte Demo-Daten zum Testen des Plugins.', 'churchtools-suite' ); ?>
							<?php else : ?>
								<?php esc_html_e( 'Sie können Ihre eigenen ChurchTools-Zugangsdaten verwenden.', 'churchtools-suite' ); ?>
							<?php endif; ?>
						</p>
					</div>
				</div>
			</div>
			
			<!-- Mode Description -->
			<?php if ( $demo_mode ) : ?>
				<div class="cts-info-box" style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 16px; margin-bottom: 24px;">
					<h4 style="margin: 0 0 12px 0;">
						<span style="font-size: 20px;">ℹ️</span>
						<?php esc_html_e( 'Was bedeutet Demo-Modus?', 'churchtools-suite' ); ?>
					</h4>
					<ul style="margin: 0; padding-left: 20px; line-height: 1.8;">
						<li><?php esc_html_e( 'Sie sehen vorkonfigurierte Demo-Termine (Gottesdienste, Bibelkreise, etc.)', 'churchtools-suite' ); ?></li>
						<li><?php esc_html_e( 'Alle Daten sind isoliert - nur Sie sehen Ihre Demo-Daten', 'churchtools-suite' ); ?></li>
						<li><?php esc_html_e( 'Keine echte ChurchTools-Verbindung erforderlich', 'churchtools-suite' ); ?></li>
						<li><?php esc_html_e( 'Perfekt zum Testen von Templates und Layouts', 'churchtools-suite' ); ?></li>
						<li><?php esc_html_e( 'Synchronisation ist simuliert (keine echten API-Calls)', 'churchtools-suite' ); ?></li>
					</ul>
				</div>
				
				<div class="cts-action-box" style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
					<h4 style="margin: 0 0 12px 0; font-size: 18px;">
						🚀 <?php esc_html_e( 'Eigene ChurchTools-Instanz testen?', 'churchtools-suite' ); ?>
					</h4>
					<p style="margin: 0 0 16px 0;">
						<?php esc_html_e( 'Klicken Sie auf den Button unten, um den Demo-Modus zu deaktivieren. Danach können Sie unter "Einstellungen → API & Verbindung" Ihre eigenen ChurchTools-Zugangsdaten eingeben.', 'churchtools-suite' ); ?>
					</p>
					<button type="button" id="cts-demo-mode-toggle" class="cts-button cts-button-primary" data-mode="disable" style="font-size: 16px; padding: 12px 24px;">
						<span style="font-size: 20px;">🔓</span>
						<?php esc_html_e( 'Demo-Modus beenden & eigene Daten nutzen', 'churchtools-suite' ); ?>
					</button>
				</div>
			<?php else : ?>
				<div class="cts-info-box" style="background: #e8f5e9; border-left: 4px solid #4caf50; padding: 16px; margin-bottom: 24px;">
					<h4 style="margin: 0 0 12px 0;">
						<span style="font-size: 20px;">✅</span>
						<?php esc_html_e( 'Eigene ChurchTools-Instanz aktiv', 'churchtools-suite' ); ?>
					</h4>
					<ul style="margin: 0; padding-left: 20px; line-height: 1.8;">
						<li><?php esc_html_e( 'Sie können unter "Einstellungen → API & Verbindung" Ihre ChurchTools-Zugänge eingeben', 'churchtools-suite' ); ?></li>
						<li><?php esc_html_e( 'Termine werden von Ihrer echten ChurchTools-Instanz synchronisiert', 'churchtools-suite' ); ?></li>
						<li><?php esc_html_e( 'Alle Standard-Funktionen sind verfügbar', 'churchtools-suite' ); ?></li>
						<li><?php esc_html_e( 'Ihre Daten bleiben privat und isoliert', 'churchtools-suite' ); ?></li>
					</ul>
				</div>
				
				<div class="cts-action-box" style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
					<h4 style="margin: 0 0 12px 0; font-size: 18px;">
						🔙 <?php esc_html_e( 'Zurück zu Demo-Daten?', 'churchtools-suite' ); ?>
					</h4>
					<p style="margin: 0 0 16px 0;">
						<?php esc_html_e( 'Wenn Sie wieder vorkonfigurierte Demo-Daten nutzen möchten, können Sie den Demo-Modus jederzeit wieder aktivieren.', 'churchtools-suite' ); ?>
					</p>
					<button type="button" id="cts-demo-mode-toggle" class="cts-button" data-mode="enable" style="font-size: 16px; padding: 12px 24px;">
						<span style="font-size: 20px;">🔒</span>
						<?php esc_html_e( 'Demo-Modus wieder aktivieren', 'churchtools-suite' ); ?>
					</button>
				</div>
			<?php endif; ?>
			
			<div id="cts-demo-toggle-result" style="display: none; margin-top: 20px; padding: 16px; border-radius: 8px;"></div>
			
		</div>
	</div>
	
	<!-- Info Box -->
	<div class="cts-card" style="margin-top: 20px;">
		<div class="cts-card-header">
			<span class="cts-card-icon">💡</span>
			<h3><?php esc_html_e( 'Tipps & Hinweise', 'churchtools-suite' ); ?></h3>
		</div>
		<div class="cts-card-body">
			<div style="line-height: 1.8;">
				<p><strong><?php esc_html_e( 'Demo-Modus:', 'churchtools-suite' ); ?></strong></p>
				<ul style="margin-bottom: 16px;">
					<li><?php esc_html_e( 'Ideal zum Testen von Shortcodes, Templates und Layouts', 'churchtools-suite' ); ?></li>
					<li><?php esc_html_e( 'Keine ChurchTools-Lizenz erforderlich', 'churchtools-suite' ); ?></li>
					<li><?php esc_html_e( 'Daten sind nur für Sie sichtbar (Multi-User-Isolation)', 'churchtools-suite' ); ?></li>
				</ul>
				
				<p><strong><?php esc_html_e( 'Eigene ChurchTools-Instanz:', 'churchtools-suite' ); ?></strong></p>
				<ul>
					<li><?php esc_html_e( 'Benötigt gültige ChurchTools-Zugangsdaten', 'churchtools-suite' ); ?></li>
					<li><?php esc_html_e( 'Echte Event-Synchronisation aus Ihrer Gemeinde', 'churchtools-suite' ); ?></li>
					<li><?php esc_html_e( 'Alle Features wie in einer Produktiv-Installation', 'churchtools-suite' ); ?></li>
				</ul>
			</div>
		</div>
	</div>
	
</div>

<script>
jQuery(document).ready(function($) {
	const toggleBtn = $('#cts-demo-mode-toggle');
	const result = $('#cts-demo-toggle-result');
	
	toggleBtn.on('click', function() {
		const mode = $(this).data('mode');
		const enabled = (mode === 'enable');
		
		// Disable button
		toggleBtn.prop('disabled', true).html('<span style="font-size: 20px;">⏳</span> ' + 
			<?php echo wp_json_encode( __( 'Bitte warten...', 'churchtools-suite' ) ); ?>
		);
		
		// Send AJAX request
		$.ajax({
			url: ajaxurl,
			method: 'POST',
			data: {
				action: 'cts_demo_toggle_mode',
				nonce: '<?php echo wp_create_nonce( 'cts_demo_toggle' ); ?>',
				enabled: enabled ? 1 : 0
			},
			success: function(response) {
				if (response.success) {
					result.html('<strong style="color: #46b450;">✅ ' + response.data.message + '</strong><br>' +
						<?php echo wp_json_encode( __( 'Seite wird neu geladen...', 'churchtools-suite' ) ); ?>
					).css({
						'display': 'block',
						'background': '#e8f5e9',
						'border-left': '4px solid #4caf50'
					});
					
					setTimeout(function() {
						location.reload();
					}, 1500);
				} else {
					result.html('<strong style="color: #dc3232;">❌ ' + response.data.message + '</strong>')
						.css({
							'display': 'block',
							'background': '#ffebee',
							'border-left': '4px solid #f44336'
						});
					
					// Re-enable button
					toggleBtn.prop('disabled', false).html(
						enabled ? 
							'<span style="font-size: 20px;">🔒</span> <?php esc_html_e( 'Demo-Modus wieder aktivieren', 'churchtools-suite' ); ?>' :
							'<span style="font-size: 20px;">🔓</span> <?php esc_html_e( 'Demo-Modus beenden & eigene Daten nutzen', 'churchtools-suite' ); ?>'
					);
				}
			},
			error: function() {
				result.html('<strong style="color: #dc3232;">❌ <?php esc_html_e( 'Fehler beim Umschalten. Bitte versuchen Sie es erneut.', 'churchtools-suite' ); ?></strong>')
					.css({
						'display': 'block',
						'background': '#ffebee',
						'border-left': '4px solid #f44336'
					});
				
				// Re-enable button
				toggleBtn.prop('disabled', false).html(
					enabled ? 
						'<span style="font-size: 20px;">🔒</span> <?php esc_html_e( 'Demo-Modus wieder aktivieren', 'churchtools-suite' ); ?>' :
						'<span style="font-size: 20px;">🔓</span> <?php esc_html_e( 'Demo-Modus beenden & eigene Daten nutzen', 'churchtools-suite' ); ?>'
				);
			}
		});
	});
});
</script>

<style>
.cts-demo-config .cts-card {
	margin-bottom: 0;
}
.cts-demo-config .cts-button {
	display: inline-flex;
	align-items: center;
	gap: 8px;
	cursor: pointer;
	transition: all 0.2s;
}
.cts-demo-config .cts-button:hover {
	transform: translateY(-2px);
	box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.cts-demo-config .cts-button:disabled {
	opacity: 0.6;
	cursor: not-allowed;
	transform: none;
}
</style>
