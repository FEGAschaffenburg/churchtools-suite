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
		wp_clear_scheduled_hook( 'churchtools_suite_sync_cron' );
		flush_rewrite_rules();
	}
}
