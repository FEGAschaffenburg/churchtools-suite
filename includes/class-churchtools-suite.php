<?php
/**
 * Main Plugin Class
 *
 * @package ChurchTools_Suite
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ChurchTools_Suite {
	
	/**
	 * Loader instance
	 */
	protected ChurchTools_Suite_Loader $loader;
	
	/**
	 * Plugin version
	 */
	protected string $version;
	
	/**
	 * Initialize the plugin
	 */
	public function __construct() {
		$this->version = CHURCHTOOLS_SUITE_VERSION;
		$this->load_dependencies();
		$this->init_logger(); // v0.9.2.3: Initialize logging system
		// Initialize update checker (registers update transient hook)
		if ( class_exists( 'ChurchTools_Suite_Update_Checker' ) ) {
			ChurchTools_Suite_Update_Checker::init();
		}
		$this->run_migrations();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_cron_hooks();
	}
	
	/**
	 * Load required dependencies
	 */
	private function load_dependencies(): void {
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-loader.php';
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-logger.php';
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-migrations.php';
		require_once CHURCHTOOLS_SUITE_PATH . 'admin/class-churchtools-suite-admin.php';
		
		// Repository base class
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-repository-base.php';
		
		// Repositories (needed in admin and frontend)
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-calendars-repository.php';
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-events-repository.php';
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-event-services-repository.php';
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-services-repository.php';
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-service-groups-repository.php';
		
		// Frontend components (v0.4.0.0+)
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-template-loader.php';
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-shortcodes.php';
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/services/class-churchtools-suite-template-data.php';
		
		// Single Event Shortcode (v0.7.1.0)
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/shortcodes/class-churchtools-suite-single-event-shortcode.php';
		
		// Single Event Handler (v0.9.3.1)
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-single-event-handler.php';
		
		// Gutenberg Blocks (v0.5.8.0+)
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-blocks.php';
		
		// Elementor Widget (v1.0.3.18+)
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/elementor/class-churchtools-suite-elementor-events-widget.php';

		// Auto updater (checks GitHub releases and installs ZIP)
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-auto-updater.php';
		// Update checker (injects GitHub release into WP update transient)
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-update-checker.php';
		// Cron display helper (user-friendly cron job names)
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-cron-display.php';
		
		$this->loader = new ChurchTools_Suite_Loader();
	}

	
	/**
	 * Initialize logging system (v0.9.2.3)
	 */
	private function init_logger(): void {
		ChurchTools_Suite_Logger::init();
	}
	
	/**
	 * Run database migrations if needed
	 * 
	 * Checks current DB version and runs any pending migrations.
	 * This runs on every plugin init but only executes migrations once.
	 */
	private function run_migrations(): void {
		ChurchTools_Suite_Migrations::run_migrations();
		
		// v0.10.2.2: Nach Update Cron-Jobs neu planen (falls Intervalle geändert wurden)
		$this->maybe_reschedule_crons();
	}
	
	/**
	 * Reschedule cron jobs after plugin update (v0.10.2.2)
	 * 
	 * Ensures cron jobs use correct intervals after updates.
	 * Only runs once per version to avoid unnecessary rescheduling.
	 */
	private function maybe_reschedule_crons(): void {
		$last_cron_reschedule_version = get_option( 'churchtools_suite_last_cron_reschedule_version', '0.0.0' );
		
		// Nur neu planen wenn neue Version installiert wurde
		if ( version_compare( $last_cron_reschedule_version, $this->version, '<' ) ) {
			require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-cron.php';
			ChurchTools_Suite_Cron::update_sync_schedule();
			
			// Version speichern
			update_option( 'churchtools_suite_last_cron_reschedule_version', $this->version, false );
			
			// Log
			if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
				ChurchTools_Suite_Logger::info(
					'cron',
					sprintf( 'Cron-Jobs nach Update auf v%s neu geplant', $this->version )
				);
			}
		}
	}
	
	/**
	 * Register admin hooks
	 */
	private function define_admin_hooks(): void {
		$admin = new ChurchTools_Suite_Admin( $this->version );
		
		// v1.0.3.1: Ensure capabilities exist (fallback in case activation hook didn't fire)
		$this->ensure_capabilities_exist();
		
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $admin, 'add_plugin_admin_menu' );
		
		// v0.10.3.4: Prevent redirect to plugins.php after update
		$this->loader->add_action( 'admin_init', $admin, 'handle_update_redirect', 1 );
		
		// Note: Public CSS is now loaded by Admin class (enqueue_styles)
		
		// Register AJAX handlers immediately
		$admin->register_ajax_handlers();
	}
	
	/**
	 * Ensure capabilities exist (fallback if activation hook didn't fire)
	 * 
	 * Called during admin_menu to ensure ChurchTools roles/capabilities exist.
	 * This is a safety measure in case the activation hook was skipped during updates.
	 * 
	 * @since 1.0.3.1
	 */
	private function ensure_capabilities_exist(): void {
		// Check if admin role has the capability
		$admin_role = get_role( 'administrator' );
		if ( ! $admin_role || ! $admin_role->has_cap( 'manage_churchtools_suite' ) ) {
			// Capabilities don't exist yet, so initialize them
			require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-roles.php';
			ChurchTools_Suite_Roles::create_or_update_roles();
		}
	}
	
	/**
	 * Register public hooks
	 */
	private function define_public_hooks(): void {
		// Register shortcodes (v0.5.0.0)
		add_action( 'init', [ 'ChurchTools_Suite_Shortcodes', 'register' ] );
		
		// Register Gutenberg blocks (v0.5.8.0)
		add_action( 'init', [ 'ChurchTools_Suite_Blocks', 'register' ] );
		
		// Register Elementor widget (v1.0.3.18+)
		add_action( 'elementor/widgets/register', [ $this, 'register_elementor_widget' ] );

		// Register single event handler (v0.9.3.1)
		add_action( 'init', [ 'ChurchTools_Suite_Single_Event_Handler', 'init' ] );
		
		// Enqueue frontend assets (also loaded in admin via admin_enqueue_scripts)
		$this->loader->add_action( 'wp_enqueue_scripts', $this, 'enqueue_public_assets' );
	}
	
	/**
	 * Register Elementor widget
	 * 
	 * Called via elementor/widgets/register hook when Elementor is loaded.
	 * Only processes if Elementor is available.
	 * 
	 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager
	 * @since 1.0.3.18
	 */
	public function register_elementor_widget( $widgets_manager ) {
		// Only register if Elementor is active and widget class exists
		if ( did_action( 'elementor/loaded' ) && class_exists( 'ChurchTools_Suite_Elementor_Events_Widget' ) ) {
			$widgets_manager->register( new ChurchTools_Suite_Elementor_Events_Widget() );
		}
	}
	
	/**
	 * Enqueue public assets
	 * 
	 * @since 0.5.1.0
	 * @since 0.6.0.3 Simplified - always load assets (small files, better UX)
	 */
	public function enqueue_public_assets(): void {
		// Always load assets - they're small and better UX than conditional loading issues
		
		// Enqueue CSS
		wp_enqueue_style(
			'churchtools-suite-public',
			CHURCHTOOLS_SUITE_URL . 'assets/css/churchtools-suite-public.css',
			[],
			$this->version,
			'all'
		);
		
		// Enqueue JS
		wp_enqueue_script(
			'churchtools-suite-public',
			CHURCHTOOLS_SUITE_URL . 'assets/js/churchtools-suite-public.js',
			[ 'jquery' ],
			$this->version,
			true
		);
		
		// Localize script
		wp_localize_script( 'churchtools-suite-public', 'churchtoolsSuitePublic', [
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'churchtools_suite_public' ),
		] );
	}
	
	/**
	 * Register cron hooks
	 */
	private function define_cron_hooks(): void {
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-cron.php';
		
		// Initialize cron system (register custom intervals)
		ChurchTools_Suite_Cron::init();
		
		add_action( 'churchtools_suite_session_keepalive', [ 'ChurchTools_Suite_Cron', 'session_keepalive' ] );
		add_action( 'churchtools_suite_auto_sync', [ 'ChurchTools_Suite_Cron', 'auto_sync' ] );
	}
	
	/**
	 * Run the loader
	 */
	public function run(): void {
		// Initialize logger (v0.7.2.6: Ensure log file is created)
		ChurchTools_Suite_Logger::init();
		
		// Log plugin start (only once per day to avoid spam)
		$last_start_log = get_transient('churchtools_suite_last_start_log');
		if (!$last_start_log) {
			ChurchTools_Suite_Logger::info(
				'plugin',
				sprintf('Plugin gestartet (Version %s)', CHURCHTOOLS_SUITE_VERSION)
			);
			set_transient('churchtools_suite_last_start_log', current_time('timestamp'), DAY_IN_SECONDS);
		}
		
		$this->loader->run();
	}
	
	/**
	 * Get the plugin version
	 */
	public function get_version(): string {
		return $this->version;
	}
}
