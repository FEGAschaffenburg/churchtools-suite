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
		// Clean any previous output to avoid HTML before JSON
		while ( ob_get_level() ) {
			ob_end_clean();
		}

		// Check nonce
		check_ajax_referer( 'churchtools_suite_admin', 'nonce' );

		// v1.0.2.0: Permission updated to manage_churchtools_suite
		if ( ! current_user_can( 'manage_churchtools_suite' ) ) {
			wp_send_json_error( [ 'message' => __( 'Keine Berechtigung.', 'churchtools-suite' ) ] );
			return;
		}

		// v0.10.3.4: Set redirect destination BEFORE update (prevents plugins.php redirect)
		set_transient( 'churchtools_suite_update_redirect', admin_url( 'admin.php?page=churchtools-suite' ), 60 );

		try {
			if ( ! class_exists( 'ChurchTools_Suite_Auto_Updater' ) ) {
				require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-auto-updater.php';
			}

			$result = ChurchTools_Suite_Auto_Updater::run_update_now();

			if ( is_wp_error( $result ) ) {
				wp_send_json_error( [ 'message' => $result->get_error_message() ] );
				return;
			}

			// Cache leeren damit Update-Meldung nach Installation verschwindet (v0.10.3.2)
			delete_transient( 'churchtools_suite_update_info' );
			delete_transient( 'churchtools_suite_release_info' );

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
		// Clean any previous output to avoid HTML before JSON
		while ( ob_get_level() ) {
			ob_end_clean();
		}

		// Check nonce
		check_ajax_referer( 'churchtools_suite_admin', 'nonce' );

		// Permission: manage_churchtools_suite (General Admin)
		if ( ! current_user_can( 'manage_churchtools_suite' ) ) {
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
	 * @since 0.10.2.9 Load public JS too (for calendar navigation in demos)
	 */
	public function enqueue_scripts() {
		// Ensure media library is available for calendar image picker
		wp_enqueue_media();

		// Load public JS first (for frontend features like calendar navigation)
		wp_enqueue_script(
			'churchtools-suite-public',
			CHURCHTOOLS_SUITE_URL . 'assets/js/churchtools-suite-public.js',
			[ 'jquery' ],
			$this->version,
			true
		);
		
		// Localize public script (needed for AJAX calendar navigation)
		wp_localize_script(
			'churchtools-suite-public',
			'churchtoolsSuitePublic',
			[
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'churchtools_suite_public' ),
			]
		);
		
		// Main admin script (jQuery-based)
		wp_enqueue_script(
			'churchtools-suite-admin',
			CHURCHTOOLS_SUITE_URL . 'assets/js/churchtools-suite-admin.js',
			[ 'jquery', 'churchtools-suite-public' ],
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
		// v1.0.2.0: Use custom capability instead of manage_options
		// This allows ChurchTools Managers to access the plugin without full WordPress admin access
		add_menu_page(
			__( 'ChurchTools Suite', 'churchtools-suite' ),
			__( 'ChurchTools', 'churchtools-suite' ),
			'manage_churchtools_suite', // New: Custom capability (vs manage_options)
			'churchtools-suite',
			[ $this, 'display_admin_page' ],
			'dashicons-calendar-alt',
			30
		);
		
		// v0.10.4.0: Shortcode Manager deaktiviert - Toggles werden über Gutenberg/Elementor gesteuert
		/*
		add_submenu_page(
			'churchtools-suite',
			__( 'Shortcode Manager', 'churchtools-suite' ),
			__( '⚡ Shortcode Manager', 'churchtools-suite' ),
			'manage_churchtools_suite',
			'churchtools-suite-shortcodes',
			[ $this, 'display_shortcode_manager' ]
		);
		*/
		
		// Shortcode Demo removed as separate submenu — demo is now integrated into Shortcode Manager

		// Add Data subpage (separate admin page for large lists)
		add_submenu_page(
			'churchtools-suite',
			__( 'Daten', 'churchtools-suite' ),
			__( '📋 Daten', 'churchtools-suite' ),
			'manage_churchtools_suite',
			'churchtools-suite-data',
			[ $this, 'display_data_page' ]
		);

		// Add Disclaimer subpage (separate admin page for legal information)
		add_submenu_page(
			'churchtools-suite',
			__( 'Haftungsausschluss', 'churchtools-suite' ),
			__( '⚠️ Haftungsausschluss', 'churchtools-suite' ),
			'manage_churchtools_suite',
			'churchtools-suite-disclaimer',
			[ $this, 'display_disclaimer_page' ]
		);

		// Add Documentation link
		add_submenu_page(
			'churchtools-suite',
			__( 'Dokumentation', 'churchtools-suite' ),
			__( '📖 Dokumentation', 'churchtools-suite' ),
			'manage_churchtools_suite',
			'churchtools-suite-docs',
			[ $this, 'redirect_to_documentation' ]
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
	 * Display Data page (dedicated subpage)
	 */
	public function display_data_page() {
		// Reuse existing data subtab view
		include_once CHURCHTOOLS_SUITE_PATH . 'admin/views/tab-data.php';
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
		// Main AJAX handlers for Settings, Calendars, Events, Services
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
		// Simple ping endpoint to verify AJAX/JSON pipeline
		add_action( 'wp_ajax_cts_keepalive_ping', [ $this, 'ajax_keepalive_ping' ] );
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
		
		// Frontend AJAX (für alle Nutzer, auch nicht-eingeloggte) (v0.10.2.7)
		add_action( 'wp_ajax_cts_load_calendar_month', [ $this, 'ajax_load_calendar_month' ] );
		add_action( 'wp_ajax_nopriv_cts_load_calendar_month', [ $this, 'ajax_load_calendar_month' ] );
		add_action( 'wp_ajax_cts_clear_sync_history', [ $this, 'ajax_clear_sync_history' ] );
		add_action( 'wp_ajax_cts_full_reset', [ $this, 'ajax_full_reset' ] );
		add_action( 'wp_ajax_cts_complete_reset', [ $this, 'ajax_complete_reset' ] ); // v0.10.1.4
		add_action( 'wp_ajax_cts_rebuild_database', [ $this, 'ajax_rebuild_database' ] ); // v0.9.0.1
		
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
		
		// Permission: configure_churchtools_suite (Settings)
		if ( ! current_user_can( 'configure_churchtools_suite' ) ) {
			wp_send_json_error( [ 'message' => __( 'Keine Berechtigung.', 'churchtools-suite' ) ] );
			return;
		}
		
		try {
			// Load CT Client
			require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-ct-client.php';
			require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-logger.php';
			
			ChurchTools_Suite_Logger::info( 'test_connection', 'Verbindungstest gestartet' );
			
			$client = new ChurchTools_Suite_CT_Client();
			$result = $client->test_connection();
			
			if ( $result['success'] ) {
				ChurchTools_Suite_Logger::info( 'test_connection', 'Verbindungstest erfolgreich', [
					'user' => $result['user_info'] ?? 'unknown'
				] );
				
				wp_send_json_success( [
					'message' => $result['message'],
					'user_info' => $result['user_info'] ?? null
				] );
			} else {
				$error_msg = $result['message'] ?? 'Unbekannter Fehler';
				ChurchTools_Suite_Logger::error( 'test_connection', 'Verbindungstest fehlgeschlagen', [
					'message' => $error_msg,
					'error_code' => $result['error_code'] ?? null,
					'error_details' => $result['error_details'] ?? null
				] );
				
				wp_send_json_error( [
					'message' => $error_msg,
					'error_code' => $result['error_code'] ?? null,
					'error_details' => $result['error_details'] ?? null
				] );
			}
		} catch ( Exception $e ) {
			ChurchTools_Suite_Logger::error( 'test_connection', 'Exception beim Verbindungstest', [
				'exception' => $e->getMessage(),
				'trace' => $e->getTraceAsString()
			] );
			
			wp_send_json_error( [
				'message' => __( 'Fehler beim Testen der Verbindung.', 'churchtools-suite' ),
				'error_details' => $e->getMessage()
			] );
		}
	}

	/**
	 * AJAX Handler: Keepalive ping (test endpoint)
	 * Returns simple JSON to validate that admin-ajax.php returns JSON correctly.
	 */
	public function ajax_keepalive_ping() {
		// Use non-fatal nonce check and return JSON
		$ok = check_ajax_referer( 'churchtools_suite_admin', 'nonce', false );
		if ( $ok === false ) {
			wp_send_json_error( [ 'message' => 'Invalid nonce' ] );
			return;
		}
		// Permission: configure_churchtools_suite (Settings)
		if ( ! current_user_can( 'configure_churchtools_suite' ) ) {
			wp_send_json_error( [ 'message' => 'No permission' ] );
			return;
		}
		wp_send_json_success( [ 'message' => 'pong' ] );
		
		// Check permissions
		if ( ! current_user_can( 'configure_churchtools_suite' ) ) {
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
		
		// Check permissions: manage_churchtools_calendars (Calendars)
		if ( ! current_user_can( 'manage_churchtools_calendars' ) ) {
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
		
		// Check permissions: manage_churchtools_calendars (Calendars)
		if ( ! current_user_can( 'manage_churchtools_calendars' ) ) {
			wp_send_json_error( [
				'message' => __( 'Keine Berechtigung.', 'churchtools-suite' )
			] );
			return;
		}
		
		try {
			// Get selected calendar IDs
			$selected_ids = isset( $_POST['selected_ids'] ) ? array_map( 'intval', $_POST['selected_ids'] ) : [];

			// Capture calendar fallback images (calendar_id => attachment_id)
			$calendar_images = [];
			if ( isset( $_POST['calendar_images'] ) && is_array( $_POST['calendar_images'] ) ) {
				foreach ( $_POST['calendar_images'] as $calendar_id => $attachment_id ) {
					$calendar_id_sanitized = sanitize_text_field( wp_unslash( $calendar_id ) );
					$attachment_id_int = absint( $attachment_id );
					if ( $calendar_id_sanitized && $attachment_id_int > 0 ) {
						$calendar_images[ $calendar_id_sanitized ] = $attachment_id_int;
					}
				}
			}

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

			// Update calendar_image_id in table (v0.9.9.58)
			foreach ( $calendar_images as $calendar_id => $attachment_id ) {
				$calendars_repo->update_calendar_image_by_calendar_id( $calendar_id, $attachment_id );
			}

			// Persist fallback images option (keep for backward compatibility)
			update_option( 'churchtools_suite_calendar_images', $calendar_images, false );

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
		
		// Check permissions: manage_churchtools_services (Services)
		if ( ! current_user_can( 'manage_churchtools_services' ) ) {
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
		
		// Check permissions: manage_churchtools_services (Services)
		if ( ! current_user_can( 'manage_churchtools_services' ) ) {
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
		
		// Check permissions: manage_churchtools_services (Services)
		if ( ! current_user_can( 'manage_churchtools_services' ) ) {
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
		
		// Check permissions: manage_churchtools_services (Services)
		if ( ! current_user_can( 'manage_churchtools_services' ) ) {
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
		// v0.10.4.23: Clean any output before JSON (fixes "keine gültige JSON-Antwort")
		if ( ob_get_level() === 0 ) {
			ob_start();
		}
		
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
		
		// Check permissions: sync_churchtools_events (Event Sync)
		if ( ! current_user_can( 'sync_churchtools_events' ) ) {
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
				
				ob_end_clean(); // v0.10.4.23
				wp_send_json_error( [
					'message' => $result->get_error_message()
				] );
				return;
			}
			
			// Mark sync as successful
			if ( $sync_id ) {
				$history_repo->complete_sync( $sync_id, $result, null );
			}
			
			ob_end_clean(); // v0.10.4.23
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
			
			ob_end_clean(); // v0.10.4.23
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

		// Permission: manage_churchtools_suite (General Admin)
		if ( ! current_user_can( 'manage_churchtools_suite' ) ) {
			wp_send_json_error( [ 'message' => __( 'Keine Berechtigung.', 'churchtools-suite' ) ] );
			return;
		}

		global $wpdb;
		$from = isset( $_POST['from'] ) ? sanitize_text_field( wp_unslash( $_POST['from'] ) ) : '';
		$to = isset( $_POST['to'] ) ? sanitize_text_field( wp_unslash( $_POST['to'] ) ) : '';
		$calendar_filter = isset( $_POST['calendar_id'] ) ? sanitize_text_field( wp_unslash( $_POST['calendar_id'] ) ) : '';
		$page = max( 1, (int) ( $_POST['paged'] ?? 1 ) );
		$limit = 200;
		$offset = ( $page - 1 ) * $limit;

		$prefix = $wpdb->prefix . CHURCHTOOLS_SUITE_DB_PREFIX;
		$table = $prefix . 'events';

		$sql = "SELECT id, event_id, appointment_id, calendar_id, title, description, event_description, appointment_description, start_datetime, end_datetime, is_all_day, location_name, address_name, address_street, address_zip, address_city, address_latitude, address_longitude, tags, status, raw_payload, last_modified, appointment_modified, created_at, updated_at FROM {$table} WHERE 1=1";
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
							<th><?php esc_html_e( 'Status', 'churchtools-suite' ); ?></th>
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
						$raw = ! empty( $event->raw_payload ) ? json_decode( $event->raw_payload, true ) : [];
						$base = $raw['appointment']['base'] ?? $raw['base'] ?? $raw;
						$link = $base['link'] ?? '';
						$image_url = $base['image'] ?? '';
						$is_canceled = (bool) ( $base['isCanceled'] ?? $raw['isCanceled'] ?? false );
						$last_modified = ! empty( $event->last_modified ) ? get_date_from_gmt( $event->last_modified ) : '';
						$appointment_modified = ! empty( $event->appointment_modified ) ? get_date_from_gmt( $event->appointment_modified ) : '';
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
							<td class="cts-event-status">
								<?php if ( $is_canceled ) : ?>
									<span class="cts-status-badge cts-status-canceled">⛔ <?php esc_html_e( 'Abgesagt', 'churchtools-suite' ); ?></span>
								<?php else : ?>
									<span class="cts-status-badge cts-status-active">✅ <?php esc_html_e( 'Aktiv', 'churchtools-suite' ); ?></span>
								<?php endif; ?>
							</td>
							<td class="cts-event-services"><span class="cts-muted">—</span></td>
							<td class="cts-event-details">
								<?php if ( $link || $image_url || $event->raw_payload ) : ?>
									<div class="cts-description-section cts-meta-grid" style="margin-bottom:8px;">
										<?php if ( ! empty( $event->event_id ) ) : ?><div><strong>Event ID:</strong> <?php echo esc_html( $event->event_id ); ?></div><?php endif; ?>
										<?php if ( ! empty( $event->appointment_id ) ) : ?><div><strong>Appointment ID:</strong> <?php echo esc_html( $event->appointment_id ); ?></div><?php endif; ?>
										<div><strong>Status:</strong> <?php echo $is_canceled ? esc_html__( 'Abgesagt', 'churchtools-suite' ) : esc_html__( 'Aktiv', 'churchtools-suite' ); ?></div>
										<div><strong><?php esc_html_e( 'Start', 'churchtools-suite' ); ?>:</strong> <?php echo esc_html( $event->start_datetime ); ?></div>
										<div><strong><?php esc_html_e( 'Ende', 'churchtools-suite' ); ?>:</strong> <?php echo esc_html( $event->end_datetime ?: '—' ); ?></div>
										<div><strong><?php esc_html_e( 'Last Modified', 'churchtools-suite' ); ?>:</strong> <?php echo esc_html( $last_modified ?: '—' ); ?></div>
										<div><strong><?php esc_html_e( 'Appointment Modified', 'churchtools-suite' ); ?>:</strong> <?php echo esc_html( $appointment_modified ?: '—' ); ?></div>
									</div>
									<?php if ( $link ) : ?>
										<div class="cts-description-section" style="margin-bottom:6px;">
											<strong>🔗 <?php esc_html_e( 'Link', 'churchtools-suite' ); ?>:</strong> <a href="<?php echo esc_url( $link ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $link ); ?></a>
										</div>
									<?php endif; ?>
									<?php if ( $image_url ) : ?>
										<div class="cts-description-section cts-image-preview">
											<img src="<?php echo esc_url( $image_url ); ?>" alt="" />
										</div>
									<?php endif; ?>
									<?php if ( $event->raw_payload ) : ?>
										<div class="cts-description-section">
											<strong>🧾 <?php esc_html_e( 'Raw Payload', 'churchtools-suite' ); ?>:</strong> <?php printf( esc_html__( '%d Zeichen JSON', 'churchtools-suite' ), strlen( $event->raw_payload ) ); ?>
										</div>
									<?php endif; ?>
								<?php else : ?>
									<span class="cts-muted">—</span>
								<?php endif; ?>
							</td>
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

		// Permission: manage_churchtools_suite (General Admin)
		if ( ! current_user_can( 'manage_churchtools_suite' ) ) {
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
		
		// Permission: sync_churchtools_events (Event Sync)
		if ( ! current_user_can( 'sync_churchtools_events' ) ) {
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
		// Clear any previous output to avoid HTML before JSON
		while ( ob_get_level() ) {
			ob_end_clean();
		}

		// Use plugin logger to verify handler invocation
		if ( ! class_exists( 'ChurchTools_Suite_Logger' ) ) {
			require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-logger.php';
		}
		if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
			ChurchTools_Suite_Logger::info( 'ajax_keepalive', 'ajax_trigger_keepalive called', [ 'user_id' => get_current_user_id() ] );
		}

		// Start a fresh output buffer to capture any unexpected output during handler
		ob_start();

		if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
			ChurchTools_Suite_Logger::debug( 'ajax_keepalive', 'Output buffer started for ajax_trigger_keepalive', [ 'ob_level' => ob_get_level() ] );
		}

		// Register shutdown handler to catch fatal errors and return JSON instead of raw HTML
		register_shutdown_function( function() {
			$error = error_get_last();
			if ( $error && in_array( $error['type'], [ E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR ] ) ) {
				require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-logger.php';
				$ob = '';
				if ( ob_get_length() ) {
					$ob = substr( ob_get_contents(), 0, 2000 );
				}
				$shutdown_payload = [
					'error' => $error,
					'headers_sent' => headers_sent(),
					'ob_preview' => $ob,
					'request_keys' => array_keys( $_REQUEST ?? [] ),
					'server' => [ 'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? null, 'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'] ?? null ],
				];
				ChurchTools_Suite_Logger::critical('ajax_keepalive', 'Fatal error in ajax_trigger_keepalive', $shutdown_payload );
				// If headers not sent, send JSON response
				if ( ! headers_sent() ) {
					header( 'Content-Type: application/json; charset=utf-8' );
					http_response_code( 500 );
					echo json_encode( [ 'success' => false, 'data' => [ 'message' => 'PHP Fatal Error: ' . $error['message'], 'file' => $error['file'], 'line' => $error['line'] ] ] );
				}
				exit;
			}
		} );

		// Check nonce without dying (avoid wp_die HTML) and handle failure with JSON
		$nonce_ok = check_ajax_referer( 'churchtools_suite_admin', 'nonce', false );
		if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
			ChurchTools_Suite_Logger::debug( 'ajax_keepalive', 'Nonce check result', [ 'nonce_ok' => $nonce_ok === false ? false : true, 'request_keys' => array_keys( $_REQUEST ?? [] ) ] );
		}
		if ( $nonce_ok === false ) {
			if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
				ChurchTools_Suite_Logger::warning( 'ajax_keepalive', 'Invalid nonce in ajax_trigger_keepalive', [ 'user_id' => get_current_user_id() ] );
			}
			// capture any output and discard
			@ob_end_clean();
			wp_send_json_error( [ 'message' => __( 'Ungültiger oder fehlender Nonce.', 'churchtools-suite' ) ] );
			return;
		}
		
		// Permission: configure_churchtools_suite (Settings)
		if ( ! current_user_can( 'configure_churchtools_suite' ) ) {
			wp_send_json_error( [
				'message' => __( 'Keine Berechtigung.', 'churchtools-suite' )
			] );
		}
		
		try {
			// CT-Client laden
			require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-ct-client.php';
			if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
				ChurchTools_Suite_Logger::debug( 'ajax_keepalive', 'Instantiating CT client' );
			}
			$ct_client = new ChurchTools_Suite_CT_Client();
			if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
				ChurchTools_Suite_Logger::info( 'ajax_keepalive', 'CT client instantiated', [ 'is_authenticated' => $ct_client->is_authenticated(), 'cookies_count' => is_array( $ct_client->get_cookies() ) ? count( $ct_client->get_cookies() ) : 0 ] );
			}
			
			// Keepalive ausführen
			if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
				ChurchTools_Suite_Logger::debug( 'ajax_keepalive', 'Calling keepalive on CT client' );
			}
			$result = $ct_client->keepalive();
			if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
				// Log summarized result (avoid dumping sensitive data)
				if ( is_wp_error( $result ) ) {
					ChurchTools_Suite_Logger::error( 'ajax_keepalive', 'Keepalive returned WP_Error', [ 'error' => $result->get_error_message() ] );
				} else {
					ChurchTools_Suite_Logger::info( 'ajax_keepalive', 'Keepalive succeeded', [ 'message' => is_array( $result ) && isset($result['message']) ? $result['message'] : null ] );
				}
			}
			
			if ( is_wp_error( $result ) ) {
				// Log result via plugin logger
				if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
					ChurchTools_Suite_Logger::error( 'ajax_keepalive', 'Keepalive failed: ' . $result->get_error_message(), [ 'user_id' => get_current_user_id() ] );
				}
				// Capture any extra output
				$extra = trim( ob_get_clean() );
				if ( class_exists( 'ChurchTools_Suite_Logger' ) && ! empty( $extra ) ) {
					ChurchTools_Suite_Logger::error( 'ajax_keepalive', 'Unexpected output before JSON (keepalive failed)', [ 'extra' => substr( $extra, 0, 2000 ) ] );
				}
				wp_send_json_error( [ 'message' => __( 'Keepalive fehlgeschlagen: ', 'churchtools-suite' ) . $result->get_error_message() ] );
				return;
			}

			// Capture any extra output and log via plugin logger
			$extra = trim( ob_get_clean() );
			if ( class_exists( 'ChurchTools_Suite_Logger' ) && ! empty( $extra ) ) {
				ChurchTools_Suite_Logger::warning( 'ajax_keepalive', 'Unexpected output before JSON (keepalive success)', [ 'extra' => substr( $extra, 0, 2000 ), 'user_id' => get_current_user_id() ] );
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
		
		// Check permissions: view_churchtools_debug (Debug/Logs)
		if ( ! current_user_can( 'view_churchtools_debug' ) ) {
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
		
		// Check permissions: view_churchtools_debug (Debug/Logs)
		if ( ! current_user_can( 'view_churchtools_debug' ) ) {
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
		
		// Check permissions: view_churchtools_debug (Debug/Logs)
		if ( ! current_user_can( 'view_churchtools_debug' ) ) {
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
		
		// Load logger
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-logger.php';
		
		// Load template loader
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-template-loader.php';
		
		// v0.9.9.84: Click action from Block, not Dashboard setting
		$click_action = isset( $_POST['click_action'] ) ? sanitize_text_field( $_POST['click_action'] ) : 'modal';
		$event_id = isset( $_POST['event_id'] ) ? sanitize_text_field( $_POST['event_id'] ) : null;
		
		ChurchTools_Suite_Logger::debug( 'ajax_modal', 'Modal request received', [
			'click_action' => $click_action,
			'event_id' => $event_id,
		] );
		
		// v0.9.9.84: If click action is "page", return page URL instead of modal HTML
		if ( $click_action === 'page' ) {
			// v0.9.9.85: Always use Dashboard setting (no Block override)
			$single_template = get_option( 'churchtools_suite_single_template', 'professional' );
			
			// Build event page URL - clean (only event_id + template)
			$event_page_url = home_url( '/events/?event_id=' . urlencode( $event_id ) . '&template=' . urlencode( $single_template ) );
			
			ChurchTools_Suite_Logger::debug( 'ajax_modal', 'Returning page redirect', [
				'action' => 'page',
				'template' => $single_template,
				'url' => $event_page_url,
			] );
			
			wp_send_json_success( [
				'action' => 'page',
				'url' => $event_page_url,
			] );
			return;
		}
		
		// v0.9.9.84: Click action is "modal" - continue with modal loading
		// v0.9.9.66: Get current view from client (if available)
		$current_view = isset( $_POST['current_view'] ) ? sanitize_text_field( $_POST['current_view'] ) : null;
		
		// Load modal template settings
		$global_modal_setting = get_option( 'churchtools_suite_modal_template', 'professional' );
		
		ChurchTools_Suite_Logger::debug( 'ajax_modal', 'Dashboard settings loaded', [
			'churchtools_suite_modal_template' => $global_modal_setting,
			'current_view' => $current_view,
		] );
		
		// v0.9.9.83: SIMPLIFIED LOGIC - Use single global template for ALL views
		// Scan filesystem for available templates
		$valid_modal_templates = self::get_available_modal_templates();
		
		// Fallback if no templates found
		if ( empty( $valid_modal_templates ) ) {
			$valid_modal_templates = [ 'professional' ];
		}
		
		// Check if dashboard setting references valid template
		if ( ! in_array( $global_modal_setting, $valid_modal_templates, true ) ) {
			ChurchTools_Suite_Logger::warning( 'ajax_modal', 'Dashboard template does not exist - using fallback', [
				'requested_template' => $global_modal_setting,
				'fallback_template' => $valid_modal_templates[0],
				'valid_templates' => $valid_modal_templates,
				'available_in_filesystem' => $valid_modal_templates,
			] );
			$global_modal_setting = $valid_modal_templates[0];
		}
		
		// v0.9.9.83: UNIFIED - Same template for ALL views (list, grid, calendar, single)
		// v0.9.9.85: Always use Dashboard setting (no Block override needed)
		$modal_template = $global_modal_setting;
		
		ChurchTools_Suite_Logger::debug( 'ajax_modal', 'Template selected', [
			'selected_template' => $modal_template,
			'source' => 'dashboard_global',
			'current_view' => $current_view,
		] );
		
		// v1.4.0: Neue Template-Struktur (views/event-modal/)
		$template_path = 'views/event-modal/' . sanitize_file_name( $modal_template ) . '.php';
		
		// v0.9.9.74: FIX - render_template with $echo=false to capture output
		// (using ob_start() with $echo=true doesn't work because output is sent directly)
		$html = ChurchTools_Suite_Template_Loader::render_template( $template_path, [], false );
		
		// v0.9.9.75: Enhanced logging using plugin logger
		$is_error_comment = strpos( $html, '<!--' ) === 0;
		ChurchTools_Suite_Logger::debug( 'ajax_modal', 'Template rendering result', [
			'template_path' => $template_path,
			'html_length' => strlen( $html ),
			'is_error' => $is_error_comment,
			'first_chars' => substr( $html, 0, 50 ),
		] );
		
		// If error, log details
		if ( $is_error_comment ) {
			$full_path = CHURCHTOOLS_SUITE_PATH . 'templates/' . $template_path;
			ChurchTools_Suite_Logger::warning( 'ajax_modal', 'Template not found or render error', [
				'template_path' => $template_path,
				'full_path' => $full_path,
				'file_exists' => file_exists( $full_path ),
				'churchtools_suite_path' => CHURCHTOOLS_SUITE_PATH,
				'error_message' => substr( $html, 4, 300 ), // Remove <!-- and get message
				'dashboard_modal_setting' => $global_modal_setting,
				'current_view' => $current_view,
			] );
		}
		
		// v0.10.3.6: Debug - Prüfe ob HTML vorhanden ist
		if ( empty( $html ) || $is_error_comment ) {
			// Fehler beim Laden des Templates
			wp_send_json_error( [
				'message' => 'Modal-Template konnte nicht geladen werden',
				'html' => $html, // Sende trotzdem für Debugging
				'debug' => [
					'template_path' => CHURCHTOOLS_SUITE_PATH . 'templates/views/event-modal/' . $modal_template . '.php',
					'exists' => file_exists( CHURCHTOOLS_SUITE_PATH . 'templates/views/event-modal/' . $modal_template . '.php' ),
					'current_view' => $current_view,
					'selected_modal' => $modal_template,
					'churchtools_suite_path' => CHURCHTOOLS_SUITE_PATH,
					'dashboard_modal_setting' => $global_modal_setting,
				],
			] );
			return;
		}
		
		// Success - log it
		ChurchTools_Suite_Logger::info( 'ajax_modal', 'Modal template loaded successfully', [
			'template_path' => $template_path,
			'selected_template' => $modal_template,
			'html_length' => strlen( $html ),
			'action' => 'modal',
		] );
		
		wp_send_json_success( [
			'action' => 'modal',
			'html' => $html
		] );
	}
	
	/**
	 * AJAX Handler: Get Event Details
	 * 
	 * v1.0.3.1: Supports demo events via fallback to Demo Data Provider
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
		
		// Get event from database
		$event = $events_repo->get_by_id( $event_id );
		
		// v1.0.3.1: Fallback to Demo Data Provider if event not found (demo events)
		$is_demo_event = false;
		if ( ! $event ) {
			// Check if demo plugin is active
			if ( defined( 'CHURCHTOOLS_SUITE_DEMO_VERSION' ) ) {
				// Try to load event from Demo Data Provider
				$demo_provider_path = WP_PLUGIN_DIR . '/churchtools-suite-demo/includes/services/class-demo-data-provider.php';
				if ( file_exists( $demo_provider_path ) ) {
					require_once $demo_provider_path;
					if ( class_exists( 'ChurchTools_Suite_Demo_Data_Provider' ) ) {
						// Get all demo events (inefficient but works for demo purposes)
						$demo_provider = new ChurchTools_Suite_Demo_Data_Provider();
						$demo_events = $demo_provider->get_events( [
							'from' => date( 'Y-m-d', strtotime( '-30 days' ) ),
							'to' => date( 'Y-m-d', strtotime( '+180 days' ) ),
							'limit' => 1000,
						] );
						
						// Find event by DB ID
						foreach ( $demo_events as $demo_event ) {
							if ( isset( $demo_event['id'] ) && (int) $demo_event['id'] === $event_id ) {
								// Convert array to object for consistent handling
								$event = (object) $demo_event;
								$is_demo_event = true;
								break;
							}
						}
					}
				}
			}
		}
		
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
		$start_datetime = $event->start_datetime;
		$end_datetime = $event->end_datetime;
		
		// v1.0.3.1: Handle timezone conversion differently for demo events
		if ( $is_demo_event ) {
			// Demo events are already in local timezone
			$start_timestamp = strtotime( $start_datetime );
			$end_timestamp = $end_datetime ? strtotime( $end_datetime ) : null;
		} else {
			// Real events from ChurchTools are in GMT
			$start_timestamp = strtotime( get_date_from_gmt( $start_datetime ) );
			$end_timestamp = $end_datetime ? strtotime( get_date_from_gmt( $end_datetime ) ) : null;
		}
		
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
		
		// Extract common fields (v1.0.3.1: Use isset() for optional fields)
		$event_id_val = $event->id;
		$title = $event->title;
		$event_desc = isset( $event->event_description ) ? $event->event_description : null;
		$apt_desc = isset( $event->appointment_description ) ? $event->appointment_description : null;
		$location_name = isset( $event->location_name ) ? $event->location_name : '';
		$address_name = isset( $event->address_name ) ? $event->address_name : '';
		$address_street = isset( $event->address_street ) ? $event->address_street : '';
		$address_zip = isset( $event->address_zip ) ? $event->address_zip : '';
		$address_city = isset( $event->address_city ) ? $event->address_city : '';
		$tags_json = isset( $event->tags ) ? $event->tags : null;
		$image_attachment_id = isset( $event->image_attachment_id ) ? $event->image_attachment_id : null;
		$image_url = isset( $event->image_url ) ? $event->image_url : null;
		
		// Build response
		$response = [
			'id' => $event_id_val,
			'title' => $title,
			'event_description' => ! empty( $event_desc ) ? wpautop( $event_desc ) : '',
			'appointment_description' => ! empty( $apt_desc ) ? wpautop( $apt_desc ) : '',
			'start_date' => date_i18n( $date_format, $start_timestamp ),
			'start_time' => $start_time_formatted,
			'end_time' => $end_time_formatted,
			'time_display' => $time_display,
			'location_name' => $location_name,
			'address_name' => $address_name,
			'address_street' => $address_street,
			'address_zip' => $address_zip,
			'address_city' => $address_city,
			'calendar_name' => $calendar ? $calendar->name : '',
			'calendar_color' => $calendar ? $calendar->color : '#3498db',
			'image_attachment_id' => $image_attachment_id,
			'image_url' => $image_url,
			'tags' => [],
			'services' => []
		];
		
		// Parse tags
		if ( ! empty( $tags_json ) ) {
			$tags_data = is_string( $tags_json ) ? json_decode( $tags_json, true ) : $tags_json;
			if ( is_array( $tags_data ) ) {
				$response['tags'] = $tags_data;
			}
		}
		
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
		
		// Permission: manage_churchtools_suite (General Admin)
		if ( ! current_user_can( 'manage_churchtools_suite' ) ) {
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
		
		// Permission: manage_churchtools_suite (General Admin)
		if ( ! current_user_can( 'manage_churchtools_suite' ) ) {
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
		
		// Permission: manage_churchtools_suite (General Admin)
		if ( ! current_user_can( 'manage_churchtools_suite' ) ) {
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
		
		// Check permissions: manage_churchtools_calendars (Calendars)
		if ( ! current_user_can( 'manage_churchtools_calendars' ) ) {
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
		
		// Permission: sync_churchtools_events (Event Sync)
		if ( ! current_user_can( 'sync_churchtools_events' ) ) {
			wp_send_json_error( [ 'message' => __( 'Keine Berechtigung', 'churchtools-suite' ) ] );
		}
		
		global $wpdb;
		$events_table = $wpdb->prefix . 'cts_events';
		$event_services_table = $wpdb->prefix . 'cts_event_services';
		
		// Delete event services first (foreign key)
		$services_deleted = $wpdb->query( "DELETE FROM {$event_services_table}" );
		if ( $services_deleted === false ) {
			wp_send_json_error( [ 'message' => __( 'Fehler beim Löschen der Event-Services', 'churchtools-suite' ) ] );
		}
		
		// Delete events
		$events_deleted = $wpdb->query( "DELETE FROM {$events_table}" );
		if ( $events_deleted === false ) {
			wp_send_json_error( [ 'message' => __( 'Fehler beim Löschen der Events', 'churchtools-suite' ) ] );
		}
		
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
		
		// Permission: manage_churchtools_calendars (Calendars)
		if ( ! current_user_can( 'manage_churchtools_calendars' ) ) {
			wp_send_json_error( [ 'message' => __( 'Keine Berechtigung', 'churchtools-suite' ) ] );
		}
		
		global $wpdb;
		$calendars_table = $wpdb->prefix . 'cts_calendars';
		
		$deleted = $wpdb->query( "DELETE FROM {$calendars_table}" );
		if ( $deleted === false ) {
			wp_send_json_error( [ 'message' => __( 'Fehler beim Löschen der Kalender', 'churchtools-suite' ) ] );
		}
		
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
		if ( $event_services_deleted === false ) {
			wp_send_json_error( [ 'message' => __( 'Fehler beim Löschen der Event-Service-Zuordnungen', 'churchtools-suite' ) ] );
		}
		
		// Delete services
		$services_deleted = $wpdb->query( "DELETE FROM {$services_table}" );
		if ( $services_deleted === false ) {
			wp_send_json_error( [ 'message' => __( 'Fehler beim Löschen der Services', 'churchtools-suite' ) ] );
		}
		
		// Delete service groups
		$groups_deleted = $wpdb->query( "DELETE FROM {$service_groups_table}" );
		if ( $groups_deleted === false ) {
			wp_send_json_error( [ 'message' => __( 'Fehler beim Löschen der Service-Gruppen', 'churchtools-suite' ) ] );
		}
		
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
		if ( $deleted === false ) {
			wp_send_json_error( [ 'message' => __( 'Fehler beim Löschen der Sync-Historie', 'churchtools-suite' ) ] );
		}
		
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
			if ( $deleted === false ) {
				wp_send_json_error( [ 'message' => sprintf( __( 'Fehler beim Löschen der Tabelle %s', 'churchtools-suite' ), esc_html( $table ) ) ] );
			}
			$total_deleted += $deleted;
		}
		
		wp_send_json_success( [
			'message' => sprintf(
				__( 'Daten erfolgreich zurückgesetzt! %d Einträge aus %d Tabellen gelöscht.', 'churchtools-suite' ),
				$total_deleted,
				count( $tables )
			)
		] );
	}
	
	/**
	 * AJAX Handler: Complete Reset (v0.10.1.4)
	 * Löscht ALLES: Daten + Einstellungen + Cookies
	 */
	public function ajax_complete_reset() {
		check_ajax_referer( 'churchtools_suite_admin', 'nonce' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Keine Berechtigung', 'churchtools-suite' ) ] );
		}
		
		global $wpdb;
		
		// 1. Delete all data from all tables (same as full_reset)
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
			if ( $deleted === false ) {
				wp_send_json_error( [ 'message' => sprintf( __( 'Fehler beim Löschen der Tabelle %s', 'churchtools-suite' ), esc_html( $table ) ) ] );
			}
			$total_deleted += $deleted;
		}
		
		// 2. Delete all plugin settings from wp_options
		$settings_deleted = 0;
		$settings_keys = [
			'churchtools_suite_ct_url',
			'churchtools_suite_ct_auth_method',
			'churchtools_suite_ct_username',
			'churchtools_suite_ct_password',
			'churchtools_suite_ct_token',
			'churchtools_suite_ct_cookies',
			'churchtools_suite_sync_days_past',
			'churchtools_suite_sync_days_future',
			'churchtools_suite_auto_sync_enabled',
			'churchtools_suite_auto_sync_interval',
			'churchtools_suite_advanced_mode',
			'churchtools_suite_events_last_sync',
			'churchtools_suite_last_sync_timestamp',
			'churchtools_suite_db_version',
			'churchtools_suite_session_keepalive',
		];
		
		foreach ( $settings_keys as $key ) {
			if ( delete_option( $key ) ) {
				$settings_deleted++;
			}
		}
		
		// 3. Clear all transients
		delete_transient( 'churchtools_suite_update_info' );
		
		wp_send_json_success( [
			'message' => sprintf(
				__( 'Plugin komplett zurückgesetzt!\n\n- %d Datenbank-Einträge gelöscht\n- %d Einstellungen gelöscht\n- Cookies gelöscht\n\nBitte Plugin neu konfigurieren.', 'churchtools-suite' ),
				$total_deleted,
				$settings_deleted
			)
		] );
	}
	
	/**
	 * AJAX Handler: Rebuild Database (v0.9.0.1)
	 * Löscht ALLE Tabellen und erstellt sie komplett neu
	 * Nützlich bei DB-Strukturproblemen nach Updates
	 */
	public function ajax_rebuild_database() {
		check_ajax_referer( 'churchtools_suite_admin', 'nonce' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Keine Berechtigung', 'churchtools-suite' ) ] );
		}
		
		global $wpdb;
		
		// 1. Drop ALL plugin tables
		$tables = [
			$wpdb->prefix . 'cts_event_services',
			$wpdb->prefix . 'cts_events',
			$wpdb->prefix . 'cts_calendars',
			$wpdb->prefix . 'cts_services',
			$wpdb->prefix . 'cts_service_groups',
			$wpdb->prefix . 'cts_sync_history',
			$wpdb->prefix . 'cts_schedule',
			$wpdb->prefix . 'cts_shortcode_presets',
		];
		
		$tables_dropped = 0;
		foreach ( $tables as $table ) {
			// Check if table exists before dropping
			$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" );
			if ( $table_exists ) {
				$wpdb->query( "DROP TABLE IF EXISTS {$table}" );
				$tables_dropped++;
			}
		}
		
		// 2. Reset DB version to force migration
		delete_option( 'churchtools_suite_db_version' );
		
		// 3. Run migrations to recreate tables
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-migrations.php';
		ChurchTools_Suite_Migrations::run_migrations();
		
		// 4. Get new DB version
		$new_version = get_option( 'churchtools_suite_db_version', '0.0' );
		
		wp_send_json_success( [
			'message' => sprintf(
				__( 'Datenbank erfolgreich neu aufgebaut!\n\n- %d Tabellen gelöscht\n- Alle Tabellen neu erstellt (DB Version %s)\n- Alle Daten verloren\n\nBitte Daten neu synchronisieren.', 'churchtools-suite' ),
				$tables_dropped,
				$new_version
			)
		] );
	}
	
	/**
	 * AJAX: Load calendar month (Frontend)
	 * 
	 * @since 0.10.2.7
	 */
	public function ajax_load_calendar_month() {
		// Log AJAX call start
		ChurchTools_Suite_Logger::debug( 'ajax_calendar', 'AJAX call started', [
			'POST' => $_POST,
			'nonce_isset' => isset( $_POST['nonce'] ),
		] );
		
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'churchtools_suite_public' ) ) {
			ChurchTools_Suite_Logger::error( 'ajax_calendar', 'Nonce verification failed' );
			wp_send_json_error( [ 'message' => __( 'Sicherheitsprüfung fehlgeschlagen', 'churchtools-suite' ) ] );
			return;
		}
		
		ChurchTools_Suite_Logger::debug( 'ajax_calendar', 'Nonce verified' );
		
		try {
			$year = isset( $_POST['year'] ) ? absint( $_POST['year'] ) : date( 'Y' );
			$month = isset( $_POST['month'] ) ? absint( $_POST['month'] ) : date( 'n' );
			
			ChurchTools_Suite_Logger::debug( 'ajax_calendar', 'Parameters extracted', [
				'year' => $year,
				'month' => $month,
			] );
			
			// Validierung
			if ( $year < 2000 || $year > 2100 || $month < 1 || $month > 12 ) {
				ChurchTools_Suite_Logger::error( 'ajax_calendar', 'Invalid date', [
					'year' => $year,
					'month' => $month,
				] );
				wp_send_json_error( [ 'message' => __( 'Ungültiges Datum', 'churchtools-suite' ) ] );
				return;
			}
			
			// Shortcode-Attribute aus Request
			$calendar_ids_raw = isset( $_POST['calendar_ids'] ) ? sanitize_text_field( $_POST['calendar_ids'] ) : '';
			$limit = isset( $_POST['limit'] ) ? absint( $_POST['limit'] ) : 100;
			// Clamp Limit auf sinnvolle Grenzen (1..500)
			if ( $limit < 1 || $limit > 500 ) {
				ChurchTools_Suite_Logger::debug( 'ajax_calendar', 'Limit clamped', [ 'original' => $limit ] );
				$limit = max( 1, min( 500, $limit ) );
			}
			$enable_modal = isset( $_POST['enable_modal'] ) ? filter_var( $_POST['enable_modal'], FILTER_VALIDATE_BOOLEAN ) : true;

			// Validierung calendar_ids: max. 20 IDs, nur A-Z, a-z, 0-9, _ und - (max. 100 Zeichen)
			$calendar_ids_list = [];
			if ( ! empty( $calendar_ids_raw ) ) {
				$parts = array_slice( array_map( 'trim', explode( ',', $calendar_ids_raw ) ), 0, 20 );
				foreach ( $parts as $p ) {
					if ( $p === '' ) { continue; }
					if ( preg_match( '/^[A-Za-z0-9_-]{1,100}$/', $p ) ) {
						$calendar_ids_list[] = $p;
					}
				}
				if ( empty( $calendar_ids_list ) ) {
					ChurchTools_Suite_Logger::warning( 'ajax_calendar', 'All provided calendar_ids invalid', [ 'raw' => $calendar_ids_raw ] );
					wp_send_json_error( [ 'message' => __( 'Ungültige Kalender-IDs', 'churchtools-suite' ) ] );
					return;
				}
			}
			
			// Anzeige-Optionen (für Tooltip-Infos)
			$show_time = isset( $_POST['show_time'] ) ? filter_var( $_POST['show_time'], FILTER_VALIDATE_BOOLEAN ) : true;
			$show_description = isset( $_POST['show_description'] ) ? filter_var( $_POST['show_description'], FILTER_VALIDATE_BOOLEAN ) : false;
			$show_location = isset( $_POST['show_location'] ) ? filter_var( $_POST['show_location'], FILTER_VALIDATE_BOOLEAN ) : false;
			$show_services = isset( $_POST['show_services'] ) ? filter_var( $_POST['show_services'], FILTER_VALIDATE_BOOLEAN ) : false;
			$show_calendar_name = isset( $_POST['show_calendar_name'] ) ? filter_var( $_POST['show_calendar_name'], FILTER_VALIDATE_BOOLEAN ) : false;
			
			// Berechne Datumsbereich für den Monat
			$from_date = sprintf( '%04d-%02d-01', $year, $month );
			$to_date = date( 'Y-m-t', strtotime( $from_date ) );
			
			ChurchTools_Suite_Logger::debug( 'ajax_calendar', 'Date range calculated', [
				'from' => $from_date,
				'to' => $to_date,
				'calendar_ids' => $calendar_ids,
				'limit' => $limit,
			] );
			
			// Baue Shortcode-Attribute
			$atts = [
				'view' => 'monthly-modern',
				'from' => $from_date,
				'to' => $to_date,
				'limit' => $limit,
				'enable_modal' => $enable_modal,
				'year' => $year,   // AJAX-spezifisch: Jahr für Titel
				'month' => $month, // AJAX-spezifisch: Monat für Titel
				'show_time' => $show_time,
				'show_description' => $show_description,
				'show_location' => $show_location,
				'show_services' => $show_services,
				'show_calendar_name' => $show_calendar_name,
			];
			
			if ( ! empty( $calendar_ids_list ) ) {
				$atts['calendar'] = implode( ',', $calendar_ids_list );
			}
			
			// Lade Template Loader
			if ( ! class_exists( 'ChurchTools_Suite_Template_Loader' ) ) {
				ChurchTools_Suite_Logger::debug( 'ajax_calendar', 'Loading Template_Loader class' );
				require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-template-loader.php';
			}
			
			// Lade Events für diesen Monat
			if ( ! class_exists( 'ChurchTools_Suite_Events_Repository' ) ) {
				ChurchTools_Suite_Logger::debug( 'ajax_calendar', 'Loading Events_Repository class' );
				require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-events-repository.php';
			}
			$events_repo = new ChurchTools_Suite_Events_Repository();
			
			ChurchTools_Suite_Logger::debug( 'ajax_calendar', 'Fetching events from database' );
			
			$raw_events = $events_repo->get_events_in_range(
				$from_date . ' 00:00:00',
				$to_date . ' 23:59:59',
				! empty( $calendar_ids_list ) ? $calendar_ids_list : [],
				$limit
			);
			
			ChurchTools_Suite_Logger::debug( 'ajax_calendar', 'Events fetched', [
				'count' => count( $raw_events ),
			] );
			
			// Formatiere Events für Template mit Template_Data Service
			ChurchTools_Suite_Logger::debug( 'ajax_calendar', 'Formatting events with Template_Data service' );
			
			// Lade Template_Data Service
			if ( ! class_exists( 'ChurchTools_Suite_Template_Data' ) ) {
				require_once CHURCHTOOLS_SUITE_PATH . 'includes/services/class-churchtools-suite-template-data.php';
			}
			$template_data = new ChurchTools_Suite_Template_Data();
			
			$events = [];
			foreach ( $raw_events as $event ) {
				$event_array = (array) $event;
				// Formatiere Event (fügt start_time, start_day, calendar_color, etc. hinzu)
				$formatted_event = $template_data->format_event( $event_array );
				$events[] = $formatted_event;
			}
			
			ChurchTools_Suite_Logger::debug( 'ajax_calendar', 'Events formatted', [
				'count' => count( $events ),
			] );
			
			// Group events by date
			$events_by_date = [];
			foreach ( $events as $event ) {
				$date = date( 'Y-m-d', strtotime( $event['start_datetime'] ) );
				if ( ! isset( $events_by_date[ $date ] ) ) {
					$events_by_date[ $date ] = [];
				}
				$events_by_date[ $date ][] = $event;
			}
			
			// Generate GRID HTML only (not full template!)
			ChurchTools_Suite_Logger::debug( 'ajax_calendar', 'Generating grid HTML' );
			
			ob_start();
			
			// Weekdays
			echo '<div class="cts-weekday">' . esc_html__( 'Mo', 'churchtools-suite' ) . '</div>';
			echo '<div class="cts-weekday">' . esc_html__( 'Di', 'churchtools-suite' ) . '</div>';
			echo '<div class="cts-weekday">' . esc_html__( 'Mi', 'churchtools-suite' ) . '</div>';
			echo '<div class="cts-weekday">' . esc_html__( 'Do', 'churchtools-suite' ) . '</div>';
			echo '<div class="cts-weekday">' . esc_html__( 'Fr', 'churchtools-suite' ) . '</div>';
			echo '<div class="cts-weekday">' . esc_html__( 'Sa', 'churchtools-suite' ) . '</div>';
			echo '<div class="cts-weekday">' . esc_html__( 'So', 'churchtools-suite' ) . '</div>';
			
			// Calculate calendar grid
			$start_weekday = date( 'N', strtotime( $from_date ) );
			$days_in_month = date( 't', strtotime( $from_date ) );
			
			// Empty cells before first day
			for ( $i = 1; $i < $start_weekday; $i++ ) {
				echo '<div class="cts-day cts-day-empty"></div>';
			}
			
			// Days of month
			for ( $day = 1; $day <= $days_in_month; $day++ ) {
				$date = sprintf( '%04d-%02d-%02d', $year, $month, $day );
				$has_events = isset( $events_by_date[ $date ] );
				$is_today = $date === date( 'Y-m-d' );
				
				$classes = [ 'cts-day' ];
				if ( $is_today ) $classes[] = 'cts-day-today';
				if ( $has_events ) $classes[] = 'cts-day-has-events';
				
				echo '<div class="' . esc_attr( implode( ' ', $classes ) ) . '" data-date="' . esc_attr( $date ) . '">';
				echo '<div class="cts-day-number">' . $day . '</div>';
				
				if ( $has_events ) {
					echo '<div class="cts-day-events">';
					foreach ( array_slice( $events_by_date[ $date ], 0, 3 ) as $event ) {
						$color = $event['calendar_color'] ?? '#667eea';
						$title = $event['start_day'] . '. ' . $event['start_month'] . ' ' . $event['start_year'] . ' - ' . $event['title'];
						
						// Event needs data-event-id for modal
						$event_id = $event['id'] ?? '';
						
						echo '<div class="cts-event-dot" style="background-color: ' . esc_attr( $color ) . '" title="' . esc_attr( $title ) . '" data-event-id="' . esc_attr( $event_id ) . '">';
						echo '<span class="cts-event-time">' . esc_html( $event['start_time'] ) . '</span>';
						echo '<span class="cts-event-title-small">' . esc_html( wp_trim_words( $event['title'], 3 ) ) . '</span>';
						echo '</div>';
					}
					if ( count( $events_by_date[ $date ] ) > 3 ) {
						echo '<div class="cts-more-events">+' . ( count( $events_by_date[ $date ] ) - 3 ) . '</div>';
					}
					echo '</div>';
				}
				
				echo '</div>';
			}
			
			$html = ob_get_clean();
			
			ChurchTools_Suite_Logger::debug( 'ajax_calendar', 'Grid HTML generated', [
				'html_length' => strlen( $html ),
			] );
			
			// Generate month name for JavaScript
			$timestamp = mktime( 0, 0, 0, $month, 1, $year );
			$month_name = date_i18n( 'F Y', $timestamp );
			
			wp_send_json_success( [
				'html' => $html,
				'month' => $month,
				'year' => $year,
				'month_name' => $month_name,
			] );
			
		} catch ( Exception $e ) {
			// Cleanup output buffer
			if ( ob_get_level() > 0 ) {
				ob_end_clean();
			}
			
			// Log error
			ChurchTools_Suite_Logger::error( 'ajax_calendar', 'AJAX Calendar Error', [
				'error' => $e->getMessage(),
				'trace' => $e->getTraceAsString(),
			] );
			
			wp_send_json_error( [ 
				'message' => __( 'Fehler beim Laden des Kalenders', 'churchtools-suite' ),
				'error' => WP_DEBUG ? $e->getMessage() : '',
				'trace' => WP_DEBUG ? $e->getTraceAsString() : '',
			] );
		}
	}
	
	/**
	 * Prevent WordPress from redirecting to plugins.php after plugin update
	 * 
	 * WordPress by default redirects to plugins.php after Plugin_Upgrader->install().
	 * We intercept this redirect and send users back to our plugin dashboard instead.
	 * 
	 * @since 0.10.3.4
	 */
	public function handle_update_redirect() {
		$redirect_target = get_transient( 'churchtools_suite_update_redirect' );
		
		if ( ! empty( $redirect_target ) ) {
			// Clear transient
			delete_transient( 'churchtools_suite_update_redirect' );
			
			// Perform redirect
			wp_safe_redirect( $redirect_target );
			exit;
		}
	}
	
	/**
	 * Get available modal templates by scanning filesystem
	 * 
	 * @since 0.9.9.82
	 * @return array List of available template names (e.g., ['professional'])
	 */
	private static function get_available_modal_templates(): array {
		$template_dir = CHURCHTOOLS_SUITE_PATH . 'templates/views/event-modal/';
		
		if ( ! is_dir( $template_dir ) ) {
			return [];
		}
		
		$templates = [];
		$files = scandir( $template_dir );
		
		if ( is_array( $files ) ) {
			foreach ( $files as $file ) {
				// Only PHP files, exclude dotfiles
				if ( substr( $file, -4 ) === '.php' && $file[0] !== '.' ) {
					// Remove .php extension to get template name
					$templates[] = substr( $file, 0, -4 );
				}
			}
		}
		
		sort( $templates ); // Alphabetical order
		return $templates;
	}
	
	/**
	 * Show disclaimer notice in admin dashboard
	 * 
	 * Displays an informational notice about software liability and usage terms
	 * Only shown on ChurchTools Suite admin pages
	 * Can be dismissed by user
	 * 
	 * @since 1.0.4.0
	 */
	/**
	 * Redirect to documentation
	 * 
	 * @since 1.0.3.16
	 */
	public function redirect_to_documentation() {
		wp_redirect( 'https://plugin.feg-aschaffenburg.de/' );
		exit;
	}

	/**
	 * Display Disclaimer admin page
	 * 
	 * @since 1.0.3.16
	 */
	public function display_disclaimer_page() {
		if ( ! current_user_can( 'manage_churchtools_suite' ) ) {
			wp_die( esc_html__( 'Sie haben keine Berechtigung, auf diese Seite zuzugreifen.', 'churchtools-suite' ) );
		}
		
		?>
		<div class="wrap cts-wrap">
			<div class="cts-header">
				<h1>⚠️ <?php esc_html_e( 'Haftungsausschluss', 'churchtools-suite' ); ?></h1>
				<p class="cts-subtitle"><?php esc_html_e( 'Rechtliche Informationen und Disclaimers', 'churchtools-suite' ); ?></p>
			</div>

			<div style="max-width: 900px; margin-top: 2rem;">
				<div style="background: #fff; padding: 2rem; border-radius: 4px; border-left: 4px solid #ff6b6b;">
					<h2><?php esc_html_e( 'Nutzung ohne Gewährleistung', 'churchtools-suite' ); ?></h2>
					<p><?php esc_html_e( 'Dieses Plugin (ChurchTools Suite) wird ohne jegliche Gewährleistung bereitgestellt ("as is"). Die Nutzung erfolgt auf eigenes Risiko.', 'churchtools-suite' ); ?></p>
				</div>

				<div style="background: #fff; padding: 2rem; border-radius: 4px; margin-top: 2rem; border-left: 4px solid #ff6b6b;">
					<h2><?php esc_html_e( 'Keine Haftung für:', 'churchtools-suite' ); ?></h2>
					<ul style="list-style-type: disc; margin-left: 2rem; line-height: 1.8;">
						<li><?php esc_html_e( 'Datenverlust oder Datenschäden', 'churchtools-suite' ); ?></li>
						<li><?php esc_html_e( 'Systemausfälle oder Fehlfunktionen', 'churchtools-suite' ); ?></li>
						<li><?php esc_html_e( 'Fehlerhafte oder unvollständige Darstellung von Inhalten', 'churchtools-suite' ); ?></li>
						<li><?php esc_html_e( 'Sicherheitsprobleme oder unbefugter Zugriff', 'churchtools-suite' ); ?></li>
						<li><?php esc_html_e( 'Inkompatibilität mit anderen Plugins oder Themes', 'churchtools-suite' ); ?></li>
						<li><?php esc_html_e( 'Unterbrechung oder Verzögerung des Service', 'churchtools-suite' ); ?></li>
					</ul>
				</div>

				<div style="background: #fff; padding: 2rem; border-radius: 4px; margin-top: 2rem; border-left: 4px solid #ffa500;">
					<h2><?php esc_html_e( 'Unabhängiges Projekt', 'churchtools-suite' ); ?></h2>
					<p>
						<strong><?php esc_html_e( 'ChurchTools ist eine registrierte Marke der ChurchTools GmbH.', 'churchtools-suite' ); ?></strong><br>
						<?php esc_html_e( 'Dieses Projekt steht in keiner Verbindung zu oder Unterstützung durch die ChurchTools GmbH. Die ChurchTools GmbH übernimmt keine Verantwortung für dieses Projekt oder dessen Verwendung.', 'churchtools-suite' ); ?>
					</p>
				</div>

				<div style="background: #fff; padding: 2rem; border-radius: 4px; margin-top: 2rem; border-left: 4px solid #4CAF50;">
					<h2><?php esc_html_e( 'Ihre Verantwortung', 'churchtools-suite' ); ?></h2>
					<ul style="list-style-type: disc; margin-left: 2rem; line-height: 1.8;">
						<li><?php esc_html_e( 'Führen Sie regelmäßig Backups Ihrer WordPress-Installation durch', 'churchtools-suite' ); ?></li>
						<li><?php esc_html_e( 'Testen Sie Updates in einer Test-Umgebung, bevor Sie diese auf der Live-Site einspielen', 'churchtools-suite' ); ?></li>
						<li><?php esc_html_e( 'Überprüfen Sie die Kompatibilität mit anderen aktiven Plugins', 'churchtools-suite' ); ?></li>
						<li><?php esc_html_e( 'Halten Sie WordPress, PHP und alle Plugins aktuell', 'churchtools-suite' ); ?></li>
						<li><?php esc_html_e( 'Lesen Sie die Dokumentation und Anleitung gründlich', 'churchtools-suite' ); ?></li>
						<li><?php esc_html_e( 'Melden Sie Bugs und Probleme auf GitHub, um zur Verbesserung beizutragen', 'churchtools-suite' ); ?></li>
					</ul>
				</div>

				<div style="background: #f0f0f0; padding: 2rem; border-radius: 4px; margin-top: 2rem;">
					<h3><?php esc_html_e( 'Weitere Informationen', 'churchtools-suite' ); ?></h3>
					<p>
						<strong><?php esc_html_e( 'Lizenz:', 'churchtools-suite' ); ?></strong> 
						<a href="https://www.gnu.org/licenses/gpl-2.0.html" target="_blank">GPL v2 or later</a>
					</p>
					<p>
						<strong><?php esc_html_e( 'GitHub Repository:', 'churchtools-suite' ); ?></strong> 
						<a href="https://github.com/FEGAschaffenburg/churchtools-suite" target="_blank">FEGAschaffenburg/churchtools-suite</a>
					</p>
					<p>
						<strong><?php esc_html_e( 'Dokumentation:', 'churchtools-suite' ); ?></strong> 
						<a href="https://github.com/FEGAschaffenburg/churchtools-suite/blob/main/README.md" target="_blank">README.md auf GitHub</a>
					</p>
					<p>
						<strong><?php esc_html_e( 'Feedback & Bug-Reports:', 'churchtools-suite' ); ?></strong> 
						<a href="https://github.com/FEGAschaffenburg/churchtools-suite/issues" target="_blank">GitHub Issues</a>
					</p>
				</div>

				<div style="margin-top: 3rem; padding-top: 2rem; border-top: 1px solid #ddd; color: #666; font-size: 0.9em;">
					<p><?php esc_html_e( 'Stand:', 'churchtools-suite' ); ?> <?php echo esc_html( date( 'd. F Y', strtotime( '2026-01-14' ) ) ); ?> | 
						<?php esc_html_e( 'Version:', 'churchtools-suite' ); ?> <?php echo esc_html( CHURCHTOOLS_SUITE_VERSION ); ?></p>
				</div>
			</div>
		</div>
		<?php
	}
	
}


