<?php
/**
 * Demo Shortcodes
 *
 * Registers demo-specific shortcodes for user registration.
 * 
 * @package ChurchTools_Suite_Demo
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ChurchTools_Suite_Demo_Shortcodes {
	
	/**
	 * Registration Service
	 *
	 * @var ChurchTools_Suite_Demo_Registration_Service
	 */
	private $registration_service;
	
	/**
	 * Constructor
	 *
	 * @param ChurchTools_Suite_Demo_Registration_Service $registration_service
	 */
	public function __construct( ChurchTools_Suite_Demo_Registration_Service $registration_service ) {
		$this->registration_service = $registration_service;
	}
	
	/**
	 * Initialize shortcodes
	 */
	public function init(): void {
		add_shortcode( 'cts_demo_register', [ $this, 'render_registration_form' ] );
		add_shortcode( 'cts_demo_register_success', [ $this, 'render_registration_success' ] );
		add_shortcode( 'cts_demo_getting_started', [ $this, 'render_getting_started' ] );
		
		// Register AJAX handlers
		add_action( 'wp_ajax_nopriv_cts_demo_register', [ $this, 'ajax_register' ] );
		add_action( 'wp_ajax_cts_demo_register', [ $this, 'ajax_register' ] );
		
		// Enqueue scripts
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}
	
	/**
	 * Render registration form
	 *
	 * @param array $atts Shortcode attributes
	 * @return string HTML output
	 */
	public function render_registration_form( $atts ): string {
		ob_start();
		include CHURCHTOOLS_SUITE_DEMO_PATH . 'templates/demo/registration-form.php';
		return ob_get_clean();
	}
	
	/**
	 * Render registration success page (new in v1.0.2)
	 * 
	 * Usage: [cts_demo_register_success email="user@example.com" password="xxx" demo_url="..."]
	 *
	 * @param array $atts Shortcode attributes
	 * @return string HTML output
	 */
	public function render_registration_success( $atts ): string {
		$atts = shortcode_atts( [
			'email' => '',
			'password' => '',
			'demo_url' => admin_url(),
		], $atts );
		
		if ( empty( $atts['email'] ) || empty( $atts['password'] ) ) {
			return '<p style="color: red;">Registrierungs-Daten fehlen</p>';
		}
		
		// Load response class
		require_once CHURCHTOOLS_SUITE_DEMO_PATH . 'includes/class-demo-registration-response.php';
		
		ob_start();
		ChurchTools_Suite_Demo_Registration_Response::render_success(
			[ 'email' => $atts['email'] ],
			$atts['password'],
			$atts['demo_url']
		);
		return ob_get_clean();
	}
	
	/**
	 * Handle AJAX registration
	 */
	public function ajax_register(): void {
		// Verify nonce
		check_ajax_referer( 'cts_demo_register', 'nonce' );
		
		// Get form data
		$data = [
			'email' => sanitize_email( $_POST['email'] ?? '' ),
			'first_name' => sanitize_text_field( $_POST['first_name'] ?? '' ),
			'last_name' => sanitize_text_field( $_POST['last_name'] ?? '' ),
			'company' => sanitize_text_field( $_POST['company'] ?? '' ),
			'purpose' => sanitize_textarea_field( $_POST['purpose'] ?? '' ),
			'password' => $_POST['password'] ?? '',
			'password_confirm' => $_POST['password_confirm'] ?? '',
		];
		
		// Validate DSGVO checkbox
		if ( empty( $_POST['privacy_accepted'] ) ) {
			wp_send_json_error( [
				'message' => 'Bitte akzeptieren Sie die Datenschutzerklärung',
			] );
		}
		
		// Register user
		$result = $this->registration_service->register_user( $data );
		
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( [
				'message' => $result->get_error_message(),
			] );
		}
		
		// Auto-login user using wp_signon (proper way for AJAX context)
		if ( ! empty( $result['username'] ) && ! empty( $result['auto_login'] ) ) {
			$creds = [
				'user_login'    => $result['username'],
				'user_password' => $data['password'],
				'remember'      => true,
			];
			
			$user = wp_signon( $creds, is_ssl() );
			
			if ( is_wp_error( $user ) ) {
				// Login failed, but registration was successful
				// Redirect to login page instead
				wp_send_json_success( [
					'message' => 'Registrierung erfolgreich! Bitte melden Sie sich jetzt an.',
					'email' => $data['email'],
					'redirect' => wp_login_url( admin_url( 'edit.php?post_type=cts_demo_page' ) ),
				] );
			}
			
			// Explicitly set current user and auth cookie for AJAX context
			wp_set_current_user( $user->ID, $user->user_login );
			wp_set_auth_cookie( $user->ID, true, is_ssl() );
			
			// Trigger login action
			do_action( 'wp_login', $user->user_login, $user );
		}
		
		// Success - redirect to Demo Pages (not main dashboard, because demo users should see their pages)
		wp_send_json_success( [
			'message' => 'Registrierung erfolgreich! Sie werden zu Ihren Demo-Seiten weitergeleitet...',
			'email' => $data['email'],
			'redirect' => admin_url( 'edit.php?post_type=cts_demo_page' ),
		] );
	}
	
	/**
	 * Enqueue scripts and styles
	 */
	public function enqueue_scripts(): void {
		global $post;
		
		// Only enqueue on pages with shortcode
		if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'cts_demo_register' ) ) {
			// Use timestamp for cache busting during development
			$version = CHURCHTOOLS_SUITE_DEMO_VERSION . '.' . time();
			
			wp_enqueue_style(
				'cts-demo-registration',
				CHURCHTOOLS_SUITE_DEMO_URL . 'assets/css/registration.css',
				[],
				$version
			);
			
			wp_enqueue_script(
				'cts-demo-registration',
				CHURCHTOOLS_SUITE_DEMO_URL . 'assets/js/registration.js',
				[ 'jquery' ],
				$version,
				true
			);
			
			wp_localize_script(
				'cts-demo-registration',
				'ctsDemo',
				[
					'ajaxUrl' => admin_url( 'admin-ajax.php' ),
					'nonce' => wp_create_nonce( 'cts_demo_register' ),
					'strings' => [
						'sending' => 'Wird gesendet...',
						'error' => 'Ein Fehler ist aufgetreten',
					],
				]
			);
		}
	}
	
	/**
	 * Render Getting Started Guide
	 *
	 * Zeigt eine Anleitung wie man eigene Demo-Seiten erstellt
	 *
	 * @return string HTML
	 */
	public function render_getting_started(): string {
		$admin_url = admin_url( 'admin.php?page=churchtools-suite' );
		$demo_pages_url = admin_url( 'edit.php?post_type=cts_demo_page' );
		
		ob_start();
		?>
		<div class="cts-demo-getting-started" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 2rem; border-radius: 12px; margin: 2rem 0; box-shadow: 0 4px 20px rgba(0,0,0,0.15);">
			<h2 style="color: white; margin-top: 0; font-size: 1.8rem; display: flex; align-items: center; gap: 0.5rem;">
				<span style="font-size: 2rem;">🚀</span>
				Eigene Event-Ansichten erstellen
			</h2>
			
			<p style="font-size: 1.1rem; line-height: 1.6; margin-bottom: 1.5rem; opacity: 0.95;">
				Diese Seite zeigt nur eine Beispiel-Ansicht. Sie können selbst unbegrenzt viele Seiten mit verschiedenen Templates und Einstellungen erstellen!
			</p>
			
			<div style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem;">
				<h3 style="color: white; margin-top: 0; font-size: 1.3rem; margin-bottom: 1rem;">📋 So geht's in 3 Schritten:</h3>
				
				<ol style="line-height: 1.8; font-size: 1.05rem; margin: 0; padding-left: 1.5rem;">
					<li style="margin-bottom: 1rem;">
						<strong>Backend öffnen:</strong> Klicken Sie auf 
						<a href="<?php echo esc_url( $admin_url ); ?>" style="color: #ffd700; text-decoration: underline; font-weight: 600;">
							ChurchTools Suite Dashboard
						</a>
					</li>
					<li style="margin-bottom: 1rem;">
						<strong>Shortcode generieren:</strong> Im Dashboard finden Sie den "Shortcode Generator" mit interaktiven Einstellungen:
						<ul style="margin-top: 0.5rem; list-style: disc; padding-left: 1.5rem; opacity: 0.9;">
							<li>View-Template wählen (List, Grid, Kalender, Slider, Cover, Countdown)</li>
							<li>Kalender auswählen</li>
							<li>Anzahl Events, Zeitraum, Farben, etc. anpassen</li>
							<li>Live-Vorschau sehen</li>
							<li>Code kopieren</li>
						</ul>
					</li>
					<li style="margin-bottom: 0;">
						<strong>Demo-Seite erstellen:</strong> Gehen Sie zu 
						<a href="<?php echo esc_url( $demo_pages_url ); ?>" style="color: #ffd700; text-decoration: underline; font-weight: 600;">
							Demo Pages → Neue Seite erstellen
						</a>
						<ul style="margin-top: 0.5rem; list-style: disc; padding-left: 1.5rem; opacity: 0.9;">
							<li><strong>Gutenberg Editor:</strong> Block "ChurchTools Events" einfügen</li>
							<li><strong>Shortcode:</strong> Kopierten Code einfügen: <code style="background: rgba(0,0,0,0.3); padding: 0.2rem 0.5rem; border-radius: 4px;">[cts_list view="minimal"]</code></li>
						</ul>
					</li>
				</ol>
			</div>
			
			<div style="display: flex; gap: 1rem; flex-wrap: wrap;">
				<a href="<?php echo esc_url( $admin_url ); ?>" 
				   style="display: inline-flex; align-items: center; gap: 0.5rem; background: white; color: #667eea; padding: 0.75rem 1.5rem; border-radius: 6px; text-decoration: none; font-weight: 600; box-shadow: 0 2px 10px rgba(0,0,0,0.2); transition: all 0.3s;">
					<span>📊</span> Zum Dashboard
				</a>
				<a href="<?php echo esc_url( $demo_pages_url ); ?>" 
				   style="display: inline-flex; align-items: center; gap: 0.5rem; background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); color: white; padding: 0.75rem 1.5rem; border-radius: 6px; text-decoration: none; font-weight: 600; border: 2px solid rgba(255,255,255,0.3); transition: all 0.3s;">
					<span>➕</span> Neue Demo-Seite
				</a>
			</div>
			
			<p style="margin-top: 1.5rem; margin-bottom: 0; opacity: 0.85; font-size: 0.95rem;">
				💡 <strong>Tipp:</strong> Sie können beliebig viele Seiten mit unterschiedlichen Ansichten erstellen und ausprobieren!
			</p>
		</div>
		<?php
		return ob_get_clean();
	}
}
