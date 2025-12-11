<?php
/**
 * Plugin Deactivation Handler
 *
 * @package ChurchTools_Suite
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ChurchTools_Suite_Deactivator {
	
	/**
	 * Plugin deactivation
	 * 
	 * - Clears scheduled cron jobs
	 * - Flushes rewrite rules
	 */
	public static function deactivate(): void {
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-cron.php';
		ChurchTools_Suite_Cron::clear_jobs();
		flush_rewrite_rules();
	}
}
