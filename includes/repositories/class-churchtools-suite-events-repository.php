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
     * Upsert (Insert or Update) an event by event_id
     * 
     * If event with this event_id exists, updates it.
     * Otherwise inserts new event.
     *
     * @param array $data Event data
     * @return int|false Event ID or false on error
     */
    public function upsert_by_event_id(array $data) {
        $defaults = [
            'event_id' => '',
            'calendar_id' => null,
            'appointment_id' => null,
            'title' => '',
            'description' => null,
            'start_datetime' => null,
            'end_datetime' => null,
            'is_all_day' => 0,
            'location_name' => null,
            'status' => null,
            'raw_payload' => null,
        ];
        $data = wp_parse_args($data, $defaults);
        
        // Check if event exists
        $existing_id = $this->db->get_var(
            $this->db->prepare(
                "SELECT id FROM {$this->table_name} WHERE event_id = %s",
                $data['event_id']
            )
        );
        
        if ($existing_id) {
            // Update existing event
            $data['updated_at'] = $this->now();
            
            $this->db->update(
                $this->table_name,
                $data,
                ['id' => $existing_id]
            );
            
            return (int) $existing_id;
        }
        
        // Insert new event
        return $this->insert($data);
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
     * Get event by ChurchTools appointment_id
     *
     * @param string $appointment_id ChurchTools appointment ID
     * @return object|null Event object or null
     */
    public function get_by_appointment_id(string $appointment_id) {
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
     * Check if event exists by event_id
     *
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
}
