<?php
/**
 * Single Event Handler
 * 
 * Handles rendering of single event view when event_id query parameter is present.
 * Intercepts page content and replaces it with single event display.
 *
 * @package ChurchTools_Suite
 * @since   0.9.3.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ChurchTools_Suite_Single_Event_Handler {
	
	/**
	 * Initialize handler
	 */
	public static function init(): void {
		// Intercept content when event_id is present
		add_filter( 'the_content', [ __CLASS__, 'maybe_show_single_event' ], 1 );
	}
	
	/**
	 * Maybe replace page content with single event view
	 *
	 * @param string $content Original content
	 * @return string Modified content
	 */
	public static function maybe_show_single_event( string $content ): string {
		// Only on singular pages (not archives, home, etc.)
		if ( ! is_singular() ) {
			return $content;
		}
		
		// Check if event_id is present
		$event_id = isset( $_GET['event_id'] ) ? absint( $_GET['event_id'] ) : 0;
		
		if ( ! $event_id ) {
			return $content;
		}
		
		// Parse display settings from query parameters
		$show_event_description = isset( $_GET['show_event_description'] ) && $_GET['show_event_description'] === '1';
		$show_appointment_description = isset( $_GET['show_appointment_description'] ) && $_GET['show_appointment_description'] === '1';
		$show_location = isset( $_GET['show_location'] ) && $_GET['show_location'] === '1';
		$show_services = isset( $_GET['show_services'] ) && $_GET['show_services'] === '1';
		$show_time = isset( $_GET['show_time'] ) && $_GET['show_time'] === '1';
		$show_tags = isset( $_GET['show_tags'] ) && $_GET['show_tags'] === '1';
		$show_calendar_name = isset( $_GET['show_calendar_name'] ) && $_GET['show_calendar_name'] === '1';
		
		// Hole Template-Einstellung aus Option, falls kein Parameter gesetzt
		$template = isset($_GET['template']) ? sanitize_file_name($_GET['template']) : get_option('churchtools_suite_single_template', 'professional');

		// Build shortcode attributes
		$atts = [
			'id' => $event_id,
			'template' => $template,
			'show_event_description' => $show_event_description,
			'show_appointment_description' => $show_appointment_description,
			'show_location' => $show_location,
			'show_services' => $show_services,
			'show_time' => $show_time,
			'show_tags' => $show_tags,
			'show_calendar_name' => $show_calendar_name,
		];
		
		// Render single event
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/shortcodes/class-churchtools-suite-single-event-shortcode.php';
		$single_event = ChurchTools_Suite_Single_Event_Shortcode::render( $atts );
		
		// Add back button
		$back_link = remove_query_arg( [
			'event_id',
			'show_event_description',
			'show_appointment_description',
			'show_location',
			'show_services',
			'show_time',
			'show_tags',
			'show_calendar_name',
		] );
		
		$back_button = sprintf(
			'<div class="cts-back-button-wrapper"><a href="%s" class="cts-back-button">← %s</a></div>',
			esc_url( $back_link ),
			esc_html__( 'Zurück zur Übersicht', 'churchtools-suite' )
		);
		
		return $back_button . $single_event;
	}
}
