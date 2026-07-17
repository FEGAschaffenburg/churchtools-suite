<?php
/**
 * Demo Events Repository
 *
 * Manages isolated demo events per user in demo_cts_events table
 *
 * @package ChurchTools_Suite_Demo
 * @since   1.0.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ChurchTools_Suite_Demo_Events_Repository {
	
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
		$this->table_name = $wpdb->prefix . 'demo_cts_events';
		$this->user_id = $user_id;
	}
	
	/**
	 * Get all events for current user
	 *
	 * @return array
	 */
	public function get_all(): array {
		$results = $this->db->get_results(
			$this->db->prepare(
				"SELECT * FROM {$this->table_name} WHERE user_id = %d ORDER BY start_datetime ASC",
				$this->user_id
			)
		);
		
		return is_array( $results ) ? $results : [];
	}
	
	/**
	 * Get events by date range for current user
	 *
	 * @param string $from Start date (Y-m-d H:i:s)
	 * @param string $to End date (Y-m-d H:i:s)
	 * @return array
	 */
	public function get_events_in_range( string $from, string $to ): array {
		$results = $this->db->get_results(
			$this->db->prepare(
				"SELECT * FROM {$this->table_name} 
				WHERE user_id = %d 
				AND start_datetime >= %s 
				AND start_datetime <= %s 
				ORDER BY start_datetime ASC",
				$this->user_id,
				$from,
				$to
			)
		);
		
		return is_array( $results ) ? $results : [];
	}
	
	/**
	 * Get single event by ID (with user isolation)
	 *
	 * @param int $id Event ID
	 * @return object|null
	 */
	public function get_by_id( int $id ) {
		return $this->db->get_row(
			$this->db->prepare(
				"SELECT * FROM {$this->table_name} WHERE id = %d AND user_id = %d",
				$id,
				$this->user_id
			)
		);
	}
	
	/**
	 * Get event by appointment_id (with user isolation)
	 *
	 * @param string $appointment_id ChurchTools appointment ID
	 * @param string $start_datetime Start datetime (for COMPOSITE KEY)
	 * @return object|null
	 */
	public function get_by_appointment_id( string $appointment_id, string $start_datetime = '' ) {
		if ( empty( $start_datetime ) ) {
			// Fallback: Get first match
			return $this->db->get_row(
				$this->db->prepare(
					"SELECT * FROM {$this->table_name} WHERE appointment_id = %s AND user_id = %d LIMIT 1",
					$appointment_id,
					$this->user_id
				)
			);
		}
		
		return $this->db->get_row(
			$this->db->prepare(
				"SELECT * FROM {$this->table_name} WHERE appointment_id = %s AND start_datetime = %s AND user_id = %d",
				$appointment_id,
				$start_datetime,
				$this->user_id
			)
		);
	}
	
	/**
	 * Check if event exists for current user
	 *
	 * @param string $appointment_id ChurchTools appointment ID
	 * @param string $start_datetime Start datetime (for COMPOSITE KEY)
	 * @return bool
	 */
	public function exists_by_appointment_id( string $appointment_id, string $start_datetime = '' ): bool {
		if ( empty( $start_datetime ) ) {
			$count = $this->db->get_var(
				$this->db->prepare(
					"SELECT COUNT(*) FROM {$this->table_name} WHERE appointment_id = %s AND user_id = %d",
					$appointment_id,
					$this->user_id
				)
			);
		} else {
			$count = $this->db->get_var(
				$this->db->prepare(
					"SELECT COUNT(*) FROM {$this->table_name} WHERE appointment_id = %s AND start_datetime = %s AND user_id = %d",
					$appointment_id,
					$start_datetime,
					$this->user_id
				)
			);
		}
		
		return (int) $count > 0;
	}
	
	/**
	 * Insert or Update event (with user isolation)
	 *
	 * @param array $data Event data
	 * @return int|false Event ID or false
	 */
	public function upsert( array $data ) {
		$defaults = [
			'event_id' => null,
			'calendar_id' => null,
			'appointment_id' => null,
			'title' => '',
			'description' => null,
			'event_description' => null,
			'appointment_description' => null,
			'start_datetime' => null,
			'end_datetime' => null,
			'is_all_day' => 0,
			'location_name' => null,
			'address_name' => null,
			'address_street' => null,
			'address_zip' => null,
			'address_city' => null,
			'address_latitude' => null,
			'address_longitude' => null,
			'tags' => null,
			'status' => null,
			'image_attachment_id' => null,
			'image_url' => null,
			'raw_payload' => null,
			'last_modified' => null,
			'appointment_modified' => null,
		];
		$data = wp_parse_args( $data, $defaults );
		
		// appointment_id AND start_datetime required (composite key)
		if ( empty( $data['appointment_id'] ) || empty( $data['start_datetime'] ) ) {
			return false;
		}
		
		// Add user_id
		$data['user_id'] = $this->user_id;
		
		// Check if exists
		$existing_id = $this->db->get_var(
			$this->db->prepare(
				"SELECT id FROM {$this->table_name} WHERE appointment_id = %s AND start_datetime = %s AND user_id = %d",
				$data['appointment_id'],
				$data['start_datetime'],
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
					'%s', // event_id
					'%s', // calendar_id
					'%s', // appointment_id
					'%s', // title
					'%s', // description
					'%s', // event_description
					'%s', // appointment_description
					'%s', // start_datetime
					'%s', // end_datetime
					'%d', // is_all_day
					'%s', // location_name
					'%s', // address_name
					'%s', // address_street
					'%s', // address_zip
					'%s', // address_city
					'%f', // address_latitude
					'%f', // address_longitude
					'%s', // tags
					'%s', // status
					'%d', // image_attachment_id
					'%s', // image_url
					'%s', // raw_payload
					'%s', // last_modified
					'%s', // appointment_modified
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
					'%s', // event_id
					'%s', // calendar_id
					'%s', // appointment_id
					'%s', // title
					'%s', // description
					'%s', // event_description
					'%s', // appointment_description
					'%s', // start_datetime
					'%s', // end_datetime
					'%d', // is_all_day
					'%s', // location_name
					'%s', // address_name
					'%s', // address_street
					'%s', // address_zip
					'%s', // address_city
					'%f', // address_latitude
					'%f', // address_longitude
					'%s', // tags
					'%s', // status
					'%d', // image_attachment_id
					'%s', // image_url
					'%s', // raw_payload
					'%s', // last_modified
					'%s', // appointment_modified
					'%d', // user_id
				]
			);
			
			return $result !== false ? $this->db->insert_id : false;
		}
	}
	
	/**
	 * Delete all events for current user
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
	 * Get event count for current user
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
