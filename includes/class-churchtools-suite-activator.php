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
	 * - Schedules cron jobs
	 * - Flushes rewrite rules
	 * - Runs migrations
	 */
	public static function activate(): void {
		self::migrate_database();
		self::create_tables();
		self::set_default_options();
		self::schedule_cron_jobs();
		flush_rewrite_rules();
	}
	
	/**
	 * Migrate database schema if needed
	 */
	private static function migrate_database(): void {
		global $wpdb;
		
		$prefix = $wpdb->prefix . CHURCHTOOLS_SUITE_DB_PREFIX;
		$events_table = $prefix . 'events';
		
		// Check if table exists
		$table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$events_table}'");
		
		if (!$table_exists) {
			// Table doesn't exist yet, will be created by create_tables()
			return;
		}
		
		// Check if old schema (external_id column exists)
		$columns = $wpdb->get_results("SHOW COLUMNS FROM {$events_table}");
		$column_names = array_column($columns, 'Field');
		
		$has_external_id = in_array('external_id', $column_names);
		$has_event_id = in_array('event_id', $column_names);
		$has_appointment_id = in_array('appointment_id', $column_names);
		$has_raw_payload = in_array('raw_payload', $column_names);
		
		// Migration needed: Rename external_id to event_id and add missing columns
		if ($has_external_id && !$has_event_id) {
			$wpdb->query("ALTER TABLE {$events_table} CHANGE COLUMN `external_id` `event_id` varchar(100) NOT NULL");
			$wpdb->query("ALTER TABLE {$events_table} DROP INDEX IF EXISTS `idx_external_id`");
			$wpdb->query("ALTER TABLE {$events_table} ADD UNIQUE KEY `event_id` (`event_id`)");
		}
		
		// Add appointment_id column if missing
		if (!$has_appointment_id) {
			$wpdb->query("ALTER TABLE {$events_table} ADD COLUMN `appointment_id` varchar(100) DEFAULT NULL AFTER `calendar_id`");
			$wpdb->query("ALTER TABLE {$events_table} ADD KEY `appointment_id` (`appointment_id`)");
		}
		
		// Add raw_payload column if missing
		if (!$has_raw_payload) {
			$wpdb->query("ALTER TABLE {$events_table} ADD COLUMN `raw_payload` longtext DEFAULT NULL AFTER `status`");
		}
	}
	
	/**
	 * Schedule cron jobs
	 */
	private static function schedule_cron_jobs(): void {
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-cron.php';
		ChurchTools_Suite_Cron::schedule_jobs();
	}
	
	/**
	 * Create database tables
	 */
	private static function create_tables(): void {
		global $wpdb;
		
		$charset_collate = $wpdb->get_charset_collate();
		$prefix = $wpdb->prefix . CHURCHTOOLS_SUITE_DB_PREFIX;
		
		$sql = [];
		
		// Calendars table
		$sql[] = "CREATE TABLE IF NOT EXISTS {$prefix}calendars (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			calendar_id varchar(100) NOT NULL,
			name varchar(255) NOT NULL,
			name_translated varchar(255) DEFAULT NULL,
			color varchar(20) DEFAULT NULL,
			is_selected tinyint(1) DEFAULT 0,
			is_public tinyint(1) DEFAULT 0,
			sort_order int(11) DEFAULT 0,
			raw_payload longtext DEFAULT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY calendar_id (calendar_id),
			KEY is_selected (is_selected)
		) $charset_collate;";
		
		// Events table
		$sql[] = "CREATE TABLE IF NOT EXISTS {$prefix}events (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			event_id varchar(100) NOT NULL,
			calendar_id varchar(100) DEFAULT NULL,
			appointment_id varchar(100) DEFAULT NULL,
			title varchar(500) NOT NULL,
			description text,
			start_datetime datetime NOT NULL,
			end_datetime datetime DEFAULT NULL,
			is_all_day tinyint(1) DEFAULT 0,
			location_name varchar(255) DEFAULT NULL,
			status varchar(50) DEFAULT NULL,
			raw_payload longtext DEFAULT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY event_id (event_id),
			KEY calendar_id (calendar_id),
			KEY appointment_id (appointment_id),
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
