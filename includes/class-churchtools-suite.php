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
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-migrations.php';
		require_once CHURCHTOOLS_SUITE_PATH . 'admin/class-churchtools-suite-admin.php';
		
		$this->loader = new ChurchTools_Suite_Loader();
	}
	
	/**
	 * Run database migrations if needed
	 * 
	 * Checks current DB version and runs any pending migrations.
	 * This runs on every plugin init but only executes migrations once.
	 */
	private function run_migrations(): void {
		ChurchTools_Suite_Migrations::run_migrations();
	}
	
	/**
	 * Register admin hooks
	 */
	private function define_admin_hooks(): void {
		$admin = new ChurchTools_Suite_Admin( $this->version );
		
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $admin, 'add_plugin_admin_menu' );
		
		// Register AJAX handlers immediately
		$admin->register_ajax_handlers();
	}
	
	/**
	 * Register public hooks
	 */
	private function define_public_hooks(): void {
		// Public functionality will be added later
	}
	
	/**
	 * Register cron hooks
	 */
	private function define_cron_hooks(): void {
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-cron.php';
		
		add_action( 'churchtools_suite_session_keepalive', [ 'ChurchTools_Suite_Cron', 'session_keepalive' ] );
	}
	
	/**
	 * Run the loader
	 */
	public function run(): void {
		$this->loader->run();
	}
	
	/**
	 * Get the plugin version
	 */
	public function get_version(): string {
		return $this->version;
	}
}
