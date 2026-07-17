<?php
/**
 * Calendars Repository
 *
 * Manages ChurchTools calendars in the database
 *
 * @package ChurchTools_Suite
 * @since   0.3.3.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class ChurchTools_Suite_Calendars_Repository extends ChurchTools_Suite_Repository_Base {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct(CHURCHTOOLS_SUITE_DB_PREFIX . 'calendars');
    }
    
    /**
     * Upsert (Insert or Update) a calendar by calendar_id
     * 
     * If calendar with this calendar_id exists, updates it (except is_selected).
     * Otherwise inserts new calendar.
     *
     * @param array $data Calendar data
     * @return int|false Calendar ID or false on error
     */
    public function upsert_by_calendar_id(array $data) {
        $defaults = [
            'calendar_id' => '',
            'name' => '',
            'name_translated' => null,
            'color' => null,
            'is_public' => 0,
            'is_selected' => 0,
            'sort_order' => null,
            'raw_payload' => null,
        ];
        $data = wp_parse_args($data, $defaults);
        
        // Check if calendar exists
        $existing_id = $this->db->get_var(
            $this->db->prepare(
                "SELECT id FROM {$this->table_name} WHERE calendar_id = %s",
                $data['calendar_id']
            )
        );
        
        if ($existing_id) {
            // Update: Keep is_selected (don't overwrite user selection)
            unset($data['is_selected']);
            $data['updated_at'] = $this->now();
            
            $this->db->update(
                $this->table_name,
                $data,
                ['id' => $existing_id]
            );
            
            return (int) $existing_id;
        }
        
        // Insert new calendar
        return $this->insert($data);
    }
    
    /**
     * Get calendar by ChurchTools calendar_id
     *
     * @param string $calendar_id ChurchTools calendar ID
     * @return object|null Calendar object or null
     */
    public function get_by_calendar_id(string $calendar_id) {
        return $this->db->get_row(
            $this->db->prepare(
                "SELECT * FROM {$this->table_name} WHERE calendar_id = %s",
                $calendar_id
            )
        );
    }
    
    /**
     * Get all selected calendars
     *
     * @return array Array of selected calendar objects
     */
    public function get_selected(): array {
        $sql = $this->db->prepare(
            "SELECT * FROM {$this->table_name} WHERE is_selected = %d ORDER BY sort_order ASC, name ASC",
            1
        );
        return $this->db->get_results($sql);
    }
    
    /**
     * Get IDs of selected calendars (WordPress IDs)
     *
     * @return array Array of calendar IDs
     */
    public function get_selected_ids(): array {
        $sql = $this->db->prepare(
            "SELECT id FROM {$this->table_name} WHERE is_selected = %d ORDER BY sort_order ASC",
            1
        );
        return array_map('intval', $this->db->get_col($sql));
    }
    
    /**
     * Get ChurchTools calendar_ids of selected calendars
     *
     * @return array Array of ChurchTools calendar IDs
     */
    public function get_selected_calendar_ids(): array {
        $sql = $this->db->prepare(
            "SELECT calendar_id FROM {$this->table_name} WHERE is_selected = %d ORDER BY sort_order ASC",
            1
        );
        return $this->db->get_col($sql);
    }
    
    /**
     * Set selection status for a calendar
     *
     * @param int $id Calendar ID
     * @param bool $selected Selected or not
     * @return bool Success
     */
    public function set_selected(int $id, bool $selected): bool {
        $result = $this->db->update(
            $this->table_name,
            ['is_selected' => $selected ? 1 : 0, 'updated_at' => $this->now()],
            ['id' => $id],
            ['%d', '%s'],
            ['%d']
        );
        return $result !== false;
    }
    
    /**
     * Update selected calendars (bulk operation)
     * 
     * Deselects all calendars, then selects the given IDs
     *
     * @param array $selected_ids Array of calendar IDs to select
     * @return bool Success
     */
    public function update_selected(array $selected_ids): bool {
        // Deselect all
        $this->db->query(
            "UPDATE {$this->table_name} SET is_selected = 0, updated_at = '{$this->now()}'"
        );
        
        if (empty($selected_ids)) {
            return true;
        }
        
        // Select given IDs
        $ids_placeholder = implode(',', array_fill(0, count($selected_ids), '%d'));
        $sql = $this->db->prepare(
            "UPDATE {$this->table_name} SET is_selected = 1, updated_at = %s WHERE id IN ({$ids_placeholder})",
            array_merge([$this->now()], $selected_ids)
        );
        
        return $this->db->query($sql) !== false;
    }
    
    /**
     * Count selected calendars
     *
     * @return int Number of selected calendars
     */
    public function count_selected(): int {
        $count = $this->db->get_var(
            $this->db->prepare(
                "SELECT COUNT(*) FROM {$this->table_name} WHERE is_selected = %d",
                1
            )
        );
        return (int) $count;
    }
    
    /**
     * Check if calendar with calendar_id exists
     *
     * @param string $calendar_id ChurchTools calendar ID
     * @return bool True if exists
     */
    public function exists_by_calendar_id(string $calendar_id): bool {
        $count = $this->db->get_var(
            $this->db->prepare(
                "SELECT COUNT(*) FROM {$this->table_name} WHERE calendar_id = %s",
                $calendar_id
            )
        );
        return (int) $count > 0;
    }
    
    /**
     * Update calendar_image_id by calendar_id (v0.9.9.58)
     *
     * @param string $calendar_id ChurchTools calendar ID
     * @param int $attachment_id WordPress attachment ID
     * @return bool Success
     */
    public function update_calendar_image_by_calendar_id(string $calendar_id, int $attachment_id): bool {
        $result = $this->db->update(
            $this->table_name,
            ['calendar_image_id' => $attachment_id, 'updated_at' => $this->now()],
            ['calendar_id' => $calendar_id],
            ['%d', '%s'],
            ['%s']
        );
        return $result !== false;
    }
}
