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
		
		// v0.9.9.86: Read template from URL parameter OR Dashboard setting
		$template = isset( $_GET['template'] ) && ! empty( $_GET['template'] )
			? sanitize_text_field( wp_unslash( $_GET['template'] ) )
			: get_option( 'churchtools_suite_single_template', 'professional' );
		
		// v0.9.9.86: Log template selection for debugging
		// Load logger explicitly
		if ( ! class_exists( 'ChurchTools_Suite_Logger' ) ) {
			require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-logger.php';
		}
		
		ChurchTools_Suite_Logger::debug( 'single_event_handler', 'Template selected for single event', [
			'event_id' => $event_id,
			'template_name' => $template,
			'source' => isset( $_GET['template'] ) ? 'URL parameter' : 'Dashboard setting',
			'url_param' => $_GET['template'] ?? 'not set',
			'dashboard_setting' => get_option( 'churchtools_suite_single_template', 'professional' ),
			'expected_path' => 'templates/views/event-single/' . $template . '.php',
		] );

		// Build shortcode attributes (simplified for v1.0)
		$atts = [
			'id' => $event_id,
			'template' => $template,
		];
		
		// Render single event
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/shortcodes/class-churchtools-suite-single-event-shortcode.php';
		$single_event = ChurchTools_Suite_Single_Event_Shortcode::render( $atts );
		
		// Add back button (simplified URL without parameters)
		$back_link = remove_query_arg( [ 'event_id' ] );
		
		$back_button = sprintf(
			'<div class="cts-back-button-wrapper"><a href="%s" class="cts-back-button">← %s</a></div>',
			esc_url( $back_link ),
			esc_html__( 'Zurück zur Übersicht', 'churchtools-suite' )
		);
		
		return $back_button . $single_event;
	}
}
