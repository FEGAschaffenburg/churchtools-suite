<?php
/**
 * Database Migration Manager
 * 
 * Handles versioned database migrations that run automatically on plugin load.
 * Each migration runs only once and is tracked via DB version in wp_options.
 *
 * v0.9.0 Clean Slate: Simplified to only essential table creation
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
	const DB_VERSION = '1.5';
	
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
	 * Creates all tables required for plugin operation.
	 * This migration is safe to run multiple times (CREATE TABLE IF NOT EXISTS).
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
			   calendar_image_id bigint(20) unsigned DEFAULT NULL,
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
		
		// Events table (v0.9.0: COMPOSITE UNIQUE KEY for appointment + datetime)
		$sql[] = "CREATE TABLE IF NOT EXISTS {$prefix}events (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
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
			UNIQUE KEY appointment_datetime (appointment_id, start_datetime),
			KEY idx_event_id (event_id),
			KEY calendar_id (calendar_id),
			KEY start_datetime (start_datetime),
			KEY last_modified (last_modified),
			KEY appointment_modified (appointment_modified)
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
		
		// Sync History table
		$sql[] = "CREATE TABLE IF NOT EXISTS {$prefix}sync_history (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			sync_type varchar(20) NOT NULL DEFAULT 'auto',
			status varchar(20) NOT NULL DEFAULT 'pending',
			calendars_processed int(11) DEFAULT 0,
			events_found int(11) DEFAULT 0,
			events_inserted int(11) DEFAULT 0,
			events_updated int(11) DEFAULT 0,
			events_skipped int(11) DEFAULT 0,
			services_imported int(11) DEFAULT 0,
			events_unchanged int(11) DEFAULT 0,
			events_deleted int(11) DEFAULT 0,
			error_message text DEFAULT NULL,
			started_at datetime NOT NULL,
			completed_at datetime DEFAULT NULL,
			duration_seconds int(11) DEFAULT NULL,
			PRIMARY KEY (id),
			KEY sync_type (sync_type),
			KEY status (status),
			KEY started_at (started_at)
		) $charset_collate;";
		
		// Services table
		$sql[] = "CREATE TABLE IF NOT EXISTS {$prefix}services (
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
		
		// Service Groups table
		$sql[] = "CREATE TABLE IF NOT EXISTS {$prefix}service_groups (
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
		
		// Shortcode Presets table
		$sql[] = "CREATE TABLE IF NOT EXISTS {$prefix}shortcode_presets (
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
		foreach ( $sql as $query ) {
			dbDelta( $query );
		}
		
		// Create system presets
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-shortcode-presets-repository.php';
		$presets_repo = new ChurchTools_Suite_Shortcode_Presets_Repository();
		$presets_repo->create_system_presets();
	}
	
	/**
	 * Migration 1.1: Add image support to events table
	 * 
	 * Adds image_attachment_id and image_url columns for local image storage
	 * 
	 * @since 0.10.5.0
	 */
	private static function migrate_to_1_1(): void {
		global $wpdb;
		
		$charset_collate = $wpdb->get_charset_collate();
		$prefix = $wpdb->prefix . CHURCHTOOLS_SUITE_DB_PREFIX;
		$events_table = $prefix . 'events';
		
		// Check if columns already exist
		$wpdb->suppress_errors();
		$columns = $wpdb->get_results( "SHOW COLUMNS FROM {$events_table} LIKE 'image_attachment_id'" );
		$wpdb->show_errors();
		
		if ( empty( $columns ) ) {
			// Add image columns after status
			$wpdb->query( "ALTER TABLE {$events_table} 
				ADD COLUMN image_attachment_id bigint(20) unsigned DEFAULT NULL AFTER status,
				ADD COLUMN image_url varchar(500) DEFAULT NULL AFTER image_attachment_id
			" );
			
			if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
				ChurchTools_Suite_Logger::log( 'migrations', 'Added image columns to events table', [
					'table' => $events_table,
				] );
			}
		}
	}
	
	/**
	 * Migration 1.2: Add calendar_image_id to calendars table
	 *
	 * @since 0.9.9.58
	 */
	private static function migrate_to_1_2(): void {
		global $wpdb;
		$prefix = $wpdb->prefix . CHURCHTOOLS_SUITE_DB_PREFIX;
		$table = $prefix . 'calendars';
		$wpdb->suppress_errors();
		$columns = $wpdb->get_results( "SHOW COLUMNS FROM {$table} LIKE 'calendar_image_id'" );
		$wpdb->show_errors();
		if ( empty( $columns ) ) {
			$wpdb->query( "ALTER TABLE {$table} ADD COLUMN calendar_image_id bigint(20) unsigned DEFAULT NULL AFTER color" );
			if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
				ChurchTools_Suite_Logger::log( 'migrations', 'Added calendar_image_id to calendars table', [ 'table' => $table ] );
			}
		}
	}
	
	/**
	 * Migration 1.3: Clean up old files from v1.0.6 refactoring
	 *
	 * Removes files that were moved during CSS/JS consolidation:
	 * - admin/css/churchtools-suite-admin.css (moved to assets/css/)
	 * - Empty admin/css/ directory
	 *
	 * @since 1.0.7.0
	 */
	private static function migrate_to_1_3(): void {
		$cleanup_items = [];
		
		// Old admin CSS file (moved to assets/css/ in v1.0.7.0)
		$old_admin_css = CHURCHTOOLS_SUITE_PATH . 'admin/css/churchtools-suite-admin.css';
		if ( file_exists( $old_admin_css ) ) {
			if ( @unlink( $old_admin_css ) ) {
				$cleanup_items[] = 'admin/css/churchtools-suite-admin.css';
			}
		}
		
		// Remove empty admin/css/ directory
		$old_admin_css_dir = CHURCHTOOLS_SUITE_PATH . 'admin/css';
		if ( is_dir( $old_admin_css_dir ) ) {
			$files = @scandir( $old_admin_css_dir );
			// Check if directory is empty (only . and .. entries)
			if ( $files && count( $files ) === 2 ) {
				if ( @rmdir( $old_admin_css_dir ) ) {
					$cleanup_items[] = 'admin/css/ (empty directory)';
				}
			}
		}
		
		// Log cleanup results
		if ( class_exists( 'ChurchTools_Suite_Logger' ) && ! empty( $cleanup_items ) ) {
			ChurchTools_Suite_Logger::log( 'migrations', 'Cleaned up old files from v1.0.6 refactoring', [
				'removed_files' => $cleanup_items,
				'migration' => '1.3',
			] );
		}
	}

	/**
	 * Migration 1.4: Migrate stored view IDs in Gutenberg + Elementor content
	 *
	 * Converts legacy/english view IDs to current canonical IDs to keep existing
	 * blocks/widgets working after template/view refactoring.
	 *
	 * @since 1.1.4.5
	 */
	private static function migrate_to_1_4(): void {
		$gutenberg_updated = self::migrate_gutenberg_block_views();
		$elementor_updated = self::migrate_elementor_widget_views();

		if ( $gutenberg_updated > 0 || $elementor_updated > 0 ) {
			update_option( 'churchtools_suite_view_migration_notice', [
				'gutenberg' => (int) $gutenberg_updated,
				'elementor' => (int) $elementor_updated,
				'timestamp' => time(),
			], false );
		}

		if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
			ChurchTools_Suite_Logger::log( 'migrations', 'Migrated stored block/widget view IDs', [
				'migration' => '1.4',
				'gutenberg_posts_updated' => $gutenberg_updated,
				'elementor_posts_updated' => $elementor_updated,
			] );
		}
	}

	/**
	 * Migration 1.5: Re-run view-ID migration to cover already-updated 1.4 installs.
	 *
	 * @since 1.1.4.6
	 */
	private static function migrate_to_1_5(): void {
		self::migrate_to_1_4();
	}

	/**
	 * Migrate Gutenberg block attributes in post_content.
	 *
	 * @return int Number of updated posts
	 */
	private static function migrate_gutenberg_block_views(): int {
		if ( ! function_exists( 'parse_blocks' ) || ! function_exists( 'serialize_blocks' ) ) {
			return 0;
		}

		global $wpdb;
		$needle = '%' . $wpdb->esc_like( 'wp:churchtools-suite/events' ) . '%';
		$post_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts} WHERE post_content LIKE %s AND post_status NOT IN ('auto-draft','trash')",
				$needle
			)
		);

		if ( empty( $post_ids ) ) {
			return 0;
		}

		$updated = 0;

		foreach ( $post_ids as $post_id ) {
			$post_id = (int) $post_id;
			$content = get_post_field( 'post_content', $post_id );
			if ( ! is_string( $content ) || $content === '' ) {
				continue;
			}

			$blocks = parse_blocks( $content );
			if ( empty( $blocks ) || ! is_array( $blocks ) ) {
				continue;
			}

			$changed = false;
			$blocks = self::migrate_gutenberg_blocks_recursive( $blocks, $changed );

			if ( ! $changed ) {
				continue;
			}

			$new_content = serialize_blocks( $blocks );
			if ( $new_content === $content ) {
				continue;
			}

			wp_update_post( [
				'ID' => $post_id,
				'post_content' => $new_content,
			] );
			clean_post_cache( $post_id );
			$updated++;
		}

		return $updated;
	}

	/**
	 * Recursively migrate Gutenberg blocks.
	 *
	 * @param array $blocks  Parsed blocks
	 * @param bool  $changed Whether changes were made
	 * @return array
	 */
	private static function migrate_gutenberg_blocks_recursive( array $blocks, bool &$changed ): array {
		foreach ( $blocks as &$block ) {
			if ( isset( $block['blockName'] ) && $block['blockName'] === 'churchtools-suite/events' ) {
				$attrs = ( isset( $block['attrs'] ) && is_array( $block['attrs'] ) ) ? $block['attrs'] : [];

				$raw_view_type = isset( $attrs['viewType'] ) ? $attrs['viewType'] : ( $attrs['view_type'] ?? 'list' );
				$view_type = self::normalize_view_type( (string) $raw_view_type );
				if ( ! isset( $attrs['viewType'] ) || $attrs['viewType'] !== $view_type ) {
					$attrs['viewType'] = $view_type;
					$changed = true;
				}

				$current_view = isset( $attrs['view'] ) ? (string) $attrs['view'] : '';
				$normalized_view = self::normalize_view_for_type( $view_type, $current_view );
				if ( $normalized_view !== $current_view ) {
					$attrs['view'] = $normalized_view;
					$changed = true;
				}

				if ( isset( $attrs['view_type'] ) ) {
					unset( $attrs['view_type'] );
					$changed = true;
				}

				$block['attrs'] = $attrs;
			}

			if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
				$block['innerBlocks'] = self::migrate_gutenberg_blocks_recursive( $block['innerBlocks'], $changed );
			}
		}
		unset( $block );

		return $blocks;
	}

	/**
	 * Migrate Elementor widget settings in _elementor_data postmeta.
	 *
	 * @return int Number of updated posts
	 */
	private static function migrate_elementor_widget_views(): int {
		global $wpdb;
		$needle = '%' . $wpdb->esc_like( 'churchtools_suite_events' ) . '%';

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_elementor_data' AND meta_value LIKE %s",
				$needle
			),
			ARRAY_A
		);

		if ( empty( $rows ) ) {
			return 0;
		}

		$updated = 0;

		foreach ( $rows as $row ) {
			$post_id = (int) $row['post_id'];
			$elements = json_decode( (string) $row['meta_value'], true );
			if ( ! is_array( $elements ) ) {
				continue;
			}

			$changed = false;
			$elements = self::migrate_elementor_elements_recursive( $elements, $changed );

			if ( ! $changed ) {
				continue;
			}

			update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $elements ) ) );
			clean_post_cache( $post_id );
			$updated++;
		}

		return $updated;
	}

	/**
	 * Recursively migrate Elementor elements.
	 *
	 * @param array $elements Elementor elements tree
	 * @param bool  $changed  Whether changes were made
	 * @return array
	 */
	private static function migrate_elementor_elements_recursive( array $elements, bool &$changed ): array {
		foreach ( $elements as &$element ) {
			if ( ! is_array( $element ) ) {
				continue;
			}

			if ( isset( $element['widgetType'] ) && $element['widgetType'] === 'churchtools_suite_events' ) {
				$settings = ( isset( $element['settings'] ) && is_array( $element['settings'] ) ) ? $element['settings'] : [];

				$view_type = self::normalize_view_type( (string) ( $settings['view_type'] ?? 'list' ) );
				if ( ! isset( $settings['view_type'] ) || $settings['view_type'] !== $view_type ) {
					$settings['view_type'] = $view_type;
					$changed = true;
				}

				$view_keys = [
					'list' => 'view_list',
					'grid' => 'view_grid',
					'calendar' => 'view_calendar',
					'countdown' => 'view_countdown',
					'carousel' => 'view_carousel',
				];

				foreach ( $view_keys as $type => $key ) {
					if ( isset( $settings[ $key ] ) ) {
						$normalized = self::normalize_view_for_type( $type, (string) $settings[ $key ] );
						if ( $normalized !== $settings[ $key ] ) {
							$settings[ $key ] = $normalized;
							$changed = true;
						}
					}
				}

				if ( isset( $settings['view'] ) ) {
					$normalized_fallback = self::normalize_view_for_type( $view_type, (string) $settings['view'] );
					if ( $normalized_fallback !== $settings['view'] ) {
						$settings['view'] = $normalized_fallback;
						$changed = true;
					}
				}

				$element['settings'] = $settings;
			}

			if ( ! empty( $element['elements'] ) && is_array( $element['elements'] ) ) {
				$element['elements'] = self::migrate_elementor_elements_recursive( $element['elements'], $changed );
			}
		}
		unset( $element );

		return $elements;
	}

	/**
	 * Normalize view type.
	 *
	 * @param string $view_type Raw view type
	 * @return string
	 */
	private static function normalize_view_type( string $view_type ): string {
		$allowed = [ 'list', 'grid', 'calendar', 'countdown', 'carousel' ];
		$view_type = strtolower( trim( $view_type ) );
		return in_array( $view_type, $allowed, true ) ? $view_type : 'list';
	}

	/**
	 * Normalize legacy/new/english/german view IDs to canonical IDs.
	 *
	 * @param string $view_type View type
	 * @param string $view      Raw view value
	 * @return string
	 */
	private static function normalize_view_for_type( string $view_type, string $view ): string {
		$view = strtolower( trim( $view ) );

		$mapping = [
			'list' => [
				'classic' => 'list-klassisch',
				'klassisch' => 'list-klassisch',
				'list-classic' => 'list-klassisch',
				'list-klassisch' => 'list-klassisch',
				'classic-with-images' => 'list-klassisch-mit-bildern',
				'list-classic-with-images' => 'list-klassisch-mit-bildern',
				'list-klassisch-mit-bildern' => 'list-klassisch-mit-bildern',
				'classic-modern' => 'list-klassisch-modern',
				'list-classic-modern' => 'list-klassisch-modern',
				'list-klassisch-modern' => 'list-klassisch-modern',
				'minimal' => 'list-minimal',
				'list-minimal' => 'list-minimal',
				'modern' => 'list-modern',
				'list-modern' => 'list-modern',
			],
			'grid' => [
				'classic' => 'grid-klassisch',
				'klassisch' => 'grid-klassisch',
				'grid-classic' => 'grid-klassisch',
				'grid-klassisch' => 'grid-klassisch',
				'simple' => 'grid-einfach',
				'einfach' => 'grid-einfach',
				'grid-simple' => 'grid-einfach',
				'grid-einfach' => 'grid-einfach',
				'minimal' => 'grid-minimal',
				'grid-minimal' => 'grid-minimal',
				'modern' => 'grid-modern',
				'grid-modern' => 'grid-modern',
				'background-images' => 'grid-hintergrundbilder',
				'grid-background-images' => 'grid-hintergrundbilder',
				'grid-hintergrundbilder' => 'grid-hintergrundbilder',
			],
			'calendar' => [
				'monthly-simple' => 'monthly-simple',
				'calendar-monthly-simple' => 'monthly-simple',
				'calendar-monatlich-einfach' => 'monthly-simple',
			],
			'countdown' => [
				'classic' => 'countdown-klassisch',
				'countdown-classic' => 'countdown-klassisch',
				'countdown-klassisch' => 'countdown-klassisch',
			],
			'carousel' => [
				'classic' => 'carousel-klassisch',
				'carousel-classic' => 'carousel-klassisch',
				'carousel-klassisch' => 'carousel-klassisch',
			],
		];

		if ( isset( $mapping[ $view_type ][ $view ] ) ) {
			return $mapping[ $view_type ][ $view ];
		}

		return $view;
	}
	
	/**
	 * Get current database version
	 * 
	 * @return string Current DB version (e.g., '1.0')
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
