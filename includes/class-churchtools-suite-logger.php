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
        $log_file = self::get_log_file();
        if ($log_file) {
            $log_dir = dirname($log_file);
            if (!is_dir($log_dir)) {
                if (function_exists('wp_mkdir_p')) {
                    wp_mkdir_p($log_dir);
                } else {
                    @mkdir($log_dir, 0755, true);
                }
            }
            if (!file_exists($log_file)) {
                @file_put_contents($log_file, '');
            }
        }
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
        
        // Keep data one-line to simplify parsing in admin log view.
        if (!empty($data)) {
            $encoded = wp_json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $formatted .= ' | Data: ' . ($encoded !== false ? $encoded : print_r($data, true));
        }

        $timestamp = function_exists('current_time') ? current_time('mysql') : date('Y-m-d H:i:s');
        $line = sprintf('[%s] %s', $timestamp, $formatted);
        
        // Keep server-level visibility.
        error_log($line);

        // Keep plugin-local file for Debug tab reliability.
        $log_file = self::get_log_file();
        if ($log_file) {
            @file_put_contents($log_file, $line . PHP_EOL, FILE_APPEND | LOCK_EX);
        }
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

public static function is_deep_debug_enabled(): bool {
    if (!defined('WP_DEBUG') || !WP_DEBUG) {
        return false;
    }

    if (defined('CHURCHTOOLS_SUITE_DEEP_DEBUG')) {
        return (bool) CHURCHTOOLS_SUITE_DEEP_DEBUG;
    }

    if (function_exists('get_option')) {
        return (bool) get_option('churchtools_suite_deep_debug', false);
    }

    return false;
}

public static function get_log_file(): ?string {
    $base_dir = '';

    if (function_exists('wp_upload_dir')) {
        $upload_dir = wp_upload_dir(null, false);
        if (is_array($upload_dir) && empty($upload_dir['error']) && !empty($upload_dir['basedir'])) {
            $base_dir = (string) $upload_dir['basedir'];
        }
    }

    if ($base_dir === '' && defined('WP_CONTENT_DIR')) {
        $base_dir = WP_CONTENT_DIR . '/uploads';
    }

    if ($base_dir === '' && defined('ABSPATH')) {
        $base_dir = rtrim(ABSPATH, '/\\') . '/wp-content/uploads';
    }

    if ($base_dir === '') {
        return null;
    }

    return rtrim($base_dir, '/\\') . '/churchtools-suite.log';
}

public static function get_log_content(int $lines = 100): array {
    $log_file = self::get_log_file();
    if (!$log_file || !file_exists($log_file) || !is_readable($log_file)) {
        return [];
    }

    $all = @file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!is_array($all) || empty($all)) {
        return [];
    }

    $tail = array_slice($all, -max(1, $lines));
    $entries = [];

    foreach ($tail as $line) {
        $parsed = self::parse_log_line((string) $line);
        if ($parsed !== null) {
            $entries[] = $parsed;
        }
    }

    return $entries;
}

public static function get_statistics(): array {
    $entries = self::get_log_content(10000);
    $level_counts = [
        self::LEVEL_DEBUG => 0,
        self::LEVEL_INFO => 0,
        self::LEVEL_WARNING => 0,
        self::LEVEL_ERROR => 0,
        self::LEVEL_CRITICAL => 0,
    ];

    foreach ($entries as $entry) {
        $level = strtolower((string) ($entry['level'] ?? ''));
        if (isset($level_counts[$level])) {
            $level_counts[$level]++;
        }
    }

    $log_file = self::get_log_file();
    $file_size = ($log_file && file_exists($log_file)) ? (int) filesize($log_file) : 0;

    return [
        'total_entries' => count($entries),
        'file_size' => $file_size,
        'oldest_entry' => !empty($entries) ? ($entries[0]['timestamp'] ?? null) : null,
        'newest_entry' => !empty($entries) ? ($entries[count($entries) - 1]['timestamp'] ?? null) : null,
        'level_counts' => $level_counts,
    ];
}

public static function export_csv(int $lines = 1000): string {
    $entries = self::get_log_content($lines);
    $out = "timestamp,level,context,message\n";

    foreach ($entries as $entry) {
        $row = [
            (string) ($entry['timestamp'] ?? ''),
            (string) ($entry['level'] ?? ''),
            (string) ($entry['context'] ?? ''),
            str_replace('"', '""', (string) ($entry['message'] ?? '')),
        ];
        $out .= sprintf("\"%s\",\"%s\",\"%s\",\"%s\"\n", $row[0], $row[1], $row[2], $row[3]);
    }

    return $out;
}

public static function clear_log() {
    $log_file = self::get_log_file();
    if ($log_file && file_exists($log_file)) {
        @file_put_contents($log_file, '');
    }
}

public static function get_log_files(): array {
    $log_file = self::get_log_file();
    if ($log_file && file_exists($log_file)) {
        return [$log_file];
    }
    return [];
}

private static function parse_log_line(string $line): ?array {
    $pattern = '/^\[(?<timestamp>[^\]]+)\]\s+\[ChurchTools Suite\]\s+\[(?<level>[A-Z]+)\]\s+(?<context>[^:]+):\s*(?<message>.*)$/';
    if (!preg_match($pattern, $line, $m)) {
        return null;
    }

    return [
        'timestamp' => trim((string) $m['timestamp']),
        'level' => strtolower(trim((string) $m['level'])),
        'context' => trim((string) $m['context']),
        'message' => trim((string) $m['message']),
    ];
}
}
