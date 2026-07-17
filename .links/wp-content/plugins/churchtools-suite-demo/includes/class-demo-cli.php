<?php
/**
 * WP-CLI Commands for ChurchTools Suite Demo
 * 
 * Provides CLI commands for managing demo users and migrations
 *
 * @package ChurchTools_Suite_Demo
 * @since   1.1.1.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register WP-CLI commands if WP-CLI is available
 */
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'cts-demo', 'ChurchTools_Suite_Demo_CLI' );
}

class ChurchTools_Suite_Demo_CLI {
	
	/**
	 * Run pending database migrations
	 * 
	 * Forces execution of all pending migrations, including user role migrations.
	 * Use this after plugin updates to ensure database schema is up to date.
	 * 
	 * ## EXAMPLES
	 * 
	 *     # Run all pending migrations
	 *     $ wp cts-demo migrate
	 *     Success: Migrations completed from v1.2 to v1.3
	 * 
	 * @when after_wp_load
	 */
	public function migrate( $args, $assoc_args ) {
		WP_CLI::log( 'Starting ChurchTools Demo migrations...' );
		
		$current_version = ChurchTools_Suite_Demo_Migrations::get_current_version();
		WP_CLI::log( "Current DB version: {$current_version}" );
		WP_CLI::log( "Target DB version: " . ChurchTools_Suite_Demo_Migrations::DB_VERSION );
		
		if ( ! ChurchTools_Suite_Demo_Migrations::has_pending_migrations() ) {
			WP_CLI::success( 'Database is already up to date. No migrations needed.' );
			return;
		}
		
		// Run migrations
		ChurchTools_Suite_Demo_Migrations::run_migrations();
		
		$new_version = ChurchTools_Suite_Demo_Migrations::get_current_version();
		WP_CLI::success( "Migrations completed from v{$current_version} to v{$new_version}" );
	}
	
	/**
	 * Migrate demo users from old role to new role
	 * 
	 * Migrates all users from 'cts_demo_user' role to 'demo_tester' role
	 * and ensures correct capabilities. This is also done automatically
	 * by the migration system, but can be run manually if needed.
	 * 
	 * ## EXAMPLES
	 * 
	 *     # Migrate all demo users to new role
	 *     $ wp cts-demo migrate-users
	 *     Success: Migrated 3 users to demo_tester role
	 * 
	 * @when after_wp_load
	 */
	public function migrate_users( $args, $assoc_args ) {
		WP_CLI::log( 'Starting user role migration...' );
		
		// Check for old role users
		$old_users = get_users( [ 'role' => 'cts_demo_user' ] );
		$old_count = count( $old_users );
		
		WP_CLI::log( "Found {$old_count} users with old role 'cts_demo_user'" );
		
		if ( $old_count === 0 ) {
			WP_CLI::log( 'No users with old role found.' );
		} else {
			foreach ( $old_users as $user ) {
				$user->set_role( 'demo_tester' );
				WP_CLI::log( "  → Migrated: {$user->user_login} (ID: {$user->ID})" );
			}
		}
		
		// Check for users with new role
		$new_users = get_users( [ 'role' => 'demo_tester' ] );
		$new_count = count( $new_users );
		
		WP_CLI::log( "Total demo_tester users after migration: {$new_count}" );
		
		// Remove old role if exists
		$old_role = get_role( 'cts_demo_user' );
		if ( $old_role ) {
			remove_role( 'cts_demo_user' );
			WP_CLI::log( 'Removed old role: cts_demo_user' );
		}
		
		WP_CLI::success( "Migrated {$old_count} users to demo_tester role" );
	}
	
	/**
	 * List all demo users and their roles
	 * 
	 * Shows all users with demo-related roles (cts_demo_user, demo_tester)
	 * and their current status.
	 * 
	 * ## OPTIONS
	 * 
	 * [--role=<role>]
	 * : Filter by specific role (cts_demo_user, demo_tester, or all)
	 * ---
	 * default: all
	 * options:
	 *   - all
	 *   - cts_demo_user
	 *   - demo_tester
	 * ---
	 * 
	 * ## EXAMPLES
	 * 
	 *     # List all demo users
	 *     $ wp cts-demo list-users
	 * 
	 *     # List only demo_tester users
	 *     $ wp cts-demo list-users --role=demo_tester
	 * 
	 * @when after_wp_load
	 */
	public function list_users( $args, $assoc_args ) {
		$role = $assoc_args['role'] ?? 'all';
		
		$roles_to_check = [];
		if ( $role === 'all' ) {
			$roles_to_check = [ 'cts_demo_user', 'demo_tester' ];
		} else {
			$roles_to_check = [ $role ];
		}
		
		$all_users = [];
		foreach ( $roles_to_check as $check_role ) {
			$users = get_users( [ 'role' => $check_role ] );
			foreach ( $users as $user ) {
				$all_users[] = [
					'ID' => $user->ID,
					'Username' => $user->user_login,
					'Email' => $user->user_email,
					'Role' => $check_role,
					'Registered' => $user->user_registered,
				];
			}
		}
		
		if ( empty( $all_users ) ) {
			WP_CLI::log( 'No demo users found.' );
			return;
		}
		
		WP_CLI\Utils\format_items( 'table', $all_users, [ 'ID', 'Username', 'Email', 'Role', 'Registered' ] );
		WP_CLI::success( 'Found ' . count( $all_users ) . ' demo users' );
	}
	
