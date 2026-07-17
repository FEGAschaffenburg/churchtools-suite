<?php
/**
 * Plugin Name:       ChurchTools Suite Demo
 * Plugin URI:        https://github.com/FEGAschaffenburg/churchtools-suite-demos
 * Description:       Demo-Addon für ChurchTools Suite - Self-Service Demo Registration mit Backend-Zugang. Erfordert ChurchTools Suite v1.0.8+
 * Version:           1.1.4.1
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Requires Plugins:  churchtools-suite
 * Author:            FEG Aschaffenburg
 * Author URI:        https://feg-aschaffenburg.de
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 *
 * TRADEMARK NOTICE:
 * ChurchTools ist eine registrierte Marke der ChurchTools GmbH.
 * Dieses Projekt steht in keiner Verbindung zu oder Unterstützung durch die ChurchTools GmbH.
 * ChurchTools Suite Demo wird ohne Gewährleistung bereitgestellt (see LICENSE).
 *
 * @package ChurchTools_Suite_Demo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants
define( 'CHURCHTOOLS_SUITE_DEMO_VERSION', '1.1.4.1' );
define( 'CHURCHTOOLS_SUITE_DEMO_PATH', plugin_dir_path( __FILE__ ) );
define( 'CHURCHTOOLS_SUITE_DEMO_URL', plugin_dir_url( __FILE__ ) );

/**
 * Main Plugin Class
 */
class ChurchTools_Suite_Demo {
	
	/**
	 * Singleton instance
	 *
	 * @var ChurchTools_Suite_Demo
	 */
	private static $instance = null;
	
	/**
	 * Demo Users Repository
	 *
	 * @var ChurchTools_Suite_Demo_Users_Repository
	 */
	public $demo_users_repo;
	
	/**
	 * Demo Registration Service
	 *
	 * @var ChurchTools_Suite_Demo_Registration_Service
	 */
	public $registration_service;
	
