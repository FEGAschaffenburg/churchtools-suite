<?php
/**
 * Sync History Repository
 *
 * Manages sync history records in the database
 *
 * @package ChurchTools_Suite
 * @since   0.3.9.3
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-repository-base.php';

class ChurchTools_Suite_Sync_History_Repository extends ChurchTools_Suite_Repository_Base {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct('cts_sync_history');
    }
    
    /**
     * Create new sync history entry
     *
     * @param string $sync_type Type of sync (auto/manual)
     * @param string $started_at Start timestamp
     * @return int|false Sync ID or false on failure
     */
    public function create_sync_entry(string $sync_type, string $started_at) {
        return $this->db->insert(
            $this->table_name,
            [
                'sync_type' => $sync_type,
                'status' => 'running',
                'started_at' => $started_at
            ],
            ['%s', '%s', '%s']
        ) ? $this->db->insert_id : false;
    }
    
    /**
     * Complete sync entry with results
     *
     * @param int $sync_id Sync ID
     * @param array $stats Sync statistics
     * @param string|null $error_message Error message if failed
     * @return bool Success status
     */
    public function complete_sync(int $sync_id, array $stats, ?string $error_message = null): bool {
        $completed_at = current_time('mysql');
        $started_at = $this->db->get_var(
            $this->db->prepare(
                "SELECT started_at FROM {$this->table_name} WHERE id = %d",
                $sync_id
            )
        );
        
        $duration = null;
        if ($started_at) {
            $duration = strtotime($completed_at) - strtotime($started_at);
        }
        
        $data = [
            'status' => $error_message ? 'error' : 'success',
            'calendars_processed' => $stats['calendars_processed'] ?? 0,
            'events_found' => $stats['events_found'] ?? 0,
            'events_inserted' => $stats['events_inserted'] ?? 0,
            'events_updated' => $stats['events_updated'] ?? 0,
            'events_skipped' => $stats['events_skipped'] ?? 0,
            'services_imported' => $stats['services_imported'] ?? 0,
            'error_message' => $error_message,
            'completed_at' => $completed_at,
            'duration_seconds' => $duration
        ];
        
        return (bool) $this->db->update(
            $this->table_name,
            $data,
            ['id' => $sync_id],
            ['%s', '%d', '%d', '%d', '%d', '%d', '%d', '%s', '%s', '%d'],
            ['%d']
        );
    }
    
    /**
     * Get recent sync history
     *
     * @param int $limit Number of records to retrieve
     * @return array Array of sync history objects
     */
    public function get_recent(int $limit = 10): array {
        return $this->db->get_results(
            $this->db->prepare(
                "SELECT * FROM {$this->table_name} ORDER BY started_at DESC LIMIT %d",
                $limit
            )
        );
    }
    
    /**
     * Get sync history by type
     *
     * @param string $sync_type Type of sync (auto/manual)
     * @param int $limit Number of records to retrieve
     * @return array Array of sync history objects
     */
    public function get_by_type(string $sync_type, int $limit = 10): array {
        return $this->db->get_results(
            $this->db->prepare(
                "SELECT * FROM {$this->table_name} WHERE sync_type = %s ORDER BY started_at DESC LIMIT %d",
                $sync_type,
                $limit
            )
        );
    }
    
    /**
     * Get failed syncs
     *
     * @param int $limit Number of records to retrieve
     * @return array Array of failed sync objects
     */
    public function get_failed(int $limit = 10): array {
        return $this->db->get_results(
            $this->db->prepare(
                "SELECT * FROM {$this->table_name} WHERE status = 'error' ORDER BY started_at DESC LIMIT %d",
                $limit
            )
        );
    }
    
    /**
     * Get statistics summary
     *
     * @param int $days Number of days to look back
     * @return object Statistics summary
     */
    public function get_stats_summary(int $days = 30): object {
        $date_from = date('Y-m-d H:i:s', current_time('timestamp') - ($days * DAY_IN_SECONDS));
        
        return $this->db->get_row(
            $this->db->prepare(
                "SELECT 
                    COUNT(*) as total_syncs,
                    SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as successful_syncs,
                    SUM(CASE WHEN status = 'error' THEN 1 ELSE 0 END) as failed_syncs,
                    SUM(events_inserted) as total_inserted,
                    SUM(events_updated) as total_updated,
                    AVG(duration_seconds) as avg_duration
                FROM {$this->table_name}
                WHERE started_at >= %s",
                $date_from
            )
        );
    }
    
    /**
     * Delete old sync history
     *
     * @param int $days Keep records from last X days
     * @return int Number of deleted records
     */
    public function cleanup_old(int $days = 90): int {
        $date_threshold = date('Y-m-d H:i:s', current_time('timestamp') - ($days * DAY_IN_SECONDS));
        
        return (int) $this->db->query(
            $this->db->prepare(
                "DELETE FROM {$this->table_name} WHERE started_at < %s",
                $date_threshold
            )
        );
    }
    
    /**
     * Cleanup stuck syncs (running status older than X minutes)
     *
     * @param int $minutes Minutes after which a running sync is considered stuck
     * @return int Number of fixed records
     */
    public function cleanup_stuck_syncs(int $minutes = 5): int {
        $threshold = date('Y-m-d H:i:s', current_time('timestamp') - ($minutes * 60));
        
        $updated = (int) $this->db->update(
            $this->table_name,
            [
                'status' => 'error',
                'error_message' => __('Sync wurde abgebrochen oder ist fehlgeschlagen (Timeout/Fatal Error)', 'churchtools-suite'),
                'completed_at' => current_time('mysql')
            ],
            [
                'status' => 'running'
            ],
            ['%s', '%s', '%s'],
            ['%s']
        );
        
        // Calculate duration for fixed syncs
        $this->db->query(
            $this->db->prepare(
                "UPDATE {$this->table_name} 
                SET duration_seconds = TIMESTAMPDIFF(SECOND, started_at, completed_at)
                WHERE status = 'error' 
                AND error_message LIKE %s 
                AND duration_seconds IS NULL",
                '%Timeout/Fatal Error%'
            )
        );
        
        return $updated;
    }
}
