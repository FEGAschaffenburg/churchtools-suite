<?php
/**
 * Database Migration Manager
 * 
 * Handles versioned database migrations that run automatically on plugin load.
 * Each migration runs only once and is tracked via DB version in wp_options.
 *
 * @package ChurchTools_Suite
 * @since   0.3.7.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ChurchTools_Suite_Migrations {
	
	/**
	 * Current database schema version
	 * 
	 * Increment this when adding new migrations.
	 * Format: Major.Minor (e.g., 1.0, 1.1, 1.2)
	 */
	const DB_VERSION = '1.1';
	
	/**
	 * Option key for storing DB version
	 */
	const DB_VERSION_KEY = 'churchtools_suite_db_version';
	
	/**
	 * Run all pending migrations
	 * 
	 * This is called on every plugin init and checks if migrations are needed.
	 * Only runs migrations that haven't been executed yet.
	 */
	public static function run_migrations(): void {
		$current_version = get_option( self::DB_VERSION_KEY, '0.0' );
		
		// No migrations needed
		if ( version_compare( $current_version, self::DB_VERSION, '>=' ) ) {
			return;
		}
		
		// Log migration start
		if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
			ChurchTools_Suite_Logger::log( 'migrations', 'Starting migrations', [
				'from_version' => $current_version,
				'to_version' => self::DB_VERSION,
			] );
		}
		
		// Run migrations in order
		if ( version_compare( $current_version, '1.0', '<' ) ) {
			self::migrate_to_1_0();
		}
		
		if ( version_compare( $current_version, '1.1', '<' ) ) {
			self::migrate_to_1_1();
		}
		
		// Update DB version
		update_option( self::DB_VERSION_KEY, self::DB_VERSION );
		
		// Log migration complete
		if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
			ChurchTools_Suite_Logger::log( 'migrations', 'Migrations completed', [
				'new_version' => self::DB_VERSION,
			] );
		}
	}
	
	/**
	 * Migration 1.0: Initial database structure
	 * 
	 * Creates all tables if they don't exist yet.
	 * This migration is safe to skip if tables already exist.
	 */
	private static function migrate_to_1_0(): void {
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
		
		// Events table (old schema with external_id)
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
			UNIQUE KEY idx_external_id (external_id),
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
		
		// Schedule table
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
	 * Migration 1.1: Event Sync Schema Update (v0.3.7.3)
	 * 
	 * Updates events table for 2-phase Event/Appointment sync:
	 * - Renames external_id → event_id (more accurate naming)
	 * - Adds appointment_id column (tracks which appointments were imported)
	 * - Adds raw_payload column (stores full API response for debugging)
	 * 
	 * This migration is idempotent and safe to run multiple times.
	 */
	private static function migrate_to_1_1(): void {
		global $wpdb;
		
		$prefix = $wpdb->prefix . CHURCHTOOLS_SUITE_DB_PREFIX;
		$events_table = $prefix . 'events';
		
		// Check if table exists
		$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $events_table ) );
		
		if ( ! $table_exists ) {
			// Table doesn't exist yet, will be created by migration 1.0
			return;
		}
		
		// Get current columns
		$columns = $wpdb->get_results( "SHOW COLUMNS FROM {$events_table}" );
		$column_names = array_column( $columns, 'Field' );
		
		$has_external_id = in_array( 'external_id', $column_names );
		$has_event_id = in_array( 'event_id', $column_names );
		$has_appointment_id = in_array( 'appointment_id', $column_names );
		$has_raw_payload = in_array( 'raw_payload', $column_names );
		
		// Step 1: Rename external_id → event_id
		if ( $has_external_id && ! $has_event_id ) {
			$wpdb->query( "ALTER TABLE {$events_table} CHANGE COLUMN `external_id` `event_id` varchar(100) NOT NULL" );
			
			// Update index
			$wpdb->query( "ALTER TABLE {$events_table} DROP INDEX IF EXISTS `idx_external_id`" );
			$wpdb->query( "ALTER TABLE {$events_table} ADD UNIQUE KEY `event_id` (`event_id`)" );
		}
		
		// Step 2: Add appointment_id column
		if ( ! $has_appointment_id ) {
			$wpdb->query( "ALTER TABLE {$events_table} ADD COLUMN `appointment_id` varchar(100) DEFAULT NULL AFTER `calendar_id`" );
			$wpdb->query( "ALTER TABLE {$events_table} ADD KEY `appointment_id` (`appointment_id`)" );
		}
		
		// Step 3: Add raw_payload column
		if ( ! $has_raw_payload ) {
			$wpdb->query( "ALTER TABLE {$events_table} ADD COLUMN `raw_payload` longtext DEFAULT NULL AFTER `status`" );
		}
	}
	
	/**
	 * Get current database version
	 * 
	 * @return string Current DB version (e.g., '1.1')
	 */
	public static function get_current_version(): string {
		return get_option( self::DB_VERSION_KEY, '0.0' );
	}
	
	/**
	 * Check if migrations are pending
	 * 
	 * @return bool True if migrations need to run
	 */
	public static function has_pending_migrations(): bool {
		$current_version = self::get_current_version();
		return version_compare( $current_version, self::DB_VERSION, '<' );
	}
}