	/**
	 * Get singleton instance
	 *
	 * @return ChurchTools_Suite_Demo
	 */
	public static function instance(): ChurchTools_Suite_Demo {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * Constructor
	 */
	private function __construct() {
		// Check for plugin updates after init to avoid early translation loading notices
		add_action( 'init', [ $this, 'check_for_updates' ], 5 );
		
		// Initialize plugin
		add_action( 'init', [ $this, 'init' ], 10 );
		
		// Check dependencies after init
		add_action( 'admin_init', [ $this, 'check_dependencies' ] );
		
		// Activation/Deactivation hooks
		register_activation_hook( __FILE__, [ $this, 'activate' ] );
		register_deactivation_hook( __FILE__, [ $this, 'deactivate' ] );
	}
	
	/**
	 * Check if parent plugin is active
	 */
	public function check_dependencies(): void {
		if ( ! class_exists( 'ChurchTools_Suite' ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die(
				'ChurchTools Suite Demo benötigt das ChurchTools Suite Plugin. Bitte installieren und aktivieren Sie es zuerst.',
				'Plugin-Abhängigkeit fehlt',
				[ 'back_link' => true ]
			);
		}
	}
	
	/**
	 * Check for plugin updates and run update routines
	 * 
	 * Runs on every page load but only executes updates when version changes.
	 * Updates existing demo users with new capabilities automatically.
	 * 
	 * @since 1.1.0.5
	 */
	public function check_for_updates(): void {
		$installed_version = get_option( 'churchtools_suite_demo_version', '0.0.0' );
		$current_version = CHURCHTOOLS_SUITE_DEMO_VERSION;
		
		// Version changed - run updates
		if ( version_compare( $installed_version, $current_version, '<' ) ) {
			
			// Load CPT class if not loaded yet
			if ( ! class_exists( 'ChurchTools_Suite_Demo_Template_CPT' ) ) {
				require_once CHURCHTOOLS_SUITE_DEMO_PATH . 'includes/class-demo-template-cpt.php';
			}
			
			// Update capabilities for all demo users
			ChurchTools_Suite_Demo_Template_CPT::add_capabilities();
			
			// Save new version
			update_option( 'churchtools_suite_demo_version', $current_version );
			
			// Log update (only if WP_DEBUG is on)
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( sprintf(
					'ChurchTools Demo: Updated from v%s to v%s - All demo user capabilities refreshed',
					$installed_version,
					$current_version
				) );
			}
		}
	}
	
	/**
	 * Initialize plugin (v1.0.3.1: Create tables on init for robustness)
	 */
	public function init(): void {
		// Ensure database tables exist (robustness: also on init, not just activation)
		$this->create_tables();
		
		// Run migrations (v1.0.6.0: Multi-user support)
		require_once CHURCHTOOLS_SUITE_DEMO_PATH . 'includes/class-demo-migrations.php';
		ChurchTools_Suite_Demo_Migrations::run_migrations();
		
		// Load dependencies
		$this->load_dependencies();
		
		// Initialize repositories
		$this->init_repositories();
		
		// Initialize services
		$this->init_services();
		
		// Register custom user role
		$this->register_demo_role();
		
		// v1.0.7.1: Cleanup orphaned demo data (if demo mode is OFF but data still exists)
		add_action( 'init', [ $this, 'cleanup_orphaned_demo_data' ] );
		
		// Register shortcodes
		$this->register_shortcodes();
		
		// Register Template CPT (Demo only)
		add_action( 'init', [ 'ChurchTools_Suite_Demo_Template_CPT', 'register' ] );
		add_action( 'init', [ 'ChurchTools_Suite_Demo_Template_CPT', 'add_capabilities' ] );
		
		// Cleanup demo pages when user is deleted
		add_action( 'delete_user', [ 'ChurchTools_Suite_Demo_Template_CPT', 'delete_user_demo_pages' ] );
		
		// Enqueue frontend CSS for Demo Pages
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_styles' ] );
		
		// Add info banner to all shortcode outputs
		add_filter( 'the_content', [ $this, 'add_demo_info_banner' ], 999 );
		
		// Register admin pages
		if ( is_admin() ) {
			$this->register_admin();
		}
		
		// Register cron jobs
		$this->register_cron_jobs();
		
		// Register verification handler
		add_action( 'template_redirect', [ $this, 'handle_verification' ] );
		
		// v1.0.7.0: Demo mode settings override
		add_action( 'admin_enqueue_scripts', [ $this, 'inject_demo_mode_ui' ] );
		add_action( 'wp_ajax_cts_demo_toggle_mode', [ $this, 'ajax_toggle_demo_mode' ] );
		
		// v1.0.7.0: Custom menu for demo pages
		add_action( 'init', [ $this, 'register_demo_menu_location' ] );
		add_filter( 'wp_nav_menu_items', [ $this, 'add_demo_pages_to_menu' ], 10, 2 );
		add_action( 'admin_bar_menu', [ $this, 'add_demo_pages_to_admin_bar' ], 100 );
		
		// NOTE: Demo events are stored in database and retrieved like normal ChurchTools events
		// No special filter needed - main plugin handles everything
		
		// Intercept sync operations and simulate them
		add_action( 'wp_ajax_cts_sync_calendars', [ $this, 'simulate_calendar_sync' ], 1 );
		add_action( 'wp_ajax_cts_sync_events', [ $this, 'simulate_event_sync' ], 1 );
		add_action( 'wp_ajax_cts_test_connection', [ $this, 'simulate_connection_test' ], 1 );
		
		// Prevent settings changes in demo mode (but no persistent notice)
		add_action( 'wp_ajax_cts_save_calendar_selection', [ $this, 'prevent_calendar_changes' ], 1 );
		
		// Restrict demo user capabilities (only Demo Pages + ChurchTools dashboard)
		if ( is_admin() ) {
			add_action( 'admin_menu', [ $this, 'restrict_demo_user_capabilities' ], 999 );
		}
	}
	
	/**
	 * Load plugin dependencies
	 */
	private function load_dependencies(): void {
		// WP-CLI Commands (v1.1.1.1)
		require_once CHURCHTOOLS_SUITE_DEMO_PATH . 'includes/class-demo-cli.php';
		
		// Auto-Updater (v1.1.1.0)
		require_once CHURCHTOOLS_SUITE_DEMO_PATH . 'includes/class-demo-auto-updater.php';
		ChurchTools_Suite_Demo_Auto_Updater::init();
		
		// Cron System (v1.1.4.0)
		require_once CHURCHTOOLS_SUITE_DEMO_PATH . 'includes/class-demo-cron.php';
		require_once CHURCHTOOLS_SUITE_DEMO_PATH . 'includes/class-demo-cron-display.php';
		ChurchTools_Suite_Demo_Cron::init();
		
		// Multi-user support (v1.0.6.0)
		require_once CHURCHTOOLS_SUITE_DEMO_PATH . 'includes/class-user-settings.php';
		require_once CHURCHTOOLS_SUITE_DEMO_PATH . 'includes/class-demo-ct-client.php';
		
		// Demo Repositories (v1.0.7.0: Isolated per user)
		require_once CHURCHTOOLS_SUITE_DEMO_PATH . 'includes/repositories/class-demo-users-repository.php';
		require_once CHURCHTOOLS_SUITE_DEMO_PATH . 'includes/repositories/class-demo-presets-repository.php';
		require_once CHURCHTOOLS_SUITE_DEMO_PATH . 'includes/repositories/class-demo-events-repository.php';
		require_once CHURCHTOOLS_SUITE_DEMO_PATH . 'includes/repositories/class-demo-calendars-repository.php';
		require_once CHURCHTOOLS_SUITE_DEMO_PATH . 'includes/repositories/class-demo-services-repository.php';
		
		// CPT for Templates (Demo only)
		require_once CHURCHTOOLS_SUITE_DEMO_PATH . 'includes/class-demo-template-cpt.php';
		
		// Services
		require_once CHURCHTOOLS_SUITE_DEMO_PATH . 'includes/services/class-demo-data-provider.php';
		require_once CHURCHTOOLS_SUITE_DEMO_PATH . 'includes/services/class-demo-registration-service.php';
		
		// Admin
		require_once CHURCHTOOLS_SUITE_DEMO_PATH . 'admin/class-demo-admin.php';
		
		// Shortcodes
		require_once CHURCHTOOLS_SUITE_DEMO_PATH . 'includes/class-demo-shortcodes.php';
		
		// v1.0.7.0: Repository overrides for demo users
		// Register filters globally - they check user role internally
		add_action( 'init', [ $this, 'setup_demo_repository_overrides' ], 1 );
	}
	
	/**
	 * Initialize repositories
	 */
	private function init_repositories(): void {
		$this->demo_users_repo = new ChurchTools_Suite_Demo_Users_Repository();
	}
	
	/**
	 * Initialize services
	 */
	private function init_services(): void {
		$this->registration_service = new ChurchTools_Suite_Demo_Registration_Service( $this->demo_users_repo );
	}
	
	/**
	 * Register custom demo user role (v1.1.0.8: Complete rewrite with editor-like capabilities)
	 * 
	 * Demo User kann:
	 * - ChurchTools Suite Dashboard ANSCHAUEN (READ-ONLY)
	 * - Daten-Seite aufrufen (manage_churchtools_suite capability)
	 * - Eigene Demo-Seiten (CPT) erstellen/bearbeiten/löschen (wie ein Editor)
	 * - KEINE normalen Posts/Pages/Media bearbeiten
	 * 
	 * NEUE STRATEGIE (v1.1.0.8):
	 * Statt minimalistischer Capabilities → Editor-ähnliche Capabilities
	 * aber nur für Demo-Pages. WordPress erwartet bestimmte Meta-Caps.
	 */
	private function register_demo_role(): void {
		// v1.1.0.8: FORCE RE-CREATE role with new capabilities
		// Remove old role first to ensure clean state
		$old_role = get_role( 'cts_demo_user' );
		if ( $old_role ) {
			remove_role( 'cts_demo_user' );
		}
		
		// Create NEW role with editor-like capabilities for demo pages
		// This fixes ALL capability mapping issues
		add_role(
			'cts_demo_user',
			'ChurchTools Demo User',
			[
				// === CORE CAPABILITIES (must have for admin access) ===
				'read' => true,
				
				// === ChurchTools Suite - READ-ONLY Dashboard ===
				'manage_churchtools_suite' => true,
				
				// === Demo Pages - FULL EDITOR CAPABILITIES ===
				// These match what an "Editor" role has, but scoped to demo pages
				'manage_cts_demo_pages' => true,
				'edit_cts_demo_pages' => true,
				'edit_others_cts_demo_pages' => false,  // v1.1.0.8: CRITICAL - can't edit others
				'publish_cts_demo_pages' => true,
				'read_private_cts_demo_pages' => true,
				'delete_cts_demo_pages' => true,
				'delete_private_cts_demo_pages' => true,
				'delete_published_cts_demo_pages' => true,
				'delete_others_cts_demo_pages' => false,
				'edit_private_cts_demo_pages' => true,
				'edit_published_cts_demo_pages' => true,
				
				// Singular forms (auto-mapped by WordPress)
				'edit_cts_demo_page' => true,
				'read_cts_demo_page' => true,
				'delete_cts_demo_page' => true,
				
				// === STANDARD WORDPRESS - NO ACCESS ===
				'edit_posts' => false,
				'edit_others_posts' => false,
				'edit_published_posts' => false,
				'publish_posts' => false,
				'delete_posts' => false,
				'delete_others_posts' => false,
				'delete_published_posts' => false,
				'read_private_posts' => false,
				
				'edit_pages' => false,
				'edit_others_pages' => false,
				'edit_published_pages' => false,
				'publish_pages' => false,
				'delete_pages' => false,
				'delete_others_pages' => false,
				'delete_published_pages' => false,
				'read_private_pages' => false,
				
				'upload_files' => false,
				'manage_categories' => false,
				'moderate_comments' => false,
				'manage_links' => false,
				
				// === ADMIN CAPABILITIES - NO ACCESS ===
				'manage_options' => false,
				'manage_plugins' => false,
				'edit_users' => false,
				'delete_users' => false,
				'unfiltered_html' => false,
			]
		);
		
		// Log role creation
		error_log( '[ChurchTools Demo] Demo user role created/updated with v1.1.0.8 capabilities' );
	}
	
	/**
	 * Restrict admin menu for demo users
	 * 
	 * Demo users should ONLY see:
	 * - Dashboard (read-only)
	 * - Demo Pages (CPT)
	 * - ChurchTools Suite (read-only dashboard)
	 * 
	 * Hide all other menus via capabilities
	 * 
	 * @since 1.0.7.2
	 */
	public function restrict_demo_user_capabilities(): void {
		$user = wp_get_current_user();
		
		// Only apply to demo users
		if ( ! in_array( 'cts_demo_user', (array) $user->roles, true ) ) {
			return;
		}
		
		// Explicitly deny access to prohibited admin pages
		global $pagenow;
		
		// List of restricted pages
		$restricted_pages = [
			'users.php',              // Users
			'edit-comments.php',      // Comments
			'edit.php',               // Posts
			'edit.php?post_type=page', // Pages
			'upload.php',             // Media
			'edit.php?post_type=cpt', // Other CPTs
			'tools.php',              // Tools
			'options-general.php',    // Settings
			'plugins.php',            // Plugins
			'themes.php',             // Themes
			'admin.php',              // Custom admin pages
		];
		
		// Allow ChurchTools Suite admin pages
		$allowed_pages = [ 'index.php', 'post.php', 'edit.php', 'upload.php', 'admin.php' ];
		$is_cts_page = ( $pagenow === 'admin.php' && isset( $_GET['page'] ) && $_GET['page'] === 'churchtools-suite' );
		
		// If user tries to access restricted page, block it (but allow ChurchTools Suite)
		if ( is_admin() && ! in_array( $pagenow, $allowed_pages, true ) && ! $is_cts_page ) {
			// Redirect to dashboard
			wp_redirect( admin_url() );
			exit;
		}
		
		// For admin.php, only allow ChurchTools Suite
		if ( $pagenow === 'admin.php' && ! $is_cts_page ) {
			global $current_screen;
			// Allow Demo Pages CPT
			if ( ! $current_screen || $current_screen->post_type !== 'cts_demo_page' ) {
				wp_redirect( admin_url() );
				exit;
			}
		}
	}

	/**
	 * Register shortcodes
	 */
	private function register_shortcodes(): void {
		$shortcodes = new ChurchTools_Suite_Demo_Shortcodes( $this->registration_service );
		$shortcodes->init();
	}
	
	/**
	 * Register admin pages
	 */
	private function register_admin(): void {
		$admin = new ChurchTools_Suite_Demo_Admin( $this->demo_users_repo );
		$admin->init();
	}
	
	/**
	 * Register cron jobs (v1.1.4.0: Separated demo seeding from cleanup)
	 */
	private function register_cron_jobs(): void {
		// Schedule new cleanup/notification jobs via Cron system
		ChurchTools_Suite_Demo_Cron::schedule_jobs();
		
		// Schedule demo event seeding (separate from cleanup)
		if ( ! wp_next_scheduled( 'cts_demo_seed_events' ) ) {
			wp_schedule_event( time(), 'daily', 'cts_demo_seed_events' );
		}
		add_action( 'cts_demo_seed_events', [ $this, 'seed_demo_events' ] );
	}

	/**
	 * Ensure demo events exist in the future (cron)
	 */
	public function seed_demo_events(): void {
		require_once CHURCHTOOLS_SUITE_DEMO_PATH . 'includes/class-churchtools-suite-demo-activator.php';
		$stats = ChurchTools_Suite_Demo_Activator::ensure_future_events();
		if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
			ChurchTools_Suite_Logger::log( 'demo_seed_events', 'Demo events ensured via cron', $stats );
		}
	}
	
