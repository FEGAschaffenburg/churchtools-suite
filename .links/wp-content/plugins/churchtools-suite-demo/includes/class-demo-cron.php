<?php
/**
 * Demo Plugin Cron Job Handler
 *
 * Handles scheduled tasks for the Demo Plugin:
 * - Auto-cleanup of expired demos
 * - Demo expiration notifications
 *
 * @package ChurchTools_Suite_Demo
 * @since   1.1.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ChurchTools_Suite_Demo_Cron {
	
	/**
	 * Initialize cron system
	 */
	public static function init(): void {
		// Register custom cron intervals
		add_filter( 'cron_schedules', [ __CLASS__, 'add_custom_cron_intervals' ] );
		
		// Register cron actions
		add_action( 'cts_demo_cleanup_expired', [ __CLASS__, 'cleanup_expired_demos' ] );
		add_action( 'cts_demo_notify_expiring', [ __CLASS__, 'notify_expiring_demos' ] );
	}
	
	/**
	 * Add custom cron intervals for Demo Plugin
	 * 
	 * @param array $schedules Existing schedules
	 * @return array Modified schedules
	 */
	public static function add_custom_cron_intervals( array $schedules ): array {
		// Täglich (falls nicht bereits definiert)
		if ( ! isset( $schedules['daily'] ) ) {
			$schedules['daily'] = [
				'interval' => 86400, // 24 * 60 * 60
				'display'  => __( 'Täglich', 'churchtools-suite-demo' )
			];
		}
		
		// Alle 6 Stunden für häufigere Cleanup-Checks
		$schedules['cts_demo_6hours'] = [
			'interval' => 21600, // 6 * 60 * 60
			'display'  => __( 'Alle 6 Stunden', 'churchtools-suite-demo' )
		];
		
		return $schedules;
	}
	
	/**
	 * Schedule cron jobs (called on activation)
	 */
	public static function schedule_jobs(): void {
		// Clear existing schedules first
		self::clear_jobs();
		
		// Schedule cleanup job based on auto_cleanup setting
		$auto_cleanup = get_option( 'cts_demo_auto_cleanup', true );
		if ( $auto_cleanup ) {
			if ( ! wp_next_scheduled( 'cts_demo_cleanup_expired' ) ) {
				wp_schedule_event( time(), 'cts_demo_6hours', 'cts_demo_cleanup_expired' );
			}
		}
		
		// Schedule notification job (always, independent of cleanup)
		if ( ! wp_next_scheduled( 'cts_demo_notify_expiring' ) ) {
			wp_schedule_event( time(), 'daily', 'cts_demo_notify_expiring' );
		}
	}
	
	/**
	 * Clear scheduled cron jobs (called on deactivation)
	 */
	public static function clear_jobs(): void {
		$timestamp = wp_next_scheduled( 'cts_demo_cleanup_expired' );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'cts_demo_cleanup_expired' );
		}
		
		$timestamp = wp_next_scheduled( 'cts_demo_notify_expiring' );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'cts_demo_notify_expiring' );
		}
	}
	
	/**
	 * Update cleanup schedule based on settings
	 * 
	 * Called when settings change.
	 */
	public static function update_cleanup_schedule(): void {
		$auto_cleanup = get_option( 'cts_demo_auto_cleanup', true );
		
		// Clear existing cleanup schedule
		$timestamp = wp_next_scheduled( 'cts_demo_cleanup_expired' );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'cts_demo_cleanup_expired' );
		}
		
		// Schedule if enabled
		if ( $auto_cleanup ) {
			wp_schedule_event( time(), 'cts_demo_6hours', 'cts_demo_cleanup_expired' );
		}
	}
	
	/**
	 * Cleanup expired demos (cron callback)
	 * 
	 * Uses demo_duration_days setting to determine expiration.
	 * Deletes:
	 * - Expired demo users and their WordPress accounts
	 * - Associated demo pages
	 * - Associated registration records
	 */
	public static function cleanup_expired_demos(): void {
		$auto_cleanup = get_option( 'cts_demo_auto_cleanup', true );
		if ( ! $auto_cleanup ) {
			return;
		}
		
		$demo_duration_days = (int) get_option( 'cts_demo_duration_days', 30 );
		
		// Get expired demo users
		$expired_users = self::get_expired_demo_users( $demo_duration_days );
		
		if ( empty( $expired_users ) ) {
			return;
		}
		
		$deleted_count = 0;
		$failed_count = 0;
		
		foreach ( $expired_users as $demo_user ) {
			$result = self::delete_demo_user( $demo_user );
			if ( $result ) {
				$deleted_count++;
			} else {
				$failed_count++;
			}
		}
		
		// Log results
		if ( $deleted_count > 0 || $failed_count > 0 ) {
			error_log( sprintf(
				'[CTS Demo Cleanup] Deleted %d expired demos, %d failed. Duration: %d days.',
				$deleted_count,
				$failed_count,
				$demo_duration_days
			) );
		}
		
		// Notify admin if enabled
		$admin_notifications = get_option( 'cts_demo_admin_notifications', true );
		if ( $admin_notifications && $deleted_count > 0 ) {
			self::send_cleanup_notification( $deleted_count, $failed_count, $demo_duration_days );
		}
	}
	
	/**
	 * Get expired demo users
	 * 
	 * @param int $demo_duration_days Expiration duration in days
	 * @return array Array of demo user objects
	 */
	private static function get_expired_demo_users( int $demo_duration_days ): array {
		global $wpdb;
		$table = $wpdb->prefix . 'cts_demo_users';
		
		$expiry_date = gmdate( 'Y-m-d H:i:s', strtotime( "-{$demo_duration_days} days" ) );
		
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE created_at < %s",
				$expiry_date
			)
		);
		
		return $results ?: [];
	}
	
	/**
	 * Delete a demo user and all associated data
	 * 
	 * @param object $demo_user Demo user database record
	 * @return bool Success status
	 */
	private static function delete_demo_user( $demo_user ): bool {
		global $wpdb;
		$table = $wpdb->prefix . 'cts_demo_users';
		
		// Delete WordPress user (this also triggers cleanup of demo pages via hook)
		if ( ! empty( $demo_user->wordpress_user_id ) ) {
			require_once ABSPATH . 'wp-admin/includes/user.php';
			$result = wp_delete_user( $demo_user->wordpress_user_id );
			if ( ! $result ) {
				error_log( sprintf(
					'[CTS Demo Cleanup] Failed to delete WP user %d for demo user %d',
					$demo_user->wordpress_user_id,
					$demo_user->id
				) );
				return false;
			}
		}
		
		// Delete demo user record
		$deleted = $wpdb->delete(
			$table,
			[ 'id' => $demo_user->id ],
			[ '%d' ]
		);
		
		return $deleted !== false;
	}
	
	/**
	 * Send cleanup notification to admin
	 * 
	 * @param int $deleted_count Number of deleted demos
	 * @param int $failed_count Number of failed deletions
	 * @param int $demo_duration_days Demo duration setting
	 */
	private static function send_cleanup_notification( int $deleted_count, int $failed_count, int $demo_duration_days ): void {
		$admin_email = get_option( 'admin_email' );
		$site_name = get_option( 'blogname' );
		
		$subject = sprintf(
			'[%s] ChurchTools Demo: %d Demos gelöscht',
			$site_name,
			$deleted_count
		);
		
		$message = sprintf(
			"ChurchTools Suite Demo - Cleanup Report\n\n" .
			"Gelöschte Demos: %d\n" .
			"Fehlgeschlagen: %d\n" .
			"Demo-Dauer: %d Tage\n\n" .
			"Zeit: %s\n\n" .
			"Diese E-Mail wurde automatisch vom ChurchTools Demo Plugin gesendet.",
			$deleted_count,
			$failed_count,
			$demo_duration_days,
			current_time( 'mysql' )
		);
		
		wp_mail( $admin_email, $subject, $message );
	}
	
	/**
	 * Notify about expiring demos (cron callback)
	 * 
	 * Sends notification about demos expiring in the next 3 days.
	 */
	public static function notify_expiring_demos(): void {
		$admin_notifications = get_option( 'cts_demo_admin_notifications', true );
		if ( ! $admin_notifications ) {
			return;
		}
		
		$demo_duration_days = (int) get_option( 'cts_demo_duration_days', 30 );
		
		// Get demos expiring in next 3 days
		$expiring_users = self::get_expiring_demo_users( $demo_duration_days, 3 );
		
		if ( empty( $expiring_users ) ) {
			return;
		}
		
		self::send_expiring_notification( $expiring_users, $demo_duration_days );
	}
	
	/**
	 * Get demo users expiring soon
	 * 
	 * @param int $demo_duration_days Demo duration in days
	 * @param int $warning_days Days before expiration to warn
	 * @return array Array of demo user objects
	 */
	private static function get_expiring_demo_users( int $demo_duration_days, int $warning_days ): array {
		global $wpdb;
		$table = $wpdb->prefix . 'cts_demo_users';
		
		$expiry_date = gmdate( 'Y-m-d H:i:s', strtotime( "-{$demo_duration_days} days" ) );
		$warning_date = gmdate( 'Y-m-d H:i:s', strtotime( "-" . ( $demo_duration_days - $warning_days ) . " days" ) );
		
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE created_at < %s AND created_at >= %s",
				$warning_date,
				$expiry_date
			)
		);
		
		return $results ?: [];
	}
	
	/**
	 * Send expiring demos notification to admin
	 * 
	 * @param array $expiring_users Array of demo user objects
	 * @param int $demo_duration_days Demo duration setting
	 */
	private static function send_expiring_notification( array $expiring_users, int $demo_duration_days ): void {
		$admin_email = get_option( 'admin_email' );
		$site_name = get_option( 'blogname' );
		
		$subject = sprintf(
			'[%s] ChurchTools Demo: %d Demos laufen bald ab',
			$site_name,
			count( $expiring_users )
		);
		
		$message = sprintf(
			"ChurchTools Suite Demo - Ablauf-Warnung\n\n" .
			"%d Demo-Accounts laufen in den nächsten 3 Tagen ab:\n\n",
			count( $expiring_users )
		);
		
		foreach ( $expiring_users as $user ) {
			$days_remaining = $demo_duration_days - floor( ( time() - strtotime( $user->created_at ) ) / 86400 );
			$message .= sprintf(
				"- %s (erstellt: %s, noch %d Tage)\n",
				$user->email,
				$user->created_at,
				max( 0, $days_remaining )
			);
		}
		
		$message .= sprintf(
			"\n\nDemo-Dauer: %d Tage\n" .
			"Zeit: %s\n\n" .
			"Diese E-Mail wurde automatisch vom ChurchTools Demo Plugin gesendet.",
			$demo_duration_days,
			current_time( 'mysql' )
		);
		
		wp_mail( $admin_email, $subject, $message );
	}
	
	/**
	 * Get next scheduled run time for a cron job
	 * 
	 * @param string $hook Cron hook name
	 * @return int|false Timestamp or false if not scheduled
	 */
	public static function get_next_run( string $hook ) {
		return wp_next_scheduled( $hook );
	}
	
	/**
	 * Get all scheduled Demo Plugin cron jobs
	 * 
	 * @return array Array of cron job information
	 */
	public static function get_scheduled_jobs(): array {
		$jobs = [];
		
		$cron_hooks = [
			'cts_demo_cleanup_expired',
			'cts_demo_notify_expiring',
		];
		
		foreach ( $cron_hooks as $hook ) {
			$next_run = wp_next_scheduled( $hook );
			if ( $next_run ) {
				$jobs[] = [
					'hook' => $hook,
					'next_run' => $next_run,
					'schedule' => self::get_schedule_for_hook( $hook ),
				];
			}
		}
		
		return $jobs;
	}
	
	/**
	 * Get schedule interval for a cron hook
	 * 
	 * @param string $hook Cron hook name
	 * @return string|false Schedule interval name or false
	 */
	private static function get_schedule_for_hook( string $hook ) {
		$crons = _get_cron_array();
		if ( empty( $crons ) ) {
			return false;
		}
		
		foreach ( $crons as $timestamp => $cron ) {
			if ( isset( $cron[ $hook ] ) && is_array( $cron[ $hook ] ) ) {
				$event = reset( $cron[ $hook ] );
				return $event['schedule'] ?? false;
			}
		}
		
		return false;
	}
}
