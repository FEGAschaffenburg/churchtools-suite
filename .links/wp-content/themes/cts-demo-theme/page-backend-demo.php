<?php
/**
 * Template Name: Backend Demo
 * Template Post Type: page
 * 
 * Backend-Demo Registrierungsseite
 * 
 * @package ChurchTools_Suite_Demo_Theme
 * @since 1.0.0
 */

get_header();
?>

<div class="page-wrapper">
	<div class="container">
		<article class="page-content">
			
			<?php while ( have_posts() ) : the_post(); ?>
				
				<div class="entry-content">
					<?php the_content(); ?>
					
					<?php
					// Load demo registration form template
					// Try to find the demo plugin directory (may have version suffix)
					$plugin_dir = WP_PLUGIN_DIR;
					$demo_plugin_path = null;
					
					// Check for exact match first
					$possible_plugin_dirs = [
						'churchtools-suite-demo',
						'churchtools-suite-demo-' . get_option( 'cts_demo_plugin_version', '1.0.5.16' ),
					];
					
					// Scan for any churchtools-suite-demo* directory
					if ( is_dir( $plugin_dir ) ) {
						$plugins = scandir( $plugin_dir );
						foreach ( $plugins as $plugin ) {
							if ( strpos( $plugin, 'churchtools-suite-demo' ) === 0 && is_dir( $plugin_dir . '/' . $plugin ) ) {
								$template_path = $plugin_dir . '/' . $plugin . '/templates/demo/registration-form.php';
								if ( file_exists( $template_path ) ) {
									$demo_plugin_path = $template_path;
									break;
								}
							}
						}
					}
					
					if ( $demo_plugin_path && file_exists( $demo_plugin_path ) ) {
						include $demo_plugin_path;
					} else {
						echo '<div class="cts-demo-registration" style="padding: 40px; text-align: center;">';
						echo '<p style="color: #991b1b; font-size: 18px; margin-bottom: 20px;">⚠️ Das Registrierungsformular konnte nicht geladen werden.</p>';
						echo '<p style="color: #64748b;">Bitte kontaktieren Sie den Administrator.</p>';
						if ( current_user_can( 'manage_options' ) ) {
							echo '<details style="margin-top: 20px; text-align: left; max-width: 600px; margin-left: auto; margin-right: auto;">';
							echo '<summary style="cursor: pointer; color: #3b82f6;">🔧 Debug-Informationen (nur für Administratoren)</summary>';
							echo '<p style="margin-top: 10px; font-size: 14px; color: #666;">Gesuchte Pfade:</p>';
							echo '<ul style="font-size: 12px; color: #999; list-style: none; padding-left: 0;">';
							$debug_paths = [
								$plugin_dir . '/churchtools-suite-demo/templates/demo/registration-form.php',
								$plugin_dir . '/churchtools-suite-demo-1.0.5.16/templates/demo/registration-form.php',
							];
							foreach ( $debug_paths as $path ) {
								echo '<li style="margin-bottom: 5px;">';
								echo file_exists( $path ) ? '✅ ' : '❌ ';
								echo esc_html( $path );
								echo '</li>';
							}
							echo '</ul>';
							echo '<p style="margin-top: 10px; font-size: 14px; color: #666;">Plugin-Verzeichnis: <code>' . esc_html( $plugin_dir ) . '</code></p>';
							echo '</details>';
						}
						echo '</div>';
					}
					?>
				</div>
				
			<?php endwhile; ?>
			
		</article>
	</div>
</div>

<style>
/* Backend Demo Page Styles */
.cts-demo-registration {
	max-width: 800px;
	margin: 0 auto;
	padding: 40px 20px;
}

.cts-demo-registration-header {
	text-align: center;
	margin-bottom: 40px;
}

.cts-demo-registration-header h2 {
	font-size: 32px;
	margin-bottom: 12px;
	color: #1e293b;
}

.cts-demo-registration-header p {
	font-size: 18px;
	color: #64748b;
}

.cts-demo-form {
	background: #ffffff;
	border: 1px solid #e2e8f0;
	border-radius: 8px;
	padding: 40px;
	margin-bottom: 40px;
}

.cts-form-group {
	margin-bottom: 24px;
}

.cts-form-group label {
	display: block;
	font-weight: 600;
	margin-bottom: 8px;
	color: #334155;
}

.cts-form-group .required {
	color: #dc2626;
}

.cts-form-group input[type="email"],
.cts-form-group input[type="text"],
.cts-form-group input[type="password"],
.cts-form-group textarea {
	width: 100%;
	padding: 12px 16px;
	border: 1px solid #cbd5e1;
	border-radius: 6px;
	font-size: 16px;
	transition: border-color 0.2s;
}

