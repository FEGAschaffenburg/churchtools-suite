<?php
/**
 * Event Sync Service
 *
 * Synchronizes events from ChurchTools into the local database
 * Two-Phase Sync:
 * - Phase 1: Events API (/events) - Events with their appointments (1:N)
 * - Phase 2: Appointments API (/calendars/{id}/appointments) - Standalone appointments without events
 *
 * @package ChurchTools_Suite
 * @since   0.3.7.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class ChurchTools_Suite_Event_Sync_Service {
    
    /**
     * ChurchTools API Client
     *
     * @var ChurchTools_Suite_CT_Client
     */
    private $ct_client;
    
    /**
     * Events Repository
     *
     * @var ChurchTools_Suite_Events_Repository
     */
    private $events_repo;
    
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
     * @param ChurchTools_Suite_Events_Repository $events_repo Events Repository
     * @param ChurchTools_Suite_Calendars_Repository $calendars_repo Calendars Repository
     */
    public function __construct($ct_client, $events_repo, $calendars_repo) {
        $this->ct_client = $ct_client;
        $this->events_repo = $events_repo;
        $this->calendars_repo = $calendars_repo;
    }
    
    /**
     * Synchronize events from selected calendars
     *
     * @param array $args {
     *     Optional. Sync parameters.
     *
     *     @type array  $calendar_ids ChurchTools calendar IDs (default: selected calendars)
     *     @type string $from         Start date (Y-m-d, default: -7 days)
     *     @type string $to           End date (Y-m-d, default: +90 days)
     * }
     * @return array|WP_Error Statistics array or WP_Error on failure
     */
    public function sync_events(array $args = []): array {
		// Get sync range from settings
		$days_past = get_option('churchtools_suite_sync_days_past', 7);
		$days_future = get_option('churchtools_suite_sync_days_future', 90);
		
		$defaults = [
			'calendar_ids' => [],
			'from' => date('Y-m-d', current_time('timestamp') - absint($days_past) * DAY_IN_SECONDS),
			'to' => date('Y-m-d', current_time('timestamp') + absint($days_future) * DAY_IN_SECONDS),
        $args = wp_parse_args($args, $defaults);
        
        // If no calendar_ids provided, use selected calendars
        if (empty($args['calendar_ids'])) {
            $args['calendar_ids'] = $this->calendars_repo->get_selected_calendar_ids();
        }
        
        if (empty($args['calendar_ids'])) {
            return new WP_Error('no_calendars_selected', __('Keine Kalender ausgewählt.', 'churchtools-suite'));
        }
        
        $stats = [
            'calendars_processed' => 0,
            'events_found' => 0,
            'appointments_found' => 0,
            'events_inserted' => 0,
            'events_updated' => 0,
            'events_skipped' => 0,
            'errors' => 0,
        ];
        
        // Fetch all events once (optimization)
        $all_events_result = $this->fetch_all_events($args);
        if (is_wp_error($all_events_result)) {
            return $all_events_result;
        }
        
        $all_events = $all_events_result['events'];
        
        // Process each calendar
        foreach ($args['calendar_ids'] as $calendar_id) {
            // Filter events for this calendar
            $relevant_events = array_filter($all_events, function($event) use ($calendar_id) {
                return $this->is_event_relevant_for_calendar($event, $calendar_id);
            });
            
            $result = $this->process_calendar_events($relevant_events, $calendar_id, $args);
            
            if (is_wp_error($result)) {
                $stats['errors']++;
                continue;
            }
            
            // Aggregate statistics
            $stats['calendars_processed']++;
            $stats['events_found'] += $result['events_found'];
            $stats['appointments_found'] += $result['appointments_found'] ?? 0;
            $stats['events_inserted'] += $result['events_inserted'];
            $stats['events_updated'] += $result['events_updated'];
            $stats['events_skipped'] += $result['events_skipped'];
        }
        
        // Save sync timestamp
        update_option('churchtools_suite_events_last_sync', current_time('mysql'), false);
        
        return $stats;
    }
    
    /**
     * Fetch all events from ChurchTools API
     *
     * @param array $args Sync parameters
     * @return array|WP_Error
     */
    private function fetch_all_events(array $args) {
        $response = $this->ct_client->api_request('/events', 'GET', [
            'direction' => 'forward',
            'include' => 'eventServices',
            'from' => $args['from'],
            'to' => $args['to'],
        ]);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        if (!isset($response['data']) || !is_array($response['data'])) {
            return new WP_Error('invalid_response', __('Ungültige Events API-Antwort', 'churchtools-suite'));
        }
        
        return [
            'events' => $response['data'],
            'total' => count($response['data']),
        ];
    }
    
    /**
     * Process events for a specific calendar
     *
     * Two-phase approach:
     * - Phase 1: Process events (from Events API)
     * - Phase 2: Process standalone appointments (from Appointments API)
     *
     * @param array $events Filtered events for this calendar
     * @param string $calendar_id ChurchTools calendar ID
     * @param array $args Sync parameters
     * @return array|WP_Error Statistics
     */
    private function process_calendar_events(array $events, string $calendar_id, array $args) {
        $stats = [
            'events_found' => count($events),
            'appointments_found' => 0,
            'events_inserted' => 0,
            'events_updated' => 0,
            'events_skipped' => 0,
        ];
        
        $imported_appointment_ids = [];
        
        // Phase 1: Process events
        foreach ($events as $event) {
            // Collect appointment IDs for Phase 2
            if (isset($event['appointment']['id'])) {
                $imported_appointment_ids[] = $event['appointment']['id'];
            }
            
            $result = $this->process_event($event, $calendar_id);
            
            if (is_wp_error($result)) {
                $stats['events_skipped']++;
                continue;
            }
            
            if ($result['action'] === 'inserted') {
                $stats['events_inserted']++;
            } elseif ($result['action'] === 'updated') {
                $stats['events_updated']++;
            }
        }
        
        // Phase 2: Process standalone appointments
        $appointments_result = $this->sync_phase2_appointments($calendar_id, $args, $imported_appointment_ids);
        
        if (!is_wp_error($appointments_result)) {
            $stats['appointments_found'] = $appointments_result['appointments_found'];
            $stats['events_inserted'] += $appointments_result['events_inserted'];
            $stats['events_updated'] += $appointments_result['events_updated'];
            $stats['events_skipped'] += $appointments_result['events_skipped'];
        }
        
        return $stats;
    }
    
    /**
     * Phase 2: Sync standalone appointments (without events)
     *
     * @param string $calendar_id ChurchTools calendar ID
     * @param array $args Sync parameters
     * @param array $imported_appointment_ids Already imported appointment IDs
     * @return array|WP_Error Statistics
     */
    private function sync_phase2_appointments(string $calendar_id, array $args, array $imported_appointment_ids) {
        $response = $this->ct_client->api_request("/calendars/{$calendar_id}/appointments", 'GET', [
            'from' => $args['from'],
            'to' => $args['to'],
        ]);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        if (!isset($response['data']) || !is_array($response['data'])) {
            return new WP_Error('invalid_response', __('Ungültige Appointments API-Antwort', 'churchtools-suite'));
        }
        
        $appointments = $response['data'];
        
        $stats = [
            'appointments_found' => count($appointments),
            'events_inserted' => 0,
            'events_updated' => 0,
            'events_skipped' => 0,
        ];
        
        foreach ($appointments as $appointment_data) {
            // Extract appointment from nested structure
            $appointment = isset($appointment_data['appointment']) ? $appointment_data['appointment'] : $appointment_data;
            
            // Get appointment ID
            $appointment_id = $appointment['base']['id'] ?? null;
            
            if (!$appointment_id) {
                continue;
            }
            
            // Skip if already imported as event
            if (in_array($appointment_id, $imported_appointment_ids, true)) {
                continue;
            }
            
            // Skip if wrong calendar
            if (!$this->is_appointment_relevant_for_calendar($appointment, $calendar_id)) {
                continue;
            }
            
            $result = $this->process_appointment($appointment, $calendar_id);
            
            if (is_wp_error($result)) {
                $stats['events_skipped']++;
                continue;
            }
            
            if ($result['action'] === 'inserted') {
                $stats['events_inserted']++;
            } elseif ($result['action'] === 'updated') {
                $stats['events_updated']++;
            }
        }
        
        return $stats;
    }
    
    /**
     * Process a single event from Events API
     *
     * @param array $event Event data from API
     * @param string $calendar_id ChurchTools calendar ID
     * @return array|WP_Error
     */
    private function process_event(array $event, string $calendar_id) {
        $event_data = $this->extract_event_data($event, $calendar_id);
        
        if (is_wp_error($event_data)) {
            return $event_data;
        }
        
        $event_id = $this->events_repo->upsert_by_event_id($event_data);
        
        if (!$event_id) {
            return new WP_Error('save_failed', __('Event konnte nicht gespeichert werden', 'churchtools-suite'));
        }
        
        $exists = $this->events_repo->exists_by_event_id($event_data['event_id']);
        
        return [
            'action' => $exists ? 'updated' : 'inserted',
            'event_id' => $event_id,
        ];
    }
    
    /**
     * Process a standalone appointment (without event)
     *
     * @param array $appointment Appointment data from API
     * @param string $calendar_id ChurchTools calendar ID
     * @return array|WP_Error
     */
    private function process_appointment(array $appointment, string $calendar_id) {
        $event_data = $this->extract_appointment_data($appointment, $calendar_id);
        
        if (is_wp_error($event_data)) {
            return $event_data;
        }
        
        // Check if already exists
        $exists = $this->events_repo->get_by_appointment_id($event_data['appointment_id']);
        
        if ($exists) {
            return [
                'action' => 'skipped',
                'event_id' => $exists->id,
            ];
        }
        
        $event_id = $this->events_repo->insert($event_data);
        
        if (!$event_id) {
            return new WP_Error('save_failed', __('Appointment konnte nicht gespeichert werden', 'churchtools-suite'));
        }
        
        return [
            'action' => 'inserted',
            'event_id' => $event_id,
        ];
    }
    
    /**
     * Extract event data for database
     *
     * @param array $event Raw event data from API
     * @param string $calendar_id ChurchTools calendar ID
     * @return array|WP_Error
     */
    private function extract_event_data(array $event, string $calendar_id) {
        if (!isset($event['id'])) {
            return new WP_Error('missing_id', __('Event hat keine ID', 'churchtools-suite'));
        }
        
        $appointment_id = $event['appointment']['id'] ?? $event['appointmentId'] ?? null;
        
        return [
            'event_id' => (string) $event['id'],
            'calendar_id' => $calendar_id,
            'appointment_id' => $appointment_id,
            'title' => $event['name'] ?? $event['designation'] ?? __('Unbenannt', 'churchtools-suite'),
            'description' => $event['note'] ?? '',
            'start_datetime' => $this->format_datetime($event['startDate'] ?? ''),
            'end_datetime' => $this->format_datetime($event['endDate'] ?? ''),
            'location_name' => $event['location'] ?? $event['address'] ?? '',
            'status' => 'active',
            'raw_payload' => wp_json_encode($event),
        ];
    }
    
    /**
     * Extract appointment data for database
     *
     * @param array $appointment Raw appointment data from API
     * @param string $calendar_id ChurchTools calendar ID
     * @return array|WP_Error
     */
    private function extract_appointment_data(array $appointment, string $calendar_id) {
        $appointment_id = $appointment['base']['id'] ?? null;
        
        if (!$appointment_id) {
            return new WP_Error('missing_id', __('Appointment hat keine ID', 'churchtools-suite'));
        }
        
        // Use appointment_id as event_id for standalone appointments
        $title = $appointment['base']['caption'] ?? $appointment['calculated']['caption'] ?? __('Unbenannt', 'churchtools-suite');
        $start_date = $appointment['calculated']['startDate'] ?? $appointment['base']['startDate'] ?? '';
        $end_date = $appointment['calculated']['endDate'] ?? $appointment['base']['endDate'] ?? '';
        
        return [
            'event_id' => 'apt_' . $appointment_id, // Prefix to avoid collision with events
            'calendar_id' => $calendar_id,
            'appointment_id' => (string) $appointment_id,
            'title' => $title,
            'description' => '',
            'start_datetime' => $this->format_datetime($start_date),
            'end_datetime' => $this->format_datetime($end_date),
            'location_name' => '',
            'status' => 'active',
            'raw_payload' => wp_json_encode($appointment),
        ];
    }
    
    /**
     * Check if event is relevant for calendar
     *
     * @param array $event Event data
     * @param string $calendar_id Target calendar ID
     * @return bool
     */
    private function is_event_relevant_for_calendar(array $event, string $calendar_id): bool {
        // Check various possible calendar ID locations
        $checks = [
            $event['calendar']['domainIdentifier'] ?? null,
            $event['calendar']['id'] ?? null,
            $event['calendarId'] ?? null,
            $event['appointment']['calendar']['domainIdentifier'] ?? null,
            $event['appointment']['calendar']['id'] ?? null,
        ];
        
        foreach ($checks as $check) {
            if ($check && (string) $check === (string) $calendar_id) {
                return true;
            }
        }
        
        // Check calendars array
        if (isset($event['calendars']) && is_array($event['calendars'])) {
            foreach ($event['calendars'] as $cal) {
                $cal_id = $cal['domainIdentifier'] ?? $cal['id'] ?? null;
                if ($cal_id && (string) $cal_id === (string) $calendar_id) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Check if appointment is relevant for calendar
     *
     * @param array $appointment Appointment data
     * @param string $calendar_id Target calendar ID
     * @return bool
     */
    private function is_appointment_relevant_for_calendar(array $appointment, string $calendar_id): bool {
        $checks = [
            $appointment['calendar_id'] ?? null,
            $appointment['calendar']['id'] ?? null,
            $appointment['base']['calendar']['id'] ?? null,
        ];
        
        foreach ($checks as $check) {
            if ($check && (string) $check === (string) $calendar_id) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Format datetime for database
     *
     * @param string $datetime Datetime string
     * @return string MySQL datetime format
     */
    private function format_datetime(string $datetime): string {
        if (empty($datetime)) {
            return current_time('mysql');
        }
        
        $timestamp = strtotime($datetime);
        return date('Y-m-d H:i:s', $timestamp);
    }
    
    /**
     * Get last sync timestamp
     *
     * @return string|null MySQL timestamp or null
     */
    public function get_last_sync_time(): ?string {
        return get_option('churchtools_suite_events_last_sync', null);
    }
}
