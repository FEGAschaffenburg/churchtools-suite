<?php
/**
 * Demo Calendars Repository
 *
 * Manages isolated demo calendars per user in demo_cts_calendars table
 *
 * @package ChurchTools_Suite_Demo
 * @since   1.0.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ChurchTools_Suite_Demo_Calendars_Repository {
	
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
		$this->table_name = $wpdb->prefix . 'demo_cts_calendars';
		$this->user_id = $user_id;
	}
	
	/**
	 * Get all calendars for current user
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
	 * Get selected calendar IDs for current user
	 *
	 * @return array
	 */
	public function get_selected_calendar_ids(): array {
		$results = $this->db->get_col(
			$this->db->prepare(
				"SELECT calendar_id FROM {$this->table_name} WHERE user_id = %d AND is_selected = 1",
				$this->user_id
			)
		);
		
		return is_array( $results ) ? $results : [];
	}
	
	/**
	 * Get calendar by calendar_id (with user isolation)
	 *
	 * @param string $calendar_id ChurchTools calendar ID
	 * @return object|null
	 */
	public function get_by_calendar_id( string $calendar_id ) {
		return $this->db->get_row(
			$this->db->prepare(
				"SELECT * FROM {$this->table_name} WHERE calendar_id = %s AND user_id = %d",
				$calendar_id,
				$this->user_id
			)
		);
	}
	
	/**
	 * Insert or Update calendar (with user isolation)
	 *
	 * @param array $data Calendar data
	 * @return int|false Calendar ID or false
	 */
	public function upsert( array $data ) {
		$defaults = [
			'calendar_id' => null,
			'name' => '',
			'name_translated' => null,
			'color' => null,
			'calendar_image_id' => null,
			'is_selected' => 0,
			'is_public' => 0,
			'sort_order' => 0,
			'raw_payload' => null,
		];
		$data = wp_parse_args( $data, $defaults );
		
		// calendar_id required
		if ( empty( $data['calendar_id'] ) ) {
			return false;
		}
		
		// Add user_id
		$data['user_id'] = $this->user_id;
		
		// Check if exists
		$existing_id = $this->db->get_var(
			$this->db->prepare(
				"SELECT id FROM {$this->table_name} WHERE calendar_id = %s AND user_id = %d",
				$data['calendar_id'],
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
					'%s', // calendar_id
					'%s', // name
					'%s', // name_translated
					'%s', // color
					'%d', // calendar_image_id
					'%d', // is_selected
					'%d', // is_public
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
					'%s', // calendar_id
					'%s', // name
					'%s', // name_translated
					'%s', // color
					'%d', // calendar_image_id
					'%d', // is_selected
					'%d', // is_public
					'%d', // sort_order
					'%s', // raw_payload
					'%d', // user_id
				]
			);
			
			return $result !== false ? $this->db->insert_id : false;
		}
	}
	
	/**
	 * Update calendar selection (with user isolation)
	 *
	 * @param string $calendar_id ChurchTools calendar ID
	 * @param bool $is_selected Selected state
	 * @return bool Success
	 */
	public function update_selection( string $calendar_id, bool $is_selected ): bool {
		$result = $this->db->update(
			$this->table_name,
			[ 'is_selected' => $is_selected ? 1 : 0 ],
			[
				'calendar_id' => $calendar_id,
				'user_id' => $this->user_id,
			],
			[ '%d' ],
			[ '%s', '%d' ]
		);
		
		return $result !== false;
	}
	
	/**
	 * Delete all calendars for current user
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
	 * Get calendar count for current user
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
