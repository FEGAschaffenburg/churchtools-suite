<?php
/**
 * Logger Class
 * 
 * Structured logging system with log levels, file rotation, and JSON format.
 * Stores logs in wp-content/uploads/churchtools-suite-logs/
 *
 * @package ChurchTools_Suite
 * @since   0.3.13.3
 * @version 0.7.0.3 Enhanced with PSR-3 levels, JSON format, rotation
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
	 * Backward compatibility aliases (v0.7.2.6)
	 * @deprecated Use LEVEL_* constants instead
	 */
	const DEBUG    = self::LEVEL_DEBUG;
	const INFO     = self::LEVEL_INFO;
	const WARNING  = self::LEVEL_WARNING;
	const ERROR    = self::LEVEL_ERROR;
	const CRITICAL = self::LEVEL_CRITICAL;
	
	/**
	 * Max log file size (10 MB)
	 */
	const MAX_FILE_SIZE = 10 * 1024 * 1024;
	
	/**
	 * Log retention days
	 */
	const RETENTION_DAYS = 30;
	
    /**
     * Log file path
     */
    private static $log_file = null;
    
    /**
     * Initialize logger
     */
    public static function init() {
        try {
            $upload_dir = wp_upload_dir();
            
            // Check if upload_dir has errors
            if (isset($upload_dir['error']) && $upload_dir['error'] !== false) {
                return;
            }
            
            $log_dir = $upload_dir['basedir'] . '/churchtools-suite-logs';
            
            // Create log directory if it doesn't exist
            if (!file_exists($log_dir)) {
                wp_mkdir_p($log_dir);
                // Add .htaccess to protect log files
                @file_put_contents($log_dir . '/.htaccess', 'Deny from all');
            }
            
            self::$log_file = $log_dir . '/sync-' . date('Y-m-d') . '.log';
        } catch (Exception $e) {
            // Fail silently
            self::$log_file = null;
        }
    }
    
    /**
     * Write log entry (Enhanced v0.7.0.3, Fixed v0.7.2.6, Production v1.0.0)
     *
     * @param string $message Log message (can include [context] prefix)
     * @param string $level Log level (debug, info, warning, error, critical)
     * @param array  $data Additional data to log
     */
    public static function log($message, string $level = 'info', array $data = []) {
        try {
            if (!self::$log_file) {
                self::init();
            }
			
			// Production filter: Skip debug/info unless WP_DEBUG or Advanced Mode (v1.0.0)
			if (in_array($level, [self::LEVEL_DEBUG, self::LEVEL_INFO], true)) {
				$advanced_mode = get_option('churchtools_suite_advanced_mode', 0);
				if (!WP_DEBUG && !$advanced_mode) {
					return; // Skip low-level logs in production
				}
			}
			
			// Extract context from message if present: "[context] message" format (v0.7.2.6)
			$context = 'general';
			if (is_string($message) && preg_match('/^\[([^\]]+)\]\s*(.+)/', $message, $matches)) {
				$context = $matches[1];
				$message = $matches[2];
			}
			
			// Build log entry (JSON format for structured logging)
			$entry = [
				'timestamp' => current_time('mysql'),
				'level'     => $level,
				'context'   => $context,
				'message'   => is_string($message) ? $message : print_r($message, true),
				'data'      => $data,
				'user_id'   => get_current_user_id(),
				'ip'        => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
			];
			
			// Convert to JSON (single line for easy parsing)
			$json = wp_json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
            
            // Append to log file
            @file_put_contents(self::$log_file, $json, FILE_APPEND);
			
			// Check if rotation is needed
			self::maybe_rotate_log();
			
			// Also log to WP debug.log in development for critical errors
			if (WP_DEBUG && WP_DEBUG_LOG && in_array($level, [self::LEVEL_ERROR, self::LEVEL_CRITICAL], true)) {
				error_log(sprintf(
					'ChurchTools Suite [%s] %s: %s',
					strtoupper($level),
					$context,
					is_string($message) ? $message : print_r($message, true)
				));
			}
        } catch (Exception $e) {
            // Fail silently - logging should never break the application
        }
    }
	
	/**
	 * Log debug message (v0.7.0.3)
	 *
	 * @param string $context Context/category
	 * @param string $message Log message
	 * @param array  $data    Additional data
	 */
	public static function debug(string $context, string $message, array $data = []) {
		// v0.7.2.6: Fixed parameter passing
		self::log("[$context] $message", self::LEVEL_DEBUG, $data);
	}
	
	/**
	 * Log info message (v0.7.0.3)
	 *
	 * @param string $context Context/category
	 * @param string $message Log message
	 * @param array  $data    Additional data
	 */
	public static function info(string $context, string $message, array $data = []) {
		// v0.7.2.6: Fixed parameter passing
		self::log("[$context] $message", self::LEVEL_INFO, $data);
	}
	
	/**
	 * Log warning message (v0.7.0.3)
	 *
	 * @param string $context Context/category
	 * @param string $message Log message
	 * @param array  $data    Additional data
	 */
	public static function warning(string $context, string $message, array $data = []) {
		// v0.7.2.6: Fixed parameter passing
		self::log("[$context] $message", self::LEVEL_WARNING, $data);
	}
	
	/**
	 * Log error message (v0.7.0.3)
	 *
	 * @param string $context Context/category
	 * @param string $message Log message
	 * @param array  $data    Additional data
	 */
	public static function error(string $context, string $message, array $data = []) {
		// v0.7.2.6: Fixed parameter passing
		self::log("[$context] $message", self::LEVEL_ERROR, $data);
	}
	
	/**
	 * Log critical message (v0.7.0.3)
	 *
	 * @param string $context Context/category
	 * @param string $message Log message
	 * @param array  $data    Additional data
	 */
	public static function critical(string $context, string $message, array $data = []) {
		// v0.7.2.6: Fixed parameter passing
		self::log("[$context] $message", self::LEVEL_CRITICAL, $data);
	}
	
	/**
	 * Maybe rotate log file (v0.7.0.3)
	 * 
	 * Rotates log file if it exceeds MAX_FILE_SIZE.
	 */
	private static function maybe_rotate_log() {
		if (!self::$log_file || !file_exists(self::$log_file)) {
			return;
		}
		
		$size = filesize(self::$log_file);
		
		if ($size < self::MAX_FILE_SIZE) {
			return;
		}
		
		// Rotate: rename current file with timestamp
		$timestamp = date('Y-m-d_H-i-s');
		$log_dir = dirname(self::$log_file);
		$rotated_file = $log_dir . "/sync-{$timestamp}.log";
		
		rename(self::$log_file, $rotated_file);
		
		// Compress rotated file (if gzip available)
		if (function_exists('gzopen')) {
			$gz = gzopen($rotated_file . '.gz', 'wb9');
			if ($gz) {
				gzwrite($gz, file_get_contents($rotated_file));
				gzclose($gz);
				unlink($rotated_file);
			}
		}
		
		// Clean old logs
		self::cleanup_old_logs();
	}
	
	/**
	 * Cleanup old log files (v0.7.0.3)
	 * 
	 * Deletes log files older than RETENTION_DAYS.
	 */
	private static function cleanup_old_logs() {
		$upload_dir = wp_upload_dir();
		$log_dir = $upload_dir['basedir'] . '/churchtools-suite-logs';
		
		$files = glob($log_dir . '/sync-*.log*');
		
		if (empty($files)) {
			return;
		}
		
		$cutoff = current_time('timestamp') - (self::RETENTION_DAYS * DAY_IN_SECONDS);
		
		foreach ($files as $file) {
			if (filemtime($file) < $cutoff) {
				@unlink($file);
			}
		}
	}
    
    /**
     * Get log file path
     *
     * @return string|null Log file path
     */
    public static function get_log_file(): ?string {
        if (!self::$log_file) {
            self::init();
        }
        return self::$log_file;
    }
    
    /**
     * Get log file content (Enhanced v0.7.0.3)
     *
     * @param int $lines Number of lines to retrieve (default: 100)
     * @return array Array of parsed log entries
     */
    public static function get_log_content(int $lines = 100): array {
        try {
            if (!self::$log_file) {
                self::init();
            }
            
            if (!file_exists(self::$log_file)) {
                return [];
            }
			
			// Read file in reverse (newest first)
			$file_lines = file(self::$log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
			if (!$file_lines) {
				return [];
			}
			
			$file_lines = array_reverse($file_lines);
			
			// Limit to requested lines
			$file_lines = array_slice($file_lines, 0, $lines);
			
			$entries = [];
			
			foreach ($file_lines as $line) {
				// Try to decode JSON
				$entry = json_decode($line, true);
				
				if ($entry) {
					$entries[] = $entry;
				} else {
					// Fallback for old format
					if (preg_match('/\[(.+?)\] \[(.+?)\] (.+)/', $line, $matches)) {
						$entries[] = [
							'timestamp' => $matches[1],
							'level' => strtolower($matches[2]),
							'context' => 'legacy',
							'message' => $matches[3],
							'data' => [],
							'user_id' => 0,
							'ip' => 'unknown',
						];
					}
				}
			}
			
			return $entries;
            
        } catch (Exception $e) {
            return [];
        }
    }
	
	/**
	 * Get log statistics (v0.7.0.3)
	 *
	 * @return array Statistics about log file
	 */
	public static function get_statistics(): array {
		try {
			if (!self::$log_file) {
				self::init();
			}
			
			if (!file_exists(self::$log_file)) {
				return [
					'total_entries' => 0,
					'file_size' => 0,
					'oldest_entry' => null,
					'newest_entry' => null,
					'level_counts' => [],
				];
			}
			
			$lines = file(self::$log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
			$total = count($lines);
			
			$oldest = null;
			$newest = null;
			$level_counts = [
				self::LEVEL_DEBUG => 0,
				self::LEVEL_INFO => 0,
				self::LEVEL_WARNING => 0,
				self::LEVEL_ERROR => 0,
				self::LEVEL_CRITICAL => 0,
			];
			
			// Parse first and last entry for timestamps
			if (!empty($lines)) {
				$first = json_decode($lines[0], true);
				$last = json_decode($lines[$total - 1], true);
				
				$oldest = $first['timestamp'] ?? null;
				$newest = $last['timestamp'] ?? null;
			}
			
			// Count levels (sample last 1000 entries for performance)
			$sample_size = min(1000, $total);
			$sample_lines = array_slice($lines, -$sample_size);
			
			foreach ($sample_lines as $line) {
				$entry = json_decode($line, true);
				if ($entry && isset($entry['level']) && isset($level_counts[$entry['level']])) {
					$level_counts[$entry['level']]++;
				}
			}
			
			return [
				'total_entries' => $total,
				'file_size' => filesize(self::$log_file),
				'oldest_entry' => $oldest,
				'newest_entry' => $newest,
				'level_counts' => $level_counts,
			];
		} catch (Exception $e) {
			return [
				'total_entries' => 0,
				'file_size' => 0,
				'oldest_entry' => null,
				'newest_entry' => null,
				'level_counts' => [],
			];
		}
	}
	
	/**
	 * Export logs to CSV (v0.7.0.3)
	 *
	 * @param int $lines Number of lines to export
	 * @return string CSV content
	 */
	public static function export_csv(int $lines = 1000): string {
		$entries = self::get_log_content($lines);
		
		$csv = "Timestamp,Level,Context,Message,User ID,IP\n";
		
		foreach ($entries as $entry) {
			$csv .= sprintf(
				'"%s","%s","%s","%s","%s","%s"' . "\n",
				$entry['timestamp'] ?? '',
				$entry['level'] ?? '',
				$entry['context'] ?? '',
				str_replace('"', '""', $entry['message'] ?? ''),
				$entry['user_id'] ?? '',
				$entry['ip'] ?? ''
			);
		}
		
		return $csv;
	}
    
    /**
     * Clear log file
     */
    public static function clear_log() {
        try {
            if (!self::$log_file) {
                self::init();
            }
            
            if (self::$log_file && file_exists(self::$log_file)) {
                @file_put_contents(self::$log_file, '');
            }
        } catch (Exception $e) {
            // Fail silently
        }
    }
    
    /**
     * Get all log files
     *
     * @return array Array of log file names
     */
    public static function get_log_files(): array {
        $upload_dir = wp_upload_dir();
        $log_dir = $upload_dir['basedir'] . '/churchtools-suite-logs';
        
        if (!file_exists($log_dir)) {
            return [];
        }
        
        $files = glob($log_dir . '/sync-*.log');
        
        // Sort by date (newest first)
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        
        return array_map('basename', $files);
    }
}