	/**
	 * Handle email verification
	 */
	public function handle_verification(): void {
		if ( isset( $_GET['action'] ) && $_GET['action'] === 'cts_verify_demo_user' && isset( $_GET['token'] ) ) {
			$token = sanitize_text_field( wp_unslash( $_GET['token'] ) );
			
			$result = $this->registration_service->verify_email( $token );
			
			if ( is_wp_error( $result ) ) {
				$error_code = $result->get_error_code();
				
				// Already verified - show friendly message and redirect to login
				if ( $error_code === 'already_verified' ) {
					wp_die(
						'<h1>✅ E-Mail bereits verifiziert</h1>' .
						'<p>Ihr Account wurde bereits aktiviert. Sie können sich jetzt anmelden.</p>' .
						'<p><a href="' . esc_url( wp_login_url() ) . '" class="button button-primary">Zum Login</a></p>',
						'Bereits verifiziert'
					);
				}
				
				// Other errors - show error message
				wp_die(
					$result->get_error_message(),
					'Verifizierung fehlgeschlagen',
					[ 'back_link' => true ]
				);
			}
			
			wp_safe_redirect( admin_url() );
			exit;
		}
	}
	
	/**
	 * Simulate calendar sync (AJAX intercept)
	 */
	public function simulate_calendar_sync(): void {
		check_ajax_referer( 'churchtools_suite_admin', 'nonce' );
		
		if ( ! current_user_can( 'manage_churchtools_suite' ) ) {
			wp_send_json_error( [ 'message' => 'Keine Berechtigung' ] );
		}
		
		// Simulate successful sync
		wp_send_json_success( [
			'message' => 'Demo-Modus: Kalender-Synchronisation simuliert',
			'calendars_found' => 6,
			'calendars_created' => 0,
			'calendars_updated' => 0,
		] );
	}
	
