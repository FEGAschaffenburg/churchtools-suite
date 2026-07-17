<?php
/**
 * Demo Services Repository
 *
 * Manages isolated demo services per user in demo_cts_services table
 *
 * @package ChurchTools_Suite_Demo
 * @since   1.0.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ChurchTools_Suite_Demo_Services_Repository {
	
	/**
	 * Database instance
	 *
	 * @var wpdb
	 */
	protected $db;
	
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table_name;
	
	/**
	 * User ID for isolation
	 *
	 * @var int
	 */
	protected $user_id;
	
	/**
	 * Constructor
	 *
	 * @param int $user_id WordPress user ID for isolation
	 */
	public function __construct( int $user_id ) {
		global $wpdb;
		$this->db = $wpdb;
		$this->table_name = $wpdb->prefix . 'demo_cts_services';
		$this->user_id = $user_id;
	}
	
	/**
	 * Get all services for current user
	 *
	 * @return array
	 */
	public function get_all(): array {
		$results = $this->db->get_results(
			$this->db->prepare(
				"SELECT * FROM {$this->table_name} WHERE user_id = %d ORDER BY sort_order ASC, name ASC",
				$this->user_id
			)
		);
		
		return is_array( $results ) ? $results : [];
	}
	
	/**
	 * Get selected service IDs for current user
	 *
	 * @return array
	 */
	public function get_selected_service_ids(): array {
		$results = $this->db->get_col(
			$this->db->prepare(
				"SELECT service_id FROM {$this->table_name} WHERE user_id = %d AND is_selected = 1",
				$this->user_id
			)
		);
		
		return is_array( $results ) ? $results : [];
	}
	
	/**
	 * Get service by service_id (with user isolation)
	 *
	 * @param string $service_id ChurchTools service ID
	 * @return object|null
	 */
	public function get_by_service_id( string $service_id ) {
		return $this->db->get_row(
			$this->db->prepare(
				"SELECT * FROM {$this->table_name} WHERE service_id = %s AND user_id = %d",
				$service_id,
				$this->user_id
			)
		);
	}
	
	/**
	 * Insert or Update service (with user isolation)
	 *
	 * @param array $data Service data
	 * @return int|false Service ID or false
	 */
	public function upsert( array $data ) {
		$defaults = [
			'service_id' => null,
			'service_group_id' => null,
			'name' => '',
			'name_translated' => null,
			'is_selected' => 0,
			'sort_order' => 0,
			'raw_payload' => null,
		];
		$data = wp_parse_args( $data, $defaults );
		
		// service_id required
		if ( empty( $data['service_id'] ) ) {
			return false;
		}
		
		// Add user_id
		$data['user_id'] = $this->user_id;
		
		// Check if exists
		$existing_id = $this->db->get_var(
			$this->db->prepare(
				"SELECT id FROM {$this->table_name} WHERE service_id = %s AND user_id = %d",
				$data['service_id'],
				$this->user_id
			)
		);
		
		if ( $existing_id ) {
			// Update existing
			$result = $this->db->update(
				$this->table_name,
				$data,
				[
					'id' => $existing_id,
					'user_id' => $this->user_id,
				],
				[
					'%s', // service_id
					'%s', // service_group_id
					'%s', // name
					'%s', // name_translated
					'%d', // is_selected
					'%d', // sort_order
					'%s', // raw_payload
					'%d', // user_id
				],
				[ '%d', '%d' ] // WHERE id, user_id
			);
			
			return $result !== false ? $existing_id : false;
		} else {
			// Insert new
			$result = $this->db->insert(
				$this->table_name,
				$data,
				[
					'%s', // service_id
					'%s', // service_group_id
					'%s', // name
					'%s', // name_translated
					'%d', // is_selected
					'%d', // sort_order
					'%s', // raw_payload
					'%d', // user_id
				]
			);
			
			return $result !== false ? $this->db->insert_id : false;
		}
	}
	
	/**
	 * Delete all services for current user
	 *
	 * @return int Number of rows deleted
	 */
	public function delete_all(): int {
		return $this->db->delete(
			$this->table_name,
			[ 'user_id' => $this->user_id ],
			[ '%d' ]
		);
	}
	
	/**
	 * Get service count for current user
	 *
	 * @return int
	 */
	public function count(): int {
		return (int) $this->db->get_var(
			$this->db->prepare(
				"SELECT COUNT(*) FROM {$this->table_name} WHERE user_id = %d",
				$this->user_id
			)
		);
	}
}
