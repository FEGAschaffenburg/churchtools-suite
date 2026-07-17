<?php
/**
 * Service Groups Repository
 *
 * Manages ChurchTools service group master data (e.g., Programm, Musik, Technik).
 * Users can select which service groups to sync, then only services from
 * selected groups will be imported.
 *
 * @package ChurchTools_Suite
 * @since   0.3.11.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ChurchTools_Suite_Service_Groups_Repository extends ChurchTools_Suite_Repository_Base {
	
	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct( CHURCHTOOLS_SUITE_DB_PREFIX . 'service_groups' );
	}
	
	/**
	 * Insert or update a service group
	 *
	 * Uses service_group_id as unique key for upsert.
	 *
	 * @param array $data {
	 *     Service group data
	 *
	 *     @type string $service_group_id ChurchTools service group ID (required)
	 *     @type string $name             Service group name (required)
	 *     @type bool   $is_selected      Whether group is selected for sync
	 *     @type int    $sort_order       Sort order
	 *     @type bool   $view_all         View all flag
	 *     @type string $raw_payload      Raw API response (JSON)
	 * }
	 * @return int|false Service group ID on success, false on failure
	 */
	public function upsert( array $data ) {
		global $wpdb;
		
		// Validate required fields
		if ( empty( $data['service_group_id'] ) || empty( $data['name'] ) ) {
			return false;
		}
		
		$table = $wpdb->prefix . $this->table;
		
		// Check if service group already exists
		$existing = $wpdb->get_row( $wpdb->prepare(
			"SELECT id FROM {$table} WHERE service_group_id = %s",
			$data['service_group_id']
		) );
		
		$defaults = [
			'is_selected' => 0,
			'sort_order' => 0,
			'view_all' => 0,
			'raw_payload' => null,
		];
		$data = wp_parse_args( $data, $defaults );
		
		// Prepare data
		$db_data = [
			'service_group_id' => $data['service_group_id'],
			'name' => $data['name'],
			'is_selected' => (int) $data['is_selected'],
			'sort_order' => (int) $data['sort_order'],
			'view_all' => (int) $data['view_all'],
			'raw_payload' => $data['raw_payload'],
			'updated_at' => current_time( 'mysql' ),
		];
		
		$format = [ '%s', '%s', '%d', '%d', '%d', '%s', '%s' ];
		
		if ( $existing ) {
			// Update existing group (preserve is_selected!)
			unset( $db_data['is_selected'] ); // Don't overwrite user selection
			$format = [ '%s', '%s', '%d', '%d', '%s', '%s' ];
			
			$result = $wpdb->update(
				$table,
				$db_data,
				[ 'id' => $existing->id ],
				$format,
				[ '%d' ]
			);
			
			return $result !== false ? $existing->id : false;
		} else {
			// Insert new group
			$db_data['created_at'] = current_time( 'mysql' );
			$format[] = '%s';
			
			$result = $wpdb->insert( $table, $db_data, $format );
			
			return $result ? $wpdb->insert_id : false;
		}
	}
	
	/**
	 * Get all selected service groups
	 *
	 * @return array Array of service group objects
	 */
	public function get_selected(): array {
		global $wpdb;
		
		$table = $wpdb->prefix . $this->table;
		
		$groups = $wpdb->get_results(
			"SELECT * FROM {$table} 
			WHERE is_selected = 1 
			ORDER BY sort_order ASC, name ASC"
		);
		
		return $groups ?: [];
	}
	
	/**
	 * Get selected service group IDs
	 *
	 * Returns array of service_group_id strings for easy checking.
	 *
	 * @return array Array of service_group_id strings
	 */
	public function get_selected_group_ids(): array {
		global $wpdb;
		
		$table = $wpdb->prefix . $this->table;
		
		$ids = $wpdb->get_col(
			"SELECT service_group_id FROM {$table} 
			WHERE is_selected = 1"
		);
		
		return $ids ?: [];
	}
	
	/**
	 * Update service group selection
	 *
	 * Sets is_selected flag for multiple groups at once.
	 *
	 * @param array $selected_ids Array of service_group_id strings to select
	 * @return bool True on success
	 */
	public function update_selection( array $selected_ids ): bool {
		global $wpdb;
		
		$table = $wpdb->prefix . $this->table;
		
		// First, deselect all
		$wpdb->query( "UPDATE {$table} SET is_selected = 0" );
		
		// Then select specified ones
		if ( ! empty( $selected_ids ) ) {
			$placeholders = implode( ',', array_fill( 0, count( $selected_ids ), '%s' ) );
			$query = $wpdb->prepare(
				"UPDATE {$table} SET is_selected = 1 WHERE service_group_id IN ({$placeholders})",
				...$selected_ids
			);
			$wpdb->query( $query );
		}
		
		return true;
	}
	
	/**
	 * Get service group by ChurchTools service_group_id
	 *
	 * @param string $service_group_id ChurchTools service group ID
	 * @return object|null Service group object or null
	 */
	public function get_by_service_group_id( string $service_group_id ): ?object {
		global $wpdb;
		
		$table = $wpdb->prefix . $this->table;
		
		$group = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$table} WHERE service_group_id = %s",
			$service_group_id
		) );
		
		return $group ?: null;
	}
	
	/**
	 * Get total service group count
	 *
	 * @return int Total number of service groups
	 */
	public function get_total_count(): int {
		global $wpdb;
		
		$table = $wpdb->prefix . $this->table;
		
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
		
		return (int) $count;
	}
	
	/**
	 * Get selected service group count
	 *
	 * @return int Number of selected service groups
	 */
	public function get_selected_count(): int {
		global $wpdb;
		
		$table = $wpdb->prefix . $this->table;
		
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE is_selected = 1" );
		
		return (int) $count;
	}
	
	/**
	 * Delete all service groups
	 *
	 * Used for cleanup or resync. Dangerous operation!
	 *
	 * @return int|false Number of rows deleted or false on failure
	 */
	public function delete_all() {
		global $wpdb;
		
		$table = $wpdb->prefix . $this->table;
		
		return $wpdb->query( "DELETE FROM {$table}" );
	}
}