	/**
	 * Simulate event sync (AJAX intercept)
	 */
	public function simulate_event_sync(): void {
		check_ajax_referer( 'churchtools_suite_admin', 'nonce' );
		
		if ( ! current_user_can( 'manage_churchtools_suite' ) ) {
			wp_send_json_error( [ 'message' => 'Keine Berechtigung' ] );
		}
		
		// Seed demo data on simulated sync
		require_once CHURCHTOOLS_SUITE_DEMO_PATH . 'includes/class-churchtools-suite-demo-activator.php';
		$seed_stats = ChurchTools_Suite_Demo_Activator::seed_demo_data_for_sync();
		
		// Count existing demo events
		global $wpdb;
		$table = $wpdb->prefix . 'cts_events';
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE calendar_id IN ('1','2','3','4','5','6')" );
		
		// Simulate successful sync
		wp_send_json_success( [
			'message' => 'Demo-Modus: Event-Synchronisation simuliert',
			'calendars_processed' => 6,
			'events_found' => (int) $count,
			'events_inserted' => $seed_stats['created'] ?? 0,
			'events_updated' => $seed_stats['updated'] ?? 0,
			'events_skipped' => 0,
			'services_imported' => 0,
		] );
	}
	
	/**
	 * Simulate connection test (AJAX intercept)
	 */
	public function simulate_connection_test(): void {
		check_ajax_referer( 'churchtools_suite_admin', 'nonce' );
		
		if ( ! current_user_can( 'manage_churchtools_suite' ) ) {
			wp_send_json_error( [ 'message' => 'Keine Berechtigung' ] );
		}
		
		// Simulate successful connection
		wp_send_json_success( [
			'message' => 'Demo-Modus: Verbindung simuliert (keine echte API-Verbindung)',
		] );
	}
	
	/**
	 * Prevent calendar selection changes in demo mode
	 */
	public function prevent_calendar_changes(): void {
		check_ajax_referer( 'churchtools_suite_admin', 'nonce' );
		
		if ( ! current_user_can( 'manage_churchtools_calendars' ) ) {
			wp_send_json_error( [ 'message' => 'Keine Berechtigung' ] );
		}
		
		// Prevent changes in demo mode
		wp_send_json_error( [
			'message' => 'Demo-Modus: Kalenderauswahl kann nicht geändert werden',
		] );
	}
	
	/**
	 * Plugin activation (v1.1.4.0: Added cron scheduling)
	 */
	public function activate(): void {
		// Create database tables
		$this->create_tables();
		
		// Register demo role
		$this->register_demo_role();
		
		// Create default demo user if it doesn't exist
		$this->create_demo_user();
		
		// Schedule cron jobs
		require_once CHURCHTOOLS_SUITE_DEMO_PATH . 'includes/class-demo-cron.php';
		ChurchTools_Suite_Demo_Cron::schedule_jobs();
		
		// Flush rewrite rules
		flush_rewrite_rules();
	}
	
