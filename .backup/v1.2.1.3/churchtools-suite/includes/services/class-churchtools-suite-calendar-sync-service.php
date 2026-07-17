<?php
/**
 * Calendar Sync Service
 *
 * Synchronizes calendars from ChurchTools into the local database
 *
 * @package ChurchTools_Suite
 * @since   0.3.4.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class ChurchTools_Suite_Calendar_Sync_Service {
    
    /**
     * ChurchTools API Client
     *
     * @var ChurchTools_Suite_CT_Client
     */
    private $ct_client;
    
    /**
     * Calendars Repository
     *
     * @var ChurchTools_Suite_Calendars_Repository
     */
    private $calendars_repo;
    
    /**
     * Constructor
     *
     * @param ChurchTools_Suite_CT_Client $ct_client ChurchTools API Client
     * @param ChurchTools_Suite_Calendars_Repository $calendars_repo Calendars Repository
     */
    public function __construct($ct_client, $calendars_repo) {
        $this->ct_client = $ct_client;
        $this->calendars_repo = $calendars_repo;
    }
    
    /**
     * Synchronize calendars from ChurchTools
     *
     * Fetches all calendars and saves them to database.
     * Preserves user selection (is_selected) on update.
     *
     * @return array|WP_Error Statistics array or WP_Error on failure
     */
    public function sync_calendars() {
        $response = $this->ct_client->api_request('/calendars');
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        if (!isset($response['data']) || !is_array($response['data'])) {
            return new WP_Error(
                'invalid_response',
                __('Ungültige API-Antwort: data-Array fehlt', 'churchtools-suite')
            );
        }
        
        $calendars = $response['data'];
        
        $stats = [
            'total' => count($calendars),
            'inserted' => 0,
            'updated' => 0,
            'errors' => 0,
        ];
        
        foreach ($calendars as $calendar_data) {
            $result = $this->import_calendar($calendar_data);
            
            if (is_wp_error($result)) {
                $stats['errors']++;
                continue;
            }
            
            if ($result['action'] === 'insert') {
                $stats['inserted']++;
            } else {
                $stats['updated']++;
            }
        }
        
        // Save sync timestamp
        update_option('churchtools_suite_calendars_last_sync', current_time('mysql'), false);
        
        return $stats;
    }
    
    /**
     * Import a single calendar
     *
     * @param array $calendar_data Calendar data from ChurchTools API
     * @return array|WP_Error Array with 'id' and 'action' or WP_Error
     */
    private function import_calendar(array $calendar_data) {
        if (empty($calendar_data['id'])) {
            return new WP_Error('missing_id', __('Kalender-ID fehlt', 'churchtools-suite'));
        }
        
        // Check if calendar already exists
        $existing = $this->calendars_repo->get_by_calendar_id($calendar_data['id']);
        $action = $existing ? 'update' : 'insert';
        
        // Prepare data
        $data = [
            'calendar_id' => sanitize_text_field($calendar_data['id']),
            'name' => sanitize_text_field($calendar_data['name'] ?? ''),
            'name_translated' => !empty($calendar_data['nameTranslated'])
                ? sanitize_text_field($calendar_data['nameTranslated'])
                : null,
            'color' => !empty($calendar_data['color'])
                ? sanitize_hex_color($calendar_data['color'])
                : null,
            'is_public' => !empty($calendar_data['isPublic']) ? 1 : 0,
            'sort_order' => isset($calendar_data['sortKey'])
                ? absint($calendar_data['sortKey'])
                : null,
            'raw_payload' => wp_json_encode($calendar_data),
        ];
        
        // For new calendars: default selection based on is_public
        if ($action === 'insert') {
            $data['is_selected'] = $data['is_public'];
        }
        
        $calendar_id = $this->calendars_repo->upsert_by_calendar_id($data);
        
        if (!$calendar_id) {
            return new WP_Error(
                'upsert_failed',
                __('Kalender konnte nicht gespeichert werden', 'churchtools-suite')
            );
        }
        
        return [
            'id' => $calendar_id,
            'action' => $action,
        ];
    }
    
    /**
     * Fetch a single calendar from ChurchTools
     *
     * @param int $calendar_id ChurchTools Calendar ID
     * @return array|WP_Error Calendar data or WP_Error
     */
    public function fetch_calendar(int $calendar_id) {
        $response = $this->ct_client->api_request("/calendars/{$calendar_id}");
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        if (!isset($response['data'])) {
            return new WP_Error(
                'invalid_response',
                __('Ungültige API-Antwort: data fehlt', 'churchtools-suite')
            );
        }
        
        return $response['data'];
    }
    
    /**
     * Get last sync timestamp
     *
     * @return string|null MySQL timestamp or null if never synced
     */
    public function get_last_sync_time(): ?string {
        return get_option('churchtools_suite_calendars_last_sync', null);
    }
    
    /**
     * Check if sync is needed (never synced or older than 1 hour)
     *
     * @return bool True if sync needed
     */
    public function is_sync_needed(): bool {
        $last_sync = $this->get_last_sync_time();
        
        if (!$last_sync) {
            return true;
        }
        
        $last_sync_timestamp = strtotime($last_sync);
        $one_hour_ago = time() - 3600;
        
        return $last_sync_timestamp < $one_hour_ago;
    }
}
