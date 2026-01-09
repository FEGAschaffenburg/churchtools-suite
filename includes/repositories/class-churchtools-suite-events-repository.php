<?php
/**
 * Events Repository
 *
 * Manages ChurchTools events in the database
 *
 * @package ChurchTools_Suite
 * @since   0.3.6.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class ChurchTools_Suite_Events_Repository extends ChurchTools_Suite_Repository_Base {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct(CHURCHTOOLS_SUITE_DB_PREFIX . 'events');
    }
    
    /**
     * Upsert (Insert or Update) an event by appointment_id + start_datetime (v0.9.0.0)
     * 
     * CRITICAL: Uses COMPOSITE KEY (appointment_id, start_datetime)!
     * Why? Appointment_id is the SERIES ID for recurring appointments.
     * Same appointment_id can appear multiple times with different start_datetime values.
     * 
     * Example: "Gottesdienst" has appointment_id=5084
     *   - 2025-10-31 17:00 (instance 1)
     *   - 2025-11-14 17:00 (instance 2)
     *   → Same appointment_id, different dates!
     * 
     * If event with this (appointment_id, start_datetime) exists, updates it.
     * Otherwise inserts new event.
     *
     * @param array $data Event data
     * @return int|false Event ID or false on error
     */
    public function upsert_by_appointment_id(array $data) {
        $defaults = [
            'event_id' => null, // v0.9.0.0: Can be NULL for standalone appointments
            'calendar_id' => null,
            'appointment_id' => null,
            'title' => '',
            'description' => null,
            'event_description' => null, // v0.9.1.0: Event-level description
            'appointment_description' => null, // v0.9.1.0: Appointment-level description
            'start_datetime' => null,
            'end_datetime' => null,
            'is_all_day' => 0,
            'location_name' => null,
            'address_name' => null, // v0.9.2.0: Address name (meetingAt)
            'address_street' => null, // v0.9.2.0: Street address
            'address_zip' => null, // v0.9.2.0: ZIP/postal code
            'address_city' => null, // v0.9.2.0: City
            'address_latitude' => null, // v0.9.2.0: GPS latitude
            'address_longitude' => null, // v0.9.2.0: GPS longitude
            'tags' => null, // v0.9.2.0: JSON array of tags
            'status' => null,
            'image_attachment_id' => null, // v0.10.5.0: WordPress attachment ID
            'image_url' => null, // v0.10.5.0: Fallback image URL
            'raw_payload' => null,
            'last_modified' => null, // v0.7.1.0: For incremental sync
            'appointment_modified' => null, // v0.8.1.0: For appointment-level changes
        ];
        $data = wp_parse_args($data, $defaults);
        
        // appointment_id AND start_datetime are required (composite key)
        if (empty($data['appointment_id']) || empty($data['start_datetime'])) {
            return false;
        }
        
        // Check if appointment exists using COMPOSITE KEY
        $existing_id = $this->db->get_var(
            $this->db->prepare(
                "SELECT id FROM {$this->table_name} WHERE appointment_id = %s AND start_datetime = %s",
                $data['appointment_id'],
                $data['start_datetime']
            )
        );
        
        if ($existing_id) {
            // Selective Update: Nur appointment-spezifische Felder überschreiben
            // v0.10.4.2: description wird ebenfalls aktualisiert (für saubere Appointment-Descriptions)
            $appointment_fields = [
                'description', // v0.10.4.2: Kombinierte description bei Appointments aktualisieren
                'appointment_description',
                'address_name',
                'address_street',
                'address_zip',
                'address_city',
                'address_latitude',
                'address_longitude',
                'tags',
                'image_attachment_id', // v0.10.5.0: WordPress attachment ID
                'image_url', // v0.10.5.0: Fallback image URL
                'appointment_modified',
                'raw_payload',
                'status',
                'updated_at',
            ];
            // Hole existierende Daten
            $existing = $this->get_by_id($existing_id);
            $update_data = [];
            foreach ($appointment_fields as $field) {
                if (array_key_exists($field, $data)) {
                    $update_data[$field] = $data[$field];
                }
            }
            $update_data['updated_at'] = $this->now();

            // Debug Logging
            if (class_exists('ChurchTools_Suite_Logger')) {
                ChurchTools_Suite_Logger::debug(
                    'repository',
                    sprintf('SELECTIVE UPDATE event ID %d (appointment_id: %s)', $existing_id, $data['appointment_id']),
                    [
                        'update_keys' => array_keys($update_data),
                        'has_address_name' => isset($update_data['address_name']),
                        'has_tags' => isset($update_data['tags']),
                        'address_name' => $update_data['address_name'] ?? 'NOT_SET',
                        'address_latitude' => $update_data['address_latitude'] ?? 'NOT_SET',
                        'tags' => isset($update_data['tags']) ? substr($update_data['tags'], 0, 100) : 'NOT_SET',
                    ]
                );
            }

            $result = $this->db->update(
                $this->table_name,
                $update_data,
                ['id' => $existing_id],
                null, // v0.9.0.1: Auto-detect format for data fields
                ['%d'] // WHERE id format
            );

            // Fehler-Logging (v0.9.0.1: Fixed false positives when no changes made)
            // $result kann sein: int (rows affected), false (error), oder 0 (no changes)
            if ($result === false) {
                if (class_exists('ChurchTools_Suite_Logger') && !empty($this->db->last_error)) {
                    ChurchTools_Suite_Logger::error(
                        'repository',
                        sprintf('SELECTIVE UPDATE failed for event ID %d', $existing_id),
                        [
                            'wpdb_error' => $this->db->last_error,
                            'last_query' => $this->db->last_query,
                        ]
                    );
                }
                return false; // v0.9.0.1: Return false on actual error
            }

            return (int) $existing_id;
        }
        
        // Insert new appointment
        return $this->insert($data);
    }
    
    /**
     * Upsert (Insert or Update) an event by event_id
     * 
     * DEPRECATED: Use upsert_by_appointment_id() instead!
     * Kept for backward compatibility only.
     *
     * @deprecated 0.9.0.0 Use upsert_by_appointment_id()
     * @param array $data Event data
     * @return int|false Event ID or false on error
     */
    public function upsert_by_event_id(array $data) {
        // Delegate to appointment-based upsert
        return $this->upsert_by_appointment_id($data);
    }
    
    /**
     * Get event by ChurchTools event_id
     *
     * @param string $event_id ChurchTools event ID
     * @return object|null Event object or null
     */
    public function get_by_event_id(string $event_id) {
        return $this->db->get_row(
            $this->db->prepare(
                "SELECT * FROM {$this->table_name} WHERE event_id = %s",
                $event_id
            )
        );
    }
    
    /**
     * Get event by ChurchTools appointment_id (v0.9.0.0: with optional start_datetime)
     * 
     * WARNING: appointment_id alone may return first match of multiple instances!
     * For recurring appointments, provide start_datetime to get specific instance.
     *
     * @param string $appointment_id ChurchTools appointment ID
     * @param string $start_datetime Optional start datetime for specific instance (Y-m-d H:i:s)
     * @return object|null Event object or null
     */
    public function get_by_appointment_id(string $appointment_id, string $start_datetime = '') {
        if (!empty($start_datetime)) {
            // Get specific instance by COMPOSITE KEY
            return $this->db->get_row(
                $this->db->prepare(
                    "SELECT * FROM {$this->table_name} WHERE appointment_id = %s AND start_datetime = %s",
                    $appointment_id,
                    $start_datetime
                )
            );
        }
        
        // Get first match (legacy behavior, may be ambiguous for recurring appointments)
        return $this->db->get_row(
            $this->db->prepare(
                "SELECT * FROM {$this->table_name} WHERE appointment_id = %s",
                $appointment_id
            )
        );
    }
    
    /**
     * Get internal ID by event_id
     *
     * @param string $event_id ChurchTools event ID
     * @return int|null Internal ID or null
     */
    public function get_id_by_event_id(string $event_id): ?int {
        $val = $this->db->get_var(
            $this->db->prepare(
                "SELECT id FROM {$this->table_name} WHERE event_id = %s",
                $event_id
            )
        );
        return $val !== null ? (int) $val : null;
    }
    
    /**
     * Get events by calendar_id
     *
     * @param string $calendar_id ChurchTools calendar ID
     * @param string $orderby Order by column (default: start_datetime)
     * @param string $order Order direction (ASC/DESC, default: ASC)
     * @param int|null $limit Limit results
     * @return array Array of event objects
     */
    public function get_by_calendar_id(string $calendar_id, string $orderby = 'start_datetime', string $order = 'ASC', ?int $limit = null): array {
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
        $orderby = sanitize_key($orderby);
        
        $sql = $this->db->prepare(
            "SELECT * FROM {$this->table_name} WHERE calendar_id = %s ORDER BY {$orderby} {$order}",
            $calendar_id
        );
        
        if ($limit !== null) {
            $sql .= $this->db->prepare(" LIMIT %d", $limit);
        }
        
        return $this->db->get_results($sql);
    }
    
    /**
     * Get upcoming events
     *
     * @param int|null $limit Limit results (default: 10)
     * @return array Array of event objects
     */
    public function get_upcoming(?int $limit = 10): array {
        $now = $this->now();
        
        $sql = $this->db->prepare(
            "SELECT * FROM {$this->table_name} 
            WHERE start_datetime >= %s 
            ORDER BY start_datetime ASC",
            $now
        );
        
        if ($limit !== null) {
            $sql .= $this->db->prepare(" LIMIT %d", $limit);
        }
        
        return $this->db->get_results($sql);
    }
    
    /**
     * Get events in date range
     *
     * @param string $start_date Start date (Y-m-d H:i:s)
     * @param string $end_date End date (Y-m-d H:i:s)
     * @param string $orderby Order by column
     * @param string $order Order direction
     * @return array Array of event objects
     */
    public function get_in_range(string $start_date, string $end_date, string $orderby = 'start_datetime', string $order = 'ASC'): array {
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
        $orderby = sanitize_key($orderby);
        
        return $this->db->get_results(
            $this->db->prepare(
                "SELECT * FROM {$this->table_name} 
                WHERE start_datetime >= %s AND start_datetime <= %s 
                ORDER BY {$orderby} {$order}",
                $start_date,
                $end_date
            )
        );
    }
    
    /**
     * Get events in date range with calendar filter (AJAX calendar navigation)
     * 
     * Used by ajax_load_calendar_month() for calendar navigation.
     * 
     * @param string $start_date Start date (Y-m-d H:i:s)
     * @param string $end_date End date (Y-m-d H:i:s)
     * @param array $calendar_ids Optional calendar IDs filter
     * @param int|null $limit Optional limit
     * @param string $orderby Order by column
     * @param string $order Order direction
     * @return array Array of event objects
     */
    public function get_events_in_range(string $start_date, string $end_date, array $calendar_ids = [], ?int $limit = null, string $orderby = 'start_datetime', string $order = 'ASC'): array {
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
        $orderby = sanitize_key($orderby);
        
        // Debug Logging
        if (class_exists('ChurchTools_Suite_Logger')) {
            ChurchTools_Suite_Logger::debug('repository', 'get_events_in_range called', [
                'start_date' => $start_date,
                'end_date' => $end_date,
                'calendar_ids' => $calendar_ids,
                'limit' => $limit,
            ]);
        }
        
        $sql = "SELECT * FROM {$this->table_name} WHERE start_datetime >= %s AND start_datetime <= %s";
        $params = [$start_date, $end_date];
        
        // Add calendar filter if specified
        if (!empty($calendar_ids) && is_array($calendar_ids)) {
            $placeholders = implode(',', array_fill(0, count($calendar_ids), '%s'));
            $sql .= " AND calendar_id IN ($placeholders)";
            $params = array_merge($params, $calendar_ids);
        }
        
        $sql .= " ORDER BY {$orderby} {$order}";
        
        // Add limit if specified
        if ($limit !== null && $limit > 0) {
            $sql .= " LIMIT %d";
            $params[] = $limit;
        }
        
        // Debug Logging
        if (class_exists('ChurchTools_Suite_Logger')) {
            ChurchTools_Suite_Logger::debug('repository', 'Executing SQL query', [
                'sql' => $sql,
                'params' => $params,
            ]);
        }
        
        $prepared = $this->db->prepare($sql, ...$params);
        
        // Debug Logging
        if (class_exists('ChurchTools_Suite_Logger')) {
            ChurchTools_Suite_Logger::debug('repository', 'Prepared SQL', [
                'query' => $prepared,
            ]);
        }
        
        $results = $this->db->get_results($prepared);
        
        // Error Logging
        if ($this->db->last_error && class_exists('ChurchTools_Suite_Logger')) {
            ChurchTools_Suite_Logger::error('repository', 'SQL error in get_events_in_range', [
                'error' => $this->db->last_error,
                'query' => $this->db->last_query,
            ]);
        }
        
        // Debug Logging
        if (class_exists('ChurchTools_Suite_Logger')) {
            ChurchTools_Suite_Logger::debug('repository', 'Query results', [
                'count' => is_array($results) ? count($results) : 0,
                'is_array' => is_array($results),
                'type' => gettype($results),
            ]);
        }
        
        return is_array($results) ? $results : [];
    }
    
    /**
     * Delete events older than specified date
     *
     * @param string $before_date Date before which to delete (Y-m-d H:i:s)
     * @return int Number of deleted rows
     */
    public function delete_older_than(string $before_date): int {
        $result = $this->db->query(
            $this->db->prepare(
                "DELETE FROM {$this->table_name} WHERE start_datetime < %s",
                $before_date
            )
        );
        
        return $result !== false ? $result : 0;
    }
    
    /**
     * Check if appointment exists by appointment_id + start_datetime (v0.9.0.0)
     * 
     * Uses COMPOSITE KEY (appointment_id, start_datetime).
     * Both parameters required because appointment_id alone is not unique!
     *
     * @param string $appointment_id ChurchTools appointment ID
     * @param string $start_datetime Start datetime (Y-m-d H:i:s)
     * @return bool True if exists
     */
    public function exists_by_appointment_id(string $appointment_id, string $start_datetime = ''): bool {
        // If no start_datetime provided, check by appointment_id only (may return true for any instance)
        if (empty($start_datetime)) {
            $count = $this->db->get_var(
                $this->db->prepare(
                    "SELECT COUNT(*) FROM {$this->table_name} WHERE appointment_id = %s",
                    $appointment_id
                )
            );
            return (int) $count > 0;
        }
        
        // Check by COMPOSITE KEY
        $count = $this->db->get_var(
            $this->db->prepare(
                "SELECT COUNT(*) FROM {$this->table_name} WHERE appointment_id = %s AND start_datetime = %s",
                $appointment_id,
                $start_datetime
            )
        );
        return (int) $count > 0;
    }
    
    /**
     * Check if event exists by event_id
     * 
     * @deprecated v0.9.0.0 Use exists_by_appointment_id() - event_id is no longer unique
     * @param string $event_id ChurchTools event ID
     * @return bool True if exists
     */
    public function exists_by_event_id(string $event_id): bool {
        $count = $this->db->get_var(
            $this->db->prepare(
                "SELECT COUNT(*) FROM {$this->table_name} WHERE event_id = %s",
                $event_id
            )
        );
        return (int) $count > 0;
    }
    
    /**
     * Get newest last_modified timestamp (v0.7.1.0)
     * 
     * Used for incremental sync to determine when last change occurred.
     *
     * @return string|null MySQL datetime or null if no events
     */
    public function get_newest_last_modified(): ?string {
        $result = $this->db->get_var(
            "SELECT MAX(last_modified) FROM {$this->table_name} WHERE last_modified IS NOT NULL"
        );
        return $result ? $result : null;
    }
    
    /**
     * Get all event IDs in date range (v0.7.1.0)
     * 
     * Used to detect deleted events by comparing with API response.
     *
     * @param string $start_date Start date (Y-m-d H:i:s)
     * @param string $end_date End date (Y-m-d H:i:s)
     * @param string|null $calendar_id Optional calendar filter
     * @return array Array of event_id strings
     */
    public function get_event_ids_in_range(string $start_date, string $end_date, ?string $calendar_id = null): array {
        $sql = "SELECT event_id FROM {$this->table_name} 
                WHERE start_datetime >= %s AND start_datetime <= %s";
        
        $params = [$start_date, $end_date];
        
        if ($calendar_id) {
            $sql .= " AND calendar_id = %s";
            $params[] = $calendar_id;
        }
        
        $results = $this->db->get_col(
            $this->db->prepare($sql, ...$params)
        );
        
        return $results ? $results : [];
    }
    
    /**
     * Delete events by event_id array (v0.7.1.0)
     * 
     * Used to remove events that were deleted in ChurchTools.
     *
     * @param array $event_ids Array of event_id strings
     * @return int Number of deleted rows
     */
    public function delete_by_event_ids(array $event_ids): int {
        if (empty($event_ids)) {
            return 0;
        }
        
        $placeholders = implode(',', array_fill(0, count($event_ids), '%s'));
        
        $result = $this->db->query(
            $this->db->prepare(
                "DELETE FROM {$this->table_name} WHERE event_id IN ($placeholders)",
                ...$event_ids
            )
        );
        
        return $result !== false ? $result : 0;
    }
}
