<?php
/**
 * Rate Limiter
 * 
 * Protects API and AJAX endpoints from excessive requests using WordPress Transients.
 * Implements sliding window rate limiting with per-minute and per-hour limits.
 *
 * @package ChurchTools_Suite
 * @since   0.7.0.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ChurchTools_Suite_Rate_Limiter {
	
	/**
	 * Rate limit: Requests per minute
	 */
	const LIMIT_PER_MINUTE = 60;
	
	/**
	 * Rate limit: Requests per hour
	 */
	const LIMIT_PER_HOUR = 1000;
	
	/**
	 * Transient prefix for rate limit counters
	 */
	const TRANSIENT_PREFIX = 'cts_rate_limit_';
	
	/**
	 * Check if request is allowed
	 * 
	 * @param string $identifier Unique identifier (e.g., user_id, ip_address, endpoint)
	 * @param string $context    Context (e.g., 'api', 'ajax', 'sync')
	 * @return bool True if allowed, false if rate limit exceeded
	 */
	public static function is_allowed( string $identifier, string $context = 'general' ): bool {
		// Bypass for localhost/development
		if ( self::is_development() ) {
			return true;
		}
		
		$minute_key = self::get_transient_key( $identifier, $context, 'minute' );
		$hour_key   = self::get_transient_key( $identifier, $context, 'hour' );
		
		// Get current counters
		$minute_count = (int) get_transient( $minute_key );
		$hour_count   = (int) get_transient( $hour_key );
		
		// Check limits
		if ( $minute_count >= self::LIMIT_PER_MINUTE ) {
			self::log_rate_limit_exceeded( $identifier, $context, 'minute', $minute_count );
			return false;
		}
		
		if ( $hour_count >= self::LIMIT_PER_HOUR ) {
			self::log_rate_limit_exceeded( $identifier, $context, 'hour', $hour_count );
			return false;
		}
		
		// Increment counters
		self::increment_counter( $minute_key, 60 ); // 1 minute TTL
		self::increment_counter( $hour_key, 3600 ); // 1 hour TTL
		
		return true;
	}
	
	/**
	 * Get current rate limit status
	 * 
	 * @param string $identifier Unique identifier
	 * @param string $context    Context
	 * @return array {
	 *     @type int $minute_count   Requests in current minute
	 *     @type int $minute_limit   Limit per minute
	 *     @type int $minute_remaining Remaining requests this minute
	 *     @type int $hour_count     Requests in current hour
	 *     @type int $hour_limit     Limit per hour
	 *     @type int $hour_remaining Remaining requests this hour
	 * }
	 */
	public static function get_status( string $identifier, string $context = 'general' ): array {
		$minute_key = self::get_transient_key( $identifier, $context, 'minute' );
		$hour_key   = self::get_transient_key( $identifier, $context, 'hour' );
		
		$minute_count = (int) get_transient( $minute_key );
		$hour_count   = (int) get_transient( $hour_key );
		
		return [
			'minute_count'     => $minute_count,
			'minute_limit'     => self::LIMIT_PER_MINUTE,
			'minute_remaining' => max( 0, self::LIMIT_PER_MINUTE - $minute_count ),
			'hour_count'       => $hour_count,
			'hour_limit'       => self::LIMIT_PER_HOUR,
			'hour_remaining'   => max( 0, self::LIMIT_PER_HOUR - $hour_count ),
		];
	}
	
	/**
	 * Reset rate limits for identifier
	 * 
	 * @param string $identifier Unique identifier
	 * @param string $context    Context
	 */
	public static function reset( string $identifier, string $context = 'general' ): void {
		$minute_key = self::get_transient_key( $identifier, $context, 'minute' );
		$hour_key   = self::get_transient_key( $identifier, $context, 'hour' );
		
		delete_transient( $minute_key );
		delete_transient( $hour_key );
	}
	
	/**
	 * Get transient key for rate limit counter
	 * 
	 * @param string $identifier Unique identifier
	 * @param string $context    Context
	 * @param string $window     Time window ('minute' or 'hour')
	 * @return string Transient key
	 */
	private static function get_transient_key( string $identifier, string $context, string $window ): string {
		// Sanitize identifier (max 64 chars for transient key compatibility)
		$safe_identifier = substr( sanitize_key( $identifier ), 0, 32 );
		$safe_context    = substr( sanitize_key( $context ), 0, 16 );
		
		return self::TRANSIENT_PREFIX . $safe_context . '_' . $safe_identifier . '_' . $window;
	}
	
	/**
	 * Increment rate limit counter
	 * 
	 * @param string $key Transient key
	 * @param int    $ttl Time to live in seconds
	 */
	private static function increment_counter( string $key, int $ttl ): void {
		$count = (int) get_transient( $key );
		$count++;
		set_transient( $key, $count, $ttl );
	}
	
	/**
	 * Check if running in development environment
	 * 
	 * @return bool True if development environment
	 */
	private static function is_development(): bool {
		// Check for localhost
		$is_local = in_array( $_SERVER['REMOTE_ADDR'] ?? '', [ '127.0.0.1', '::1' ], true );
		
		// Check for WP_DEBUG
		$is_debug = defined( 'WP_DEBUG' ) && WP_DEBUG;
		
		// Check for development constant
		$is_dev = defined( 'CHURCHTOOLS_SUITE_DEV' ) && CHURCHTOOLS_SUITE_DEV;
		
		return $is_local || $is_debug || $is_dev;
	}
	
	/**
	 * Log rate limit exceeded event
	 * 
	 * @param string $identifier Unique identifier
	 * @param string $context    Context
	 * @param string $window     Time window
	 * @param int    $count      Current count
	 */
	private static function log_rate_limit_exceeded( string $identifier, string $context, string $window, int $count ): void {
		if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
			ChurchTools_Suite_Logger::log( 'rate_limit', sprintf(
				'Rate limit exceeded - Identifier: %s, Context: %s, Window: %s, Count: %d',
				$identifier,
				$context,
				$window,
				$count
			), 'warning' );
		}
		
		// Log to debug.log in development
		if ( WP_DEBUG && WP_DEBUG_LOG ) {
			error_log( sprintf(
				'ChurchTools Suite Rate Limit: %s blocked (%s/%s) - %d requests',
				$identifier,
				$context,
				$window,
				$count
			) );
		}
	}
	
	/**
	 * Get rate limit statistics
	 * 
	 * Returns all active rate limit counters for monitoring purposes.
	 * 
	 * @return array Array of rate limit data
	 */
	public static function get_statistics(): array {
		global $wpdb;
		
		// Query all rate limit transients
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT option_name, option_value 
				FROM {$wpdb->options} 
				WHERE option_name LIKE %s 
				ORDER BY option_name ASC",
				'_transient_' . self::TRANSIENT_PREFIX . '%'
			)
		);
		
		$stats = [];
		
		foreach ( $results as $row ) {
			// Extract identifier from transient name
			$key = str_replace( '_transient_' . self::TRANSIENT_PREFIX, '', $row->option_name );
			$parts = explode( '_', $key );
			
			if ( count( $parts ) >= 3 ) {
				$context    = $parts[0];
				$identifier = implode( '_', array_slice( $parts, 1, -1 ) );
				$window     = end( $parts );
				
				$stats[] = [
					'context'    => $context,
					'identifier' => $identifier,
					'window'     => $window,
					'count'      => (int) $row->option_value,
					'limit'      => $window === 'minute' ? self::LIMIT_PER_MINUTE : self::LIMIT_PER_HOUR,
				];
			}
		}
		
		return $stats;
	}
	
	/**
	 * Clear all rate limit counters
	 * 
	 * Use with caution - this resets all rate limits.
	 */
	public static function clear_all(): void {
		global $wpdb;
		
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} 
				WHERE option_name LIKE %s 
				OR option_name LIKE %s",
				'_transient_' . self::TRANSIENT_PREFIX . '%',
				'_transient_timeout_' . self::TRANSIENT_PREFIX . '%'
			)
		);
	}
}
