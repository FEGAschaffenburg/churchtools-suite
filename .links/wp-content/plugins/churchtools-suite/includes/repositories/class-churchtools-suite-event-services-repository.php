<?php
/**
 * Event Services Repository
 *
 * Manages event services (e.g., Predigt, Moderation, Musik)
 * Links services to events and tracks person assignments.
 *
 * @package ChurchTools_Suite
 * @since   0.3.10.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ChurchTools_Suite_Event_Services_Repository extends ChurchTools_Suite_Repository_Base {
	
	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct( CHURCHTOOLS_SUITE_DB_PREFIX . 'event_services' );
	}
	
	/**
	 * Insert or update a service
	 *
	 * Uses event_id + service_name as natural key for upsert.
	 * If service exists, updates it. Otherwise inserts new.
	 *
	 * @param array $data {
	 *     Service data
	 *
	 *     @type int    $event_id      Local event ID (required)
	 *     @type string $service_id    ChurchTools service ID
	 *     @type string $service_name  Service name (required)
	 *     @type string $person_name   Person assigned to service
	 * }
	 * @return int|false Service ID on success, false on failure
	 */
	public function upsert( array $data ) {
		global $wpdb;
		
		// Validate required fields
		if ( empty( $data['event_id'] ) || empty( $data['service_name'] ) ) {
			return false;
		}
		
		$table = $wpdb->prefix . $this->table;
		
		// Check if service already exists for this event
		$existing = $wpdb->get_row( $wpdb->prepare(
			"SELECT id FROM {$table} WHERE event_id = %d AND service_name = %s",
			$data['event_id'],
			$data['service_name']
		) );
		
		$defaults = [
			'service_id' => null,
			'person_name' => null,
		];
		$data = wp_parse_args( $data, $defaults );
		
		// Prepare data
		$db_data = [
			'event_id' => $data['event_id'],
			'service_id' => $data['service_id'],
			'service_name' => $data['service_name'],
			'person_name' => $data['person_name'],
			'updated_at' => current_time( 'mysql' ),
		];
		
		$format = [ '%d', '%s', '%s', '%s', '%s' ];
		
		if ( $existing ) {
			// Update existing service
			$result = $wpdb->update(
				$table,
				$db_data,
				[ 'id' => $existing->id ],
				$format,
				[ '%d' ]
			);
			
			return $result !== false ? $existing->id : false;
		} else {
			// Insert new service
			$db_data['created_at'] = current_time( 'mysql' );
			$format[] = '%s';
			
			$result = $wpdb->insert( $table, $db_data, $format );
			
			return $result ? $wpdb->insert_id : false;
		}
	}
	
	/**
	 * Get all services for an event
	 *
	 * @param int $event_id Local event ID
	 * @return array Array of service objects
	 */
	public function get_for_event( int $event_id ): array {
		global $wpdb;
		
		$table = $wpdb->prefix . $this->table;
		
		$services = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM {$table} WHERE event_id = %d ORDER BY service_name ASC",
			$event_id
		) );
		
		return $services ?: [];
	}
	
	/**
	 * Alias for get_for_event() - for consistency with other repositories
	 *
	 * @param int $event_id Local event ID
	 * @return array Array of service objects
	 */
	public function get_by_event_id( int $event_id ): array {
		return $this->get_for_event( $event_id );
	}
	
	/**
	 * Delete all services for an event
	 *
	 * Used when an event is deleted to clean up orphaned services.
	 *
	 * @param int $event_id Local event ID
	 * @return int|false Number of rows deleted or false on failure
	 */
	public function delete_for_event( int $event_id ) {
		global $wpdb;
		
		$table = $wpdb->prefix . $this->table;
		
		return $wpdb->delete(
			$table,
			[ 'event_id' => $event_id ],
			[ '%d' ]
		);
	}
	
	/**
	 * Get all unique service names
	 *
	 * Returns a list of all service names that have been imported.
	 * Useful for statistics and filter UI.
	 *
	 * @return array Array of service names
	 */
	public function get_unique_service_names(): array {
		global $wpdb;
		
		$table = $wpdb->prefix . $this->table;
		
		$names = $wpdb->get_col(
			"SELECT DISTINCT service_name 
			FROM {$table} 
			WHERE service_name IS NOT NULL 
			ORDER BY service_name ASC"
		);
		
		return $names ?: [];
	}
	
	/**
	 * Get service count by service name
	 *
	 * Returns statistics about how often each service is used.
	 *
	 * @param int $limit Maximum number of results
	 * @return array Array of objects with service_name and count
	 */
	public function get_service_stats( int $limit = 20 ): array {
		global $wpdb;
		
		$table = $wpdb->prefix . $this->table;
		
		$stats = $wpdb->get_results( $wpdb->prepare(
			"SELECT service_name, COUNT(*) as count 
			FROM {$table} 
			WHERE service_name IS NOT NULL 
			GROUP BY service_name 
			ORDER BY count DESC 
			LIMIT %d",
			$limit
		) );
		
		return $stats ?: [];
	}
	
	/**
	 * Get total service count
	 *
	 * @return int Total number of services
	 */
	public function get_total_count(): int {
		global $wpdb;
		
		$table = $wpdb->prefix . $this->table;
		
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
		
		return (int) $count;
	}
	
	/**
	 * Get services by service name
	 *
	 * @param string $service_name Service name to filter by
	 * @param int    $limit        Maximum number of results
	 * @return array Array of service objects
	 */
	public function get_by_service_name( string $service_name, int $limit = 100 ): array {
		global $wpdb;
		
		$table = $wpdb->prefix . $this->table;
		
		$services = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM {$table} 
			WHERE service_name = %s 
			ORDER BY created_at DESC 
			LIMIT %d",
			$service_name,
			$limit
		) );
		
		return $services ?: [];
	}
	
	/**
	 * Search services by person name
	 *
	 * @param string $person_name Person name to search for (partial match)
	 * @param int    $limit       Maximum number of results
	 * @return array Array of service objects
	 */
	public function search_by_person( string $person_name, int $limit = 100 ): array {
		global $wpdb;
		
		$table = $wpdb->prefix . $this->table;
		
		$search_term = '%' . $wpdb->esc_like( $person_name ) . '%';
		
		$services = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM {$table} 
			WHERE person_name LIKE %s 
			ORDER BY created_at DESC 
			LIMIT %d",
			$search_term,
			$limit
		) );
		
		return $services ?: [];
	}
	
	/**
	 * Delete all services
	 *
	 * Used for cleanup or reset. Dangerous operation!
	 *
	 * @return int|false Number of rows deleted or false on failure
	 */
	public function delete_all() {
		global $wpdb;
		
		$table = $wpdb->prefix . $this->table;
		
		return $wpdb->query( "DELETE FROM {$table}" );
	}
}
