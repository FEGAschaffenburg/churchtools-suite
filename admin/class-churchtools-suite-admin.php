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
		add_action( 'wp_ajax_cts_sync_calendars', [ $this, 'ajax_sync_calendars' ] );
		add_action( 'wp_ajax_cts_save_calendar_selection', [ $this, 'ajax_save_calendar_selection' ] );
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
	
	/**
	 * AJAX Handler: Sync Calendars from ChurchTools
	 */
	public function ajax_sync_calendars(): void {
		// Check nonce
		check_ajax_referer( 'churchtools_suite_admin', 'nonce' );
		
		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [
				'message' => __( 'Keine Berechtigung.', 'churchtools-suite' )
			] );
			return;
		}
		
		try {
			// Load dependencies
			require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-ct-client.php';
			require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-repository-base.php';
			require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-calendars-repository.php';
			require_once CHURCHTOOLS_SUITE_PATH . 'includes/services/class-churchtools-suite-calendar-sync-service.php';
			
			$client = new ChurchTools_Suite_CT_Client();
			$calendars_repo = new ChurchTools_Suite_Calendars_Repository();
			$sync_service = new ChurchTools_Suite_Calendar_Sync_Service( $client, $calendars_repo );
			
			$result = $sync_service->sync_calendars();
			
			if ( is_wp_error( $result ) ) {
				wp_send_json_error( [
					'message' => $result->get_error_message()
				] );
				return;
			}
			
			wp_send_json_success( [
				'message' => sprintf(
					__( 'Synchronisation erfolgreich! %d Kalender gefunden, %d neu, %d aktualisiert, %d Fehler.', 'churchtools-suite' ),
					$result['total'],
					$result['inserted'],
					$result['updated'],
					$result['errors']
				),
				'stats' => $result
			] );
		} catch ( Exception $e ) {
			wp_send_json_error( [
				'message' => __( 'Fehler: ', 'churchtools-suite' ) . $e->getMessage()
			] );
		}
	}
	
	/**
	 * AJAX Handler: Save Calendar Selection
	 */
	public function ajax_save_calendar_selection(): void {
		// Check nonce
		check_ajax_referer( 'churchtools_suite_admin', 'nonce' );
		
		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [
				'message' => __( 'Keine Berechtigung.', 'churchtools-suite' )
			] );
			return;
		}
		
		try {
			// Get selected calendar IDs
			$selected_ids = isset( $_POST['selected_ids'] ) ? array_map( 'intval', $_POST['selected_ids'] ) : [];
			
			// Load repository
			require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-repository-base.php';
			require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-calendars-repository.php';
			
			$calendars_repo = new ChurchTools_Suite_Calendars_Repository();
			$result = $calendars_repo->update_selected( $selected_ids );
			
			if ( ! $result ) {
				wp_send_json_error( [
					'message' => __( 'Fehler beim Speichern der Auswahl.', 'churchtools-suite' )
				] );
				return;
			}
			
			$selected_count = count( $selected_ids );
			$total_count = $calendars_repo->count();
			
			wp_send_json_success( [
				'message' => sprintf(
					__( 'Auswahl gespeichert: %d von %d Kalendern ausgewählt.', 'churchtools-suite' ),
					$selected_count,
					$total_count
				),
				'selected_count' => $selected_count,
				'total_count' => $total_count
			] );
		} catch ( Exception $e ) {
			wp_send_json_error( [
				'message' => __( 'Fehler: ', 'churchtools-suite' ) . $e->getMessage()
			] );
		}
	}
}