	/**
	 * Plugin deactivation (v1.1.4.0: Added new cron cleanup)
	 */
	public function deactivate(): void {
		// Clear new cron jobs
		require_once CHURCHTOOLS_SUITE_DEMO_PATH . 'includes/class-demo-cron.php';
		ChurchTools_Suite_Demo_Cron::clear_jobs();
		
		// Clear old demo seeding job
		$timestamp = wp_next_scheduled( 'cts_demo_seed_events' );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'cts_demo_seed_events' );
		}
		
		// Flush rewrite rules
		flush_rewrite_rules();
	}
	
	/**
	 * Create default demo user if it doesn't exist
	 * 
	 * This user has cts_manager role to access the plugin backend
	 * 
	 * @since 1.0.2
	 */
	private function create_demo_user(): void {
		// Check if demo-user already exists
		$demo_user = get_user_by( 'login', 'demo-manager' );
		
		if ( $demo_user ) {
			return; // Demo user already exists
		}
		
		// Create new demo user with strong password
		$password = wp_generate_password( 16, true );
		
		$user_id = wp_create_user(
			'demo-manager',
			$password,
			'demo@example.com'
		);
		
		if ( is_wp_error( $user_id ) ) {
			// Log error if logging available
			if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
				ChurchTools_Suite_Logger::log(
					'demo_plugin',
					'Failed to create demo user',
					[ 'error' => $user_id->get_error_message() ]
				);
			}
			return;
		}
		
		// Assign cts_demo_user role (v1.0.7.3: Deprecated cts_manager, use cts_demo_user instead)
		$user = new WP_User( $user_id );
		if ( method_exists( $user, 'set_role' ) ) {
			$user->set_role( 'cts_demo_user' );
		}
		
		// Log success
		if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
			ChurchTools_Suite_Logger::log(
				'demo_plugin',
				'Demo user created on activation',
				[
					'user_id' => $user_id,
					'username' => 'demo-manager',
					'role' => 'cts_manager',
				]
			);
		}
	}
	
	/**
	 * Create database tables (v1.0.3.1: Fixed table name prefix and added columns)
	 */
	private function create_tables(): void {
		global $wpdb;
		
		$charset_collate = $wpdb->get_charset_collate();
		$table_name = $wpdb->prefix . 'cts_demo_users'; // wp_cts_demo_users
		
		// Migration: Rename old table if exists (v1.0.5.1)
		$old_table = $wpdb->prefix . 'demo_users';
		$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$old_table}'" );
		if ( $table_exists ) {
			$wpdb->query( "RENAME TABLE {$old_table} TO {$table_name}" );
			
			if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
				ChurchTools_Suite_Logger::log(
					'demo_plugin',
					'Migrated old table name',
					[ 'from' => $old_table, 'to' => $table_name ]
				);
			}
		}
		
		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			email varchar(255) NOT NULL,
			name varchar(255) NOT NULL,
			organization varchar(255) DEFAULT NULL,
			purpose text DEFAULT NULL,
			verification_token varchar(64) NOT NULL,
			is_verified tinyint(1) DEFAULT 0,
			verified_at datetime DEFAULT NULL,
			wordpress_user_id bigint(20) unsigned DEFAULT NULL,
			last_login_at datetime DEFAULT NULL,
			expires_at datetime DEFAULT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY email (email),
			UNIQUE KEY verification_token (verification_token),
			KEY is_verified (is_verified),
			KEY verified_at (verified_at),
			KEY created_at (created_at),
			KEY wordpress_user_id (wordpress_user_id)
		) $charset_collate;";
		
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
		
		// Log table creation
		if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
			ChurchTools_Suite_Logger::log(
				'demo_plugin',
				'Demo tables created/verified',
				[ 'table' => $table_name ]
			);
		}
	}
	
	/**
	 * Enqueue frontend CSS for Demo Pages
	 * 
	 * Adds professional styling for single demo page display on frontend
	 * 
	 * @since 1.0.7.4
	 */
	public function enqueue_frontend_styles(): void {
		// Only enqueue on single demo page posts
		if ( ! is_singular( 'cts_demo_page' ) ) {
			return;
		}
		
		// Register and enqueue demo pages frontend CSS
		wp_enqueue_style(
			'cts-demo-pages-frontend',
			CHURCHTOOLS_SUITE_DEMO_URL . 'assets/css/demo-pages-frontend.css',
			[],
			CHURCHTOOLS_SUITE_DEMO_VERSION,
			'all'
		);
	}
	
	/**
	 * Add demo info banner above content on view pages only
	 * 
	 * Shows a helpful banner on specific demo view pages only,
	 * to guide users on creating their own demo pages.
	 * 
	 * @param string $content Page content
	 * @return string Modified content
	 */
	public function add_demo_info_banner( string $content ): string {
		// Only on frontend, not in admin
		if ( is_admin() ) {
			return $content;
		}
		
		// Only on view pages
		global $post;
		if ( ! $post ) {
			return $content;
		}
		
		// Check if page title starts with view-specific prefixes
		$view_prefixes = [
			'Listen:',
			'Karten:',
			'Kalender:',
			'Grid:',
			'Slider:',
			'Countdown:',
		];
		
		$is_view_page = false;
		foreach ( $view_prefixes as $prefix ) {
			if ( strpos( $post->post_title, $prefix ) === 0 ) {
				$is_view_page = true;
				break;
			}
		}
		
		if ( ! $is_view_page ) {
			return $content;
		}
		
		// Additional safety check: only if shortcode present
		if ( ! has_shortcode( $content, 'cts_list' ) && 
		     ! has_shortcode( $content, 'cts_grid' ) && 
		     ! has_shortcode( $content, 'cts_calendar' ) &&
		     ! has_shortcode( $content, 'cts_countdown' ) &&
		     ! has_shortcode( $content, 'cts_slider' ) ) {
			return $content;
		}
		
		// Banner HTML
		$banner = '
		<div class="cts-demo-info-banner" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1.5rem; border-radius: 8px; margin: 0 0 2rem 0; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
			<div style="display: flex; align-items: center; gap: 1rem;">
				<span style="font-size: 2rem; flex-shrink: 0;">💡</span>
				<div>
					<h3 style="margin: 0 0 0.5rem 0; font-size: 1.1rem; color: white; font-weight: 600;">Eigene Demo-Seiten erstellen</h3>
					<p style="margin: 0; font-size: 0.95rem; line-height: 1.5; opacity: 0.95;">
						Sie können im Backend eigene Seiten mit individuellen Einstellungen erstellen!<br>
						<strong style="color: #fbbf24;">→ Demo Pages > Neue Seite hinzufügen</strong><br>
						Verwenden Sie den Gutenberg-Block <strong>"ChurchTools Events"</strong> oder Shortcodes wie <code style="background: rgba(255,255,255,0.2); padding: 0.2rem 0.4rem; border-radius: 3px; font-size: 0.9rem;">[cts_list view="minimal"]</code>
					</p>
				</div>
			</div>
		</div>';
		
		return $banner . $content;
	}
	
	/**
	 * Inject demo mode UI into Settings tab (v1.0.7.0)
	 */
	public function inject_demo_mode_ui( $hook ): void {
		// Only on ChurchTools Suite admin pages
		if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'churchtools-suite' ) {
			return;
		}
		
		// Only on Settings tab
		if ( ! isset( $_GET['tab'] ) || $_GET['tab'] !== 'settings' ) {
			return;
		}
		
		// Only on API subtab
		if ( ! isset( $_GET['subtab'] ) || $_GET['subtab'] !== 'api' ) {
			return;
		}
		
		// Only for demo users
		if ( ! current_user_can( 'cts_demo_user' ) ) {
			return;
		}
		
		// Buffer output and inject demo mode UI
		ob_start( function( $content ) {
			// Find the API card and inject demo mode toggle before it
			$demo_ui = file_get_contents( CHURCHTOOLS_SUITE_DEMO_PATH . 'admin/views/settings-override-api.php' );
			$demo_ui = eval( '?>' . $demo_ui );
			
			// Inject before first form
			$content = preg_replace( 
				'/(<form[^>]*method="post"[^>]*>)/',
				ob_get_clean() . '$1',
				$content,
				1
			);
			
			return $content;
		} );
	}
	
	/**
	 * Handle demo mode toggle (v1.0.7.1: Delete demo data when disabled, import when enabled)
	 */
	public function ajax_toggle_demo_mode(): void {
		check_ajax_referer( 'cts_demo_toggle', 'nonce' );
		
		if ( ! current_user_can( 'cts_demo_user' ) ) {
			wp_send_json_error( [ 'message' => 'Keine Berechtigung' ] );
		}
		
		$enabled = ! empty( $_POST['enabled'] );
		$user_id = get_current_user_id();
		
		// Load user settings class
		require_once CHURCHTOOLS_SUITE_DEMO_PATH . 'includes/class-user-settings.php';
		
		if ( $enabled ) {
			// v1.0.7.1: Import demo data when activating
			$this->import_demo_data_for_user( $user_id );
			
			// Toggle mode to ON
			ChurchTools_Suite_User_Settings::set_demo_mode( true, $user_id );
			
			// Clear cleanup transient (allow future cleanup if disabled again)
			delete_transient( "cts_demo_cleanup_done_{$user_id}" );
			
			$message = 'Demo-Modus aktiviert - Demo-Daten wurden importiert';
		} else {
			// v1.0.7.1: Delete demo data when disabling
			$this->delete_user_demo_data( $user_id );
			
			// Toggle mode to OFF
			ChurchTools_Suite_User_Settings::set_demo_mode( false, $user_id );
			
			$message = 'Demo-Modus deaktiviert - Alle Demo-Daten wurden gelöscht';
		}
		
		// Log
		error_log( sprintf(
			'[ChurchTools Demo] User %d toggled demo mode: %s',
			$user_id,
			$enabled ? 'enabled' : 'disabled'
		) );
		
		wp_send_json_success( [ 'message' => $message ] );
	}
	
	/**
	 * Import demo data for user (v1.0.7.1)
	 * 
	 * Seeds database with hardcoded demo data for immediate testing.
	 * Much faster than API import, uses Demo Data Provider's built-in data.
	 *
	 * @param int $user_id WordPress user ID
	 */
	private function import_demo_data_for_user( int $user_id ): void {
		error_log( "[ChurchTools Demo] Seeding demo data for user {$user_id}" );
		
		// Load Demo Data Provider (has hardcoded demo data)
		require_once CHURCHTOOLS_SUITE_DEMO_PATH . 'includes/services/class-demo-data-provider.php';
		$provider = new ChurchTools_Suite_Demo_Data_Provider();
		
		// Load Demo Repositories
		require_once CHURCHTOOLS_SUITE_DEMO_PATH . 'includes/repositories/class-demo-calendars-repository.php';
		require_once CHURCHTOOLS_SUITE_DEMO_PATH . 'includes/repositories/class-demo-events-repository.php';
		
		$calendars_repo = new ChurchTools_Suite_Demo_Calendars_Repository( $user_id );
		$events_repo = new ChurchTools_Suite_Demo_Events_Repository( $user_id );
		
		// Import calendars (hardcoded from Demo Data Provider)
		$demo_calendars = $provider->get_demo_calendars_raw();
		$cal_count = 0;
		foreach ( $demo_calendars as $calendar ) {
			if ( $calendars_repo->upsert( $calendar ) ) {
				$cal_count++;
			}
		}
		
		// Import events (generate from now + 90 days)
		$from = current_time( 'Y-m-d' );
		$to = date( 'Y-m-d', current_time( 'timestamp' ) + 90 * DAY_IN_SECONDS );
		
		// Use public get_events() method to load demo data
		$demo_events = $provider->get_events( [
			'from' => $from,
			'to' => $to,
			'limit' => 100,
			'calendar_ids' => [], // All calendars
		] );
		
		$event_count = 0;
		foreach ( $demo_events as $event ) {
			// Add required fields for repository
			$event_data = [
				'event_id' => $event['id'] ?? null,
				'calendar_id' => $event['calendar_id'] ?? '1',
				'appointment_id' => $event['appointment_id'] ?? $event['id'],
				'title' => $event['title'],
				'event_description' => $event['description'] ?? '',
				'start_datetime' => $event['start_datetime'],
				'end_datetime' => $event['end_datetime'] ?? null,
				'location_name' => $event['location'] ?? '',
				'raw_payload' => wp_json_encode( $event ),
			];
			
			if ( $events_repo->upsert( $event_data ) ) {
				$event_count++;
			}
		}
		
		error_log( sprintf(
			'[ChurchTools Demo] Seeded %d calendars and %d events for user %d',
			$cal_count,
			$event_count,
			$user_id
		) );
	}
	
	/**
	 * Delete all demo data for a specific user (v1.0.7.1)
	 * 
	 * Multi-User safe: Only deletes data for the specified user_id
	 * Uses DEMO tables (demo_cts_*) not main plugin tables!
	 *
	 * @param int $user_id WordPress user ID
	 */
	private function delete_user_demo_data( int $user_id ): void {
		global $wpdb;
		
		// Get DEMO table names (demo_cts_* not cts_*)
		$events_table = $wpdb->prefix . 'demo_cts_events';
		$calendars_table = $wpdb->prefix . 'demo_cts_calendars';
		$services_table = $wpdb->prefix . 'demo_cts_services';
		
		// Count before deletion (for logging)
		$events_count = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$events_table} WHERE user_id = %d",
			$user_id
		) );
		
		$calendars_count = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$calendars_table} WHERE user_id = %d",
			$user_id
		) );
		
		$services_count = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$services_table} WHERE user_id = %d",
			$user_id
		) );
		
		// Delete all events for this user from DEMO table
		$wpdb->query( $wpdb->prepare(
			"DELETE FROM {$events_table} WHERE user_id = %d",
			$user_id
		) );
		
		// Delete all calendars for this user from DEMO table
		$wpdb->query( $wpdb->prepare(
			"DELETE FROM {$calendars_table} WHERE user_id = %d",
			$user_id
		) );
		
		// Delete all services for this user from DEMO table
		$wpdb->query( $wpdb->prepare(
			"DELETE FROM {$services_table} WHERE user_id = %d",
			$user_id
		) );
		
		// Clear any cached counts
		wp_cache_delete( "cts_events_count_{$user_id}" );
		wp_cache_delete( "cts_calendars_count_{$user_id}" );
		
		// Log deletion
		error_log( sprintf(
			'[ChurchTools Demo] Deleted demo data for user %d: %d events, %d calendars, %d services',
			$user_id,
			$events_count,
			$calendars_count,
			$services_count
		) );
	}
	
	/**
	 * Cleanup orphaned demo data (v1.0.7.1)
	 * 
	 * Runs ONCE per user - deletes demo data if demo mode is OFF.
	 * Prevents old demo data from showing when user disables demo mode.
	 */
	public function cleanup_orphaned_demo_data(): void {
		// Only for logged-in demo users
		if ( ! is_user_logged_in() || ! current_user_can( 'cts_demo_user' ) ) {
			return;
		}
		
		$user_id = get_current_user_id();
		
		// Check if cleanup already done (transient prevents repeated checks)
		$cleanup_done = get_transient( "cts_demo_cleanup_done_{$user_id}" );
		if ( $cleanup_done ) {
			return; // Already cleaned up, skip
		}
		
		// Load user settings
		require_once CHURCHTOOLS_SUITE_DEMO_PATH . 'includes/class-user-settings.php';
		
		// Check if demo mode is OFF
		$demo_mode = ChurchTools_Suite_User_Settings::is_demo_mode( $user_id );
		
		if ( ! $demo_mode ) {
			// Demo mode is OFF - delete orphaned data
			$this->delete_user_demo_data( $user_id );
			
			// Mark cleanup as done (expires in 1 hour - allows re-import if user re-enables)
			set_transient( "cts_demo_cleanup_done_{$user_id}", true, HOUR_IN_SECONDS );
		}
	}
	
	/**
	 * Show demo mode banner in Settings > API tab
	 * 
	 * @since 1.0.7.1
	 */
	public function show_demo_mode_banner(): void {
		// Only for demo users
		if ( ! current_user_can( 'cts_demo_user' ) ) {
			return;
		}
		
		// Only on ChurchTools Suite settings page
		$screen = get_current_screen();
		if ( ! $screen || strpos( $screen->id, 'churchtools-suite' ) === false ) {
			return;
		}
		
		// Check if on API subtab
		$tab = $_GET['tab'] ?? '';
		$subtab = $_GET['subtab'] ?? '';
		
		if ( $tab !== 'settings' || $subtab !== 'api' ) {
			return;
		}
		
		// Check demo mode status
		require_once CHURCHTOOLS_SUITE_DEMO_PATH . 'includes/class-user-settings.php';
		$demo_mode = ChurchTools_Suite_User_Settings::is_demo_mode();
		
		?>
		<div class="notice notice-info" style="padding: 16px; border-left: 4px solid #0073aa;">
			<div style="display: flex; align-items: start; gap: 12px;">
				<span style="font-size: 24px;">ℹ️</span>
				<div style="flex: 1;">
					<?php if ( $demo_mode ) : ?>
						<h3 style="margin: 0 0 8px 0;">Demo-Modus aktiv</h3>
						<p style="margin: 0 0 12px 0;">
							Sie nutzen aktuell vorkonfigurierte <strong>Demo-Daten</strong>. 
							Um Ihre eigene ChurchTools-Instanz zu testen, deaktivieren Sie den Demo-Modus.
						</p>
						<button type="button" id="cts-demo-exit-btn" class="button button-primary">
							🚀 Demo-Modus beenden & eigene Daten nutzen
						</button>
					<?php else : ?>
						<h3 style="margin: 0 0 8px 0;">Eigene ChurchTools-Instanz</h3>
						<p style="margin: 0 0 12px 0;">
							Sie können jetzt Ihre <strong>eigenen ChurchTools-Zugangsdaten</strong> eingeben und testen.
							Zum Zurückwechseln zu Demo-Daten klicken Sie unten.
						</p>
						<button type="button" id="cts-demo-reenter-btn" class="button">
							🔙 Zurück zu Demo-Daten
						</button>
					<?php endif; ?>
					<span id="cts-demo-toggle-result" style="display: none; margin-left: 12px;"></span>
				</div>
			</div>
		</div>
		
		<script>
		jQuery(document).ready(function($) {
			const exitBtn = $('#cts-demo-exit-btn');
			const reenterBtn = $('#cts-demo-reenter-btn');
			const result = $('#cts-demo-toggle-result');
			
			function toggleDemoMode(enabled) {
				const btn = enabled ? reenterBtn : exitBtn;
				btn.prop('disabled', true).text('⏳ Bitte warten...');
				
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
							result.text('✅ ' + response.data.message).css('color', '#46b450').show();
							setTimeout(function() {
								location.reload();
							}, 1500);
						} else {
							result.text('❌ ' + response.data.message).css('color', '#dc3232').show();
							btn.prop('disabled', false).text(enabled ? '🔙 Zurück zu Demo-Daten' : '🚀 Demo-Modus beenden');
						}
					},
					error: function() {
						result.text('❌ Fehler beim Umschalten').css('color', '#dc3232').show();
						btn.prop('disabled', false).text(enabled ? '🔙 Zurück zu Demo-Daten' : '🚀 Demo-Modus beenden');
					}
				});
			}
			
			exitBtn.on('click', function() { toggleDemoMode(false); });
			reenterBtn.on('click', function() { toggleDemoMode(true); });
		});
		</script>
		<?php
	}
	
	/**
	 * Register custom menu location for demo pages (v1.0.7.0)
	 */
	public function register_demo_menu_location(): void {
		register_nav_menus( [
			'demo-pages' => __( 'Demo Pages Menu (automatisch)', 'churchtools-suite' ),
		] );
	}
	
	/**
	 * Add user's demo pages to menu (v1.0.7.0)
	 * 
	 * Automatically adds the logged-in user's demo pages to any menu
	 * assigned to the 'demo-pages' location.
	 *
	 * @param string $items Menu HTML
	 * @param object $args Menu args
	 * @return string Modified menu HTML
	 */
	public function add_demo_pages_to_menu( string $items, $args ): string {
		// Only for demo-pages menu location
		if ( ! isset( $args->theme_location ) || $args->theme_location !== 'demo-pages' ) {
			return $items;
		}
		
		// Only for logged-in demo users
		if ( ! is_user_logged_in() || ! current_user_can( 'cts_demo_user' ) ) {
			return $items;
		}
		
		// Get user's demo pages
		$demo_pages = get_posts( [
			'post_type' => 'cts_demo_page',
			'author' => get_current_user_id(),
			'posts_per_page' => -1,
			'orderby' => 'title',
			'order' => 'ASC',
			'post_status' => 'publish',
		] );
		
		if ( empty( $demo_pages ) ) {
			// No pages yet - show hint
			$items .= '<li class="menu-item no-demo-pages">';
			$items .= '<a href="' . admin_url( 'post-new.php?post_type=cts_demo_page' ) . '">';
			$items .= '➕ ' . __( 'Erste Demo-Seite erstellen', 'churchtools-suite' );
			$items .= '</a></li>';
			return $items;
		}
		
		// Add pages to menu
		foreach ( $demo_pages as $page ) {
			$items .= sprintf(
				'<li class="menu-item menu-item-demo-page"><a href="%s">📄 %s</a></li>',
				get_permalink( $page->ID ),
				esc_html( $page->post_title )
			);
		}
		
		// Add "Create new" link
		$items .= '<li class="menu-item menu-item-create-demo">';
		$items .= '<a href="' . admin_url( 'post-new.php?post_type=cts_demo_page' ) . '">';
		$items .= '➕ ' . __( 'Neue Seite', 'churchtools-suite' );
		$items .= '</a></li>';
		
		return $items;
	}
	
	/**
	 * Add demo pages to admin bar (v1.0.7.0)
	 *
	 * @param WP_Admin_Bar $wp_admin_bar
	 */
	public function add_demo_pages_to_admin_bar( $wp_admin_bar ): void {
		// Only for logged-in demo users
		if ( ! is_user_logged_in() || ! current_user_can( 'cts_demo_user' ) ) {
			return;
		}
		
		// Get user's demo pages
		$demo_pages = get_posts( [
			'post_type' => 'cts_demo_page',
			'author' => get_current_user_id(),
			'posts_per_page' => 10,
			'orderby' => 'modified',
			'order' => 'DESC',
			'post_status' => 'publish',
		] );
		
		// Add parent menu
		$wp_admin_bar->add_node( [
			'id' => 'demo-pages',
			'title' => '📄 Meine Demo-Seiten (' . count( $demo_pages ) . ')',
			'href' => admin_url( 'edit.php?post_type=cts_demo_page' ),
		] );
		
		// Add pages as submenu
		if ( ! empty( $demo_pages ) ) {
			foreach ( $demo_pages as $page ) {
				$wp_admin_bar->add_node( [
					'parent' => 'demo-pages',
					'id' => 'demo-page-' . $page->ID,
					'title' => esc_html( $page->post_title ),
					'href' => get_permalink( $page->ID ),
				] );
			}
			
			// Separator
			$wp_admin_bar->add_node( [
				'parent' => 'demo-pages',
				'id' => 'demo-pages-divider',
				'title' => '—',
				'meta' => [ 'class' => 'ab-sub-secondary' ],
			] );
		}
		
		// Add "Create new" link
		$wp_admin_bar->add_node( [
			'parent' => 'demo-pages',
			'id' => 'demo-pages-new',
			'title' => '➕ Neue Seite erstellen',
			'href' => admin_url( 'post-new.php?post_type=cts_demo_page' ),
		] );
		
		// Add "Manage all" link
		$wp_admin_bar->add_node( [
			'parent' => 'demo-pages',
			'id' => 'demo-pages-manage',
			'title' => '⚙️ Alle verwalten',
			'href' => admin_url( 'edit.php?post_type=cts_demo_page' ),
		] );
	}
	
	/**
	 * Setup demo repository overrides (v1.0.7.0)
	 * 
	 * Temporarily replaces main plugin repository classes with demo versions
	 * for current demo user. This allows shortcodes to automatically use isolated
	 * demo data without modifying main plugin code.
	 * 
	 * Uses PHP class_alias to redirect class names.
	 */
	/**
	 * Setup demo repository overrides (v1.0.7.0)
	 * 
	 * Registers filter hooks to override main plugin repositories with isolated demo versions.
	 * Uses Repository Factory pattern from main plugin v1.0.8.0+.
	 * 
	 * Filters are registered globally but only activate for demo users (checked in override methods).
	 */
	public function setup_demo_repository_overrides(): void {
		// Check if main plugin has Repository Factory (v1.0.8.0+)
		if ( ! function_exists( 'churchtools_suite_get_repository' ) ) {
			// Fallback: Main plugin not updated yet
			error_log( '[ChurchTools Demo] WARNING: Main plugin needs v1.0.8.0+ for Repository Factory' );
			return;
		}
		
		// Register filters to override repositories
		// Note: Filters check user role internally before returning demo repositories
		add_filter( 'churchtools_suite_get_events_repository', [ $this, 'override_events_repository' ], 10, 2 );
		add_filter( 'churchtools_suite_get_calendars_repository', [ $this, 'override_calendars_repository' ], 10, 2 );
		add_filter( 'churchtools_suite_get_services_repository', [ $this, 'override_services_repository' ], 10, 2 );
		add_filter( 'churchtools_suite_get_event_services_repository', [ $this, 'override_event_services_repository' ], 10, 2 );
	}
	
	/**
	 * Override Events Repository for demo users (v1.0.7.0)
	 * 
	 * Returns isolated demo repository when user is demo user.
	 * This ensures demo users only see their own events.
	 *
	 * @param mixed $repository Default repository (ignored)
	 * @param int|null $user_id User ID (optional)
	 * @return mixed Demo repository or original
	 */
	public function override_events_repository( $repository, $user_id = null ) {
		// Get current user if not specified
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}
		
		// Check if user is demo user
		if ( $user_id && user_can( $user_id, 'cts_demo_user' ) ) {
			return new ChurchTools_Suite_Demo_Events_Repository( $user_id );
		}
		
		return $repository;
	}
	
	/**
	 * Override Calendars Repository for demo users (v1.0.7.0)
	 *
	 * @param mixed $repository Default repository (ignored)
	 * @param int|null $user_id User ID (optional)
	 * @return mixed Demo repository or original
	 */
	public function override_calendars_repository( $repository, $user_id = null ) {
		// Get current user if not specified
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}
		
		// Check if user is demo user
		if ( $user_id && user_can( $user_id, 'cts_demo_user' ) ) {
			return new ChurchTools_Suite_Demo_Calendars_Repository( $user_id );
		}
		
		return $repository;
	}
	
	/**
	 * Override Services Repository for demo users (v1.0.7.0)
	 *
	 * @param mixed $repository Default repository (ignored)
	 * @param int|null $user_id User ID (optional)
	 * @return mixed Demo repository or original
	 */
	public function override_services_repository( $repository, $user_id = null ) {
		// Get current user if not specified
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}
		
		// Check if user is demo user
		if ( $user_id && user_can( $user_id, 'cts_demo_user' ) ) {
			return new ChurchTools_Suite_Demo_Services_Repository( $user_id );
		}
		
		return $repository;
	}
	
	/**
	 * Override Event Services Repository for demo users (v1.0.7.0)
	 * 
	 * Note: Demo plugin doesn't have separate event_services table yet.
	 * Returns null to use main plugin's repository (services are part of events).
	 *
	 * @param mixed $repository Default repository (ignored)
	 * @param int|null $user_id User ID (optional)
	 * @return mixed Demo repository or original
	 */
	public function override_event_services_repository( $repository, $user_id = null ) {
		// Demo plugin doesn't have event_services table isolation yet
		// Services are stored within events, so this is handled by events repository
		return $repository;
	}
}

// Initialize plugin
function churchtools_suite_demo() {
	return ChurchTools_Suite_Demo::instance();
}

// Start plugin on init to avoid early translation loading
add_action( 'init', 'churchtools_suite_demo', 1 );

// Plugin activation/deactivation hooks (v1.0.4.0: Added demo event persistence)
register_activation_hook( __FILE__, function() {
	// Load Activator for demo events
	require_once CHURCHTOOLS_SUITE_DEMO_PATH . 'includes/class-churchtools-suite-demo-activator.php';
	
	// Run demo activator (creates demo events in database)
	ChurchTools_Suite_Demo_Activator::activate();
	
	// Run plugin activator (creates tables, users)
	churchtools_suite_demo()->activate();
} );

register_deactivation_hook( __FILE__, function() {
	// Load Activator for demo events
	require_once CHURCHTOOLS_SUITE_DEMO_PATH . 'includes/class-churchtools-suite-demo-activator.php';
	
	// Run demo deactivator (optional cleanup)
	ChurchTools_Suite_Demo_Activator::deactivate();
	
	// Run plugin deactivator (cleanup)
	churchtools_suite_demo()->deactivate();
} );
