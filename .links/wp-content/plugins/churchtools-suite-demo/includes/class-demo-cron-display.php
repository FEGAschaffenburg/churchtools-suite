<?php
/**
 * Demo Plugin Cron Job Display Names Helper
 *
 * Provides user-friendly display names and descriptions for Demo Plugin cron jobs.
 * Separate from the main plugin's cron display to avoid confusion.
 *
 * @package ChurchTools_Suite_Demo
 * @since   1.1.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ChurchTools_Suite_Demo_Cron_Display {
	
	/**
	 * Get human-readable display name for a cron hook
	 *
	 * @param string $hook Cron hook name
	 * @return string Display name
	 */
	public static function get_cron_display_name( string $hook ): string {
		$names = [
			'cts_demo_cleanup_expired' => __( 'Demo-Bereinigung', 'churchtools-suite-demo' ),
			'cts_demo_notify_expiring' => __( 'Demo-Ablauf-Benachrichtigung', 'churchtools-suite-demo' ),
		];
		
		return $names[ $hook ] ?? $hook;
	}
	
	/**
	 * Get human-readable description for a cron hook
	 *
	 * @param string $hook Cron hook name
	 * @return string Description
	 */
	public static function get_cron_description( string $hook ): string {
		$descriptions = [
			'cts_demo_cleanup_expired' => __( 'Löscht abgelaufene Demo-Accounts und deren Seiten automatisch.', 'churchtools-suite-demo' ),
			'cts_demo_notify_expiring' => __( 'Sendet tägliche Benachrichtigung über bald ablaufende Demos.', 'churchtools-suite-demo' ),
		];
		
		return $descriptions[ $hook ] ?? '';
	}
	
	/**
	 * Get cron schedule display name
	 * 
	 * @param string $schedule Schedule interval name
	 * @return string Display name
	 */
	public static function get_schedule_display_name( string $schedule ): string {
		$names = [
			'hourly' => __( 'Stündlich', 'churchtools-suite-demo' ),
			'twicedaily' => __( '2x täglich', 'churchtools-suite-demo' ),
			'daily' => __( 'Täglich', 'churchtools-suite-demo' ),
			'cts_demo_6hours' => __( 'Alle 6 Stunden', 'churchtools-suite-demo' ),
		];
		
		return $names[ $schedule ] ?? $schedule;
	}
	
	/**
	 * Format cron event for display
	 *
	 * @param string $hook Hook name
	 * @param int $next_run Next run timestamp
	 * @param string $schedule Schedule interval
	 * @return array Formatted display data
	 */
	public static function format_cron_event( string $hook, int $next_run, string $schedule ): array {
		return [
			'name' => self::get_cron_display_name( $hook ),
			'description' => self::get_cron_description( $hook ),
			'hook' => $hook,
			'next_run' => $next_run,
			'next_run_formatted' => self::format_next_run( $next_run ),
			'schedule' => $schedule,
			'schedule_display' => self::get_schedule_display_name( $schedule ),
		];
	}
	
	/**
	 * Format next run timestamp
	 * 
	 * @param int $timestamp Unix timestamp
	 * @return string Formatted date/time with relative
	 */
	private static function format_next_run( int $timestamp ): string {
		$date = gmdate( 'Y-m-d H:i:s', $timestamp );
		$local_date = get_date_from_gmt( $date, get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );
		
		$diff = $timestamp - time();
		$relative = self::format_time_diff( $diff );
		
		return sprintf( '%s (%s)', $local_date, $relative );
	}
	
	/**
	 * Format time difference
	 * 
	 * @param int $diff Time difference in seconds
	 * @return string Formatted difference
	 */
	public static function format_time_diff( int $diff ): string {
		if ( $diff < 0 ) {
			return __( 'Überfällig', 'churchtools-suite-demo' );
		}
		
		if ( $diff < 60 ) {
			return sprintf( _n( 'in %s Sekunde', 'in %s Sekunden', $diff, 'churchtools-suite-demo' ), $diff );
		}
		
		if ( $diff < 3600 ) {
			$minutes = floor( $diff / 60 );
			return sprintf( _n( 'in %s Minute', 'in %s Minuten', $minutes, 'churchtools-suite-demo' ), $minutes );
		}
		
		if ( $diff < 86400 ) {
			$hours = floor( $diff / 3600 );
			return sprintf( _n( 'in %s Stunde', 'in %s Stunden', $hours, 'churchtools-suite-demo' ), $hours );
		}
		
		$days = floor( $diff / 86400 );
		return sprintf( _n( 'in %s Tag', 'in %s Tagen', $days, 'churchtools-suite-demo' ), $days );
	}
	
	/**
	 * Get status icon for cron job
	 * 
	 * @param int $next_run Next run timestamp
	 * @return string HTML icon
	 */
	public static function get_status_icon( int $next_run ): string {
		$diff = $next_run - time();
		
		if ( $diff < 0 ) {
			return '<span class="dashicons dashicons-warning" style="color: #d63638;"></span>';
		}
		
		if ( $diff < 3600 ) {
			return '<span class="dashicons dashicons-clock" style="color: #dba617;"></span>';
		}
		
		return '<span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span>';
	}
}
