<?php
/**
 * Template Data Provider
 * 
 * Fetches and formats event data for templates.
 * Provides clean data structure for all view types.
 *
 * @package ChurchTools_Suite
 * @since   0.5.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ChurchTools_Suite_Template_Data {
	
	/**
	 * Events Repository
	 *
	 * @var ChurchTools_Suite_Events_Repository
	 */
	private $events_repo;
	
	/**
	 * Calendars Repository
	 *
	 * @var ChurchTools_Suite_Calendars_Repository
	 */
	private $calendars_repo;
	
	/**
	 * Event Services Repository
	 *
	 * @var ChurchTools_Suite_Event_Services_Repository
	 */
	private $event_services_repo;
	
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->events_repo = new ChurchTools_Suite_Events_Repository();
		$this->calendars_repo = new ChurchTools_Suite_Calendars_Repository();
		$this->event_services_repo = new ChurchTools_Suite_Event_Services_Repository();
	}
	
	/**
	 * Get events with filters
	 * 
	 * @param array $filters {
	 *     Optional. Query filters.
	 *
	 *     @type array  $calendar_ids ChurchTools calendar IDs
	 *     @type int    $limit        Maximum number of events
	 *     @type string $from         Start date (Y-m-d H:i:s)
	 *     @type string $to           End date (Y-m-d H:i:s)
	 *     @type string $order        Sort order (ASC|DESC)
	 * }
	 * @return array Formatted events data
	 */
	public function get_events( array $filters = [] ): array {
		$defaults = [
			'calendar_ids' => [],
			'limit' => 20,
			'from' => '',
			'to' => '',
			'order' => 'ASC',
		];
		
		$filters = wp_parse_args( $filters, $defaults );
		
		// Build query
		global $wpdb;
		$table = $wpdb->prefix . CHURCHTOOLS_SUITE_DB_PREFIX . 'events';
		
		$where = [];
		$where_values = [];
		
		// Calendar filter - only apply if specific calendars are selected
		if ( ! empty( $filters['calendar_ids'] ) && is_array( $filters['calendar_ids'] ) ) {
			$calendar_placeholders = implode( ',', array_fill( 0, count( $filters['calendar_ids'] ), '%s' ) );
			$where[] = $wpdb->prepare( "calendar_id IN ($calendar_placeholders)", $filters['calendar_ids'] );
		}
		
		// Date range filter - if not specified, show upcoming events (from now)
		if ( ! empty( $filters['from'] ) ) {
			$where[] = $wpdb->prepare( 'start_datetime >= %s', $filters['from'] );
		} else {
			// Default: show events from today onwards
			$where[] = $wpdb->prepare( 'start_datetime >= %s', current_time( 'mysql' ) );
		}
		
		if ( ! empty( $filters['to'] ) ) {
			$where[] = $wpdb->prepare( 'start_datetime <= %s', $filters['to'] );
		}
		
		// Status filter
		$where[] = "status = 'active'";
		
		$where_clause = ! empty( $where ) ? 'WHERE ' . implode( ' AND ', $where ) : '';
		$order = strtoupper( $filters['order'] ) === 'DESC' ? 'DESC' : 'ASC';
		$limit = absint( $filters['limit'] );
		
		$sql = "SELECT * FROM {$table} 
				{$where_clause}
				ORDER BY start_datetime {$order} 
				LIMIT {$limit}";
		
		// Debug output
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'ChurchTools Suite Template Data: SQL Query: ' . $sql );
			error_log( 'ChurchTools Suite Template Data: Filters: ' . print_r( $filters, true ) );
		}
		
		$results = $wpdb->get_results( $sql, ARRAY_A );
		
		// Debug output
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'ChurchTools Suite Template Data: Found ' . ( $results ? count( $results ) : 0 ) . ' events' );
			if ( $wpdb->last_error ) {
				error_log( 'ChurchTools Suite Template Data: SQL Error: ' . $wpdb->last_error );
			}
		}
		
		if ( ! $results ) {
			return [];
		}
		
		// Format events
		$events = [];
		foreach ( $results as $row ) {
			$events[] = $this->format_event( $row );
		}
		
		return $events;
	}
	
	/**
	 * Get single event by ID
	 * 
	 * @param string|int $id Event ID (local ID or ChurchTools event_id)
	 * @return array Formatted event data or empty array
	 */
	public function get_event_by_id( $id ): array {
		if ( empty( $id ) ) {
			return [];
		}
		
		// Try local ID first
		if ( is_numeric( $id ) ) {
			$event = $this->events_repo->get_by_id( absint( $id ) );
		}
		
		// Try ChurchTools event_id
		if ( empty( $event ) ) {
			$event = $this->events_repo->get_by_event_id( (string) $id );
		}
		
		if ( ! $event ) {
			return [];
		}
		
		return $this->format_event( (array) $event );
	}
	
	/**
	 * Format event data for templates
	 * 
	 * @param array $event Raw event data from database
	 * @return array Formatted event data
	 */
	private function format_event( array $event ): array {
		// Get calendar info
		$calendar = null;
		if ( ! empty( $event['calendar_id'] ) ) {
			$calendar = $this->calendars_repo->get_by_calendar_id( $event['calendar_id'] );
		}
		
		// Get services
		$services = [];
		if ( ! empty( $event['id'] ) ) {
			$services = $this->event_services_repo->get_for_event( absint( $event['id'] ) );
			
			// Format services
			$services = array_map( function( $service ) {
				return [
					'service_id' => $service->service_id,
					'service_name' => $service->service_name,
					'person_name' => $service->person_name,
				];
			}, $services );
		}
		
		// Format dates with WordPress timezone
		$date_format = get_option( 'date_format', 'd.m.Y' );
		$time_format = get_option( 'time_format', 'H:i' );
		
		// Check if 24h format (no 'a' or 'A' in format string)
		$is_24h = ( strpos( $time_format, 'a' ) === false && strpos( $time_format, 'A' ) === false );
		$time_suffix = $is_24h ? ' Uhr' : '';
		
		// Convert to WordPress timezone
		$start_timestamp = strtotime( get_date_from_gmt( $event['start_datetime'] ) );
		$end_timestamp = ! empty( $event['end_datetime'] ) ? strtotime( get_date_from_gmt( $event['end_datetime'] ) ) : null;
		
		// Format times with suffix
		$start_time_formatted = date_i18n( $time_format, $start_timestamp ) . $time_suffix;
		$end_time_formatted = '';
		
		if ( $end_timestamp ) {
			$end_time_formatted = date_i18n( $time_format, $end_timestamp ) . $time_suffix;
		}
		
		// Build time display string (always show start-end)
		$time_display = $start_time_formatted;
		if ( $end_time_formatted ) {
			$time_display .= ' - ' . $end_time_formatted;
		}
		
		return [
			// IDs
			'id' => absint( $event['id'] ),
			'event_id' => $event['event_id'] ?? '',
			'appointment_id' => $event['appointment_id'] ?? '',
			'calendar_id' => $event['calendar_id'] ?? '',
			
			// Calendar info
			'calendar_name' => $calendar ? $calendar->name : '',
			'calendar_name_translated' => $calendar ? $calendar->name_translated : '',
			'calendar_color' => $calendar ? $calendar->color : '#3498db',
			
			// Basic data
			'title' => $event['title'] ?? __( 'Unbenannt', 'churchtools-suite' ),
			'description' => $event['description'] ?? '',
			'location_name' => $event['location_name'] ?? '',
			// Structured address fields (preferred)
			'address_name' => $event['address_name'] ?? '',
			'address_street' => $event['address_street'] ?? '',
			'address_zip' => $event['address_zip'] ?? '',
			'address_city' => $event['address_city'] ?? '',
			'address_latitude' => $event['address_latitude'] ?? null,
			'address_longitude' => $event['address_longitude'] ?? null,
			'tags' => $event['tags'] ?? null,
			'status' => $event['status'] ?? 'active',
			
			// Dates
			'start_datetime' => $event['start_datetime'],
			'end_datetime' => $event['end_datetime'] ?? null,
			'start_timestamp' => $start_timestamp,
			'end_timestamp' => $end_timestamp,
			
			// Formatted dates
			'start_date' => date_i18n( $date_format, $start_timestamp ),
			'start_time' => $start_time_formatted,
			'end_date' => $end_timestamp ? date_i18n( $date_format, $end_timestamp ) : '',
			'end_time' => $end_time_formatted,
			'time_display' => $time_display,
			
			// Date components
		'start_day' => date_i18n( 'j', $start_timestamp ), // Tag ohne führende Null
		'start_month' => strtoupper( date_i18n( 'M', $start_timestamp ) ), // Kurzer Monat UPPERCASE (z.B. "DEZ")
		'start_month_short' => date_i18n( 'M', $start_timestamp ), // Kurzer Monat (z.B. "Dez")
		'start_month_full' => date_i18n( 'F', $start_timestamp ), // Voller Monat (z.B. "Dezember")
		'start_year' => date_i18n( 'y', $start_timestamp ), // Jahr 2-stellig (z.B. "25")
			'is_past' => $start_timestamp < current_time( 'timestamp' ),
			'is_today' => date( 'Y-m-d', $start_timestamp ) === current_time( 'Y-m-d' ),
			'is_multiday' => $this->is_multiday_event( $event ),
			'duration_minutes' => $this->get_duration_minutes( $start_timestamp, $end_timestamp ),
			
			// Services
			'services' => $services,
			'services_count' => count( $services ),
			
			// Raw payload (for debugging)
			'raw_payload' => $event['raw_payload'] ?? null,
			
			// Metadata
			'created_at' => $event['created_at'] ?? '',
			'updated_at' => $event['updated_at'] ?? '',
		];
	}
	
	/**
	 * Check if event is all-day
	 * 
	 * @param array $event Event data
	 * @return bool
	 */
	private function is_all_day_event( array $event ): bool {
		// Check if event has is_all_day field
		if ( isset( $event['is_all_day'] ) ) {
			return (bool) $event['is_all_day'];
		}
		
		// Fallback: Check if time is 00:00:00
		if ( ! empty( $event['start_datetime'] ) ) {
			$time = date( 'H:i:s', strtotime( $event['start_datetime'] ) );
			return $time === '00:00:00';
		}
		
		return false;
	}
	
	/**
	 * Check if event spans multiple days
	 * 
	 * @param array $event Event data
	 * @return bool
	 */
	private function is_multiday_event( array $event ): bool {
		if ( empty( $event['end_datetime'] ) ) {
			return false;
		}
		
		$start_date = date( 'Y-m-d', strtotime( $event['start_datetime'] ) );
		$end_date = date( 'Y-m-d', strtotime( $event['end_datetime'] ) );
		
		return $start_date !== $end_date;
	}
	
	/**
	 * Get event duration in minutes
	 * 
	 * @param int $start_timestamp Start timestamp
	 * @param int|null $end_timestamp End timestamp
	 * @return int Duration in minutes
	 */
	private function get_duration_minutes( int $start_timestamp, ?int $end_timestamp ): int {
		if ( ! $end_timestamp ) {
			return 0;
		}
		
		$duration = $end_timestamp - $start_timestamp;
		
		return max( 0, (int) round( $duration / 60 ) );
	}
	
	/**
	 * Get events grouped by date
	 * 
	 * Useful for list/calendar views
	 * 
	 * @param array $filters Query filters
	 * @return array Events grouped by date
	 */
	public function get_events_by_date( array $filters = [] ): array {
		$events = $this->get_events( $filters );
		
		$grouped = [];
		foreach ( $events as $event ) {
			$date = date( 'Y-m-d', $event['start_timestamp'] );
			
			if ( ! isset( $grouped[ $date ] ) ) {
				$grouped[ $date ] = [];
			}
			
			$grouped[ $date ][] = $event;
		}
		
		return $grouped;
	}
	
	/**
	 * Get events grouped by calendar
	 * 
	 * @param array $filters Query filters
	 * @return array Events grouped by calendar
	 */
	public function get_events_by_calendar( array $filters = [] ): array {
		$events = $this->get_events( $filters );
		
		$grouped = [];
		foreach ( $events as $event ) {
			$calendar_id = $event['calendar_id'];
			
			if ( ! isset( $grouped[ $calendar_id ] ) ) {
				$grouped[ $calendar_id ] = [
					'calendar_name' => $event['calendar_name'],
					'calendar_color' => $event['calendar_color'],
					'events' => [],
				];
			}
			
			$grouped[ $calendar_id ]['events'][] = $event;
		}
		
		return $grouped;
	}
	
	/**
	 * Get statistics for events
	 * 
	 * @param array $filters Query filters
	 * @return array Statistics
	 */
	public function get_event_statistics( array $filters = [] ): array {
		$events = $this->get_events( $filters );
		
		$stats = [
			'total' => count( $events ),
			'upcoming' => 0,
			'past' => 0,
			'today' => 0,
			'calendars' => [],
		];

		foreach ( $events as $event ) {
			if ( ! empty( $event['is_past'] ) ) {
				$stats['past']++;
			} else {
				$stats['upcoming']++;
			}

			if ( ! empty( $event['is_today'] ) ) {
				$stats['today']++;
			}

			$calendar_id = $event['calendar_id'];
			if ( ! isset( $stats['calendars'][ $calendar_id ] ) ) {
				$stats['calendars'][ $calendar_id ] = 0;
			}
			$stats['calendars'][ $calendar_id ]++;
		}

		return $stats;
	}
}
