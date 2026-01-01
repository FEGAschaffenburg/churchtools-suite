<?php
/**
 * Demo Data Provider
 * 
 * Provides fake ChurchTools data for demo/testing purposes.
 * Only active when CTS_DEMO_MODE constant is set to true in wp-config.php
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
		'Peter Mueller',
		'Sarah Weber',
		'Michael Becker',
		'Laura Wagner',
		'Thomas Fischer',
		'Julia Koch',
		'Daniel Richter',
		'Sophie Klein',
	];
	
	/**
	 * Get demo calendars
	 *
	 * @return array
	 */
	public function get_calendars(): array {
		return $this->demo_calendars;
	}
	
	/**
	 * Get demo events
	 *
	 * Generates events for next 90 days
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
		
		$events = [];
		
		// Gottesdienste (Sundays)
		$events = array_merge( $events, $this->generate_weekly_events(
			'Gottesdienst',
			'1',
			'Sunday',
			'10:00',
			'11:30',
			$args['from'],
			$args['to']
		) );
		
		// Jugend (Fridays)
		$events = array_merge( $events, $this->generate_weekly_events(
			'Jugendabend',
			'2',
			'Friday',
			'19:00',
			'21:00',
			$args['from'],
			$args['to']
		) );
		
		// Kinder (Sundays - parallel to Gottesdienst)
		$events = array_merge( $events, $this->generate_weekly_events(
			'Kindergottesdienst',
			'3',
			'Sunday',
			'10:00',
			'11:30',
			$args['from'],
			$args['to']
		) );
		
		// Lobpreis-Probe (Thursdays)
		$events = array_merge( $events, $this->generate_weekly_events(
			'Lobpreis-Probe',
			'4',
			'Thursday',
			'20:00',
			'21:30',
			$args['from'],
			$args['to']
		) );
		
		// Kleingruppe (Wednesdays)
		$events = array_merge( $events, $this->generate_weekly_events(
			'Hauskreis Mitte',
			'5',
			'Wednesday',
			'19:30',
			'21:30',
			$args['from'],
			$args['to']
		) );
		
		// Special Events
		$events = array_merge( $events, $this->generate_special_events( $args['from'], $args['to'] ) );
		
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
		
		// Apply limit
		if ( $args['limit'] > 0 ) {
			$events = array_slice( $events, 0, $args['limit'] );
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
		$current = strtotime( "next $day_of_week", strtotime( $from ) - DAY_IN_SECONDS );
		$end = strtotime( $to );
		
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
				'address_street' => 'Hauptstraße 123',
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
				'description' => 'Großes Gemeindefest mit Gottesdienst, Essen, Spielen und Musik für die ganze Familie.',
				'event_description' => 'Großes Gemeindefest mit Gottesdienst, Essen, Spielen und Musik für die ganze Familie.',
				'appointment_description' => '',
				'start_datetime' => date( 'Y-m-d', $fest_date ) . ' 11:00:00',
				'end_datetime' => date( 'Y-m-d', $fest_date ) . ' 17:00:00',
				'location_name' => 'Gemeindezentrum',
				'address_name' => 'Gemeindezentrum',
				'address_street' => 'Hauptstraße 123',
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
				'description' => 'Der Alpha-Kurs ist eine Entdeckungsreise zum christlichen Glauben. Für alle, die Fragen haben.',
				'event_description' => 'Der Alpha-Kurs ist eine Entdeckungsreise zum christlichen Glauben. Für alle, die Fragen haben.',
				'appointment_description' => '',
				'start_datetime' => date( 'Y-m-d', $alpha_date ) . ' 19:00:00',
				'end_datetime' => date( 'Y-m-d', $alpha_date ) . ' 21:30:00',
				'location_name' => 'Gemeindezentrum',
				'address_name' => 'Gemeindezentrum',
				'address_street' => 'Hauptstraße 123',
				'address_zip' => '63739',
				'address_city' => 'Aschaffenburg',
				'address_latitude' => 49.9745,
				'address_longitude' => 9.1501,
				'tags' => wp_json_encode( [
					['name' => 'Alpha-Kurs', 'color' => '#2563eb'],
					['name' => 'Gäste willkommen', 'color' => '#16a34a'],
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
			'Kindergottesdienst' => 'Spiel, Spaß und spannende Geschichten aus der Bibel für Kinder von 3-12 Jahren.',
			'Lobpreis-Probe' => 'Gemeinsame Probe für das Lobpreis-Team. Neue Lieder lernen und alte üben.',
			'Hauskreis Mitte' => 'Kleingruppe für alle, die Gemeinschaft und Austausch über den Glauben suchen.',
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
	 * Check if demo mode is enabled
	 *
	 * @return bool
	 */
	public static function is_demo_mode(): bool {
		return defined( 'CTS_DEMO_MODE' ) && CTS_DEMO_MODE === true;
	}
}
