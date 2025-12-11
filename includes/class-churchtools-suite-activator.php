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
	 * - Creates database tables
	 * - Sets default options
	 * - Flushes rewrite rules
	 */
	public static function activate() {
		self::create_tables();
		self::set_default_options();
		flush_rewrite_rules();
	}
	
	/**
	 * Create database tables
	 */
	private static function create_tables() {
		global $wpdb;
		
		$charset_collate = $wpdb->get_charset_collate();
		$prefix = $wpdb->prefix . CHURCHTOOLS_SUITE_DB_PREFIX;
		
		$sql = [];
		
		// Calendars table
		$sql[] = "CREATE TABLE IF NOT EXISTS {$prefix}calendars (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			external_id varchar(100) NOT NULL,
			name varchar(255) NOT NULL,
			color varchar(20) DEFAULT NULL,
			is_selected tinyint(1) DEFAULT 0,
			is_public tinyint(1) DEFAULT 0,
			sort_order int(11) DEFAULT 0,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY external_id (external_id),
			KEY is_selected (is_selected)
		) $charset_collate;";
		
		// Events table
		$sql[] = "CREATE TABLE IF NOT EXISTS {$prefix}events (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			external_id varchar(100) NOT NULL,
			calendar_id varchar(100) DEFAULT NULL,
			title varchar(500) NOT NULL,
			description text,
			start_datetime datetime NOT NULL,
			end_datetime datetime DEFAULT NULL,
			is_all_day tinyint(1) DEFAULT 0,
			location_name varchar(255) DEFAULT NULL,
			status varchar(50) DEFAULT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY external_id (external_id),
			KEY calendar_id (calendar_id),
			KEY start_datetime (start_datetime)
		) $charset_collate;";
		
		// Event Services table
		$sql[] = "CREATE TABLE IF NOT EXISTS {$prefix}event_services (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			event_id bigint(20) unsigned NOT NULL,
			service_id varchar(100) DEFAULT NULL,
			service_name varchar(255) DEFAULT NULL,
			person_name varchar(255) DEFAULT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY event_id (event_id)
		) $charset_collate;";
		
		// Schedule table (unified view)
		$sql[] = "CREATE TABLE IF NOT EXISTS {$prefix}schedule (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			source_type varchar(20) NOT NULL,
			source_local_id bigint(20) unsigned NOT NULL,
			external_id varchar(100) DEFAULT NULL,
			calendar_id varchar(100) DEFAULT NULL,
			title varchar(500) NOT NULL,
			description text,
			start_datetime datetime NOT NULL,
			end_datetime datetime DEFAULT NULL,
			is_all_day tinyint(1) DEFAULT 0,
			location_name varchar(255) DEFAULT NULL,
			status varchar(50) DEFAULT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY source_unique (source_type, source_local_id),
			KEY calendar_id (calendar_id),
			KEY start_datetime (start_datetime)
		) $charset_collate;";
		
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		foreach ( $sql as $query ) {
			dbDelta( $query );
		}
	}
	
	/**
	 * Set default options
	 */
	private static function set_default_options() {
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
