<?php
/**
 * Roles & Capabilities Management
 *
 * Defines custom WordPress roles and capabilities for ChurchTools Suite.
 * Option B: Role-based access control (cts_manager role for plugin configuration)
 *
 * @package ChurchTools_Suite
 * @since   1.0.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ChurchTools_Suite_Roles {
	
	/**
	 * Custom role: ChurchTools Manager
	 * Can manage plugin settings, sync, and configuration
	 */
	const ROLE_CTS_MANAGER = 'cts_manager';
	
	/**
	 * List of custom capabilities
	 */
	const CAPABILITIES = [
		'manage_churchtools_suite',        // Main capability
		'configure_churchtools_suite',     // Configure API settings
		'sync_churchtools_events',         // Trigger manual sync
		'manage_churchtools_calendars',    // Select/manage calendars
		'manage_churchtools_services',     // Select/manage services
		'view_churchtools_debug',          // View debug information
	];
	
	/**
	 * Register custom role and capabilities
	 * Called on plugin activation
	 * 
	 * NOTE: cts_manager role is DEPRECATED as of v1.0.3.15
	 * Use cts_demo_user from Demo Plugin instead
	 */
	public static function register_role(): void {
		// DO NOT create cts_manager role anymore
		// Demo users should use cts_demo_user role from demo-plugin
		
		// Only add capabilities to Administrator role (for backwards compatibility)
		$admin_role = get_role( 'administrator' );
		if ( $admin_role ) {
			foreach ( self::CAPABILITIES as $cap ) {
				$admin_role->add_cap( $cap );
			}
		}
	}
	
	/**
	 * Remove custom role and capabilities on deactivation
	 */
	public static function remove_role(): void {
		// Remove deprecated cts_manager role if it exists
		remove_role( self::ROLE_CTS_MANAGER );
		
		// Remove capabilities from Administrator
		$admin_role = get_role( 'administrator' );
		if ( $admin_role ) {
			foreach ( self::CAPABILITIES as $cap ) {
				$admin_role->remove_cap( $cap );
			}
		}
	}
	
	/**
	 * Check if user has ChurchTools Suite access
	 *
	 * @param int|null $user_id User ID (default: current user)
	 * @return bool
	 */
	public static function user_can_manage_churchtools( $user_id = null ): bool {
		if ( $user_id === null ) {
			$user_id = get_current_user_id();
		}
		
		if ( empty( $user_id ) ) {
			return false;
		}
		
		return user_can( $user_id, 'manage_churchtools_suite' );
	}
	
	/**
	 * Check if user can configure ChurchTools
	 *
	 * @param int|null $user_id User ID
	 * @return bool
	 */
	public static function user_can_configure( $user_id = null ): bool {
		if ( $user_id === null ) {
			$user_id = get_current_user_id();
		}
		
		if ( empty( $user_id ) ) {
			return false;
		}
		
		return user_can( $user_id, 'configure_churchtools_suite' );
	}
	
	/**
	 * Check if user can sync events
	 *
	 * @param int|null $user_id User ID
	 * @return bool
	 */
	public static function user_can_sync( $user_id = null ): bool {
		if ( $user_id === null ) {
			$user_id = get_current_user_id();
		}
		
		if ( empty( $user_id ) ) {
			return false;
		}
		
		return user_can( $user_id, 'sync_churchtools_events' );
	}
	
	/**
	 * Get users with ChurchTools Manager role or capability
	 *
	 * @return array Array of WP_User objects
	 */
	public static function get_cts_managers(): array {
		$users = get_users( [
			'role' => self::ROLE_CTS_MANAGER,
		] );
		
		return $users;
	}
}
