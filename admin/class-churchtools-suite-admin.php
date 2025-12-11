<?php
/**
 * Admin Area Handler
 *
 * @package ChurchTools_Suite
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ChurchTools_Suite_Admin {
	
	/**
	 * Plugin version
	 */
	private string $version;
	
	/**
	 * Initialize admin area
	 */
	public function __construct( string $version ) {
		$this->version = $version;
	}
	
	/**
	 * Enqueue admin styles
	 */
	public function enqueue_styles(): void {
		// Only load on plugin pages
		if ( ! $this->is_plugin_page() ) {
			return;
		}
		
		wp_enqueue_style(
			'churchtools-suite-admin',
			CHURCHTOOLS_SUITE_URL . 'admin/css/churchtools-suite-admin.css',
			[],
			$this->version
		);
	}
	
	/**
	 * Enqueue admin scripts
	 */
	public function enqueue_scripts(): void {
		// Only load on plugin pages
		if ( ! $this->is_plugin_page() ) {
			return;
		}
		
		wp_enqueue_script(
			'churchtools-suite-admin',
			CHURCHTOOLS_SUITE_URL . 'admin/js/churchtools-suite-admin.js',
			[ 'jquery' ],
			$this->version,
			true
		);
		
		wp_localize_script(
			'churchtools-suite-admin',
			'churchtoolsSuite',
			[
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'churchtools_suite_admin' ),
				'version' => $this->version,
			]
		);
	}
	
	/**
	 * Add plugin admin menu
	 */
	public function add_plugin_admin_menu(): void {
		add_menu_page(
			__( 'ChurchTools Suite', 'churchtools-suite' ),
			__( 'ChurchTools', 'churchtools-suite' ),
			'manage_options',
			'churchtools-suite',
			[ $this, 'display_admin_page' ],
			'dashicons-calendar-alt',
			30
		);
	}
	
	/**
	 * Display main admin page
	 */
	public function display_admin_page(): void {
		// Get active tab
		$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'dashboard';
		
		// Include view
		include_once CHURCHTOOLS_SUITE_PATH . 'admin/views/admin-page.php';
	}
	
	/**
	 * Check if current page is a plugin page
	 */
	private function is_plugin_page(): bool {
		$screen = get_current_screen();
		return $screen && strpos( $screen->id, 'churchtools-suite' ) !== false;
	}
	
	/**
	 * Register AJAX handlers
	 */
	public function register_ajax_handlers(): void {
		add_action( 'wp_ajax_cts_test_connection', [ $this, 'ajax_test_connection' ] );
	}
	
	/**
	 * AJAX Handler: Test ChurchTools Connection
	 */
	public function ajax_test_connection(): void {
		// Check nonce
		check_ajax_referer( 'churchtools_suite_admin', 'nonce' );
		
		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [
				'message' => 'Keine Berechtigung.'
			] );
		}
		
		// Load CT Client
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-ct-client.php';
		
		$client = new ChurchTools_Suite_CT_Client();
		$result = $client->test_connection();
		
		if ( $result['success'] ) {
			wp_send_json_success( [
				'message' => $result['message'],
				'user_info' => $result['user_info'] ?? null
			] );
		} else {
			wp_send_json_error( [
				'message' => $result['message']
			] );
		}
	}
}
