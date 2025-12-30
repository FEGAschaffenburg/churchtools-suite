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
	 * AJAX Handler: Run update now (performs installation) — requires additional confirmation
	 */
	public function ajax_run_update() {
		// Check nonce
		check_ajax_referer( 'churchtools_suite_admin', 'nonce' );

		// Permission
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Keine Berechtigung.', 'churchtools-suite' ) ] );
			return;
		}

		try {
			if ( ! class_exists( 'ChurchTools_Suite_Auto_Updater' ) ) {
				require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-auto-updater.php';
			}

			$result = ChurchTools_Suite_Auto_Updater::run_update_now();

			if ( is_wp_error( $result ) ) {
				wp_send_json_error( [ 'message' => $result->get_error_message() ] );
				return;
			}

			wp_send_json_success( [ 'message' => $result['message'] ?? __( 'Update gestartet.', 'churchtools-suite' ) ] );
		} catch ( Exception $e ) {
			wp_send_json_error( [ 'message' => __( 'Fehler: ', 'churchtools-suite' ) . $e->getMessage() ] );
		}
	}

	/**
	 * AJAX Handler: Manual Update Trigger
	 * Triggers the auto-updater check immediately.
	 */
	public function ajax_manual_update() {
		// Check nonce
		check_ajax_referer( 'churchtools_suite_admin', 'nonce' );

		// Permission
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Keine Berechtigung.', 'churchtools-suite' ) ] );
			return;
		}

		   try {
			   if ( ! class_exists( 'ChurchTools_Suite_Auto_Updater' ) ) {
				   require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-auto-updater.php';
			   }

			   // Logging: Start manuelle Update-Prüfung
			   if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
				   ChurchTools_Suite_Logger::info('updater', 'Manuelle Update-Prüfung gestartet', [
					   'current_version' => defined('CHURCHTOOLS_SUITE_VERSION') ? CHURCHTOOLS_SUITE_VERSION : null,
					   'user' => get_current_user_id(),
					   'ip' => $_SERVER['REMOTE_ADDR'] ?? null
				   ]);
			   }

			   // Only check availability — do NOT perform the update from the admin button
			   $info = ChurchTools_Suite_Auto_Updater::get_latest_release_info();
			   if ( is_wp_error( $info ) ) {
				   if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
					   ChurchTools_Suite_Logger::error('updater', 'Fehler bei manueller Update-Prüfung', [ 'error' => $info->get_error_message() ]);
				   }
				   wp_send_json_error( [ 'message' => __( 'Fehler beim Abrufen der Release-Informationen.', 'churchtools-suite' ), 'error' => $info->get_error_message() ] );
				   return;
			   }

			   if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
				   ChurchTools_Suite_Logger::info('updater', 'Manuelle Update-Prüfung abgeschlossen', [
					   'found_update' => !empty($info['is_update']),
					   'latest_version' => $info['latest_version'] ?? null,
					   'tag_name' => $info['tag_name'] ?? null,
					   'zip_url' => $info['zip_url'] ?? null
				   ]);
			   }

			   wp_send_json_success( [ 'message' => __( 'Update-Prüfung abgeschlossen.', 'churchtools-suite' ), 'data' => $info ] );
		   } catch ( Exception $e ) {
			   if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
				   ChurchTools_Suite_Logger::error('updater', 'Exception bei manueller Update-Prüfung', [ 'exception' => $e->getMessage() ]);
			   }
			   wp_send_json_error( [ 'message' => __( 'Fehler: ', 'churchtools-suite' ) . $e->getMessage() ] );
		   }
	}
	
	/**
	 * Enqueue admin styles
	 * 
	 * @since 0.6.1.0 Always load (no conditional check)
	 * @since 0.6.1.5 Load public CSS first, then admin CSS (correct order)
	 */
	public function enqueue_styles() {
		// Load public CSS first (for demos in admin area)
		wp_enqueue_style(
			'churchtools-suite-public',
			CHURCHTOOLS_SUITE_URL . 'assets/css/churchtools-suite-public.css',
			[],
			$this->version
		);
		
		// Load admin CSS after (depends on public CSS)
		wp_enqueue_style(
			'churchtools-suite-admin',
			CHURCHTOOLS_SUITE_URL . 'assets/css/churchtools-suite-admin.css',
			[ 'churchtools-suite-public' ],
			$this->version
		);
	}
	
	/**
	 * Enqueue admin scripts
	 * 
	 * @since 0.6.1.0 Always load (no conditional check)
	 */
	public function enqueue_scripts() {
		// Main admin script (jQuery-based)
		wp_enqueue_script(
			'churchtools-suite-admin',
			CHURCHTOOLS_SUITE_URL . 'assets/js/churchtools-suite-admin.js',
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
	public function add_plugin_admin_menu() {
		add_menu_page(
			__( 'ChurchTools Suite', 'churchtools-suite' ),
			__( 'ChurchTools', 'churchtools-suite' ),
			'manage_options',
			'churchtools-suite',
			[ $this, 'display_admin_page' ],
			'dashicons-calendar-alt',
			30
		);
		
		// Add Shortcode Manager as submenu
		add_submenu_page(
			'churchtools-suite',
			__( 'Shortcode Manager', 'churchtools-suite' ),
			__( '⚡ Shortcode Manager', 'churchtools-suite' ),
			'manage_options',
			'churchtools-suite-shortcodes',
			[ $this, 'display_shortcode_manager' ]
		);
		
		// Shortcode Demo removed as separate submenu — demo is now integrated into Shortcode Manager

		// Add Data subpage (separate admin page for large lists)
		add_submenu_page(
			'churchtools-suite',
			__( 'Daten', 'churchtools-suite' ),
			__( '📋 Daten', 'churchtools-suite' ),
			'manage_options',
			'churchtools-suite-data',
			[ $this, 'display_data_page' ]
		);

		// Add Documentation subpage
		add_submenu_page(
			'churchtools-suite',
			__( 'Dokumentation', 'churchtools-suite' ),
			__( '📚 Dokumentation', 'churchtools-suite' ),
			'manage_options',
			'churchtools-suite-docs',
			[ $this, 'display_documentation_page' ]
		);

		// Note: Settings, Sync and Debug are handled as tabs in the main admin page
		// (admin/views/admin-page.php) — no separate submenu entries are added here.
	}
	
	/**
	 * Display main admin page
	 */
	public function display_admin_page() {
		// Get active tab
		$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'dashboard';
		
		// Include view
		include_once CHURCHTOOLS_SUITE_PATH . 'admin/views/admin-page.php';
	}
	
	/**
	 * Display shortcode manager page
	 */
	public function display_shortcode_manager() {
		include_once CHURCHTOOLS_SUITE_PATH . 'admin/views/shortcode-manager.php';
	}
	
	/**
	 * Display shortcode demo page
	 */
	public function display_shortcode_demo() {
		include_once CHURCHTOOLS_SUITE_PATH . 'admin/views/shortcode-demo.php';
	}

	/**
	 * Display Data page (dedicated subpage)
	 */
	public function display_data_page() {
		// Reuse existing data subtab view
		include_once CHURCHTOOLS_SUITE_PATH . 'admin/views/tab-data.php';
	}


	/**
	 * Display Documentation page (dedicated subpage)
	 */
	public function display_documentation_page() {
		include_once CHURCHTOOLS_SUITE_PATH . 'admin/views/tab-documentation.php';
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
	public function register_ajax_handlers() {
		add_action( 'wp_ajax_cts_test_connection', [ $this, 'ajax_test_connection' ] );
		add_action( 'wp_ajax_cts_sync_calendars', [ $this, 'ajax_sync_calendars' ] );
		add_action( 'wp_ajax_cts_save_calendar_selection', [ $this, 'ajax_save_calendar_selection' ] );
		add_action( 'wp_ajax_cts_sync_service_groups', [ $this, 'ajax_sync_service_groups' ] );
		add_action( 'wp_ajax_cts_save_service_group_selection', [ $this, 'ajax_save_service_group_selection' ] );
		add_action( 'wp_ajax_cts_sync_services', [ $this, 'ajax_sync_services' ] );
		add_action( 'wp_ajax_cts_save_service_selection', [ $this, 'ajax_save_service_selection' ] );
		add_action( 'wp_ajax_cts_sync_events', [ $this, 'ajax_sync_events' ] );
		add_action( 'wp_ajax_cts_trigger_manual_sync', [ $this, 'ajax_trigger_manual_sync' ] );
		add_action( 'wp_ajax_cts_manual_update', [ $this, 'ajax_manual_update' ] );
		add_action( 'wp_ajax_cts_run_update', [ $this, 'ajax_run_update' ] );
		add_action( 'wp_ajax_cts_trigger_keepalive', [ $this, 'ajax_trigger_keepalive' ] );
		add_action( 'wp_ajax_cts_reload_logs', [ $this, 'ajax_reload_logs' ] );
		add_action( 'wp_ajax_cts_clear_logs', [ $this, 'ajax_clear_logs' ] );
		add_action( 'wp_ajax_cts_clear_block_logs', [ $this, 'ajax_clear_block_logs' ] );
		add_action( 'wp_ajax_cts_save_preset', [ $this, 'ajax_save_preset' ] );
		add_action( 'wp_ajax_cts_update_preset', [ $this, 'ajax_update_preset' ] );
		add_action( 'wp_ajax_cts_delete_preset', [ $this, 'ajax_delete_preset' ] );
		add_action( 'wp_ajax_cts_get_calendars', [ $this, 'ajax_get_calendars' ] );
		// AJAX data lists (server-side filtering/pagination)
		add_action( 'wp_ajax_cts_fetch_events_list', [ $this, 'ajax_fetch_events_list' ] );
		add_action( 'wp_ajax_cts_fetch_imported_services_list', [ $this, 'ajax_fetch_imported_services_list' ] );
		
		// Reset & Cleanup (v0.7.2.4)
		add_action( 'wp_ajax_cts_clear_events', [ $this, 'ajax_clear_events' ] );
		add_action( 'wp_ajax_cts_clear_calendars', [ $this, 'ajax_clear_calendars' ] );
		add_action( 'wp_ajax_cts_clear_services', [ $this, 'ajax_clear_services' ] );
		add_action( 'wp_ajax_cts_clear_sync_history', [ $this, 'ajax_clear_sync_history' ] );
		add_action( 'wp_ajax_cts_full_reset', [ $this, 'ajax_full_reset' ] );
		
		// Public AJAX (for frontend modal)
		add_action( 'wp_ajax_cts_get_modal_template', [ $this, 'ajax_get_modal_template' ] );
		add_action( 'wp_ajax_nopriv_cts_get_modal_template', [ $this, 'ajax_get_modal_template' ] );
		add_action( 'wp_ajax_cts_get_event_details', [ $this, 'ajax_get_event_details' ] );
		add_action( 'wp_ajax_nopriv_cts_get_event_details', [ $this, 'ajax_get_event_details' ] );
	}
	
	/**
	 * AJAX Handler: Test ChurchTools Connection
	 */
	public function ajax_test_connection() {
		// Check nonce
		check_ajax_referer( 'churchtools_suite_admin', 'nonce' );
		
		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [
				'message' => 'Keine Berechtigung.'
			] );
		}
		
		// Rate Limiting (v0.7.0.2)
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-rate-limiter.php';
		
		$user_id = get_current_user_id();
		$identifier = 'user_' . $user_id;
		
		if ( ! ChurchTools_Suite_Rate_Limiter::is_allowed( $identifier, 'ajax' ) ) {
			wp_send_json_error( [
				'message' => __( 'Zu viele Anfragen. Bitte warten Sie einen Moment.', 'churchtools-suite' )
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
	public function ajax_sync_calendars() {
		// Check nonce
		check_ajax_referer( 'churchtools_suite_admin', 'nonce' );
		
		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [
				'message' => __( 'Keine Berechtigung.', 'churchtools-suite' )
			] );
			return;
		}
		
		// Rate Limiting (v0.7.0.2)
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-rate-limiter.php';
		
		$user_id = get_current_user_id();
		$identifier = 'user_' . $user_id;
		
		if ( ! ChurchTools_Suite_Rate_Limiter::is_allowed( $identifier, 'ajax' ) ) {
			wp_send_json_error( [
				'message' => __( 'Zu viele Anfragen. Bitte warten Sie einen Moment.', 'churchtools-suite' )
			] );
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
	public function ajax_save_calendar_selection() {
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
	
	/**
	 * AJAX Handler: Sync Service Groups from ChurchTools
	 */
	public function ajax_sync_service_groups() {
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
			require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-services-repository.php';
			require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-service-groups-repository.php';
			require_once CHURCHTOOLS_SUITE_PATH . 'includes/services/class-churchtools-suite-service-sync-service.php';
			
			// Initialize services
			$ct_client = new ChurchTools_Suite_CT_Client();
			$services_repo = new ChurchTools_Suite_Services_Repository();
			$service_groups_repo = new ChurchTools_Suite_Service_Groups_Repository();
			$sync_service = new ChurchTools_Suite_Service_Sync_Service( $ct_client, $services_repo, $service_groups_repo );
			
			// Run sync
			$result = $sync_service->sync_service_groups();
			
			if ( is_wp_error( $result ) ) {
				$error_data = $result->get_error_data();
				$error_message = $result->get_error_message();
				
				// Add URL to error message if available
				if ( isset( $error_data['url'] ) ) {
					$error_message .= ' (URL: ' . $error_data['url'] . ')';
				}
				
				wp_send_json_error( [
					'message' => $error_message,
					'error_data' => $error_data
				] );
				return;
			}
			
			$message = sprintf(
				__( 'Synchronisation erfolgreich! %d Service-Gruppen gefunden. %d neu, %d aktualisiert.', 'churchtools-suite' ),
				$result['groups_found'],
				$result['groups_inserted'],
				$result['groups_updated']
			);
			
			wp_send_json_success( [
				'message' => $message,
				'stats' => $result
			] );
		} catch ( Exception $e ) {
			wp_send_json_error( [
				'message' => __( 'Fehler: ', 'churchtools-suite' ) . $e->getMessage()
			] );
		}
	}
	
	/**
	 * AJAX Handler: Save Service Group Selection
	 */
	public function ajax_save_service_group_selection() {
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
			// Get selected service group IDs
			$selected_ids = isset( $_POST['selected_ids'] ) ? array_map( 'sanitize_text_field', $_POST['selected_ids'] ) : [];
			
			// Load repository
			require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-repository-base.php';
			require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-service-groups-repository.php';
			
			$service_groups_repo = new ChurchTools_Suite_Service_Groups_Repository();
			$result = $service_groups_repo->update_selection( $selected_ids );
			
			if ( ! $result ) {
				wp_send_json_error( [
					'message' => __( 'Fehler beim Speichern.', 'churchtools-suite' )
				] );
				return;
			}
			
			wp_send_json_success( [
				'message' => sprintf(
					__( 'Auswahl gespeichert! %d Service-Gruppen ausgewählt.', 'churchtools-suite' ),
					count( $selected_ids )
				)
			] );
		} catch ( Exception $e ) {
			wp_send_json_error( [
				'message' => __( 'Fehler: ', 'churchtools-suite' ) . $e->getMessage()
			] );
		}
	}
	
	/**
	 * AJAX Handler: Sync Services from ChurchTools
	 */
	public function ajax_sync_services() {
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
			require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-services-repository.php';
			require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-service-groups-repository.php';
			require_once CHURCHTOOLS_SUITE_PATH . 'includes/services/class-churchtools-suite-service-sync-service.php';
			
			// Initialize services
			$ct_client = new ChurchTools_Suite_CT_Client();
			$services_repo = new ChurchTools_Suite_Services_Repository();
			$service_groups_repo = new ChurchTools_Suite_Service_Groups_Repository();
			$sync_service = new ChurchTools_Suite_Service_Sync_Service( $ct_client, $services_repo, $service_groups_repo );
			
			// Run sync
			$result = $sync_service->sync_services();
			
			if ( is_wp_error( $result ) ) {
				wp_send_json_error( [
					'message' => $result->get_error_message()
				] );
				return;
			}
			
			$message = sprintf(
				__( 'Synchronisation erfolgreich! %d Services gefunden. %d neu, %d aktualisiert.', 'churchtools-suite' ),
				$result['services_found'],
				$result['services_inserted'],
				$result['services_updated']
			);
			
			wp_send_json_success( [
				'message' => $message,
				'stats' => $result
			] );
		} catch ( Exception $e ) {
			wp_send_json_error( [
				'message' => __( 'Fehler: ', 'churchtools-suite' ) . $e->getMessage()
			] );
		}
	}
	
	/**
	 * AJAX Handler: Save Service Selection
	 */
	public function ajax_save_service_selection() {
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
			// Get selected service IDs
			$selected_ids = isset( $_POST['selected_ids'] ) ? array_map( 'sanitize_text_field', $_POST['selected_ids'] ) : [];
			
			// Load repository
			require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-repository-base.php';
			require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-services-repository.php';
			
			$services_repo = new ChurchTools_Suite_Services_Repository();
			$result = $services_repo->update_selection( $selected_ids );
			
			if ( ! $result ) {
				wp_send_json_error( [
					'message' => __( 'Fehler beim Speichern der Auswahl.', 'churchtools-suite' )
				] );
				return;
			}
			
			$selected_count = count( $selected_ids );
			$total_count = $services_repo->get_total_count();
			
			wp_send_json_success( [
				'message' => sprintf(
					__( 'Auswahl gespeichert: %d von %d Services ausgewählt.', 'churchtools-suite' ),
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
	
	/**
	 * AJAX Handler: Sync Events from ChurchTools
	 */
	public function ajax_sync_events() {
		// v0.7.2.6: Register shutdown handler to catch fatal errors
		register_shutdown_function( function() {
			$error = error_get_last();
			if ( $error && in_array( $error['type'], [ E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR ] ) ) {
				// Log to plugin log file (v0.7.2.6)
				require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-logger.php';
				ChurchTools_Suite_Logger::critical(
					'fatal_error',
					sprintf(
						'PHP Fatal Error während Event-Sync: %s',
						$error['message']
					),
					[
						'file' => $error['file'],
						'line' => $error['line'],
						'type' => $error['type']
					]
				);
				
				if ( ! headers_sent() ) {
					header( 'Content-Type: application/json; charset=utf-8' );
					http_response_code( 200 );
					echo json_encode( [
						'success' => false,
						'data' => [
							'message' => sprintf(
								'PHP Fatal Error: %s in %s (Zeile %d). Details im Debug-Tab unter "Logs".',
								$error['message'],
								basename( $error['file'] ),
								$error['line']
							)
						]
					] );
				}
				exit;
			}
		} );
		
		// Check nonce
		check_ajax_referer( 'churchtools_suite_admin', 'nonce' );
		
		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [
				'message' => __( 'Keine Berechtigung.', 'churchtools-suite' )
			] );
			return;
		}
		
		// Cleanup stuck syncs (older than 5 minutes)
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-sync-history-repository.php';
		$history_repo = new ChurchTools_Suite_Sync_History_Repository();
		$history_repo->cleanup_stuck_syncs( 5 );
		
		// Create sync history entry
		$sync_id = $history_repo->create_sync_entry( 'manual', current_time( 'mysql' ) );
		
		try {
			// Load dependencies
			require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-ct-client.php';
			require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-repository-base.php';
			require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-calendars-repository.php';
			require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-events-repository.php';
			require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-event-services-repository.php';
			require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-services-repository.php';
			require_once CHURCHTOOLS_SUITE_PATH . 'includes/services/class-churchtools-suite-event-sync-service.php';
			
			$client = new ChurchTools_Suite_CT_Client();
			$calendars_repo = new ChurchTools_Suite_Calendars_Repository();
			$events_repo = new ChurchTools_Suite_Events_Repository();
			$event_services_repo = new ChurchTools_Suite_Event_Services_Repository();
			$services_repo = new ChurchTools_Suite_Services_Repository();
			$sync_service = new ChurchTools_Suite_Event_Sync_Service( $client, $events_repo, $calendars_repo, $event_services_repo, $services_repo );
			
			// Optional: Custom date range from POST
			$args = [];
			if ( isset( $_POST['from'] ) ) {
				$args['from'] = sanitize_text_field( $_POST['from'] );
			}
			if ( isset( $_POST['to'] ) ) {
				$args['to'] = sanitize_text_field( $_POST['to'] );
			}
			
			// v0.7.1.0: Force full sync option
			if ( isset( $_POST['force_full'] ) && $_POST['force_full'] === '1' ) {
				$args['force_full'] = true;
			}
			
			$result = $sync_service->sync_events( $args );
			
			if ( is_wp_error( $result ) ) {
				// Mark sync as failed
				if ( $sync_id ) {
					$history_repo->complete_sync( $sync_id, [], $result->get_error_message() );
				}
				
				wp_send_json_error( [
					'message' => $result->get_error_message()
				] );
				return;
			}
			
			// Mark sync as successful
			if ( $sync_id ) {
				$history_repo->complete_sync( $sync_id, $result, null );
			}
			
			wp_send_json_success( [
				'message' => sprintf(
					__( 'Synchronisation erfolgreich! %d Kalender verarbeitet, %d Events gefunden, %d Appointments gefunden, %d neu, %d aktualisiert, %d übersprungen, %d Fehler.', 'churchtools-suite' ),
					$result['calendars_processed'],
					$result['events_found'],
					$result['appointments_found'],
					$result['events_inserted'],
					$result['events_updated'],
					$result['events_skipped'],
					$result['errors']
				),
				'stats' => $result,
				'sync_type' => $result['sync_type'] ?? 'full', // v0.7.1.0: Pass sync type to frontend
			] );
		} catch ( Exception $e ) {
			// Mark sync as failed
			if ( $sync_id ) {
				$history_repo->complete_sync( $sync_id, [], $e->getMessage() );
			}
			
			wp_send_json_error( [
				'message' => __( 'Fehler: ', 'churchtools-suite' ) . $e->getMessage()
			] );
		}
	}

	/**
	 * AJAX: Fetch events list (server-side pagination & filtering)
	 */
	public function ajax_fetch_events_list() {
		check_ajax_referer( 'churchtools_suite_admin', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Keine Berechtigung.', 'churchtools-suite' ) ] );
			return;
		}

		global $wpdb;
		$from = isset( $_POST['from'] ) ? sanitize_text_field( wp_unslash( $_POST['from'] ) ) : '';
		$to = isset( $_POST['to'] ) ? sanitize_text_field( wp_unslash( $_POST['to'] ) ) : '';
		$calendar_filter = isset( $_POST['calendar_id'] ) ? sanitize_text_field( wp_unslash( $_POST['calendar_id'] ) ) : '';
		$page = max( 1, (int) ( $_POST['paged'] ?? 1 ) );
		$limit = 50;
		$offset = ( $page - 1 ) * $limit;

		$prefix = $wpdb->prefix . CHURCHTOOLS_SUITE_DB_PREFIX;
		$table = $prefix . 'events';

		$sql = "SELECT id, event_id, appointment_id, calendar_id, title, description, event_description, appointment_description, start_datetime, end_datetime, is_all_day, location_name, address_name, address_street, address_zip, address_city, address_latitude, address_longitude, tags FROM {$table} WHERE 1=1";
		$count_sql = "SELECT COUNT(*) FROM {$table} WHERE 1=1";
		$where = [];
		$params = [];

		if ( ! empty( $from ) ) {
			$where[] = 'start_datetime >= %s';
			$params[] = $from . ' 00:00:00';
		}
		if ( ! empty( $to ) ) {
			$where[] = 'start_datetime <= %s';
			$params[] = $to . ' 23:59:59';
		}
		if ( ! empty( $calendar_filter ) ) {
			$where[] = 'calendar_id = %s';
			$params[] = $calendar_filter;
		}

		if ( ! empty( $where ) ) {
			$cond = ' AND ' . implode( ' AND ', $where );
			$sql .= $cond;
			$count_sql .= $cond;
		}

		$sql .= ' ORDER BY start_datetime ASC LIMIT %d OFFSET %d';
		$params_with_limit = array_merge( $params, [ $limit, $offset ] );

		$prepared_sql = empty( $params_with_limit ) ? $sql : $wpdb->prepare( $sql, ...$params_with_limit );
		$prepared_count = empty( $params ) ? $count_sql : $wpdb->prepare( $count_sql, ...$params );

		$events = $wpdb->get_results( $prepared_sql );
		$total = (int) $wpdb->get_var( $prepared_count );
		$total_pages = max(1, ceil( $total / $limit ));

		// Build HTML fragment (table rows + pagination)
		ob_start();
		if ( empty( $events ) ) {
			?>
			<div class="cts-empty-state"><span class="cts-empty-icon">📅</span><h3><?php esc_html_e( 'Keine Termine gefunden', 'churchtools-suite' ); ?></h3></div>
			<?php
		} else {
			?>
			<div class="cts-table-wrapper">
				<table class="cts-events-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Datum & Zeit', 'churchtools-suite' ); ?></th>
							<th><?php esc_html_e( 'Titel', 'churchtools-suite' ); ?></th>
							<th><?php esc_html_e( 'Kalender', 'churchtools-suite' ); ?></th>
							<th><?php esc_html_e( 'Ort / Adresse', 'churchtools-suite' ); ?></th>
							<th><?php esc_html_e( 'Tags', 'churchtools-suite' ); ?></th>
							<th><?php esc_html_e( 'Typ', 'churchtools-suite' ); ?></th>
							<th><?php esc_html_e( 'Services', 'churchtools-suite' ); ?></th>
							<th><?php esc_html_e( 'Details', 'churchtools-suite' ); ?></th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ( $events as $event ) :
						$start_local = get_date_from_gmt( $event->start_datetime );
						$end_local = $event->end_datetime ? get_date_from_gmt( $event->end_datetime ) : null;
						$is_all_day = (bool) $event->is_all_day;
						$type_label = ! empty( $event->appointment_id ) ? __( 'Termin', 'churchtools-suite' ) : __( 'Event', 'churchtools-suite' );
						$type_icon = ! empty( $event->appointment_id ) ? '📅' : '🎯';
						?>
						<tr>
							<td class="cts-event-date">
								<div class="cts-event-date-primary"><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $start_local ) ) ); ?></div>
								<div class="cts-event-date-time"><?php if ( ! $is_all_day ) { echo esc_html( date_i18n( get_option( 'time_format' ), strtotime( $start_local ) ) ); if ( $end_local ) { echo ' - ' . esc_html( date_i18n( get_option( 'time_format' ), strtotime( $end_local ) ) ); } } else { esc_html_e( 'Ganztägig', 'churchtools-suite' ); } ?></div>
							</td>
							<td class="cts-event-title"><div class="cts-event-title-main"><?php echo esc_html( $event->title ); ?></div></td>
							<td class="cts-event-calendar"><span class="cts-calendar-badge"><?php echo esc_html( $event->calendar_id ); ?></span></td>
							<td class="cts-event-location">
								<?php if ( ! empty( $event->address_name ) || ! empty( $event->address_street ) ) : ?>
									<div class="cts-address-structured">
										<?php if ( ! empty( $event->address_name ) ) : ?><div class="cts-address-name"><strong>🏠 <?php echo esc_html( $event->address_name ); ?></strong></div><?php endif; ?>
										<?php if ( ! empty( $event->address_street ) ) : ?><div class="cts-address-street"><?php echo esc_html( $event->address_street ); ?></div><?php endif; ?>
									</div>
								<?php elseif ( ! empty( $event->location_name ) ) : ?>
									<span>📍 <?php echo esc_html( $event->location_name ); ?></span>
								<?php else : ?><span class="cts-muted">—</span><?php endif; ?>
							</td>
							<td class="cts-event-tags">
								<?php if ( ! empty( $event->tags ) ) { $tags = json_decode( $event->tags, true ); if ( is_array( $tags ) && ! empty( $tags ) ) { foreach ( $tags as $tag ) { ?><span class="cts-tag">🏷️ <?php echo esc_html( $tag['name'] ?? '' ); ?></span><?php } } else { echo '<span class="cts-muted">—</span>'; } } else { echo '<span class="cts-muted">—</span>'; } ?>
							</td>
							<td class="cts-event-type"><span class="cts-type-badge"><?php echo esc_html( $type_icon . ' ' . $type_label ); ?></span></td>
							<td class="cts-event-services"><span class="cts-muted">—</span></td>
							<td class="cts-event-details"><span class="cts-muted">—</span></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<?php
		}

		// Pagination HTML
		if ( $total_pages > 1 ) {
			$pagination = '<div class="cts-pagination">';
			if ( $page > 1 ) {
				$pagination .= '<button data-paged="' . ( $page - 1 ) . '" class="cts-ajax-page cts-btn cts-btn-secondary">← ' . __( 'Zurück', 'churchtools-suite' ) . '</button>';
			}
			$pagination .= '<span class="cts-pagination-info">' . sprintf( __( 'Seite %d von %d', 'churchtools-suite' ), $page, $total_pages ) . '</span>';
			if ( $page < $total_pages ) {
				$pagination .= '<button data-paged="' . ( $page + 1 ) . '" class="cts-ajax-page cts-btn cts-btn-secondary">' . __( 'Weiter', 'churchtools-suite' ) . ' →</button>';
			}
			$pagination .= '</div>';
			echo $pagination;
		}

		$html = ob_get_clean();
		wp_send_json_success( [ 'html' => $html, 'total' => $total, 'page' => $page, 'total_pages' => $total_pages ] );
	}

	/**
	 * AJAX: Fetch imported services list (server-side pagination)
	 */
	public function ajax_fetch_imported_services_list() {
		check_ajax_referer( 'churchtools_suite_admin', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Keine Berechtigung.', 'churchtools-suite' ) ] );
			return;
		}

		require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-repository-base.php';
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-event-services-repository.php';
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-events-repository.php';

		$event_services_repo = new ChurchTools_Suite_Event_Services_Repository();
		$events_repo = new ChurchTools_Suite_Events_Repository();

		$page = max( 1, (int) ( $_POST['paged'] ?? 1 ) );
		$limit = 50;
		$offset = ( $page - 1 ) * $limit;

		$all_services = $event_services_repo->get_all();
		$total = count( $all_services );
		$services = array_slice( $all_services, $offset, $limit );
		$total_pages = max(1, ceil( $total / $limit ));

		ob_start();
		if ( empty( $services ) ) {
			?>
			<div class="cts-empty-state"><span class="cts-empty-icon">👥</span><h3><?php esc_html_e( 'Keine Services gefunden', 'churchtools-suite' ); ?></h3></div>
			<?php
		} else {
			?>
			<div class="cts-card">
				<div class="cts-table-wrapper">
					<table class="cts-events-table">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Service', 'churchtools-suite' ); ?></th>
								<th><?php esc_html_e( 'Person', 'churchtools-suite' ); ?></th>
								<th><?php esc_html_e( 'Event', 'churchtools-suite' ); ?></th>
								<th><?php esc_html_e( 'Service ID', 'churchtools-suite' ); ?></th>
								<th><?php esc_html_e( 'Importiert', 'churchtools-suite' ); ?></th>
							</tr>
						</thead>
						<tbody>
						<?php foreach ( $services as $service ) : $event = $events_repo->get_by_id( $service->event_id ); ?>
							<tr>
								<td><strong><?php echo esc_html( $service->service_name ); ?></strong></td>
								<td><?php echo ! empty( $service->person_name ) ? esc_html( $service->person_name ) : '<span class="cts-muted">—</span>'; ?></td>
								<td><?php if ( $event ) { echo '<div class="cts-event-title-main">' . esc_html( $event->title ) . '</div><div class="cts-event-date-time">' . esc_html( date_i18n( get_option( 'date_format' ), strtotime( $event->start_datetime ) ) ) . '</div>'; } else { echo '<span class="cts-muted">Event gelöscht</span>'; } ?></td>
								<td><?php echo ! empty( $service->service_id ) ? '<code>' . esc_html( $service->service_id ) . '</code>' : '<span class="cts-muted">—</span>'; ?></td>
								<td><?php echo ! empty( $service->created_at ) ? esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $service->created_at ) ) ) : '<span class="cts-muted">—</span>'; ?></td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
			<?php
		}

		if ( $total_pages > 1 ) {
			$pagination = '<div class="cts-pagination">';
			if ( $page > 1 ) { $pagination .= '<button data-paged="' . ( $page - 1 ) . '" class="cts-ajax-page cts-btn cts-btn-secondary">← ' . __( 'Zurück', 'churchtools-suite' ) . '</button>'; }
			$pagination .= '<span class="cts-pagination-info">' . sprintf( __( 'Seite %d von %d', 'churchtools-suite' ), $page, $total_pages ) . '</span>';
			if ( $page < $total_pages ) { $pagination .= '<button data-paged="' . ( $page + 1 ) . '" class="cts-ajax-page cts-btn cts-btn-secondary">' . __( 'Weiter', 'churchtools-suite' ) . ' →</button>'; }
			$pagination .= '</div>';
			echo $pagination;
		}

		$html = ob_get_clean();
		wp_send_json_success( [ 'html' => $html, 'total' => $total, 'page' => $page, 'total_pages' => $total_pages ] );
	}
	
	/**
	 * AJAX Handler: Trigger Manual Sync
	 * Führt sofortigen Cron-Sync aus
	 */
	public function ajax_trigger_manual_sync() {
		// Clean output buffer
		if (ob_get_level() > 0) {
			ob_clean();
		}
		
		check_ajax_referer( 'churchtools_suite_admin', 'nonce' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [
				'message' => __( 'Keine Berechtigung.', 'churchtools-suite' )
			] );
			return;
		}
		
		// Load Logger with error suppression
		@require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-logger.php';
		
		try {
			@ChurchTools_Suite_Logger::log('=== MANUAL SYNC START ===', 'info');
			$start_time = current_time('mysql');
			
			// Sync-Historie Repository laden
			@ChurchTools_Suite_Logger::log('Loading Sync History Repository', 'info');
			require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-sync-history-repository.php';
			
			@ChurchTools_Suite_Logger::log('Instantiating Sync History Repository', 'info');
			$history_repo = new ChurchTools_Suite_Sync_History_Repository();
			
			// Historie-Eintrag erstellen
			@ChurchTools_Suite_Logger::log('Creating sync history entry', 'info');
			$sync_id = $history_repo->create_sync_entry('manual', $start_time);
			@ChurchTools_Suite_Logger::log(sprintf('Sync ID: %d', $sync_id), 'info');
			
			// Event Sync Service laden
			@ChurchTools_Suite_Logger::log('Loading dependencies', 'info');
			
			@ChurchTools_Suite_Logger::log('Loading: class-churchtools-suite-ct-client.php', 'info');
			require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-ct-client.php';
			
			@ChurchTools_Suite_Logger::log('Loading: class-churchtools-suite-repository-base.php', 'info');
			require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-repository-base.php';
			
			@ChurchTools_Suite_Logger::log('Loading: class-churchtools-suite-events-repository.php', 'info');
			require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-events-repository.php';
			
			@ChurchTools_Suite_Logger::log('Loading: class-churchtools-suite-calendars-repository.php', 'info');
			require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-calendars-repository.php';
			
			@ChurchTools_Suite_Logger::log('Loading: class-churchtools-suite-event-services-repository.php', 'info');
			require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-event-services-repository.php';
			
			@ChurchTools_Suite_Logger::log('Loading: class-churchtools-suite-services-repository.php', 'info');
			require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-services-repository.php';
			
			@ChurchTools_Suite_Logger::log('Loading: class-churchtools-suite-event-sync-service.php', 'info');
			require_once CHURCHTOOLS_SUITE_PATH . 'includes/services/class-churchtools-suite-event-sync-service.php';
			
			@ChurchTools_Suite_Logger::log('All dependencies loaded successfully', 'info');
			
			// Service initialisieren
			@ChurchTools_Suite_Logger::log('Initializing services', 'info');
			$ct_client = new ChurchTools_Suite_CT_Client();
			$events_repo = new ChurchTools_Suite_Events_Repository();
			$calendars_repo = new ChurchTools_Suite_Calendars_Repository();
			$event_services_repo = new ChurchTools_Suite_Event_Services_Repository();
			$services_repo = new ChurchTools_Suite_Services_Repository();
			$sync_service = new ChurchTools_Suite_Event_Sync_Service($ct_client, $events_repo, $calendars_repo, $event_services_repo, $services_repo);
			
			// Sync ausführen
			@ChurchTools_Suite_Logger::log('Starting sync_events()', 'info');
			$result = $sync_service->sync_events([
				'force_full' => false, // Manual trigger uses incremental by default (v0.7.1.0)
			]);
			@ChurchTools_Suite_Logger::log(sprintf('sync_events() completed - Result type: %s', gettype($result)), 'info');
			
			if (is_wp_error($result)) {
				@ChurchTools_Suite_Logger::log('Sync returned WP_Error: ' . $result->get_error_message(), 'error');
				
				// Fehler-Eintrag
				if ($sync_id) {
					$history_repo->complete_sync($sync_id, [], $result->get_error_message());
				}
				
				wp_send_json_error( [
					'message' => __( 'Sync fehlgeschlagen: ', 'churchtools-suite' ) . $result->get_error_message()
				] );
				return;
			}
			
			// Erfolg - Stats zusammenstellen
			@ChurchTools_Suite_Logger::log(sprintf('Processing results - Keys: %s', implode(', ', array_keys($result))), 'info');
			
			$stats = [
				'calendars_processed' => $result['calendars_processed'] ?? 0,
				'events_found' => $result['events_found'] ?? 0,
				'events_inserted' => $result['events_inserted'] ?? 0,
				'events_updated' => $result['events_updated'] ?? 0,
				'events_skipped' => $result['events_skipped'] ?? 0,
				'services_imported' => $result['services_imported'] ?? 0,
				'started_at' => $start_time,
				'completed_at' => current_time('mysql')
			];
			
			@ChurchTools_Suite_Logger::log(sprintf('Stats: %d calendars, %d events, %d services', 
				$stats['calendars_processed'], 
				$stats['events_found'], 
				$stats['services_imported']
			), 'info');
			
			// Historie-Eintrag abschließen
			if ($sync_id) {
				@ChurchTools_Suite_Logger::log('Completing sync history entry', 'info');
				$history_repo->complete_sync($sync_id, $stats, null);
			}
			
			@ChurchTools_Suite_Logger::log('=== MANUAL SYNC SUCCESS ===', 'info');
			
			wp_send_json_success( [
				'message' => sprintf(
					__( '✅ Manueller Sync erfolgreich! %d Kalender, %d Events gefunden, %d neu, %d aktualisiert, %d übersprungen, %d Services importiert', 'churchtools-suite' ),
					$stats['calendars_processed'],
					$stats['events_found'],
					$stats['events_inserted'],
					$stats['events_updated'],
					$stats['events_skipped'],
					$stats['services_imported']
				),
				'stats' => $stats
			] );
			
		} catch ( Exception $e ) {
			@ChurchTools_Suite_Logger::log('=== MANUAL SYNC ERROR ===', 'error');
			@ChurchTools_Suite_Logger::log('Exception: ' . $e->getMessage(), 'error');
			@ChurchTools_Suite_Logger::log('Stack trace: ' . $e->getTraceAsString(), 'error');
			
			// Fehler-Eintrag
			if (isset($sync_id) && $sync_id && isset($history_repo)) {
				try {
					$history_repo->complete_sync($sync_id, [], $e->getMessage());
				} catch (Exception $inner_e) {
					// Ignore history errors during error handling
				}
			}
			
			wp_send_json_error( [
				'message' => __( 'Fehler: ', 'churchtools-suite' ) . $e->getMessage()
			] );
		} catch ( Error $e ) {
			// Catch PHP 7+ Fatal Errors
			@ChurchTools_Suite_Logger::log('=== MANUAL SYNC FATAL ERROR ===', 'error');
			@ChurchTools_Suite_Logger::log('Fatal Error: ' . $e->getMessage(), 'error');
			@ChurchTools_Suite_Logger::log('File: ' . $e->getFile() . ':' . $e->getLine(), 'error');
			
			wp_send_json_error( [
				'message' => __( 'Fataler Fehler: ', 'churchtools-suite' ) . $e->getMessage() . ' in ' . basename($e->getFile()) . ':' . $e->getLine()
			] );
		}
	}
	
	/**
	 * AJAX Handler: Trigger Keepalive
	 * Führt sofortigen Session Keepalive aus
	 */
	public function ajax_trigger_keepalive() {
		check_ajax_referer( 'churchtools_suite_admin', 'nonce' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [
				'message' => __( 'Keine Berechtigung.', 'churchtools-suite' )
			] );
		}
		
		try {
			// CT-Client laden
			require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-ct-client.php';
			$ct_client = new ChurchTools_Suite_CT_Client();
			
			// Keepalive ausführen
			$result = $ct_client->keepalive();
			
			if (is_wp_error($result)) {
				wp_send_json_error( [
					'message' => __( 'Keepalive fehlgeschlagen: ', 'churchtools-suite' ) . $result->get_error_message()
				] );
				return;
			}
			
			wp_send_json_success( [
				'message' => __( '✅ Session Keepalive erfolgreich!', 'churchtools-suite' )
			] );
			
		} catch ( Exception $e ) {
			wp_send_json_error( [
				'message' => __( 'Fehler: ', 'churchtools-suite' ) . $e->getMessage()
			] );
		}
	}
	
	/**
	 * AJAX Handler: Reload Logs
	 */
	public function ajax_reload_logs() {
		// Clean any previous output
		ob_clean();
		
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
			// Load Logger class
			require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-logger.php';
			
			$log_content = ChurchTools_Suite_Logger::get_log_content(100); // Last 100 lines
			
			if ( empty( $log_content ) ) {
				$html = '<span style="color: #8c8f94;">Keine Logs verfügbar. Führen Sie einen Sync aus, um Logs zu generieren.</span>';
			} else {
				// Colorize log levels
				$log_content = htmlspecialchars( $log_content );
				$log_content = preg_replace( '/\[ERROR\]/', '<span style="color: #f48771; font-weight: 600;">[ERROR]</span>', $log_content );
				$log_content = preg_replace( '/\[WARNING\]/', '<span style="color: #dcdcaa; font-weight: 600;">[WARNING]</span>', $log_content );
				$log_content = preg_replace( '/\[INFO\]/', '<span style="color: #4ec9b0; font-weight: 600;">[INFO]</span>', $log_content );
				$html = $log_content;
			}
			
			wp_send_json_success( [
				'html' => $html,
				'message' => __( 'Logs neu geladen.', 'churchtools-suite' )
			] );
			
		} catch ( Exception $e ) {
			wp_send_json_error( [
				'message' => __( 'Fehler beim Laden der Logs: ', 'churchtools-suite' ) . $e->getMessage()
			] );
		}
	}
	
	/**
	 * AJAX Handler: Clear Logs
	 */
	public function ajax_clear_logs() {
		// Clean any previous output
		ob_clean();
		
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
			// Load Logger class
			require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-logger.php';
			
			ChurchTools_Suite_Logger::clear_log();
			
			wp_send_json_success( [
				'message' => __( 'Logs gelöscht.', 'churchtools-suite' ),
				'html' => '<span style="color: #8c8f94;">Logs wurden gelöscht. Führen Sie einen Sync aus, um neue Logs zu generieren.</span>'
			] );
			
		} catch ( Exception $e ) {
			wp_send_json_error( [
				'message' => __( 'Fehler beim Löschen der Logs: ', 'churchtools-suite' ) . $e->getMessage()
			] );
		}
	}
	
	/**
	 * AJAX Handler: Clear Block Debug Logs
	 */
	public function ajax_clear_block_logs() {
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
			// Clear block logs
			delete_option( 'churchtools_suite_block_debug_logs' );
			delete_option( 'churchtools_suite_block_status' );
			
			wp_send_json_success( [
				'message' => __( 'Block-Logs gelöscht.', 'churchtools-suite' )
			] );
			
		} catch ( Exception $e ) {
			wp_send_json_error( [
				'message' => __( 'Fehler beim Löschen: ', 'churchtools-suite' ) . $e->getMessage()
			] );
		}
	}
	
	/**
	 * AJAX Handler: Get Modal Template
	 */
	public function ajax_get_modal_template() {
		// Check nonce
		check_ajax_referer( 'churchtools_suite_public', 'nonce' );
		
		// Load template loader
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-template-loader.php';
		
		// Render modal template
		ob_start();
		ChurchTools_Suite_Template_Loader::render_template( 'modal/event-detail.php', [], false );
		$html = ob_get_clean();
		
		wp_send_json_success( [
			'html' => $html
		] );
	}
	
	/**
	 * AJAX Handler: Get Event Details
	 */
	public function ajax_get_event_details() {
		// Check nonce
		check_ajax_referer( 'churchtools_suite_public', 'nonce' );
		
		$event_id = isset( $_POST['event_id'] ) ? intval( $_POST['event_id'] ) : 0;
		
		if ( ! $event_id ) {
			wp_send_json_error( [
				'message' => __( 'Keine Event-ID angegeben.', 'churchtools-suite' )
			] );
		}
		
		// Load repositories
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-repository-base.php';
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-events-repository.php';
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-calendars-repository.php';
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-event-services-repository.php';
		
		global $wpdb;
		
		$events_repo = new ChurchTools_Suite_Events_Repository( $wpdb );
		$calendars_repo = new ChurchTools_Suite_Calendars_Repository( $wpdb );
		$services_repo = new ChurchTools_Suite_Event_Services_Repository( $wpdb );
		
		// Get event
		$event = $events_repo->get_by_id( $event_id );
		
		if ( ! $event ) {
			wp_send_json_error( [
				'message' => __( 'Event nicht gefunden.', 'churchtools-suite' )
			] );
		}
		
		// Get calendar
		$calendar = null;
		if ( $event->calendar_id ) {
			$calendar = $calendars_repo->get_by_calendar_id( $event->calendar_id );
		}
		
		// Get services
		$services = $services_repo->get_for_event( $event_id );
		
		// Format dates with WordPress timezone
		$date_format = get_option( 'date_format', 'd.m.Y' );
		$time_format = get_option( 'time_format', 'H:i' );
		
		// Check if 24h format (no 'a' or 'A' in format string)
		$is_24h = ( strpos( $time_format, 'a' ) === false && strpos( $time_format, 'A' ) === false );
		$time_suffix = $is_24h ? ' Uhr' : '';
		
		// Convert to WordPress timezone
		$start_timestamp = strtotime( get_date_from_gmt( $event->start_datetime ) );
		$end_timestamp = $event->end_datetime ? strtotime( get_date_from_gmt( $event->end_datetime ) ) : null;
		
		// Format times with suffix
		$start_time_formatted = date_i18n( $time_format, $start_timestamp ) . $time_suffix;
		$end_time_formatted = '';
		
		if ( $end_timestamp ) {
			$end_time_formatted = date_i18n( $time_format, $end_timestamp ) . $time_suffix;
		}
		
		// Build time display string
		$time_display = $start_time_formatted;
		if ( $end_time_formatted ) {
			$time_display .= ' - ' . $end_time_formatted;
		}
		
		// Build response
		$response = [
			'id' => $event->id,
			'title' => $event->title,
			'description' => wpautop( $event->description ),
			'start_date' => date_i18n( $date_format, $start_timestamp ),
			'start_time' => $start_time_formatted,
			'end_time' => $end_time_formatted,
			'time_display' => $time_display,
			'location_name' => $event->location_name,
			'calendar_name' => $calendar ? $calendar->name : '',
			'calendar_color' => $calendar ? $calendar->color : '#3498db',
			'services' => []
		];
		
		// Format services
		if ( $services ) {
			foreach ( $services as $service ) {
				$response['services'][] = [
					'service_name' => $service->service_name,
					'person_name' => $service->person_name
				];
			}
		}
		
		wp_send_json_success( $response );
	}
	
	/**
	 * AJAX: Save Shortcode Preset
	 */
	public function ajax_save_preset() {
		check_ajax_referer( 'churchtools_suite_admin', 'nonce' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Keine Berechtigung', 'churchtools-suite' ) ] );
		}
		
		$name = sanitize_text_field( $_POST['name'] ?? '' );
		$description = sanitize_textarea_field( $_POST['description'] ?? '' );
		$shortcode_tag = sanitize_text_field( $_POST['shortcode_tag'] ?? '' );
		$configuration_json = wp_unslash( $_POST['configuration'] ?? '{}' );
		
		if ( empty( $name ) || empty( $shortcode_tag ) ) {
			wp_send_json_error( [ 'message' => __( 'Name und Shortcode-Typ sind Pflichtfelder', 'churchtools-suite' ) ] );
		}
		
		// Decode and validate configuration
		$configuration = json_decode( $configuration_json, true );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			wp_send_json_error( [ 'message' => __( 'Ungültige Konfiguration', 'churchtools-suite' ) ] );
		}
		
		// Store original base view before replacing with slug
		if ( isset( $configuration['view'] ) && ! empty( $configuration['view'] ) ) {
			$configuration['_base_view'] = $configuration['view'];
		}
		
		// Generate slug from name
		$slug = sanitize_title( $name );
		
		// Replace view parameter with preset slug
		$configuration['view'] = $slug;
		
		// Load repository
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-repository-base.php';
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-shortcode-presets-repository.php';
		$presets_repo = new ChurchTools_Suite_Shortcode_Presets_Repository();
		
		// Save preset
		$preset_id = $presets_repo->create_preset( [
			'name'           => $name,
			'description'    => $description,
			'shortcode_tag'  => $shortcode_tag,
			'configuration'  => $configuration,
			'is_system'      => 0,
		] );
		
		if ( $preset_id ) {
			wp_send_json_success( [
				'message' => __( 'Preset erfolgreich gespeichert', 'churchtools-suite' ),
				'preset_id' => $preset_id,
			] );
		} else {
			wp_send_json_error( [ 'message' => __( 'Fehler beim Speichern', 'churchtools-suite' ) ] );
		}
	}
	
	/**
	 * AJAX: Update Shortcode Preset
	 */
	public function ajax_update_preset() {
		check_ajax_referer( 'churchtools_suite_admin', 'nonce' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Keine Berechtigung', 'churchtools-suite' ) ] );
		}
		
		$preset_id = absint( $_POST['preset_id'] ?? 0 );
		$name = sanitize_text_field( $_POST['name'] ?? '' );
		$description = sanitize_textarea_field( $_POST['description'] ?? '' );
		$shortcode_tag = sanitize_text_field( $_POST['shortcode_tag'] ?? '' );
		$configuration_json = wp_unslash( $_POST['configuration'] ?? '{}' );
		
		if ( ! $preset_id ) {
			wp_send_json_error( [ 'message' => __( 'Ungültige Preset-ID', 'churchtools-suite' ) ] );
		}
		
		if ( empty( $name ) || empty( $shortcode_tag ) ) {
			wp_send_json_error( [ 'message' => __( 'Name und Shortcode-Typ sind Pflichtfelder', 'churchtools-suite' ) ] );
		}
		
		// Decode and validate configuration
		$configuration = json_decode( $configuration_json, true );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			wp_send_json_error( [ 'message' => __( 'Ungültige Konfiguration', 'churchtools-suite' ) ] );
		}
		
		// Store original base view before replacing with slug
		if ( isset( $configuration['view'] ) && ! empty( $configuration['view'] ) ) {
			$configuration['_base_view'] = $configuration['view'];
		}
		
		// Generate slug from name
		$slug = sanitize_title( $name );
		
		// Replace view parameter with preset slug
		$configuration['view'] = $slug;
		
		// Load repository
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-repository-base.php';
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-shortcode-presets-repository.php';
		$presets_repo = new ChurchTools_Suite_Shortcode_Presets_Repository();
		
		// Update preset
		$success = $presets_repo->update_preset( $preset_id, [
			'name'           => $name,
			'description'    => $description,
			'shortcode_tag'  => $shortcode_tag,
			'configuration'  => $configuration,
		] );
		
		if ( $success ) {
			wp_send_json_success( [
				'message' => __( 'Preset erfolgreich aktualisiert', 'churchtools-suite' ),
				'preset_id' => $preset_id,
			] );
		} else {
			wp_send_json_error( [ 'message' => __( 'Fehler beim Aktualisieren (System-Presets können nicht bearbeitet werden)', 'churchtools-suite' ) ] );
		}
	}
	
	/**
	 * AJAX: Delete Shortcode Preset
	 */
	public function ajax_delete_preset() {
		check_ajax_referer( 'churchtools_suite_admin', 'nonce' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Keine Berechtigung', 'churchtools-suite' ) ] );
		}
		
		$preset_id = absint( $_POST['preset_id'] ?? 0 );
		
		if ( ! $preset_id ) {
			wp_send_json_error( [ 'message' => __( 'Ungültige Preset-ID', 'churchtools-suite' ) ] );
		}
		
		// Load repository
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-repository-base.php';
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-shortcode-presets-repository.php';
		$presets_repo = new ChurchTools_Suite_Shortcode_Presets_Repository();
		
		// Delete preset (checks for system presets internally)
		$success = $presets_repo->delete_preset( $preset_id );
		
		if ( $success ) {
			wp_send_json_success( [
				'message' => __( 'Preset erfolgreich gelöscht', 'churchtools-suite' ),
			] );
		} else {
			wp_send_json_error( [ 'message' => __( 'Fehler beim Löschen (System-Presets können nicht gelöscht werden)', 'churchtools-suite' ) ] );
		}
	}
	
	/**
	 * AJAX Handler: Get Calendars für Checkbox-Auswahl
	 * 
	 * @since 0.6.5.18
	 */
	public function ajax_get_calendars() {
		// Check nonce
		check_ajax_referer( 'churchtools_suite_admin', 'nonce' );
		
		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Keine Berechtigung', 'churchtools-suite' ) ] );
		}
		
		// Load repository
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-repository-base.php';
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-calendars-repository.php';
		$calendars_repo = new ChurchTools_Suite_Calendars_Repository();
		
		// Get all calendars
		$calendars = $calendars_repo->get_all();
		
		if ( empty( $calendars ) ) {
			wp_send_json_success( [
				'calendars' => [],
				'message' => __( 'Keine Kalender verfügbar. Bitte zuerst Kalender synchronisieren.', 'churchtools-suite' ),
			] );
			return;
		}
		
		// Format for frontend
		$formatted_calendars = array_map( function( $calendar ) {
			return [
				'id' => $calendar->calendar_id,
				'name' => $calendar->name,
				'color' => $calendar->color ?? '#667eea',
			];
		}, $calendars );
		
		wp_send_json_success( [
			'calendars' => $formatted_calendars,
		] );
	}
	
	/**
	 * AJAX Handler: Clear Events
	 * Löscht alle Events aus der Datenbank
	 */
	public function ajax_clear_events() {
		check_ajax_referer( 'churchtools_suite_admin', 'nonce' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Keine Berechtigung', 'churchtools-suite' ) ] );
		}
		
		global $wpdb;
		$events_table = $wpdb->prefix . 'cts_events';
		$event_services_table = $wpdb->prefix . 'cts_event_services';
		
		// Delete event services first (foreign key)
		$services_deleted = $wpdb->query( "DELETE FROM {$event_services_table}" );
		
		// Delete events
		$events_deleted = $wpdb->query( "DELETE FROM {$events_table}" );
		
		wp_send_json_success( [
			'message' => sprintf(
				__( '%d Events und %d Service-Zuordnungen gelöscht', 'churchtools-suite' ),
				$events_deleted,
				$services_deleted
			)
		] );
	}
	
	/**
	 * AJAX Handler: Clear Calendars
	 * Löscht alle Kalender aus der Datenbank
	 */
	public function ajax_clear_calendars() {
		check_ajax_referer( 'churchtools_suite_admin', 'nonce' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Keine Berechtigung', 'churchtools-suite' ) ] );
		}
		
		global $wpdb;
		$calendars_table = $wpdb->prefix . 'cts_calendars';
		
		$deleted = $wpdb->query( "DELETE FROM {$calendars_table}" );
		
		wp_send_json_success( [
			'message' => sprintf(
				__( '%d Kalender gelöscht', 'churchtools-suite' ),
				$deleted
			)
		] );
	}
	
	/**
	 * AJAX Handler: Clear Services
	 * Löscht alle Services und Service-Gruppen
	 */
	public function ajax_clear_services() {
		check_ajax_referer( 'churchtools_suite_admin', 'nonce' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Keine Berechtigung', 'churchtools-suite' ) ] );
		}
		
		global $wpdb;
		$services_table = $wpdb->prefix . 'cts_services';
		$service_groups_table = $wpdb->prefix . 'cts_service_groups';
		$event_services_table = $wpdb->prefix . 'cts_event_services';
		
		// Delete event services first (foreign key)
		$event_services_deleted = $wpdb->query( "DELETE FROM {$event_services_table}" );
		
		// Delete services
		$services_deleted = $wpdb->query( "DELETE FROM {$services_table}" );
		
		// Delete service groups
		$groups_deleted = $wpdb->query( "DELETE FROM {$service_groups_table}" );
		
		wp_send_json_success( [
			'message' => sprintf(
				__( '%d Services, %d Service-Gruppen und %d Event-Service-Zuordnungen gelöscht', 'churchtools-suite' ),
				$services_deleted,
				$groups_deleted,
				$event_services_deleted
			)
		] );
	}
	
	/**
	 * AJAX Handler: Clear Sync History
	 * Löscht die gesamte Sync-Historie
	 */
	public function ajax_clear_sync_history() {
		check_ajax_referer( 'churchtools_suite_admin', 'nonce' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Keine Berechtigung', 'churchtools-suite' ) ] );
		}
		
		global $wpdb;
		$sync_history_table = $wpdb->prefix . 'cts_sync_history';
		
		$deleted = $wpdb->query( "DELETE FROM {$sync_history_table}" );
		
		wp_send_json_success( [
			'message' => sprintf(
				__( '%d Sync-Historie-Einträge gelöscht', 'churchtools-suite' ),
				$deleted
			)
		] );
	}
	
	/**
	 * AJAX Handler: Full Reset
	 * Löscht ALLE Daten (Events, Kalender, Services, Sync-Historie)
	 */
	public function ajax_full_reset() {
		check_ajax_referer( 'churchtools_suite_admin', 'nonce' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Keine Berechtigung', 'churchtools-suite' ) ] );
		}
		
		global $wpdb;
		
		// Delete all data from all tables
		$tables = [
			$wpdb->prefix . 'cts_event_services',
			$wpdb->prefix . 'cts_events',
			$wpdb->prefix . 'cts_calendars',
			$wpdb->prefix . 'cts_services',
			$wpdb->prefix . 'cts_service_groups',
			$wpdb->prefix . 'cts_sync_history',
		];
		
		$total_deleted = 0;
		foreach ( $tables as $table ) {
			$deleted = $wpdb->query( "DELETE FROM {$table}" );
			$total_deleted += $deleted;
		}
		
		wp_send_json_success( [
			'message' => sprintf(
				__( 'Plugin erfolgreich zurückgesetzt! %d Einträge aus %d Tabellen gelöscht.', 'churchtools-suite' ),
				$total_deleted,
				count( $tables )
			)
		] );
	}
}
