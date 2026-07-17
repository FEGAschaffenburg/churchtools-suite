<?php
/**
 * Logger Class (Simplified v1.1.4.2)
 * 
 * Simple logging wrapper using WordPress error_log().
 * Only logs errors and warnings in production, all levels in WP_DEBUG mode.
 *
 * @package ChurchTools_Suite
 * @since   0.3.13.3
 * @version 1.1.4.2 Simplified to use WordPress error_log()
 */

if (!defined('ABSPATH')) {
    exit;
}

class ChurchTools_Suite_Logger {
    
/**
 * Log levels (PSR-3 compatible)
 */
const LEVEL_DEBUG    = 'debug';
const LEVEL_INFO     = 'info';
const LEVEL_WARNING  = 'warning';
const LEVEL_ERROR    = 'error';
const LEVEL_CRITICAL = 'critical';

/**
 * Backward compatibility aliases
 */
const DEBUG    = self::LEVEL_DEBUG;
const INFO     = self::LEVEL_INFO;
const WARNING  = self::LEVEL_WARNING;
const ERROR    = self::LEVEL_ERROR;
const CRITICAL = self::LEVEL_CRITICAL;
    
    /**
     * Initialize logger (deprecated, kept for compatibility)
     */
    public static function init() {
        // No-op: Using WordPress error_log() now
    }
    
    /**
     * Write log entry (Simplified v1.1.4.2)
     *
     * @param string $message Log message (can include [context] prefix)
     * @param string $level Log level (debug, info, warning, error, critical)
     * @param array  $data Additional data to log
     */
    public static function log($message, string $level = 'info', array $data = []) {
        // Skip debug/info in production
        if (!WP_DEBUG && in_array($level, [self::LEVEL_DEBUG, self::LEVEL_INFO], true)) {
            return;
        }
        
        // Extract context from message if present
        $context = 'general';
        if (is_string($message) && preg_match('/^\[([^\]]+)\]\s*(.+)/', $message, $matches)) {
            $context = $matches[1];
            $message = $matches[2];
        }
        
        // Format message
        $formatted = sprintf(
            '[ChurchTools Suite] [%s] %s: %s',
            strtoupper($level),
            $context,
            is_string($message) ? $message : print_r($message, true)
        );
        
        // Add data if present
        if (!empty($data)) {
            $formatted .= ' | Data: ' . print_r($data, true);
        }
        
        // Log using WordPress error_log
        error_log($formatted);
    }

public static function debug(string $context, string $message, array $data = []) {
self::log("[$context] $message", self::LEVEL_DEBUG, $data);
}

public static function info(string $context, string $message, array $data = []) {
self::log("[$context] $message", self::LEVEL_INFO, $data);
}

public static function warning(string $context, string $message, array $data = []) {
self::log("[$context] $message", self::LEVEL_WARNING, $data);
}

public static function error(string $context, string $message, array $data = []) {
self::log("[$context] $message", self::LEVEL_ERROR, $data);
}

public static function critical(string $context, string $message, array $data = []) {
self::log("[$context] $message", self::LEVEL_CRITICAL, $data);
}

// Legacy methods kept for backward compatibility
public static function get_log_file(): ?string { return null; }
public static function get_log_content(int $lines = 100): array { return []; }
public static function get_statistics(): array {
return ['total_entries' => 0, 'file_size' => 0, 'oldest_entry' => null, 'newest_entry' => null, 'level_counts' => []];
}
public static function export_csv(int $lines = 1000): string { return "Using WordPress error_log() now\n"; }
public static function clear_log() {}
public static function get_log_files(): array { return []; }
}
