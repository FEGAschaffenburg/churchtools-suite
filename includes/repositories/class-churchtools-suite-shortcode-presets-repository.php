<?php
/**
 * Shortcode Presets Repository
 * 
 * Verwaltet gespeicherte Shortcode-Konfigurationen
 * 
 * @package ChurchTools_Suite
 * @since   0.5.10.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ChurchTools_Suite_Shortcode_Presets_Repository extends ChurchTools_Suite_Repository_Base {
	
	/**
	 * Constructor
	 */
	public function __construct() {
		global $wpdb;
		parent::__construct( $wpdb->prefix . CHURCHTOOLS_SUITE_DB_PREFIX . 'shortcode_presets' );
	}
	
	/**
	 * Get all presets
	 * 
	 * @return array
	 */
	public function get_all_presets(): array {
		global $wpdb;
		
		$results = $wpdb->get_results(
			"SELECT * FROM {$this->table} ORDER BY is_system DESC, name ASC",
			ARRAY_A
		);
		
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
	 * Get preset by ID
	 * 
	 * @param int $id Preset ID
	 * @return array|null
	 */
	public function get_preset_by_id( int $id ): ?array {
		global $wpdb;
		
		$result = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$this->table} WHERE id = %d",
				$id
			),
			ARRAY_A
		);
		
		if ( ! $result ) {
			return null;
		}
		
		$result['configuration'] = json_decode( $result['configuration'], true );
		
		return $result;
	}
	
	/**
	 * Get presets by shortcode tag
	 * 
	 * @param string $shortcode_tag Shortcode tag (z.B. 'cts_list')
	 * @return array
	 */
	public function get_presets_by_tag( string $shortcode_tag ): array {
		global $wpdb;
		
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$this->table} WHERE shortcode_tag = %s ORDER BY is_system DESC, name ASC",
				$shortcode_tag
			),
			ARRAY_A
		);
		
		if ( ! $results ) {
			return [];
		}
		
		foreach ( $results as &$preset ) {
			$preset['configuration'] = json_decode( $preset['configuration'], true );
		}
		
		return $results;
	}
	
	/**
	 * Create new preset
	 * 
	 * @param array $data Preset data
	 * @return int|false Preset ID or false on failure
	 */
	public function create_preset( array $data ) {
		global $wpdb;
		
		$insert_data = [
			'name'           => $data['name'] ?? '',
			'description'    => $data['description'] ?? '',
			'shortcode_tag'  => $data['shortcode_tag'] ?? '',
			'configuration'  => wp_json_encode( $data['configuration'] ?? [] ),
			'is_system'      => $data['is_system'] ?? 0,
			'created_at'     => current_time( 'mysql' ),
		];
		
		$result = $wpdb->insert(
			$this->table,
			$insert_data,
			[ '%s', '%s', '%s', '%s', '%d', '%s' ]
		);
		
		return $result ? $wpdb->insert_id : false;
	}
	
	/**
	 * Update preset
	 * 
	 * @param int $id Preset ID
	 * @param array $data Updated data
	 * @return bool
	 */
	public function update_preset( int $id, array $data ): bool {
		global $wpdb;
		
		$update_data = [];
		
		if ( isset( $data['name'] ) ) {
			$update_data['name'] = $data['name'];
		}
		
		if ( isset( $data['description'] ) ) {
			$update_data['description'] = $data['description'];
		}
		
		if ( isset( $data['configuration'] ) ) {
			$update_data['configuration'] = wp_json_encode( $data['configuration'] );
		}
		
		if ( empty( $update_data ) ) {
			return false;
		}
		
		$result = $wpdb->update(
			$this->table,
			$update_data,
			[ 'id' => $id ],
			array_fill( 0, count( $update_data ), '%s' ),
			[ '%d' ]
		);
		
		return $result !== false;
	}
	
	/**
	 * Delete preset
	 * 
	 * @param int $id Preset ID
	 * @return bool
	 */
	public function delete_preset( int $id ): bool {
		global $wpdb;
		
		// Don't delete system presets
		$preset = $this->get_preset_by_id( $id );
		if ( $preset && $preset['is_system'] ) {
			return false;
		}
		
		$result = $wpdb->delete(
			$this->table,
			[ 'id' => $id ],
			[ '%d' ]
		);
		
		return $result !== false;
	}
	
	/**
	 * Create system presets
	 * 
	 * Erstellt vordefinierte System-Presets für häufige Use Cases
	 */
	public function create_system_presets(): void {
		$system_presets = [
			[
				'name'          => 'Standard Liste',
				'description'   => 'Klassische Listen-Ansicht mit allen Details',
				'shortcode_tag' => 'cts_list',
				'configuration' => [
					'view'          => 'classic',
					'limit'         => '20',
					'show_services' => 'true',
				],
				'is_system'     => 1,
			],
			[
				'name'          => 'Kompakte Liste',
				'description'   => 'Kompakte Listen-Ansicht für Sidebar',
				'shortcode_tag' => 'cts_list',
				'configuration' => [
					'view'          => 'medium',
					'limit'         => '5',
					'show_services' => 'false',
				],
				'is_system'     => 1,
			],
			[
				'name'          => 'Event-Grid',
				'description'   => '3-spaltige Raster-Ansicht',
				'shortcode_tag' => 'cts_grid',
				'configuration' => [
					'view'    => 'simple',
					'columns' => '3',
					'limit'   => '12',
				],
				'is_system'     => 1,
			],
			[
				'name'          => 'Monatskalender',
				'description'   => 'Moderne Monatsübersicht',
				'shortcode_tag' => 'cts_calendar',
				'configuration' => [
					'view' => 'monthly-modern',
				],
				'is_system'     => 1,
			],
		];
		
		foreach ( $system_presets as $preset ) {
			// Check if already exists
			global $wpdb;
			$exists = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$this->table} WHERE name = %s AND is_system = 1",
					$preset['name']
				)
			);
			
			if ( ! $exists ) {
				$this->create_preset( $preset );
			}
		}
	}
}
