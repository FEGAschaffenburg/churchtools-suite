<?php
/**
 * User-Aware Shortcode Presets Repository Wrapper
 *
 * Extends main plugin's presets repository with user_id filtering
 * for multi-user isolation in demo environment.
 *
 * @package ChurchTools_Suite_Demo
 * @since   1.0.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ChurchTools_Suite_Demo_Presets_Repository {
	
	/**
	 * Main plugin's presets repository
	 *
	 * @var ChurchTools_Suite_Shortcode_Presets_Repository
	 */
	private $main_repo;
	
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->main_repo = new ChurchTools_Suite_Shortcode_Presets_Repository();
	}
	
	/**
	 * Get all presets for current user
	 * 
	 * Returns system presets (user_id=NULL) + user's own presets
	 *
	 * @param int|null $user_id WordPress user ID (null = current user)
	 * @return array
	 */
	public function get_all_presets( $user_id = null ): array {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}
		
		global $wpdb;
		$table = $wpdb->prefix . CHURCHTOOLS_SUITE_DB_PREFIX . 'shortcode_presets';
		
		$results = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM {$table} 
			WHERE user_id IS NULL OR user_id = %d 
			ORDER BY is_system DESC, name ASC",
			$user_id
		), ARRAY_A );
		
		if ( ! $results ) {
			return [];
		}
		
		// Decode JSON configuration
		foreach ( $results as &$preset ) {
			$preset['configuration'] = json_decode( $preset['configuration'], true );
		}
		
		return $results;
	}
	
	/**
	 * Get preset by ID (with ownership check)
	 *
	 * @param int $id Preset ID
	 * @param int|null $user_id WordPress user ID (null = current user)
	 * @return array|null
	 */
	public function get_preset_by_id( int $id, $user_id = null ): ?array {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}
		
		global $wpdb;
		$table = $wpdb->prefix . CHURCHTOOLS_SUITE_DB_PREFIX . 'shortcode_presets';
		
		$result = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$table} 
			WHERE id = %d AND (user_id IS NULL OR user_id = %d)",
			$id,
			$user_id
		), ARRAY_A );
		
		if ( ! $result ) {
			return null;
		}
		
		$result['configuration'] = json_decode( $result['configuration'], true );
		
		return $result;
	}
	
	/**
	 * Create new preset for current user
	 *
	 * @param array $data Preset data
	 * @param int|null $user_id WordPress user ID (null = current user)
	 * @return int|false Preset ID or false on failure
	 */
	public function create_preset( array $data, $user_id = null ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}
		
		global $wpdb;
		$table = $wpdb->prefix . CHURCHTOOLS_SUITE_DB_PREFIX . 'shortcode_presets';
		
		$insert_data = [
			'name'           => $data['name'] ?? '',
			'description'    => $data['description'] ?? '',
			'shortcode_tag'  => $data['shortcode_tag'] ?? '',
			'configuration'  => wp_json_encode( $data['configuration'] ?? [] ),
			'is_system'      => 0, // User presets are never system
			'user_id'        => $user_id,
			'created_at'     => current_time( 'mysql' ),
		];
		
		$result = $wpdb->insert(
			$table,
			$insert_data,
			[ '%s', '%s', '%s', '%s', '%d', '%d', '%s' ]
		);
		
		return $result ? $wpdb->insert_id : false;
	}
	
	/**
	 * Update preset (with ownership check)
	 *
	 * @param int $id Preset ID
	 * @param array $data Updated data
	 * @param int|null $user_id WordPress user ID (null = current user)
	 * @return bool
	 */
	public function update_preset( int $id, array $data, $user_id = null ): bool {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}
		
		// Verify ownership
		$preset = $this->get_preset_by_id( $id, $user_id );
		if ( ! $preset ) {
			return false; // Not found or not owned by user
		}
		
		// System presets cannot be edited
		if ( $preset['is_system'] ) {
			return false;
		}
		
		global $wpdb;
		$table = $wpdb->prefix . CHURCHTOOLS_SUITE_DB_PREFIX . 'shortcode_presets';
		
		$update_data = [];
		$format = [];
		
		if ( isset( $data['name'] ) ) {
			$update_data['name'] = $data['name'];
			$format[] = '%s';
		}
		
		if ( isset( $data['description'] ) ) {
			$update_data['description'] = $data['description'];
			$format[] = '%s';
		}
		
		if ( isset( $data['configuration'] ) ) {
			$update_data['configuration'] = wp_json_encode( $data['configuration'] );
			$format[] = '%s';
		}
		
		if ( empty( $update_data ) ) {
			return false;
		}
		
		$result = $wpdb->update(
			$table,
			$update_data,
			[ 'id' => $id, 'user_id' => $user_id ],
			$format,
			[ '%d', '%d' ]
		);
		
		return $result !== false;
	}
	
	/**
	 * Delete preset (with ownership check)
	 *
	 * @param int $id Preset ID
	 * @param int|null $user_id WordPress user ID (null = current user)
	 * @return bool
	 */
	public function delete_preset( int $id, $user_id = null ): bool {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}
		
		// Verify ownership
		$preset = $this->get_preset_by_id( $id, $user_id );
		if ( ! $preset ) {
			return false; // Not found or not owned by user
		}
		
		// System presets cannot be deleted
		if ( $preset['is_system'] ) {
			return false;
		}
		
		global $wpdb;
		$table = $wpdb->prefix . CHURCHTOOLS_SUITE_DB_PREFIX . 'shortcode_presets';
		
		$result = $wpdb->delete(
			$table,
			[ 'id' => $id, 'user_id' => $user_id ],
			[ '%d', '%d' ]
		);
		
		return $result !== false;
	}
}
