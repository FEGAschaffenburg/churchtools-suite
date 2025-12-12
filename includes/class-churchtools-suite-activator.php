<?php
/**
 * Plugin Activation Handler
 *
 * @package ChurchTools_Suite
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ChurchTools_Suite_Activator {
	
	/**
	 * Plugin activation
	 * 
	 * - Runs database migrations (via migration system)
	 * - Sets default options
	 * - Schedules cron jobs
	 * - Flushes rewrite rules
	 * 
	 * Note: Database tables are created via migration system (class-churchtools-suite-migrations.php)
	 */
	public static function activate(): void {
		// Load migrations system
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-migrations.php';
		
		// Run all pending migrations (including table creation)
		ChurchTools_Suite_Migrations::run_migrations();
		
		self::set_default_options();
		self::schedule_cron_jobs();
		flush_rewrite_rules();
	}
	
	/**
	 * Schedule cron jobs
	 */
	private static function schedule_cron_jobs(): void {
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-cron.php';
		ChurchTools_Suite_Cron::schedule_jobs();
	}
	
	/**
	 * Set default options
	 */
	private static function set_default_options(): void {
		$defaults = [
			'churchtools_suite_version' => CHURCHTOOLS_SUITE_VERSION,
			'churchtools_suite_auto_sync_enabled' => 0,
			'churchtools_suite_sync_interval' => 3600, // 1 hour
		];
		
		foreach ( $defaults as $key => $value ) {
			if ( get_option( $key ) === false ) {
				add_option( $key, $value );
			}
		}
	}
}