.cts-form-group input:focus,
.cts-form-group textarea:focus {
	outline: none;
	border-color: #3b82f6;
	box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.cts-field-hint {
	font-size: 13px;
	color: #64748b;
	margin-top: 4px;
}

.cts-password-match.error {
	color: #dc2626;
}

.cts-password-match.success {
	color: #059669;
}

.cts-checkbox-group {
	background: #f8fafc;
	padding: 16px;
	border-radius: 6px;
}

.cts-checkbox-label {
	display: flex;
	align-items: flex-start;
	gap: 12px;
	cursor: pointer;
}

.cts-checkbox-label input[type="checkbox"] {
	margin-top: 4px;
	width: 18px;
	height: 18px;
	cursor: pointer;
}

.cts-privacy-note {
	margin-top: 8px;
	font-size: 14px;
	color: #64748b;
	padding-left: 30px;
}

.cts-form-actions {
	margin-top: 32px;
}

.cts-submit-btn {
	width: 100%;
	padding: 14px 24px;
	background: #3b82f6;
	color: white;
	border: none;
	border-radius: 6px;
	font-size: 16px;
	font-weight: 600;
	cursor: pointer;
	transition: background 0.2s;
}

.cts-submit-btn:hover {
	background: #2563eb;
}

.cts-submit-btn:disabled {
	background: #94a3b8;
	cursor: not-allowed;
}

.btn-spinner {
	display: inline-flex;
	align-items: center;
	gap: 8px;
}

.spinner {
	display: inline-block;
	width: 16px;
	height: 16px;
	border: 2px solid rgba(255, 255, 255, 0.3);
	border-top-color: white;
	border-radius: 50%;
	animation: spin 0.6s linear infinite;
}

@keyframes spin {
	to { transform: rotate(360deg); }
}

.cts-form-message {
	margin-top: 20px;
	padding: 16px;
	border-radius: 6px;
	font-size: 14px;
}

.cts-form-message.success {
	background: #dcfce7;
	color: #166534;
	border: 1px solid #86efac;
}

.cts-form-message.error {
	background: #fee2e2;
	color: #991b1b;
	border: 1px solid #fca5a5;
}

.cts-demo-info {
	background: #f8fafc;
	border: 1px solid #e2e8f0;
	border-radius: 8px;
	padding: 32px;
}

.cts-demo-info h3 {
	margin-bottom: 16px;
	color: #1e293b;
}

.cts-demo-info ol {
	margin-left: 24px;
}

.cts-demo-info li {
	margin-bottom: 12px;
	color: #475569;
	line-height: 1.6;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
	const form = document.getElementById('cts-demo-registration-form');
	if (!form) return;
	
	// Passwort-Validierung
	const passwordField = document.getElementById('cts-demo-password');
	const confirmField = document.getElementById('cts-demo-password-confirm');
	const matchHint = document.querySelector('.cts-password-match');
	
	if (passwordField && confirmField && matchHint) {
		function checkPasswordMatch() {
			const password = passwordField.value;
			const confirm = confirmField.value;
			
			if (confirm.length === 0) {
				matchHint.style.display = 'none';
				return;
			}
			
			matchHint.style.display = 'block';
			
			if (password === confirm) {
				matchHint.className = 'cts-field-hint cts-password-match success';
				matchHint.textContent = '✓ Passwörter stimmen überein';
			} else {
				matchHint.className = 'cts-field-hint cts-password-match error';
				matchHint.textContent = '✗ Passwörter stimmen nicht überein';
			}
		}
		
		passwordField.addEventListener('input', checkPasswordMatch);
		confirmField.addEventListener('input', checkPasswordMatch);
	}
	
	form.addEventListener('submit', async function(e) {
		e.preventDefault();
		
		// Passwort-Validierung vor dem Absenden
		if (passwordField && confirmField) {
			if (passwordField.value !== confirmField.value) {
				alert('Die Passwörter stimmen nicht überein!');
				return;
			}
			if (passwordField.value.length < 8) {
				alert('Das Passwort muss mindestens 8 Zeichen lang sein!');
				return;
			}
		}
		
		const submitBtn = form.querySelector('.cts-submit-btn');
		const btnText = submitBtn.querySelector('.btn-text');
		const btnSpinner = submitBtn.querySelector('.btn-spinner');
		const messageDiv = form.querySelector('.cts-form-message');
		
		// Disable submit button
		submitBtn.disabled = true;
		btnText.style.display = 'none';
		btnSpinner.style.display = 'inline-flex';
		messageDiv.style.display = 'none';
		
		const formData = new FormData(form);
		formData.append('action', 'cts_demo_register');
		formData.append('nonce', '<?php echo wp_create_nonce("cts_demo_register"); ?>');
		
		try {
			const response = await fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
				method: 'POST',
				body: formData
			});
			
			const data = await response.json();
			
			if (data.success) {
				messageDiv.className = 'cts-form-message success';
				messageDiv.textContent = (data.data && data.data.message) || 'Registrierung erfolgreich! Bitte prüfen Sie Ihre E-Mails.';
				messageDiv.style.display = 'block';
				form.reset();
				if (matchHint) matchHint.style.display = 'none';
			} else {
				throw new Error((data.data && data.data.message) || data.message || 'Registrierung fehlgeschlagen');
			}
		} catch (error) {
			messageDiv.className = 'cts-form-message error';
			messageDiv.textContent = error.message || 'Ein Fehler ist aufgetreten. Bitte versuchen Sie es später erneut.';
			messageDiv.style.display = 'block';
			
			// Debug-Ausgabe in Konsole
			console.error('Registration error:', error);
		} finally {
			submitBtn.disabled = false;
			btnText.style.display = 'inline';
			btnSpinner.style.display = 'none';
		}
	});
});
</script>

<?php get_footer(); ?>
