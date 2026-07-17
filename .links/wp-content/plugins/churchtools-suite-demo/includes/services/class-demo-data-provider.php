<?php
/**
 * Demo Data Provider
 * 
 * Provides demo ChurchTools data for demo/testing purposes.
 * 
 * v1.0.4.0: Modified to read from database instead of generating on-the-fly
 * - Demo events are now stored in the database during plugin activation
 * - This method reads database events for demo calendars (IDs 1-6)
 * - Fallback to generation only if database is not initialized (backwards compatibility)
 * 
 * @package ChurchTools_Suite
 * @since   0.9.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ChurchTools_Suite_Demo_Data_Provider {
	
	/**
	 * Demo calendars (master data)
	 *
	 * @var array
	 */
	private $demo_calendars = [
		[
			'calendar_id' => '1',
			'name' => 'Gottesdienste',
			'name_translated' => 'Gottesdienste',
			'color' => '#2563eb',
			'is_selected' => 1,
		],
		[
			'calendar_id' => '2',
			'name' => 'Jugend',
			'name_translated' => 'Jugend',
			'color' => '#16a34a',
			'is_selected' => 1,
		],
		[
			'calendar_id' => '3',
			'name' => 'Kinder',
			'name_translated' => 'Kinder',
			'color' => '#eab308',
			'is_selected' => 1,
		],
		[
			'calendar_id' => '4',
			'name' => 'Musik',
			'name_translated' => 'Musik',
			'color' => '#dc2626',
			'is_selected' => 1,
		],
		[
			'calendar_id' => '5',
			'name' => 'Kleingruppen',
			'name_translated' => 'Kleingruppen',
			'color' => '#9333ea',
			'is_selected' => 1,
		],
		[
			'calendar_id' => '6',
			'name' => 'Gemeindeveranstaltungen',
			'name_translated' => 'Gemeindeveranstaltungen',
			'color' => '#0891b2',
			'is_selected' => 1,
		],
	];
	
	/**
	 * Demo services
	 *
	 * @var array
	 */
	private $demo_services = [
		['service_id' => '1', 'name' => 'Prediger'],
		['service_id' => '2', 'name' => 'Moderation'],
		['service_id' => '3', 'name' => 'Lobpreis-Leitung'],
		['service_id' => '4', 'name' => 'Gesang'],
		['service_id' => '5', 'name' => 'Keyboard'],
		['service_id' => '6', 'name' => 'Gitarre'],
		['service_id' => '7', 'name' => 'Schlagzeug'],
		['service_id' => '8', 'name' => 'Technik'],
		['service_id' => '9', 'name' => 'Kinderbetreuung'],
	];
	
	/**
	 * Demo person names (for service assignments)
	 *
	 * @var array
	 */
	private $demo_persons = [
		'Max Mustermann',
		'Anna Schmidt',
		'Peter MÃ¼ller',
		'Sarah Weber',
		'Michael Becker',
		'Laura Wagner',
		'Thomas Fischer',
		'Julia Koch',
		'Daniel Richter',
		'Sophie Klein',
	];
	
	/**
	 * Get demo calendars (v1.0.7.1: Respects demo mode, admin-aware)
	 * 
	 * If demo mode is OFF AND not in admin, returns empty array (no demo calendars visible on frontend).
	 * If in admin area (Daten-Seite), always returns calendars regardless of demo mode.
	 * If demo mode is ON, returns demo calendars from database filtered by user_id.
	 *
	 * @return array
	 */
	public function get_calendars(): array {
		// v1.0.7.1: Check if demo mode is active (only relevant for frontend)
		$user_id = get_current_user_id();
		$demo_mode = false;
		
		if ( class_exists( 'ChurchTools_Suite_User_Settings' ) ) {
			$demo_mode = ChurchTools_Suite_User_Settings::is_demo_mode( $user_id );
		}
		
		// v1.0.7.1: If demo mode is OFF AND not in admin area → hide calendars
		// Admin area (Daten-Seite) always shows calendars for configuration/testing
		if ( ! $demo_mode && ! is_admin() ) {
			return [];
		}
		
		// Try to load from database using Repository Factory
		$factory_path = CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-repository-factory.php';
		if ( file_exists( $factory_path ) ) {
			require_once $factory_path;
			
			if ( class_exists( 'ChurchTools_Suite_Repository_Factory' ) ) {
				// Use factory to get user-specific calendars
				$repo = ChurchTools_Suite_Repository_Factory::get_calendars_repo( $user_id );
				$db_calendars = $repo->get_all();
				
				if ( ! empty( $db_calendars ) ) {
					// Convert to array format
					return array_map( function( $cal ) {
						return (array) $cal;
					}, $db_calendars );
				}
			}
		}
		
		// Fallback to hardcoded demo calendars (initial data or backwards compatibility)
		return $this->demo_calendars;
	}
	
	/**
	 * Get demo events
	 *
	 * v1.0.4.0: Reads from database instead of generating on-the-fly
	 * Falls back to generation if database not initialized
	 *
	 * @param array $args Optional parameters (from, to, limit, calendar_ids)
	 * @return array
	 */
	public function get_events( array $args = [] ): array {
		$defaults = [
			'from' => current_time( 'Y-m-d' ),
			'to' => date( 'Y-m-d', current_time( 'timestamp' ) + 90 * DAY_IN_SECONDS ),
			'limit' => 50,
			'calendar_ids' => [],
		];
		$args = wp_parse_args( $args, $defaults );
		
		// v1.0.4.0: Try to load from database first (if Events Repository available)
		$db_events = $this->get_events_from_database( $args );
		
		if ( ! empty( $db_events ) ) {
			// Return database events
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( "Demo Provider: Returning {$db_events} events from database" );
			}
			return $db_events;
		}
		
		// Fallback: Generate events on-the-fly (backwards compatibility)
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( "Demo Provider: Database events not available, falling back to generation" );
		}
		
		return $this->generate_events_fallback( $args );
	}
	
	/**
	 * Get demo events from database (v1.0.4.0, v1.0.7.1: Repository Factory + Demo mode filtering)
	 *
	 * Queries Events Repository for demo calendar events.
	 * v1.0.7.1: Uses Repository Factory for user_id isolation.
	 * If demo mode is OFF AND not in admin, returns empty array (no demo data visible on frontend).
	 * Admin area (Daten-Seite) always shows data regardless of demo mode.
	 *
	 * @param array $args Query parameters
	 * @return array Events from database or empty array if not available
	 */
	private function get_events_from_database( array $args ): array {
		// v1.0.7.1: Check if demo mode is active (only relevant for frontend/shortcodes)
		$user_id = get_current_user_id();
		$demo_mode = false;
		
		if ( class_exists( 'ChurchTools_Suite_User_Settings' ) ) {
			$demo_mode = ChurchTools_Suite_User_Settings::is_demo_mode( $user_id );
		}
		
		// v1.0.7.1: If demo mode is OFF AND not in admin area → hide data
		// Admin area (Daten-Seite) always shows data for configuration/testing
		if ( ! $demo_mode && ! is_admin() ) {
			return [];
		}
		
		// v1.0.7.1: Use Repository Factory for user_id-based filtering
		$factory_path = CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-repository-factory.php';
		if ( file_exists( $factory_path ) ) {
			require_once $factory_path;
			
			if ( class_exists( 'ChurchTools_Suite_Repository_Factory' ) ) {
				// Use factory to get user-specific repo
				$repo = ChurchTools_Suite_Repository_Factory::get_events_repo( $user_id );
			} else {
				// Fallback: Standard repo without user_id
				return [];
			}
		} else {
			// Fallback: Standard repo (backwards compatibility)
			$repo_path = CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-events-repository.php';
			if ( ! file_exists( $repo_path ) ) {
				return [];
			}
			
			require_once $repo_path;
			
			if ( ! class_exists( 'ChurchTools_Suite_Events_Repository' ) ) {
				return [];
			}
			
			$repo = new ChurchTools_Suite_Events_Repository();
		}
		
		// Get demo calendars (1-6)
		$demo_calendar_ids = [ '1', '2', '3', '4', '5', '6' ];
		
		// Use provided calendar IDs if specified, otherwise use all demo calendars
		if ( ! empty( $args['calendar_ids'] ) ) {
			$calendar_ids = array_filter( $args['calendar_ids'], function( $id ) use ( $demo_calendar_ids ) {
				return in_array( (string) $id, $demo_calendar_ids, true );
			} );
		} else {
			$calendar_ids = $demo_calendar_ids;
		}
		
		if ( empty( $calendar_ids ) ) {
			return [];
		}
		
		// Query database for events in date range and calendars
		$from_datetime = $args['from'] . ' 00:00:00';
		$to_datetime = $args['to'] . ' 23:59:59';
		
		$query = "
			SELECT * FROM " . $repo->get_table_name() . "
			WHERE calendar_id IN ('" . implode( "','", array_map( 'esc_sql', $calendar_ids ) ) . "')
			AND start_datetime >= %s
			AND start_datetime <= %s
			ORDER BY start_datetime ASC
			LIMIT %d
		";
		
		global $wpdb;
		$query = $wpdb->prepare( $query, $from_datetime, $to_datetime, intval( $args['limit'] ) );
		$db_results = $wpdb->get_results( $query );
		
		if ( empty( $db_results ) ) {
			return [];
		}
		
		// Convert database objects to event arrays
		$events = [];
		foreach ( $db_results as $row ) {
			$events[] = [
				'id' => $row->id,
				'event_id' => $row->event_id,
				'appointment_id' => $row->appointment_id,
				'calendar_id' => $row->calendar_id,
				'title' => $row->title,
				'description' => $row->description,
				'event_description' => $row->event_description,
				'appointment_description' => $row->appointment_description,
				'start_datetime' => $row->start_datetime,
				'end_datetime' => $row->end_datetime,
				'location_name' => $row->location_name,
				'address_name' => $row->address_name,
				'address_street' => $row->address_street,
				'address_zip' => $row->address_zip,
				'address_city' => $row->address_city,
				'address_latitude' => $row->address_latitude,
				'address_longitude' => $row->address_longitude,
				'tags' => $row->tags,
				'status' => $row->status,
				'image_attachment_id' => $row->image_attachment_id,
				'image_url' => $row->image_url,
				'raw_payload' => $row->raw_payload,
			];
		}
		
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( "Demo Provider: Loaded " . count( $events ) . " events from database" );
		}
		
		return $events;
	}
	
	/**
	 * Generate events on-the-fly (fallback, backwards compatibility)
	 *
	 * @param array $args Query parameters
	 * @return array
	 */
	private function generate_events_fallback( array $args ): array {
		$events = [];
		
		// Debug
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( "Demo Provider: get_events() called START (FALLBACK)" );
			error_log( "  from={$args['from']}, to={$args['to']}, limit={$args['limit']}" );
		}
		
		// Gottesdienste (Sundays)
		$sunday_events = $this->generate_weekly_events(
			'Gottesdienst',
			'1',
			'Sunday',
			'10:00',
			'11:30',
			$args['from'],
			$args['to']
		);
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( "  Gottesdienste: " . count( $sunday_events ) . " events" );
		}
		$events = array_merge( $events, $sunday_events );
		
		// Jugend (Fridays)
		$friday_events = $this->generate_weekly_events(
			'Jugendabend',
			'2',
			'Friday',
			'19:00',
			'21:00',
			$args['from'],
			$args['to']
		);
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( "  Jugendabend: " . count( $friday_events ) . " events" );
		}
		$events = array_merge( $events, $friday_events );
		
		// Kinder (Sundays - parallel to Gottesdienst)
		$kinder_events = $this->generate_weekly_events(
			'Kindergottesdienst',
			'3',
			'Sunday',
			'10:00',
			'11:30',
			$args['from'],
			$args['to']
		);
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( "  Kindergottesdienst: " . count( $kinder_events ) . " events" );
		}
		$events = array_merge( $events, $kinder_events );
		
		// Lobpreis-Probe (Thursdays)
		$lobpreis_events = $this->generate_weekly_events(
			'Lobpreis-Probe',
			'4',
			'Thursday',
			'20:00',
			'21:30',
			$args['from'],
			$args['to']
		);
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( "  Lobpreis-Probe: " . count( $lobpreis_events ) . " events" );
		}
		$events = array_merge( $events, $lobpreis_events );
		
		// Kleingruppe (Wednesdays)
		$haus_events = $this->generate_weekly_events(
			'Hauskreis Mitte',
			'5',
			'Wednesday',
			'19:30',
			'21:30',
			$args['from'],
			$args['to']
		);
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( "  Hauskreis: " . count( $haus_events ) . " events" );
		}
		$events = array_merge( $events, $haus_events );
		
		// Special Events
		$special_events = $this->generate_special_events( $args['from'], $args['to'] );
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( "  Special Events: " . count( $special_events ) . " events" );
			error_log( "  TOTAL BEFORE FILTER: " . count( $events ) . " events" );
		}
		$events = array_merge( $events, $special_events );
		
		// Filter by calendar_ids if specified
		if ( ! empty( $args['calendar_ids'] ) ) {
			$events = array_filter( $events, function( $event ) use ( $args ) {
				return in_array( $event['calendar_id'], $args['calendar_ids'], true );
			} );
		}
		
		// Sort by start date
		usort( $events, function( $a, $b ) {
			return strtotime( $a['start_datetime'] ) - strtotime( $b['start_datetime'] );
		} );
		
		// Debug: Before limit
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( "Demo Provider: Before limit - " . count( $events ) . " events" );
		}
		
		// Apply limit
		if ( $args['limit'] > 0 ) {
			$events = array_slice( $events, 0, $args['limit'] );
		}
		
		// Debug: After limit
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( "Demo Provider: After limit - " . count( $events ) . " events" );
		}
		
		return $events;
	}
	
	/**
	 * Generate weekly recurring events
	 *
	 * @param string $title Event title
	 * @param string $calendar_id Calendar ID
	 * @param string $day_of_week Day name (Monday, Tuesday, etc.)
	 * @param string $start_time Start time (HH:MM)
	 * @param string $end_time End time (HH:MM)
	 * @param string $from Start date (Y-m-d)
	 * @param string $to End date (Y-m-d)
	 * @return array
	 */
	private function generate_weekly_events( 
		string $title, 
		string $calendar_id, 
		string $day_of_week,
		string $start_time,
		string $end_time,
		string $from,
		string $to
	): array {
		$events = [];
		$from_ts = strtotime( $from );
		$current = strtotime( "next $day_of_week", $from_ts - DAY_IN_SECONDS );
		$end = strtotime( $to . ' 23:59:59' );
		
		$counter = 1;
		while ( $current <= $end ) {
			$event_id = $calendar_id . '_' . date( 'Ymd', $current );
			
			$events[] = [
				'id' => $counter++,
				'event_id' => $event_id,
				'appointment_id' => $event_id,
				'calendar_id' => $calendar_id,
				'title' => $title,
				'description' => $this->get_event_description( $title ),
				'event_description' => $this->get_event_description( $title ),
				'appointment_description' => '',
				'start_datetime' => date( 'Y-m-d', $current ) . ' ' . $start_time . ':00',
				'end_datetime' => date( 'Y-m-d', $current ) . ' ' . $end_time . ':00',
				'location_name' => $this->get_event_location( $title ),
				'address_name' => $this->get_event_location( $title ),
				'address_street' => 'HauptstraÃŸe 123',
				'address_zip' => '63739',
				'address_city' => 'Aschaffenburg',
				'address_latitude' => 49.9745,
				'address_longitude' => 9.1501,
				'tags' => wp_json_encode( $this->get_event_tags( $title ) ),
				'status' => 'active',
			];
			
			$current = strtotime( '+1 week', $current );
		}
		
		return $events;
	}
	
	/**
	 * Generate special one-time events
	 *
	 * @param string $from Start date
	 * @param string $to End date
	 * @return array
	 */
	private function generate_special_events( string $from, string $to ): array {
		$events = [];
		$start = strtotime( $from );
		
		// Gemeindefest (in 30 days)
		$fest_date = $start + 30 * DAY_IN_SECONDS;
		if ( $fest_date <= strtotime( $to ) ) {
			$events[] = [
				'id' => 9001,
				'event_id' => 'special_1',
				'appointment_id' => 'special_1',
				'calendar_id' => '6',
				'title' => 'Gemeindefest',
				'description' => 'GroÃŸes Gemeindefest mit Gottesdienst, Essen, Spielen und Musik fÃ¼r die ganze Familie.',
				'event_description' => 'GroÃŸes Gemeindefest mit Gottesdienst, Essen, Spielen und Musik fÃ¼r die ganze Familie.',
				'appointment_description' => '',
				'start_datetime' => date( 'Y-m-d', $fest_date ) . ' 11:00:00',
				'end_datetime' => date( 'Y-m-d', $fest_date ) . ' 17:00:00',
				'location_name' => 'Gemeindezentrum',
				'address_name' => 'Gemeindezentrum',
				'address_street' => 'HauptstraÃŸe 123',
				'address_zip' => '63739',
				'address_city' => 'Aschaffenburg',
				'address_latitude' => 49.9745,
				'address_longitude' => 9.1501,
				'tags' => wp_json_encode( [
					['name' => 'Highlight', 'color' => '#dc2626'],
					['name' => 'Familie', 'color' => '#16a34a'],
				] ),
				'status' => 'active',
			];
		}
		
		// Alpha-Kurs Start (in 14 days)
		$alpha_date = $start + 14 * DAY_IN_SECONDS;
		if ( $alpha_date <= strtotime( $to ) ) {
			$events[] = [
				'id' => 9002,
				'event_id' => 'special_2',
				'appointment_id' => 'special_2',
				'calendar_id' => '6',
				'title' => 'Alpha-Kurs: Startabend',
				'description' => 'Der Alpha-Kurs ist eine Entdeckungsreise zum christlichen Glauben. FÃ¼r alle, die Fragen haben.',
				'event_description' => 'Der Alpha-Kurs ist eine Entdeckungsreise zum christlichen Glauben. FÃ¼r alle, die Fragen haben.',
				'appointment_description' => '',
				'start_datetime' => date( 'Y-m-d', $alpha_date ) . ' 19:00:00',
				'end_datetime' => date( 'Y-m-d', $alpha_date ) . ' 21:30:00',
				'location_name' => 'Gemeindezentrum',
				'address_name' => 'Gemeindezentrum',
				'address_street' => 'HauptstraÃŸe 123',
				'address_zip' => '63739',
				'address_city' => 'Aschaffenburg',
				'address_latitude' => 49.9745,
				'address_longitude' => 9.1501,
				'tags' => wp_json_encode( [
					['name' => 'Alpha-Kurs', 'color' => '#2563eb'],
					['name' => 'GÃ¤ste willkommen', 'color' => '#16a34a'],
				] ),
				'status' => 'active',
			];
		}
		
		return $events;
	}
	
	/**
	 * Get demo services for an event
	 *
	 * @param int $event_id Local event ID
	 * @return array
	 */
	public function get_event_services( int $event_id ): array {
		// Random services for demo
		$services = [];
		$service_count = rand( 2, 4 );
		
		$shuffled_services = $this->demo_services;
		shuffle( $shuffled_services );
		
		for ( $i = 0; $i < $service_count; $i++ ) {
			if ( ! isset( $shuffled_services[ $i ] ) ) {
				break;
			}
			
			$service = $shuffled_services[ $i ];
			$person = $this->demo_persons[ array_rand( $this->demo_persons ) ];
			
			$services[] = [
				'id' => $i + 1,
				'event_id' => $event_id,
				'service_id' => $service['service_id'],
				'service_name' => $service['name'],
				'person_name' => $person,
			];
		}
		
		return $services;
	}
	
	/**
	 * Get description based on event title
	 *
	 * @param string $title Event title
	 * @return string
	 */
	private function get_event_description( string $title ): string {
		$descriptions = [
			'Gottesdienst' => 'Herzliche Einladung zum gemeinsamen Gottesdienst. Mit Lobpreis, Predigt und Gemeinschaft.',
			'Jugendabend' => 'Triff andere Jugendliche, erlebe Gemeinschaft und spannende Themen.',
			'Kindergottesdienst' => 'Spiel, SpaÃŸ und spannende Geschichten aus der Bibel fÃ¼r Kinder von 3-12 Jahren.',
			'Lobpreis-Probe' => 'Gemeinsame Probe fÃ¼r das Lobpreis-Team. Neue Lieder lernen und alte Ã¼ben.',
			'Hauskreis Mitte' => 'Kleingruppe fÃ¼r alle, die Gemeinschaft und Austausch Ã¼ber den Glauben suchen.',
		];
		
		return $descriptions[ $title ] ?? 'Weitere Informationen folgen.';
	}
	
	/**
	 * Get location based on event title
	 *
	 * @param string $title Event title
	 * @return string
	 */
	private function get_event_location( string $title ): string {
		$locations = [
			'Gottesdienst' => 'Gemeindezentrum',
			'Jugendabend' => 'Jugendraum',
			'Kindergottesdienst' => 'Kinderkirche',
			'Lobpreis-Probe' => 'Gemeindesaal',
			'Hauskreis Mitte' => 'Privat (bei Familie Schmidt)',
		];
		
		return $locations[ $title ] ?? 'Gemeindezentrum';
	}
	
	/**
	 * Get tags based on event title
	 *
	 * @param string $title Event title
	 * @return array
	 */
	private function get_event_tags( string $title ): array {
		$tags = [
			'Gottesdienst' => [
				['name' => 'Gottesdienst', 'color' => '#2563eb'],
				['name' => 'Predigt', 'color' => '#16a34a'],
			],
			'Jugendabend' => [
				['name' => 'Jugend', 'color' => '#16a34a'],
				['name' => 'Gemeinschaft', 'color' => '#eab308'],
			],
			'Kindergottesdienst' => [
				['name' => 'Kinder', 'color' => '#eab308'],
				['name' => 'Familie', 'color' => '#16a34a'],
			],
			'Lobpreis-Probe' => [
				['name' => 'Musik', 'color' => '#dc2626'],
				['name' => 'Lobpreis', 'color' => '#9333ea'],
			],
			'Hauskreis Mitte' => [
				['name' => 'Kleingruppe', 'color' => '#9333ea'],
				['name' => 'Gemeinschaft', 'color' => '#eab308'],
			],
		];
		
		return $tags[ $title ] ?? [];
	}
	
	/**
	 * Get raw demo calendars (v1.0.7.1)
	 * 
	 * Returns hardcoded demo calendars for seeding database.
	 * Used by import_demo_data_for_user() to populate demo_cts_calendars.
	 * 
	 * @return array Array of calendar data arrays
	 */
	public function get_demo_calendars_raw(): array {
		return $this->demo_calendars;
	}
	
}
