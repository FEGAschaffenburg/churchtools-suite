<?php
/**
 * Demo Plugin Database Migration Manager
 * 
 * Handles versioned database migrations for demo plugin
 *
 * @package ChurchTools_Suite_Demo
 * @since   1.0.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ChurchTools_Suite_Demo_Migrations {
	
	/**
	 * Current database schema version
	 */
	const DB_VERSION = '1.3';
	
	/**
	 * Option key for storing DB version
	 */
	const DB_VERSION_KEY = 'churchtools_suite_demo_db_version';
	
	/**
	 * Run all pending migrations
	 */
	public static function run_migrations(): void {
		$current_version = get_option( self::DB_VERSION_KEY, '0.0' );
		
		// No migrations needed
		if ( version_compare( $current_version, self::DB_VERSION, '>=' ) ) {
			return;
		}
		
		// Log migration start
		error_log( sprintf(
			'[ChurchTools Demo] Starting migrations from v%s to v%s',
			$current_version,
			self::DB_VERSION
		) );
		
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
		
		// Update DB version
		update_option( self::DB_VERSION_KEY, self::DB_VERSION );
		
		error_log( sprintf(
			'[ChurchTools Demo] Migrations completed to v%s',
			self::DB_VERSION
		) );
	}
	
	/**
	 * Migration 1.0: Initial demo_users table
	 * 
	 * Creates demo_users table if not exists
	 */
	private static function migrate_to_1_0(): void {
		global $wpdb;
		
		$charset_collate = $wpdb->get_charset_collate();
		$table = $wpdb->prefix . 'cts_demo_users';
		
		$sql = "CREATE TABLE IF NOT EXISTS {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			email varchar(255) NOT NULL,
			first_name varchar(100) DEFAULT NULL,
			last_name varchar(100) DEFAULT NULL,
			name varchar(255) DEFAULT NULL,
			company varchar(255) DEFAULT NULL,
			organization varchar(255) DEFAULT NULL,
			purpose text DEFAULT NULL,
			verification_token varchar(64) NOT NULL,
			password_hash varchar(255) DEFAULT NULL,
			is_verified tinyint(1) DEFAULT 0,
			verified_at datetime DEFAULT NULL,
			wordpress_user_id bigint(20) unsigned DEFAULT NULL,
			last_login_at datetime DEFAULT NULL,
			expires_at datetime DEFAULT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY email (email),
			KEY is_verified (is_verified),
			KEY wordpress_user_id (wordpress_user_id)
		) $charset_collate;";
		
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
		
		error_log( '[ChurchTools Demo] Migration 1.0: demo_users table created/verified' );
	}
	
	/**
	 * Migration 1.1: Add user_id to views and presets (Multi-User Support)
	 * 
	 * Adds user_id column to plugin_cts_views and plugin_cts_shortcode_presets
	 * to enable per-user isolation of settings.
	 * 
	 * Note: Tables are created by main plugin, we just add columns.
	 * 
	 * @since 1.0.6.0
	 */
	private static function migrate_to_1_1(): void {
		global $wpdb;
		
		$prefix = $wpdb->prefix . 'cts_';
		
		// 1. Add user_id to views table (IF TABLE EXISTS)
		$views_table = $prefix . 'views';
		$views_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$views_table}'" );
		
		if ( $views_exists ) {
			$wpdb->suppress_errors();
			$columns = $wpdb->get_results( "SHOW COLUMNS FROM {$views_table} LIKE 'user_id'" );
			$wpdb->show_errors();
			
			if ( empty( $columns ) ) {
				$wpdb->query( "ALTER TABLE {$views_table} 
					ADD COLUMN user_id bigint(20) unsigned DEFAULT NULL AFTER id,
					ADD INDEX idx_user_id (user_id)
				" );
				
				error_log( '[ChurchTools Demo] Migration 1.1: Added user_id to views table' );
				
				// Assign existing views to admin user (or leave NULL for system views)
				$admin_user = get_users( [ 'role' => 'administrator', 'number' => 1 ] );
				if ( ! empty( $admin_user ) ) {
					$admin_id = $admin_user[0]->ID;
					$wpdb->query( $wpdb->prepare(
						"UPDATE {$views_table} SET user_id = %d WHERE user_id IS NULL",
						$admin_id
					) );
					error_log( sprintf(
						'[ChurchTools Demo] Migration 1.1: Assigned existing views to admin user (ID: %d)',
						$admin_id
					) );
				}
			} else {
				error_log( '[ChurchTools Demo] Migration 1.1: user_id column already exists in views table' );
			}
		} else {
			error_log( '[ChurchTools Demo] Migration 1.1: Views table not found (will be created by main plugin)' );
		}
		
		// 2. Add user_id to shortcode_presets table (IF TABLE EXISTS)
		$presets_table = $prefix . 'shortcode_presets';
		$presets_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$presets_table}'" );
		
		if ( $presets_exists ) {
			$wpdb->suppress_errors();
			$columns = $wpdb->get_results( "SHOW COLUMNS FROM {$presets_table} LIKE 'user_id'" );
			$wpdb->show_errors();
			
			if ( empty( $columns ) ) {
				$wpdb->query( "ALTER TABLE {$presets_table} 
					ADD COLUMN user_id bigint(20) unsigned DEFAULT NULL AFTER id,
					ADD INDEX idx_user_id (user_id)
				" );
				
				error_log( '[ChurchTools Demo] Migration 1.1: Added user_id to shortcode_presets table' );
				
				// System presets (is_system=1) stay with user_id=NULL
				// User presets get assigned to admin
				$admin_user = get_users( [ 'role' => 'administrator', 'number' => 1 ] );
				if ( ! empty( $admin_user ) ) {
					$admin_id = $admin_user[0]->ID;
					$wpdb->query( $wpdb->prepare(
						"UPDATE {$presets_table} SET user_id = %d WHERE user_id IS NULL AND is_system = 0",
						$admin_id
					) );
					error_log( sprintf(
						'[ChurchTools Demo] Migration 1.1: Assigned user presets to admin (ID: %d), system presets remain NULL',
						$admin_id
					) );
				}
			} else {
				error_log( '[ChurchTools Demo] Migration 1.1: user_id column already exists in shortcode_presets table' );
			}
		} else {
			error_log( '[ChurchTools Demo] Migration 1.1: Shortcode presets table not found (will be created by main plugin)' );
		}
		
		error_log( '[ChurchTools Demo] Migration 1.1: Multi-User isolation completed' );
	}
	
	/**
	 * Migration 1.2: Create isolated demo tables
	 * 
	 * Creates demo_cts_events, demo_cts_calendars, demo_cts_services tables
	 * with user_id column for true multi-user data isolation.
	 * 
	 * Each demo user has their own isolated events, calendars, and services.
	 * Main plugin tables remain single-user (admin only).
	 * 
	 * @since 1.0.7.0
	 */
	private static function migrate_to_1_2(): void {
		global $wpdb;
		
		$charset_collate = $wpdb->get_charset_collate();
		$prefix = $wpdb->prefix . 'demo_cts_';
		
		$sql = [];
		
		// Demo Events table - Same structure as wp_cts_events + user_id
		$sql[] = "CREATE TABLE IF NOT EXISTS {$prefix}events (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned NOT NULL,
			event_id varchar(100) DEFAULT NULL,
			calendar_id varchar(100) DEFAULT NULL,
			appointment_id varchar(100) NOT NULL,
			title varchar(500) NOT NULL,
			description text,
			event_description text DEFAULT NULL,
			appointment_description text DEFAULT NULL,
			start_datetime datetime NOT NULL,
			end_datetime datetime DEFAULT NULL,
			is_all_day tinyint(1) DEFAULT 0,
			location_name varchar(255) DEFAULT NULL,
			address_name varchar(255) DEFAULT NULL,
			address_street varchar(255) DEFAULT NULL,
			address_zip varchar(20) DEFAULT NULL,
			address_city varchar(255) DEFAULT NULL,
			address_latitude decimal(10,8) DEFAULT NULL,
			address_longitude decimal(11,8) DEFAULT NULL,
			tags longtext DEFAULT NULL,
			status varchar(50) DEFAULT NULL,
			image_attachment_id bigint(20) unsigned DEFAULT NULL,
			image_url varchar(500) DEFAULT NULL,
			raw_payload longtext DEFAULT NULL,
			last_modified datetime DEFAULT NULL,
			appointment_modified datetime DEFAULT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY idx_user_id (user_id),
			KEY idx_event_id (event_id),
			KEY idx_appointment_id (appointment_id),
			KEY calendar_id (calendar_id),
			KEY start_datetime (start_datetime),
			KEY user_start (user_id, start_datetime)
		) $charset_collate;";
		
		// Demo Calendars table - Same structure as wp_cts_calendars + user_id
		$sql[] = "CREATE TABLE IF NOT EXISTS {$prefix}calendars (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned NOT NULL,
			calendar_id varchar(100) NOT NULL,
			name varchar(255) NOT NULL,
			name_translated varchar(255) DEFAULT NULL,
			color varchar(20) DEFAULT NULL,
			calendar_image_id bigint(20) unsigned DEFAULT NULL,
			is_selected tinyint(1) DEFAULT 0,
			is_public tinyint(1) DEFAULT 0,
			sort_order int(11) DEFAULT 0,
			raw_payload longtext DEFAULT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY idx_user_id (user_id),
			KEY idx_calendar_id (calendar_id),
			KEY user_calendar (user_id, calendar_id),
			KEY is_selected (is_selected)
		) $charset_collate;";
		
		// Demo Services table - Same structure as wp_cts_services + user_id
		$sql[] = "CREATE TABLE IF NOT EXISTS {$prefix}services (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned NOT NULL,
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
			KEY idx_user_id (user_id),
			KEY idx_service_id (service_id),
			KEY user_service (user_id, service_id),
			KEY service_group_id (service_group_id),
			KEY is_selected (is_selected)
		) $charset_collate;";
		
		// Demo Event Services table - Same structure as wp_cts_event_services + user_id
		$sql[] = "CREATE TABLE IF NOT EXISTS {$prefix}event_services (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned NOT NULL,
			event_id bigint(20) unsigned NOT NULL,
			service_id varchar(100) DEFAULT NULL,
			service_name varchar(255) DEFAULT NULL,
			person_name varchar(255) DEFAULT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY idx_user_id (user_id),
			KEY event_id (event_id),
			KEY user_event (user_id, event_id)
		) $charset_collate;";
		
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		foreach ( $sql as $query ) {
			dbDelta( $query );
		}
		
		error_log( '[ChurchTools Demo] Migration 1.2: Isolated demo tables created (demo_cts_events, demo_cts_calendars, demo_cts_services, demo_cts_event_services)' );
	}
	
	/**
	 * Migration 1.3: Migrate existing demo users to new role system
	 * 
	 * - Migrates users from old 'cts_demo_user' role to new 'demo_tester' role
	 * - Ensures all demo_tester users have correct capabilities
	 * - Cleans up old role if it exists
	 * 
	 * @since 1.1.1.1
	 */
	private static function migrate_to_1_3(): void {
		// 1. Migrate users from old role 'cts_demo_user' to 'demo_tester'
		$old_users = get_users( [ 'role' => 'cts_demo_user' ] );
		$migrated_count = 0;
		
		foreach ( $old_users as $user ) {
			$user->set_role( 'demo_tester' );
			$migrated_count++;
			error_log( sprintf(
				'[ChurchTools Demo] Migration 1.3: Migrated user %s (ID: %d) from cts_demo_user to demo_tester',
				$user->user_login,
				$user->ID
			) );
		}
		
		if ( $migrated_count > 0 ) {
			error_log( sprintf(
				'[ChurchTools Demo] Migration 1.3: Migrated %d users from cts_demo_user to demo_tester',
				$migrated_count
			) );
		}
		
		// 2. Remove old role if it exists
		$old_role = get_role( 'cts_demo_user' );
		if ( $old_role ) {
			remove_role( 'cts_demo_user' );
			error_log( '[ChurchTools Demo] Migration 1.3: Removed old cts_demo_user role' );
		}
		
		// 3. Ensure all demo_tester users have correct capabilities
		// Note: ensure_demo_user_role() runs on every init, so capabilities are auto-fixed
		$demo_users = get_users( [ 'role' => 'demo_tester' ] );
		if ( count( $demo_users ) > 0 ) {
			error_log( sprintf(
				'[ChurchTools Demo] Migration 1.3: Found %d demo_tester users (capabilities will be synced on next init)',
				count( $demo_users )
			) );
		}
		
		error_log( '[ChurchTools Demo] Migration 1.3: User role migration completed' );
	}
	
	/**
	 * Get current database version
	 */
	public static function get_current_version(): string {
		return get_option( self::DB_VERSION_KEY, '0.0' );
	}
	
	/**
	 * Check if migrations are pending
	 */
	public static function has_pending_migrations(): bool {
		$current_version = self::get_current_version();
		return version_compare( $current_version, self::DB_VERSION, '<' );
	}
}
