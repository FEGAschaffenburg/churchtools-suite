<?php
/**
 * Service Sync Service
 *
 * Synchronizes ChurchTools service master data into the local database.
 * Services are fetched from /api/masterdata/serviceGroups endpoint.
 *
 * @package ChurchTools_Suite
 * @since   0.3.11.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ChurchTools_Suite_Service_Sync_Service {
	
	/**
	 * ChurchTools API Client
	 *
	 * @var ChurchTools_Suite_CT_Client
	 */
	private $ct_client;
	
	/**
	 * Services Repository
	 *
	 * @var ChurchTools_Suite_Services_Repository
	 */
	private $services_repo;
	
	/**
	 * Service Groups Repository
	 *
	 * @var ChurchTools_Suite_Service_Groups_Repository
	 */
	private $service_groups_repo;
	
	/**
	 * Constructor
	 *
	 * @param ChurchTools_Suite_CT_Client $ct_client ChurchTools API Client
	 * @param ChurchTools_Suite_Services_Repository $services_repo Services Repository
	 * @param ChurchTools_Suite_Service_Groups_Repository $service_groups_repo Service Groups Repository
	 */
	public function __construct( $ct_client, $services_repo, $service_groups_repo = null ) {
		$this->ct_client = $ct_client;
		$this->services_repo = $services_repo;
		$this->service_groups_repo = $service_groups_repo;
	}
	
	/**
	 * Synchronize service groups from ChurchTools
	 *
	 * Fetches service groups from ChurchTools API and stores them in the local database.
	 * Uses servicegroups endpoint (api_request adds /api/ prefix).
	 *
	 * @return array|WP_Error Statistics array or WP_Error on failure
	 */
	public function sync_service_groups() {
		if ( ! $this->service_groups_repo ) {
			return new WP_Error( 'no_repo', __( 'Service Groups Repository nicht verfügbar', 'churchtools-suite' ) );
		}
		
		// DEBUG: Log sync start
		error_log( 'ChurchTools Service Groups Sync: Starting...' );
		
		// Fetch service groups from ChurchTools
		$response = $this->ct_client->api_request( 'servicegroups', 'GET' );
		
		if ( is_wp_error( $response ) ) {
			error_log( 'ChurchTools Service Groups Sync: API Error - ' . $response->get_error_message() );
			return $response;
		}
		
		if ( ! isset( $response['data'] ) || ! is_array( $response['data'] ) ) {
			error_log( 'ChurchTools Service Groups Sync: Invalid response structure - ' . wp_json_encode( $response ) );
			return new WP_Error( 'invalid_response', __( 'Ungültige API-Antwort für Service Groups', 'churchtools-suite' ) );
		}
		
		$groups = $response['data'];
		
		// DEBUG: Log groups found
		error_log( sprintf( 'ChurchTools Service Groups Sync: Found %d groups', count( $groups ) ) );
		
		$stats = [
			'groups_found' => count( $groups ),
			'groups_inserted' => 0,
			'groups_updated' => 0,
			'groups_skipped' => 0,
		];
		
		// Process each service group
		foreach ( $groups as $group ) {
			$result = $this->process_service_group( $group );
			
			if ( is_wp_error( $result ) ) {
				$stats['groups_skipped']++;
				continue;
			}
			
			if ( $result['action'] === 'inserted' ) {
				$stats['groups_inserted']++;
			} elseif ( $result['action'] === 'updated' ) {
				$stats['groups_updated']++;
			}
		}
		
		// Save sync timestamp
		update_option( 'churchtools_suite_service_groups_last_sync', current_time( 'mysql' ), false );
		
		// DEBUG: Log sync complete
		error_log( sprintf(
			'ChurchTools Service Groups Sync: Complete - %d found, %d inserted, %d updated, %d skipped',
			$stats['groups_found'],
			$stats['groups_inserted'],
			$stats['groups_updated'],
			$stats['groups_skipped']
		) );
		
		return $stats;
	}
	
	/**
	 * Synchronize services from ChurchTools
	 *
	 * Fetches services from ChurchTools API and stores them in the local database.
	 * Only syncs services from selected service groups (if groups are selected).
	 * Uses services endpoint (api_request adds /api/ prefix).
	 *
	 * @return array|WP_Error Statistics array or WP_Error on failure
	 */
	public function sync_services() {
		// Get selected service groups (if any)
		$selected_group_ids = [];
		if ( $this->service_groups_repo ) {
			$selected_group_ids = $this->service_groups_repo->get_selected_group_ids();
		}
		
		// DEBUG: Log sync start
		error_log( sprintf(
			'ChurchTools Services Sync: Starting... (filtering by %d selected groups)',
			count( $selected_group_ids )
		) );
		
		// Fetch services from ChurchTools
		$response = $this->ct_client->api_request( 'services', 'GET' );
		
		if ( is_wp_error( $response ) ) {
			error_log( 'ChurchTools Services Sync: API Error - ' . $response->get_error_message() );
			return $response;
		}
		
		if ( ! isset( $response['data'] ) || ! is_array( $response['data'] ) ) {
			return new WP_Error( 'invalid_response', __( 'Ungültige API-Antwort für Services', 'churchtools-suite' ) );
		}
		
		$services = $response['data'];
		
		$stats = [
			'services_found' => count( $services ),
			'services_inserted' => 0,
			'services_updated' => 0,
			'services_skipped' => 0,
		];
		
		// Process each service
		foreach ( $services as $service ) {
			// Filter by selected service groups
			if ( ! empty( $selected_group_ids ) ) {
				$service_group_id = isset( $service['serviceGroupId'] ) ? (string) $service['serviceGroupId'] : null;
				if ( ! $service_group_id || ! in_array( $service_group_id, $selected_group_ids, true ) ) {
					$stats['services_skipped']++;
					continue;
				}
			}
			
			$result = $this->process_service( $service );
			
			if ( is_wp_error( $result ) ) {
				$stats['services_skipped']++;
				continue;
			}
			
			if ( $result['action'] === 'inserted' ) {
				$stats['services_inserted']++;
			} elseif ( $result['action'] === 'updated' ) {
				$stats['services_updated']++;
			}
		}
		
		// Save sync timestamp
		update_option( 'churchtools_suite_services_last_sync', current_time( 'mysql' ), false );
		
		return $stats;
	}
	
	/**
	 * Process a single service group
	 *
	 * @param array $group Service group data from API
	 * @return array|WP_Error Result array or WP_Error
	 */
	private function process_service_group( array $group ) {
		if ( ! isset( $group['id'] ) ) {
			return new WP_Error( 'missing_id', __( 'Service Group hat keine ID', 'churchtools-suite' ) );
		}
		
		// Skip if no name provided
		if ( empty( $group['name'] ) ) {
			return new WP_Error( 'missing_name', __( 'Service Group hat keinen Namen', 'churchtools-suite' ) );
		}
		
		$group_data = [
			'service_group_id' => (string) $group['id'],
			'name' => $group['name'],
			'sort_order' => $group['sortKey'] ?? 0,
			'view_all' => isset( $group['viewAll'] ) ? (int) $group['viewAll'] : 0,
			'raw_payload' => wp_json_encode( $group ),
		];
		
		// Check if exists before upserting
		$exists_before = $this->service_groups_repo->get_by_service_group_id( $group_data['service_group_id'] );
		
		$group_id = $this->service_groups_repo->upsert( $group_data );
		
		if ( ! $group_id ) {
			return new WP_Error( 'save_failed', __( 'Service Group konnte nicht gespeichert werden', 'churchtools-suite' ) );
		}
		
		return [
			'action' => $exists_before ? 'updated' : 'inserted',
			'group_id' => $group_id,
		];
	}
	
	/**
	 * Process a single service
	 *
	 * @param array $service Service data from API
	 * @return array|WP_Error Result array or WP_Error
	 */
	private function process_service( array $service ) {
		if ( ! isset( $service['id'] ) ) {
			return new WP_Error( 'missing_id', __( 'Service hat keine ID', 'churchtools-suite' ) );
		}
		
		// Skip if no name provided
		if ( empty( $service['name'] ) ) {
			return new WP_Error( 'missing_name', __( 'Service hat keinen Namen', 'churchtools-suite' ) );
		}
		
		$service_data = [
			'service_id' => (string) $service['id'],
			'service_group_id' => isset( $service['serviceGroupId'] ) ? (string) $service['serviceGroupId'] : null,
			'name' => $service['name'],
			'name_translated' => $service['nameTranslated'] ?? null,
			'sort_order' => $service['sortKey'] ?? 0,
			'raw_payload' => wp_json_encode( $service ),
		];
		
		// Check if exists before upserting
		$exists_before = $this->services_repo->get_by_service_id( $service_data['service_id'] );
		
		$service_id = $this->services_repo->upsert( $service_data );
		
		if ( ! $service_id ) {
			return new WP_Error( 'save_failed', __( 'Service konnte nicht gespeichert werden', 'churchtools-suite' ) );
		}
		
		return [
			'action' => $exists_before ? 'updated' : 'inserted',
			'service_id' => $service_id,
		];
	}
	
	/**
	 * Get last service groups sync timestamp
	 *
	 * @return string|null MySQL timestamp or null
	 */
	public function get_last_service_groups_sync_time(): ?string {
		return get_option( 'churchtools_suite_service_groups_last_sync', null );
	}
	
	/**
	 * Get last services sync timestamp
	 *
	 * @return string|null MySQL timestamp or null
	 */
	public function get_last_sync_time(): ?string {
		return get_option( 'churchtools_suite_services_last_sync', null );
	}
}
