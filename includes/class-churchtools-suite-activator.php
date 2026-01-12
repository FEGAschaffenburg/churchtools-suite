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
		// Load and register roles/capabilities (v1.0.2.0)
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-roles.php';
		ChurchTools_Suite_Roles::register_role();
		
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
	 * 
	 * Smart-Defaults (v0.7.0.1):
	 * - Auto-Sync aktiviert (täglich)
	 * - Sync-Range: 7 Tage rückwärts, 90 Tage vorwärts
	 * - Session Keep-Alive aktiviert
	 */
	private static function set_default_options(): void {
		$defaults = [
			'churchtools_suite_version' => CHURCHTOOLS_SUITE_VERSION,
			'churchtools_suite_auto_sync_enabled' => 1, // ✅ Auto-Sync standardmäßig aktiviert
			'churchtools_suite_auto_sync_interval' => 'daily', // ✅ Täglich (statt stündlich)
			'churchtools_suite_sync_days_past' => 7, // ✅ 7 Tage rückwärts
			'churchtools_suite_sync_days_future' => 90, // ✅ 90 Tage vorwärts
			'churchtools_suite_session_keepalive_enabled' => 1, // ✅ Session Keep-Alive aktiv
		];
		
		foreach ( $defaults as $key => $value ) {
			if ( get_option( $key ) === false ) {
				add_option( $key, $value );
			}
		}
	}
}
