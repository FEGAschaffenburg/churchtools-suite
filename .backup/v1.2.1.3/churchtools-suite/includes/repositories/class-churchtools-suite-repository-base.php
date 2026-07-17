<?php
/**
 * Repository Base Class
 *
 * Abstract base class providing common database operations for all repositories
 *
 * @package ChurchTools_Suite
 * @since   0.3.2.0
 */

if (!defined('ABSPATH')) {
    exit;
}

abstract class ChurchTools_Suite_Repository_Base {
    
    /**
     * WordPress Database object
     *
     * @var wpdb
     */
    protected $db;
    
    /**
     * Table name (without prefix)
     *
     * @var string
     */
    protected $table;
    
    /**
     * Full table name (with prefix)
     *
     * @var string
     */
    protected $table_name;
    
    /**
     * Constructor
     *
     * @param string $table Table name without wp prefix but with plugin prefix (e.g., 'cts_calendars')
     */
    public function __construct(string $table) {
        global $wpdb;
        $this->db = $wpdb;
        $this->table = $table;
        // Table already has plugin prefix, just add WordPress prefix
        $this->table_name = $wpdb->prefix . $table;
    }
    
    /**
     * Get full table name (for debugging)
     */
    public function get_table_name(): string {
        return $this->table_name;
    }
    
    /**
     * Get current timestamp in MySQL format (GMT)
     *
     * @return string MySQL datetime
     */
    protected function now(): string {
        return current_time('mysql', 1); // GMT
    }
    
    /**
     * Get a single record by ID
     *
     * @param int $id Record ID
     * @return object|null Record object or null if not found
     */
    public function get_by_id(int $id) {
        return $this->db->get_row(
            $this->db->prepare(
                "SELECT * FROM {$this->table_name} WHERE id = %d",
                $id
            )
        );
    }
    
    /**
     * Get all records
     *
     * @param string $orderby Order by column (default: 'id')
     * @param string $order Order direction (ASC|DESC, default: 'ASC')
     * @param int $limit Limit number of results (0 = no limit)
     * @return array Array of record objects
     */
    public function get_all(string $orderby = 'id', string $order = 'ASC', int $limit = 0): array {
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
        $sql = "SELECT * FROM {$this->table_name} ORDER BY {$orderby} {$order}";
        
        if ($limit > 0) {
            $sql .= $this->db->prepare(" LIMIT %d", $limit);
        }
        
        return $this->db->get_results($sql);
    }
    
    /**
     * Check if a record with given ID exists
     *
     * @param int $id Record ID
     * @return bool True if exists, false otherwise
     */
    public function exists(int $id): bool {
        $count = $this->db->get_var(
            $this->db->prepare(
                "SELECT COUNT(*) FROM {$this->table_name} WHERE id = %d",
                $id
            )
        );
        return (int) $count > 0;
    }
    
    /**
     * Insert a new record
     *
     * @param array $data Data to insert (column => value)
     * @return int|false New record ID or false on error
     */
    public function insert(array $data) {
        // Add automatic timestamps
        $data['created_at'] = $this->now();
        $data['updated_at'] = $this->now();
        
        // v0.9.2.2: Debug logging for insert operation
        if (class_exists('ChurchTools_Suite_Logger')) {
            ChurchTools_Suite_Logger::debug(
                'repository',
                sprintf('INSERT into %s', $this->table_name),
                [
                    'data_keys' => array_keys($data),
                    'has_address_name' => isset($data['address_name']),
                    'has_tags' => isset($data['tags']),
                    'address_name' => $data['address_name'] ?? 'NOT_SET',
                    'address_latitude' => $data['address_latitude'] ?? 'NOT_SET',
                    'tags' => isset($data['tags']) ? substr($data['tags'], 0, 100) : 'NOT_SET',
                ]
            );
        }
        
        $result = $this->db->insert($this->table_name, $data);
        
        if ($result === false) {
            // v0.9.2.2: Log wpdb error
            if (class_exists('ChurchTools_Suite_Logger') && !empty($this->db->last_error)) {
                ChurchTools_Suite_Logger::error(
                    'repository',
                    sprintf('INSERT failed for %s', $this->table_name),
                    [
                        'wpdb_error' => $this->db->last_error,
                        'last_query' => $this->db->last_query,
                    ]
                );
            }
            return false;
        }
        
        // v0.9.2.2: Log success
        if (class_exists('ChurchTools_Suite_Logger')) {
            ChurchTools_Suite_Logger::debug(
                'repository',
                sprintf('INSERT successful: ID %d', $this->db->insert_id),
                ['insert_id' => $this->db->insert_id]
            );
        }
        
        return (int) $this->db->insert_id;
    }
    
    /**
     * Update a record by ID
     *
     * @param int $id Record ID
     * @param array $data Data to update (column => value)
     * @return int|false Number of rows updated or false on error
     */
    public function update_by_id(int $id, array $data) {
        // Add automatic timestamp
        $data['updated_at'] = $this->now();
        
        return $this->db->update(
            $this->table_name,
            $data,
            ['id' => $id],
            null,
            ['%d']
        );
    }
    
    /**
     * Delete a record by ID
     *
     * @param int $id Record ID
     * @return int|false Number of rows deleted or false on error
     */
    public function delete_by_id(int $id) {
        return $this->db->delete(
            $this->table_name,
            ['id' => $id],
            ['%d']
        );
    }
    
    /**
     * Count total records
     *
     * @return int Number of records
     */
    public function count(): int {
        $count = $this->db->get_var(
            "SELECT COUNT(*) FROM {$this->table_name}"
        );
        return (int) $count;
    }
    
    /**
     * Delete all records
     *
     * @return int|false Number of rows deleted or false on error
     */
    public function delete_all() {
        return $this->db->query("DELETE FROM {$this->table_name}");
    }
    
    /**
     * Truncate table (delete all records and reset auto-increment)
     *
     * @return bool True on success, false on error
     */
    public function truncate(): bool {
        return $this->db->query("TRUNCATE TABLE {$this->table_name}") !== false;
    }
    
    /**
     * Get records with WHERE clause
     *
     * @param array $where WHERE conditions (column => value)
     * @param string $orderby Order by column
     * @param string $order Order direction (ASC|DESC)
     * @param int $limit Limit number of results
     * @return array Array of record objects
     */
    public function get_where(array $where, string $orderby = 'id', string $order = 'ASC', int $limit = 0): array {
        $where_clauses = [];
        $where_values = [];
        
        foreach ($where as $column => $value) {
            if (is_null($value)) {
                $where_clauses[] = "{$column} IS NULL";
            } else {
                $where_clauses[] = "{$column} = %s";
                $where_values[] = $value;
            }
        }
        
        $where_sql = implode(' AND ', $where_clauses);
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
        
        $sql = "SELECT * FROM {$this->table_name}";
        if (!empty($where_sql)) {
            $sql .= " WHERE {$where_sql}";
        }
        $sql .= " ORDER BY {$orderby} {$order}";
        
        if ($limit > 0) {
            $sql .= " LIMIT %d";
            $where_values[] = $limit;
        }
        
        if (!empty($where_values)) {
            $sql = $this->db->prepare($sql, $where_values);
        }
        
        return $this->db->get_results($sql);
    }
    
    /**
     * Get last database error
     *
     * @return string Error message
     */
    public function get_last_error(): string {
        return $this->db->last_error;
    }
}
