<?php
/**
 * Demo Users Repository
 *
 * Manages demo user registrations for backend access.
 * 
 * @package ChurchTools_Suite
 * @since   0.10.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ChurchTools_Suite_Demo_Users_Repository extends ChurchTools_Suite_Repository_Base {
	
	/**
	 * Constructor
	 */
	public function __construct() {
		// Table names in repository base expect the plugin-specific prefix included.
		// Use the same prefix as the main plugin to produce `wp_cts_demo_users`.
		parent::__construct( 'cts_demo_users' );
	}
	
	/**
	 * Create new demo user registration
	 *
	 * @param array $data {
	 *     Demo user data
	 *     @type string $email              Required
	 *     @type string $name               Optional
	 *     @type string $company            Optional
	 *     @type string $purpose            Optional
	 *     @type string $verification_token Required (unique)
	 *     @type string $password_hash      Required (hashed password)
	 * }
	 * @return int|false Demo user ID or false on failure
	 */
	public function create( array $data ) {
		$defaults = [
			'email' => '',
			'first_name' => null,
			'last_name' => null,
			'name' => null,
			'company' => null,
			'purpose' => null,
			'verification_token' => '',
			'password_hash' => '',
			'verified_at' => null,
			'wordpress_user_id' => null,
			'last_login_at' => null,
			'created_at' => current_time( 'mysql' ),
		];
		
		$data = wp_parse_args( $data, $defaults );
		
		// Auto-generate name from first_name + last_name if not provided
		if ( empty( $data['name'] ) && ! empty( $data['first_name'] ) && ! empty( $data['last_name'] ) ) {
			$data['name'] = $data['first_name'] . ' ' . $data['last_name'];
		}
		
		// Validate required fields
		if ( empty( $data['email'] ) || empty( $data['verification_token'] ) || empty( $data['password_hash'] ) ) {
			return false;
		}
		
		global $wpdb;
		
		$result = $wpdb->insert(
			$this->get_table_name(),
			$data,
			[ '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s' ]
		);
		
		return $result ? $wpdb->insert_id : false;
	}
	
	/**
	 * Get demo user by email
	 *
	 * @param string $email Email address
	 * @return object|null Demo user object or null
	 */
	public function get_by_email( string $email ): ?object {
		global $wpdb;
		
		$result = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$this->get_table_name()} WHERE email = %s",
				$email
			)
		);
		
		return $result ?: null;
	}
	
	/**
	 * Get demo user by verification token
	 *
	 * @param string $token Verification token
	 * @return object|null Demo user object or null
	 */
	public function get_by_token( string $token ): ?object {
		global $wpdb;
		
		$result = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$this->get_table_name()} WHERE verification_token = %s",
				$token
			)
		);
		
		return $result ?: null;
	}
	
	/**
	 * Get demo user by WordPress user ID
	 *
	 * @param int $wp_user_id WordPress user ID
	 * @return object|null Demo user object or null
	 */
	public function get_by_wp_user_id( int $wp_user_id ): ?object {
		global $wpdb;
		
		$result = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$this->get_table_name()} WHERE wordpress_user_id = %d",
				$wp_user_id
			)
		);
		
		return $result ?: null;
	}
	
	/**
	 * Mark email as verified
	 *
	 * @param int $id          Demo user ID
	 * @param int $wp_user_id  WordPress user ID
	 * @return bool Success
	 */
	public function verify( int $id, int $wp_user_id ): bool {
		global $wpdb;
		
		$result = $wpdb->update(
			$this->get_table_name(),
			[
				'verified_at' => current_time( 'mysql' ),
				'wordpress_user_id' => $wp_user_id,
			],
			[ 'id' => $id ],
			[ '%s', '%d' ],
			[ '%d' ]
		);
		
		return $result !== false;
	}
	
	/**
	 * Update last login timestamp
	 *
	 * @param int $id Demo user ID
	 * @return bool Success
	 */
	public function update_last_login( int $id ): bool {
		global $wpdb;
		
		$result = $wpdb->update(
			$this->get_table_name(),
			[ 'last_login_at' => current_time( 'mysql' ) ],
			[ 'id' => $id ],
			[ '%s' ],
			[ '%d' ]
		);
		
		return $result !== false;
	}
	
	/**
	 * Delete demo user by ID
	 *
	 * @param int $id Demo user ID
	 * @return bool Success
	 */
	public function delete( int $id ): bool {
		global $wpdb;
		
		$result = $wpdb->delete(
			$this->get_table_name(),
			[ 'id' => $id ],
			[ '%d' ]
		);
		
		return $result !== false;
	}
	
	/**
	 * Delete unverified users older than specified days
	 *
	 * @param int $days Days threshold (default: 7)
	 * @return int Number of deleted users
	 */
	public function delete_unverified_older_than( int $days = 7 ): int {
		global $wpdb;
		
		$threshold = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) - ( $days * DAY_IN_SECONDS ) );
		
		$deleted = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$this->get_table_name()} 
				WHERE verified_at IS NULL 
				AND created_at < %s",
				$threshold
			)
		);
		
		return $deleted ?: 0;
	}
	
	/**
	 * Delete verified users older than specified days
	 *
	 * @param int $days Days threshold (default: 30)
	 * @return int Number of deleted users
	 */
	public function delete_verified_older_than( int $days = 30 ): int {
		global $wpdb;
		
		$threshold = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) - ( $days * DAY_IN_SECONDS ) );
		
		// Get WordPress user IDs to delete
		$wp_user_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT wordpress_user_id FROM {$this->get_table_name()} 
				WHERE verified_at IS NOT NULL 
				AND created_at < %s
				AND wordpress_user_id IS NOT NULL",
				$threshold
			)
		);
		
		// Delete from demo_users table
		$deleted = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$this->get_table_name()} 
				WHERE verified_at IS NOT NULL 
				AND created_at < %s",
				$threshold
			)
		);
		
		// Delete corresponding WordPress users
		if ( ! empty( $wp_user_ids ) ) {
			require_once ABSPATH . 'wp-admin/includes/user.php';
			foreach ( $wp_user_ids as $user_id ) {
				wp_delete_user( absint( $user_id ) );
			}
		}
		
		return $deleted ?: 0;
	}
	
	/**
	 * Get all demo users with pagination
	 *
	 * @param array $args {
	 *     Query arguments
	 *     @type int    $limit   Number of results (default: 50)
	 *     @type int    $offset  Offset (default: 0)
	 *     @type string $orderby Order by column (default: 'created_at')
	 *     @type string $order   ASC or DESC (default: 'DESC')
	 *     @type bool   $verified_only Only verified users
	 * }
	 * @return array Demo users
	 */
	public function get_paginated( array $args = [] ): array {
		global $wpdb;
		
		$defaults = [
			'limit' => 50,
			'offset' => 0,
			'orderby' => 'created_at',
			'order' => 'DESC',
			'verified_only' => false,
		];
		
		$args = wp_parse_args( $args, $defaults );
		
		$where = '';
		if ( $args['verified_only'] ) {
			$where = 'WHERE verified_at IS NOT NULL';
		}
		
		$orderby = sanitize_sql_orderby( $args['orderby'] . ' ' . $args['order'] );
		
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$this->get_table_name()} 
				{$where}
				ORDER BY {$orderby}
				LIMIT %d OFFSET %d",
				absint( $args['limit'] ),
				absint( $args['offset'] )
			)
		);
		
		return $results ?: [];
	}
	
	/**
	 * Get statistics
	 *
	 * @return array {
	 *     Statistics data
	 *     @type int $total           Total registrations
	 *     @type int $verified        Verified users
	 *     @type int $unverified      Unverified users
	 *     @type int $last_7_days     Registrations in last 7 days
	 *     @type int $last_30_days    Registrations in last 30 days
	 * }
	 */
	public function get_statistics(): array {
		global $wpdb;
		
		$table = $this->get_table_name();
		
		$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
		$verified = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE verified_at IS NOT NULL" );
		$unverified = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE verified_at IS NULL" );
		
		$threshold_7 = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) - ( 7 * DAY_IN_SECONDS ) );
		$last_7_days = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE created_at >= %s", $threshold_7 )
		);
		
		$threshold_30 = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) - ( 30 * DAY_IN_SECONDS ) );
		$last_30_days = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE created_at >= %s", $threshold_30 )
		);
		
		return [
			'total' => $total,
			'verified' => $verified,
			'unverified' => $unverified,
			'last_7_days' => $last_7_days,
			'last_30_days' => $last_30_days,
		];
	}
}
