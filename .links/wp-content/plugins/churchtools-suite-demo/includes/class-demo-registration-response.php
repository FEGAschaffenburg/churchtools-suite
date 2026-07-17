<?php
/**
 * Registration Response Handler
 * 
 * Nach erfolgreicher Registrierung werden die Zugansdaten angezeigt
 * 
 * @package ChurchTools_Suite_Demo
 * @since   1.0.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ChurchTools_Suite_Demo_Registration_Response {
	
	/**
	 * Render successful registration response
	 * 
	 * @param array $user_data Registered user data
	 * @param string $password User's password
	 * @param string $demo_url URL to demo access
	 */
	public static function render_success( array $user_data, string $password, string $demo_url ): void {
		?>
		<div class="cts-demo-registration-success">
			<div class="success-header">
				<h2>✅ <?php esc_html_e( 'Registrierung erfolgreich!', 'churchtools-suite-demo' ); ?></h2>
				<p><?php esc_html_e( 'Willkommen zur ChurchTools Suite Demo. Hier sind deine Zugansdaten:', 'churchtools-suite-demo' ); ?></p>
			</div>
			
			<div class="credentials-box">
				<h3><?php esc_html_e( 'Deine Anmeldedaten', 'churchtools-suite-demo' ); ?></h3>
				
				<div class="credential-item">
					<label><?php esc_html_e( 'E-Mail:', 'churchtools-suite-demo' ); ?></label>
					<div class="credential-value">
						<input type="text" value="<?php echo esc_attr( $user_data['email'] ); ?>" readonly onclick="this.select()">
						<button class="copy-btn" data-value="<?php echo esc_attr( $user_data['email'] ); ?>">
							<?php esc_html_e( 'Kopieren', 'churchtools-suite-demo' ); ?>
						</button>
					</div>
				</div>
				
				<div class="credential-item">
					<label><?php esc_html_e( 'Passwort:', 'churchtools-suite-demo' ); ?></label>
					<div class="credential-value">
						<input type="password" value="<?php echo esc_attr( $password ); ?>" readonly id="password-field" onclick="this.select()">
						<button class="toggle-password-btn" data-target="password-field" onclick="togglePasswordVisibility(this)">
							<?php esc_html_e( 'Anzeigen', 'churchtools-suite-demo' ); ?>
						</button>
						<button class="copy-btn" data-value="<?php echo esc_attr( $password ); ?>">
							<?php esc_html_e( 'Kopieren', 'churchtools-suite-demo' ); ?>
						</button>
					</div>
				</div>
			</div>
			
			<div class="next-steps">
				<h3><?php esc_html_e( 'Nächste Schritte', 'churchtools-suite-demo' ); ?></h3>
				
				<div class="step">
					<strong><?php esc_html_e( '1. Überprüfe deine E-Mail', 'churchtools-suite-demo' ); ?></strong>
					<p><?php esc_html_e( 'Eine Bestätigungsemail wurde an deine E-Mail-Adresse gesendet.', 'churchtools-suite-demo' ); ?></p>
				</div>
				
				<div class="step">
					<strong><?php esc_html_e( '2. Bestätige deine E-Mail', 'churchtools-suite-demo' ); ?></strong>
					<p><?php esc_html_e( 'Klicke auf den Link in der E-Mail um deine E-Mail-Adresse zu bestätigen.', 'churchtools-suite-demo' ); ?></p>
				</div>
				
				<div class="step">
					<strong><?php esc_html_e( '3. Melde dich an', 'churchtools-suite-demo' ); ?></strong>
					<p><?php esc_html_e( 'Nach Bestätigung kannst du dich mit deinen Anmeldedaten anmelden und die Demo testen.', 'churchtools-suite-demo' ); ?></p>
				</div>
			</div>
			
			<div class="demo-access">
				<h3><?php esc_html_e( 'Zur Demo', 'churchtools-suite-demo' ); ?></h3>
				<a href="<?php echo esc_url( $demo_url ); ?>" class="btn btn-primary">
					<?php esc_html_e( 'Zur ChurchTools Suite Demo →', 'churchtools-suite-demo' ); ?>
				</a>
			</div>
			
			<div class="info-box">
				<p>
					<strong><?php esc_html_e( '❓ Hilfe', 'churchtools-suite-demo' ); ?></strong><br>
					<?php esc_html_e( 'Sollte etwas nicht funktionieren, kontaktiere uns bitte:', 'churchtools-suite-demo' ); ?>
					<a href="mailto:support@example.com">support@example.com</a>
				</p>
			</div>
		</div>
		
		<style>
			.cts-demo-registration-success {
				max-width: 600px;
				margin: 40px auto;
				padding: 30px;
				background: #f8fafc;
				border-radius: 8px;
				border-left: 4px solid #22c55e;
			}
			
			.success-header {
				margin-bottom: 30px;
			}
			
			.success-header h2 {
				color: #22c55e;
				margin-top: 0;
			}
			
			.credentials-box {
				background: white;
				padding: 20px;
				border-radius: 6px;
				margin-bottom: 30px;
				border: 1px solid #e5e7eb;
			}
			
			.credentials-box h3 {
				margin-top: 0;
				color: #1e293b;
			}
			
			.credential-item {
				margin-bottom: 20px;
			}
			
			.credential-item:last-child {
				margin-bottom: 0;
			}
			
			.credential-item label {
				display: block;
				font-weight: 600;
				margin-bottom: 8px;
				color: #475569;
			}
			
			.credential-value {
				display: flex;
				gap: 8px;
			}
			
			.credential-value input {
				flex: 1;
				padding: 10px 12px;
				border: 1px solid #cbd5e1;
				border-radius: 4px;
				font-family: monospace;
				font-size: 14px;
			}
			
			.credential-value button {
				padding: 10px 14px;
				background: #3b82f6;
				color: white;
				border: none;
				border-radius: 4px;
				cursor: pointer;
				font-size: 12px;
				font-weight: 500;
				white-space: nowrap;
				transition: background 0.2s;
			}
			
			.credential-value button:hover {
				background: #2563eb;
			}
			
			.next-steps {
				background: white;
				padding: 20px;
				border-radius: 6px;
				margin-bottom: 30px;
				border: 1px solid #e5e7eb;
			}
			
			.next-steps h3 {
				margin-top: 0;
				color: #1e293b;
			}
			
			.step {
				margin-bottom: 16px;
				padding-bottom: 16px;
				border-bottom: 1px solid #e5e7eb;
			}
			
			.step:last-child {
				margin-bottom: 0;
				padding-bottom: 0;
				border-bottom: none;
			}
			
			.step strong {
				color: #1e293b;
			}
			
			.step p {
				margin: 8px 0 0 0;
				color: #64748b;
				font-size: 14px;
			}
			
			.demo-access {
				text-align: center;
				margin-bottom: 30px;
			}
			
			.demo-access .btn {
				display: inline-block;
				padding: 12px 32px;
				background: #22c55e;
				color: white;
				text-decoration: none;
				border-radius: 6px;
				font-weight: 600;
				transition: background 0.2s;
			}
			
			.demo-access .btn:hover {
				background: #16a34a;
			}
			
			.info-box {
				background: #fef3c7;
				border-left: 4px solid #f59e0b;
				padding: 16px;
				border-radius: 4px;
				font-size: 14px;
				color: #92400e;
			}
			
			.info-box p {
				margin: 0;
			}
			
			.info-box a {
				color: #d97706;
				text-decoration: none;
				font-weight: 600;
			}
		</style>
		
		<script>
			function togglePasswordVisibility(btn) {
				const target = document.getElementById(btn.getAttribute('data-target'));
				if (target.type === 'password') {
					target.type = 'text';
					btn.textContent = '<?php esc_html_e( 'Verbergen', 'churchtools-suite-demo' ); ?>';
				} else {
					target.type = 'password';
					btn.textContent = '<?php esc_html_e( 'Anzeigen', 'churchtools-suite-demo' ); ?>';
				}
			}
			
			document.querySelectorAll('.copy-btn').forEach(btn => {
				btn.addEventListener('click', function(e) {
					e.preventDefault();
					const value = this.getAttribute('data-value');
					navigator.clipboard.writeText(value).then(() => {
						const oldText = this.textContent;
						this.textContent = '✓ <?php esc_html_e( 'Kopiert', 'churchtools-suite-demo' ); ?>';
						setTimeout(() => {
							this.textContent = oldText;
						}, 2000);
					});
				});
			});
		</script>
		<?php
	}
	
	/**
	 * Render error response
	 * 
	 * @param WP_Error $error Error object
	 */
	public static function render_error( WP_Error $error ): void {
		?>
		<div class="cts-demo-registration-error">
			<h2>❌ <?php esc_html_e( 'Registrierung fehlgeschlagen', 'churchtools-suite-demo' ); ?></h2>
			<p class="error-message">
				<?php echo wp_kses_post( $error->get_error_message() ); ?>
			</p>
			<a href="javascript:history.back()" class="btn btn-secondary">
				<?php esc_html_e( '← Zurück zum Formular', 'churchtools-suite-demo' ); ?>
			</a>
		</div>
		
		<style>
			.cts-demo-registration-error {
				max-width: 600px;
				margin: 40px auto;
				padding: 30px;
				background: #fef2f2;
				border-radius: 8px;
				border-left: 4px solid #ef4444;
			}
			
			.cts-demo-registration-error h2 {
				color: #dc2626;
				margin-top: 0;
			}
			
			.error-message {
				color: #991b1b;
				margin-bottom: 20px;
			}
			
			.btn {
				display: inline-block;
				padding: 10px 24px;
				border-radius: 4px;
				text-decoration: none;
				font-weight: 600;
				transition: background 0.2s;
			}
			
			.btn-secondary {
				background: #64748b;
				color: white;
			}
			
			.btn-secondary:hover {
				background: #475569;
			}
		</style>
		<?php
	}
}
