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
	const DB_VERSION = '2.2';
	
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
		
		if ( version_compare( $current_version, '1.2', '<' ) ) {
			self::migrate_to_1_2();
		}
		
		if ( version_compare( $current_version, '1.3', '<' ) ) {
			self::migrate_to_1_3();
		}
		
		if ( version_compare( $current_version, '1.4', '<' ) ) {
			self::migrate_to_1_4();
		}
		
		if ( version_compare( $current_version, '1.5', '<' ) ) {
			self::migrate_to_1_5();
		}
		
		if ( version_compare( $current_version, '1.6', '<' ) ) {
			self::migrate_to_1_6();
		}
		
		if ( version_compare( $current_version, '1.7', '<' ) ) {
			self::migrate_to_1_7();
		}
		
		if ( version_compare( $current_version, '1.8', '<' ) ) {
			self::migrate_to_1_8();
		}
		
		if ( version_compare( $current_version, '1.9', '<' ) ) {
			self::migrate_to_1_9();
		}
		
		if ( version_compare( $current_version, '2.0', '<' ) ) {
			self::migrate_to_2_0();
		}
		
		if ( version_compare( $current_version, '2.1', '<' ) ) {
			self::migrate_to_2_1();
		}
		
		if ( version_compare( $current_version, '2.2', '<' ) ) {
			self::migrate_to_2_2();
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
	 * Migration 1.2: Sync History Table (v0.3.9.3)
	 * 
	 * Creates sync_history table for tracking all sync operations.
	 * Stores statistics, errors, and timing information.
	 * 
	 * This migration is idempotent and safe to run multiple times.
	 */
	private static function migrate_to_1_2(): void {
		global $wpdb;
		
		$charset_collate = $wpdb->get_charset_collate();
		$prefix = $wpdb->prefix . CHURCHTOOLS_SUITE_DB_PREFIX;
		
		$table_name = $prefix . 'sync_history';
		
		// Check if table already exists
		$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );
		
		if ( $table_exists ) {
			return; // Table already exists, skip creation
		}
		
		// Create sync_history table
		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			sync_type varchar(20) NOT NULL DEFAULT 'auto',
			status varchar(20) NOT NULL DEFAULT 'pending',
			calendars_processed int(11) DEFAULT 0,
			events_found int(11) DEFAULT 0,
			events_inserted int(11) DEFAULT 0,
			events_updated int(11) DEFAULT 0,
			events_skipped int(11) DEFAULT 0,
			error_message text DEFAULT NULL,
			started_at datetime NOT NULL,
			completed_at datetime DEFAULT NULL,
			duration_seconds int(11) DEFAULT NULL,
			PRIMARY KEY (id),
			KEY sync_type (sync_type),
			KEY status (status),
			KEY started_at (started_at)
		) $charset_collate;";
		
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}
	
	/**
	 * Migration 1.3: Services Table (v0.3.11.0)
	 * 
	 * Creates services table for master data of ChurchTools services.
	 * Allows users to select which services to import from events.
	 * 
	 * This migration is idempotent and safe to run multiple times.
	 */
	private static function migrate_to_1_3(): void {
		global $wpdb;
		
		$charset_collate = $wpdb->get_charset_collate();
		$prefix = $wpdb->prefix . CHURCHTOOLS_SUITE_DB_PREFIX;
		
		$table_name = $prefix . 'services';
		
		// Check if table already exists
		$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );
		
		if ( $table_exists ) {
			return; // Table already exists, skip creation
		}
		
		// Create services table
		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			service_id varchar(100) NOT NULL,
			service_group_id varchar(100) DEFAULT NULL,
			name varchar(255) NOT NULL,
			name_translated varchar(255) DEFAULT NULL,
			is_selected tinyint(1) DEFAULT 0,
			sort_order int(11) DEFAULT 0,
			raw_payload longtext DEFAULT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY service_id (service_id),
			KEY service_group_id (service_group_id),
			KEY is_selected (is_selected)
		) $charset_collate;";
		
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}
	
	/**
	 * Migration 1.4: Service Groups Table (v0.3.11.3)
	 * 
	 * Creates service_groups table for ChurchTools service groups master data.
	 * Allows users to select which service groups to sync.
	 * 
	 * This migration is idempotent and safe to run multiple times.
	 */
	private static function migrate_to_1_4(): void {
		global $wpdb;
		
		$charset_collate = $wpdb->get_charset_collate();
		$prefix = $wpdb->prefix . CHURCHTOOLS_SUITE_DB_PREFIX;
		
		$table_name = $prefix . 'service_groups';
		
		// Check if table already exists
		$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );
		
		if ( $table_exists ) {
			return; // Table already exists, skip creation
		}
		
		// Create service_groups table
		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			service_group_id varchar(100) NOT NULL,
			name varchar(255) NOT NULL,
			is_selected tinyint(1) DEFAULT 0,
			sort_order int(11) DEFAULT 0,
			view_all tinyint(1) DEFAULT 0,
			raw_payload longtext DEFAULT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY service_group_id (service_group_id),
			KEY is_selected (is_selected)
		) $charset_collate;";
		
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}
	
	/**
	 * Migration 1.5: Add services_imported to sync_history (v0.3.13.2)
	 * 
	 * Adds services_imported column to sync_history table to track
	 * how many services were imported during each sync.
	 * 
	 * This migration is idempotent and safe to run multiple times.
	 */
	private static function migrate_to_1_5(): void {
		global $wpdb;
		
		$prefix = $wpdb->prefix . CHURCHTOOLS_SUITE_DB_PREFIX;
		$table_name = $prefix . 'sync_history';
		
		// Check if table exists
		$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );
		
		if ( ! $table_exists ) {
			return; // Table doesn't exist yet
		}
		
		// Check if column already exists
		$column_exists = $wpdb->get_results(
			$wpdb->prepare(
				"SHOW COLUMNS FROM {$table_name} LIKE %s",
				'services_imported'
			)
		);
		
		if ( ! empty( $column_exists ) ) {
			return; // Column already exists
		}
		
		// Add services_imported column
		$wpdb->query(
			"ALTER TABLE {$table_name} 
			ADD COLUMN services_imported int(11) DEFAULT 0 AFTER events_skipped"
		);
	}
	
	/**
	 * Migration 1.6: Shortcode Presets Table (v0.5.10.0)
	 * 
	 * Creates shortcode_presets table for storing saved shortcode configurations.
	 * Allows users to create and save custom shortcode presets.
	 * 
	 * This migration is idempotent and safe to run multiple times.
	 */
	private static function migrate_to_1_6(): void {
		global $wpdb;
		
		$charset_collate = $wpdb->get_charset_collate();
		$prefix = $wpdb->prefix . CHURCHTOOLS_SUITE_DB_PREFIX;
		
		$table_name = $prefix . 'shortcode_presets';
		
		// Check if table already exists
		$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );
		
		if ( $table_exists ) {
			return; // Table already exists, skip creation
		}
		
		// Create shortcode_presets table
		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
			description text DEFAULT NULL,
			shortcode_tag varchar(100) NOT NULL,
			configuration longtext NOT NULL,
			is_system tinyint(1) DEFAULT 0,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY shortcode_tag (shortcode_tag),
			KEY is_system (is_system)
		) $charset_collate;";
		
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
		
		// Create system presets
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-shortcode-presets-repository.php';
		$presets_repo = new ChurchTools_Suite_Shortcode_Presets_Repository();
		$presets_repo->create_system_presets();
	}
	
	/**
	 * Migration 1.7: Incremental Sync Support (v0.7.1.0)
	 * 
	 * Adds last_modified column to events table for delta sync.
	 * Allows tracking when events were last changed in ChurchTools.
	 * 
	 * This migration is idempotent and safe to run multiple times.
	 */
	private static function migrate_to_1_7(): void {
		global $wpdb;
		
		$prefix = $wpdb->prefix . CHURCHTOOLS_SUITE_DB_PREFIX;
		$table_name = $prefix . 'events';
		
		// Check if table exists
		$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );
		
		if ( ! $table_exists ) {
			return; // Table doesn't exist yet
		}
		
		// Check if column already exists
		$column_exists = $wpdb->get_results(
			$wpdb->prepare(
				"SHOW COLUMNS FROM {$table_name} LIKE %s",
				'last_modified'
			)
		);
		
		if ( ! empty( $column_exists ) ) {
			return; // Column already exists
		}
		
		// Add last_modified column
		$wpdb->query(
			"ALTER TABLE {$table_name} 
			ADD COLUMN last_modified datetime DEFAULT NULL AFTER updated_at,
			ADD INDEX last_modified (last_modified)"
		);
		
		// Initialize existing events with current timestamp
		// (Conservative: treat all existing events as recently synced)
		$wpdb->query(
			"UPDATE {$table_name} 
			SET last_modified = updated_at 
			WHERE last_modified IS NULL"
		);
	}
	
	/**
	 * Migration 1.8: Incremental Sync Statistics (v0.7.1.0)
	 * 
	 * Adds sync_type, events_unchanged, events_deleted to sync_history table.
	 * Tracks incremental vs full sync and unchanged/deleted event counts.
	 * 
	 * This migration is idempotent and safe to run multiple times.
	 */
	private static function migrate_to_1_8(): void {
		global $wpdb;
		
		$prefix = $wpdb->prefix . CHURCHTOOLS_SUITE_DB_PREFIX;
		$table_name = $prefix . 'sync_history';
		
		// Check if table exists
		$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );
		
		if ( ! $table_exists ) {
			return; // Table doesn't exist yet
		}
		
		// Add sync_type column (full or incremental)
		$sync_type_exists = $wpdb->get_results(
			$wpdb->prepare("SHOW COLUMNS FROM {$table_name} LIKE %s", 'sync_type')
		);
		
		if ( empty( $sync_type_exists ) ) {
			$wpdb->query(
				"ALTER TABLE {$table_name} 
				ADD COLUMN sync_type varchar(20) NOT NULL DEFAULT 'full' AFTER status"
			);
		}
		
		// Add events_unchanged column
		$unchanged_exists = $wpdb->get_results(
			$wpdb->prepare("SHOW COLUMNS FROM {$table_name} LIKE %s", 'events_unchanged')
		);
		
		if ( empty( $unchanged_exists ) ) {
			$wpdb->query(
				"ALTER TABLE {$table_name} 
				ADD COLUMN events_unchanged int(11) DEFAULT 0 AFTER events_skipped"
			);
		}
		
		// Add events_deleted column
		$deleted_exists = $wpdb->get_results(
			$wpdb->prepare("SHOW COLUMNS FROM {$table_name} LIKE %s", 'events_deleted')
		);
		
		if ( empty( $deleted_exists ) ) {
			$wpdb->query(
				"ALTER TABLE {$table_name} 
				ADD COLUMN events_deleted int(11) DEFAULT 0 AFTER events_unchanged"
			);
		}
	}
	
	/**
	 * Migration 1.9: Appointment Modified Tracking (v0.8.1.0)
	 * 
	 * Adds appointment_modified column to events table for tracking
	 * appointment-level changes independent from event-level changes.
	 * 
	 * This migration is idempotent and safe to run multiple times.
	 */
	private static function migrate_to_1_9(): void {
		global $wpdb;
		
		$prefix = $wpdb->prefix . CHURCHTOOLS_SUITE_DB_PREFIX;
		$table_name = $prefix . 'events';
		
		// Check if table exists
		$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );
		
		if ( ! $table_exists ) {
			return; // Table doesn't exist yet
		}
		
		// Check if column already exists
		$column_exists = $wpdb->get_results(
			$wpdb->prepare(
				"SHOW COLUMNS FROM {$table_name} LIKE %s",
				'appointment_modified'
			)
		);
		
		if ( ! empty( $column_exists ) ) {
			return; // Column already exists
		}
		
		// Add appointment_modified column
		$wpdb->query(
			"ALTER TABLE {$table_name} 
			ADD COLUMN appointment_modified datetime DEFAULT NULL AFTER last_modified,
			ADD INDEX appointment_modified (appointment_modified)"
		);
		
		// Initialize existing events with NULL (will be populated on next sync)
		// No UPDATE needed - NULL is the correct initial value
	}
	
	/**
	 * Migration 2.0: Composite Unique Key (v0.9.0.0)
	 * 
	 * CRITICAL FIX: Events can have multiple appointments (1:N relationship).
	 * Appointment_id alone may not be unique for recurring events.
	 * 
	 * Changes:
	 * - Remove UNIQUE constraint from event_id
	 * - Add INDEX on event_id (for filtering by series)
	 * - Add COMPOSITE UNIQUE KEY (appointment_id, start_datetime)
	 * - Allow event_id to be NULL (standalone appointments)
	 * - Make appointment_id NOT NULL (every row must have one)
	 * 
	 * Why composite key?
	 * - Appointment_id might be series-level (not instance-level)
	 * - start_datetime is guaranteed unique per instance
	 * - Combination ensures each appointment instance is unique
	 * 
	 * This migration is idempotent and safe to run multiple times.
	 */
	private static function migrate_to_2_0(): void {
		global $wpdb;
		
		$prefix = $wpdb->prefix . CHURCHTOOLS_SUITE_DB_PREFIX;
		$table_name = $prefix . 'events';
		
		// Check if table exists
		$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );
		
		if ( ! $table_exists ) {
			return; // Table doesn't exist yet
		}
		
		// Get current indexes
		$indexes = $wpdb->get_results( "SHOW INDEX FROM {$table_name}" );
		$index_names = array_column( $indexes, 'Key_name' );
		
		// Step 1: Remove UNIQUE constraint from event_id (if exists)
		if ( in_array( 'event_id', $index_names, true ) ) {
			$wpdb->query( "ALTER TABLE {$table_name} DROP INDEX event_id" );
			ChurchTools_Suite_Logger::log( 'migrations', 'Dropped UNIQUE index from event_id' );
		}
		
		// Step 2: Add regular index for event_id (for grouping/filtering)
		if ( ! in_array( 'idx_event_id', $index_names, true ) ) {
			$wpdb->query( "ALTER TABLE {$table_name} ADD INDEX idx_event_id (event_id)" );
		}
		
		// Step 3: Make event_id nullable (for standalone appointments)
		$wpdb->query( "ALTER TABLE {$table_name} MODIFY COLUMN event_id varchar(100) DEFAULT NULL" );
		
		// Step 4: Update appointment_id column to NOT NULL
		// First, set NULL values to a temporary value (shouldn't exist, but safety first)
		$null_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name} WHERE appointment_id IS NULL" );
		
		if ( $null_count > 0 ) {
			ChurchTools_Suite_Logger::log(
				'migrations',
				sprintf( 'Found %d events without appointment_id - setting temporary IDs', $null_count )
			);
			
			// Generate temporary appointment IDs for orphaned events
			$wpdb->query(
				"UPDATE {$table_name} 
				SET appointment_id = CONCAT('temp_', id, '_', DATE_FORMAT(start_datetime, '%Y%m%d%H%i%s'))
				WHERE appointment_id IS NULL"
			);
		}
		
		// Now make appointment_id NOT NULL
		$wpdb->query( "ALTER TABLE {$table_name} MODIFY COLUMN appointment_id varchar(100) NOT NULL" );
		
		// Step 5: Remove old appointment_id UNIQUE index (if exists)
		if ( in_array( 'appointment_id', $index_names, true ) ) {
			$wpdb->query( "ALTER TABLE {$table_name} DROP INDEX appointment_id" );
			ChurchTools_Suite_Logger::log( 'migrations', 'Dropped old appointment_id index' );
		}
		
		// Step 6: Add COMPOSITE UNIQUE KEY (appointment_id + start_datetime)
		$has_composite = false;
		foreach ( $indexes as $index ) {
			if ( $index->Key_name === 'appointment_datetime' && $index->Non_unique == 0 ) {
				$has_composite = true;
				break;
			}
		}
		
		if ( ! $has_composite ) {
			$wpdb->query( "ALTER TABLE {$table_name} ADD UNIQUE KEY appointment_datetime (appointment_id, start_datetime)" );
			ChurchTools_Suite_Logger::log( 'migrations', 'Added COMPOSITE UNIQUE index (appointment_id, start_datetime)' );
		}
		
		ChurchTools_Suite_Logger::log(
			'migrations',
			'Migration 2.0 complete: Using composite unique key (appointment_id + start_datetime)'
		);
	}
	
	/**
	 * Migration 2.1: Separate Event and Appointment Descriptions (v0.9.1.0)
	 * 
	 * Splits combined description field into:
	 * - event_description (Event.note - series-level info)
	 * - appointment_description (Appointment.note/description - instance-specific)
	 * 
	 * This allows templates/blocks to decide which info to display.
	 * 
	 * This migration is idempotent and safe to run multiple times.
	 */
	private static function migrate_to_2_1(): void {
		global $wpdb;
		
		$prefix = $wpdb->prefix . CHURCHTOOLS_SUITE_DB_PREFIX;
		$table_name = $prefix . 'events';
		
		// Check if table exists
		$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );
		
		if ( ! $table_exists ) {
			return; // Table doesn't exist yet
		}
		
		// Check if columns already exist
		$columns = $wpdb->get_results( "SHOW COLUMNS FROM {$table_name}" );
		$column_names = array_column( $columns, 'Field' );
		
		$has_event_desc = in_array( 'event_description', $column_names, true );
		$has_appointment_desc = in_array( 'appointment_description', $column_names, true );
		
		// Add event_description column
		if ( ! $has_event_desc ) {
			$wpdb->query( "ALTER TABLE {$table_name} ADD COLUMN event_description text DEFAULT NULL AFTER description" );
			ChurchTools_Suite_Logger::log( 'migrations', 'Added event_description column' );
		}
		
		// Add appointment_description column
		if ( ! $has_appointment_desc ) {
			$wpdb->query( "ALTER TABLE {$table_name} ADD COLUMN appointment_description text DEFAULT NULL AFTER event_description" );
			ChurchTools_Suite_Logger::log( 'migrations', 'Added appointment_description column' );
		}
		
		// Split existing descriptions (best effort)
		// If description contains separator "--- Termindetails ---", split it
		$events_with_combined = $wpdb->get_results(
			"SELECT id, description FROM {$table_name} WHERE description LIKE '%--- Termindetails ---%'"
		);
		
		if ( ! empty( $events_with_combined ) ) {
			foreach ( $events_with_combined as $event ) {
				$parts = explode( "\n\n--- Termindetails ---\n\n", $event->description, 2 );
				
				if ( count( $parts ) === 2 ) {
					$wpdb->update(
						$table_name,
						[
							'event_description' => $parts[0],
							'appointment_description' => $parts[1],
						],
						[ 'id' => $event->id ]
					);
				}
			}
			
			ChurchTools_Suite_Logger::log(
				'migrations',
				sprintf( 'Split %d combined descriptions into separate fields', count( $events_with_combined ) )
			);
		}
		
		ChurchTools_Suite_Logger::log(
			'migrations',
			'Migration 2.1 complete: Separate event and appointment descriptions'
		);
	}
	
	/**
	 * Migration 2.2: Address Details and Tags (v0.9.2.0)
	 * 
	 * Adds structured address fields and tags support:
	 * - address_name (meetingAt name)
	 * - address_street
	 * - address_zip
	 * - address_city
	 * - address_latitude
	 * - address_longitude
	 * - tags (JSON array of tag objects)
	 * 
	 * This migration is idempotent and safe to run multiple times.
	 */
	private static function migrate_to_2_2(): void {
		global $wpdb;
		
		$prefix = $wpdb->prefix . CHURCHTOOLS_SUITE_DB_PREFIX;
		$table_name = $prefix . 'events';
		
		// Check if table exists
		$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );
		
		if ( ! $table_exists ) {
			return; // Table doesn't exist yet
		}
		
		// Get current columns
		$columns = $wpdb->get_results( "SHOW COLUMNS FROM {$table_name}" );
		$column_names = array_column( $columns, 'Field' );
		
		// Add address_name column
		if ( ! in_array( 'address_name', $column_names, true ) ) {
			$wpdb->query( "ALTER TABLE {$table_name} ADD COLUMN address_name varchar(255) DEFAULT NULL AFTER location_name" );
			ChurchTools_Suite_Logger::log( 'migrations', 'Added address_name column' );
		}
		
		// Add address_street column
		if ( ! in_array( 'address_street', $column_names, true ) ) {
			$wpdb->query( "ALTER TABLE {$table_name} ADD COLUMN address_street varchar(255) DEFAULT NULL AFTER address_name" );
			ChurchTools_Suite_Logger::log( 'migrations', 'Added address_street column' );
		}
		
		// Add address_zip column
		if ( ! in_array( 'address_zip', $column_names, true ) ) {
			$wpdb->query( "ALTER TABLE {$table_name} ADD COLUMN address_zip varchar(20) DEFAULT NULL AFTER address_street" );
			ChurchTools_Suite_Logger::log( 'migrations', 'Added address_zip column' );
		}
		
		// Add address_city column
		if ( ! in_array( 'address_city', $column_names, true ) ) {
			$wpdb->query( "ALTER TABLE {$table_name} ADD COLUMN address_city varchar(255) DEFAULT NULL AFTER address_zip" );
			ChurchTools_Suite_Logger::log( 'migrations', 'Added address_city column' );
		}
		
		// Add address_latitude column
		if ( ! in_array( 'address_latitude', $column_names, true ) ) {
			$wpdb->query( "ALTER TABLE {$table_name} ADD COLUMN address_latitude decimal(10,8) DEFAULT NULL AFTER address_city" );
			ChurchTools_Suite_Logger::log( 'migrations', 'Added address_latitude column' );
		}
		
		// Add address_longitude column
		if ( ! in_array( 'address_longitude', $column_names, true ) ) {
			$wpdb->query( "ALTER TABLE {$table_name} ADD COLUMN address_longitude decimal(11,8) DEFAULT NULL AFTER address_latitude" );
			ChurchTools_Suite_Logger::log( 'migrations', 'Added address_longitude column' );
		}
		
		// Add tags column (JSON array)
		if ( ! in_array( 'tags', $column_names, true ) ) {
			$wpdb->query( "ALTER TABLE {$table_name} ADD COLUMN tags longtext DEFAULT NULL AFTER address_longitude" );
			ChurchTools_Suite_Logger::log( 'migrations', 'Added tags column' );
		}
		
		ChurchTools_Suite_Logger::log(
			'migrations',
			'Migration 2.2 complete: Address details and tags support'
		);
	}
	
	/**
	 * Get current database version
	 * 
	 * @return string Current DB version (e.g., '1.2')
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
