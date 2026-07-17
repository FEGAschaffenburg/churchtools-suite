<?php
/**
 * Demo Registration Service
 *
 * Handles self-service demo user registration with email verification.
 * 
 * @package ChurchTools_Suite
 * @since   0.10.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ChurchTools_Suite_Demo_Registration_Service {
	
	/**
	 * Demo Users Repository
	 *
	 * @var ChurchTools_Suite_Demo_Users_Repository
	 */
	private $repo;
	
	/**
	 * Constructor
	 *
	 * @param ChurchTools_Suite_Demo_Users_Repository $repo Demo users repository
	 */
	public function __construct( ChurchTools_Suite_Demo_Users_Repository $repo ) {
		$this->repo = $repo;
	}
	
	/**
	 * Register new demo user
	 *
	 * @param array $data {
	 *     Registration data
	 *     @type string $email    Required
	 *     @type string $first_name     Required
	 *     @type string $last_name      Required
	 *     @type string $company  Optional
	 *     @type string $purpose  Optional
	 *     @type string $password Required
	 *     @type string $password_confirm Required
	 * }
	 * @return array|WP_Error {
	 *     Success: ['demo_user_id' => int, 'token' => string]
	 *     Error: WP_Error object
	 * }
	 */
	public function register_user( array $data ) {
		// Validate email
		$email = sanitize_email( $data['email'] ?? '' );
		
		if ( ! is_email( $email ) ) {
			return new WP_Error( 'invalid_email', __( 'Ungültige E-Mail-Adresse', 'churchtools-suite' ) );
		}
		
		// Validate names
		$first_name = sanitize_text_field( $data['first_name'] ?? '' );
		$last_name = sanitize_text_field( $data['last_name'] ?? '' );
		
		if ( empty( $first_name ) || empty( $last_name ) ) {
			return new WP_Error( 'name_required', __( 'Vor- und Nachname sind erforderlich', 'churchtools-suite' ) );
		}
		
		// Validate password
		$password = $data['password'] ?? '';
		$password_confirm = $data['password_confirm'] ?? '';
		
		if ( empty( $password ) ) {
			return new WP_Error( 'password_required', __( 'Passwort ist erforderlich', 'churchtools-suite' ) );
		}
		
		if ( strlen( $password ) < 8 ) {
			return new WP_Error( 'password_too_short', __( 'Passwort muss mindestens 8 Zeichen lang sein', 'churchtools-suite' ) );
		}
		
		if ( $password !== $password_confirm ) {
			return new WP_Error( 'password_mismatch', __( 'Passwörter stimmen nicht überein', 'churchtools-suite' ) );
		}
		
		// Check if email already registered
		$existing = $this->repo->get_by_email( $email );
		if ( $existing ) {
			return new WP_Error( 'email_exists', __( 'Diese E-Mail-Adresse ist bereits registriert', 'churchtools-suite' ) );
		}
		
		// Generate verification token (for legacy compatibility)
		$token = $this->generate_verification_token();
		
		// Hash password
		$password_hash = wp_hash_password( $password );
		
		// Create demo user record
		$demo_user_id = $this->repo->create( [
			'email' => $email,
			'first_name' => $first_name,
			'last_name' => $last_name,
			'company' => sanitize_text_field( $data['company'] ?? '' ),
			'purpose' => sanitize_textarea_field( $data['purpose'] ?? '' ),
			'verification_token' => $token,
			'password_hash' => $password_hash,
		] );
		
		if ( ! $demo_user_id ) {
			return new WP_Error( 'creation_failed', __( 'Registrierung fehlgeschlagen', 'churchtools-suite' ) );
		}
		
		// Get demo user object
		$demo_user = $this->repo->get_by_id( $demo_user_id );
		if ( ! $demo_user ) {
			return new WP_Error( 'creation_failed', __( 'Registrierung fehlgeschlagen', 'churchtools-suite' ) );
		}
		
		// Create WordPress user IMMEDIATELY (no verification needed for demo)
		// Pass original password (not hash) for wp_insert_user
		$wp_result = $this->create_wp_user( $demo_user, $password );
		
		if ( is_wp_error( $wp_result ) ) {
			return $wp_result;
		}
		
		$wp_user_id = $wp_result['wp_user_id'];
		$username = $wp_result['username'];
		$temp_password = $wp_result['password'];
		
		// Mark as verified immediately
		$this->repo->verify( $demo_user_id, $wp_user_id );
		
		// Send welcome email with credentials (non-blocking)
		try {
			$this->send_welcome_email( $email, $username, $first_name );
		} catch ( Exception $e ) {
			// Email sending failed, but don't block registration
			error_log( '[ChurchTools Demo] Welcome email failed: ' . $e->getMessage() );
		}
		
		// Log registration
		if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
			ChurchTools_Suite_Logger::log( 'demo_registration', 'New demo user registered (instant activation)', [
				'demo_user_id' => $demo_user_id,
				'wp_user_id' => $wp_user_id,
				'email' => $email,
				'name' => $first_name . ' ' . $last_name,
				'company' => $data['company'] ?? null,
			] );
		}
		
		// Send admin notification
		$this->send_admin_notification( $demo_user_id, $email, $data );
		
		return [
			'demo_user_id' => $demo_user_id,
			'wp_user_id' => $wp_user_id,
			'username' => $username,
			'auto_login' => true,
		];
	}
	
	/**
	 * Verify email and create WordPress user (DEPRECATED - for backward compatibility only)
	 *
	 * @deprecated Since instant activation, this method is no longer used
	 * @param string $token Verification token
	 * @return array|WP_Error {
	 *     Success: ['wp_user_id' => int, 'auto_login' => bool]
	 *     Error: WP_Error object
	 * }
	 */
	public function verify_email( string $token ) {
		// Get demo user by token
		$demo_user = $this->repo->get_by_token( $token );
		
		if ( ! $demo_user ) {
			return new WP_Error( 'invalid_token', __( 'Ungültiger Verifizierungs-Link', 'churchtools-suite' ) );
		}
		
		// Check if already verified
		if ( $demo_user->verified_at ) {
			return new WP_Error( 'already_verified', __( 'E-Mail bereits verifiziert. Sie können sich jetzt anmelden.', 'churchtools-suite' ) );
		}
		
		// For legacy verification links, we need to generate a temporary password
		// since the original password wasn't stored in plain text
		$temp_password = wp_generate_password( 12, true, true );
		
		// Create WordPress user with temporary password
		$result = $this->create_wp_user( $demo_user, $temp_password );
		
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		
		$wp_user_id = $result['wp_user_id'];
		
		// Mark as verified
		$this->repo->verify( $demo_user->id, $wp_user_id );
		
		// Send success email with credentials
		$this->send_success_email( $demo_user->email, $result['username'], $temp_password );
		
		// Log verification
		if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
			ChurchTools_Suite_Logger::log( 'demo_registration', 'Demo user verified (legacy)', [
				'demo_user_id' => $demo_user->id,
				'wp_user_id' => $wp_user_id,
				'email' => $demo_user->email,
			] );
		}
		
		return [
			'wp_user_id' => $wp_user_id,
			'auto_login' => true,
		];
	}
	
	/**
	 * Auto-login user after verification
	 *
	 * @param int $wp_user_id WordPress user ID
	 * @return bool Success
	 */
	public function auto_login( int $wp_user_id ): bool {
		$user = get_userdata( $wp_user_id );
		
		if ( ! $user ) {
			return false;
		}
		
		// Set auth cookies
		wp_set_auth_cookie( $wp_user_id, true );
		wp_set_current_user( $wp_user_id );
		do_action( 'wp_login', $user->user_login, $user );
		
		// Update last login
		$demo_user = $this->repo->get_by_wp_user_id( $wp_user_id );
		if ( $demo_user ) {
			$this->repo->update_last_login( $demo_user->id );
		}
		
		return true;
	}
	
	/**
	 * Create WordPress user with demo role
	 *
	 * @param object $demo_user Demo user object
	 * @param string $password  Plain text password from registration
	 * @return array|WP_Error ['wp_user_id' => int, 'username' => string, 'password' => string] or error
	 */
	private function create_wp_user( object $demo_user, string $password = '' ) {
		// Generate username from email
		$username = $this->generate_username( $demo_user->email );
		
		// Use plain text password (wp_insert_user will hash it)
		if ( empty( $password ) ) {
			return new WP_Error( 'no_password', __( 'Kein Passwort übergeben', 'churchtools-suite' ) );
		}
		
		// Get first_name and last_name, fallback to name field
		$first_name = $demo_user->first_name ?? '';
		$last_name = $demo_user->last_name ?? '';
		$display_name = trim( $first_name . ' ' . $last_name );
		
		if ( empty( $display_name ) ) {
			$display_name = $demo_user->name ?: $demo_user->email;
		}
		
		// Create user with plain text password (wp_insert_user will hash it)
		$user_id = wp_insert_user( [
			'user_login' => $username,
			'user_pass' => $password, // Plain text - will be hashed by wp_insert_user
			'user_email' => $demo_user->email,
			'display_name' => $display_name,
			'first_name' => $first_name,
			'last_name' => $last_name,
			'role' => 'demo_tester',
		] );
		
		if ( is_wp_error( $user_id ) ) {
			// Log detailed error for debugging
			error_log( '[ChurchTools Demo] wp_insert_user failed: ' . $user_id->get_error_message() );
			
			// Return the actual error message from WordPress
			return new WP_Error( 
				$user_id->get_error_code(), 
				sprintf( 
					__( 'WordPress-Benutzer konnte nicht erstellt werden: %s', 'churchtools-suite' ), 
					$user_id->get_error_message() 
				)
			);
		}
		
		// v1.0.6.0: Initialize default user settings for multi-user isolation
		$this->initialize_user_settings( $user_id );
		
		return [
			'wp_user_id' => $user_id,
			'username' => $username,
			'password' => null, // User knows their password from registration
		];
	}
	
	/**
	 * Initialize default settings for new demo user (v1.0.6.0, v1.0.7.0)
	 * 
	 * Sets up user-specific settings with pre-filled demo data.
	 *
	 * @param int $user_id WordPress user ID
	 * @since 1.0.6.0
	 */
	private function initialize_user_settings( int $user_id ): void {
		// Load user settings class if not already loaded
		if ( ! class_exists( 'ChurchTools_Suite_User_Settings' ) ) {
			require_once CHURCHTOOLS_SUITE_DEMO_PATH . 'includes/class-user-settings.php';
		}
		
		// v1.0.7.0: Enable demo mode by default (user can disable later)
		update_user_meta( $user_id, 'cts_demo_mode', true );
		
		// Set default demo ChurchTools connection (public demo instance)
		ChurchTools_Suite_User_Settings::set( 'ct_url', 'https://demo.church.tools', $user_id );
		
		// Set default sync intervals
		ChurchTools_Suite_User_Settings::set( 'sync_days_past', 7, $user_id );
		ChurchTools_Suite_User_Settings::set( 'sync_days_future', 90, $user_id );
		
		// Disable auto-sync by default (demo users test manually)
		ChurchTools_Suite_User_Settings::set( 'auto_sync_enabled', 0, $user_id );
		
		// v1.0.7.0: Import demo data for immediate testing
		$this->import_demo_data( $user_id );
		
		error_log( sprintf(
			'[ChurchTools Demo] Initialized default settings for user %d (demo mode enabled)',
			$user_id
		) );
	}
	
	/**
	 * Import demo data for new user (v1.0.7.0)
	 * 
	 * Imports calendars, events, and services from demo.church.tools
	 * into isolated demo tables (demo_cts_events, demo_cts_calendars).
	 *
	 * @param int $user_id WordPress user ID
	 * @since 1.0.7.0
	 */
	public function import_demo_data( int $user_id ): void {
		error_log( sprintf(
			'[ChurchTools Demo] Starting demo data import for user %d (isolated demo tables)',
			$user_id
		) );
		
		// Load required classes
		if ( ! class_exists( 'ChurchTools_Suite_CT_Client' ) ) {
			require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-ct-client.php';
		}
		
		// Load Demo Repositories (isolated per user)
		if ( ! class_exists( 'ChurchTools_Suite_Demo_Calendars_Repository' ) ) {
			require_once CHURCHTOOLS_SUITE_DEMO_PATH . 'includes/repositories/class-demo-calendars-repository.php';
		}
		if ( ! class_exists( 'ChurchTools_Suite_Demo_Events_Repository' ) ) {
			require_once CHURCHTOOLS_SUITE_DEMO_PATH . 'includes/repositories/class-demo-events-repository.php';
		}
		if ( ! class_exists( 'ChurchTools_Suite_Demo_Services_Repository' ) ) {
			require_once CHURCHTOOLS_SUITE_DEMO_PATH . 'includes/repositories/class-demo-services-repository.php';
		}
		
		// Create CT client for demo instance
		$ct_client = new ChurchTools_Suite_CT_Client( $user_id );
		
		// Create isolated repositories for this user
		$calendars_repo = new ChurchTools_Suite_Demo_Calendars_Repository( $user_id );
		$events_repo = new ChurchTools_Suite_Demo_Events_Repository( $user_id );
		$services_repo = new ChurchTools_Suite_Demo_Services_Repository( $user_id );
		
		// Import calendars directly from API
		try {
			$response = $ct_client->api_request( '/calendars', 'GET' );
			
			if ( ! is_wp_error( $response ) && isset( $response['data'] ) && is_array( $response['data'] ) ) {
				$imported_count = 0;
				$selected_count = 0;
				
				foreach ( $response['data'] as $index => $calendar_data ) {
					$calendar_id = $calendar_data['id'] ?? $calendar_data['domainIdentifier'] ?? null;
					if ( ! $calendar_id ) {
						continue;
					}
					
					$calendar = [
						'calendar_id' => (string) $calendar_id,
						'name' => $calendar_data['name'] ?? $calendar_data['designation'] ?? __( 'Unbenannt', 'churchtools-suite-demo' ),
						'name_translated' => $calendar_data['nameTranslated'] ?? null,
						'color' => $calendar_data['color'] ?? null,
						'is_selected' => ( $index < 3 ) ? 1 : 0, // Select first 3 calendars
						'sort_order' => $index,
						'raw_payload' => wp_json_encode( $calendar_data ),
					];
					
					if ( $calendars_repo->upsert( $calendar ) ) {
						$imported_count++;
						if ( $calendar['is_selected'] ) {
							$selected_count++;
						}
					}
				}
				
				error_log( sprintf(
					'[ChurchTools Demo] Imported %d calendars for user %d (%d selected)',
					$imported_count,
					$user_id,
					$selected_count
				) );
			}
		} catch ( Exception $e ) {
			error_log( sprintf(
				'[ChurchTools Demo] Calendar import failed for user %d: %s',
				$user_id,
				$e->getMessage()
			) );
		}
		
		// Import events from selected calendars
		try {
			$selected_calendar_ids = $calendars_repo->get_selected_calendar_ids();
			
			if ( empty( $selected_calendar_ids ) ) {
				error_log( sprintf(
					'[ChurchTools Demo] No calendars selected for user %d, skipping event import',
					$user_id
				) );
				return;
			}
			
			// Get date range (next 90 days)
			$from = date( 'Y-m-d' );
			$to = date( 'Y-m-d', strtotime( '+90 days' ) );
			
			$response = $ct_client->api_request( '/events', 'GET', [
				'from' => $from,
				'to' => $to,
			] );
			
			if ( ! is_wp_error( $response ) && isset( $response['data'] ) && is_array( $response['data'] ) ) {
				$imported_count = 0;
				
				foreach ( $response['data'] as $event_data ) {
					// Check if event belongs to one of our selected calendars
					$event_calendar_id = $event_data['calendar']['domainIdentifier'] ?? $event_data['calendar']['id'] ?? null;
					if ( ! $event_calendar_id || ! in_array( (string) $event_calendar_id, $selected_calendar_ids, true ) ) {
						continue;
					}
					
					$event = [
						'event_id' => isset( $event_data['id'] ) ? (string) $event_data['id'] : null,
						'calendar_id' => (string) $event_calendar_id,
						'appointment_id' => isset( $event_data['appointmentId'] ) ? (string) $event_data['appointmentId'] : ( isset( $event_data['id'] ) ? (string) $event_data['id'] : '' ),
						'title' => $event_data['name'] ?? $event_data['designation'] ?? __( 'Unbenannt', 'churchtools-suite-demo' ),
						'event_description' => $event_data['note'] ?? '',
						'start_datetime' => date( 'Y-m-d H:i:s', strtotime( $event_data['startDate'] ?? 'now' ) ),
						'end_datetime' => isset( $event_data['endDate'] ) ? date( 'Y-m-d H:i:s', strtotime( $event_data['endDate'] ) ) : null,
						'location_name' => $event_data['location'] ?? '',
						'raw_payload' => wp_json_encode( $event_data ),
					];
					
					if ( $events_repo->upsert( $event ) ) {
						$imported_count++;
					}
				}
				
				error_log( sprintf(
					'[ChurchTools Demo] Imported %d events for user %d',
					$imported_count,
					$user_id
				) );
			}
		} catch ( Exception $e ) {
			error_log( sprintf(
				'[ChurchTools Demo] Event import failed for user %d: %s',
				$user_id,
				$e->getMessage()
			) );
		}
		
		error_log( sprintf(
			'[ChurchTools Demo] Demo data import completed for user %d',
			$user_id
		) );
	}
	
	/**
	 * Send welcome email with credentials
	 *
	 * @param string $email        Email address
	 * @param string $username     WordPress username
	 * @param string $first_name   First name
	 * @return bool Success
	 */
	private function send_welcome_email( string $email, string $username, string $first_name ): bool {
		$subject = 'Willkommen bei ChurchTools Suite Demo';
		
		$message = sprintf(
			"Hallo %s,\n\n" .
			"vielen Dank für Ihre Registrierung beim ChurchTools Suite Demo-Backend.\n\n" .
			"Ihr Account wurde erfolgreich erstellt und Sie sind bereits eingeloggt!\n\n" .
			"Ihre Zugangsdaten:\n\n" .
			"Login-URL: %s\n" .
			"Benutzername: %s\n" .
			"Passwort: [von Ihnen bei der Registrierung gewählt]\n\n" .
			"Hinweis: Demo-Accounts werden nach 30 Tagen automatisch gelöscht.\n\n" .
			"Viel Spaß beim Testen!\n\n" .
			"Ihr ChurchTools Suite Team",
			$first_name,
			wp_login_url(),
			$username
		);
		
		return wp_mail( $email, $subject, $message );
	}
	
	/**
	 * Generate verification token
	 *
	 * @return string Unique 64-character token
	 */
	private function generate_verification_token(): string {
		return bin2hex( random_bytes( 32 ) );
	}
	
	/**
	 * Generate username from email
	 *
	 * @param string $email Email address
	 * @return string Unique username
	 */
	private function generate_username( string $email ): string {
		$base = sanitize_user( current( explode( '@', $email ) ), true );
		$username = 'demo_' . $base;
		
		// Ensure uniqueness
		$counter = 1;
		while ( username_exists( $username ) ) {
			$username = 'demo_' . $base . '_' . $counter;
			$counter++;
		}
		
		return $username;
	}
	
	/**
	 * Resend verification email (public wrapper for admin)
	 *
	 * @param string $email Email address
	 * @param string $token Verification token
	 * @return bool|WP_Error Success or error
	 */
	public function resend_verification_email( string $email, string $token ) {
		// Get demo user to get ID
		$demo_user = $this->repo->get_by_email( $email );
		
		if ( ! $demo_user ) {
			return new WP_Error( 'user_not_found', __( 'Benutzer nicht gefunden', 'churchtools-suite' ) );
		}
		
		if ( $demo_user->verified_at ) {
			return new WP_Error( 'already_verified', __( 'Benutzer ist bereits verifiziert', 'churchtools-suite' ) );
		}
		
		// Send email
		$sent = $this->send_verification_email( $demo_user->id, $email, $token );
		
		if ( ! $sent ) {
			return new WP_Error( 'email_failed', __( 'E-Mail konnte nicht gesendet werden', 'churchtools-suite' ) );
		}
		
		// Log
		if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
			ChurchTools_Suite_Logger::log( 'demo_registration', 'Verification email resent', [
				'demo_user_id' => $demo_user->id,
				'email' => $email,
			] );
		}
		
		return true;
	}
	
	/**
	 * Send verification email (DEPRECATED - kept for backward compatibility)
	 *
	 * @param int    $demo_user_id Demo user ID
	 * @param string $email        Email address
	 * @param string $token        Verification token
	 * @param string $first_name   First name
	 * @return bool Success
	 * @deprecated Use send_welcome_email() instead
	 */
	private function send_verification_email( int $demo_user_id, string $email, string $token, string $first_name = '' ): bool {
		$verification_url = add_query_arg( [
			'action' => 'cts_verify_demo_user',
			'token' => $token,
		], home_url( '/' ) );
		
		$demo_duration_days = (int) get_option( 'cts_demo_duration_days', 30 );
		
		$subject = sprintf( __( 'Verifizieren Sie Ihre Demo-Registrierung - %s', 'churchtools-suite' ), get_bloginfo( 'name' ) );
		
		$greeting = ! empty( $first_name ) ? sprintf( __( 'Hallo %s,', 'churchtools-suite' ), $first_name ) : __( 'Hallo,', 'churchtools-suite' );
		
		$message = sprintf(
			__( "%s\n\nvielen Dank für Ihre Registrierung für die ChurchTools Suite Demo!\n\nBitte verifizieren Sie Ihre E-Mail-Adresse, indem Sie auf den folgenden Link klicken:\n\n%s\n\nDieser Link ist %d Tage gültig.\n\nNach der Verifizierung erhalten Sie eine separate E-Mail mit Ihren Zugangsdaten zum WordPress-Backend.\n\nHinweis: Ihr Demo-Zugang ist %d Tage gültig und wird dann automatisch deaktiviert.\n\nViel Spaß beim Erkunden!\n\nMit freundlichen Grüßen\nIhr ChurchTools Suite Team", 'churchtools-suite' ),
			$greeting,
			$verification_url,
			$demo_duration_days,
			$demo_duration_days
		);
		
		$headers = [
			'Content-Type: text/plain; charset=UTF-8',
		];
		
		return wp_mail( $email, $subject, $message, $headers );
	}
	
	/**
	 * Send success email with login credentials
	 *
	 * @param string $email    Email address
	 * @param string $username WordPress username
	 * @param string $password Not used anymore (user knows password from registration)
	 * @return bool Success
	 */
	private function send_success_email( string $email, string $username, string $password ): bool {
		$login_url = admin_url();
		
		$demo_duration_days = (int) get_option( 'cts_demo_duration_days', 30 );
		
		$subject = sprintf( __( 'Ihre Demo-Zugangsdaten - %s', 'churchtools-suite' ), get_bloginfo( 'name' ) );
		
		$message = sprintf(
			__( "Herzlich willkommen!\n\nIhre E-Mail-Adresse wurde erfolgreich verifiziert. Sie können sich jetzt mit Ihrem gewählten Passwort anmelden:\n\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n🔐 ZUGANGSDATEN\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\nBenutzername: %s\nPasswort: [von Ihnen bei der Registrierung gewählt]\n\nLogin-URL: %s\n\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n⚠️ WICHTIG:\n• Ihr Demo-Zugang ist %d Tage gültig\n• Sie können das ChurchTools Suite Plugin im Backend erkunden\n• Es stehen vorinstallierte Demo-Daten zur Verfügung\n\n📖 Anleitung:\nBesuchen Sie unsere Schnellstart-Anleitung für Tipps zur Nutzung:\nhttps://plugin.feg-aschaffenburg.de/schnellstart/\n\nViel Spaß beim Erkunden des Plugins!\n\nMit freundlichen Grüßen\nIhr ChurchTools Suite Team", 'churchtools-suite' ),
			$username,
			$login_url,
			$demo_duration_days
		);
		
		$headers = [
			'Content-Type: text/plain; charset=UTF-8',
		];
		
		return wp_mail( $email, $subject, $message, $headers );
	}
	
	/**
	 * Send admin notification (v1.1.4.0: Added BCC support)
	 *
	 * @param int    $demo_user_id Demo user ID
	 * @param string $email        User email
	 * @param array  $data         Registration data
	 * @return bool Success
	 */
	private function send_admin_notification( int $demo_user_id, string $email, array $data ): bool {
		// Admin-Email fest auf plugin@feg-aschaffenburg.de
		$admin_email = 'plugin@feg-aschaffenburg.de';
		
		$subject = sprintf( '🎉 Neue Demo-Registrierung #%d - ChurchTools Suite', $demo_user_id );
		
		$name = isset( $data['first_name'] ) && isset( $data['last_name'] ) 
			? $data['first_name'] . ' ' . $data['last_name'] 
			: ( $data['name'] ?? '-' );
		
		// HTML-formattierte E-Mail
		$message = sprintf(
			"<html><body style='font-family: Arial, sans-serif; line-height: 1.6;'>" .
			"<div style='max-width: 600px; margin: 0 auto; padding: 20px; background: #f5f5f5;'>" .
			"<div style='background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>" .
			"<h2 style='color: #2563eb; margin-top: 0;'>🎉 Neue Demo-Registrierung</h2>" .
			
			"<div style='background: #eff6ff; padding: 20px; border-radius: 8px; margin: 20px 0;'>" .
			"<table style='width: 100%%; border-collapse: collapse;'>" .
			"<tr><td style='padding: 8px 0; font-weight: bold; width: 150px;'>👤 Name:</td><td style='padding: 8px 0;'>%s</td></tr>" .
			"<tr><td style='padding: 8px 0; font-weight: bold;'>📧 E-Mail:</td><td style='padding: 8px 0;'><a href='mailto:%s' style='color: #2563eb;'>%s</a></td></tr>" .
			"<tr><td style='padding: 8px 0; font-weight: bold;'>🏢 Firma/Gemeinde:</td><td style='padding: 8px 0;'>%s</td></tr>" .
			"<tr><td style='padding: 8px 0; font-weight: bold;'>🎯 Verwendungszweck:</td><td style='padding: 8px 0;'>%s</td></tr>" .
			"<tr><td style='padding: 8px 0; font-weight: bold;'>📅 Registriert am:</td><td style='padding: 8px 0;'>%s</td></tr>" .
			"<tr><td style='padding: 8px 0; font-weight: bold;'>🆔 Demo User ID:</td><td style='padding: 8px 0;'>#%d</td></tr>" .
			"</table>" .
			"</div>" .
			
			"<div style='margin: 20px 0; padding: 15px; background: #dcfce7; border-left: 4px solid #16a34a; border-radius: 4px;'>" .
			"<strong style='color: #16a34a;'>✅ Status:</strong> WordPress-Account wurde automatisch erstellt und verifiziert." .
			"</div>" .
			
			"<div style='margin-top: 30px; padding-top: 20px; border-top: 2px solid #e5e7eb;'>" .
			"<p style='margin: 10px 0;'><strong>📊 Admin-Panel:</strong></p>" .
			"<a href='%s' style='display: inline-block; padding: 12px 24px; background: #2563eb; color: white; text-decoration: none; border-radius: 6px; font-weight: bold;'>→ Demo-Registrierungen verwalten</a>" .
			"</div>" .
			
			"</div>" .
			"<div style='text-align: center; margin-top: 20px; color: #6b7280; font-size: 12px;'>" .
			"<p>ChurchTools Suite Demo Plugin • %s</p>" .
			"</div>" .
			"</div>" .
			"</body></html>",
			esc_html( $name ),
			esc_attr( $email ),
			esc_html( $email ),
			esc_html( $data['company'] ?? '-' ),
			esc_html( $data['purpose'] ?? '-' ),
			date_i18n( 'd.m.Y H:i', current_time( 'timestamp' ) ),
			$demo_user_id,
			admin_url( 'admin.php?page=churchtools-suite-demo' ),
			get_bloginfo( 'name' )
		);
		
		$headers = [
			'Content-Type: text/html; charset=UTF-8',
			'From: ChurchTools Suite <noreply@' . wp_parse_url( home_url(), PHP_URL_HOST ) . '>',
		];
		
		// BCC hinzufügen wenn konfiguriert
		$bcc_email = get_option( 'cts_demo_bcc_email', '' );
		if ( ! empty( $bcc_email ) && is_email( $bcc_email ) ) {
			$headers[] = 'Bcc: ' . $bcc_email;
		}
		
		return wp_mail( $admin_email, $subject, $message, $headers );
	}
}
