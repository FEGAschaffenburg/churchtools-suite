<?php
/**
 * User-Specific Settings Manager
 *
 * Manages per-user settings using WordPress user_meta instead of global options.
 * Enables multi-user isolation for demo environment.
 *
 * @package ChurchTools_Suite_Demo
 * @since   1.0.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ChurchTools_Suite_User_Settings {
	
	/**
	 * Get user-specific setting (v1.0.7.0: Demo mode support)
	 * 
	 * Tries user_meta first, falls back to global option for backwards compatibility.
	 * If user is in demo mode, returns demo-specific values for CT connection.
	 *
	 * @param string $key Setting key (without churchtools_suite_ prefix)
	 * @param int|null $user_id WordPress user ID (null = current user)
	 * @param mixed $default Default value if not found
	 * @return mixed Setting value
	 */
	public static function get( string $key, $user_id = null, $default = null ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}
		
		// v1.0.7.0: Check if user is in demo mode
		$demo_mode = get_user_meta( $user_id, 'cts_demo_mode', true );
		
		// If demo mode active, return demo values for CT connection
		if ( $demo_mode ) {
			$demo_values = [
				'ct_url' => 'https://demo.church.tools',
				'ct_login' => '', // Public demo instance (no auth needed)
				'ct_password' => '',
			];
			
			if ( isset( $demo_values[ $key ] ) ) {
				return $demo_values[ $key ];
			}
		}
		
		// Try user-specific setting first
		$meta_key = 'churchtools_suite_' . $key;
		$user_value = get_user_meta( $user_id, $meta_key, true );
		
		// If user setting exists, return it
		if ( $user_value !== '' && $user_value !== false ) {
			return $user_value;
		}
		
		// Fallback to global setting (for backwards compatibility)
		$option_key = 'churchtools_suite_' . $key;
		$global_value = get_option( $option_key );
		
		// Return global value or default
		return $global_value !== false ? $global_value : $default;
	}
	
	/**
	 * Set user-specific setting
	 *
	 * @param string $key Setting key (without churchtools_suite_ prefix)
	 * @param mixed $value Setting value
	 * @param int|null $user_id WordPress user ID (null = current user)
	 * @return int|bool Meta ID on success, false on failure
	 */
	public static function set( string $key, $value, $user_id = null ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}
		
		$meta_key = 'churchtools_suite_' . $key;
		return update_user_meta( $user_id, $meta_key, $value );
	}
	
	/**
	 * Delete user-specific setting
	 *
	 * @param string $key Setting key (without churchtools_suite_ prefix)
	 * @param int|null $user_id WordPress user ID (null = current user)
	 * @return bool True on success, false on failure
	 */
	public static function delete( string $key, $user_id = null ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}
		
		$meta_key = 'churchtools_suite_' . $key;
		return delete_user_meta( $user_id, $meta_key );
	}
	
	/**
	 * Get all user settings (for debugging/export)
	 *
	 * @param int|null $user_id WordPress user ID (null = current user)
	 * @return array Associative array of settings
	 */
	public static function get_all( $user_id = null ): array {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}
		
		global $wpdb;
		
		$results = $wpdb->get_results( $wpdb->prepare(
			"SELECT meta_key, meta_value FROM {$wpdb->usermeta} 
			WHERE user_id = %d AND meta_key LIKE 'churchtools_suite_%%'",
			$user_id
		), ARRAY_A );
		
		$settings = [];
		foreach ( $results as $row ) {
			// Remove prefix for cleaner keys
			$key = str_replace( 'churchtools_suite_', '', $row['meta_key'] );
			$settings[ $key ] = maybe_unserialize( $row['meta_value'] );
		}
		
		return $settings;
	}
	
	/**
	 * Delete all user settings (for cleanup when user is deleted)
	 *
	 * @param int $user_id WordPress user ID
	 * @return int Number of rows deleted
	 */
	public static function delete_all( int $user_id ): int {
		global $wpdb;
		
		return $wpdb->query( $wpdb->prepare(
			"DELETE FROM {$wpdb->usermeta} 
			WHERE user_id = %d AND meta_key LIKE 'churchtools_suite_%%'",
			$user_id
		) );
	}
	
	/**
	 * Check if user has any settings configured
	 *
	 * @param int|null $user_id WordPress user ID (null = current user)
	 * @return bool True if user has settings
	 */
	public static function has_settings( $user_id = null ): bool {
		$settings = self::get_all( $user_id );
		return ! empty( $settings );
	}
	
	/**
	 * Check if user is in demo mode (v1.0.7.0)
	 *
	 * @param int|null $user_id WordPress user ID (null = current user)
	 * @return bool True if demo mode enabled
	 */
	public static function is_demo_mode( $user_id = null ): bool {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}
		
		return (bool) get_user_meta( $user_id, 'cts_demo_mode', true );
	}
	
	/**
	 * Enable/disable demo mode for user (v1.0.7.0)
	 *
	 * @param bool $enabled True to enable demo mode
	 * @param int|null $user_id WordPress user ID (null = current user)
	 * @return bool Success
	 */
	public static function set_demo_mode( bool $enabled, $user_id = null ): bool {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}
		
		return update_user_meta( $user_id, 'cts_demo_mode', $enabled );
	}
	
	/**
	 * Migrate global settings to user settings
	 * 
	 * Useful for initial migration or when admin wants to share their settings
	 * with a specific user.
	 *
	 * @param int $user_id Target user ID
	 * @param array $keys Settings keys to migrate (without prefix)
	 * @return int Number of settings migrated
	 */
	public static function migrate_from_global( int $user_id, array $keys ): int {
		$migrated = 0;
		
		foreach ( $keys as $key ) {
			$option_key = 'churchtools_suite_' . $key;
			$value = get_option( $option_key );
			
			if ( $value !== false ) {
				self::set( $key, $value, $user_id );
				$migrated++;
			}
		}
		
		return $migrated;
	}
}