	/**
	 * Show database migration status
	 * 
	 * Displays current database version and pending migrations.
	 * 
	 * ## EXAMPLES
	 * 
	 *     # Check migration status
	 *     $ wp cts-demo status
	 * 
	 * @when after_wp_load
	 */
	public function status( $args, $assoc_args ) {
		$current = ChurchTools_Suite_Demo_Migrations::get_current_version();
		$target = ChurchTools_Suite_Demo_Migrations::DB_VERSION;
		$pending = ChurchTools_Suite_Demo_Migrations::has_pending_migrations();
		
		WP_CLI::log( '=== ChurchTools Demo Plugin Status ===' );
		WP_CLI::log( '' );
		WP_CLI::log( "Current DB Version:  {$current}" );
		WP_CLI::log( "Target DB Version:   {$target}" );
		WP_CLI::log( "Pending Migrations:  " . ( $pending ? 'YES' : 'NO' ) );
		WP_CLI::log( '' );
		
		// Count demo users by role
		$old_users = get_users( [ 'role' => 'cts_demo_user' ] );
		$new_users = get_users( [ 'role' => 'demo_tester' ] );
		
		WP_CLI::log( '=== Demo Users ===' );
		WP_CLI::log( "Old Role (cts_demo_user): " . count( $old_users ) );
		WP_CLI::log( "New Role (demo_tester):   " . count( $new_users ) );
		WP_CLI::log( '' );
		
		if ( $pending ) {
			WP_CLI::warning( 'Database migrations are pending. Run: wp cts-demo migrate' );
		} else {
			WP_CLI::success( 'Database is up to date' );
		}
	}
	
	/**
	 * Fix administrator CPT capabilities
	 * 
	 * Ensures administrator role has all CPT capabilities for cts_demo_page.
	 * Use this if admin cannot edit demo pages created by other users.
	 * 
	 * ## EXAMPLES
	 * 
	 *     # Fix admin capabilities
	 *     $ wp cts-demo fix_admin_caps
	 * 
	 * @when after_wp_load
	 */
	public function fix_admin_caps( $args, $assoc_args ) {
		WP_CLI::log( 'Fixing administrator CPT capabilities...' );
		
		// Define all CPT capabilities
		$cpt_caps = [
			'edit_cts_demo_pages',
			'edit_others_cts_demo_pages',
			'publish_cts_demo_pages',
			'read_private_cts_demo_pages',
			'delete_cts_demo_pages',
			'delete_private_cts_demo_pages',
			'delete_published_cts_demo_pages',
			'delete_others_cts_demo_pages',
			'edit_private_cts_demo_pages',
			'edit_published_cts_demo_pages',
			'edit_cts_demo_page',
			'read_cts_demo_page',
			'delete_cts_demo_page',
			'manage_cts_demo_pages',
		];
		
		// Get administrator role
		$admin_role = get_role( 'administrator' );
		
		if ( ! $admin_role ) {
			WP_CLI::error( 'Administrator role not found!' );
		}
		
		$added = 0;
		$existing = 0;
		
		foreach ( $cpt_caps as $cap ) {
			$has_before = $admin_role->has_cap( $cap );
			
			if ( ! $has_before ) {
				$admin_role->add_cap( $cap );
				WP_CLI::log( "  + Added: {$cap}" );
				$added++;
			} else {
				$existing++;
			}
		}
		
		WP_CLI::log( '' );
		WP_CLI::log( "Total capabilities: " . count( $cpt_caps ) );
		WP_CLI::log( "Already existing: {$existing}" );
		WP_CLI::log( "Newly added: {$added}" );
		WP_CLI::log( '' );
		
		if ( $added > 0 ) {
			WP_CLI::success( "Fixed administrator capabilities! Added {$added} missing capabilities." );
		} else {
			WP_CLI::success( 'Administrator already has all CPT capabilities.' );
		}
	}
}
