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
     * Event Services Repository
     *
     * @var ChurchTools_Suite_Event_Services_Repository
     */
    private $event_services_repo;
    
    /**
     * Services Repository
     *
     * @var ChurchTools_Suite_Services_Repository
     */
    private $services_repo;
    
    /**
     * Constructor
     *
     * @param ChurchTools_Suite_CT_Client $ct_client ChurchTools API Client
     * @param ChurchTools_Suite_Events_Repository $events_repo Events Repository
     * @param ChurchTools_Suite_Calendars_Repository $calendars_repo Calendars Repository
     * @param ChurchTools_Suite_Event_Services_Repository $event_services_repo Event Services Repository (optional)
     * @param ChurchTools_Suite_Services_Repository $services_repo Services Repository (optional)
     */
    public function __construct($ct_client, $events_repo, $calendars_repo, $event_services_repo = null, $services_repo = null) {
        $this->ct_client = $ct_client;
        $this->events_repo = $events_repo;
        $this->calendars_repo = $calendars_repo;
        $this->event_services_repo = $event_services_repo;
        $this->services_repo = $services_repo;
        
        // Load logger
        require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-logger.php';
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
     *     @type bool   $force_full   Force full sync instead of incremental (default: false)
     * }
     * @return array Statistics array (may contain 'success' => false on error)
     */
    public function sync_events(array $args = []): array {
		// Get sync range from settings
		$days_past = get_option('churchtools_suite_sync_days_past', 7);
		$days_future = get_option('churchtools_suite_sync_days_future', 90);
		
		$defaults = [
			'calendar_ids' => [],
			'from' => date('Y-m-d', current_time('timestamp') - absint($days_past) * DAY_IN_SECONDS),
			'to' => date('Y-m-d', current_time('timestamp') + absint($days_future) * DAY_IN_SECONDS),
			'force_full' => false, // v0.7.1.0: Allow forcing full sync
		];
		$args = wp_parse_args($args, $defaults);
        
        // Determine if incremental sync is possible (v0.7.1.0)
        $last_sync = get_option('churchtools_suite_last_sync_timestamp');
        $is_incremental = !$args['force_full'] && $last_sync;
        
        // Log sync start
        ChurchTools_Suite_Logger::log(
            $is_incremental ? 'Event Sync started (INCREMENTAL)' : 'Event Sync started (FULL)',
            ChurchTools_Suite_Logger::INFO,
            [
                'from' => $args['from'],
                'to' => $args['to'],
                'calendar_ids' => $args['calendar_ids'],
                'last_sync' => $last_sync,
                'is_incremental' => $is_incremental,
            ]
        );
        
        // If no calendar_ids provided, use selected calendars
        if (empty($args['calendar_ids'])) {
            $args['calendar_ids'] = $this->calendars_repo->get_selected_calendar_ids();
        }
        
        if (empty($args['calendar_ids'])) {
            ChurchTools_Suite_Logger::log(
                'Event Sync aborted: No calendars selected',
                ChurchTools_Suite_Logger::WARNING
            );
            return [
                'success' => false,
                'error' => __('Keine Kalender ausgewählt.', 'churchtools-suite'),
                'error_code' => 'no_calendars_selected',
            ];
        }
        
        $stats = [
            'sync_type' => $is_incremental ? 'incremental' : 'full', // v0.7.1.0
            'calendars_processed' => 0,
            'events_found' => 0,
            'appointments_found' => 0,
            'events_inserted' => 0,
            'events_updated' => 0,
            'events_unchanged' => 0, // v0.7.1.0
            'events_deleted' => 0, // v0.7.1.0
            'events_skipped' => 0,
            'services_imported' => 0,
            'errors' => 0,
        ];
        
        $start_time = microtime(true);
        
        // Fetch all events once (optimization)
        $all_events_result = $this->fetch_all_events($args);
        if (is_wp_error($all_events_result)) {
            ChurchTools_Suite_Logger::log(
                'Event Sync failed: Could not fetch events',
                ChurchTools_Suite_Logger::ERROR,
                ['error' => $all_events_result->get_error_message()]
            );
            return [
                'success' => false,
                'error' => $all_events_result->get_error_message(),
                'error_code' => $all_events_result->get_error_code(),
            ];
        }

        $all_events = $all_events_result['events'];
        
        ChurchTools_Suite_Logger::log(
            sprintf('Fetched %d events from API', count($all_events)),
            ChurchTools_Suite_Logger::INFO
        );
        
        // Process each calendar
        foreach ($args['calendar_ids'] as $calendar_id) {
            // Filter events for this calendar
            $relevant_events = array_filter($all_events, function($event) use ($calendar_id) {
                return $this->is_event_relevant_for_calendar($event, $calendar_id);
            });
            
            ChurchTools_Suite_Logger::log(
                sprintf('Processing calendar %s: %d relevant events', $calendar_id, count($relevant_events)),
                ChurchTools_Suite_Logger::DEBUG
            );
            
            $result = $this->process_calendar_events($relevant_events, $calendar_id, $args, $is_incremental);
            
            if (is_wp_error($result)) {
                ChurchTools_Suite_Logger::log(
                    sprintf('Calendar %s processing failed', $calendar_id),
                    ChurchTools_Suite_Logger::ERROR,
                    ['error' => $result->get_error_message()]
                );
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
            $stats['services_imported'] += $result['services_imported'] ?? 0;
            $stats['events_deleted'] += $result['events_deleted'] ?? 0;
        }
        
        // Calculate duration
        $duration = round(microtime(true) - $start_time, 2);
        
        // Log sync complete
        ChurchTools_Suite_Logger::log(
            'Event Sync completed',
            ChurchTools_Suite_Logger::INFO,
            [
                'duration_seconds' => $duration,
                'calendars_processed' => $stats['calendars_processed'],
                'events_inserted' => $stats['events_inserted'],
                'events_updated' => $stats['events_updated'],
                'events_deleted' => $stats['events_deleted'],
                'events_skipped' => $stats['events_skipped'],
                'services_imported' => $stats['services_imported'],
                'errors' => $stats['errors'],
            ]
        );
        
        // Save sync timestamp (v0.7.1.0: Before saving timestamp for next incremental sync)
        update_option('churchtools_suite_events_last_sync', current_time('mysql'), false);
        update_option('churchtools_suite_last_sync_timestamp', current_time('timestamp'), false);
        
        return $stats;
    }
    
    /**
     * Fetch all events from ChurchTools API (v0.7.1.0: Incremental sync support)
     *
     * @param array $args Sync parameters
     * @return array|WP_Error
     */
    private function fetch_all_events(array $args) {
        $api_params = [
            'direction' => 'forward',
            'from' => $args['from'],
            'to' => $args['to'],
            // v0.10.4.0: Include eventServices (tags nur bei Appointments!)
            'include' => 'eventServices',
        ];
        
        // v0.7.1.0: Incremental sync - only fetch modified events
        if (!$args['force_full']) {
            $last_sync = get_option('churchtools_suite_last_sync_timestamp');
            if ($last_sync) {
                // Convert timestamp to ISO 8601 format
                $modified_after = date('c', $last_sync);
                $api_params['modified_after'] = $modified_after;
                
                ChurchTools_Suite_Logger::log(
                    sprintf('Using incremental sync (modified_after: %s)', $modified_after),
                    ChurchTools_Suite_Logger::INFO
                );
            }
        }
        
        // v0.7.2.6: Debug logging for API request
        ChurchTools_Suite_Logger::debug(
            'event_sync',
            'Fetching events from API',
            [
                'endpoint' => '/events',
                'params' => $api_params,
                'force_full' => $args['force_full']
            ]
        );
        
        $response = $this->ct_client->api_request('/events', 'GET', $api_params);
        
        if (is_wp_error($response)) {
            // v0.7.2.6: Log API error details
            ChurchTools_Suite_Logger::error(
                'event_sync',
                'Failed to fetch events from API',
                [
                    'error_code' => $response->get_error_code(),
                    'error_message' => $response->get_error_message(),
                    'params' => $api_params
                ]
            );
            return $response;
        }
        
        if (!isset($response['data']) || !is_array($response['data'])) {
            // v0.7.2.6: Log invalid response structure
            ChurchTools_Suite_Logger::error(
                'event_sync',
                'Invalid API response structure',
                [
                    'has_data' => isset($response['data']),
                    'data_type' => gettype($response['data'] ?? null),
                    'response_keys' => array_keys($response)
                ]
            );
            return new WP_Error('invalid_response', __('Ungültige Events API-Antwort', 'churchtools-suite'));
        }
        
        // v0.7.2.6: Log successful fetch
        ChurchTools_Suite_Logger::debug(
            'event_sync',
            sprintf('Successfully fetched %d events from API', count($response['data'])),
            ['event_count' => count($response['data'])]
        );
        
        // v0.7.3.3: Filter events by date range (ChurchTools API may return more than requested)
        $filtered_events = $this->filter_events_by_date_range($response['data'], $args['from'], $args['to']);
        
        if (count($filtered_events) < count($response['data'])) {
            ChurchTools_Suite_Logger::debug(
                'event_sync',
                sprintf('Filtered %d events outside date range (%d → %d)', 
                    count($response['data']) - count($filtered_events),
                    count($response['data']),
                    count($filtered_events)
                ),
                [
                    'from' => $args['from'],
                    'to' => $args['to'],
                    'before_filter' => count($response['data']),
                    'after_filter' => count($filtered_events)
                ]
            );
        }
        
        return [
            'events' => $filtered_events,
            'total' => count($filtered_events),
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
     * @param bool $is_incremental Whether this is an incremental sync (v0.7.3.1)
     * @return array|WP_Error Statistics
     */
    private function process_calendar_events(array $events, string $calendar_id, array $args, bool $is_incremental = false) {
        $stats = [
            'events_found' => count($events),
            'appointments_found' => 0,
            'events_inserted' => 0,
            'events_updated' => 0,
            'events_skipped' => 0,
            'services_imported' => 0,
        ];
        
        $imported_appointment_ids = [];
        $phase1_api_keys = [];
        
        // Log Phase 1 start
        ChurchTools_Suite_Logger::log(
            sprintf('Calendar %s - Phase 1 start: %d events to process', $calendar_id, count($events)),
            ChurchTools_Suite_Logger::DEBUG
        );
        
        // Phase 1: Process events
        foreach ($events as $index => $event) {
            // v0.7.2.6: Debug log each event
            ChurchTools_Suite_Logger::debug(
                'event_sync',
                sprintf('Processing event %d/%d: ID=%s', $index + 1, count($events), $event['id'] ?? 'unknown'),
                [
                    'event_id' => $event['id'] ?? null,
                    'title' => $event['name'] ?? $event['designation'] ?? 'unknown',
                    'has_appointment' => isset($event['appointmentId'])
                ]
            );
            
            // Collect appointment IDs for Phase 2
            // API structure: event.appointmentId (direct), or nested event.appointment.base.id/event.appointment.id
            $event_appointment_id = '';
            if (!empty($event['appointmentId'])) {
                $event_appointment_id = (string) $event['appointmentId'];
            } elseif (!empty($event['appointment']['base']['id'])) {
                $event_appointment_id = (string) $event['appointment']['base']['id'];
            } elseif (!empty($event['appointment']['id'])) {
                $event_appointment_id = (string) $event['appointment']['id'];
            }

            if ($event_appointment_id !== '') {
                $imported_appointment_ids[] = $event_appointment_id;
            }
            
            $result = $this->process_event($event, $calendar_id);
            
            if (is_wp_error($result)) {
                // v0.7.2.6: Enhanced error logging
                ChurchTools_Suite_Logger::warning(
                    'event_sync',
                    sprintf('Failed to process event %s: %s', $event['id'] ?? 'unknown', $result->get_error_message()),
                    [
                        'error_code' => $result->get_error_code(),
                        'error_message' => $result->get_error_message(),
                        'event_id' => $event['id'] ?? null,
                        'event_data' => [
                            'title' => $event['name'] ?? $event['designation'] ?? null,
                            'startDate' => $event['startDate'] ?? null,
                            'calendar_id' => $calendar_id
                        ]
                    ]
                );
                $stats['events_skipped']++;
                continue;
            }
            
            if ($result['action'] === 'inserted') {
                $stats['events_inserted']++;
            } elseif ($result['action'] === 'updated') {
                $stats['events_updated']++;
            }
            
            // Track services imported
            if (isset($result['services_imported'])) {
                $stats['services_imported'] += $result['services_imported'];
            }

            // Track composite keys from Phase 1 (must match Phase 2/cleanup key format)
            $appointment_id = '';
            if (!empty($event['appointmentId'])) {
                $appointment_id = (string) $event['appointmentId'];
            } elseif (!empty($event['appointment']['base']['id'])) {
                $appointment_id = (string) $event['appointment']['base']['id'];
            } elseif (!empty($event['appointment']['id'])) {
                $appointment_id = (string) $event['appointment']['id'];
            }

            if ($appointment_id !== '') {
                $composite_key = $this->build_appointment_composite_key(
                    $appointment_id,
                    $this->get_appointment_start_date_from_event($event)
                );
                if ($composite_key !== '') {
                    $phase1_api_keys[] = $composite_key;
                }
            }
        }
        
        // Log Phase 1 complete
        ChurchTools_Suite_Logger::log(
            sprintf('Calendar %s - Phase 1 complete', $calendar_id),
            ChurchTools_Suite_Logger::DEBUG,
            [
                'inserted' => $stats['events_inserted'],
                'updated' => $stats['events_updated'],
                'skipped' => $stats['events_skipped'],
                'services' => $stats['services_imported'],
                'appointment_ids' => count($imported_appointment_ids),
            ]
        );
        
        // Phase 2: Process standalone appointments
        $appointments_result = $this->sync_phase2_appointments($calendar_id, $args, $imported_appointment_ids);
        
        $phase2_success = !is_wp_error($appointments_result);
        if ($phase2_success) {
            $stats['appointments_found'] = $appointments_result['appointments_found'];
            $stats['events_inserted'] += $appointments_result['events_inserted'];
            $stats['events_updated'] += $appointments_result['events_updated'];
            $stats['events_skipped'] += $appointments_result['events_skipped'];
        } else {
            ChurchTools_Suite_Logger::warning(
                'event_sync',
                sprintf('Calendar %s - Skip deleted check: Appointments API failed', $calendar_id),
                [
                    'error' => $appointments_result->get_error_message(),
                ]
            );
        }
        
        // Phase 3: Detect deleted events/appointments
        $deleted_count = 0;
        if ($phase2_success) {
            $api_appointment_keys = array_values(array_unique(array_merge(
                $phase1_api_keys,
                $appointments_result['api_appointment_keys'] ?? []
            )));
            $deleted_count = $this->detect_deleted_events($calendar_id, $args, $api_appointment_keys);
            ChurchTools_Suite_Logger::debug(
                'event_sync',
                sprintf('Calendar %s - Deleted events check: %d deleted', $calendar_id, $deleted_count)
            );
        } elseif (!$phase2_success) {
            ChurchTools_Suite_Logger::debug(
                'event_sync',
                sprintf('Calendar %s - Skipping deleted events check (phase 2 failed)', $calendar_id)
            );
        }
        $stats['events_deleted'] = $deleted_count;
        
        return $stats;
    }
    
    /**
     * Detect and remove events that were deleted in ChurchTools (v0.7.1.0)
     * 
     * Compares local rows (appointment_id|start_datetime) in the sync date range with
     * composite keys from Phase 1 + Phase 2 (Appointments API). Missing rows are removed.
     * Skipped when Phase 2 fails (no stale deletes on partial API outage).
     *
     * @param string $calendar_id ChurchTools calendar ID
     * @param array $args Sync parameters
     * @param array<int,string> $api_appointment_keys Composite keys from API (appointment_id|start_datetime)
     * @return int Number of deleted events
     */
    private function detect_deleted_events(string $calendar_id, array $args, array $api_appointment_keys): int {
        // Get all local appointment rows for this calendar in the date range.
        $local_rows = $this->events_repo->get_appointment_rows_in_range(
            $args['from'] . ' 00:00:00',
            $args['to'] . ' 23:59:59',
            $calendar_id
        );

        if (empty($local_rows)) {
            return 0;
        }

        // Index API keys for fast lookup.
        $api_lookup = array_fill_keys($api_appointment_keys, true);

        $delete_local_ids = [];

        foreach ($local_rows as $row) {
            $appointment_id = isset($row['appointment_id']) ? (string) $row['appointment_id'] : '';
            $start_datetime = isset($row['start_datetime']) ? (string) $row['start_datetime'] : '';
            $local_id = isset($row['id']) ? (int) $row['id'] : 0;

            if ($appointment_id === '' || $start_datetime === '' || $local_id <= 0) {
                continue;
            }

            $key = $appointment_id . '|' . $start_datetime;
            if (!isset($api_lookup[$key])) {
                $delete_local_ids[] = $local_id;
            }
        }

        if (empty($delete_local_ids)) {
            return 0;
        }

        // Clean linked services first (if repository is available)
        if ($this->event_services_repo) {
            foreach ($delete_local_ids as $local_id) {
                $this->event_services_repo->delete_for_event((int) $local_id);
            }
        }

        $deleted_count = $this->events_repo->delete_by_local_ids($delete_local_ids);

        ChurchTools_Suite_Logger::log(
            sprintf('Deleted %d events/appointments from calendar %s (no longer in ChurchTools)', $deleted_count, $calendar_id),
            ChurchTools_Suite_Logger::INFO,
            [
                'deleted_local_ids' => array_values($delete_local_ids),
                'api_key_count' => count($api_appointment_keys),
            ]
        );

        return $deleted_count;
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
        // ChurchTools API erwartet include als Array: bookings, event, group, meetingRequests, tags, titleSuffix
        // API-Aufbau exakt wie im Beispiel: include[]-Parameter als Array, Reihenfolge wie gewünscht
        $api_params = [
            'from' => $args['from'],
            'to' => $args['to'],
            'include' => [
                'bookings',
                'event',
                'group',
                'meetingRequests',
                'tags',
                'titleSuffix',
            ],
        ];
        // Debug: Logge die finale API-URL
        if (class_exists('ChurchTools_Suite_Logger')) {
            ChurchTools_Suite_Logger::debug(
                'api_request',
                'Appointments API Request',
                [
                    'endpoint' => "/calendars/{$calendar_id}/appointments",
                    'params' => $api_params,
                ]
            );
        }
        $response = $this->ct_client->api_request("/calendars/{$calendar_id}/appointments", 'GET', $api_params);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        if (!isset($response['data']) || !is_array($response['data'])) {
            return new WP_Error('invalid_response', __('Ungültige Appointments API-Antwort', 'churchtools-suite'));
        }
        
        $appointments = $response['data'];
		
		// v0.10.4.3: Log first appointment (sample) to debug tags
		if (!empty($appointments)) {
			ChurchTools_Suite_Logger::debug(
				'api_request',
				sprintf('FIRST APPOINTMENT SAMPLE (Calendar %s)', $calendar_id),
				[
					'sample_appointment' => $appointments[0], // Full structure
					'sample_keys' => array_keys($appointments[0]),
					'total_appointments' => count($appointments),
				]
			);
		}
		
		$stats = [
			'appointments_found' => count($appointments),
			'events_inserted' => 0,
			'events_updated' => 0,
			'events_skipped' => 0,
            'api_appointment_keys' => [],
		];
        
        ChurchTools_Suite_Logger::log(
            sprintf('Calendar %s - Phase 2 start: %d appointments found (UPDATE ALL events with appointment data)', 
                $calendar_id, count($appointments)),
            ChurchTools_Suite_Logger::DEBUG
        );
        
        // v0.7.3.3: Filter appointments by date range (API may return more)
        $from_timestamp = strtotime($args['from'] . ' 00:00:00');
        $to_timestamp = strtotime($args['to'] . ' 23:59:59');
        
        $skipped_outside_range = 0;
        
        foreach ($appointments as $appointment_data) {
            // v0.10.4.9: Keep FULL appointment_data (includes tags, bookings, etc.)
            // Do NOT extract only appointment - tags are on outer level!
            
            // Get appointment ID from nested structure
            $appointment = $appointment_data['appointment'] ?? $appointment_data;
            $appointment_id = $appointment['base']['id'] ?? null;
            
            if (!$appointment_id) {
                continue;
            }
            
            // Skip if wrong calendar
            if (!$this->is_appointment_relevant_for_calendar($appointment, $calendar_id)) {
                continue;
            }
            
            // v0.7.3.3: Skip if outside date range
            $start_date = $this->get_appointment_start_date_from_appointment_array($appointment);
            if ($start_date !== '') {
                $apt_timestamp = strtotime($start_date);
                if ($apt_timestamp !== false && ($apt_timestamp < $from_timestamp || $apt_timestamp > $to_timestamp)) {
                    $skipped_outside_range++;
                    continue;
                }
            }

            $composite_key = $this->build_appointment_composite_key((string) $appointment_id, $start_date);
            if ($composite_key !== '') {
                $stats['api_appointment_keys'][] = $composite_key;
            }
            
            // v0.9.0.0: Simplified - just upsert ALL appointments
            // COMPOSITE KEY (appointment_id + start_datetime) handles duplicates
            // If already imported in Phase 1 → updates with appointment-specific data
            // If standalone → inserts new row
            // v0.10.4.9: Pass FULL appointment_data (includes tags!)
            $result = $this->process_appointment($appointment_data, $calendar_id);
            
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
        
        ChurchTools_Suite_Logger::log(
            sprintf('Calendar %s - Phase 2 complete', $calendar_id),
            ChurchTools_Suite_Logger::DEBUG,
            [
                'inserted' => $stats['events_inserted'],
                'updated' => $stats['events_updated'],
                'skipped' => $stats['events_skipped'],
                'outside_range' => $skipped_outside_range,
                'api_keys' => count($stats['api_appointment_keys']),
            ]
        );

        $stats['api_appointment_keys'] = array_values(array_unique($stats['api_appointment_keys']));
        
        return $stats;
    }
    
    /**
     * Process a single event from Events API (v0.9.0.0)
     * 
     * Each API response item contains ONE appointment. Events with multiple 
     * appointments return as separate API items with same event_id but different 
     * appointment_ids. We store each as a separate row using appointment_id as 
     * the unique key.
     *
     * @param array $event Event data from API (actually event + 1 appointment)
     * @param string $calendar_id ChurchTools calendar ID
     * @return array|WP_Error
     */
    private function process_event(array $event, string $calendar_id) {
        $event_data = $this->extract_event_data($event, $calendar_id);
        
        if (is_wp_error($event_data)) {
            return $event_data;
        }
        
        // v0.9.0.0: Check by COMPOSITE KEY (appointment_id + start_datetime)
        $exists_before = !empty($event_data['appointment_id']) && !empty($event_data['start_datetime'])
            ? $this->events_repo->exists_by_appointment_id($event_data['appointment_id'], $event_data['start_datetime'])
            : false;
        
        $event_id = $this->events_repo->upsert_by_event_id($event_data);
        
        if (!$event_id) {
            return new WP_Error('save_failed', __('Event konnte nicht gespeichert werden', 'churchtools-suite'));
        }
        
        // Process event services (if repositories available)
        $services_imported = 0;
        if ($this->event_services_repo && $this->services_repo) {
            $services_imported = $this->process_event_services($event_id, $event);
        }
        
        return [
            'action' => $exists_before ? 'updated' : 'inserted',
            'event_id' => $event_id,
            'services_imported' => $services_imported,
        ];
    }
    
    /**
     * Process a standalone appointment (without event) (v0.9.0.0, v0.9.2.5: Enhanced, v0.10.4.9: Full data)
     * 
     * Updates ALL appointments with appointment-specific data (address, tags, appointment_description).
     * - If from Phase 1 (event-based) → Updates existing event with appointment data
     * - If standalone → Inserts new row
     *
     * @param array $appointment_data RAW appointment data from API (includes tags, bookings on outer level!)
     * @param string $calendar_id ChurchTools calendar ID
     * @return array|WP_Error
     */
    private function process_appointment(array $appointment_data, string $calendar_id) {
        $event_data = $this->extract_appointment_data($appointment_data, $calendar_id);
        
        if (is_wp_error($event_data)) {
            return $event_data;
        }
        
        // v0.9.2.5: Debug logging - verify address extraction
        ChurchTools_Suite_Logger::debug(
            'event_sync',
            sprintf('Phase 2 - Processing appointment %s', $event_data['appointment_id']),
            [
                'appointment_id' => $event_data['appointment_id'],
                'has_address_name' => !empty($event_data['address_name']),
                'address_name' => $event_data['address_name'] ?? 'NOT_SET',
                'address_city' => $event_data['address_city'] ?? 'NOT_SET',
                'has_tags' => !empty($event_data['tags']),
                'tags_preview' => isset($event_data['tags']) ? substr($event_data['tags'], 0, 100) : 'NOT_SET',
            ]
        );
        
        // v0.9.0.0: Check before upsert for statistics
        $exists_before = !empty($event_data['appointment_id']) && !empty($event_data['start_datetime'])
            ? $this->events_repo->exists_by_appointment_id($event_data['appointment_id'], $event_data['start_datetime'])
            : false;
        
        // v0.9.0.0: Use upsert (delegates to appointment_id version)
        $event_id = $this->events_repo->upsert_by_event_id($event_data);
        
        if (!$event_id) {
            return new WP_Error('save_failed', __('Appointment konnte nicht gespeichert werden', 'churchtools-suite'));
        }
        
        return [
            'action' => $exists_before ? 'updated' : 'inserted',
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
        // v0.7.2.6: Debug log extraction start
        ChurchTools_Suite_Logger::debug(
            'event_sync',
            'Extracting event data',
            [
                'event_id' => $event['id'] ?? null,
                'has_name' => isset($event['name']),
                'has_startDate' => isset($event['startDate']),
                'has_appointmentId' => isset($event['appointmentId']),
                'available_keys' => array_keys($event)
            ]
        );
        
        if (!isset($event['id'])) {
            ChurchTools_Suite_Logger::error(
                'event_sync',
                'Event missing ID field',
                ['event_keys' => array_keys($event), 'event_sample' => array_slice($event, 0, 5)]
            );
            return new WP_Error('missing_id', __('Event hat keine ID', 'churchtools-suite'));
        }
        
        // API structure: event.appointmentId (direct field, not nested)
        $appointment_id = null;
        if (!empty($event['appointmentId'])) {
            $appointment_id = (string) $event['appointmentId'];
        } elseif (!empty($event['appointment']['base']['id'])) {
            $appointment_id = (string) $event['appointment']['base']['id'];
        } elseif (!empty($event['appointment']['id'])) {
            $appointment_id = (string) $event['appointment']['id'];
        }
        
        // Extract last_modified timestamp (v0.7.1.0)
        $last_modified = null;
        if (isset($event['meta']['modifiedDate'])) {
            $last_modified = $this->format_datetime($event['meta']['modifiedDate']);
        } elseif (isset($event['modifiedDate'])) {
            $last_modified = $this->format_datetime($event['modifiedDate']);
        }
        
        // v0.8.1.0: Extract appointment_modified (if appointment exists)
        $appointment_modified = null;
        if (isset($event['appointment']['meta']['modifiedDate'])) {
            $appointment_modified = $this->format_datetime($event['appointment']['meta']['modifiedDate']);
        } elseif (isset($event['appointment']['modifiedDate'])) {
            $appointment_modified = $this->format_datetime($event['appointment']['modifiedDate']);
        }
        
        // v0.9.1.0: Separate descriptions (v0.10.4.10: Removed combined field)
        $event_description = $event['note'] ?? '';
        $appointment_description = $event['appointment']['note'] ?? '';
        
        // v0.9.2.0: Extract address details (v0.9.2.2: Fixed nested path like in extract_appointment_data)
        $address = $event['appointment']['base']['address'] ?? $event['appointment']['address'] ?? $event['address'] ?? null;
        $address_name = null;
        $address_street = null;
        $address_zip = null;
        $address_city = null;
        $address_latitude = null;
        $address_longitude = null;
        
        if ($address && is_array($address)) {
            $address_name = $address['name'] ?? $address['meetingAt'] ?? null;
            $address_street = $address['street'] ?? null;
            $address_zip = $address['zip'] ?? $address['postalcode'] ?? null;
            $address_city = $address['city'] ?? null;
            $address_latitude = isset($address['latitude']) ? (float) $address['latitude'] : (isset($address['geoLat']) ? (float) $address['geoLat'] : null);
            $address_longitude = isset($address['longitude']) ? (float) $address['longitude'] : (isset($address['geoLng']) ? (float) $address['geoLng'] : null);
        }
        
        // v0.10.4.0: Tags gibt es NUR bei Appointments, nicht bei Events!
        // Tags werden in Phase 2 (Appointments API) importiert
        $tags = null;
        
        // v0.10.5.0: Import image from ChurchTools (Update: prüfe, ob Bild noch aktuell ist)
        $image_attachment_id = null;
        $image_url = null;
        $event_image_candidates = [
            $event['appointment']['base']['image'] ?? null,
            $event['appointment']['image'] ?? null,
            $event['image'] ?? null,
        ];
        $external_image_url = $this->resolve_preferred_image_url($event_image_candidates);
        $external_image_name = $this->resolve_preferred_image_name($event_image_candidates);

        // Beim Re-Sync Bild immer neu importieren, damit Änderungen zuverlässig übernommen werden.
        $existing_event = null;
        if (!empty($event['id'])) {
            $existing_event = $this->events_repo->get_by_event_id($event['id']);
        }

        if (!empty($external_image_url) && filter_var($external_image_url, FILTER_VALIDATE_URL)) {
            require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-image-importer.php';
            
            // Check if image with this URL already exists (avoid duplicates for recurring events)
            $existing_image_id = ChurchTools_Suite_Image_Importer::find_existing_image_by_url($external_image_url);
            
            if ($existing_image_id > 0) {
                // Image with this URL already exists - reuse it
                $image_attachment_id = $existing_image_id;
                $image_url = ChurchTools_Suite_Image_Importer::get_image_url($existing_image_id);
                
                ChurchTools_Suite_Logger::debug(
                    'event_sync',
                    sprintf('Image reused for event %s: attachment_id=%d (existing for URL)', $event['id'], $existing_image_id),
                    ['external_url' => $external_image_url]
                );
            } else {
                // Image with this URL does not exist - import it
                if ($existing_event && !empty($existing_event->image_attachment_id)) {
                    $this->delete_previous_cts_image((int) $existing_event->image_attachment_id, (string) $event['id']);
                }

                $import_result = ChurchTools_Suite_Image_Importer::import_image(
                    $external_image_url,
                    $external_image_name ?? $event['name'] ?? $event['designation'] ?? 'Event Image',
                    (string) $event['id']
                );
                if (!is_wp_error($import_result)) {
                    $image_attachment_id = $import_result;
                    $image_url = ChurchTools_Suite_Image_Importer::get_image_url($import_result);
                    ChurchTools_Suite_Logger::debug(
                        'event_sync',
                        sprintf('Image recreated for event %s: attachment_id=%d', $event['id'], $import_result),
                        ['external_url' => $external_image_url]
                    );
                } else {
                    ChurchTools_Suite_Logger::warning(
                        'event_sync',
                        sprintf('Failed to recreate image for event %s: %s', $event['id'], $import_result->get_error_message()),
                        ['external_url' => $external_image_url, 'error_code' => $import_result->get_error_code()]
                    );
                }
            }
        }
        
        return [
            'event_id' => (string) $event['id'],
            'calendar_id' => $calendar_id,
            'appointment_id' => $appointment_id,
            'title' => $event['name'] ?? $event['designation'] ?? __('Unbenannt', 'churchtools-suite'),
            'description' => null, // v0.10.4.10: Removed combined field - use event_description/appointment_description
            'event_description' => $event_description, // v0.9.1.0: Event-level
            'appointment_description' => $appointment_description, // v0.9.1.0: Appointment-level
            'start_datetime' => $this->format_datetime($this->get_appointment_start_date_from_event($event)),
            'end_datetime' => $this->format_datetime($event['endDate'] ?? ''),
            'location_name' => $event['location'] ?? $event['address'] ?? '',
            'address_name' => $address_name, // v0.9.2.0
            'address_street' => $address_street, // v0.9.2.0
            'address_zip' => $address_zip, // v0.9.2.0
            'address_city' => $address_city, // v0.9.2.0
            'address_latitude' => $address_latitude, // v0.9.2.0
            'address_longitude' => $address_longitude, // v0.9.2.0
            'tags' => $tags, // v0.9.2.0
            'status' => 'active',
            'image_attachment_id' => $image_attachment_id, // v0.10.5.0
            'image_url' => $image_url, // v0.10.5.0 - Fallback URL
            'raw_payload' => wp_json_encode($event),
            'last_modified' => $last_modified, // v0.7.1.0
            'appointment_modified' => $appointment_modified, // v0.8.1.0
        ];
    }
    
    /**
     * Extract appointment data for database (v0.9.0.0, v0.10.4.9: Extract from FULL appointment_data)
     * 
     * Extracts ALL available appointment fields:
     * - title, subtitle (note)
     * - description (information)
     * - address (location)
     * - link, image
     * - dates (calculated preferred, fallback to base)
     * - tags (on outer level, not in appointment.base!)
     *
     * @param array $appointment_data RAW appointment data from API (includes appointment, tags, bookings)
     * @param string $calendar_id ChurchTools calendar ID
     * @return array|WP_Error
     */
    private function extract_appointment_data(array $appointment_data, string $calendar_id) {
        // v0.10.4.9: Extract nested appointment structure
        $appointment = $appointment_data['appointment'] ?? $appointment_data;
        
        $appointment_id = $appointment['base']['id'] ?? null;
        
        if (!$appointment_id) {
            return new WP_Error('missing_id', __('Appointment hat keine ID', 'churchtools-suite'));
        }
        
        // v0.10.4.9: Log RAW appointment_data (includes tags on outer level!)
        ChurchTools_Suite_Logger::debug(
            'event_sync',
            sprintf('RAW APPOINTMENT DATA for ID %s', $appointment_id),
            [
                'raw_appointment_data' => $appointment_data, // FULL outer object
                'has_tags_key_outer' => isset($appointment_data['tags']),
                'tags_value_outer' => $appointment_data['tags'] ?? 'NOT_SET',
                'appointment_data_keys' => array_keys($appointment_data),
            ]
        );
        
        // Extract all available fields from base (v0.9.2.7: Support both nested and flat structure)
        // Newer API: appointment.base (nested)
        // Older/deprecated: base (flat, alias)
        $base = $appointment['base'] ?? [];
        $calc = $appointment['calculated'] ?? [];
        
        // Title (caption in API)
        $title = $base['title'] ?? $base['caption'] ?? $calc['caption'] ?? __('Unbenannt', 'churchtools-suite');
        
        // v0.9.2.7: Add titleSuffix if present (e.g., "(Predigt: Ralf Broszat)")
        $title_suffix = $appointment['titleSuffix'] ?? '';
        if (!empty($title_suffix)) {
            $title .= ' ' . $title_suffix;
        }
        
        // Subtitle (note in API) + Description (information)
        $subtitle = $base['subtitle'] ?? $base['note'] ?? '';
        $description = $base['description'] ?? $base['information'] ?? '';
        
        // Combine subtitle + description
        $combined_description = '';
        if (!empty($subtitle)) {
            $combined_description = $subtitle;
        }
        if (!empty($description)) {
            if (!empty($combined_description)) {
                $combined_description .= "\n\n";
            }
            $combined_description .= $description;
        }
        
        // v0.9.2.0: Extract address details from appointment
        // API Struktur: appointment.base.address ODER base.address (deprecated)
        $address = $appointment['appointment']['base']['address'] ?? $appointment['base']['address'] ?? null;
        $address_name = null;
        $address_street = null;
        $address_zip = null;
        $address_city = null;
        $address_latitude = null;
        $address_longitude = null;
        $location = '';
        
        if ($address && is_array($address)) {
            $address_name = $address['name'] ?? $address['meetingAt'] ?? null;
            $address_street = $address['street'] ?? null;
            $address_zip = $address['zip'] ?? $address['postalcode'] ?? null;
            $address_city = $address['city'] ?? null;
            $address_latitude = isset($address['latitude']) ? (float) $address['latitude'] : (isset($address['geoLat']) ? (float) $address['geoLat'] : null);
            $address_longitude = isset($address['longitude']) ? (float) $address['longitude'] : (isset($address['geoLng']) ? (float) $address['geoLng'] : null);
            
            // Fallback location_name from address
            $location = $address_name ?? '';
        } else {
            // Fallback to base address string
            $location = $base['address'] ?? '';
        }

        // v0.9.9.x: Enrich missing address/geo from bookings (resource address)
        if (empty($address_name) || empty($address_latitude) || empty($address_longitude)) {
            if (!empty($appointment_data['bookings']) && is_array($appointment_data['bookings'])) {
                $first_booking = $appointment_data['bookings'][0]['base'] ?? $appointment_data['bookings'][0] ?? [];
                $resource = $first_booking['resource'] ?? [];
                $resource_address = $resource['address'] ?? [];
                if (empty($address_name) && !empty($resource['name'])) {
                    $address_name = $resource['name'];
                    if (empty($location)) {
                        $location = $resource['name'];
                    }
                }
                if (is_array($resource_address)) {
                    $address_street = $address_street ?: ($resource_address['street'] ?? null);
                    $address_zip = $address_zip ?: ($resource_address['zip'] ?? $resource_address['postalcode'] ?? null);
                    $address_city = $address_city ?: ($resource_address['city'] ?? null);
                    $address_latitude = $address_latitude ?: (isset($resource_address['latitude']) ? (float) $resource_address['latitude'] : (isset($resource_address['geoLat']) ? (float) $resource_address['geoLat'] : null));
                    $address_longitude = $address_longitude ?: (isset($resource_address['longitude']) ? (float) $resource_address['longitude'] : (isset($resource_address['geoLng']) ? (float) $resource_address['geoLng'] : null));
                }
            }
        }
        
        // v0.10.4.9: Extract tags from appointment_data (tags are on OUTER level, not in appointment.base!)
        $tags = null;
        if (isset($appointment_data['tags']) && is_array($appointment_data['tags']) && !empty($appointment_data['tags'])) {
            // v0.10.4.0: Normalize color values
            $tags = wp_json_encode($this->normalize_tag_colors($appointment_data['tags']));
            
            // v0.10.4.2: Debug logging für Tags
            ChurchTools_Suite_Logger::debug(
                'event_sync',
                sprintf('Appointment %s - Tags gefunden und normalisiert', $appointment_id),
                [
                    'raw_tags' => $appointment_data['tags'],
                    'normalized_count' => count($this->normalize_tag_colors($appointment_data['tags'])),
                    'json_length' => strlen($tags),
                ]
            );
        } else {
            // v0.10.4.2: Log wenn Tags NICHT vorhanden sind
            ChurchTools_Suite_Logger::warning(
                'event_sync',
                sprintf('Appointment %s - KEINE TAGS in API-Response', $appointment_id),
                [
                    'has_tags_key' => isset($appointment_data['tags']),
                    'tags_is_array' => isset($appointment_data['tags']) && is_array($appointment_data['tags']),
                    'tags_empty' => isset($appointment_data['tags']) ? empty($appointment_data['tags']) : 'NOT_SET',
                    'appointment_data_keys' => array_keys($appointment_data),
                ]
            );
        }
        
        // v0.9.2.1: Debug logging für extrahierte Daten
        ChurchTools_Suite_Logger::debug(
            'event_sync',
            sprintf('Appointment %s - Extracted data', $appointment_id),
            [
                'address_name' => $address_name,
                'address_street' => $address_street,
                'address_zip' => $address_zip,
                'address_city' => $address_city,
                'address_latitude' => $address_latitude,
                'address_longitude' => $address_longitude,
                'tags_count' => isset($appointment['tags']) ? count($appointment['tags']) : 0,
                'tags' => $tags,
            ]
        );
        
        // Dates (prefer calculated, fallback to base) — same source as cleanup composite keys
        $start_date = $this->get_appointment_start_date_from_appointment_array($appointment);
        $end_date = $calc['endDate'] ?? $base['endDate'] ?? '';
        
        // v0.10.5.0: Import image from ChurchTools (also for appointments!)
        $image_attachment_id = null;
        $image_url = null;
        // FIX: Image object has imageUrl field inside it!
        $appointment_image_candidates = [
            $appointment['base']['image'] ?? null,
            $appointment['image'] ?? null,
            $appointment_data['image'] ?? null,
        ];
        $external_image_url = $this->resolve_preferred_image_url($appointment_image_candidates);
        $external_image_name = $this->resolve_preferred_image_name($appointment_image_candidates);
        
        if (!empty($external_image_url) && filter_var($external_image_url, FILTER_VALIDATE_URL)) {
            // Lade Image Importer
            require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-image-importer.php';

            $start_datetime = $this->format_datetime($start_date);
            $existing_appointment = null;
            if (!empty($start_datetime)) {
                $existing_appointment = $this->events_repo->get_by_appointment_id((string) $appointment_id, $start_datetime);
            }

            // Check if image with this URL already exists (avoid duplicates for recurring appointments)
            $existing_image_id = ChurchTools_Suite_Image_Importer::find_existing_image_by_url($external_image_url);
            
            if ($existing_image_id > 0) {
                // Image with this URL already exists - reuse it
                $image_attachment_id = $existing_image_id;
                $image_url = ChurchTools_Suite_Image_Importer::get_image_url($existing_image_id);
                
                ChurchTools_Suite_Logger::debug(
                    'event_sync',
                    sprintf('Image reused for appointment %s: attachment_id=%d (existing for URL)', $appointment_id, $existing_image_id),
                    ['external_url' => $external_image_url]
                );
            } else {
                // Image with this URL does not exist - import it
                if ($existing_appointment && !empty($existing_appointment->image_attachment_id)) {
                    $this->delete_previous_cts_image((int) $existing_appointment->image_attachment_id, (string) $appointment_id);
                }

                // Beim Re-Sync immer neu importieren
                $import_result = ChurchTools_Suite_Image_Importer::import_image(
                    $external_image_url,
                    $external_image_name ?? $title,
                    (string) $appointment_id
                );

                if (!is_wp_error($import_result)) {
                    $image_attachment_id = $import_result;
                    $image_url = ChurchTools_Suite_Image_Importer::get_image_url($import_result);

                    ChurchTools_Suite_Logger::debug(
                        'event_sync',
                        sprintf('Image recreated for appointment %s: attachment_id=%d', $appointment_id, $import_result),
                        ['external_url' => $external_image_url]
                    );
                } else {
                    ChurchTools_Suite_Logger::warning(
                        'event_sync',
                        sprintf('Failed to recreate image for appointment %s: %s', $appointment_id, $import_result->get_error_message()),
                        ['external_url' => $external_image_url]
                    );
                }
            }
        }
        
        return [
            'event_id' => null, // v0.9.0.0: NULL for standalone appointments
            'calendar_id' => $calendar_id,
            'appointment_id' => (string) $appointment_id,
            'title' => $title,
            'description' => null, // v0.10.4.10: Removed combined field - use event_description/appointment_description
            'event_description' => null, // v0.9.1.0: Standalone appointments have no event
            'appointment_description' => $combined_description, // v0.9.1.0: Appointment-level
            'start_datetime' => $this->format_datetime($start_date),
            'end_datetime' => $this->format_datetime($end_date),
            'location_name' => $location,
            'address_name' => $address_name, // v0.9.2.0
            'address_street' => $address_street, // v0.9.2.0
            'address_zip' => $address_zip, // v0.9.2.0
            'address_city' => $address_city, // v0.9.2.0
            'address_latitude' => $address_latitude, // v0.9.2.0
            'address_longitude' => $address_longitude, // v0.9.2.0
            'tags' => $tags, // v0.9.2.0
            'status' => 'active',
            'image_attachment_id' => $image_attachment_id, // v0.10.5.0
            'image_url' => $image_url, // v0.10.5.0 - Fallback URL
            // Store full appointment_data to preserve outer-level fields (e.g. tags/bookings)
            'raw_payload' => wp_json_encode($appointment_data),
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
        // Debug: Log event structure first time
        static $logged = false;
        if (!$logged && WP_DEBUG) {
            ChurchTools_Suite_Logger::log(
                'Event Structure Sample (first event)',
                ChurchTools_Suite_Logger::DEBUG,
                ['event' => $event]
            );
            $logged = true;
        }
        
        // Check various possible calendar ID locations
        $checks = [
            // Direct fields
            $event['calendar']['domainIdentifier'] ?? null,
            $event['calendar']['id'] ?? null,
            $event['calendarId'] ?? null,
            // Appointment nested fields
            $event['appointment']['calendar']['domainIdentifier'] ?? null,
            $event['appointment']['calendar']['id'] ?? null,
            $event['appointment']['calendarId'] ?? null,
            // Base appointment fields
            $event['appointment']['base']['calendar']['id'] ?? null,
            $event['appointment']['base']['calendar']['domainIdentifier'] ?? null,
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
        
        // Check if event has appointments with calendars
        if (isset($event['appointments']) && is_array($event['appointments'])) {
            foreach ($event['appointments'] as $apt) {
                $apt_cal_id = $apt['calendar']['domainIdentifier'] ?? $apt['calendar']['id'] ?? $apt['base']['calendar']['id'] ?? null;
                if ($apt_cal_id && (string) $apt_cal_id === (string) $calendar_id) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Delete previously imported CTS image if ownership matches the current sync key.
     */
    private function delete_previous_cts_image(int $attachment_id, string $sync_key = ''): void {
        if ($attachment_id <= 0) {
            return;
        }

        $imported_from = (string) get_post_meta($attachment_id, '_cts_imported_from', true);
        if (empty($imported_from)) {
            return;
        }

        $owner_key = (string) get_post_meta($attachment_id, '_cts_event_id', true);
        if (!empty($sync_key) && !empty($owner_key) && $owner_key !== $sync_key) {
            return;
        }

        require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-image-importer.php';
        ChurchTools_Suite_Image_Importer::delete_image($attachment_id);
    }

    /**
     * Resolve best possible image URL with preference for original/full URLs.
     * Falls back to imageUrl if no better option is available.
     *
     * @param array $image_candidates Image candidate arrays from API structures
     * @return string|null
     */
    private function resolve_preferred_image_url(array $image_candidates): ?string {
        $preferred_keys = [
            'originalUrl',
            'downloadUrl',
            'originalImageUrl',
            'fileUrl',
            'url',
        ];
        $fallback_keys = [
            'imageUrl',
            'thumbnailUrl',
            'thumbUrl',
        ];

        foreach ($image_candidates as $candidate) {
            if (!is_array($candidate)) {
                continue;
            }
            foreach ($preferred_keys as $key) {
                if (!empty($candidate[$key]) && filter_var($candidate[$key], FILTER_VALIDATE_URL)) {
                    return (string) $candidate[$key];
                }
            }
        }

        foreach ($image_candidates as $candidate) {
            if (!is_array($candidate)) {
                continue;
            }
            foreach ($fallback_keys as $key) {
                if (!empty($candidate[$key]) && filter_var($candidate[$key], FILTER_VALIDATE_URL)) {
                    return (string) $candidate[$key];
                }
            }
        }

        return null;
    }

    /**
     * Resolve image display/file name from available API image structures.
     *
     * @param array $image_candidates Image candidate arrays from API structures
     * @return string|null
     */
    private function resolve_preferred_image_name(array $image_candidates): ?string {
        foreach ($image_candidates as $candidate) {
            if (!is_array($candidate)) {
                continue;
            }
            if (!empty($candidate['name']) && is_string($candidate['name'])) {
                return (string) $candidate['name'];
            }
            if (!empty($candidate['filename']) && is_string($candidate['filename'])) {
                return (string) $candidate['filename'];
            }
        }

        return null;
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
     * Update event with new appointment data (v0.8.1.0, v0.9.0.0: COMPOSITE KEY)
     * 
     * Called when appointment was modified but event wasn't.
     * Updates appointment_modified timestamp and combined description.
     *
     * @param string $appointment_id ChurchTools appointment ID
     * @param array $appointment Appointment data from API
     * @param string $calendar_id ChurchTools calendar ID
     * @param string $apt_modified Appointment modified timestamp
     * @param string $start_datetime Start datetime for COMPOSITE KEY lookup (v0.9.0.0)
     * @return array|WP_Error
     */
    private function update_event_appointment(string $appointment_id, array $appointment, string $calendar_id, string $apt_modified, string $start_datetime = '') {
        // Get existing event by COMPOSITE KEY (v0.9.0.0)
        $existing_event = $this->events_repo->get_by_appointment_id($appointment_id, $start_datetime);
        
        if (!$existing_event) {
            ChurchTools_Suite_Logger::warning(
                'event_sync',
                sprintf('Appointment %s (start: %s) not found in database (should exist from Phase 1)', $appointment_id, $start_datetime),
                ['appointment_id' => $appointment_id, 'calendar_id' => $calendar_id, 'start_datetime' => $start_datetime]
            );
            return new WP_Error('not_found', 'Event nicht gefunden');
        }
        
        // Check if appointment_modified changed
        if ($existing_event->appointment_modified === $apt_modified) {
            // No change, skip update
            return ['action' => 'skipped', 'event_id' => $existing_event->id];
        }
        
        // Extract appointment note for combined description
        $apt_note = $appointment['note'] ?? $appointment['base']['note'] ?? '';
        
        // Get existing event note (event-level description)
        $event_note = '';
        if (!empty($existing_event->raw_payload)) {
            $raw = json_decode($existing_event->raw_payload, true);
            $event_note = $raw['note'] ?? '';
        }
        
        // Combine descriptions
        $combined_description = $event_note;
        if (!empty($apt_note)) {
            if (!empty($combined_description)) {
                $combined_description .= "\n\n--- Termindetails ---\n\n";
            }
            $combined_description .= $apt_note;
        }
        
        // Update event with new appointment data
        $update_data = [
            'description' => $combined_description,
            'appointment_modified' => $apt_modified,
        ];
        
        $result = $this->events_repo->update($existing_event->id, $update_data);
        
        if (!$result) {
            return new WP_Error('update_failed', 'Event konnte nicht aktualisiert werden');
        }
        
        ChurchTools_Suite_Logger::debug(
            'event_sync',
            sprintf('Updated event %d with new appointment data (apt_modified: %s)', 
                $existing_event->id, $apt_modified),
            [
                'event_id' => $existing_event->id,
                'appointment_id' => $appointment_id,
                'old_modified' => $existing_event->appointment_modified,
                'new_modified' => $apt_modified,
            ]
        );
        
        return ['action' => 'updated', 'event_id' => $existing_event->id];
    }
    
    /**
     * Filter events by date range (v0.7.3.3)
     * 
     * ChurchTools API may return events outside the requested range,
     * especially when using incremental sync with modified_after parameter.
     *
     * @param array $events Events from API
     * @param string $from Start date (Y-m-d)
     * @param string $to End date (Y-m-d)
     * @return array Filtered events
     */
    private function filter_events_by_date_range(array $events, string $from, string $to): array {
        $from_timestamp = strtotime($from . ' 00:00:00');
        $to_timestamp = strtotime($to . ' 23:59:59');
        
        return array_filter($events, function($event) use ($from_timestamp, $to_timestamp) {
            // Get event start date
            $start_date = $event['startDate'] ?? null;
            if (!$start_date) {
                return false; // No start date, skip
            }
            
            $event_timestamp = strtotime($start_date);
            if ($event_timestamp === false) {
                return false; // Invalid date format
            }
            
            // Event must start within the range
            return $event_timestamp >= $from_timestamp && $event_timestamp <= $to_timestamp;
        });
    }
    
    /**
     * Canonical start date from an appointment object (calculated → base).
     *
     * @param array $appointment Appointment node from ChurchTools API
     * @return string Raw start date string or empty
     */
    private function get_appointment_start_date_from_appointment_array(array $appointment): string {
        $calc = $appointment['calculated'] ?? [];
        $base = $appointment['base'] ?? [];

        return (string) ($calc['startDate'] ?? $base['startDate'] ?? '');
    }

    /**
     * Canonical start date for an event row (appointment dates before event.startDate).
     *
     * @param array $event Event payload from Events API
     * @return string Raw start date string or empty
     */
    private function get_appointment_start_date_from_event(array $event): string {
        if (!empty($event['appointment']) && is_array($event['appointment'])) {
            $from_appointment = $this->get_appointment_start_date_from_appointment_array($event['appointment']);
            if ($from_appointment !== '') {
                return $from_appointment;
            }
        }

        return (string) ($event['startDate'] ?? '');
    }

    /**
     * Composite key used for upsert and sync cleanup (appointment_id|Y-m-d H:i:s).
     *
     * @param string $appointment_id ChurchTools appointment ID
     * @param string $start_date_raw Raw date from API
     * @return string Composite key or empty when invalid
     */
    private function build_appointment_composite_key(string $appointment_id, string $start_date_raw): string {
        if ($appointment_id === '' || $start_date_raw === '') {
            return '';
        }

        $start_datetime = $this->format_datetime($start_date_raw);
        if ($start_datetime === '') {
            return '';
        }

        return $appointment_id . '|' . $start_datetime;
    }

    /**
     * Format datetime for database
     *
     * @param string $datetime Datetime string
     * @return string MySQL datetime format
     */
    private function format_datetime(string $datetime): string {
        if (empty($datetime)) {
            return '';
        }
        
        $timestamp = strtotime($datetime);
        
        // v0.7.2.6: Log invalid datetime formats
        if ($timestamp === false) {
            ChurchTools_Suite_Logger::warning(
                'event_sync',
                'Invalid datetime format',
                ['datetime_input' => $datetime]
            );
            return '';
        }
        
        return date('Y-m-d H:i:s', $timestamp);
    }
    
    /**
     * Process event services for an event
     *
     * Extracts eventServices from event data, filters by selected services,
     * and saves them to event_services table.
     *
     * @param int $event_id Local event ID
     * @param array $event Event data from API
     * @return void
     */
    private function process_event_services($event_id, array $event) {
        // Get selected service IDs
        $selected_service_ids = $this->services_repo->get_selected_service_ids();
        
		ChurchTools_Suite_Logger::log(
			sprintf('Service Import Start - Event ID: %d | %d selected services: %s', 
				$event_id, count($selected_service_ids), implode(', ', $selected_service_ids)),
			ChurchTools_Suite_Logger::DEBUG
		);
		
		// If no services selected, skip
		if (empty($selected_service_ids)) {
			ChurchTools_Suite_Logger::log(
				'No services selected in Services-Tab, skipping import',
				ChurchTools_Suite_Logger::WARNING
			);
		$this->event_services_repo->delete_for_event($event_id);
		return 0;
	}
	
	// Extract eventServices from API response
	$event_services = isset($event['eventServices']) && is_array($event['eventServices']) 
		? $event['eventServices'] 
		: [];
	
	if (empty($event_services)) {
		ChurchTools_Suite_Logger::log(
			sprintf('Event %d has no eventServices in API response. Available keys: %s',
				$event_id, implode(', ', array_keys($event))),
			ChurchTools_Suite_Logger::WARNING
		);
		return 0;
	}
	
	ChurchTools_Suite_Logger::log(
		sprintf('Event %d has %d eventServices in API response', $event_id, count($event_services)),
		ChurchTools_Suite_Logger::DEBUG
	);
		
		// Process each service
		$imported_count = 0;
		foreach ($event_services as $event_service) {
			$service_id = isset($event_service['serviceId']) ? (string) $event_service['serviceId'] : null;
			
			// Skip if no service ID
			if (!$service_id) {
				ChurchTools_Suite_Logger::log(
					'Skipping - no serviceId found in event data',
					ChurchTools_Suite_Logger::WARNING
				);
				continue;
			}
			
			// Skip if service not selected
			if (!in_array($service_id, $selected_service_ids, true)) {
				continue; // Silent skip for unselected services
			}
			
			// Extract person name (v0.7.2.9: Check personId on eventService level)
			$person_name = '';
			
            // v0.7.2.9: personId is directly on eventService, not nested in person.
            // Do not skip unassigned services: keep service_name visible in list/single templates.
            if (!isset($event_service['personId']) || $event_service['personId'] === null) {
                ChurchTools_Suite_Logger::debug(
                    'event_sync',
                    sprintf('Importing service %s without assigned person (personId is null)', $service_id),
                    ['event_id' => $event_id, 'service_id' => $service_id, 'personId' => $event_service['personId'] ?? 'NOT_SET']
                );
            }
			
            // Person is assigned, try to get name from person object
			if (!empty($event_service['person']) && is_array($event_service['person'])) {
				$person = $event_service['person'];
				
				// v0.7.2.9: Debug log person structure
				ChurchTools_Suite_Logger::debug(
					'event_sync',
					sprintf('Service %s has person object', $service_id),
					[
						'event_id' => $event_id,
						'service_id' => $service_id,
						'personId' => $event_service['personId'],
						'has_firstName' => isset($person['firstName']),
						'has_lastName' => isset($person['lastName']),
						'person_keys' => array_keys($person)
					]
				);
				
                $first_name = $person['firstName'] ?? $person['domainAttributes']['firstName'] ?? '';
                $last_name = $person['lastName'] ?? $person['domainAttributes']['lastName'] ?? '';
				$person_name = trim($first_name . ' ' . $last_name);

                // Fallback: Some ChurchTools payloads provide only display-like fields
                // for external/non-registered people (without first/last name split).
                if ($person_name === '') {
                    $person_name = $this->extract_service_person_fallback_name($event_service);
                }
				
				// v0.7.2.9: Log extracted name
				ChurchTools_Suite_Logger::debug(
					'event_sync',
					sprintf('Extracted person name for service %s: "%s"', $service_id, $person_name),
					['event_id' => $event_id, 'first_name' => $first_name, 'last_name' => $last_name]
				);
			} else {
				// personId exists but no person object - use personId as fallback
				ChurchTools_Suite_Logger::debug(
					'event_sync',
					sprintf('Service %s has personId but no person object', $service_id),
					['event_id' => $event_id, 'service_id' => $service_id, 'personId' => $event_service['personId']]
				);
                $person_name = $this->extract_service_person_fallback_name($event_service);
			}

            if ($person_name === '') {
                $person_name = $this->extract_service_person_fallback_name($event_service);
            }

            if ($person_name === '') {
                ChurchTools_Suite_Logger::debug(
                    'event_sync',
                    sprintf('Service %s has no resolvable person display name, keeping empty', $service_id),
                    [
                        'event_id' => $event_id,
                        'service_id' => $service_id,
                        'event_service_keys' => array_keys($event_service),
                    ]
                );
            }
			
			// Look up service name from repository
			$service = $this->services_repo->get_by_service_id($service_id);
			$service_name = $service ? $service->name : '';
			
			if (empty($service_name)) {
				ChurchTools_Suite_Logger::log(
					sprintf('Service %s not found in services table - skipping', $service_id),
					ChurchTools_Suite_Logger::WARNING
				);
				continue;
			}
			
			// Prepare service data
			$service_data = [
				'event_id' => $event_id,
				'service_id' => $service_id,
				'service_name' => $service_name,
				'person_name' => $person_name,
			];
			
			// Insert service
			$result = $this->event_services_repo->upsert($service_data);
			
			if ($result) {
				$imported_count++;
			} else {
				ChurchTools_Suite_Logger::log(
					sprintf('Failed to save service %s (%s) for event %d', $service_id, $service_name, $event_id),
					ChurchTools_Suite_Logger::ERROR
				);
			}
		}

		ChurchTools_Suite_Logger::log(
			sprintf('Service Import Complete - Event %d: %d services imported', $event_id, $imported_count),
			ChurchTools_Suite_Logger::DEBUG
		);

		return $imported_count;
	}

    /**
     * Extract a displayable person name from non-standard service payload fields.
     *
     * ChurchTools can return service assignees that are not linked to a CT person
     * (e.g. external/manual entries). In these cases personId may be null and
     * firstName/lastName may be empty, but a display label can still exist.
     *
     * @param array $event_service Event service payload
     * @return string
     */
    private function extract_service_person_fallback_name(array $event_service): string {
        $candidates = [
            $event_service['personName'] ?? '',
            $event_service['name'] ?? '',
            $event_service['displayName'] ?? '',
            $event_service['caption'] ?? '',
            $event_service['note'] ?? '',
            $event_service['comment'] ?? '',
            $event_service['text'] ?? '',
            $event_service['description'] ?? '',
            $event_service['person']['name'] ?? '',
            $event_service['person']['displayName'] ?? '',
            $event_service['person']['domainAttributes']['name'] ?? '',
            $event_service['person']['domainAttributes']['displayName'] ?? '',
            $event_service['person']['domainAttributes']['title'] ?? '',
        ];

        foreach ($candidates as $candidate) {
            if (!is_string($candidate)) {
                continue;
            }

            $candidate = trim($candidate);
            if ($candidate !== '') {
                return $candidate;
            }
        }

        return '';
    }

	/**
	 * Get last sync timestamp
     *
     * @return string|null MySQL timestamp or null
     */
    public function get_last_sync_time(): ?string {
        return get_option('churchtools_suite_events_last_sync', null);
    }
    
    /**
     * Normalize tag colors from ChurchTools color names to hex codes (v0.10.4.0)
     * 
     * ChurchTools uses predefined color names like "basic", "red", "blue".
     * We need hex codes for CSS styling.
     * 
     * @param array $tags Array of tag objects with 'color' field
     * @return array Normalized tags with hex color codes
     */
    private function normalize_tag_colors(array $tags): array {
        // ChurchTools color name → Hex code mapping
        $color_map = [
            'basic' => '#6b7280',      // Gray-500
            'red' => '#ef4444',        // Red-500
            'orange' => '#f97316',     // Orange-500
            'yellow' => '#eab308',     // Yellow-500
            'green' => '#22c55e',      // Green-500
            'blue' => '#3b82f6',       // Blue-500
            'indigo' => '#6366f1',     // Indigo-500
            'purple' => '#a855f7',     // Purple-500
            'pink' => '#ec4899',       // Pink-500
            'gray' => '#6b7280',       // Gray-500
            'grey' => '#6b7280',       // Gray-500
        ];
        
        foreach ($tags as &$tag) {
            if (isset($tag['color'])) {
                $color = strtolower($tag['color']);
                
                // If color is already hex code, keep it
                if (preg_match('/^#[0-9a-f]{6}$/i', $tag['color'])) {
                    continue;
                }
                
                // Map color name to hex code
                $tag['color'] = $color_map[$color] ?? '#6b7280'; // Default gray
            } else {
                // No color specified, use default
                $tag['color'] = '#6b7280';
            }
        }
        
        return $tags;
    }
}
