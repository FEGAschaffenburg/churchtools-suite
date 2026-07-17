<?php
/**
 * User-Aware ChurchTools Client Wrapper
 *
 * Extends main plugin's CT client to use user-specific settings
 * instead of global settings for multi-user isolation.
 *
 * @package ChurchTools_Suite_Demo
 * @since   1.0.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ChurchTools_Suite_Demo_CT_Client {
	
	/**
	 * WordPress user ID
	 *
	 * @var int
	 */
	private $user_id;
	
	/**
	 * Main ChurchTools client instance
	 *
	 * @var ChurchTools_Suite_CT_Client
	 */
	private $ct_client;
	
	/**
	 * Constructor
	 *
	 * @param int|null $user_id WordPress user ID (null = current user)
	 */
	public function __construct( $user_id = null ) {
		$this->user_id = $user_id ?: get_current_user_id();
		
		// We'll override settings via filter before creating CT client
		add_filter( 'option_churchtools_suite_ct_url', [ $this, 'override_ct_url' ] );
		add_filter( 'option_churchtools_suite_ct_username', [ $this, 'override_ct_username' ] );
		add_filter( 'option_churchtools_suite_ct_password', [ $this, 'override_ct_password' ] );
		add_filter( 'option_churchtools_suite_ct_cookies', [ $this, 'override_ct_cookies' ] );
		
		// Create main CT client (will use our overridden settings)
		$this->ct_client = new ChurchTools_Suite_CT_Client();
		
		// Remove filters after creation
		remove_filter( 'option_churchtools_suite_ct_url', [ $this, 'override_ct_url' ] );
		remove_filter( 'option_churchtools_suite_ct_username', [ $this, 'override_ct_username' ] );
		remove_filter( 'option_churchtools_suite_ct_password', [ $this, 'override_ct_password' ] );
		remove_filter( 'option_churchtools_suite_ct_cookies', [ $this, 'override_ct_cookies' ] );
	}
	
	/**
	 * Override CT URL with user-specific setting
	 */
	public function override_ct_url( $value ) {
		return ChurchTools_Suite_User_Settings::get( 'ct_url', $this->user_id, $value );
	}
	
	/**
	 * Override CT username with user-specific setting
	 */
	public function override_ct_username( $value ) {
		return ChurchTools_Suite_User_Settings::get( 'ct_username', $this->user_id, $value );
	}
	
	/**
	 * Override CT password with user-specific setting
	 */
	public function override_ct_password( $value ) {
		return ChurchTools_Suite_User_Settings::get( 'ct_password', $this->user_id, $value );
	}
	
	/**
	 * Override CT cookies with user-specific setting
	 */
	public function override_ct_cookies( $value ) {
		return ChurchTools_Suite_User_Settings::get( 'ct_cookies', $this->user_id, $value );
	}
	
	/**
	 * Proxy all method calls to main CT client
	 *
	 * @param string $method Method name
	 * @param array $args Method arguments
	 * @return mixed
	 */
	public function __call( $method, $args ) {
		if ( method_exists( $this->ct_client, $method ) ) {
			return call_user_func_array( [ $this->ct_client, $method ], $args );
		}
		
		throw new BadMethodCallException( "Method {$method} does not exist on ChurchTools_Suite_CT_Client" );
	}
	
	/**
	 * Test connection for current user
	 *
	 * @return array|WP_Error
	 */
	public function test_connection() {
		return $this->ct_client->test_connection();
	}
	
	/**
	 * Save user-specific cookies after login
	 *
	 * @param array $cookies Cookies to save
	 */
	public function save_cookies( array $cookies ): void {
		ChurchTools_Suite_User_Settings::set( 'ct_cookies', $cookies, $this->user_id );
	}
}
